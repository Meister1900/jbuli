<?php

/**
 * @package     JBuli
 * @copyright   (C) 2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class modBulitorschuetzenHelper
{
    private const ALLOWED_LEAGUES = ['bl1', 'bl2', 'ucl'];

    public function __construct($module)
    {
        $assets = Factory::getApplication()->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'mod_bulitorschuetzen.styles',
            'modules/mod_bulitorschuetzen/media/css/module.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'mod_bulitorschuetzen.script',
            'modules/mod_bulitorschuetzen/media/js/module.js',
            ['version' => 'auto'],
            ['defer' => true]
        );
    }

    public static function getTorschuetzenAjax(): string
    {
        self::sendAjaxNoCacheHeaders();

        $input = Factory::getApplication()->input;
        $moduleId = $input->getInt('module_id');
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'module', 'params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = ' . $moduleId)
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_bulitorschuetzen'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1');
        $module = $db->setQuery($query)->loadObject();

        if (!$module) {
            throw new RuntimeException('Das Torschützen-Modul wurde nicht gefunden.');
        }

        $params = new Registry();
        $params->loadString((string) $module->params);

        $league = strtolower(trim((string) $params->get('league', 'bl1')));
        if (!in_array($league, self::ALLOWED_LEAGUES, true)) {
            $league = 'bl1';
        }

        $season = max(2000, min(2100, (int) $params->get('season', 2026)));
        $limit = max(1, min(50, (int) $params->get('limit', 10)));
        $timeout = max(1, min(30, (int) $params->get('timeout', 5)));
        $refresh = max(1, min(1440, (int) $params->get('refresh', 30)));
        $showPhotos = (bool) $params->get('showphotos', 1);

        $baseUrl = 'https://api.openligadb.de/';
        $goalGetters = self::fetchCachedApiArray(
            $moduleId,
            'goalgetters',
            $league,
            $season,
            $baseUrl . 'getgoalgetters/' . rawurlencode($league) . '/' . $season,
            $refresh,
            $timeout
        );

        if ($goalGetters === []) {
            return '<div class="alert alert-warning">'
                . htmlspecialchars(
                    (string) $params->get('timeout_error', 'Die Torschützenliste konnte derzeit nicht geladen werden.'),
                    ENT_QUOTES,
                    'UTF-8'
                )
                . '</div>';
        }

        $teams = self::fetchCachedApiArray(
            $moduleId,
            'teams',
            $league,
            $season,
            $baseUrl . 'getavailableteams/' . rawurlencode($league) . '/' . $season,
            max($refresh, 360),
            $timeout
        );
        $matches = self::fetchCachedApiArray(
            $moduleId,
            'matches',
            $league,
            $season,
            $baseUrl . 'getmatchdata/' . rawurlencode($league) . '/' . $season,
            $refresh,
            $timeout
        );

        return self::renderGoalGetters($goalGetters, $teams, $matches, $limit, $showPhotos);
    }

    public static function uploadPlayerPortraitAjax(): array
    {
        $app = Factory::getApplication();
        if (!$app->isClient('administrator') || !Session::checkToken('post')) {
            throw new RuntimeException('Ungültige Upload-Anfrage.');
        }

        $user = $app->getIdentity();
        if (!$user || !$user->authorise('core.manage', 'com_modules')) {
            throw new RuntimeException('Keine Berechtigung zum Hochladen von Spielerbildern.');
        }

        $input = $app->input;
        $moduleId = $input->getInt('module_id');
        $module = self::publishedSiteModule($moduleId);
        if (!$module) {
            throw new RuntimeException('Die veröffentlichte Torschützen-Modulinstanz wurde nicht gefunden.');
        }

        $playerId = $input->getInt('player_id');
        $upload = $input->files->get('portrait', null, 'raw');
        if ($playerId <= 0 || !is_array($upload)) {
            throw new RuntimeException('Spieler und Bilddatei sind erforderlich.');
        }
        if ((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Die Bilddatei konnte nicht hochgeladen werden.');
        }

        $temporaryFile = (string) ($upload['tmp_name'] ?? '');
        $size = (int) ($upload['size'] ?? 0);
        if ($temporaryFile === '' || !is_uploaded_file($temporaryFile) || $size <= 0 || $size > 5 * 1024 * 1024) {
            throw new RuntimeException('Die Bilddatei ist ungültig oder größer als 5 MB.');
        }

        $params = new Registry();
        $params->loadString((string) $module->params);
        $league = self::validatedLeague((string) $params->get('league', 'bl1'));
        $season = max(2000, min(2100, (int) $params->get('season', 2026)));
        $refresh = max(1, min(1440, (int) $params->get('refresh', 30)));
        $timeout = max(1, min(30, (int) $params->get('timeout', 5)));
        $goalGetters = self::goalGettersForModule($moduleId, $league, $season, $refresh, $timeout);
        if (!in_array($playerId, array_map(static fn (array $entry): int => (int) self::field($entry, 'goalGetterId', 0), $goalGetters), true)) {
            throw new RuntimeException('Der ausgewählte Spieler gehört nicht zur eingestellten Liga und Saison.');
        }

        $image = @getimagesize($temporaryFile);
        $extensions = ['image/webp' => 'webp', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mime = is_array($image) ? (string) ($image['mime'] ?? '') : '';
        if (!is_array($image) || (int) ($image[0] ?? 0) < 150 || !isset($extensions[$mime])) {
            throw new RuntimeException('Erlaubt sind dekodierbare JPEG-, PNG- oder WebP-Bilder ab 150 Pixel Breite.');
        }

        $directory = __DIR__ . '/images/player-uploads';
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Der lokale Ordner für Spielerbilder konnte nicht erstellt werden.');
        }

        $extension = $extensions[$mime];
        $target = $directory . '/' . $playerId . '.' . $extension;
        $temporaryTarget = $target . '.upload';
        if (!move_uploaded_file($temporaryFile, $temporaryTarget)) {
            throw new RuntimeException('Die Bilddatei konnte nicht gespeichert werden.');
        }

        foreach (['webp', 'jpg', 'jpeg', 'png'] as $oldExtension) {
            $oldFile = $directory . '/' . $playerId . '.' . $oldExtension;
            if (is_file($oldFile) && !@unlink($oldFile)) {
                @unlink($temporaryTarget);
                throw new RuntimeException('Ein vorhandenes Spielerbild konnte nicht ersetzt werden.');
            }
        }
        if (!@rename($temporaryTarget, $target)) {
            @unlink($temporaryTarget);
            throw new RuntimeException('Die Bilddatei konnte nicht finalisiert werden.');
        }

        return ['message' => 'Spielerbild gespeichert als ' . $playerId . '.' . $extension . '.', 'playerId' => $playerId];
    }

    public static function goalGetterOptions(
        int $moduleId,
        string $league,
        int $season,
        int $refresh,
        int $timeout
    ): array {
        if ($moduleId <= 0) {
            return [];
        }
        $goalGetters = self::goalGettersForModule(
            $moduleId,
            self::validatedLeague($league),
            max(2000, min(2100, $season)),
            max(1, min(1440, $refresh)),
            max(1, min(30, $timeout))
        );
        usort($goalGetters, static function (array $left, array $right): int {
            return strcasecmp(
                self::goalGetterLabel((string) self::field($left, 'goalGetterName', '')),
                self::goalGetterLabel((string) self::field($right, 'goalGetterName', ''))
            );
        });

        $result = [];
        foreach ($goalGetters as $goalGetter) {
            $playerId = (int) self::field($goalGetter, 'goalGetterId', 0);
            if ($playerId <= 0) {
                continue;
            }
            $result[] = [
                'id' => $playerId,
                'label' => self::goalGetterLabel((string) self::field($goalGetter, 'goalGetterName', '')),
                'goals' => max(0, (int) self::field($goalGetter, 'goalCount', 0)),
            ];
        }

        return $result;
    }

    private static function renderGoalGetters(
        array $goalGetters,
        array $teams,
        array $matches,
        int $limit,
        bool $showPhotos
    ): string {
        usort($goalGetters, static function (array $left, array $right): int {
            $goalDifference = (int) self::field($right, 'goalCount', 0)
                <=> (int) self::field($left, 'goalCount', 0);
            if ($goalDifference !== 0) {
                return $goalDifference;
            }

            return strcasecmp(
                (string) self::field($left, 'goalGetterName', ''),
                (string) self::field($right, 'goalGetterName', '')
            );
        });
        $goalGetters = array_slice($goalGetters, 0, $limit);

        $teamData = [];
        foreach ($teams as $team) {
            $teamId = (int) self::field($team, 'teamId', 0);
            if ($teamId <= 0) {
                continue;
            }
            $teamData[$teamId] = [
                'name' => trim((string) self::field($team, 'shortName', ''))
                    ?: trim((string) self::field($team, 'teamName', '')),
                'icon' => self::localTeamIcon((string) self::field($team, 'teamName', ''))
                    ?: self::safeRemoteImageUrl((string) self::field($team, 'teamIconUrl', '')),
            ];
        }
        foreach ($matches as $match) {
            foreach (['team1', 'team2'] as $side) {
                $team = self::field($match, $side, []);
                if (!is_array($team)) {
                    continue;
                }
                $teamId = (int) self::field($team, 'teamId', 0);
                if ($teamId <= 0) {
                    continue;
                }
                $fallback = [
                    'name' => trim((string) self::field($team, 'shortName', ''))
                        ?: trim((string) self::field($team, 'teamName', '')),
                    'icon' => self::localTeamIcon((string) self::field($team, 'teamName', ''))
                        ?: self::safeRemoteImageUrl((string) self::field($team, 'teamIconUrl', '')),
                ];
                $current = $teamData[$teamId] ?? ['name' => '', 'icon' => ''];
                $teamData[$teamId] = [
                    'name' => $current['name'] !== '' ? $current['name'] : $fallback['name'],
                    'icon' => $current['icon'] !== '' ? $current['icon'] : $fallback['icon'],
                ];
            }
        }

        $playerTeams = self::buildPlayerTeamMap($matches);
        $html = '<div class="jbuli-scorers-table-wrap"><table class="jbuli-scorers-table">'
            . '<thead><tr><th class="jbuli-scorers-rank">Pl.</th>'
            . '<th>Spieler</th><th class="jbuli-scorers-goals">Tore</th></tr></thead><tbody>';

        $lastGoals = null;
        $displayRank = 0;
        foreach ($goalGetters as $index => $goalGetter) {
            $playerId = (int) self::field($goalGetter, 'goalGetterId', 0);
            $playerName = trim((string) self::field($goalGetter, 'goalGetterName', ''));
            $goals = max(0, (int) self::field($goalGetter, 'goalCount', 0));
            if ($playerName === '') {
                $playerName = 'Unbekannter Spieler';
            }
            if ($lastGoals === null || $goals !== $lastGoals) {
                $displayRank = $index + 1;
            }
            $lastGoals = $goals;

            $teamId = $playerTeams[$playerId] ?? 0;
            $team = $teamData[$teamId] ?? ['name' => '', 'icon' => ''];
            $photo = $showPhotos ? self::resolvePlayerPhoto($playerId) : '';
            $initials = self::playerInitials($playerName);

            $html .= '<tr class="jbuli-scorers-row">'
                . '<td class="jbuli-scorers-rank">' . $displayRank . '</td>'
                . '<td><div class="jbuli-scorers-player">';
            if ($showPhotos) {
                $html .= '<span class="jbuli-scorers-portrait' . ($photo !== '' ? ' has-photo' : ' is-placeholder')
                    . '" tabindex="0"' . ($photo === '' ? ' data-tooltip="Kein Bild hochgeladen"' : '')
                    . ' aria-label="' . ($photo !== '' ? 'Porträt von ' : 'Kein Porträt von ')
                    . htmlspecialchars($playerName, ENT_QUOTES, 'UTF-8') . '">';
                if ($photo !== '') {
                    $html .= '<img src="' . htmlspecialchars($photo, ENT_QUOTES, 'UTF-8')
                        . '" alt="" loading="lazy" decoding="async">';
                } else {
                    $html .= '<span class="jbuli-scorers-initials" aria-hidden="true">'
                        . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                $html .= '</span>';
            }
            $html .= '<span class="jbuli-scorers-player-text"><strong>'
                . htmlspecialchars($playerName, ENT_QUOTES, 'UTF-8') . '</strong>';
            if ($team['name'] !== '' || $team['icon'] !== '') {
                $html .= '<span class="jbuli-scorers-team">';
                if ($team['icon'] !== '') {
                    $html .= '<img src="' . htmlspecialchars($team['icon'], ENT_QUOTES, 'UTF-8')
                        . '" alt="" loading="lazy" decoding="async">';
                }
                $html .= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $html .= '</span></div></td>'
                . '<td class="jbuli-scorers-goals"><strong>' . $goals . '</strong></td></tr>';
        }

        return $html . '</tbody></table></div>';
    }

    private static function buildPlayerTeamMap(array $matches): array
    {
        $counts = [];
        foreach ($matches as $match) {
            $goals = self::field($match, 'goals', []);
            if (!is_array($goals)) {
                continue;
            }
            foreach ($goals as $goal) {
                if (!is_array($goal)) {
                    continue;
                }
                $playerId = (int) self::field($goal, 'goalGetterId', 0);
                $teamId = (int) self::field($goal, 'scoringTeamId', 0);
                if ($playerId <= 0 || $teamId <= 0) {
                    continue;
                }
                $counts[$playerId][$teamId] = ($counts[$playerId][$teamId] ?? 0) + 1;
            }
        }

        $result = [];
        foreach ($counts as $playerId => $teamCounts) {
            arsort($teamCounts, SORT_NUMERIC);
            $result[(int) $playerId] = (int) array_key_first($teamCounts);
        }

        return $result;
    }

    private static function resolvePlayerPhoto(int $playerId): string
    {
        if ($playerId <= 0) {
            return '';
        }
        foreach (['webp', 'jpg', 'jpeg', 'png'] as $extension) {
            $filename = $playerId . '.' . $extension;
            $filePath = __DIR__ . '/images/player-uploads/' . $filename;
            if (is_file($filePath) && self::isValidPlayerPortrait($filePath)) {
                return Uri::root() . 'modules/mod_bulitorschuetzen/images/player-uploads/' . rawurlencode($filename);
            }
        }

        return '';
    }

    private static function localTeamIcon(string $teamName): string
    {
        $name = mb_strtolower(trim($teamName));
        $files = ['arsenal' => 'arsenal.png', 'atletico' => 'atletico.png', 'madrid' => 'real.png', 'paris' => 'paris.png', 'barcelona' => 'barcelona.png'];
        foreach ($files as $needle => $file) {
            if (str_contains($name, $needle)) {
                return Uri::root() . 'modules/mod_bulitorschuetzen/images/teams/' . $file;
            }
        }
        return '';
    }

    private static function publishedSiteModule(int $moduleId): ?object
    {
        if ($moduleId <= 0) {
            return null;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'module', 'params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = ' . $moduleId)
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_bulitorschuetzen'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1');

        return $db->setQuery($query)->loadObject() ?: null;
    }

    private static function goalGettersForModule(
        int $moduleId,
        string $league,
        int $season,
        int $refresh,
        int $timeout
    ): array {
        return self::fetchCachedApiArray(
            $moduleId,
            'goalgetters',
            $league,
            $season,
            'https://api.openligadb.de/getgoalgetters/' . rawurlencode($league) . '/' . $season,
            $refresh,
            $timeout
        );
    }

    private static function validatedLeague(string $league): string
    {
        $league = strtolower(trim($league));

        return in_array($league, self::ALLOWED_LEAGUES, true) ? $league : 'bl1';
    }

    private static function goalGetterLabel(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) < 2) {
            return $name !== '' ? $name : 'Unbekannter Spieler';
        }
        $lastName = array_pop($parts);

        return $lastName . ', ' . implode(' ', $parts);
    }

    private static function isValidPlayerPortrait(string $filePath): bool
    {
        static $validity = [];
        if (array_key_exists($filePath, $validity)) {
            return $validity[$filePath];
        }

        $image = @getimagesize($filePath);
        $validity[$filePath] = is_array($image)
            && (int) ($image[0] ?? 0) >= 150
            && in_array((string) ($image['mime'] ?? ''), ['image/webp', 'image/jpeg', 'image/png'], true);

        return $validity[$filePath];
    }

    private static function playerInitials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letter = preg_replace('/[^\p{L}\p{N}]/u', '', $part) ?: '';
            $initials .= mb_strtoupper(mb_substr($letter, 0, 1));
        }

        return $initials !== '' ? $initials : '?';
    }

    private static function safeRemoteImageUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : '';
    }

    private static function field(array $record, string $name, $default = null)
    {
        foreach ($record as $key => $value) {
            if (strcasecmp((string) $key, $name) === 0) {
                return $value;
            }
        }

        return $default;
    }

    private static function fetchCachedApiArray(
        int $moduleId,
        string $type,
        string $league,
        int $season,
        string $url,
        int $refreshMinutes,
        int $timeout
    ): array {
        $cacheFile = JPATH_CACHE . '/mod_bulitorschuetzen_' . $moduleId . '_'
            . preg_replace('/[^a-z0-9_-]/i', '', $type) . '_'
            . preg_replace('/[^a-z0-9_-]/i', '', $league) . '_' . $season . '.json';
        $cached = self::readJsonArray($cacheFile);
        if ($cached !== [] && is_file($cacheFile) && filemtime($cacheFile) + ($refreshMinutes * 60) > time()) {
            return $cached;
        }

        $lockHandle = @fopen($cacheFile . '.lock', 'c+');
        if (!is_resource($lockHandle)) {
            return $cached;
        }
        $hasLock = @flock($lockHandle, LOCK_EX);
        if (!$hasLock) {
            fclose($lockHandle);
            return $cached;
        }

        $freshAfterLock = self::readJsonArray($cacheFile);
        if ($freshAfterLock !== [] && is_file($cacheFile) && filemtime($cacheFile) + ($refreshMinutes * 60) > time()) {
            self::releaseLock($lockHandle);
            return $freshAfterLock;
        }
        if ($freshAfterLock !== []) {
            $cached = $freshAfterLock;
        }

        $json = self::fetchData($url, $timeout);
        $decoded = is_string($json) ? self::decodeJsonArray($json) : [];
        if ($decoded === [] && $cached === []) {
            $json = self::fetchData($url, max(5, $timeout + 2));
            $decoded = is_string($json) ? self::decodeJsonArray($json) : [];
        }
        if ($decoded !== []) {
            self::writeCacheAtomically($cacheFile, (string) $json);
            $cached = $decoded;
        }

        self::releaseLock($lockHandle);

        return $cached;
    }

    private static function fetchData(string $url, int $timeout)
    {
        if (function_exists('curl_version')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
                CURLOPT_TIMEOUT => max(1, $timeout),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Joomla/6 mod_bulitorschuetzen',
            ]);
            $content = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

            return is_string($content) && $status >= 200 && $status < 300 ? $content : false;
        }
        if (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => max(1, $timeout),
                    'user_agent' => 'Joomla/6 mod_bulitorschuetzen',
                ],
            ]);

            return @file_get_contents($url, false, $context);
        }

        return false;
    }

    private static function decodeJsonArray(string $json): array
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private static function readJsonArray(string $filename): array
    {
        if (!is_readable($filename)) {
            return [];
        }
        $json = file_get_contents($filename);

        return is_string($json) ? self::decodeJsonArray($json) : [];
    }

    private static function writeCacheAtomically(string $filename, string $content): void
    {
        try {
            $suffix = bin2hex(random_bytes(5));
        } catch (Throwable $e) {
            $suffix = str_replace('.', '', uniqid('', true));
        }
        $temporary = $filename . '.' . $suffix . '.tmp';
        if (file_put_contents($temporary, $content, LOCK_EX) === false) {
            return;
        }
        if (!@rename($temporary, $filename)) {
            @unlink($temporary);
        }
    }

    private static function releaseLock($lockHandle): void
    {
        @flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }

    private static function sendAjaxNoCacheHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('CDN-Cache-Control: no-store');
        header('Cloudflare-CDN-Cache-Control: no-store');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Type: application/json; charset=utf-8');
    }
}

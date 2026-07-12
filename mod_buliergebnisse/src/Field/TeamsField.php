<?php

namespace Jbuli\Module\Buliergebnisse\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

class TeamsField extends ListField
{
    protected $type = 'Teams';

    protected function getOptions(): array
    {
        $data = $this->form->getData();
        $league = trim((string) $data->get('params.league', '')) ?: 'bl1';
        $season = (int) $data->get('params.season', 2026);
        $season = $season > 0 ? $season : 2026;
        $nameFormat = (string) $data->get('params.nameformat', 'medium');
        $selectedTeam = (string) $data->get('params.meinVerein', '');
        $league = $this->normaliseLeagueShortcut($league, $season);

        $json = $this->fetch('https://api.openligadb.de/getavailableteams/' . rawurlencode($league) . '/' . $season);
        $teams = $json ? json_decode($json) : [];
        $options = [];

        foreach ((array) $teams as $team) {
            if (empty($team->teamName)) {
                continue;
            }
            $shortName = trim((string) ($team->shortName ?? ''));
            $label = $nameFormat === 'long' || $shortName === '' ? $team->teamName : $shortName;
            $options[] = HTMLHelper::_('select.option', $team->teamName, $label);
        }

        if ($selectedTeam !== '' && !array_filter($options, static fn($option): bool => $option->value === $selectedTeam)) {
            $options[] = HTMLHelper::_('select.option', $selectedTeam, $selectedTeam);
        }

        usort($options, static fn($a, $b): int => strcasecmp($a->text, $b->text));

        if ($options === []) {
            // Eine vorübergehend nicht erreichbare API darf das Backend-Feld
            // nicht vollständig leeren. Die XML-Optionen bleiben als Fallback.
            $options = array_values(array_filter(
                parent::getOptions(),
                static fn($option): bool => (string) ($option->value ?? '') !== ''
            ));
        }

        return array_merge(
            [HTMLHelper::_('select.option', '', 'Keinen Verein hervorheben')],
            $options
        );
    }

    private function normaliseLeagueShortcut(string $league, int $season): string
    {
        $league = strtolower(trim($league));
        if ($season >= 2025 && in_array($league, ['cl1617', 'cl', 'ucl2025'], true)) {
            return 'ucl';
        }
        if ($season >= 2025 && $league === 'pl') {
            return 'epl';
        }
        if ($season >= 2026 && $league === 'pd') {
            return 'la1';
        }

        return $league;
    }

    private function fetch(string $url): string|false
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Joomla/6 mod_buliergebnisse backend',
        ]);
        $content = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        return $content !== false && $status >= 200 && $status < 300 ? $content : false;
    }
}

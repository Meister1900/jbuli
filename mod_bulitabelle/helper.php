<?php

/**
 * @package     JBuli
 * @copyright   (C) 2014-2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Uri\Uri as JURI;
use Joomla\Database\DatabaseInterface;

/**
 * helper.php - (c) Markus Krupp
 * Die Daten werden vom Webservice openligadb.de bereitgestellt.
 */

class modBulitabelleHelper
{
    /**
     * Constructor
     */
    public function __construct($module)
    {

        // Load JQuery
        JHtml::_('jquery.framework');

        $assets = JFactory::getApplication()->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle('mod_bulitabelle.styles', 'modules/mod_bulitabelle/media/css/module.css', ['version' => 'auto']);
        $assets->registerAndUseScript('mod_bulitabelle.script', 'modules/mod_bulitabelle/media/js/module.js', ['version' => 'auto'], ['defer' => true]);
        /*
        $document->addStyleDeclaration(
            '#bulitabelle_' . (int) $module->id . ' { width:100%; max-width:none; overflow-x:hidden; }'
                . '#bulitabelle_' . (int) $module->id . ' .jbuli-loader { display:inline-block; width:22px; height:22px; box-sizing:border-box; border:3px solid currentColor; border-right-color:transparent; border-radius:50%; opacity:.72; vertical-align:middle; animation:jbuli-spin .72s linear infinite; }'
                . '@keyframes jbuli-spin { to { transform:rotate(360deg); } }'
                . '@media (prefers-reduced-motion:reduce) { #bulitabelle_' . (int) $module->id . ' .jbuli-loader { animation-duration:1.6s; } }'
                . '#bulitabelle_' . (int) $module->id . ' table { width:100%; border-collapse:collapse; font-variant-numeric:tabular-nums; }'
                . '#bulitabelle_' . (int) $module->id . ' th,'
                . '#bulitabelle_' . (int) $module->id . ' td { vertical-align:middle; white-space:nowrap; padding:5px 6px; }'
                . '#bulitabelle_' . (int) $module->id . ' thead th { font-weight:800; border-bottom:2px solid currentColor; padding-top:7px; padding-bottom:7px; }'
                . '#bulitabelle_' . (int) $module->id . ' tbody tr { border-bottom:1px solid rgba(127,127,127,.3); }'
                . '#bulitabelle_' . (int) $module->id . ' tbody tr:hover { background:rgba(127,127,127,.08); }'
                . '#bulitabelle_' . (int) $module->id . ' .jbuli-logo { width:32px; min-width:32px; padding-left:2px; padding-right:6px; }'
                . '#bulitabelle_' . (int) $module->id . ' .jbuli-logo img { display:block; width:20px; height:20px; object-fit:contain; }'
                . '#bulitabelle_' . (int) $module->id . ' .jbuli-team { width:100%; min-width:88px; white-space:normal; text-align:left !important; padding-right:12px; }'
                . '#bulitabelle_' . (int) $module->id . ' .jbuli-points { font-weight:900; color:#c40018; text-align:center; }'
                . '#bulitabelle_' . (int) $module->id . ' .jbuli-column-hidden { display:none !important; }'
                . '#bulitabelle_' . (int) $module->id . ' th:last-child,'
                . '#bulitabelle_' . (int) $module->id . ' td:last-child { padding-right:10px; }'
                . '#bulitabelle_' . (int) $module->id . ' tr.jbuli-zone-separator { border-bottom:2px solid rgba(100,100,100,.75); }'
        );

        $document->addScriptDeclaration('
      jQuery(document).ready(function() {
        load_bulitabelle_' . $module->id . '();
        var container = document.getElementById("bulitabelle_' . $module->id . '");
        if (container && window.ResizeObserver) {
          new ResizeObserver(function() {
            window.requestAnimationFrame(fit_bulitabelle_' . $module->id . ');
          }).observe(container);
        } else {
          jQuery(window).on("resize.bulitabelle_' . $module->id . '", fit_bulitabelle_' . $module->id . ');
        }
      });

      function fit_bulitabelle_' . $module->id . '() {
        var container = document.getElementById("bulitabelle_' . $module->id . '");
        var table = container ? container.querySelector(".jbuli-standings") : null;
        if (!container || !table) { return; }

        table.querySelectorAll(".jbuli-responsive-column").forEach(function(cell) {
          cell.classList.remove("jbuli-column-hidden");
        });

        var priorities = [".jbuli-form", ".jbuli-goals", ".jbuli-diff", ".jbuli-played"];
        priorities.forEach(function(selector) {
          if (table.scrollWidth <= container.clientWidth + 1) { return; }
          table.querySelectorAll(selector).forEach(function(cell) {
            cell.classList.add("jbuli-column-hidden");
          });
        });
      }
        
      function load_bulitabelle_' . $module->id . '() {
        jQuery("#bulitabelle_loading_' . $module->id . '").show();
        jQuery.post( "' . JURI::base() . 'index.php",
            {
              option: "com_ajax",
              module: "bulitabelle",
              Itemid: "' . $itemId . '",
              method: "getTabelle",
              format: "json",
              module_id: "' . (int) $module->id . '"
            },
            function(data){
              jQuery("#bulitabelle_loading_' . $module->id . '").hide();
              if (data.success == false) {
                jQuery("#bulitabelle_' . $module->id . '").html(data.message);
              } else {
                jQuery("#bulitabelle_' . $module->id . '").html(data.data);
                fit_bulitabelle_' . $module->id . '();
              }
            }
        ).fail(function(xhr) {
		  try {
			// Ungewollten Output von anderen Plugins wie GoogleAnalytics oder PHP Meldungen wegschneiden
			data = jQuery.parseJSON(xhr.responseText.substring(xhr.responseText.indexOf("success")-2));
		  }
		  catch (e) {
			data = {success: false, message: "Keine Verbindung zum Ergebnisserver. Bitte später erneut versuchen."};
		  };
          jQuery("#bulitabelle_loading_' . $module->id . '").hide();
          if (data.success == false) {
            jQuery("#bulitabelle_' . $module->id . '").html(data.message);
          } else {
            jQuery("#bulitabelle_' . $module->id . '").html(data.data);
            fit_bulitabelle_' . $module->id . '();
          }
        });
      };
    ');
        */
    }

    /**
     * fetch data from api using curl or file_get_contents
     */
    public static function fetchdata($url, $timeout)
    {
        $url = str_replace('https://www.openligadb.de/api/', 'https://api.openligadb.de/', $url);
        if (function_exists('curl_version')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, min(5, (int) $timeout));
            curl_setopt($curl, CURLOPT_TIMEOUT, max(1, (int) $timeout));
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla/6 mod_bulitabelle');
            $content = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            return $content !== false && $status >= 200 && $status < 300 ? $content : false;
        } elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
                'http' => ['timeout' => $timeout]
            ]);

            return file_get_contents($url, 0, $context);
        }

        return false;
    }

    private static function decodeApiResponse(string $json)
    {
        $value = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return self::normaliseApiKeys($value);
    }

    private static function normaliseApiKeys($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'normaliseApiKeys'], $value);
        }
        if (is_object($value)) {
            $normalised = new stdClass();
            foreach (get_object_vars($value) as $key => $item) {
                $normalised->{ucfirst($key)} = self::normaliseApiKeys($item);
            }
            return $normalised;
        }
        return $value;
    }

    /**
     * AJAX Endpoint
     */
    public static function getTabelleAjax()
    {
        self::sendAjaxNoCacheHeaders();
        $jinput = JFactory::getApplication()->input;
        $db = JFactory::getContainer()->get(DatabaseInterface::class);
        $moduleId = $jinput->getInt('module_id');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'module', 'params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = ' . $moduleId)
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_bulitabelle'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1');
        $module = $db->setQuery($query)->loadObject();
        if (!$module) {
            throw new RuntimeException('Das Tabellen-Modul wurde nicht gefunden.');
        }

        $jparams = new JRegistry();
        $jparams->loadString($module->params);

        $liga = (string) $jparams->get('league', 'bl1');
        if (!in_array($liga, ['bl1', 'bl2'], true)) {
            $liga = 'bl1';
        }
        $season = max(1, (int) $jparams->get('season', 2026));
        $timeout = max(1, (int) $jparams->get('timeout', 3));
        $refreshSeconds = max(60, (int) $jparams->get('refresh', 60) * 60);

        // Tabelle aus der Joomla Tabelle holen
        $query = 'SELECT ' . $db->quoteName('team') . ', ' . $db->quoteName('spiele') . ', ' . $db->quoteName('gewonnen') . ', ' . $db->quoteName('unentschieden') . ', ' . $db->quoteName('verloren') . ', ' . $db->quoteName('tore') . ', ' . $db->quoteName('gegentore') . ', ' . $db->quoteName('punkte') . ' FROM ' . $db->quoteName('#__bulitabelle') . ' WHERE modul_id = ' . (int) $module->id . ' ORDER BY punkte DESC, tore-gegentore DESC, tore DESC';

        $db->setQuery($query);
        $tabelle = $db->loadAssocList();

        $spieltag = max(1, (int) $jparams->get('lastCurrentMatchday', 1));

        // Tabelle aktualisieren falls Refresh-Intervall erreicht
        $refreshStateFile = JPATH_CACHE . '/mod_bulitabelle_' . (int) $module->id
            . '_standings_' . $liga . '_' . $season . '.json';
        $refreshState = self::readRefreshState($refreshStateFile);
        if (self::refreshAttemptIsDue($tabelle, $refreshState, $refreshSeconds, time())) {
            $refreshLock = @fopen($refreshStateFile . '.lock', 'c');
            if (!is_resource($refreshLock) || !@flock($refreshLock, LOCK_EX)) {
                if (is_resource($refreshLock)) {
                    fclose($refreshLock);
                }
                if ($tabelle === []) {
                    throw new RuntimeException('Die Tabelle wird bereits aktualisiert.');
                }
            } else {
                try {
                    // Nach dem Lock erneut prüfen: Ein paralleler Request kann die
                    // Tabelle und den Zeitstempel inzwischen aktualisiert haben.
                    $db->setQuery($query);
                    $tabelle = $db->loadAssocList();
                    $refreshState = self::readRefreshState($refreshStateFile);
                    if (self::refreshAttemptIsDue($tabelle, $refreshState, $refreshSeconds, time())) {
                        $refreshState['last_attempt'] = time();
                        if (!self::writeRefreshState($refreshStateFile, $refreshState)) {
                            throw new RuntimeException('Der Aktualisierungsstatus konnte nicht gespeichert werden.');
                        }

                        $paarungen = self::fetchdata(
                            'https://www.openligadb.de/api/getmatchdata/' . $liga . '/' . $season,
                            $timeout
                        );

                        if ($paarungen != false && stristr($paarungen, 'Maximale Abfrageanzahl von 1000 Abfragen pro Tag erreicht!') == false && stristr($paarungen, 'An error has occurred') == false) {
                            $paarungen = self::decodeApiResponse($paarungen);
                            if (!is_array($paarungen)) {
                                $paarungen = [];
                            }
                            $berechneteTabelle = [];
                            $i = 0;
                            foreach ($paarungen as $partie) {
                    if (!is_object($partie) || !isset($partie->Team1->TeamName, $partie->Team2->TeamName)) {
                        continue;
                    }
                    $i++;
                    $alle_ergebnisse = is_array($partie->MatchResults ?? null) ? $partie->MatchResults : [];
                    if (isset($alle_ergebnisse[0]) && $alle_ergebnisse[0] instanceof stdClass) {
                        $tore_team1 = null;
                        $tore_team2 = null;
                        foreach ($alle_ergebnisse as $ergebnis) {
                            if (($ergebnis->ResultName ?? '') == 'Endergebnis' && isset($ergebnis->PointsTeam1, $ergebnis->PointsTeam2)) {
                                $tore_team1 = $ergebnis->PointsTeam1;
                                $tore_team2 = $ergebnis->PointsTeam2;

                                break;
                            }
                        }

                        if ($tore_team1 === null || $tore_team2 === null) {
                            continue;
                        }

                        if (!isset($berechneteTabelle[$partie->Team1->TeamName])) {
                            $berechneteTabelle[$partie->Team1->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        }
                        if (!isset($berechneteTabelle[$partie->Team2->TeamName])) {
                            $berechneteTabelle[$partie->Team2->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        }

                        if ($tore_team1 == $tore_team2) {
                            $punkte_team1 = 1;
                            $punkte_team2 = 1;
                            [$sieg1, $remis1, $niederlage1] = [0, 1, 0];
                            [$sieg2, $remis2, $niederlage2] = [0, 1, 0];
                        } elseif ($tore_team1 > $tore_team2) {
                            $punkte_team1 = 3;
                            $punkte_team2 = 0;
                            [$sieg1, $remis1, $niederlage1] = [1, 0, 0];
                            [$sieg2, $remis2, $niederlage2] = [0, 0, 1];
                        } elseif ($tore_team1 < $tore_team2) {
                            $punkte_team1 = 0;
                            $punkte_team2 = 3;
                            [$sieg1, $remis1, $niederlage1] = [0, 0, 1];
                            [$sieg2, $remis2, $niederlage2] = [1, 0, 0];
                        }

                        $berechneteTabelle[$partie->Team1->TeamName] = [
                            'spiele' => $berechneteTabelle[$partie->Team1->TeamName]['spiele'] + 1,
                            'gewonnen' => $berechneteTabelle[$partie->Team1->TeamName]['gewonnen'] + $sieg1,
                            'unentschieden' => $berechneteTabelle[$partie->Team1->TeamName]['unentschieden'] + $remis1,
                            'verloren' => $berechneteTabelle[$partie->Team1->TeamName]['verloren'] + $niederlage1,
                            'punkte' => $berechneteTabelle[$partie->Team1->TeamName]['punkte'] + $punkte_team1,
                            'tore' => $berechneteTabelle[$partie->Team1->TeamName]['tore'] + $tore_team1,
                            'gegentore' => $berechneteTabelle[$partie->Team1->TeamName]['gegentore'] + $tore_team2
                        ];
                        $berechneteTabelle[$partie->Team2->TeamName] = [
                            'spiele' => $berechneteTabelle[$partie->Team2->TeamName]['spiele'] + 1,
                            'gewonnen' => $berechneteTabelle[$partie->Team2->TeamName]['gewonnen'] + $sieg2,
                            'unentschieden' => $berechneteTabelle[$partie->Team2->TeamName]['unentschieden'] + $remis2,
                            'verloren' => $berechneteTabelle[$partie->Team2->TeamName]['verloren'] + $niederlage2,
                            'punkte' => $berechneteTabelle[$partie->Team2->TeamName]['punkte'] + $punkte_team2,
                            'tore' => $berechneteTabelle[$partie->Team2->TeamName]['tore'] + $tore_team2,
                            'gegentore' => $berechneteTabelle[$partie->Team2->TeamName]['gegentore'] + $tore_team1
                        ];
                    } elseif ($i < 10) {
                        $berechneteTabelle[$partie->Team1->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        $berechneteTabelle[$partie->Team2->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        if ($i == 9) {
                            break;
                        }
                    }
                }

                            if ($berechneteTabelle !== []) {
                                $db->transactionStart();
                                try {
                                    $sql = 'DELETE FROM ' . $db->quoteName('#__bulitabelle') . ' WHERE modul_id = ' . (int) $module->id;
                                    $db->setQuery($sql)->execute();

                                    foreach ($berechneteTabelle as $name => $team) {
                    if ($name == 'SV Sandhausen' && $jparams->get('season') == '2015') {
                        $team['punkte'] -= 3;
                    }
                    $sql = 'REPLACE INTO ' . $db->quoteName('#__bulitabelle')
                        . ' (' . implode(',', $db->quoteName(['team', 'spiele', 'gewonnen', 'unentschieden', 'verloren', 'tore', 'gegentore', 'punkte', 'modul_id'])) . ')'
                        . ' VALUES (' . implode(',', [$db->quote($name), (int) $team['spiele'], (int) $team['gewonnen'], (int) $team['unentschieden'], (int) $team['verloren'], (int) $team['tore'], (int) $team['gegentore'], (int) $team['punkte'], (int) $module->id]) . ')';
                                        $db->setQuery($sql)->execute();
                                    }
                                    $db->transactionCommit();
                                } catch (Throwable $exception) {
                                    $db->transactionRollback();
                                    throw $exception;
                                }

                                $refreshState['last_success'] = time();
                                if (!self::writeRefreshState($refreshStateFile, $refreshState)) {
                                    throw new RuntimeException('Der erfolgreiche Aktualisierungsstand konnte nicht gespeichert werden.');
                                }
                                $db->setQuery($query);
                                $tabelle = $db->loadAssocList();
                            }
                        }
                    }
                } finally {
                    @flock($refreshLock, LOCK_UN);
                    fclose($refreshLock);
                }
            }
        }

        // Live Spiele laden
        $liveteams = [];
        if ($jparams->get('live') == '1') {
            // Öffentliche AJAX-Aufrufe dürfen OpenLigaDB nicht bei jedem
            // Seitenaufruf erneut belasten. Live-Metadaten fünf Minuten cachen.
            $liveMetadataTtl = 300;
            $currentGroupCache = JPATH_CACHE . '/mod_bulitabelle_' . (int) $module->id
                . '_currentgroup_' . $liga . '.json';
            $currentGroupJson = self::fetchCachedApiResponse(
                'https://api.openligadb.de/getcurrentgroup/' . $liga,
                $currentGroupCache,
                $timeout,
                $liveMetadataTtl
            );
            $currentGroup = is_string($currentGroupJson) ? self::decodeApiResponse($currentGroupJson) : null;
            if (is_object($currentGroup) && isset($currentGroup->GroupOrderID)) {
                $spieltag = max(1, (int) $currentGroup->GroupOrderID);
            }

            // Paarungen abrufen
            $saison = $season;
            $liveMatchesCache = JPATH_CACHE . '/mod_bulitabelle_' . (int) $module->id
                . '_matches_' . $liga . '_' . $saison . '_' . $spieltag . '.json';
            $paarungenJson = self::fetchCachedApiResponse(
                'https://api.openligadb.de/getmatchdata/' . $liga . '/' . $saison . '/' . $spieltag,
                $liveMatchesCache,
                $timeout,
                $liveMetadataTtl
            );
            $decodedMatches = is_string($paarungenJson) ? self::decodeApiResponse($paarungenJson) : null;
            $paarungen = is_array($decodedMatches) ? $decodedMatches : [];

            // LIVE Spiele ermitteln
            foreach ((array) $paarungen as $partie) {
                if (!is_object($partie) || !isset($partie->Team1->TeamName, $partie->Team2->TeamName)) {
                    continue;
                }
                if (isset($partie->MatchResults[0])) {
                    $ergebnisse = $partie->MatchResults[0];
                    if ($ergebnisse instanceof stdClass) {
                        if (!($partie->MatchIsFinished ?? true)) {
                            $liveteams[] = $partie->Team1->TeamName;
                            $liveteams[] = $partie->Team2->TeamName;
                        }
                    }
                }
            }
        }

        $teamIconUrls = [];
        $teamsCache = JPATH_CACHE . '/mod_bulitabelle_' . (int) $module->id
            . '_teams_' . $liga . '_' . $season . '.json';
        $teamsJson = self::fetchCachedApiResponse(
            'https://api.openligadb.de/getavailableteams/' . $liga . '/' . $season,
            $teamsCache,
            $timeout,
            max($refreshSeconds, 21600)
        );
        $availableTeams = is_string($teamsJson) ? self::decodeApiResponse($teamsJson) : null;
        foreach (is_array($availableTeams) ? $availableTeams : [] as $apiTeam) {
            if (!is_object($apiTeam)) {
                continue;
            }
            $teamName = trim((string) ($apiTeam->TeamName ?? ''));
            $iconUrl = self::safeRemoteImageUrl((string) ($apiTeam->TeamIconUrl ?? ''));
            if ($teamName !== '' && $iconUrl !== '') {
                $teamIconUrls[$teamName] = $iconUrl;
            }
        }

        // Bezeichnung Webservice => Bezeichnung in Tabelle
        $ersetzen = [
            'FC Bayern München' => 'Bayern',
            'Bayer 04 Leverkusen' => 'Leverkusen',
            'FC Bayern' => 'Bayern',
            'Bayer Leverkusen' => 'Leverkusen',
            'Borussia Dortmund' => 'Dortmund',
            'FC Schalke 04' => 'Schalke',
            'Borussia Mönchengladbach' => 'Gladbach',
            'VfL Wolfsburg' => 'Wolfsburg',
            '1. FSV Mainz 05' => 'Mainz',
            'Hertha BSC' => 'Hertha',
            'FC Augsburg' => 'Augsburg',
            'Hannover 96' => 'Hannover',
            'TSG 1899 Hoffenheim' => 'Hoffenheim',
            'TSG Hoffenheim' => 'Hoffenheim',
            'Eintracht Frankfurt' => 'Frankfurt',
            'Werder Bremen' => 'Bremen',
            'SV Werder Bremen' => 'Bremen',
            'VfB Stuttgart' => 'Stuttgart',
            'SC Freiburg' => 'Freiburg',
            '1. FC Nürnberg' => 'Nürnberg',
            'Hamburger SV' => 'Hamburg',
            'Eintracht Braunschweig' => 'Braunschweig',
            'Energie Cottbus' => 'Cottbus',
            'FC Energie Cottbus' => 'Cottbus',
            'Arminia Bielefeld' => 'Bielefeld',
            'DSC Arminia Bielefeld' => 'Bielefeld',
            'Karlsruher SC' => 'Karlsruhe',
            '1. FC Kaiserslautern' => 'Lautern',
            'VfL Bochum' => 'Bochum',
            'SG Dynamo Dresden' => 'Dresden',
            'Dynamo Dresden' => 'Dresden',
            '1. FC Köln' => 'Köln',
            'Erzgebirge Aue' => 'Aue',
            'FC Ingolstadt 04' => 'Ingolstadt',
            'SC Paderborn 07' =>  'Paderborn',
            'SV Sandhausen' => 'Sandhausen',
            'VfR Aalen' => 'Aalen',
            'Fortuna Düsseldorf' => 'Düsseldorf',
            'FC St. Pauli' => 'St. Pauli',
            'SpVgg Greuther Fürth' => 'Fürth',
            '1. FC Union Berlin' => 'Berlin',
            'FSV Frankfurt' => 'FSV Frankfurt',
            'SV Darmstadt 98' => 'Darmstadt',
            '1. FC Heidenheim 1846' => 'Heidenheim',
            'RB Leipzig' => 'Leipzig',
            'MSV Duisburg' => 'Duisburg',
            'Arminia Bielefeld' => 'Bielefeld',
            'Jahn Regensburg' => 'Regensburg',
            'Holstein Kiel' => 'Kiel',
            'SG Dynamo Dresden' => 'Dresden',
            '1. FC Magdeburg' => 'Magdeburg',
            'VfL Osnabrück' => 'Osnabrück',
            'SV Wehen Wiesbaden' => 'Wiesbaden',
            'Würzburger Kickers' => 'Würzburg',
            'FC Hansa Rostock' => 'Rostock',
            'SV 07 Elversberg' => 'Elversberg',
            'Preußen Münster' => 'Münster',
        ];

        if (count($tabelle) == 0) {
            throw new Exception('Zurzeit können keine Daten vom Webservice abgerufen werden :-(');
        }

        $platz = 1;
        $style = 'text-align:right; vertical-align:middle; margin-right:2px;';
        $htmloutput = '<table class="jbuli-standings"><thead><tr>'
            . '<th style="' . $style . '">Pl.</th><th aria-label="Logo"></th><th style="text-align:left;">Team</th>'
            . '<th class="jbuli-played jbuli-responsive-column" style="' . $style . '">Sp.</th>'
            . '<th class="jbuli-form jbuli-responsive-column" style="' . $style . '">G</th>'
            . '<th class="jbuli-form jbuli-responsive-column" style="' . $style . '">U</th>'
            . '<th class="jbuli-form jbuli-responsive-column" style="' . $style . '">V</th>'
            . '<th class="jbuli-goals jbuli-responsive-column" style="' . $style . '">Tore</th>'
            . '<th class="jbuli-diff jbuli-responsive-column" style="' . $style . '">Diff.</th>'
            . '<th style="' . $style . '">Pkt</th></tr></thead><tbody>';

        $previousRankKey = null;
        foreach ($tabelle as $row) {
            $diff = (int) $row['tore'] - (int) $row['gegentore'];
            $rankKey = (int) $row['punkte'] . ':' . $diff . ':' . (int) $row['tore'];
            $displayPlace = $rankKey === $previousRankKey ? '' : (string) $platz;

            if ($jparams->get('live') == '1' && in_array($row['team'], $liveteams)) {
                $tdstyle = 'text-align:right; color:red; vertical-align:middle; margin-right:2px;';
            } else {
                $tdstyle = 'text-align:right; vertical-align:middle; margin-right:2px;';
            }

            if ($row['team'] == $jparams->get('meinVerein')) {
                $trstyle = self::escapeHtmlAttribute((string) $jparams->get('meinVereinCSS', ''));
            } else {
                $trstyle = '';
            }

            $zoneSeparator = ($jparams->get('league') == 'bl1' && in_array($platz, [1, 4, 5, 6, 15, 16], true))
                || ($jparams->get('league') == 'bl2' && in_array($platz, [2, 3, 15, 16], true));

            $displayName = $jparams->get('longnames') == '1'
                ? $row['team']
                : ($ersetzen[$row['team']] ?? $row['team']);
            $logoName = $ersetzen[$row['team']] ?? $row['team'];
            $logo = strtolower(str_replace(['ü', 'ä', 'ö', ' '], ['ue', 'ae', 'oe', ''], $logoName)) . '.png';
            if ($logoName === 'Cottbus') {
                $logo = 'cottbus.svg';
            } elseif ($logoName === 'St. Pauli') {
                $logo = 'st.pauli.png';
            } elseif ($logoName === 'Münster') {
                $logo = 'muenster.svg';
            }
            $localLogoUrl = self::localLogoUrl($logo);
            $remoteLogoUrl = $teamIconUrls[$row['team']] ?? '';
            $logoSource = $remoteLogoUrl !== '' ? $remoteLogoUrl : $localLogoUrl;
            $logoFallback = $remoteLogoUrl !== '' && $localLogoUrl !== ''
                ? ' data-fallback-src="' . self::escapeHtmlAttribute($localLogoUrl) . '"'
                : '';
            $htmloutput .= '<tr class="' . ($zoneSeparator ? 'jbuli-zone-separator' : '') . '" style="' . $trstyle . '">'
                . '<td style="' . $tdstyle . '"><b>' . ($displayPlace === '' ? '&nbsp;' : $displayPlace . '&nbsp;') . '</b></td>'
                . '<td class="jbuli-logo">' . ($logoSource !== ''
                    ? '<img loading="lazy" decoding="async" title="' . self::escapeHtmlAttribute($displayName)
                        . '" alt="" src="' . self::escapeHtmlAttribute($logoSource) . '"' . $logoFallback . '>'
                    : '') . '</td>'
                . '<td class="jbuli-team" style="' . $tdstyle . '">' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td class="jbuli-played jbuli-responsive-column" style="' . $tdstyle . '">' . (int) $row['spiele'] . '</td>'
                . '<td class="jbuli-form jbuli-responsive-column" style="' . $tdstyle . '">' . (int) $row['gewonnen'] . '</td>'
                . '<td class="jbuli-form jbuli-responsive-column" style="' . $tdstyle . '">' . (int) $row['unentschieden'] . '</td>'
                . '<td class="jbuli-form jbuli-responsive-column" style="' . $tdstyle . '">' . (int) $row['verloren'] . '</td>'
                . '<td class="jbuli-goals jbuli-responsive-column" style="' . $tdstyle . '">' . (int) $row['tore'] . ':' . (int) $row['gegentore'] . '</td>'
                . '<td class="jbuli-diff jbuli-responsive-column" style="' . $tdstyle . '">' . $diff . '</td>'
                . '<td class="jbuli-points" style="' . $tdstyle . ';font-weight:900;color:#c40018;text-align:center;">' . (int) $row['punkte'] . '</td></tr>';

            $previousRankKey = $rankKey;
            $platz++;
        }

        $htmloutput .= '</tbody></table>';

        return $htmloutput;
    }

    private static function localLogoUrl(string $filename): string
    {
        $filename = trim($filename);
        if ($filename === '' || basename($filename) !== $filename) {
            return '';
        }

        return is_file(__DIR__ . '/images/' . $filename)
            ? JURI::root() . 'modules/mod_bulitabelle/images/' . rawurlencode($filename)
            : '';
    }

    private static function safeRemoteImageUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x20\x7f]/u', $url)) {
            return '';
        }
        $parts = parse_url($url);

        return is_array($parts)
            && strtolower((string) ($parts['scheme'] ?? '')) === 'https'
            && !empty($parts['host'])
            && empty($parts['user'])
            && empty($parts['pass'])
            ? $url
            : '';
    }

    private static function writeCacheAtomically(string $cachefile, string $content): bool
    {
        $temporaryFile = $cachefile . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (@file_put_contents($temporaryFile, $content, LOCK_EX) !== false) {
            if (@rename($temporaryFile, $cachefile)) {
                return true;
            }
            @unlink($temporaryFile);
        }

        return false;
    }

    private static function fetchCachedApiResponse(string $url, string $cachefile, int $timeout, int $cacheTtl): string|false
    {
        $readCache = static function () use ($cachefile): string|false {
            if (!is_readable($cachefile)) {
                return false;
            }
            $content = file_get_contents($cachefile);

            return is_string($content) && $content !== '' ? $content : false;
        };

        $cached = $readCache();
        if ($cached !== false && is_file($cachefile) && filemtime($cachefile) + $cacheTtl >= time()) {
            return $cached;
        }

        $lockHandle = @fopen($cachefile . '.lock', 'c');
        if (!is_resource($lockHandle) || !@flock($lockHandle, LOCK_EX)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }

            return $cached;
        }

        try {
            $lockedCache = $readCache();
            if ($lockedCache !== false && is_file($cachefile) && filemtime($cachefile) + $cacheTtl >= time()) {
                return $lockedCache;
            }
            if ($lockedCache !== false) {
                $cached = $lockedCache;
            }

            $response = self::fetchdata($url, $timeout);
            if (is_string($response) && $response !== '') {
                if (!self::writeCacheAtomically($cachefile, $response)) {
                    throw new RuntimeException('Der OpenLigaDB-Cache konnte nicht gespeichert werden.');
                }

                return $response;
            }

            return $cached;
        } finally {
            @flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private static function readRefreshState(string $stateFile): array
    {
        if (!is_readable($stateFile)) {
            return ['last_attempt' => 0, 'last_success' => 0];
        }
        $json = file_get_contents($stateFile);
        $state = is_string($json) ? json_decode($json, true) : null;

        return [
            'last_attempt' => is_array($state) ? max(0, (int) ($state['last_attempt'] ?? 0)) : 0,
            'last_success' => is_array($state) ? max(0, (int) ($state['last_success'] ?? 0)) : 0,
        ];
    }

    private static function writeRefreshState(string $stateFile, array $state): bool
    {
        $json = json_encode([
            'last_attempt' => max(0, (int) ($state['last_attempt'] ?? 0)),
            'last_success' => max(0, (int) ($state['last_success'] ?? 0)),
        ], JSON_UNESCAPED_SLASHES);

        return is_string($json) && self::writeCacheAtomically($stateFile, $json);
    }

    private static function refreshAttemptIsDue(array $table, array $state, int $refreshSeconds, int $now): bool
    {
        $lastAttempt = max(0, (int) ($state['last_attempt'] ?? 0));
        $lastSuccess = max(0, (int) ($state['last_success'] ?? 0));
        $retrySeconds = min(max(60, $refreshSeconds), 300);

        if ($table === [] && $lastSuccess >= $lastAttempt) {
            return true;
        }
        if ($lastAttempt > 0 && $lastAttempt + $retrySeconds > $now) {
            return false;
        }

        return $table === [] || $lastSuccess === 0 || $lastSuccess + max(60, $refreshSeconds) <= $now;
    }

    private static function escapeHtmlAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

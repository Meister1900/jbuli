<?php
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

        $app = JFactory::getApplication();
        $document = $app->getDocument();
        $activeMenu = $app->getMenu()->getActive();
        $itemId = $activeMenu ? (int) $activeMenu->id : 0;
        $document->addStyleDeclaration(
            '#bulitabelle_' . (int) $module->id . ' { width:100%; max-width:none; overflow-x:hidden; }'
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
        'http' => [ 'timeout' => $timeout ]
      ]);

            return file_get_contents($url, 0, $context);
        }

        return false;
    }

    private static function decodeApiResponse(string $json)
    {
        $value = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) { return null; }
        return self::normaliseApiKeys($value);
    }

    private static function normaliseApiKeys($value)
    {
        if (is_array($value)) { return array_map([self::class, 'normaliseApiKeys'], $value); }
        if (is_object($value)) {
            $normalised = new stdClass();
            foreach (get_object_vars($value) as $key => $item) { $normalised->{ucfirst($key)} = self::normaliseApiKeys($item); }
            return $normalised;
        }
        return $value;
    }

    /**
     * AJAX Endpoint
     */
    public static function getTabelleAjax()
    {
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

        $liga = $jparams->get('league');

        // Tabelle aus der Joomla Tabelle holen
        $query = 'SELECT '.$db->quoteName('team').', '.$db->quoteName('spiele').', '.$db->quoteName('gewonnen').', '.$db->quoteName('unentschieden').', '.$db->quoteName('verloren').', '.$db->quoteName('tore').', '.$db->quoteName('gegentore').', '.$db->quoteName('punkte') .' FROM '.$db->quoteName('#__bulitabelle') . ' WHERE modul_id = ' . (int) $module->id . ' ORDER BY punkte DESC, tore-gegentore DESC, tore DESC';

        $db->setQuery($query);
        $tabelle = $db->loadAssocList();

        // Aktuellen Spieltag ermitteln
        $spieltag = self::fetchdata('https://www.openligadb.de/api/getcurrentgroup/' . $liga, $jparams->get('timeout'));

        if ($spieltag === false) {
            // Kein Spieltag vom Webservice -> den vom letzten Mal nehmen
            if ($jparams->get('lastCurrentMatchday') != '') {
                $spieltag = $jparams->get('lastCurrentMatchday');
            } else {
                $spieltag = 1;
            }
        } else {
            $currentGroup = self::decodeApiResponse($spieltag);
            $spieltag = is_object($currentGroup) && isset($currentGroup->GroupOrderID)
                ? (int) $currentGroup->GroupOrderID
                : (int) $jparams->get('lastCurrentMatchday', 1);

            // Nicht mehr über die in Joomla 6 entfernte JTable-Modul-API speichern.
        }

        // Tabelle aktualisieren falls Refresh-Intervall erreicht
        if (count($tabelle) == 0 || $jparams->get('lastupdate') == '' || ($jparams->get('lastupdate') + ($jparams->get('refresh') * 60) < time())) {
            $paarungen = self::fetchdata('https://www.openligadb.de/api/getmatchdata/' . $liga . '/' . $jparams->get('season'), $jparams->get('timeout'));

            if ($paarungen != false && stristr($paarungen, 'Maximale Abfrageanzahl von 1000 Abfragen pro Tag erreicht!') == false && stristr($paarungen, 'An error has occurred') == false) {
                $paarungen = self::decodeApiResponse($paarungen);
                if (!is_array($paarungen)) {
                    $paarungen = [];
                }
                $tabelle = [];
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

                        if (!isset($tabelle[$partie->Team1->TeamName])) {
                            $tabelle[$partie->Team1->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        }
                        if (!isset($tabelle[$partie->Team2->TeamName])) {
                            $tabelle[$partie->Team2->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
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

                        $tabelle[$partie->Team1->TeamName] = ['spiele' => $tabelle[$partie->Team1->TeamName]['spiele'] + 1,
              'gewonnen' => $tabelle[$partie->Team1->TeamName]['gewonnen'] + $sieg1,
              'unentschieden' => $tabelle[$partie->Team1->TeamName]['unentschieden'] + $remis1,
              'verloren' => $tabelle[$partie->Team1->TeamName]['verloren'] + $niederlage1,
              'punkte' => $tabelle[$partie->Team1->TeamName]['punkte'] + $punkte_team1,
              'tore' => $tabelle[$partie->Team1->TeamName]['tore'] + $tore_team1,
              'gegentore' => $tabelle[$partie->Team1->TeamName]['gegentore'] + $tore_team2];
                        $tabelle[$partie->Team2->TeamName] = ['spiele' => $tabelle[$partie->Team2->TeamName]['spiele'] + 1,
              'gewonnen' => $tabelle[$partie->Team2->TeamName]['gewonnen'] + $sieg2,
              'unentschieden' => $tabelle[$partie->Team2->TeamName]['unentschieden'] + $remis2,
              'verloren' => $tabelle[$partie->Team2->TeamName]['verloren'] + $niederlage2,
              'punkte' => $tabelle[$partie->Team2->TeamName]['punkte'] + $punkte_team2,
              'tore' => $tabelle[$partie->Team2->TeamName]['tore'] + $tore_team2,
              'gegentore' => $tabelle[$partie->Team2->TeamName]['gegentore'] + $tore_team1];
                    } elseif ($i<10) {
                        $tabelle[$partie->Team1->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        $tabelle[$partie->Team2->TeamName] = ['spiele' => 0, 'gewonnen' => 0, 'unentschieden' => 0, 'verloren' => 0, 'punkte' => 0, 'tore' => 0, 'gegentore' => 0];
                        if ($i == 9) {
                            break;
                        }
                    }
                }

                if ($module->id) {
                    $sql = 'DELETE FROM '.$db->quoteName('#__bulitabelle') . ' WHERE modul_id = ' . $module->id;
                    $db->setQuery($sql);
                    $db->execute();
                }

                foreach ($tabelle as $name=>$team) {
                    if ($name == 'SV Sandhausen' && $jparams->get('season') == '2015') {
                        $team['punkte'] -= 3;
                    }
                    $sql = 'REPLACE INTO ' . $db->quoteName('#__bulitabelle')
                        . ' (' . implode(',', $db->quoteName(['team', 'spiele', 'gewonnen', 'unentschieden', 'verloren', 'tore', 'gegentore', 'punkte', 'modul_id'])) . ')'
                        . ' VALUES (' . implode(',', [$db->quote($name), (int) $team['spiele'], (int) $team['gewonnen'], (int) $team['unentschieden'], (int) $team['verloren'], (int) $team['tore'], (int) $team['gegentore'], (int) $team['punkte'], (int) $module->id]) . ')';
                    $db->setQuery($sql)->execute();
                }

                $db->setQuery($query);
                $tabelle = $db->loadAssocList();
            }
        }

        // Live Spiele laden
        $liveteams = [];
        if ($jparams->get('live') == '1') {

      // Paarungen abrufen
            $saison = $jparams->get('season');

            // Cache lesen
            $cache = '';
            $cachefile = JPATH_BASE."/modules/mod_bulitabelle/cache.txt";
            if (is_readable($cachefile)) {
                $cache = file_get_contents($cachefile);
            } else {
                $cache = self::fetchdata('https://www.jbuli.de/modules/mod_bulitabelle/cache.txt', $jparams->get('timeout'));

                if ($cache != false) {
                    self::writeCacheAtomically($cachefile, $cache);
                }
            }
            $paarungen_cache = [];
            if (is_string($cache) && $cache !== '') {
                $decodedCache = @unserialize($cache, ['allowed_classes' => [stdClass::class]]);
                if (is_array($decodedCache)) {
                    $paarungen_cache = $decodedCache;
                }
            }
            $cacheKey = $spieltag . $liga . $saison;
            $paarungen = [];

            // Letzte Änderung ermitteln
            $lastchange = self::fetchdata('https://www.openligadb.de/api/getlastchangedate/' . $liga . '/' . $saison . '/' . $spieltag, $jparams->get('timeout'));

            if ($lastchange === false) {
                // Kein Datum vom Webservice -> Datum aus dem Cache holen
                if (!empty($paarungen_cache[$cacheKey]) && is_array($paarungen_cache[$cacheKey])) {
                    $lastchange = array_key_first($paarungen_cache[$cacheKey]);
                } else {
                    $lastchange = 0;
                }
            } else {
                $decodedLastChange = json_decode($lastchange);
                $lastchange = is_string($decodedLastChange) ? (strtotime($decodedLastChange) ?: 0) : 0;
            }

            // Spieltag mit diesem Stand schon im Cache?
            if (isset($paarungen_cache[$cacheKey][$lastchange])) {
                $paarungen = $paarungen_cache[$cacheKey][$lastchange];
            } else {
                // Daten abrufen und in den Cache schreiben
                $paarungen = self::fetchdata('https://www.openligadb.de/api/getmatchdata/' . $liga . '/' . $saison . '/' . $spieltag, $jparams->get('timeout'));

                if ($paarungen != false && stristr($paarungen, 'Maximale Abfrageanzahl von 1000 Abfragen pro Tag erreicht!') == false) {
                    $decodedResponse = self::decodeApiResponse($paarungen);
                    $paarungen = is_array($decodedResponse) ? $decodedResponse : [];
                    if ($paarungen !== []) {
                        unset($paarungen_cache[$cacheKey]);
                        $paarungen_cache[$cacheKey][$lastchange] = $paarungen;
                        self::writeCacheAtomically($cachefile, serialize($paarungen_cache));
                    }
                }
            }

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
            . '<th style="'.$style.'">Pl.</th><th aria-label="Logo"></th><th style="text-align:left;">Team</th>'
            . '<th class="jbuli-played jbuli-responsive-column" style="'.$style.'">Sp.</th>'
            . '<th class="jbuli-form jbuli-responsive-column" style="'.$style.'">G</th>'
            . '<th class="jbuli-form jbuli-responsive-column" style="'.$style.'">U</th>'
            . '<th class="jbuli-form jbuli-responsive-column" style="'.$style.'">V</th>'
            . '<th class="jbuli-goals jbuli-responsive-column" style="'.$style.'">Tore</th>'
            . '<th class="jbuli-diff jbuli-responsive-column" style="'.$style.'">Diff.</th>'
            . '<th style="'.$style.'">Pkt</th></tr></thead><tbody>';

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
                $trstyle = $jparams->get('meinVereinCSS');
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
            $htmloutput .= '<tr class="' . ($zoneSeparator ? 'jbuli-zone-separator' : '') . '" style="' . $trstyle . '">'
                . '<td style="'.$tdstyle.'"><b>' . ($displayPlace === '' ? '&nbsp;' : $displayPlace . '&nbsp;') . '</b></td>'
                . '<td class="jbuli-logo"><img loading="lazy" title="'.htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8').'" alt="" src="'.JURI::root().'modules/mod_bulitabelle/images/' . rawurlencode($logo) . '"></td>'
                . '<td class="jbuli-team" style="'.$tdstyle.'">' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td class="jbuli-played jbuli-responsive-column" style="'.$tdstyle.'">' . (int) $row['spiele'] . '</td>'
                . '<td class="jbuli-form jbuli-responsive-column" style="'.$tdstyle.'">' . (int) $row['gewonnen'] . '</td>'
                . '<td class="jbuli-form jbuli-responsive-column" style="'.$tdstyle.'">' . (int) $row['unentschieden'] . '</td>'
                . '<td class="jbuli-form jbuli-responsive-column" style="'.$tdstyle.'">' . (int) $row['verloren'] . '</td>'
                . '<td class="jbuli-goals jbuli-responsive-column" style="'.$tdstyle.'">' . (int) $row['tore'] . ':' . (int) $row['gegentore'] . '</td>'
                . '<td class="jbuli-diff jbuli-responsive-column" style="'.$tdstyle.'">' . $diff . '</td>'
                . '<td class="jbuli-points" style="'.$tdstyle.';font-weight:900;color:#c40018;text-align:center;">' . (int) $row['punkte'] . '</td></tr>';

            $previousRankKey = $rankKey;
            $platz++;
        }

        $htmloutput .= '</tbody></table>';

        return $htmloutput;
    }

    private static function writeCacheAtomically(string $cachefile, string $content): void
    {
        $temporaryFile = $cachefile . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (@file_put_contents($temporaryFile, $content, LOCK_EX) !== false) {
            if (!@rename($temporaryFile, $cachefile)) {
                @unlink($temporaryFile);
            }
        }
    }
}

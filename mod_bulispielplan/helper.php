<?php
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Uri\Uri as JURI;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
use Joomla\Database\DatabaseInterface;

class modBulispielplanHelper
{
    /**
     * Constructor
     */
    public function __construct($module, $params)
    {
        JHtml::_('jquery.framework');

        $app = JFactory::getApplication();
        $document = $app->getDocument();
        $activeMenu = $app->getMenu()->getActive();
        $itemId = $activeMenu ? (int) $activeMenu->id : 0;

        $style = '#bulispielplan_' . (int) $module->id . ' { width:100%; max-width:none; }
              #bulispielplan_' . (int) $module->id . ' table { width:100%; border-collapse:collapse; font-variant-numeric:tabular-nums; }
              #bulispielplan_' . (int) $module->id . ' select { width:100%; max-width:100%; padding:6px 8px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td { vertical-align:middle; padding:6px 7px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule tr { border-bottom:1px solid rgba(127,127,127,.25); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule tr:hover { background:rgba(127,127,127,.08); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(1) { width:2.2rem; text-align:right; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(2) { width:3.8rem; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(3) { width:32px; min-width:32px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(5) { width:2rem; text-align:center; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(6) { width:3.5rem; text-align:right; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' img { display:block; width:20px; height:20px; object-fit:contain; }';
        $document->addStyleDeclaration($style);

        $document->addScriptDeclaration('
      jQuery(document).ready(function() {
        change_verein_' . $module->id . '();
        jQuery(document).on("change", "#verein_' . $module->id . '", change_verein_' . $module->id . ');
      });
        
      function change_verein_' . $module->id . '() {
        jQuery("#bulispielplan_loading_' . $module->id . '").show();
        jQuery.post( "' . JURI::base() . 'index.php",
            {
              option: "com_ajax",
              module: "bulispielplan",
              Itemid: "' . $itemId . '",
              method: "getSpielplan",
              format: "json",
              module_id: "' . (int) $module->id . '",
              verein: jQuery("#verein_' . $module->id . '").val(),
            },
            function(data){
              jQuery("#bulispielplan_loading_' . $module->id . '").hide();
              if (data.success == false) {
                jQuery("#bulispielplan_' . $module->id . '").html(data.message);
              } else {
                jQuery("#bulispielplan_' . $module->id . '").html(data.data);
                var current = document.getElementById("' . $module->id . '_current");
                if (current) { current.scrollIntoView({block: "center"}); }
              }
            }
        ).fail(function(xhr) {
		  try {
			// Ungewollten Output von anderen Plugins wie GoogleAnalytics oder PHP Meldungen wegschneiden
			data = jQuery.parseJSON(xhr.responseText.substring(xhr.responseText.indexOf("success")-2));
		  }
		  catch (e) {
			alert("Fehlerhafter JSON Response - Doku pruefen!");
		  };
          jQuery("#bulispielplan_loading_' . $module->id . '").hide();
          if (data.success == false) {
            jQuery("#bulispielplan_' . $module->id . '").html(data.message);
          } else {
            jQuery("#bulispielplan_' . $module->id . '").html(data.data);
            var current = document.getElementById("' . $module->id . '_current");
            if (current) { current.scrollIntoView({block: "center"}); }
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
            curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla/6 mod_bulispielplan');
            $content = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);
            return $content !== false && $status >= 200 && $status < 300 ? $content : false;
        } elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
        'http' => [ 'timeout' => $timeout ]
      ]);

            return file_get_contents($url, 0, $context);
        } else {
            return false;
        }
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
    public static function getSpielplanAjax()
    {
        $jinput = JFactory::getApplication()->input;
        $db = JFactory::getContainer()->get(DatabaseInterface::class);
        $moduleId = $jinput->getInt('module_id');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'module', 'params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = ' . $moduleId)
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_bulispielplan'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1');
        $module = $db->setQuery($query)->loadObject();
        if (!$module) {
            throw new RuntimeException('Das Spielplan-Modul wurde nicht gefunden.');
        }
        $jparams = new JRegistry();
        $jparams->loadString((string) $module->params);
        $context = stream_context_create([
      'http' => [ 'timeout' => $jparams->get('timeout') ]
    ]);

        // Liga ermitteln
        $teamAliases = [
            'Werder Bremen' => 'SV Werder Bremen',
            'TSG 1899 Hoffenheim' => 'TSG Hoffenheim',
            'Bayer Leverkusen' => 'Bayer 04 Leverkusen',
            'Arminia Bielefeld' => 'DSC Arminia Bielefeld',
            'SG Dynamo Dresden' => 'Dynamo Dresden',
        ];
        $configuredTeam = (string) $jparams->get('meinVerein', '');
        $configuredTeam = $teamAliases[$configuredTeam] ?? $configuredTeam;
        $query = 'SELECT '.$db->quoteName('liga').' FROM '.$db->quoteName('#__bulispielplan') . ' WHERE bezeichnung_webservice = ' . $db->quote($configuredTeam);
        $db->setQuery($query);
        $liga = (string) $db->loadResult();
        if ($liga === '') {
            $liga = 'bl1';
        }

        // Teams aus der Joomla Tabelle holen
        $query = 'SELECT '.$db->quoteName('bezeichnung_webservice').', '.$db->quoteName('bezeichnung_kurz').', '.$db->quoteName('bezeichnung_mittel').', '.$db->quoteName('dateiname_logo').' FROM '.$db->quoteName('#__bulispielplan') . ' WHERE liga = ' . $db->quote($liga) . ' ORDER BY bezeichnung_mittel';
        $db->setQuery($query);
        $teams = $db->loadAssocList('bezeichnung_webservice');

        // Für historische Spielpläne alle bekannten Vereine als Logo-Mapping laden.
        // Die Ligazugehörigkeit in der Stammtabelle bildet nur die aktuelle Saison ab.
        $query = 'SELECT '.$db->quoteName('bezeichnung_webservice').', '.$db->quoteName('bezeichnung_kurz').', '.$db->quoteName('bezeichnung_mittel').', '.$db->quoteName('dateiname_logo')
            .' FROM '.$db->quoteName('#__bulispielplan');
        $db->setQuery($query);
        $allTeams = $db->loadAssocList('bezeichnung_webservice');
        foreach ($allTeams as &$teamData) {
            $teamData['logo_module'] = 'mod_bulispielplan';
        }
        unset($teamData);

        // Wenn das Ergebnis-Modul installiert ist, dessen bestätigten vollständigen
        // Logo-Bestand bevorzugen. Der Spielplan bleibt ohne dieses Modul lauffähig.
        $resultTable = $db->replacePrefix('#__buliergebnisse');
        if (in_array($resultTable, $db->getTableList(), true)) {
            $query = 'SELECT '.$db->quoteName('bezeichnung_webservice').', '.$db->quoteName('bezeichnung_kurz').', '.$db->quoteName('bezeichnung_mittel').', '.$db->quoteName('dateiname_logo')
                .' FROM '.$db->quoteName('#__buliergebnisse');
            $resultTeams = $db->setQuery($query)->loadAssocList('bezeichnung_webservice');
            foreach ($resultTeams as $name => $teamData) {
                $teamData['logo_module'] = 'mod_buliergebnisse';
                $allTeams[$name] = $teamData;
            }
        }

        // Start HTML OUTPUT
        $table = "\r\n<table class='jbuli-team-selector'>\r\n";

        // Verein Dropdown
        $table .= "<tr><td align='left' valign='middle'><nobr><select id='verein_" . $module->id . "'>";
        $verein = '';
        $requestedTeam = trim($jinput->getString('verein', ''));
        $requestedTeam = $teamAliases[$requestedTeam] ?? $requestedTeam;

        foreach ($teams as $team) {
            $teamName = (string) $team['bezeichnung_webservice'];
            if (isset($teamAliases[$teamName])) {
                continue;
            }
            $selected = ($requestedTeam !== '' && $teamName === $requestedTeam)
                || ($requestedTeam === '' && $teamName === $configuredTeam);
            if ($selected) {
                $verein = $teamName;
            }
            $table .= '<option value="' . htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8') . '"'
                . ($selected ? ' selected="selected"' : '') . '>'
                . htmlspecialchars((string) $team['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        if ($verein === '' && !empty($teams)) {
            $firstTeam = reset($teams);
            $verein = $firstTeam['bezeichnung_webservice'];
        }

        $table .= "</select>&nbsp;&nbsp;&nbsp;<img id='bulispielplan_loading_" . $module->id . "' src='".JURI::root()."modules/mod_bulispielplan/images/ajax-loader.gif' style='display:none;'></nobr></td></tr></table>";
        $table .= "<div class='jbuli-schedule-scroll' style='height:" . (int) $jparams->get('hoehe', 400) . "px; width:100%; overflow-y:auto; overflow-x:hidden; margin-top:1rem;'>";
        $table .= "<table class='jbuli-schedule'>\r\n";

        $ligen = [$liga, 'dfb' . $jparams->get('season')];
        $partien = [];

        foreach ($ligen as $competitionIndex => $liga) {
            $cache = '';
            $paarungen = [];
            $cachefile = JPATH_CACHE . '/mod_bulispielplan_' . preg_replace('/[^a-z0-9_-]/i', '', $liga) . '_' . (int) $jparams->get('season') . '.json';
            if (is_readable($cachefile)) {
                $cache = file_get_contents($cachefile);
                $paarungen = self::decodeApiResponse($cache) ?: [];
            }

            // Daten neu holen wenn Refresh-Intervall erreicht
            $cacheExpired = !is_file($cachefile)
                || filemtime($cachefile) + ((int) $jparams->get('refresh', 60) * 60) < time();
            if ($cache === '' || $cacheExpired) {
                $paarungenjson = self::fetchdata('https://www.openligadb.de/api/getmatchdata/' . $liga . '/' . $jparams->get('season'), $jparams->get('timeout'));

                if ($paarungenjson != false && stristr($paarungenjson, 'Maximale Abfrageanzahl von 1000 Abfragen pro Tag erreicht!') == false && stristr($paarungenjson, 'An error has occurred') == false) {
                    $paarungen = self::decodeApiResponse($paarungenjson) ?: [];
                    file_put_contents($cachefile, $paarungenjson, LOCK_EX);

                } else {
                    if ($cache == '') {
                        // Zusatzwettbewerbe sind optional; die Liga trotzdem anzeigen.
                        if ($competitionIndex === 0) {
                            throw new RuntimeException((string) $jparams->get('timeout_error'));
                        }
                        continue;
                    } else {
                        $paarungen = self::decodeApiResponse($cache) ?: [];
                    }
                }
            }

            foreach ((array) $paarungen as $partie) {
                $partie->wettbewerb = $liga;
            }

            $partien = array_merge($partien, (array) $paarungen);
        }

        usort($partien, function ($a, $b) {
            return strcmp($a->MatchDateTime, $b->MatchDateTime);
        });

        $anzahl_partien = 0;
        foreach ($partien as $partie) {
            if ($partie->Team1->TeamName == $verein || $partie->Team2->TeamName == $verein) {
                $anzahl_partien++;
            }
        }

        // Output Spielplan
        $i = 0;
        $c = 0;
        $id = '';
        $hat_ergebnisse = false;
        foreach ($partien as $partie) {
            if ($partie->Team1->TeamName == $verein || $partie->Team2->TeamName == $verein) {
                $c++;
                $tootip_text = '';
                $goals = '';
                $ergebnisse = '<td>';
                $alle_ergebnisse = $partie->MatchResults;

                if (! is_array($alle_ergebnisse) || count($alle_ergebnisse) == 0) {
                    $tootip_text .= '&nbsp;-:-';
                    if ($id != 'current' && $hat_ergebnisse) {
                        $id = 'current';
                    } else {
                        $id = '';
                    }

                    $hat_ergebnisse = false;
                } else {
                    if (!$partie->MatchIsFinished && $alle_ergebnisse[0] instanceof stdClass) {
                        $tootip_text .= '<font color="red">';
                    }

                    $ergebnisse .= '<nobr>&nbsp;';
                    $id = '';
                    $hat_ergebnisse = true;

                    // Endergebnis ermitteln
                    foreach ($alle_ergebnisse as $ergebnis) {
                        if ($ergebnis->ResultName == 'Endergebnis') {
                            if ($partie->Team1->TeamName == $verein) {
                                $tootip_text .= $ergebnis->PointsTeam1.":".$ergebnis->PointsTeam2;
                            } else {
                                $tootip_text .= $ergebnis->PointsTeam2.":".$ergebnis->PointsTeam1;
                            }

                            break;
                        }
                    }

                    foreach ($partie->Goals as $goal) {
                        if ($goal->GoalGetterName) {
                            if ($goal->MatchMinute) {
                                if ($partie->Team1->TeamName == $verein) {
                                    $goals .= '<b>' . $goal->ScoreTeam1 . ':' . $goal->ScoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . ' (' . $goal->MatchMinute . '.)<br>';
                                } else {
                                    $goals .= '<b>' . $goal->ScoreTeam2 . ':' . $goal->ScoreTeam1 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . ' (' . $goal->MatchMinute . '.)<br>';
                                }
                            } else {
                                if ($partie->Team1->TeamName == $verein) {
                                    $goals .= '<b>' . $goal->ScoreTeam1 . ':' . $goal->ScoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . '<br>';
                                } else {
                                    $goals .= '<b>' . $goal->ScoreTeam2 . ':' . $goal->ScoreTeam1 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . '<br>';
                                }
                            }
                        }
                    }
                }

                if (isset($partie->MatchIsFinished)) {
                    if (!$partie->MatchIsFinished && isset($alle_ergebnisse[0]) && $alle_ergebnisse[0] instanceof stdClass) {
                        $tootip_text .= "</font>";
                    }
                }

                $tootip_text .= "</nobr>";

                if ($goals <> '') {
                    $goalTitle = htmlspecialchars(trim(strip_tags(str_replace('<br>', "\n", $goals))), ENT_QUOTES, 'UTF-8');
                    $ergebnisse .= '<span title="' . $goalTitle . '">' . $tootip_text . '</span>';
                } else {
                    $ergebnisse .= $tootip_text;
                }

                $ergebnisse .= "</td>\r\n";
            }

            $tage = ["So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa."];

            if ($partie->Team1->TeamName == $verein || $partie->Team2->TeamName == $verein) {
                if ($partie->wettbewerb == $ligen[0]) {
                    $anzeigename = 'Bundesliga';
                    $i++;
                    $kurz = $i;
                } elseif ($partie->wettbewerb == $ligen[1]) {
                    $kurz = 'PK';
                    $anzeigename = 'DFB Pokal';
                    $bildSrc = JURI::root().'modules/mod_bulispielplan/images/pokal.png';
                } elseif ($partie->wettbewerb == $ligen[2]) {
                    $kurz = 'CL';
                    $anzeigename = 'Champions League';
                    $bildSrc = JURI::root().'modules/mod_bulispielplan/images/cl.png';
                }

                if ($partie->Team1->TeamName == $verein) {
                    $wo = 'H';
                    $opponent = $partie->Team2;
                    $anzeige = $opponent->TeamName;
                    if ($partie->wettbewerb == $ligen[0]) {
                        $teamData = $allTeams[$opponent->TeamName] ?? null;
                        $anzeige = $teamData['bezeichnung_mittel'] ?? $opponent->TeamName;
                        $bildSrc = $teamData
                            ? JURI::root().'modules/' . $teamData['logo_module'] . '/images/' . rawurlencode($teamData['dateiname_logo'])
                            : (string) ($opponent->TeamIconUrl ?? '');
                    }
                } else {
                    $wo = 'A';
                    $opponent = $partie->Team1;
                    $anzeige = $opponent->TeamName;
                    if ($partie->wettbewerb == $ligen[0]) {
                        $teamData = $allTeams[$opponent->TeamName] ?? null;
                        $anzeige = $teamData['bezeichnung_mittel'] ?? $opponent->TeamName;
                        $bildSrc = $teamData
                            ? JURI::root().'modules/' . $teamData['logo_module'] . '/images/' . rawurlencode($teamData['dateiname_logo'])
                            : (string) ($opponent->TeamIconUrl ?? '');
                    }
                }

                // Workaround wenn die Saison vorbei ist das letzte Spiel als current setzen
                if ($id != 'current' && $c == $anzahl_partien && $hat_ergebnisse) {
                    $id = 'current';
                }
                if ($anzeigename != 'Bundesliga') {
                    $tooltip = $anzeige;
                } else {
                    $tooltip = '';
                }

                $dateTitle = $tage[date("w", strtotime($partie->MatchDateTime))] . ' '
                    . date('d.m.Y H:i', strtotime($partie->MatchDateTime)) . ' Uhr'
                    . (isset($partie->Location) && is_object($partie->Location) && !empty($partie->Location->LocationStadium) ? ' – ' . $partie->Location->LocationStadium : '');
                $table .= '<tr id="' . $module->id . '_' . $id . '"><td style="text-align:right; padding-right: 5px;">' . $kurz . '</td>
        <td title="' . htmlspecialchars($dateTitle, ENT_QUOTES, 'UTF-8') . '">' . date('d.m.', strtotime($partie->MatchDateTime)) . '</td>
        <td><img style="width:20px; height:20px; object-fit:contain;" title="' . htmlspecialchars($anzeige, ENT_QUOTES, 'UTF-8') . '" alt="" src="' . htmlspecialchars($bildSrc, ENT_QUOTES, 'UTF-8') . '"></td>
        <td style="width:100%;"><div title="' . $tooltip . '" style="cursor:default; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:100%;">' . $anzeige . '</div></td>
        <td>' . $wo . '</td><td>' . $ergebnisse . '</tr>';
            }
        }

        $table .= "</table></div>";

        return $table;
    }
}

<?php
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Uri\Uri as JURI;
use Joomla\Database\DatabaseInterface;
/**
 * helper.php - (c) Markus Krupp
 * Die Daten werden vom Webservice openligadb bereitgestellt
 */

class modBuliergebnisseHelper
{
    /**
     * Constructor
     */
    public function __construct($module)
    {
        // Load Bootstrap and JQuery
        JHtml::_('bootstrap.framework');

        $app = JFactory::getApplication();
        $document = $app->getDocument();
        $activeMenu = $app->getMenu()->getActive();
        $itemId = $activeMenu ? (int) $activeMenu->id : 0;
        $document->addStyleDeclaration(
            '#spielplan_' . (int) $module->id . ' { width:100%; max-width:none; container-type:inline-size; }'
            . '#spielplan_' . (int) $module->id . ' table { width:100%; border-collapse:collapse; }'
            . '#spielplan_' . (int) $module->id . ' td { vertical-align:middle; padding:4px 5px; }'
            . '#spielplan_' . (int) $module->id . ' select { width:auto !important; min-width:58px; max-width:100%; min-height:36px; padding:5px 30px 5px 9px; cursor:pointer; appearance:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\'%3E%3Cpath d=\'M1 1.5 6 6.5 11 1.5\' fill=\'none\' stroke=\'%23555\' stroke-width=\'1.6\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; background-size:12px 8px; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-match { border-bottom:1px solid rgba(127,127,127,.22); transition:background-color .15s ease; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-match:hover { background:rgba(127,127,127,.10); }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-logo { width:28px; min-width:28px; padding-left:2px; padding-right:6px; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-logo img { display:block; width:20px; height:20px; object-fit:contain; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-team { width:42%; overflow-wrap:anywhere; hyphens:auto; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-separator { width:14px; min-width:14px; padding-left:1px; padding-right:1px; text-align:center; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-result { width:58px; min-width:58px; text-align:right; white-space:nowrap; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-date { width:34px; min-width:34px; }'
            . '@container (max-width:400px) {'
            . '#spielplan_' . (int) $module->id . ' .jbuli-match td { font-size:.93rem; padding-left:3px; padding-right:3px; }'
            . '#spielplan_' . (int) $module->id . ' .jbuli-team { width:auto; }'
            . '}'
        );

        $document->addScriptDeclaration('
      jQuery(document).ready(function() {
        change_spieltag_' . $module->id . '();
        jQuery(document).on("change", "#spielplan_' . $module->id . '", change_spieltag_' . $module->id . ');
      });
        
      function change_spieltag_' . $module->id . '() {
        jQuery("#buliergebnisse_loading_' . $module->id . '").show();
        jQuery.post( "' . JURI::base() . 'index.php",
            {
              option: "com_ajax",
              module: "buliergebnisse",
              Itemid: "' . $itemId . '",
              method: "getErgebnisse",
              format: "json",
              module_id: "' . (int) $module->id . '",
              spieltag: jQuery("#spieltag_' . $module->id . ' option:selected").text(),
            },
            function(data){
              jQuery("#buliergebnisse_loading_' . $module->id . '").hide();
              if (data.success == false) {
                jQuery("#spielplan_' . $module->id . '").html(data.message);
              } else {
                jQuery("#spielplan_' . $module->id . '").html(data.data);
                jQuery(".hasTooltip").tooltip({html: "true"});
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
	      jQuery("#buliergebnisse_loading_' . $module->id . '").hide();
          if (data.success == false) {
            jQuery("#spielplan_' . $module->id . '").html(data.message);
          } else {
            jQuery("#spielplan_' . $module->id . '").html(data.data);
            jQuery(".hasTooltip").tooltip({html: "true"});
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
            curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla/6 mod_buliergebnisse');
            $content = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
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
    public static function getErgebnisseAjax()
    {
        $jinput = JFactory::getApplication()->input;
        $db = JFactory::getContainer()->get(DatabaseInterface::class);
        $moduleId = $jinput->getInt('module_id');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'module', 'params']))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = ' . $moduleId)
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_buliergebnisse'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1');
        $module = $db->setQuery($query)->loadObject();
        if (!$module) {
            throw new RuntimeException('Das Ergebnis-Modul wurde nicht gefunden.');
        }

        $jparams = new JRegistry();
        $jparams->loadString($module->params);

        $tage = ['So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.'];
        $clrunden = ['', 'Gruppe A', 'Gruppe B', 'Gruppe C', 'Gruppe D', 'Gruppe E', 'Gruppe F', 'Gruppe G', 'Gruppe H', 'Achtelfinale', 'Viertelfinale', 'Halbfinale', 'Finale'];

        // Teams aus der Joomla Tabelle holen
        $query = 'SELECT '.$db->quoteName('bezeichnung_webservice').', '.$db->quoteName('bezeichnung_kurz').', '.$db->quoteName('bezeichnung_mittel').', '.$db->quoteName('dateiname_logo').' FROM '.$db->quoteName('#__buliergebnisse');
        $db->setQuery($query);
        $teams = $db->loadAssocList('bezeichnung_webservice');
        $liga = $jparams->get('league');
        $saison = (int) $jparams->get('season');
        $currentSeasonStart = (int) date('n') >= 7 ? (int) date('Y') : (int) date('Y') - 1;

        // Spieltag ermitteln
        if ($jinput->get('spieltag', 'default_value', 'filter') != '') {
            $spieltag = $jinput->get('spieltag', 'default_value', 'filter');
            if (! is_numeric($spieltag)) {
                $spieltag = array_search($spieltag, $clrunden);
            }
        } elseif ($jparams->get('matchday') != 0 && $jparams->get('matchday') != -1) {
            $spieltag = $jparams->get('matchday');
        } elseif ($saison < $currentSeasonStart) {
            if ($liga == 'pl' || $liga == 'sa' || $liga == 'pd') {
                $spieltag = 38;
            } elseif ($liga == 'cl1617') {
                $spieltag = 12;
            } else {
                $spieltag = 34;
            }
        } else {
            $spieltag = self::fetchdata('https://www.openligadb.de/api/getcurrentgroup/' .$liga, $jparams->get('timeout'));

            if ($spieltag === false) {
                // Kein Spieltag vom Webservice -> den vom letzten Mal nehmen
                if ($jparams->get('lastCurrentMatchday') != '') {
                    $spieltag = $jparams->get('lastCurrentMatchday');
                } else {
                    $spieltag = 1;
                }
            } else {
                $currentGroup = self::decodeApiResponse($spieltag);
                if (is_object($currentGroup) && isset($currentGroup->GroupOrderID)) {
                    $spieltagsname = (string) ($currentGroup->GroupName ?? '');
                    $spieltag = (int) $currentGroup->GroupOrderID;
                } else {
                    $spieltagsname = '';
                    $spieltag = (int) $jparams->get('lastCurrentMatchday', 1);
                }

                if ($liga == 'cl1617') {
                    $spieltag = array_search($spieltagsname, $clrunden);
                    if (! $spieltag || $spieltag < 9) {
                        $spieltag = 1;
                    }
                }

                // Der aktuelle Spieltag gilt für diese Anfrage. Die alte JTable-API
                // zum Schreiben von Modulparametern existiert in Joomla 6 nicht mehr.
            }
        }

        // Wenn -1 eingestellt dann vorherigen Spieltag anzeigen
        if ($jparams->get('matchday') == '-1' && $spieltag > 1) {
            $spieltag -= 1;
        }

        // Cache lesen
        $cache = '';
        $cachefile = JPATH_BASE."/modules/mod_buliergebnisse/cache.txt";
        if (is_readable($cachefile)) {
            $cache = file_get_contents($cachefile);
        } else {
            $cache = self::fetchdata('https://www.jbuli.de/modules/mod_buliergebnisse/cache.txt', 10);

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

            if ($paarungen === false || stristr($paarungen, 'Maximale Abfrageanzahl von 1000 Abfragen pro Tag erreicht!') != false) {
                // Webservice nicht erreichbar, prüfen ob Spieltag mit älterem Stand im Cache ist
                if (!empty($paarungen_cache[$cacheKey]) && is_array($paarungen_cache[$cacheKey])) {
                    $paarungen = reset($paarungen_cache[$cacheKey]);
                }
            } else {
                $decodedResponse = self::decodeApiResponse($paarungen);
                $paarungen = is_array($decodedResponse) ? $decodedResponse : [];
                if ($paarungen !== []) {
                    unset($paarungen_cache[$cacheKey]);
                    $paarungen_cache[$cacheKey][$lastchange] = $paarungen;
                    self::writeCacheAtomically($cachefile, serialize($paarungen_cache));
                }
            }
        }

        // Prüfen wie viele Ergebnisse zu diesem Spieltag vorliegen
        $anzahl_ergebnisse = 0;
        $anzahl_live = 0;
        $nameFormat = (string) $jparams->get('nameformat', '');
        if ($nameFormat === '') {
            $nameFormat = $jparams->get('longnames') == '1' ? 'long' : ($jparams->get('kompakt') == '1' ? 'short' : 'medium');
        }
        $bezeichnung = ['long' => 'bezeichnung_webservice', 'medium' => 'bezeichnung_mittel', 'short' => 'bezeichnung_kurz'][$nameFormat] ?? 'bezeichnung_mittel';
        $compactView = $nameFormat === 'short';
        if (is_array($paarungen)) {
            foreach ($paarungen as $partie) {
                if (!is_object($partie) || !isset($partie->Team1->TeamName, $partie->Team2->TeamName, $partie->MatchDateTime)) {
                    continue;
                }
                if (isset($partie->MatchResults[0])) {
                    $ergebnisse = $partie->MatchResults[0];
                    if ($ergebnisse instanceof stdClass) {
                        $anzahl_ergebnisse++;
                        if (!($partie->MatchIsFinished ?? true)) {
                            $anzahl_live++;
                        }
                    }
                }
            }
        }

        // Start HTML OUTPUT
        $table = "<table class='jbuli-results'>\r\n";

        // Spieltag Dropdown
        $table .= "<tr>\r\n<td align='left' valign='middle' colspan='8' style='padding-bottom:10px;'><nobr>Spieltag:&nbsp;<select id='spieltag_" . $module->id . "'>";

        if ($liga == 'pl' || $liga == 'sa' || $liga == 'pd') {
            $spieltage = 38;
        } elseif ($liga == 'cl1617') {
            $spieltage = 12;
        } else {
            $spieltage = 34;
        }

        for ($i=1;$i<=$spieltage;$i++) {
            if ($liga == 'cl1617') {
                $anzeige = $clrunden[$i];
            } else {
                $anzeige = $i;
            }

            if ($i == $spieltag && $i == $jparams->get('lastCurrentMatchday')) {
                $table .= "<option value='$i' style='font-weight:bold;' selected='selected'>$anzeige</option>";
            } elseif ($i == $spieltag) {
                $table .= "<option value='$i' selected='selected'>$anzeige</option>";
            } elseif ($i == $jparams->get('lastCurrentMatchday')) {
                $table .= "<option value='$i' style='font-weight:bold;'>$anzeige</option>";
            } else {
                $table .= "<option value='$i'>$anzeige</option>";
            }
        }

        $table .= "</select>&nbsp;&nbsp;&nbsp;<img id='buliergebnisse_loading_" . $module->id . "' src='".JURI::root()."modules/mod_buliergebnisse/images/ajax-loader.gif' style='display:none;'>";
        $table .= "</nobr></td>\r\n</tr>\r\n";

        // Live Spiele anzeigen
        if ($anzahl_ergebnisse > 0 && $anzahl_live == 1) {
            $table .= "<tr>\r\n<td align='center' valign='middle' colspan='7'><font color='red'><b>JETZT EIN SPIEL LIVE!</b></font></td>\r\n</tr>\r\n";
        } elseif ($anzahl_ergebnisse > 0 && $anzahl_live > 1) {
            $table .= "<tr>\r\n<td align='center' valign='middle' colspan='7'><font color='red'><b>JETZT ".$anzahl_live." SPIELE LIVE!</b></font></td>\r\n</tr>\r\n";
        }

        if (!$paarungen) {
            $table .= '</table>' . $jparams->get('timeout_error');

            return $table;
        }

        $i = 0;
        $termin = '';
        foreach ($paarungen as $partie) {
            if (!is_object($partie) || !isset($partie->Team1->TeamName, $partie->Team2->TeamName, $partie->MatchDateTime)) {
                continue;
            }
            $i++;

            if (trim($partie->Team1->TeamName) == $jparams->get('meinVerein') ||  trim($partie->Team2->TeamName) == $jparams->get('meinVerein')) {
                $style = $jparams->get('meinVereinCSS');
            } else {
                $style = '';
            }

            if ($termin != $partie->MatchDateTime && !$compactView) {
                if ($i==1) {
                    $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7'><b><i>".$tage[date("w", strtotime($partie->MatchDateTime))]." ".date("d.m. H:i", strtotime($partie->MatchDateTime))." Uhr</i></b></td>\r\n</tr>\r\n";
                } else {
                    $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7' style='padding-top:10px;'><b><i>".$tage[date("w", strtotime($partie->MatchDateTime))]." ".date("d.m. H:i", strtotime($partie->MatchDateTime))." Uhr</i></b></td>\r\n</tr>\r\n";
                }
            }

            $table .= "<tr class='jbuli-match' style='$style'>\r\n";

            if ($compactView) {
                if ($liga == 'cl1617') {
                    $table .= "<td class='jbuli-date' align='left' valign='middle'>".date("d.m.", strtotime($partie->MatchDateTime))."</td>\r\n";
                } else {
                    $table .= "<td class='jbuli-date' align='left' valign='middle'>".$tage[date("w", strtotime($partie->MatchDateTime))]."</td>\r\n";
                }
            }

            $termin = $partie->MatchDateTime;

            $team1Name = trim((string) ($partie->Team1->TeamName ?? ''));
            $team2Name = trim((string) ($partie->Team2->TeamName ?? ''));
            $team1 = $teams[$team1Name] ?? ['bezeichnung_mittel' => $team1Name, $bezeichnung => $team1Name, 'dateiname_logo' => ''];
            $team2 = $teams[$team2Name] ?? ['bezeichnung_mittel' => $team2Name, $bezeichnung => $team2Name, 'dateiname_logo' => ''];

            // Team 1
            $table .= "<td class='jbuli-logo' align='left' valign='middle'><img title='".htmlspecialchars((string) $team1['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8')."' alt='' src='".JURI::root()."modules/mod_buliergebnisse/images/".rawurlencode((string) $team1['dateiname_logo'])."' /></td>\r\n";
            $table .= "<td class='jbuli-team' align='left' valign='middle'>".htmlspecialchars((string) ($team1[$bezeichnung] ?? $team1Name), ENT_QUOTES, 'UTF-8')."</td>\r\n";

            $table .= "<td class='jbuli-separator' align='left' valign='middle'>-</td>\r\n";

            // Team 2
            $table .= "<td class='jbuli-logo' align='left' valign='middle'><img title='".htmlspecialchars((string) $team2['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8')."' alt='' src='".JURI::root()."modules/mod_buliergebnisse/images/".rawurlencode((string) $team2['dateiname_logo'])."' /></td>\r\n";
            $table .= "<td class='jbuli-team' align='left' valign='middle'>".htmlspecialchars((string) ($team2[$bezeichnung] ?? $team2Name), ENT_QUOTES, 'UTF-8')."</td>\r\n";

            $tootip_text = "";
            $endergebnis = "";
            $halbzeitergebnis = "";
            $goals = '';
            if ($anzahl_ergebnisse > 0) {
                $table .= "<td class='jbuli-result' align='left' valign='middle'>";
                $alle_ergebnisse = is_array($partie->MatchResults ?? null) ? $partie->MatchResults : [];
                if (isset($alle_ergebnisse[0])) {
                    if (!$partie->MatchIsFinished && $alle_ergebnisse[0] instanceof stdClass) {
                        $tootip_text .= "<font color=red>";
                    }
                }
                $table .= "<nobr>&nbsp;";

                if (! is_array($alle_ergebnisse) || count($alle_ergebnisse) == 0) {
                    $tootip_text .= '-:- (-:-)';
                } else {
                    // Halbzeitergebnis / Endergebnis ermitteln
                    foreach ($alle_ergebnisse as $ergebnis) {
                        if (($ergebnis->ResultName ?? '') == 'Endergebnis' && isset($ergebnis->PointsTeam1, $ergebnis->PointsTeam2)) {
                            $endergebnis = $ergebnis->PointsTeam1.":".$ergebnis->PointsTeam2;
                        } elseif (isset($ergebnis->PointsTeam1, $ergebnis->PointsTeam2) && (($ergebnis->ResultName ?? '') == 'Halbzeitergebnis' || ($ergebnis->ResultName ?? '') == 'Halbzeit')) {
                            $halbzeitergebnis = " (".$ergebnis->PointsTeam1.":".$ergebnis->PointsTeam2.")";
                        }
                    }
                    if ($endergebnis == '') {
                        $endergebnis = '0:0';
                    }
                    $tootip_text .= $endergebnis . $halbzeitergebnis;

                    foreach ((array) ($partie->Goals ?? []) as $goal) {
                        if (!empty($goal->GoalGetterName)) {
                            $scoreTeam1 = (int) ($goal->ScoreTeam1 ?? 0);
                            $scoreTeam2 = (int) ($goal->ScoreTeam2 ?? 0);
                            if (!empty($goal->MatchMinute)) {
                                $goals .= '<b>' . $scoreTeam1 . ':' . $scoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . ' (' . $goal->MatchMinute . '.)<br>';
                            } else {
                                $goals .= '<b>' . $scoreTeam1 . ':' . $scoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . '<br>';
                            }
                        }
                    }
                }
                if (isset($partie->MatchIsFinished, $alle_ergebnisse[0])) {
                    if (!($partie->MatchIsFinished ?? true) && $alle_ergebnisse[0] instanceof stdClass) {
                        $tootip_text .= "</font>";
                    }
                }

                $tootip_text .= "</nobr>";

                if ($goals <> '') {
                    $goalTitle = htmlspecialchars(trim(strip_tags(str_replace('<br>', "\n", $goals))), ENT_QUOTES, 'UTF-8');
                    $table .= '<span class="jbuli-result-tooltip" title="' . $goalTitle . '">' . $tootip_text . '</span>';
                } else {
                    $table .= $tootip_text;
                }

                $table .= "</td>\r\n";
            } else {
                $table .= "<td class='jbuli-result'>-:- (-:-)</td>\r\n";
            }
            $table .= "</tr>\r\n";
        }

        $table .= "</table>\r\n";

        return $table;
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

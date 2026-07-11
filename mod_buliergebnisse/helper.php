<?php
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Uri\Uri as JURI;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
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
        $document->addStyleDeclaration(
            '#spielplan_' . (int) $module->id . ' { width:100%; max-width:none; }'
            . '#spielplan_' . (int) $module->id . ' table { width:100%; border-collapse:collapse; }'
            . '#spielplan_' . (int) $module->id . ' td { vertical-align:middle; }'
            . '#spielplan_' . (int) $module->id . ' img { object-fit:contain; }'
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
              Itemid: "' . $app->getMenu()->getActive()->id . '",
              method: "getErgebnisse",
              format: "json",
              titel: "' . $module->title . '",
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
			alert("Fehlerhafter JSON Response - Doku pruefen!");
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
        $module = JModuleHelper::getModule('buliergebnisse', $jinput->get('titel', 'default_value', 'filter'));
        $db = JFactory::getContainer()->get(DatabaseInterface::class);

        $jparams = new JRegistry();
        $jparams->loadString($module->params);

        $tage = ['So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.'];
        $clrunden = ['', 'Gruppe A', 'Gruppe B', 'Gruppe C', 'Gruppe D', 'Gruppe E', 'Gruppe F', 'Gruppe G', 'Gruppe H', 'Achtelfinale', 'Viertelfinale', 'Halbfinale', 'Finale'];

        // Teams aus der Joomla Tabelle holen
        $query = 'SELECT '.$db->quoteName('bezeichnung_webservice').', '.$db->quoteName('bezeichnung_kurz').', '.$db->quoteName('bezeichnung_mittel').', '.$db->quoteName('dateiname_logo').' FROM '.$db->quoteName('#__buliergebnisse');
        $db->setQuery($query);
        $teams = $db->loadAssocList('bezeichnung_webservice');
        $liga = $jparams->get('league');

        // Spieltag ermitteln
        if ($jinput->get('spieltag', 'default_value', 'filter') != '') {
            $spieltag = $jinput->get('spieltag', 'default_value', 'filter');
            if (! is_numeric($spieltag)) {
                $spieltag = array_search($spieltag, $clrunden);
            }
        } elseif ($jparams->get('matchday') != 0 && $jparams->get('matchday') != -1) {
            $spieltag = $jparams->get('matchday');
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
                $spieltag = self::decodeApiResponse($spieltag);
                $spieltagsname = $spieltag->GroupName;
                $spieltag = $spieltag->GroupOrderID;

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

        $saison = $jparams->get('season');

        // Cache lesen
        $cachefile = JPATH_BASE."/modules/mod_buliergebnisse/cache.txt";
        if (is_readable($cachefile)) {
            $cache = file_get_contents($cachefile);
        } else {
            $cache = self::fetchdata('http://www.jbuli.de/modules/mod_buliergebnisse/cache.txt', 10);

            if ($cache != false) {
                file_put_contents($cachefile, $cache);
            }
        }
        $paarungen_cache = unserialize($cache);

        // Letzte Änderung ermitteln
        $lastchange = self::fetchdata('https://www.openligadb.de/api/getlastchangedate/' . $liga . '/' . $saison . '/' . $spieltag, $jparams->get('timeout'));

        if ($lastchange === false) {
            // Kein Datum vom Webservice -> Datum aus dem Cache holen
            if ($paarungen_cache[$spieltag . $liga . $saison]) {
                $lastchange = array_keys($paarungen_cache[$spieltag . $liga . $saison]);
                $lastchange = $lastchange[0];
            }
        } else {
            $lastchange = strtotime(json_decode($lastchange));
        }

        // Spieltag mit diesem Stand schon im Cache?
        if (isset($paarungen_cache[$spieltag . $liga . $saison][$lastchange])) {
            $paarungen = $paarungen_cache[$spieltag . $liga . $saison][$lastchange];
        } else {
            // Daten abrufen und in den Cache schreiben
            $paarungen = self::fetchdata('https://www.openligadb.de/api/getmatchdata/' . $liga . '/' . $saison . '/' . $spieltag, $jparams->get('timeout'));

            if ($paarungen === false || stristr($paarungen, 'Maximale Abfrageanzahl von 1000 Abfragen pro Tag erreicht!') != false) {
                // Webservice nicht erreichbar, prüfen ob Spieltag mit älterem Stand im Cache ist
                if (is_array($paarungen_cache[$spieltag . $liga . $saison])) {
                    $paarungen = reset($paarungen_cache[$spieltag . $liga . $saison]);
                }
            } else {
                $paarungen = self::decodeApiResponse($paarungen);
                unset($paarungen_cache[$spieltag . $liga . $saison]);
                $paarungen_cache[$spieltag . $liga . $saison][$lastchange] = $paarungen;
                file_put_contents($cachefile, serialize($paarungen_cache));
            }
        }

        // Prüfen wie viele Ergebnisse zu diesem Spieltag vorliegen
        $anzahl_ergebnisse = 0;
        $anzahl_live = 0;
        if ($paarungen) {
            $bezeichnung = 'bezeichnung_mittel';
            foreach ($paarungen as $partie) {
                if (isset($partie->MatchResults[0])) {
                    $ergebnisse = $partie->MatchResults[0];
                    if ($ergebnisse instanceof stdClass) {
                        $anzahl_ergebnisse++;
                        if ($jparams->get('longnames') == '1') {
                            $bezeichnung = 'bezeichnung_mittel';
                        } else {
                            $bezeichnung = 'bezeichnung_kurz';
                        }

                        if ($partie->MatchIsFinished == false) {
                            $anzahl_live++;
                        }
                    }
                }
            }
        }

        // Start HTML OUTPUT
        $table = "<table class='jbuli-results'>\r\n";

        // Spieltag Dropdown
        if ($liga == 'cl1617') {
            $breite = '100';
        } else {
            $breite = '50';
        }
        $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7' style='padding-bottom:10px;'><nobr>Spieltag:&nbsp;<select style='min-width: " . $breite . "px;' id='spieltag_" . $module->id . "'>";

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
            $i++;

            if (trim($partie->Team1->TeamName) == $jparams->get('meinVerein') ||  trim($partie->Team2->TeamName) == $jparams->get('meinVerein')) {
                $style = $jparams->get('meinVereinCSS');
            } else {
                $style = '';
            }

            if ($termin != $partie->MatchDateTime && !$jparams->get('kompakt')) {
                if ($i==1) {
                    $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7'><b><i>".$tage[date("w", strtotime($partie->MatchDateTime))]." ".date("d.m. H:i", strtotime($partie->MatchDateTime))." Uhr</i></b></td>\r\n</tr>\r\n";
                } else {
                    $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7' style='padding-top:10px;'><b><i>".$tage[date("w", strtotime($partie->MatchDateTime))]." ".date("d.m. H:i", strtotime($partie->MatchDateTime))." Uhr</i></b></td>\r\n</tr>\r\n";
                }
            }

            $table .= "<tr style='$style'>\r\n";

            if ($jparams->get('kompakt')) {
                if ($liga == 'cl1617') {
                    $table .= "<td align='left' valign='middle'>".date("d.m.", strtotime($partie->MatchDateTime))."</td>\r\n";
                } else {
                    $table .= "<td align='left' valign='middle'>".$tage[date("w", strtotime($partie->MatchDateTime))]."</td>\r\n";
                }
            }

            $termin = $partie->MatchDateTime;

            // Team 1
            $table .= "<td align='left' valign='middle'><img style='width:20px; height:20px;' title='".$teams[trim($partie->Team1->TeamName)]['bezeichnung_mittel']."' alt='".$teams[trim($partie->Team1->TeamName)]['bezeichnung_mittel']."' src='".JURI::root()."modules/mod_buliergebnisse/images/".$teams[trim($partie->Team1->TeamName)]['dateiname_logo']."' /></td>\r\n";
            $table .= "<td align='left' valign='middle'>".$teams[trim($partie->Team1->TeamName)][$bezeichnung]."</td>\r\n";

            $table .= "<td align='left' valign='middle'>-&nbsp;</td>\r\n";

            // Team 2
            $table .= "<td align='left' valign='middle'><img style='width:20px; height:20px;' title='".$teams[trim($partie->Team2->TeamName)]['bezeichnung_mittel']."' alt='".$teams[trim($partie->Team2->TeamName)]['bezeichnung_mittel']."' src='".JURI::root()."modules/mod_buliergebnisse/images/".$teams[trim($partie->Team2->TeamName)]['dateiname_logo']."' /></td>\r\n";
            $table .= "<td align='left' valign='middle'>".$teams[trim($partie->Team2->TeamName)][$bezeichnung]."</td>\r\n";

            $tootip_text = "";
            $endergebnis = "";
            $halbzeitergebnis = "";
            if ($anzahl_ergebnisse > 0) {
                $table .= "<td align='left' valign='middle'>";
                $alle_ergebnisse = $partie->MatchResults;
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
                        if ($ergebnis->ResultName == 'Endergebnis') {
                            $endergebnis = $ergebnis->PointsTeam1.":".$ergebnis->PointsTeam2;
                        } elseif ($ergebnis->ResultName == 'Halbzeitergebnis' || $ergebnis->ResultName == 'Halbzeit') {
                            $halbzeitergebnis = " (".$ergebnis->PointsTeam1.":".$ergebnis->PointsTeam2.")";
                        }
                    }
                    if ($endergebnis == '') {
                        $endergebnis = '0:0';
                    }
                    $tootip_text .= $endergebnis . $halbzeitergebnis;

                    $goals = '';
                    foreach ($partie->Goals as $goal) {
                        if ($goal->GoalGetterName) {
                            if ($goal->MatchMinute) {
                                $goals .= '<b>' . $goal->ScoreTeam1 . ':' . $goal->ScoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . ' (' . $goal->MatchMinute . '.)<br>';
                            } else {
                                $goals .= '<b>' . $goal->ScoreTeam1 . ':' . $goal->ScoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . '<br>';
                            }
                        }
                    }
                }
                if (isset($partie->matchIsFinished)) {
                    if (!$partie->matchIsFinished && $alle_ergebnisse[0] instanceof stdClass) {
                        $tootip_text .= "</font>";
                    }
                }

                $tootip_text .= "</nobr>";

                if ($goals <> '') {
                    $table .= JHtml::_('tooltip', $goals, 'Tore', '', $tootip_text);
                } else {
                    $table .= $tootip_text;
                }

                $table .= "</td>\r\n";
            } elseif ($jparams->get('longnames') == '1') {
                $table .= "<td>-:- (-:-)</td>\r\n";
            }
            $table .= "</tr>\r\n";
        }

        $table .= "</table>\r\n";

        return $table;
    }
}

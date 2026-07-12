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
                . '#spielplan_' . (int) $module->id . ' .jbuli-loader { display:inline-block; width:22px; height:22px; box-sizing:border-box; border:3px solid currentColor; border-right-color:transparent; border-radius:50%; opacity:.72; vertical-align:middle; animation:jbuli-spin .72s linear infinite; }'
                . '@keyframes jbuli-spin { to { transform:rotate(360deg); } }'
                . '@media (prefers-reduced-motion:reduce) { #spielplan_' . (int) $module->id . ' .jbuli-loader { animation-duration:1.6s; } }'
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
                . '#spielplan_' . (int) $module->id . ' .jbuli-goal-tooltip { cursor:pointer; outline-offset:2px; border-bottom:1px dotted currentColor; }'
                . '#jbuli-goal-popover_' . (int) $module->id . ' { position:fixed; z-index:2147483647; display:none; min-width:190px; max-width:min(320px,calc(100vw - 16px)); padding:10px 12px; border:1px solid rgba(255,255,255,.16); border-radius:8px; background:#182126; color:#fff; box-shadow:0 8px 28px rgba(0,0,0,.32); font-size:.875rem; line-height:1.35; pointer-events:none; }'
                . '#jbuli-goal-popover_' . (int) $module->id . '.is-visible { display:block; }'
                . '#jbuli-goal-popover_' . (int) $module->id . ' .jbuli-tooltip-heading { margin:0 0 7px; color:#fff; font-weight:700; }'
                . '#jbuli-goal-popover_' . (int) $module->id . ' .jbuli-tooltip-row { display:grid; grid-template-columns:2.6rem 1fr; gap:8px; padding:3px 0; }'
                . '#jbuli-goal-popover_' . (int) $module->id . ' .jbuli-tooltip-score { color:#8ed1fc; font-weight:700; font-variant-numeric:tabular-nums; }'
                . '@container (max-width:400px) {'
                . '#spielplan_' . (int) $module->id . ' .jbuli-match td { font-size:.93rem; padding-left:3px; padding-right:3px; }'
                . '#spielplan_' . (int) $module->id . ' .jbuli-team { width:auto; }'
                . '}'
        );

        $document->addScriptDeclaration('
      jQuery(document).ready(function() {
        init_goal_tooltip_' . $module->id . '();
        change_spieltag_' . $module->id . '();
        jQuery(document).on("change", "#spielplan_' . $module->id . '", change_spieltag_' . $module->id . ');
      });

      function init_goal_tooltip_' . $module->id . '() {
        var root = jQuery("#spielplan_' . $module->id . '");
        var popover = jQuery("#jbuli-goal-popover_' . $module->id . '");
        if (!popover.length) {
          popover = jQuery("<div id=\"jbuli-goal-popover_' . $module->id . '\" role=\"tooltip\"></div>").appendTo(document.body);
        }
        root.off(".jbuliGoalTooltip").on("mouseenter.jbuliGoalTooltip focusin.jbuliGoalTooltip", ".jbuli-goal-tooltip", function() {
          var target = this;
          var lines = String(target.getAttribute("data-tooltip") || "").split(/\\r?\\n/).filter(Boolean);
          if (!lines.length) { return; }
          popover.empty().append(jQuery("<div class=\"jbuli-tooltip-heading\"></div>").text("Torschützen"));
          lines.forEach(function(line) {
            var parts = line.match(/^(\\d+:\\d+)\\s+(.*)$/);
            var row = jQuery("<div class=\"jbuli-tooltip-row\"></div>");
            row.append(jQuery("<span class=\"jbuli-tooltip-score\"></span>").text(parts ? parts[1] : ""));
            row.append(jQuery("<span></span>").text(parts ? parts[2] : line));
            popover.append(row);
          });
          popover.addClass("is-visible");
          var rect = target.getBoundingClientRect();
          var left = Math.max(8, Math.min(rect.left + rect.width / 2 - popover.outerWidth() / 2, window.innerWidth - popover.outerWidth() - 8));
          var top = rect.top - popover.outerHeight() - 9;
          if (top < 8) { top = rect.bottom + 9; }
          popover.css({left: left + "px", top: top + "px"});
        }).on("mouseleave.jbuliGoalTooltip focusout.jbuliGoalTooltip", ".jbuli-goal-tooltip", function() {
          popover.removeClass("is-visible");
        });
      }
        
      function change_spieltag_' . $module->id . '() {
        var requestNumber = (window.jbuliErgebnisseRequest_' . $module->id . ' || 0) + 1;
        window.jbuliErgebnisseRequest_' . $module->id . ' = requestNumber;
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
              if (requestNumber !== window.jbuliErgebnisseRequest_' . $module->id . ') { return; }
              jQuery("#buliergebnisse_loading_' . $module->id . '").hide();
              if (data.success == false) {
                jQuery("#spielplan_' . $module->id . '").html(data.message);
              } else {
                jQuery("#spielplan_' . $module->id . '").html(data.data);
                jQuery(".hasTooltip").tooltip({html: "true"});
              }
            }
        ).fail(function(xhr) {
	      if (requestNumber !== window.jbuliErgebnisseRequest_' . $module->id . ') { return; }
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
                'http' => ['timeout' => $timeout]
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
        self::sendAjaxNoCacheHeaders();
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
        $clrunden = ['', '1. Spieltag', '2. Spieltag', '3. Spieltag', '4. Spieltag', '5. Spieltag', '6. Spieltag', '7. Spieltag', '8. Spieltag', 'Playoffs', 'Achtelfinale Hinspiele', 'Achtelfinale Rückspiele', 'Viertelfinale Hinspiele', 'Viertelfinale Rückspiele', 'Halbfinale Hinspiele', 'Halbfinale Rückspiele', 'Finale'];

        // Teams aus der Joomla Tabelle holen
        $query = 'SELECT ' . $db->quoteName('bezeichnung_webservice') . ', ' . $db->quoteName('bezeichnung_kurz') . ', ' . $db->quoteName('bezeichnung_mittel') . ', ' . $db->quoteName('dateiname_logo') . ' FROM ' . $db->quoteName('#__buliergebnisse');
        $db->setQuery($query);
        $teams = $db->loadAssocList('bezeichnung_webservice');
        $saison = (int) $jparams->get('season');
        $liga = self::normaliseLeagueShortcut((string) $jparams->get('league'), $saison);
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
            if (in_array($liga, ['epl', 'pl', 'sa', 'la1', 'pd'], true)) {
                $spieltag = 38;
            } elseif ($liga == 'ucl') {
                $spieltag = 16;
            } else {
                $spieltag = 34;
            }
        } else {
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
                if (is_object($currentGroup) && isset($currentGroup->GroupOrderID)) {
                    $spieltagsname = (string) ($currentGroup->GroupName ?? '');
                    $spieltag = (int) $currentGroup->GroupOrderID;
                } else {
                    $spieltagsname = '';
                    $spieltag = (int) $jparams->get('lastCurrentMatchday', 1);
                }

                // Der aktuelle Spieltag gilt für diese Anfrage. Die alte JTable-API
                // zum Schreiben von Modulparametern existiert in Joomla 6 nicht mehr.
            }
        }

        // Wenn -1 eingestellt dann vorherigen Spieltag anzeigen
        if ($jparams->get('matchday') == '-1' && $spieltag > 1) {
            $spieltag -= 1;
        }

        // Jede Auswahl erhält einen eigenen Cache. So können parallele
        // Spieltagswechsel keine Einträge einer anderen Auswahl überschreiben.
        $safeLiga = preg_replace('/[^a-z0-9_-]/i', '', $liga);
        $cachefile = JPATH_CACHE . '/mod_buliergebnisse_' . (int) $module->id . '_'
            . $safeLiga . '_' . $saison . '_' . (int) $spieltag . '.json';
        self::migrateLegacyMatchdayCache(
            JPATH_CACHE . '/mod_buliergebnisse_' . (int) $module->id . '.cache',
            $cachefile,
            $spieltag . $liga . $saison
        );
        $paarungen = self::fetchCachedMatchday(
            'https://api.openligadb.de/getmatchdata/' . $liga . '/' . $saison . '/' . $spieltag,
            'https://api.openligadb.de/getlastchangedate/' . $liga . '/' . $saison . '/' . $spieltag,
            $cachefile,
            (int) $jparams->get('timeout')
        );

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

        if (in_array($liga, ['epl', 'pl', 'sa', 'la1', 'pd'], true)) {
            $spieltage = 38;
        } elseif ($liga == 'ucl') {
            $spieltage = 16;
        } else {
            $spieltage = 34;
        }

        for ($i = 1; $i <= $spieltage; $i++) {
            if ($liga == 'ucl') {
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

        $table .= "</select><span id='buliergebnisse_loading_" . $module->id . "' class='jbuli-loader' role='status' aria-label='Wird geladen' style='display:none; margin-left:8px;'></span>";
        $table .= "</nobr></td>\r\n</tr>\r\n";

        // Live Spiele anzeigen
        if ($anzahl_ergebnisse > 0 && $anzahl_live == 1) {
            $table .= "<tr>\r\n<td align='center' valign='middle' colspan='7'><font color='red'><b>JETZT EIN SPIEL LIVE!</b></font></td>\r\n</tr>\r\n";
        } elseif ($anzahl_ergebnisse > 0 && $anzahl_live > 1) {
            $table .= "<tr>\r\n<td align='center' valign='middle' colspan='7'><font color='red'><b>JETZT " . $anzahl_live . " SPIELE LIVE!</b></font></td>\r\n</tr>\r\n";
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
                if ($i == 1) {
                    $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7'><b><i>" . $tage[date("w", strtotime($partie->MatchDateTime))] . " " . date("d.m. H:i", strtotime($partie->MatchDateTime)) . " Uhr</i></b></td>\r\n</tr>\r\n";
                } else {
                    $table .= "<tr>\r\n<td align='left' valign='middle' colspan='7' style='padding-top:10px;'><b><i>" . $tage[date("w", strtotime($partie->MatchDateTime))] . " " . date("d.m. H:i", strtotime($partie->MatchDateTime)) . " Uhr</i></b></td>\r\n</tr>\r\n";
                }
            }

            $table .= "<tr class='jbuli-match' style='$style'>\r\n";

            if ($compactView) {
                if ($liga == 'ucl') {
                    $table .= "<td class='jbuli-date' align='left' valign='middle'>" . date("d.m.", strtotime($partie->MatchDateTime)) . "</td>\r\n";
                } else {
                    $table .= "<td class='jbuli-date' align='left' valign='middle'>" . $tage[date("w", strtotime($partie->MatchDateTime))] . "</td>\r\n";
                }
            }

            $termin = $partie->MatchDateTime;

            $team1Name = trim((string) ($partie->Team1->TeamName ?? ''));
            $team2Name = trim((string) ($partie->Team2->TeamName ?? ''));
            $team1 = $teams[$team1Name] ?? ['bezeichnung_mittel' => $team1Name, $bezeichnung => $team1Name, 'dateiname_logo' => ''];
            $team2 = $teams[$team2Name] ?? ['bezeichnung_mittel' => $team2Name, $bezeichnung => $team2Name, 'dateiname_logo' => ''];
            $team1LogoFile = (string) ($team1['dateiname_logo'] ?? '');
            $team2LogoFile = (string) ($team2['dateiname_logo'] ?? '');
            $team1Logo = $team1LogoFile !== '' && is_file(JPATH_BASE . '/modules/mod_buliergebnisse/images/' . $team1LogoFile)
                ? JURI::root() . 'modules/mod_buliergebnisse/images/' . rawurlencode($team1LogoFile)
                : (string) ($partie->Team1->TeamIconUrl ?? '');
            $team2Logo = $team2LogoFile !== '' && is_file(JPATH_BASE . '/modules/mod_buliergebnisse/images/' . $team2LogoFile)
                ? JURI::root() . 'modules/mod_buliergebnisse/images/' . rawurlencode($team2LogoFile)
                : (string) ($partie->Team2->TeamIconUrl ?? '');

            // Team 1
            $table .= "<td class='jbuli-logo' align='left' valign='middle'><img title='" . htmlspecialchars((string) $team1['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8') . "' alt='' src='" . htmlspecialchars($team1Logo, ENT_QUOTES, 'UTF-8') . "' /></td>\r\n";
            $table .= "<td class='jbuli-team' align='left' valign='middle'>" . htmlspecialchars((string) ($team1[$bezeichnung] ?? $team1Name), ENT_QUOTES, 'UTF-8') . "</td>\r\n";

            $table .= "<td class='jbuli-separator' align='left' valign='middle'>-</td>\r\n";

            // Team 2
            $table .= "<td class='jbuli-logo' align='left' valign='middle'><img title='" . htmlspecialchars((string) $team2['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8') . "' alt='' src='" . htmlspecialchars($team2Logo, ENT_QUOTES, 'UTF-8') . "' /></td>\r\n";
            $table .= "<td class='jbuli-team' align='left' valign='middle'>" . htmlspecialchars((string) ($team2[$bezeichnung] ?? $team2Name), ENT_QUOTES, 'UTF-8') . "</td>\r\n";

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
                            $endergebnis = $ergebnis->PointsTeam1 . ":" . $ergebnis->PointsTeam2;
                        } elseif (isset($ergebnis->PointsTeam1, $ergebnis->PointsTeam2) && (($ergebnis->ResultName ?? '') == 'Halbzeitergebnis' || ($ergebnis->ResultName ?? '') == 'Halbzeit')) {
                            $halbzeitergebnis = " (" . $ergebnis->PointsTeam1 . ":" . $ergebnis->PointsTeam2 . ")";
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
                    $goalText = trim(html_entity_decode(strip_tags(str_replace('<br>', "\n", $goals)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $goalAttribute = htmlspecialchars($goalText, ENT_QUOTES, 'UTF-8');
                    $table .= '<span class="jbuli-goal-tooltip" tabindex="0" data-tooltip="' . $goalAttribute . '" aria-label="Torschützen anzeigen">' . $tootip_text . '</span>';
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

    private static function fetchCachedMatchday(string $url, string $lastChangeUrl, string $cachefile, int $timeout): array
    {
        $readCache = static function () use ($cachefile): array {
            if (!is_readable($cachefile)) {
                return [];
            }
            $json = file_get_contents($cachefile);
            if (!is_string($json) || $json === '') {
                return [];
            }
            $decoded = self::decodeApiResponse($json);

            return is_array($decoded) ? $decoded : [];
        };

        $cached = $readCache();
        $lockHandle = @fopen($cachefile . '.lock', 'c');
        $hasLock = is_resource($lockHandle) && @flock($lockHandle, LOCK_EX);
        if ($hasLock) {
            $lockedCache = $readCache();
            if ($lockedCache !== []) {
                $cached = $lockedCache;
            }
        }

        $lastChangeResponse = self::fetchdata($lastChangeUrl, $timeout);
        if ($cached !== [] && $lastChangeResponse === false) {
            self::releaseCacheLock($lockHandle, $hasLock);

            return $cached;
        }

        $decodedLastChange = is_string($lastChangeResponse) ? json_decode($lastChangeResponse) : null;
        $lastChange = is_string($decodedLastChange) ? (strtotime($decodedLastChange) ?: 0) : 0;
        if ($cached !== [] && ($lastChange === 0 || (int) @filemtime($cachefile) >= $lastChange)) {
            self::releaseCacheLock($lockHandle, $hasLock);

            return $cached;
        }

        $json = self::fetchdata($url, $timeout);
        $decoded = is_string($json) ? self::decodeApiResponse($json) : null;
        // Ein Strg+F5 wirkte bisher oft nur deshalb, weil es einen zweiten
        // API-Versuch auslöste. Bei einem kalten Cache erledigt das Modul diesen
        // einmaligen Retry nun selbst und lässt OpenLigaDB etwas mehr Zeit.
        if ((!is_array($decoded) || $decoded === []) && $cached === []) {
            $json = self::fetchdata($url, max(5, $timeout + 2));
            $decoded = is_string($json) ? self::decodeApiResponse($json) : null;
        }
        if (is_array($decoded) && $decoded !== []) {
            self::writeCacheAtomically($cachefile, $json);
            $cached = $decoded;
        }

        self::releaseCacheLock($lockHandle, $hasLock);

        return $cached;
    }

    private static function migrateLegacyMatchdayCache(string $legacyFile, string $cachefile, string $cacheKey): void
    {
        if (is_file($cachefile) || !is_readable($legacyFile)) {
            return;
        }

        $content = file_get_contents($legacyFile);
        $legacy = is_string($content) && $content !== ''
            ? @unserialize($content, ['allowed_classes' => [stdClass::class]])
            : null;
        if (!is_array($legacy) || empty($legacy[$cacheKey]) || !is_array($legacy[$cacheKey])) {
            return;
        }

        $matches = reset($legacy[$cacheKey]);
        if (!is_array($matches) || $matches === []) {
            return;
        }

        $json = json_encode($matches, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            self::writeCacheAtomically($cachefile, $json);
        }
    }

    private static function releaseCacheLock($lockHandle, bool $hasLock): void
    {
        if ($hasLock) {
            @flock($lockHandle, LOCK_UN);
        }
        if (is_resource($lockHandle)) {
            fclose($lockHandle);
        }
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

    private static function normaliseLeagueShortcut(string $league, int $season): string
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
}

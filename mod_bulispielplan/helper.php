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

        $style = '#bulispielplan_' . (int) $module->id . ' { width:100%; max-width:none; container-type:inline-size; }
              #bulispielplan_' . (int) $module->id . ' table { width:100%; border-collapse:collapse; font-variant-numeric:tabular-nums; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select { display:flex; align-items:center; width:100%; min-height:38px; border:1px solid rgba(127,127,127,.55); border-radius:4px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select > img { width:22px; height:22px; margin-left:8px; flex:0 0 22px; }
              #bulispielplan_' . (int) $module->id . ' select { width:100%; height:36px; max-width:100%; padding:5px 8px; border:0; background:transparent; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select.jbuli-enhanced { position:relative; display:block; border:0; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select.jbuli-enhanced > select,
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select.jbuli-enhanced > img { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-button { display:flex; align-items:center; gap:9px; width:100%; min-height:38px; padding:6px 34px 6px 9px; cursor:pointer; border:1px solid rgba(127,127,127,.55); border-radius:4px; background-color:transparent; background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\'%3E%3Cpath d=\'M1 1.5 6 6.5 11 1.5\' fill=\'none\' stroke=\'%23555\' stroke-width=\'1.6\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; background-size:12px 8px; color:inherit; text-align:left; position:relative; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-button img,
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option img { width:20px; height:20px; flex:0 0 20px; object-fit:contain; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-menu { display:none; position:absolute; z-index:1000; top:calc(100% + 3px); left:0; right:0; max-height:320px; overflow-y:auto; padding:4px; border:1px solid rgba(127,127,127,.55); border-radius:4px; background:var(--body-bg,#fff); box-shadow:0 5px 18px rgba(0,0,0,.18); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-menu.is-open { display:block; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option { display:flex; align-items:center; gap:9px; width:100%; min-height:34px; padding:6px 8px; border:0; border-radius:3px; background:transparent; color:inherit; text-align:left; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option:hover,
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option:focus { background:rgba(127,127,127,.14); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td { vertical-align:middle; padding:6px 7px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule tr { border-bottom:1px solid rgba(127,127,127,.25); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule tr:hover { background:rgba(127,127,127,.08); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(1) { width:2.2rem; text-align:right; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(2) { width:3.8rem; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(3) { width:32px; min-width:32px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(5) { width:2rem; text-align:center; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(6) { width:3.5rem; text-align:right; font-weight:700; }
              @container (max-width:460px) {
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td { padding-left:3px; padding-right:3px; }
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(1),
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(5) { display:none; }
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(6) { min-width:3.5rem; }
              }
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
                enhance_verein_dropdown_' . $module->id . '();
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
			data = {success: false, message: "Keine Verbindung zum Ergebnisserver. Bitte später erneut versuchen."};
		  };
          jQuery("#bulispielplan_loading_' . $module->id . '").hide();
          if (data.success == false) {
            jQuery("#bulispielplan_' . $module->id . '").html(data.message);
          } else {
            jQuery("#bulispielplan_' . $module->id . '").html(data.data);
            enhance_verein_dropdown_' . $module->id . '();
            var current = document.getElementById("' . $module->id . '_current");
            if (current) { current.scrollIntoView({block: "center"}); }
          }
        });
      };

      function enhance_verein_dropdown_' . $module->id . '() {
        var select = jQuery("#verein_' . $module->id . '");
        var wrapper = select.closest(".jbuli-team-select");
        if (!select.length || wrapper.hasClass("jbuli-enhanced")) { return; }

        var trigger = jQuery("<button type=\"button\" class=\"jbuli-select-button\" aria-haspopup=\"listbox\" aria-expanded=\"false\"></button>");
        var menu = jQuery("<div class=\"jbuli-select-menu\" role=\"listbox\"></div>");

        function fillButton(option) {
          trigger.empty();
          var url = option.data("logo") || "";
          if (url) { trigger.append(jQuery("<img alt=\"\">").attr("src", url)); }
          trigger.append(jQuery("<span></span>").text(option.text()));
        }

        select.find("option").each(function() {
          var option = jQuery(this);
          var item = jQuery("<button type=\"button\" class=\"jbuli-select-option\" role=\"option\"></button>");
          var url = option.data("logo") || "";
          if (url) { item.append(jQuery("<img alt=\"\">").attr("src", url)); }
          item.append(jQuery("<span></span>").text(option.text()));
          item.attr("aria-selected", option.is(":selected") ? "true" : "false");
          item.on("click", function(event) {
            event.stopPropagation();
            select.val(option.val());
            menu.removeClass("is-open");
            trigger.attr("aria-expanded", "false");
            fillButton(option);
            select.trigger("change");
          });
          menu.append(item);
        });

        trigger.on("click", function(event) {
          event.stopPropagation();
          var open = !menu.hasClass("is-open");
          menu.toggleClass("is-open", open);
          trigger.attr("aria-expanded", open ? "true" : "false");
        });
        jQuery(document).off("click.jbuli_' . $module->id . '").on("click.jbuli_' . $module->id . '", function() {
          menu.removeClass("is-open");
          trigger.attr("aria-expanded", "false");
        });

        wrapper.addClass("jbuli-enhanced").append(trigger, menu);
        fillButton(select.find("option:selected"));
      }
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
        $liga = (string) $jparams->get('league', 'bl1');
        if (!in_array($liga, ['bl1', 'bl2'], true)) {
            $liga = 'bl1';
        }

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

        // Die Vereinsauswahl exakt aus Liga und Saison der API aufbauen.
        $availableJson = self::fetchdata(
            'https://api.openligadb.de/getavailableteams/' . $liga . '/' . (int) $jparams->get('season'),
            $jparams->get('timeout')
        );
        $availableTeams = $availableJson ? self::decodeApiResponse($availableJson) : [];
        $teams = [];
        foreach ((array) $availableTeams as $apiTeam) {
            $name = (string) ($apiTeam->TeamName ?? '');
            if ($name === '') {
                continue;
            }
            $teams[$name] = $allTeams[$name] ?? [
                'bezeichnung_webservice' => $name,
                'bezeichnung_kurz' => (string) ($apiTeam->ShortName ?? $name),
                'bezeichnung_mittel' => (string) ($apiTeam->ShortName ?? $name),
                'dateiname_logo' => '',
                'logo_module' => '',
            ];
            $teams[$name]['team_icon_url'] = (string) ($apiTeam->TeamIconUrl ?? '');
        }
        uasort($teams, static fn(array $a, array $b): int => strcasecmp($a['bezeichnung_mittel'], $b['bezeichnung_mittel']));

        // Start HTML OUTPUT
        $table = "\r\n<table class='jbuli-team-selector'>\r\n";

        // Verein Dropdown
        $table .= "<tr><td><div class='jbuli-team-select'><img id='verein_logo_" . $module->id . "' alt='' style='display:none;'><select id='verein_" . $module->id . "'>";
        $verein = '';
        $requestedTeam = trim($jinput->getString('verein', ''));
        $requestedTeam = $teamAliases[$requestedTeam] ?? $requestedTeam;
        $useLongNames = $jparams->get('longnames') == '1';

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
            $logoUrl = !empty($team['dateiname_logo']) && !empty($team['logo_module'])
                ? JURI::root() . 'modules/' . $team['logo_module'] . '/images/' . rawurlencode($team['dateiname_logo'])
                : (string) ($team['team_icon_url'] ?? '');
            $table .= '<option value="' . htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-logo="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '"'
                . ($selected ? ' selected="selected"' : '') . '>'
                . htmlspecialchars($useLongNames ? $teamName : (string) $team['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        if ($verein === '' && !empty($teams)) {
            $firstTeam = reset($teams);
            $verein = $firstTeam['bezeichnung_webservice'];
        }

        $table .= "</select><img id='bulispielplan_loading_" . $module->id . "' src='".JURI::root()."modules/mod_bulispielplan/images/ajax-loader.gif' style='display:none; margin-right:8px;'></div></td></tr></table>";
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
                        $anzeige = $useLongNames ? $opponent->TeamName : ($teamData['bezeichnung_mittel'] ?? $opponent->TeamName);
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
                        $anzeige = $useLongNames ? $opponent->TeamName : ($teamData['bezeichnung_mittel'] ?? $opponent->TeamName);
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

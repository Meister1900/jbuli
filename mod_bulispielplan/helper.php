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

class modBulispielplanHelper
{
    /**
     * Constructor
     */
    public function __construct($module, $params)
    {
        JHtml::_('jquery.framework');

        $assets = JFactory::getApplication()->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle('mod_bulispielplan.styles', 'modules/mod_bulispielplan/media/css/module.css', ['version' => 'auto']);
        $assets->registerAndUseScript('mod_bulispielplan.script', 'modules/mod_bulispielplan/media/js/module.js', ['version' => 'auto'], ['defer' => true]);

        /*
        $style = '#bulispielplan_' . (int) $module->id . ' { width:100%; max-width:none; container-type:inline-size; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-loader { display:inline-block; width:22px; height:22px; box-sizing:border-box; border:3px solid currentColor; border-right-color:transparent; border-radius:50%; opacity:.72; vertical-align:middle; animation:jbuli-spin .72s linear infinite; }
              @keyframes jbuli-spin { to { transform:rotate(360deg); } }
              @media (prefers-reduced-motion:reduce) { #bulispielplan_' . (int) $module->id . ' .jbuli-loader { animation-duration:1.6s; } }
              #bulispielplan_' . (int) $module->id . ' table { width:100%; border-collapse:collapse; font-variant-numeric:tabular-nums; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule { table-layout:fixed; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select-row { display:flex; align-items:center; gap:8px; width:100%; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select-row > .jbuli-loader { flex:0 0 22px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select { display:flex; align-items:center; flex:1 1 auto; min-width:0; min-height:38px; border:1px solid rgba(127,127,127,.55); border-radius:4px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select > img { width:22px; height:22px; margin-left:8px; flex:0 0 22px; }
              #bulispielplan_' . (int) $module->id . ' select { width:100%; height:36px; max-width:100%; padding:5px 8px; border:0; background:transparent; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select.jbuli-enhanced { position:relative; display:block; border:0; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select.jbuli-enhanced > select,
              #bulispielplan_' . (int) $module->id . ' .jbuli-team-select.jbuli-enhanced > img { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-button { display:flex; align-items:center; gap:9px; width:100%; min-height:38px; padding:6px 34px 6px 9px; cursor:pointer; border:1px solid rgba(127,127,127,.55); border-radius:4px; background-color:transparent; background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\'%3E%3Cpath d=\'M1 1.5 6 6.5 11 1.5\' fill=\'none\' stroke=\'%23555\' stroke-width=\'1.6\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; background-size:12px 8px; color:inherit; text-align:left; position:relative; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-button img,
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option img { width:20px; height:20px; flex:0 0 20px; object-fit:contain; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-menu { display:none; position:absolute; z-index:1000; top:calc(100% + 3px); left:0; right:0; max-height:320px; overflow-y:auto; padding:4px; border:1px solid rgba(127,127,127,.55); border-radius:4px; box-shadow:0 5px 18px rgba(0,0,0,.18); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-menu.is-open { display:block; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option { display:flex; align-items:center; gap:9px; width:100%; min-height:34px; padding:6px 8px; border:0; border-radius:3px; background:transparent !important; color:inherit !important; text-align:left; cursor:pointer; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option:hover,
              #bulispielplan_' . (int) $module->id . ' .jbuli-select-option:focus { background:rgba(127,127,127,.14) !important; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td { vertical-align:middle; padding:6px 7px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule tr { border-bottom:1px solid rgba(127,127,127,.25); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule tr:hover { background:rgba(127,127,127,.08); }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(1) { width:2.2rem; text-align:right; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(2) { width:6.2rem; min-width:6.2rem; white-space:nowrap; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(3) { width:32px; min-width:32px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(4) { min-width:70px; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(5) { width:2rem; text-align:center; font-weight:700; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(6) { width:4rem; min-width:4rem; padding-right:12px; text-align:center; font-weight:700; white-space:nowrap; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule.jbuli-hide-year td:nth-child(2) { width:3.8rem; min-width:3.8rem; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-schedule.jbuli-hide-year .jbuli-date-year { display:none; }
              #bulispielplan_' . (int) $module->id . ' .jbuli-goal-tooltip { cursor:pointer; outline-offset:2px; border-bottom:1px dotted currentColor; }
              #jbuli-goal-popover_' . (int) $module->id . ' { position:fixed; z-index:2147483647; display:none; min-width:190px; max-width:min(320px,calc(100vw - 16px)); padding:10px 12px; border:1px solid rgba(255,255,255,.16); border-radius:8px; background:#182126; color:#fff; box-shadow:0 8px 28px rgba(0,0,0,.32); font-size:.875rem; line-height:1.35; pointer-events:none; }
              #jbuli-goal-popover_' . (int) $module->id . '.is-visible { display:block; }
              #jbuli-goal-popover_' . (int) $module->id . ' .jbuli-tooltip-heading { margin:0 0 7px; color:#fff; font-weight:700; }
              #jbuli-goal-popover_' . (int) $module->id . ' .jbuli-tooltip-row { display:grid; grid-template-columns:2.6rem 1fr; gap:8px; padding:3px 0; }
              #jbuli-goal-popover_' . (int) $module->id . ' .jbuli-tooltip-score { color:#8ed1fc; font-weight:700; font-variant-numeric:tabular-nums; }
              @container (max-width:460px) {
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td { padding-left:3px; padding-right:3px; }
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(1) { width:1.8rem; min-width:1.8rem; font-size:.86rem; }
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(5) { width:1.6rem; min-width:1.6rem; display:table-cell; }
                #bulispielplan_' . (int) $module->id . ' .jbuli-schedule td:nth-child(6) { min-width:4rem; padding-right:10px; }
              }
              #bulispielplan_' . (int) $module->id . ' img { display:block; width:20px; height:20px; object-fit:contain; }';
        $document->addStyleDeclaration($style);

        $document->addScriptDeclaration('
      jQuery(document).ready(function() {
        init_goal_tooltip_' . $module->id . '();
        change_verein_' . $module->id . '();
        jQuery(document).on("change", "#verein_' . $module->id . '", change_verein_' . $module->id . ');
        var container = document.getElementById("bulispielplan_' . $module->id . '");
        if (container && window.ResizeObserver) {
          new ResizeObserver(function() {
            window.requestAnimationFrame(fit_spielplan_date_' . $module->id . ');
          }).observe(container);
        } else {
          jQuery(window).on("resize.bulispielplan_' . $module->id . '", fit_spielplan_date_' . $module->id . ');
        }
      });

      function init_goal_tooltip_' . $module->id . '() {
        var root = jQuery("#bulispielplan_' . $module->id . '");
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

      function fit_spielplan_date_' . $module->id . '() {
        var container = document.getElementById("bulispielplan_' . $module->id . '");
        var scroll = container ? container.querySelector(".jbuli-schedule-scroll") : null;
        var table = scroll ? scroll.querySelector(".jbuli-schedule") : null;
        if (!scroll || !table) { return; }
        table.classList.remove("jbuli-hide-year");
        if (scroll.clientWidth < 460 || table.scrollWidth > scroll.clientWidth + 1) {
          table.classList.add("jbuli-hide-year");
        }
      }
        
      function change_verein_' . $module->id . '() {
        var requestNumber = (window.jbuliSpielplanRequest_' . $module->id . ' || 0) + 1;
        window.jbuliSpielplanRequest_' . $module->id . ' = requestNumber;
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
              if (requestNumber !== window.jbuliSpielplanRequest_' . $module->id . ') { return; }
              jQuery("#bulispielplan_loading_' . $module->id . '").hide();
              if (data.success == false) {
                jQuery("#bulispielplan_' . $module->id . '").html(data.message);
              } else {
                jQuery("#bulispielplan_' . $module->id . '").html(data.data);
                enhance_verein_dropdown_' . $module->id . '();
                fit_spielplan_date_' . $module->id . '();
              }
            }
        ).fail(function(xhr) {
          if (requestNumber !== window.jbuliSpielplanRequest_' . $module->id . ') { return; }
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
            fit_spielplan_date_' . $module->id . '();
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
          if (open) {
            var surface = wrapper.parent();
            while (surface.length) {
              var background = window.getComputedStyle(surface[0]).backgroundColor;
              if (background && background !== "rgba(0, 0, 0, 0)" && background !== "transparent") {
                menu.css({backgroundColor: background, color: window.getComputedStyle(surface[0]).color});
                break;
              }
              surface = surface.parent();
            }
          }
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
            curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla/6 mod_bulispielplan');
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
    public static function getSpielplanAjax()
    {
        self::sendAjaxNoCacheHeaders();
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
            'http' => ['timeout' => $jparams->get('timeout')]
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
        $query = 'SELECT ' . $db->quoteName('bezeichnung_webservice') . ', ' . $db->quoteName('bezeichnung_kurz') . ', ' . $db->quoteName('bezeichnung_mittel') . ', ' . $db->quoteName('dateiname_logo')
            . ' FROM ' . $db->quoteName('#__bulispielplan');
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
            $query = 'SELECT ' . $db->quoteName('bezeichnung_webservice') . ', ' . $db->quoteName('bezeichnung_kurz') . ', ' . $db->quoteName('bezeichnung_mittel') . ', ' . $db->quoteName('dateiname_logo')
                . ' FROM ' . $db->quoteName('#__buliergebnisse');
            $resultTeams = $db->setQuery($query)->loadAssocList('bezeichnung_webservice');
            foreach ($resultTeams as $name => $teamData) {
                $teamData['logo_module'] = 'mod_buliergebnisse';
                $allTeams[$name] = $teamData;
            }
        }

        // Die Vereinsauswahl exakt aus Liga und Saison der API aufbauen und
        // gegen parallele Reloads sowie kurzfristige API-Ausfälle absichern.
        $season = (int) $jparams->get('season');
        $cacheTtl = max(60, (int) $jparams->get('refresh', 60) * 60);
        $availableCachefile = JPATH_CACHE . '/mod_bulispielplan_' . (int) $module->id . '_teams_' . $liga . '_' . $season . '.json';
        $availableTeams = self::fetchCachedApiArray(
            'https://api.openligadb.de/getavailableteams/' . $liga . '/' . $season,
            $availableCachefile,
            (int) $jparams->get('timeout'),
            $cacheTtl
        );
        $teams = [];
        foreach ($availableTeams as $apiTeam) {
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
            $teams[$name]['team_id'] = (int) ($apiTeam->TeamId ?? 0);
        }
        $requestedTeam = trim($jinput->getString('verein', ''));
        $requestedTeam = $teamAliases[$requestedTeam] ?? $requestedTeam;
        $fallbackTeam = $requestedTeam !== '' ? $requestedTeam : $configuredTeam;
        if ($teams === [] && $fallbackTeam !== '') {
            $teams[$fallbackTeam] = $allTeams[$fallbackTeam] ?? [
                'bezeichnung_webservice' => $fallbackTeam,
                'bezeichnung_kurz' => $fallbackTeam,
                'bezeichnung_mittel' => $fallbackTeam,
                'dateiname_logo' => '',
                'logo_module' => '',
                'team_icon_url' => '',
                'team_id' => 0,
            ];
        }
        uasort($teams, static fn(array $a, array $b): int => strcasecmp($a['bezeichnung_mittel'], $b['bezeichnung_mittel']));

        // Start HTML OUTPUT
        $table = "\r\n<table class='jbuli-team-selector'>\r\n";

        // Verein Dropdown
        $table .= "<tr><td><div class='jbuli-team-select-row'><div class='jbuli-team-select'><img id='verein_logo_" . $module->id . "' alt='' style='display:none;'><select id='verein_" . $module->id . "'>";
        $verein = '';
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
            $remoteLogoUrl = self::safeRemoteImageUrl((string) ($team['team_icon_url'] ?? ''));
            $localLogoUrl = self::localLogoUrl(
                (string) ($team['logo_module'] ?? ''),
                (string) ($team['dateiname_logo'] ?? '')
            );
            $logoUrl = $remoteLogoUrl !== '' ? $remoteLogoUrl : $localLogoUrl;
            $logoFallback = $remoteLogoUrl !== '' && $localLogoUrl !== ''
                ? ' data-logo-fallback="' . self::escapeHtmlAttribute($localLogoUrl) . '"'
                : '';
            $table .= '<option value="' . htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-logo="' . self::escapeHtmlAttribute($logoUrl) . '"' . $logoFallback
                . ($selected ? ' selected="selected"' : '') . '>'
                . htmlspecialchars($useLongNames ? $teamName : (string) $team['bezeichnung_mittel'], ENT_QUOTES, 'UTF-8')
                . '</option>';
        }

        if ($verein === '' && !empty($teams)) {
            $firstTeam = reset($teams);
            $verein = $firstTeam['bezeichnung_webservice'];
        }
        $vereinTeamId = (int) ($teams[$verein]['team_id'] ?? 0);

        $table .= "</select></div><span id='bulispielplan_loading_" . $module->id . "' class='jbuli-loader' role='status' aria-label='Wird geladen' style='display:none;'></span></div></td></tr></table>";
        $table .= "<div class='jbuli-schedule-scroll' style='height:" . (int) $jparams->get('hoehe', 400) . "px; width:100%; overflow-y:auto; overflow-x:hidden; margin-top:1rem;'>";
        $table .= "<table class='jbuli-schedule'>\r\n";

        // OpenLigaDB verwendet seit den aktuellen Wettbewerben die stabilen
        // Kürzel "dfb" und "ucl"; die Saison steht ausschließlich im zweiten
        // URL-Segment. Alle drei Wettbewerbe werden in einen Spielplan gemischt.
        $ligen = [$liga, 'dfb', 'ucl'];
        $partien = [];

        foreach ($ligen as $competitionIndex => $competition) {
            $cachefile = JPATH_CACHE . '/mod_bulispielplan_' . (int) $module->id . '_' . preg_replace('/[^a-z0-9_-]/i', '', $competition) . '_' . $season . '.json';
            $paarungen = self::fetchCachedApiArray(
                'https://api.openligadb.de/getmatchdata/' . $competition . '/' . $season,
                $cachefile,
                (int) $jparams->get('timeout'),
                $cacheTtl
            );
            if ($paarungen === []) {
                // Zusatzwettbewerbe sind optional; die Liga trotzdem anzeigen.
                if ($competitionIndex === 0) {
                    throw new RuntimeException((string) $jparams->get('timeout_error'));
                }
                continue;
            }

            foreach ($paarungen as $partie) {
                $partie->wettbewerb = $competition;
            }

            $partien = array_merge($partien, $paarungen);
        }

        $partien = array_values(array_filter($partien, static function ($partie): bool {
            return is_object($partie)
                && isset($partie->Team1->TeamName, $partie->Team2->TeamName, $partie->MatchDateTime);
        }));

        usort($partien, function ($a, $b) {
            return strcmp($a->MatchDateTime, $b->MatchDateTime);
        });

        $anzahl_partien = 0;
        foreach ($partien as $partie) {
            if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)
                || self::isSelectedTeam($partie->Team2, $verein, $vereinTeamId)) {
                $anzahl_partien++;
            }
        }

        // Output Spielplan
        $i = 0;
        $c = 0;
        $id = '';
        $hat_ergebnisse = false;
        foreach ($partien as $partie) {
            if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)
                || self::isSelectedTeam($partie->Team2, $verein, $vereinTeamId)) {
                $c++;
                $tootip_text = '';
                $goals = '';
                $ergebnisse = '<td class="jbuli-result">';
                $alle_ergebnisse = is_array($partie->MatchResults ?? null) ? $partie->MatchResults : [];

                if (! is_array($alle_ergebnisse) || count($alle_ergebnisse) == 0) {
                    $tootip_text .= '&nbsp;-:-';
                    if ($id != 'current' && $hat_ergebnisse) {
                        $id = 'current';
                    } else {
                        $id = '';
                    }

                    $hat_ergebnisse = false;
                } else {
                    if (!($partie->MatchIsFinished ?? true) && $alle_ergebnisse[0] instanceof stdClass) {
                        $tootip_text .= '<font color="red">';
                    }

                    $ergebnisse .= '<nobr>&nbsp;';
                    $id = '';
                    $hat_ergebnisse = true;

                    // Endergebnis ermitteln
                    foreach ($alle_ergebnisse as $ergebnis) {
                        if (($ergebnis->ResultName ?? '') == 'Endergebnis' && isset($ergebnis->PointsTeam1, $ergebnis->PointsTeam2)) {
                            if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)) {
                                $tootip_text .= $ergebnis->PointsTeam1 . ":" . $ergebnis->PointsTeam2;
                            } else {
                                $tootip_text .= $ergebnis->PointsTeam2 . ":" . $ergebnis->PointsTeam1;
                            }

                            break;
                        }
                    }

                    foreach ((array) ($partie->Goals ?? []) as $goal) {
                        if (!empty($goal->GoalGetterName)) {
                            $scoreTeam1 = (int) ($goal->ScoreTeam1 ?? 0);
                            $scoreTeam2 = (int) ($goal->ScoreTeam2 ?? 0);
                            if (!empty($goal->MatchMinute)) {
                                if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)) {
                                    $goals .= '<b>' . $scoreTeam1 . ':' . $scoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . ' (' . $goal->MatchMinute . '.)<br>';
                                } else {
                                    $goals .= '<b>' . $scoreTeam2 . ':' . $scoreTeam1 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . ' (' . $goal->MatchMinute . '.)<br>';
                                }
                            } else {
                                if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)) {
                                    $goals .= '<b>' . $scoreTeam1 . ':' . $scoreTeam2 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . '<br>';
                                } else {
                                    $goals .= '<b>' . $scoreTeam2 . ':' . $scoreTeam1 . '</b>&nbsp;&nbsp;' . $goal->GoalGetterName . '<br>';
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
                    $goalText = trim(html_entity_decode(strip_tags(str_replace('<br>', "\n", $goals)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $goalAttribute = htmlspecialchars($goalText, ENT_QUOTES, 'UTF-8');
                    $ergebnisse .= '<span class="jbuli-goal-tooltip" tabindex="0" data-tooltip="' . $goalAttribute . '" aria-label="Torschützen anzeigen">' . $tootip_text . '</span>';
                } else {
                    $ergebnisse .= $tootip_text;
                }

                $ergebnisse .= "</td>\r\n";
            }

            $tage = ["So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa."];

            if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)
                || self::isSelectedTeam($partie->Team2, $verein, $vereinTeamId)) {
                $bildFallback = '';
                if ($partie->wettbewerb == $ligen[0]) {
                    $anzeigename = 'Bundesliga';
                    $i++;
                    $kurz = $i;
                } elseif ($partie->wettbewerb == $ligen[1]) {
                    $kurz = 'PK';
                    $anzeigename = 'DFB Pokal';
                    $bildSrc = JURI::root() . 'modules/mod_bulispielplan/images/pokal.png';
                } elseif ($partie->wettbewerb == $ligen[2]) {
                    $kurz = 'CL';
                    $anzeigename = 'Champions League';
                    $bildSrc = JURI::root() . 'modules/mod_bulispielplan/images/cl.png';
                }

                if (self::isSelectedTeam($partie->Team1, $verein, $vereinTeamId)) {
                    $wo = 'H';
                    $opponent = $partie->Team2;
                    $teamData = $allTeams[$opponent->TeamName] ?? null;
                    $mediumName = trim((string) ($teamData['bezeichnung_mittel'] ?? ''));
                    $apiShortName = trim((string) ($opponent->ShortName ?? ''));
                    $anzeige = $useLongNames
                        ? $opponent->TeamName
                        : ($mediumName !== '' ? $mediumName : ($apiShortName !== '' ? $apiShortName : self::shortenTeamName($opponent->TeamName)));
                    if ($partie->wettbewerb == $ligen[0]) {
                        $localLogoUrl = is_array($teamData)
                            ? self::localLogoUrl(
                                (string) ($teamData['logo_module'] ?? ''),
                                (string) ($teamData['dateiname_logo'] ?? '')
                            )
                            : '';
                        $remoteLogoUrl = self::safeRemoteImageUrl((string) ($opponent->TeamIconUrl ?? ''));
                        $bildSrc = $remoteLogoUrl !== '' ? $remoteLogoUrl : $localLogoUrl;
                        $bildFallback = $remoteLogoUrl !== '' && $localLogoUrl !== '' ? $localLogoUrl : '';
                    }
                } else {
                    $wo = 'A';
                    $opponent = $partie->Team1;
                    $teamData = $allTeams[$opponent->TeamName] ?? null;
                    $mediumName = trim((string) ($teamData['bezeichnung_mittel'] ?? ''));
                    $apiShortName = trim((string) ($opponent->ShortName ?? ''));
                    $anzeige = $useLongNames
                        ? $opponent->TeamName
                        : ($mediumName !== '' ? $mediumName : ($apiShortName !== '' ? $apiShortName : self::shortenTeamName($opponent->TeamName)));
                    if ($partie->wettbewerb == $ligen[0]) {
                        $localLogoUrl = is_array($teamData)
                            ? self::localLogoUrl(
                                (string) ($teamData['logo_module'] ?? ''),
                                (string) ($teamData['dateiname_logo'] ?? '')
                            )
                            : '';
                        $remoteLogoUrl = self::safeRemoteImageUrl((string) ($opponent->TeamIconUrl ?? ''));
                        $bildSrc = $remoteLogoUrl !== '' ? $remoteLogoUrl : $localLogoUrl;
                        $bildFallback = $remoteLogoUrl !== '' && $localLogoUrl !== '' ? $localLogoUrl : '';
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
                $fallbackAttribute = !empty($bildFallback)
                    ? ' data-fallback-src="' . self::escapeHtmlAttribute($bildFallback) . '"'
                    : '';
                $table .= '<tr id="' . $module->id . '_' . $id . '"><td style="text-align:right; padding-right: 5px;">' . $kurz . '</td>
        <td title="' . htmlspecialchars($dateTitle, ENT_QUOTES, 'UTF-8') . '">' . date('d.m.', strtotime($partie->MatchDateTime)) . '<span class="jbuli-date-year">' . date('Y', strtotime($partie->MatchDateTime)) . '</span></td>
        <td><img style="width:20px; height:20px; object-fit:contain;" title="' . htmlspecialchars($anzeige, ENT_QUOTES, 'UTF-8') . '" alt="" src="' . self::escapeHtmlAttribute($bildSrc) . '"' . $fallbackAttribute . '></td>
        <td><div title="' . htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') . '" style="cursor:default; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:100%;">' . htmlspecialchars($anzeige, ENT_QUOTES, 'UTF-8') . '</div></td>
        <td>' . $wo . '</td>' . $ergebnisse . '</tr>';
            }
        }

        $table .= "</table></div>";

        return $table;
    }

    private static function localLogoUrl(string $moduleName, string $filename): string
    {
        $moduleName = trim($moduleName);
        $filename = trim($filename);
        if (!in_array($moduleName, ['mod_bulispielplan', 'mod_buliergebnisse'], true)
            || $filename === ''
            || basename($filename) !== $filename) {
            return '';
        }

        return is_file(JPATH_BASE . '/modules/' . $moduleName . '/images/' . $filename)
            ? JURI::root() . 'modules/' . $moduleName . '/images/' . rawurlencode($filename)
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

    private static function escapeHtmlAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function fetchCachedApiArray(string $url, string $cachefile, int $timeout, int $cacheTtl): array
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
        $cacheIsFresh = $cached !== [] && is_file($cachefile)
            && filemtime($cachefile) + $cacheTtl >= time();
        if ($cacheIsFresh) {
            return $cached;
        }

        $lockHandle = @fopen($cachefile . '.lock', 'c');
        $hasLock = is_resource($lockHandle) && @flock($lockHandle, LOCK_EX);
        if ($hasLock) {
            // Ein paralleler Request könnte den Cache während des Wartens bereits
            // gefüllt haben. Deshalb nach dem Lock noch einmal prüfen.
            $lockedCache = $readCache();
            $lockedCacheIsFresh = $lockedCache !== [] && is_file($cachefile)
                && filemtime($cachefile) + $cacheTtl >= time();
            if ($lockedCacheIsFresh) {
                @flock($lockHandle, LOCK_UN);
                fclose($lockHandle);

                return $lockedCache;
            }
            if ($lockedCache !== []) {
                $cached = $lockedCache;
            }
        }

        $json = self::fetchdata($url, $timeout);
        $decoded = is_string($json) ? self::decodeApiResponse($json) : null;
        if (is_array($decoded) && $decoded !== []) {
            self::writeCacheAtomically($cachefile, $json);
            $cached = $decoded;
        }

        if ($hasLock) {
            @flock($lockHandle, LOCK_UN);
        }
        if (is_resource($lockHandle)) {
            fclose($lockHandle);
        }

        return $cached;
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

    private static function shortenTeamName(string $name): string
    {
        $name = trim($name);
        if (strlen($name) <= 18) {
            return $name;
        }

        $withoutPrefix = preg_replace(
            '/^(?:1\.\s*)?(?:FC|SV|SC|TSV|VfB|VfL|SpVgg|FSV|BSG|SG)\s+/iu',
            '',
            $name
        );
        if (is_string($withoutPrefix) && strlen($withoutPrefix) <= 18) {
            return $withoutPrefix;
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        return $parts ? (string) end($parts) : $name;
    }

    private static function isSelectedTeam($team, string $selectedName, int $selectedTeamId): bool
    {
        if (!is_object($team)) {
            return false;
        }

        $teamId = (int) ($team->TeamId ?? 0);
        if ($selectedTeamId > 0 && $teamId > 0) {
            return $teamId === $selectedTeamId;
        }

        return (string) ($team->TeamName ?? '') === $selectedName;
    }
}

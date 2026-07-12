<?php
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
/**
 * mod_buliergebnisse.php - (c) Markus Krupp
 * Die Daten werden vom Webservice openligadb.de bereitgestellt
 */

    // no direct access
    defined('_JEXEC') or die('Restricted access');
    require_once __DIR__ . '/helper.php';

    $params->set('moduleclass_sfx', (string) $params->get('moduleclass_sfx', ''));
    $params->set('header_class', (string) $params->get('header_class', ''));
    $params->set('module_tag', (string) $params->get('module_tag', 'div'));
    $params->set('header_tag', (string) $params->get('header_tag', 'h3'));
    $strHTMLOutput = '';
    try {
        $ergebnisse = new modBuliergebnisseHelper($module);
        $strHTMLOutput = "\r\n<!-- Bundesliga-Ergebnisse 2.1.12 - (c) Markus Krupp - https://www.jbuli.de/-->\r\n";
        $strHTMLOutput .= "<div id='spielplan_" . $module->id . "'><span id='buliergebnisse_loading_" . $module->id . "' class='jbuli-loader' role='status' aria-label='Wird geladen' style='display:block; margin:12px auto;'></span></div>\r\n";
    } catch (Throwable $e) {
        $strHTMLOutput = '<div class="alert alert-warning">'
            . htmlspecialchars((string) $params->get('timeout_error', 'Die Ergebnisse konnten nicht geladen werden.'), ENT_QUOTES, 'UTF-8')
            . '</div>';
    }

    require JModuleHelper::getLayoutPath('mod_buliergebnisse');

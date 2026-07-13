<?php
/**
 * @package     JBuli
 * @copyright   (C) 2014-2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
/**
 * helper.php - (c) Markus Krupp
 * Die Daten werden vom Webservice openligadb.de bereitgestellt.
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
        $ergebnisse = new modBulispielplanHelper($module, $params);
        $strHTMLOutput = "\r\n<!-- Bundesliga-Spielplan 2.1.21 - (c) Markus Krupp - https://www.krupphome.de/-->\r\n";
        $strHTMLOutput .= '<div id="bulispielplan_' . $module->id . '"><span id="bulispielplan_loading_' . $module->id . '" class="jbuli-loader" role="status" aria-label="Wird geladen" style="display:block; margin:12px auto;"></span></div>';
    } catch (Throwable $e) {
        $strHTMLOutput = '<div class="alert alert-warning">'
            . htmlspecialchars((string) $params->get('timeout_error', 'Der Spielplan konnte nicht geladen werden.'), ENT_QUOTES, 'UTF-8')
            . '</div>';
    }

    require JModuleHelper::getLayoutPath('mod_bulispielplan');

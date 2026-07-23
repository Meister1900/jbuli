<?php

/**
 * @package     JBuli
 * @copyright   (C) 2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/helper.php';

$params->set('moduleclass_sfx', (string) $params->get('moduleclass_sfx', ''));
$params->set('header_class', (string) $params->get('header_class', ''));
$params->set('module_tag', (string) $params->get('module_tag', 'div'));
$params->set('header_tag', (string) $params->get('header_tag', 'h3'));

$strHTMLOutput = '';

try {
    new modBulitorschuetzenHelper($module);

    $moduleId = (int) $module->id;
    $errorText = htmlspecialchars(
        (string) $params->get('timeout_error', 'Die Torschützenliste konnte derzeit nicht geladen werden.'),
        ENT_QUOTES,
        'UTF-8'
    );
    $endpoint = htmlspecialchars(Uri::root() . 'index.php', ENT_QUOTES, 'UTF-8');

    $strHTMLOutput = "\n<!-- Torschützen 1.0.15 - (c) Markus Krupp - https://www.krupphome.de/ -->\n";
    $strHTMLOutput .= '<div id="bulitorschuetzen_' . $moduleId . '" class="jbuli-scorers"'
        . ' data-module-id="' . $moduleId . '"'
        . ' data-endpoint="' . $endpoint . '"'
        . ' data-error="' . $errorText . '">'
        . '<span class="jbuli-scorers-loader" role="status" aria-label="Wird geladen"></span>'
        . '</div>';
} catch (Throwable $e) {
    $strHTMLOutput = '<div class="alert alert-warning">'
        . htmlspecialchars(
            (string) $params->get('timeout_error', 'Die Torschützenliste konnte derzeit nicht geladen werden.'),
            ENT_QUOTES,
            'UTF-8'
        )
        . '</div>';
}

require ModuleHelper::getLayoutPath('mod_bulitorschuetzen');

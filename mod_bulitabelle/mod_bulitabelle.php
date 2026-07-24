<?php
/**
 * @package     JBuli
 * @copyright   (C) 2014-2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Uri\Uri as JURI;
/**
 * mod_bulitabelle.php - (c) Markus Krupp
 * Die Daten werden vom Webservice openligadb bereitgestellt.
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
      $tabelle = new modBulitabelleHelper($module);
      $strHTMLOutput = "\r\n<!-- Bundesliga-Tabelle 2.1.13 - (c) Markus Krupp - https://www.krupphome.de/-->\r\n";
      $activeMenu = JFactory::getApplication()->getMenu()->getActive();
      $itemId = $activeMenu ? (int) $activeMenu->id : 0;
      $endpoint = htmlspecialchars(JURI::base() . 'index.php', ENT_QUOTES, 'UTF-8');
      $strHTMLOutput .= '<div id="bulitabelle_' . (int) $module->id . '" class="jbuli-standings-root" data-module-id="' . (int) $module->id . '" data-item-id="' . $itemId . '" data-endpoint="' . $endpoint . '"><span id="bulitabelle_loading_' . (int) $module->id . '" class="jbuli-loader jbuli-loader-initial" role="status" aria-label="Wird geladen"></span></div>';
  } catch (Throwable $e) {
      $strHTMLOutput = '<div class="alert alert-warning">Ein Fehler ist aufgetreten.</div>';
  }

  require JModuleHelper::getLayoutPath('mod_bulitabelle');

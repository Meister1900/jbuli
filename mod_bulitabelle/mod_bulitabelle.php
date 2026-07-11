<?php
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
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
      $strHTMLOutput = "\r\n<!-- Bundesliga-Tabelle 1.18 - (c) Markus Krupp - http://www.jbuli.de-->\r\n";
      $strHTMLOutput .= '<div id="bulitabelle_' . $module->id . '"> <img id="bulitabelle_loading_' . $module->id . '" src="'.JURI::root().'modules/mod_bulitabelle/images/ajax-loader.gif"></div>';
  } catch (Throwable $e) {
      $strHTMLOutput = '<div class="alert alert-warning">Ein Fehler ist aufgetreten.</div>';
  }

  require JModuleHelper::getLayoutPath('mod_bulitabelle');

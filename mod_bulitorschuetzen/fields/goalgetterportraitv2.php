<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/modules/mod_bulitorschuetzen/helper.php';

class JFormFieldGoalgetterportraitv2 extends FormField
{
    protected $type = 'Goalgetterportraitv2';

    protected function getInput(): string
    {
        $data = $this->form->getData();
        $moduleId = (int) $data->get('id', 0);
        $params = $data->get('params', []);
        if ($params instanceof Registry) {
            $params = $params->toArray();
        }
        if (is_string($params)) {
            $decoded = json_decode($params, true);
            $params = is_array($decoded) ? $decoded : [];
        }
        if (is_object($params)) {
            $params = get_object_vars($params);
        }
        if (!is_array($params)) {
            $params = [];
        }
        if ($moduleId <= 0) {
            return '<div class="alert alert-info">Das Modul zuerst speichern; anschließend stehen die Torschützen zur Auswahl bereit.</div>';
        }

        $options = modBulitorschuetzenHelper::goalGetterOptions(
            $moduleId,
            (string) ($params['league'] ?? 'bl1'),
            (int) ($params['season'] ?? 2026),
            (int) ($params['refresh'] ?? 30),
            (int) ($params['timeout'] ?? 5)
        );
        if ($options === []) {
            return '<div class="alert alert-warning">Für die gespeicherte Liga und Saison sind derzeit keine Torschützen verfügbar.</div>';
        }

        Factory::getApplication()->getDocument()->getWebAssetManager()->registerAndUseScript(
            'mod_bulitorschuetzen.admin-upload',
            'modules/mod_bulitorschuetzen/media/js/admin-upload.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        $html = '<div class="jbuli-scorers-admin-upload" data-module-id="' . $moduleId . '" data-token="'
            . htmlspecialchars(Session::getFormToken(), ENT_QUOTES, 'UTF-8') . '">'
            . '<div class="row g-3 align-items-end"><div class="col-md-5"><label class="form-label" for="jbuli-scorers-player-'
            . $moduleId . '">Spieler</label><select id="jbuli-scorers-player-' . $moduleId
            . '" class="form-select jbuli-scorers-player-select">';
        foreach ($options as $option) {
            $html .= '<option value="' . (int) $option['id'] . '">'
                . htmlspecialchars($option['label'] . ' (' . $option['goals'] . ' Tore)', ENT_QUOTES, 'UTF-8')
                . '</option>';
        }
        return $html . '</select></div><div class="col-md-5"><label class="form-label" for="jbuli-scorers-file-'
            . $moduleId . '">Bilddatei</label><input id="jbuli-scorers-file-' . $moduleId
            . '" class="form-control jbuli-scorers-file-input" type="file" accept="image/jpeg,image/png,image/webp"></div>'
            . '<div class="col-md-2"><button class="btn btn-primary w-100 jbuli-scorers-upload-button" type="button">Hochladen</button></div></div>'
            . '<div class="form-text">JPEG, PNG oder WebP, maximal 5 MB und mindestens 150 Pixel breit. Die Datei wird automatisch nach der OpenLigaDB-Spieler-ID benannt.</div>'
            . '<div class="jbuli-scorers-upload-status mt-2" role="status" aria-live="polite"></div></div>';
    }
}

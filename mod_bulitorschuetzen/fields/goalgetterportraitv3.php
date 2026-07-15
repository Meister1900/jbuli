<?php

/**
 * @package     JBuli
 * @copyright   (C) 2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/goalgetterportraitv2.php';

class JFormFieldGoalgetterportraitv3 extends JFormFieldGoalgetterportraitv2
{
    protected $type = 'Goalgetterportraitv3';

    protected function getInput(): string
    {
        $input = parent::getInput();
        $notice = '<div class="alert alert-info mt-3 mb-0">Nur Bilder hochladen, für deren Nutzung Sie die erforderlichen Rechte besitzen.</div>';

        return str_replace(
            '<div class="jbuli-scorers-upload-status',
            $notice . '<div class="jbuli-scorers-upload-status',
            $input
        );
    }
}

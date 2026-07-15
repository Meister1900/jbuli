<?php

/**
 * @package     JBuli
 * @copyright   (C) 2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');


class mod_bulitorschuetzenInstallerScript
{
    public function postflight($route, $adapter): void
    {
        if (in_array($route, ['install', 'update'], true)) {
            $this->installAdministratorHelper();
        }
    }

    public function uninstall($adapter): void
    {
        $directory = JPATH_ADMINISTRATOR . '/modules/mod_bulitorschuetzen';
        $helper = $directory . '/helper.php';
        if (is_file($helper)) {
            @unlink($helper);
        }
        if (is_dir($directory) && count(array_diff(scandir($directory) ?: [], ['.', '..'])) === 0) {
            @rmdir($directory);
        }
    }

    private function installAdministratorHelper(): void
    {
        $source = JPATH_SITE . '/modules/mod_bulitorschuetzen/administrator-helper.php';
        $directory = JPATH_ADMINISTRATOR . '/modules/mod_bulitorschuetzen';
        $target = $directory . '/helper.php';
        if (!is_file($source)) {
            throw new RuntimeException('Der Administrator-Helper für den Spielerbild-Upload fehlt.');
        }
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Der Administrator-Modulordner konnte nicht erstellt werden.');
        }
        if (!@copy($source, $target)) {
            throw new RuntimeException('Der Administrator-Helper für den Spielerbild-Upload konnte nicht installiert werden.');
        }
    }
}

<?php
use Joomla\CMS\Factory as JFactory;
use Joomla\Database\DatabaseInterface;

class mod_bulitabelleInstallerScript
{
    /**
     * Constructor
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     */
    public function __construct($adapter)
    {
    }

    /**
     * Called before any type of action
     *
     * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($route, $adapter)
    {
    }

    /**
     * Called after any type of action
     *
     * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($route, $adapter)
    {
    }

    /**
     * Called on installation
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install($adapter)
    {
        $this->setupDatabase();
    }

    /**
     * Called on update
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update($adapter)
    {
        $this->setupDatabase();
    }

    /**
     * Called on uninstallation
     *
     * @param   JAdapterInstance  $adapter  The object responsible for running this script
     */
    public function uninstall($adapter)
    {
        $db = JFactory::getContainer()->get(DatabaseInterface::class);
        $query = 'DROP TABLE '.$db->quoteName('#__bulitabelle');

        $db->setQuery($query);
        $db->execute();
    }

    private function setupDatabase()
    {
        $db = JFactory::getContainer()->get(DatabaseInterface::class);
        $query = 'CREATE TABLE IF NOT EXISTS '.$db->quoteName('#__bulitabelle').' (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, team VARCHAR(100), spiele INT, gewonnen INT NOT NULL DEFAULT 0, unentschieden INT NOT NULL DEFAULT 0, verloren INT NOT NULL DEFAULT 0, tore INT, gegentore INT, punkte INT, modul_id INT, UNIQUE KEY uniq_modul_team (modul_id, team))';
        $db->setQuery($query);
        $db->execute();

        $columns = $db->getTableColumns($db->replacePrefix('#__bulitabelle'));
        foreach (['gewonnen', 'unentschieden', 'verloren'] as $column) {
            if (!isset($columns[$column])) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__bulitabelle')
                    . ' ADD ' . $db->quoteName($column) . ' INT NOT NULL DEFAULT 0';
                $db->setQuery($query)->execute();
            }
        }

        $query = 'TRUNCATE TABLE '.$db->quoteName('#__bulitabelle');
        $db->setQuery($query);
        $db->execute();

        $hasUniqueKey = false;
        foreach ($db->getTableKeys($db->replacePrefix('#__bulitabelle')) as $key) {
            $keyName = strtolower((string) ($key->Key_name ?? $key->key_name ?? ''));
            if ($keyName === 'uniq_modul_team') {
                $hasUniqueKey = true;
                break;
            }
        }
        if (!$hasUniqueKey) {
            $query = 'ALTER TABLE ' . $db->quoteName('#__bulitabelle')
                . ' ADD UNIQUE KEY ' . $db->quoteName('uniq_modul_team')
                . ' (' . $db->quoteName('modul_id') . ', ' . $db->quoteName('team') . ')';
            $db->setQuery($query)->execute();
        }

        $cachefile = JPATH_BASE."/../modules/mod_bulitabelle/cache.txt";
        if (is_readable($cachefile)) {
            unlink($cachefile);
        }
    }
}

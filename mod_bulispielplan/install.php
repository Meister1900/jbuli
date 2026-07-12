<?php
use Joomla\CMS\Factory as JFactory;
use Joomla\Database\DatabaseInterface;

class mod_bulispielplanInstallerScript
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
        $query = 'DROP TABLE IF EXISTS '.$db->quoteName('#__bulispielplan');

        $db->setQuery($query);
        $db->execute();

    }

    private function setupDatabase()
    {
        $db = JFactory::getContainer()->get(DatabaseInterface::class);
        $query = 'CREATE TABLE IF NOT EXISTS '.$db->quoteName('#__bulispielplan').' (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY, liga VARCHAR(7), bezeichnung_webservice VARCHAR(100), bezeichnung_kurz VARCHAR(100), bezeichnung_mittel VARCHAR(100), dateiname_logo VARCHAR(100))';

        $db->setQuery($query);
        $db->execute();

        $query = 'TRUNCATE TABLE '.$db->quoteName('#__bulispielplan');
        $db->setQuery($query);
        $db->execute();

        $query = "INSERT INTO ".$db->quoteName('#__bulispielplan')." VALUES
               (1, 'bl1', 'VfL Wolfsburg', 'WOL', 'Wolfsburg', 'wolfsburg.png'), 
               (2, 'bl1', 'FC Schalke 04', 'S04', 'Schalke', 'schalke.png'),
               (3, 'bl1', 'TSG 1899 Hoffenheim', 'HOF', 'Hoffenheim', 'hoffenheim.png'), 
               (4, 'bl1', 'Werder Bremen', 'BRE', 'Bremen', 'bremen.png'),
               (5, 'bl1', 'Borussia Mönchengladbach', 'BMG', 'M''Gladbach', 'gladbach.png'), 
               (6, 'bl1', 'Eintracht Frankfurt', 'FRA', 'Frankfurt', 'frankfurt.png'), 
               (7, 'bl1', '1. FSV Mainz 05', 'MAI', 'Mainz', 'mainz.png'),
               (8, 'bl1', 'SC Freiburg', 'FRE', 'Freiburg', 'freiburg.png'), 
               (9, 'bl2', 'Hamburger SV', 'HSV', 'Hamburg', 'hamburg.png'), 
               (10, 'bl2', 'Hannover 96', 'HAN', 'Hannover', 'hannover.png'), 
               (11, 'bl1', 'Borussia Dortmund', 'BVB', 'Dortmund', 'dortmund.png'), 
               (12, 'bl1', 'VfB Stuttgart', 'STU', 'Stuttgart', 'stuttgart.png'), 
               (13, 'bl1', 'FC Augsburg', 'AUG', 'Augsburg', 'augsburg.png'),
               (14, 'bl1', 'Hertha BSC', 'BSC', 'Hertha', 'hertha.png'), 
               (15, 'bl1', '1. FC Köln', 'KLN', 'Köln', 'koeln.png'),
			   (16, 'bl1', 'RB Leipzig', 'LPZ', 'Leipzig', 'leipzig.png'), 
			   (17, 'bl2', 'Fortuna Düsseldorf', 'DÜS', 'Düsseldorf', 'duesseldorf.png'), 
               (18, 'bl2', 'FC St. Pauli', 'STP', 'St. Pauli', 'pauli.png'),
               (19, 'bl1', 'VfL Bochum', 'BOC', 'Bochum', 'bochum.png'), 
               (20, 'bl2', 'SpVgg Greuther Fürth', 'FÜR', 'Fürth', 'fuerth.png'),
               (21, 'bl2', '1. FC Heidenheim 1846', 'HEI', 'Heidenheim', 'heidenheim.png'), 
               (22, 'bl2', '1. FC Nürnberg', 'NÜR', 'Nürnberg', 'nuernberg.png'), 
               (23, 'bl2', 'SV Darmstadt 98', 'DAR', 'Darmstadt', 'darmstadt.png'), 
               (24, 'bl2', 'SV Sandhausen', 'SAN', 'Sandhausen', 'sandhausen.png'),
               (25, 'bl1', '1. FC Union Berlin', 'BER', 'Berlin', 'berlin.png'),
               (26, 'bl2', 'Arminia Bielefeld', 'BIE', 'Bielefeld', 'bielefeld.png'),
      		   (27, 'bl2', 'Holstein Kiel', 'KIE', 'Kiel', 'kiel.png'),
      		   (28, 'bl2', 'Jahn Regensburg', 'REG', 'Regensburg', 'regensburg.png'),
      		   (29, 'bl1', 'FC Bayern München', 'FCB', 'Bayern', 'bayern.png'),
      		   (30, 'bl1', 'Bayer Leverkusen', 'LEV', 'Leverkusen', 'leverkusen.png'),
               (31, 'bl2', 'SC Paderborn 07', 'PAD', 'Paderborn', 'paderborn.png'),
			   (32, 'bl2', 'FC Hansa Rostock', 'ROS', 'Rostock', 'rostock.png'),
			   (33, 'bl2', 'Karlsruher SC', 'KSC', 'Karlsruhe', 'karlsruhe.png'),
               (34, 'bl2', '1. FC Kaiserslautern', 'KLA', 'Lautern', 'lautern.png'),
               (35, 'bl2', '1. FC Magdeburg', 'MAG', 'Magdeburg', 'magdeburg.png'),
               (36, 'bl2', 'Eintracht Braunschweig', 'EBS', 'Braunschweig', 'braunschweig.png'),
               (37, 'bl1', 'SV Werder Bremen', 'SVW', 'Bremen', 'bremen.png'),
               (38, 'bl1', 'TSG Hoffenheim', 'TSG', 'Hoffenheim', 'hoffenheim.png'),
               (39, 'bl1', 'SV 07 Elversberg', 'ELV', 'Elversberg', 'elversberg.png'),
               (40, 'bl2', 'Dynamo Dresden', 'SGD', 'Dresden', 'dresden.png'),
               (41, 'bl2', 'Energie Cottbus', 'FCE', 'Cottbus', 'cottbus.svg'),
               (42, 'bl2', 'VfL Osnabrück', 'OSN', 'Osnabrück', 'osnabrueck.png'),
               (43, 'bl2', 'DSC Arminia Bielefeld', 'DSC', 'Bielefeld', 'bielefeld.png'),
               (44, 'bl1', 'Bayer 04 Leverkusen', 'B04', 'Leverkusen', 'leverkusen.png'),
               (45, 'bl2', 'Preußen Münster', 'SCP', 'Münster', 'muenster.svg');
			   ";

        $db->setQuery($query);
        $db->execute();

        // Ligazugehörigkeit 2026/27 für bestehende und neue OpenLigaDB-Namen.
        $leagueUpdates = [
            'bl1' => ['Hamburger SV', 'SC Paderborn 07', 'FC Schalke 04', 'SV 07 Elversberg', 'SV Werder Bremen', 'TSG Hoffenheim'],
            'bl2' => ['VfL Wolfsburg', '1. FC Heidenheim 1846', 'FC St. Pauli', 'Hannover 96', 'SV Darmstadt 98', '1. FC Kaiserslautern', 'Hertha BSC', '1. FC Nürnberg', 'VfL Bochum', 'Karlsruher SC', 'Dynamo Dresden', 'Holstein Kiel', 'DSC Arminia Bielefeld', '1. FC Magdeburg', 'Eintracht Braunschweig', 'SpVgg Greuther Fürth', 'VfL Osnabrück', 'Energie Cottbus'],
        ];
        foreach ($leagueUpdates as $league => $names) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bulispielplan'))
                ->set($db->quoteName('liga') . ' = ' . $db->quote($league))
                ->where($db->quoteName('bezeichnung_webservice') . ' IN (' . implode(',', $db->quote($names)) . ')');
            $db->setQuery($query)->execute();
        }

        foreach (glob(JPATH_BASE."/../modules/mod_bulispielplan/cache_*.txt") as $cachefile) {
            if (is_file($cachefile)) {
                @unlink($cachefile);
            }
        }
        foreach (glob(JPATH_CACHE . '/mod_bulispielplan_*.json') as $cachefile) {
            if (is_file($cachefile)) {
                @unlink($cachefile);
            }
        }
        foreach (glob(JPATH_CACHE . '/mod_bulispielplan_*.lock') as $lockfile) {
            if (is_file($lockfile)) {
                @unlink($lockfile);
            }
        }
    }
}

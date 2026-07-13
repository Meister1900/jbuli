<?php

/**
 * @package     JBuli
 * @copyright   (C) 2014-2026 Markus Krupp
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jbuli\Module\Bulispielplan\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

class TeamsField extends ListField
{
    protected $type = 'Teams';

    protected function getOptions(): array
    {
        $data = $this->form->getData();
        $league = (string) $data->get('params.league', 'bl1');
        $season = (int) $data->get('params.season', 2026);
        $longNames = (string) $data->get('params.longnames', '0') === '1';
        $selectedTeam = (string) $data->get('params.meinVerein', '');
        $league = in_array($league, ['bl1', 'bl2'], true) ? $league : 'bl1';

        $json = $this->fetch('https://api.openligadb.de/getavailableteams/' . $league . '/' . $season);
        $teams = $json ? json_decode($json) : [];
        $options = [];

        foreach ((array) $teams as $team) {
            if (empty($team->teamName)) {
                continue;
            }
            $label = $longNames ? $team->teamName : ($team->shortName ?: $team->teamName);
            $options[] = HTMLHelper::_('select.option', $team->teamName, $label);
        }

        if ($selectedTeam !== '' && !array_filter($options, static fn($option): bool => $option->value === $selectedTeam)) {
            $options[] = HTMLHelper::_('select.option', $selectedTeam, $selectedTeam);
        }

        usort($options, static fn($a, $b): int => strcasecmp($a->text, $b->text));

        return array_merge(
            [HTMLHelper::_('select.option', '', 'Bitte Verein wählen')],
            $options
        );
    }

    private function fetch(string $url): string|false
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Joomla/6 mod_bulispielplan backend',
        ]);
        $content = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        return $content !== false && $status >= 200 && $status < 300 ? $content : false;
    }
}

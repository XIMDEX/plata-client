<?php
/**
 *  \details &copy; 2011  Open Ximdex Evolution SL [http://www.ximdex.org]
 *
 *  Ximdex a Semantic Content Management System (CMS)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  See the Affero GNU General Public License for more details.
 *  You should have received a copy of the Affero GNU General Public License
 *  version 3 along with Ximdex (see LICENSE file).
 *
 *  If not, visit http://gnu.org/licenses/agpl-3.0.html.
 *
 * @author Ximdex DevTeam <dev@ximdex.com>
 * @version $Revision$
 */

namespace core;

class Config
{
    private const CONFIG_FILE = '/.config';

    private static function open(bool $groups = false)
    {
        $file = dirname(dirname(__FILE__)).self::CONFIG_FILE;
        $result = parse_ini_file($file, $groups);
 
        return $result;
    }
    
    public static function get(string $config = '')
    {
        $configs = self::open();

        if (!empty($config) && array_key_exists($config, $configs)) {
            $configs = $configs[$config];
        }

        return $configs;
    }

    public static function getGroup(string $group = '')
    {
        $configs = self::open(true);

        if (!empty($group) && array_key_exists($group, $configs)) {
            $configs = $configs[$group];
        }

        return $configs;
    }

    public static function rejectGroup(string $group = '')
    {
        $configs = self::open(true);
        
        foreach($configs as $_group => $config) {
            if($group === $_group) {
                unset($configs[$_group]);
            }
        }

        return $configs;
    }
}

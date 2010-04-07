<?php
/**
 * Copyright 2010 Cyrille Mahieux
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations
 * under the License.
 *
 * ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°> ><)))°>
 *
 * Configuration class for editing, saving, ...
 *
 * @author c.mahieux@of2m.fr
 * @since 05/04/2010
 */
class Library_Configuration implements ArrayAccess
{
    private static $_instance = null;
    private static $_iniPath = 'config/memcache.ini';
    private static $_iniKeys = array('stats',
                                     'slabs',
                                     'items',
                                     'get',
                                     'delete',
                                     'serverd',
                                     'connection_timeout',
                                     'max_item_dump');
    private static $_ini;

    /**
     * Constructor of MemCacheAdmin_Configuration class
     * Load ini file
     *
     * @return void
     */
    private function __construct()
    {
        # Opening ini file
        self::$_ini = parse_ini_file(self::$_iniPath);

        # Initializing server list
        foreach(self::$_ini['server'] as $key => $server)
        {
            # Exploding by server:port
            $server = explode(':', $server);
            unset(self::$_ini['server'][$key]);
            self::$_ini['server'][$server[0]] = $server[1];
        }
    }

    /**
     * Get MemCacheAdmin_Configuration singleton
     *
     * @return MemCacheAdmin_Configuration
     */
    public static function getInstance()
    {
        if(!isset(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset Offset to get
     *
     * @return boolean
     */
    public function offsetGet($offset)
    {
        if(isset(self::$_ini[$offset]))
        {
            return self::$_ini[$offset];
        }
        else
        {
            return null;
        }
    }

    /**
     * Offset to set
     *
     * @param mixed $offset Offset to set
     * @param mixed $value Value to set
     *
     * @return boolean
     */
    public function offsetSet($offset, $value)
    {
        self::$_ini[$offset] = $value;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset(self::$_ini[$offset]);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to unset
     *
     * @return boolean
     */
    public function offsetUnset($offset)
    {
        unset(self::$_ini[$offset]);
    }

    /**
     * Check if every ini keys are set
     * Return true if ini is correct, false otherwise
     *
     * @return boolean
     */
    public static function check()
    {
        # Checking configuration keys
        foreach(self::$_iniKeys as $iniKey)
        {
            # Ini file key not set or server not an array @todo Fix the method
            if((!isset(self::$_ini[$iniKey])) || (($iniKey == 'server') && (!is_array(self::$_ini['server']))))
            {
                return false;
            }
        }
    }

    /**
     * Write ini file
     * Return true if written, false otherwise
     *
     * @return boolean
     */
    public static function write()
    {
        if(self::check())
        {
            $iniContent = array();
            foreach(self::$_ini as $iniKey => $iniValue)
            {
                $iniContent[] = '[' . $iniKey . ']';
                if(is_array($iniValue))
                {
                    foreach($iniValue as $subIniValue)
                    {
                        $iniContent[] = $iniKey . '[] = "' . $subIniValue . '"';
                    }
                }
                else
                {
                    $iniContent[] = $iniKey . ' = "' . $iniValue . '"';
                }
            }
            return is_numeric(file_put_contents(self::$_iniPath, implode("\r\n", $iniContent)));
        }
        return false;
    }
}
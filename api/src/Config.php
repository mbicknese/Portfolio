<?php
namespace MBicknese\Portfolio;

class Config
{
    
    /**
     * Array of all values stored in the configuration
     * @var Mixed[]
     */
    private static $values;

    /**
     * Loads all config files from given directory
     * @param  string $config_dir Absolute path to load config files from
     */
    public static function loadConfig($config_dir)
    {

        self::$values = array();
        $config_files = glob($config_dir . '/*.ini');
        foreach ($config_files as $file) {
            self::$values = array_merge(
                self::$values,
                parse_ini_file($file)
            );
        }
    }

    /**
     * Retrieves a configuration value
     * @param  string $name Name of the configuration value as set in the ini file
     * @return mixed        Value of requested configuration name or null if it doesn't exist
     */
    public static function get($name)
    {
        if (isset(self::$values[$name])) {
            return self::$values[$name];
        }
        return null;
    }
}

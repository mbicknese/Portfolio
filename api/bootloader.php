<?php

/**
 * Define some globals which are usefull in the pre-OOP fase
 */
define('DIR_ROOT', dirname(__DIR__));
define('DIR_SRC',  DIR_ROOT . '/api/src'); 

/**
 * Get Composers autoloading to work for us
 */
require DIR_ROOT . '/vendor/autoload.php';

/**
 * Load all configuration files
 */
\MBicknese\Portfolio\Config::loadConfig(DIR_ROOT . '/api/config');

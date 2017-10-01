<?php
// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';

\Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
     'Pref' => APPPATH.'classes/pref.php'
));

// Register the autoloader
\Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
\Fuel::$env = \Arr::get($_SERVER, 'FUEL_ENV', \Arr::get($_ENV, 'FUEL_ENV', \Fuel::DEVELOPMENT));

// Initialize the framework with the config file.
\Fuel::init('config.php');

// 独自定数
require APPPATH . 'config' . DIRECTORY_SEPARATOR . 'constant.php';
require APPPATH . 'config' . DIRECTORY_SEPARATOR . \FUEL::$env . DIRECTORY_SEPARATOR . 'constant.php';

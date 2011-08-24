<?php

// Set include paths
define('PROJECT_ROOT', realpath(__DIR__ .'/../'));
define('LIBRARY_PATH', realpath(\PROJECT_ROOT .'/lib'));
define('APPLICATION_ENV', 'development');

// Include Paths
$includePaths = array(
    get_include_path(),
    \LIBRARY_PATH, 
    '/usr/share/php/libzend-framework-php/'
);
set_include_path(
    implode(
        PATH_SEPARATOR,
        $includePaths
    )
);

// Custom Autoloader
function autoload($className) {
	foreach($GLOBALS['includePaths'] as $path) {
		$classNamespaced = $path .'/' . str_replace('\\', '/', $className) . '.php';
		$classConvention = $path . '/' . str_replace('_','/',$className) . '.php';
		$classParent = $path . '/' . substr($className, 0, strrpos($className, '/')) . '.php';
		if (file_exists($classNamespaced)) {
//		    echo "\t--: " . $classNamespaced . "\n";
			include_once ($classNamespaced);
		} elseif (file_exists($classConvention)) {
//		    echo "\t--: " . $classConvention . "\n";
			include_once($classConvention);
        } elseif (file_exists($classParent)) {
//            echo "\t--: " . $classParent . "\n";
            include_once($classParent);
        }
	}
}
spl_autoload_register('autoload');
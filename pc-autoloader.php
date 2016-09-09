<?php

/*
 * We maintain a class map here to autoload and required files.
 *
 * @param $class
 *   The class name.
 */
function __autoload($class) {
   
    $classes = array(
        'PropertyCompassAdmin'  => 'admin/pc-admin.php'
    );

    if(isset($classes[$class])) {
        require_once dirname(__FILE__) . '/' . $classes[$class];
    }
}
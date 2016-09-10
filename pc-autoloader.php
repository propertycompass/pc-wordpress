<?php
function __autoload($class) {

    $classes = array(
        'PC_PostTypes'        => 'config/pc-post-types.php',
        'PC_Admin'             => 'admin/pc-admin.php'
    );

    if(isset($classes[$class])) {
    	echo dirname(__FILE__) . '/' . $classes[$class];
        require_once dirname(__FILE__) . '/' . $classes[$class];
    }
    exit;
}
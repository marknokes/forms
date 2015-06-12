<?php
include 'constants.php';

function __autoload( $class_name ) {
    include 'classes' . DIRECTORY_SEPARATOR . $class_name . '.php';
}

session_start();
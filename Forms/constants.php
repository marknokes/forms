<?php

define( 'ABS_PATH', __DIR__ );

/*
* Here's my best attempt at normalizing this data in different
* environments to retrieve a proper relative path!
*/
$abs_path = preg_replace( "|[\/\\\\]|", "/", __DIR__ );

$doc_root = trim( preg_replace( "|[\/\\\\]|", "/", $_SERVER['DOCUMENT_ROOT'] ), "/" );

define( 'REL_PATH', str_replace( $doc_root, "", $abs_path ) );
<?php

include './loader.php';

$return = 0;

try {

    $Form = \Form\Form::get_instance();

    $return = $Form->validate_submission();

} catch( Exception $e ) {

    $return = json_encode( array(
        'exception' => true,
        'message' => $e->getMessage()
    ) );
    
}

echo $return;

die;
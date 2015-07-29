<?php

include './loader.php';

$return = '0';

$Form = \Form\Form::get_instance();

switch( $_POST['ajax_action'] )
{
    case "process_form" :

		$return = $Form->validate_submission();

	    break;

    case "ldap_auth" :

        if ( !isset( $_POST['username'], $_POST['password'], $Form  ) ) {
            $return = '0';
            break;
        }

        $response = $Form->authorize();

        if ( true === $response )
            // success action
            $return = json_encode( $Form->ldap_auth['success'] );
        elseif ( false === $response )
            // Incorrect username or password
            $return = '3';
        elseif ( '0' === $response )
            // There was an unexpected error (can't connect to LDAP server)
            $return = '0';

        if ( true === $response && isset( $Form->ldap_auth['success']['set_session'],
            $Form->ldap_auth['success']['set_session']['key'],
            $Form->ldap_auth['success']['set_session']['value']
        ) )
            $_SESSION[ $Form->ldap_auth['success']['set_session']['key'] ] = $Form->ldap_auth['success']['set_session']['value'];

        break;
}

echo $return;
die;
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

        $return = $Form->authorize() ? json_encode( $Form->ldap_auth['success'] ) : '3';

        if ( '3' !== $return && isset( $Form->ldap_auth['success']['set_session'],
            $Form->ldap_auth['success']['set_session']['key'],
            $Form->ldap_auth['success']['set_session']['value']
        ) )
            $_SESSION[ $Form->ldap_auth['success']['set_session']['key'] ] = $Form->ldap_auth['success']['set_session']['value'];

        break;
}

echo $return;
die;
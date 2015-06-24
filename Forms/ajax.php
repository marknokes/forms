<?php

include './loader.php';

$return = '0';

switch( $_POST['ajax_action'] )
{
    case "process_form" :
        
        if ( isset( $_SESSION['form'] ) )
        {
    		$Form = $_SESSION['form'];
			$return = $Form->validate_submission();
		}

	    break;

    case "ldap_auth" :

        if ( !isset( $_POST['username'], $_POST['password'], $_SESSION['form']  ) ) {
            $return = '0';
            break;
        }

        $LDAP_Auth = $_SESSION['form'];

        $return = $LDAP_Auth->authorize() ? json_encode( $LDAP_Auth->ldap_auth['success'] ) : '3';

        if ( '3' !== $return )
            $_SESSION['ldap_authed'] = true;

        break;
}

echo $return;
die;
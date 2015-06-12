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

        if ( !isset( $_POST['ad_groups'], $_POST['success_action'], $_POST['success_data'], $_POST['username'], $_POST['password'] )
            || empty( $_POST['ad_groups'] )
            || empty( $_POST['success_action'] )
            || empty( $_POST['success_data'] ) )
        {
            $return = '0';
            break;
        }

        $LDAP_Auth = new \Form\LDAP_Auth( $_POST['ad_groups'] );

        $success_action = json_encode( array(
            'success_action' => $_POST['success_action'],
            'success_data'   => $_POST['success_data']
        ) );

        $return = $LDAP_Auth->authorize() ? $success_action : '3';
        
        break;
}

echo $return;
die;
<?php
    
require_once 'Forms/loader.php';

$options = array(
    'tmp_dir'               => 'C:\\tmp\\', // required for sending email attachments. default is C:\tmp\
    'id'                    => 'form-css-id', // required. default is form-css-id
    'email'                 => true, // optional. default is true
    'to'                    => 'mnokes@uco.edu', // required if email set to true above
    'default_from'          => 'mnokes@uco.edu', // optional. if there's an email field in the form with replyTo = true, the form submitter's email will be used
    'default_from_name'     => 'Test Form', // optional
    'subject'               => 'Email Subject HERE', // required if email set to true above
    'stylesheet_path'       => 'styles.css', // optional. use this option to override bootstrap styles
    'recaptcha'             => false, // required. default is false
    'recaptcha_site_key'    => '', // optional. required if recaptcha = true
    'recaptcha_secret_key'  => '', // optional. required if recaptcha = true

    /**
    * Use these options if using $Form = new \Form\LDAP_Auth();
    *
    * 'ldap_auth' => array(
    *     'ad_server'   => 'ldap.server.hostname',
    *     'base_dn'     => 'DC=dn,DC=base',
    *     'ldap_user'   => 'CN=name,CN=distinguished,DC=user,DC=ldap',
    *     'ldap_pass'   => 'ldap_user_password',
    *     'search'      => array(
    *         'memberOf=CN=,OU=,OU=',
    *         'memberOf=CN=,OU=,OU=',
    *     ),
    *     'success'     => array(
    *         'action'      => 'Redirect',
    *         'data'        => 'http://www.domain.com',
    *         'set_session' => array(
    *           'key' => 'authorized',
    *           'value' => true
    *          )
    *     )
    * ),
    */
    
    /**
    * Use these options to save data to Hannon Hill's Cascade Server
    *
    * 'save'                  => array(
    *     'to_save' => array( 
    *         'path'     => '/path/to/folder',
    *         'fileName' => 'filename.csv'
    *     ),
    *     'site' => 'Cascade Server Website Name'
    * ),
    * 'soapURL'               => '', // optional
    * 'auth'                  => array( // optional
    *     'username' => '',
    *     'password' => ''
    * ),
    */
);

$fields = array(

    'ajax_action' => array( // required. value is either process_form or ldap_auth
        'fieldName'     => 'ajax_action',
        'type'          => 'hidden',
        'default_value' => 'process_form'
    ),

    /**
    * Use these fields if ajax_action is set to ldap_auth. Right now it only does a redirect, however
    * you may have it save a cookie, set a session var, or something else.
    *
    *   'username' => array(
    *       'fieldName' => 'Username',
    *       'type'      => 'text'
    *   ),
    *   'password' => array(
    *       'fieldName' => 'Password',
    *       'type'      => 'password'
    *   ),
    */

    /**
    * Field type options
    */
    
    'field-0' => array(
        'fieldName'             => 'Email',
        'type'                  => 'email',
        'required'              => true,
        'cloneable'             => false,
        'send_email_attachment' => false,
        'replyTo'               => true
    ),
    'field-1' => array(
        'fieldName' => 'Text',
        'type'      => 'text',
        'required'  => false,
        'cloneable' => false
    ),
    'field-2' => array(
        'fieldName' => 'Cloneable',
        'type'      => 'text',
        'required'  => false,
        'cloneable' => true
    ),
    // 'field-3' => array(
    //     'fieldName'     => 'WYSIWYG',
    //     'type'          => 'message',
    //     'default_value' => '<h1>Heading</h1><p>Here\'s a message to be displayed on the form. This can be before or after any field.</p>'
    // ),
    'field-4' => array(
        'fieldName'             => 'Signature One',
        'type'                  => 'signature-pad',
        'required'              => true,
        'cloneable'             => false
    )
    // 'field-4' => array(
    //     'fieldName' => 'Textarea',
    //     'type'      => 'textarea',
    //     'required'  => false
    // ),
    // 'field-5' => array(
    //     'fieldName'     => 'Checkboxes',
    //     'type'          => 'checkbox',
    //     'required'      => false,
    //     'default_value' => 'Checked',
    //     'options'       => array(
    //         'Value One',
    //         'Value Two'
    //     )
    // ),
    // 'field-6' => array(
    //     'fieldName' => 'Radio Buttons',
    //     'type'      => 'radio',
    //     'required'  => false,
    //     'options'   => array(
    //         'Option 1',
    //         'Option 2',
    //         'Option 3'
    //     )
    // ),
    // 'field-7' => array(
    //     'fieldName' => 'Datepicker',
    //     'type'      => 'datepicker',
    //     'required'  => false
    // ),
    // 'field-8' => array(
    //     'fieldName' => 'Timepicker',
    //     'type'      => 'timepicker',
    //     'required'  => false
    // ),
    // 'field-9' => array(
    //     'fieldName' => 'Select',
    //     'type'      => 'dropdown',
    //     'required'  => false,
    //     'options'   => array(
    //         'Value One',
    //         'Value Two',
    //         'Value Three'
    //     )
    // ),
    // 'field-10' => array(
    //     'fieldName' => 'Multi Select',
    //     'type'      => 'dropdown',
    //     'multiple'  => true,
    //     'required'  => false,
    //     'options'   => array(
    //         'Value One',
    //         'Value Two',
    //         'Value Three',
    //         'Value Four'
    //     )
    // )
);

$Form = new \Form\Form( $options, $fields );

/**
*  Use this for an LDAP auth form
*  $Form = new \Form\LDAP_Auth( $options, $fields );
*/

?>

<html>

	<head>

		<?php $Form->print_styles(); ?>

	</head>

	<body>

		<?php $Form->get(); ?>

	</body>

	<?php $Form->print_scripts(); ?>

</html>
<html>

    <head>

        <title>Test Form</title>
        
        <meta content="text/html;charset=UTF-8" http-equiv="Content-type"/>
        
        <?php
    
        require_once 'Forms/loader.php';

        $Form = new \Form\Form();

        $Form->options = array(
            'tmp_dir'               => 'C:\\tmp\\', // required for sending email attachments. default is C:\tmp\
            'id'                    => 'form-css-id', // required. default is form-css-id
            'email'                 => true, // optional. default is true
            'to'                    => 'address@domain.com', // required if email set to true above
            'default_from'          => 'address@domain.com', // optional. if there's an email field in the form with replyTo = true, the form submitter's email will be used
            'default_from_name'     => 'Name', // optional
            'subject'               => 'Email Subject', // required if email set to true above
            'stylesheet_path'       => 'styles.css', // optional. use this option to override bootstrap styles
            'recaptcha'             => false, // required. default is false
            'recaptcha_site_key'    => '', // optional. required if recaptcha = true
            'recaptcha_secret_key'  => '', // optional. required if recaptcha = true
            
            /**
            *
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
            * )
            */
        );

        $Form->fields = array(

            'ajax_action' => array( // required. value is either process_form or ldap_auth
                'fieldName'     => 'ajax_action',
                'type'          => 'hidden',
                'default_value' => 'process_form'
            ),

            /**
            * Use these fields if ajax_action is set to ldap_auth. Right now it only does a redirect, however
            * you may have it save a cookie, set a session var, or something else.
            *
            *   'ad_groups' => array(
            *       'fieldName'     => 'ad_groups[]',
            *       'type'          => 'hidden',
            *       'default_value' => array(
            *           'memberOf=CN=Group,OU=Org_unit,OU=Org_unit,OU=Org_unit',
            *           'memberOf=CN=Group,OU=Org_unit,OU=Org_unit,OU=Org_unit',
            *       )
            *   ),
            *   'success_action' => array(
            *       'fieldName'     => 'success_action',
            *       'type'          => 'hidden',
            *       'default_value' => 'Redirect'
            *   ),
            *   'success_data' => array(
            *       'fieldName'     => 'success_data',
            *       'type'          => 'hidden',
            *       'default_value' => 'http://www.google.com'
            *   ),
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
            'field-3' => array(
                'fieldName'     => 'WYSIWYG',
                'type'          => 'message',
                'default_value' => '<h1>Heading</h1><p>Here\'s a message to be displayed on the form. This can be before or after any field.</p>'
            ),
            'field-4' => array(
                'fieldName' => 'Textarea',
                'type'      => 'textarea',
                'required'  => false
            ),
            'field-5' => array(
                'fieldName'     => 'Checkboxes',
                'type'          => 'checkbox',
                'required'      => false,
                'default_value' => 'Checked',
                'options'       => array(
                    'Value One',
                    'Value Two'
                )
            ),
            'field-6' => array(
                'fieldName' => 'Radio Buttons',
                'type'      => 'radio',
                'required'  => false,
                'options'   => array(
                    'Option 1',
                    'Option 2',
                    'Option 3'
                )
            ),
            'field-7' => array(
                'fieldName' => 'Datepicker',
                'type'      => 'datepicker',
                'required'  => false
            ),
            'field-8' => array(
                'fieldName' => 'Timepicker',
                'type'      => 'timepicker',
                'required'  => false
            ),
            'field-9' => array(
                'fieldName' => 'Select',
                'type'      => 'dropdown',
                'required'  => false,
                'options'   => array(
                    'Value One',
                    'Value Two',
                    'Value Three'
                )
            ),
            'field-10' => array(
                'fieldName' => 'Multi Select',
                'type'      => 'dropdown',
                'multiple'  => true,
                'required'  => false,
                'options'   => array(
                    'Value One',
                    'Value Two',
                    'Value Three',
                    'Value Four'
                )
            )
        );

        $Form->init();
    ?>
    
    <?php $Form->print_styles(); ?>
    
    </head>
    
    <body>
        
        <?php $Form->get(); ?>
    
    </body>

    <?php $Form->print_scripts(); ?>

</html>
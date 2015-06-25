<?php

/**
*    This is a PHP library that allows the simple creation of web forms. It additionaly has support for Cascade Server integration.
*    @copyright Copyright (c) 2015  Mark Nokes
*    @link      https://github.com/marknokes
* 
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see http://www.gnu.org/licenses/.
*/

namespace Form;

class Form {

    /**
    * The form object instance
    * @var object
    */
    public static $_instance;

    /**
    * An array which will contain the form fields and their related options
    * @var array
    */
    public $fields = array();

    /**
    * An array which will contain the form options
    * @var array
    */
    public $options = array();
    
    /**
    * Temporary directory for email file attachment creation
    * @var string
    */
    private $tmp_dir = 'C:\\tmp\\';

    /**
    * Absolute path to the template directory
    * @var string
    */
    private $template_dir = __DIR__ . '/../../templates/';

    /**
    * The CSS id of the form.
    * @var string
    */
    private $id = 'form-css-id';

    /**
    * Whether or not to email the form results
    * @var bool
    */
    private $email = true;
    
    /**
    * The email address to which an email of the form data may be sent
    * @var string
    */
    private $to = '';

    /**
    * The default email address from which an email of the form data will be sent if not specified
    * @var string
    */
    private $default_from = '';

    /**
    * The email address from which an email of the form data may be sent
    * @var string
    */
    private $default_from_name = '';

    /**
    * The email address from which an email of the form data may be sent. This will be determined by an email field with the replyTo option.
    * @var string
    */
    private $from = '';

    /**
    * The email subject
    * @var string
    */
    private $subject = '';

    /**
    * Optional relative path to a CSS stylesheet which may be used to override default bootstrap styles
    * @var string
    */
    private $stylesheet_path = '';

    /**
    * Boolean representing whether or not the form should contain a Google ReCaptcha
    * @var bool
    */
    private $recaptcha = false;

    /**
    * Optional Google ReCaptcha public key
    * @var string
    */
    private $recaptcha_site_key = '';

    /**
    * Optional Google ReCaptcha private key
    * @var string
    */
    private $recaptcha_secret_key = '';

    /**
    * Optional WSDL URL for interacting with Cascade Server web services API.
    * @var string
    */
    private $soapURL = "";

    /**
    * Optional SOAP auth for interacting with Cascade Server web services API.
    * @var string
    */
    private $auth = array( 'username' => '', 'password' => '' );

    /**
    * An array of two keys, html and text, that hold the formatted message content to be emailed/attached
    * @var array
    */
    private $message_content = array();

    /**
    * Determines if the form data should be sent as a .txt file attachment to the form submitter. Email field is required and the key should be used as "from".
    * @var bool
    */
    private $send_email_attachment = false;

    /**
    * Determines if the form data should saved to Cascade Server as .csv file
    * @var bool
    */
    private $save = false;
    
    /**
    * Create a configured instance to use the UCO_Form class.
    *
    * @param array $options An array of form options.
    * @param array $fields An array of form fields.
    */
    public function __construct( $options, $fields )
    {
        foreach( $options as $key => $value )
            $this->$key = $value;

        $this->fields = $fields;

        $this->set_email_vars();
        // For calling validate_submission() in object context from our ajax.php file.
        $_SESSION['form'] = $this;
    }

    /**
    * Get a configured instance to use the UCO_Form class.
    */
    public static function get_instance()
    {
        if ( isset( $_SESSION['form'] ) )
            self::$_instance = $_SESSION['form'];
        elseif ( !( self::$_instance instanceof self ) )
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
    * Setup $this->send_email_attachment and $this->from vars.
    * 
    * If sendAttachment is true on an email field type and the field is not cloneable set the
    * value to the key. Then later we can send the email to $_POST[ key ] which will be
    * equal to the form submitters email address. Same is true for $this->from.
    *
    * @return null
    */
    private function set_email_vars()
    {
        foreach( $this->fields as $key => $field )
        {
            if ( "email" === $field['type'] )
            {   
                // We aren't going to send the email attachement to multiple email addresses, although we certainly could.
                $this->send_email_attachment = !empty( $field['send_email_attachment'] ) && $field['cloneable'] !== true ? $key : false;

                $this->from = !empty( $field['replyTo'] ) ? $key : $this->$default_from;
            }
        }

        return;
    }
    
    /**
    * Retrieves template part, makes variables available to template.
    *
    * @param string $part The name of the template. This should reside in the template directory set above.
    * @param array $vars An array of variables with the key as the variable name and value as value.
    * @param bool $return_str TRUE will result in a string value FALSE calls include
    * @return mixed TRUE will result in a string value FALSE calls include and returns null.
    */
    private function get_template_part( $part = '', $vars = array(), $return_str = false )
    {
        if ( !$part ) return '';
        
        $tpl = $this->template_dir . $part . '.php';

        if( !file_exists( $tpl ) )
            return '';

        if ( $vars )
            extract( $vars );
        
        if ( $return_str )
        {
            ob_start();
            include $tpl;
            return ob_get_clean();
        }
        else
        {
            include $tpl;
        }
    }
    
    /**
    * Creates HTML for fields.
    *
    * There should be a template in the template dir to match a provided field type.
    *
    * @return string Outputs HTML
    */
    public function list_fields()
    {

        $no_form_label = array( 'hidden', 'message' );

        ob_start();

        foreach( $this->fields as $field_id => $data )
        {
            echo '<div class="form-group">';
            
            $tpl_vars = array(
                 'id'            => $field_id,
                 'field_name'    => $data['fieldName'],
                 'required'      => !empty( $data['required'] ),
                 'default_value' => !empty( $data['default_value'] ) ? $data['default_value'] : '',
                 'type'          => $data['type'],
                 'cloneable'     => !empty( $data['cloneable'] ),
                 'multiple'      => !empty( $data['multiple'] ) && true === $data['multiple']
            );

            if ( isset( $data['type'] ) && !in_array( $data['type'], $no_form_label ) )
                $this->get_template_part('form-label', $tpl_vars );
            
            switch( $data['type'] )
            {
                case 'checkbox' :
                case 'radio' :
                case 'dropdown' :
                    $options = isset( $data['options'] ) ? $data['options'] : false;
                    if ( false === $options ) break;
                    $this->get_template_part( $data['type'], array_merge( array( 'options' => $options ), $tpl_vars ) );
                    break;
                default :
                    $this->get_template_part( $data['type'], $tpl_vars );
            }
            
            echo '</div>';
        }
        return ob_get_clean();
    }

    /**
    * Creates a .txt file and sends email with attachment to form submitter
    *
    * @param string $text Text data to attach to email.
    * @param string $mail_to Email address to which an email should be sent.
    * @return bool TRUE on success FALSE on failure
    */
    private function send_form_data_to_user( $text, $mail_to )
    {
        // The tmp directory var is empty or it doesn't exist and we couldn't create it
        if ( empty( $this->tmp_dir ) || ( !file_exists( $this->tmp_dir ) && !mkdir( $this->tmp_dir ) ) )
            return false;

        $file_name = time() . ".txt";
        $file = $this->tmp_dir.$file_name;

        // create tmp file
        $tmp = fopen( $file, "w" );
        fwrite( $tmp, $text );
        fclose( $tmp );

        // details
        $from = $this->default_from_name . "<" . $this->default_from . ">";
        $subject = "Form generated email";
        $message = "";
        $message_body = "Please print, sign, and deliver the attachment to the Service Desk.";
        $file_size = filesize( $file );
        $handle = fopen( $file, "r" );
        $content = fread( $handle, $file_size );
        fclose( $handle );
        $content = chunk_split( base64_encode( $content ) );
        $boundary = md5( uniqid( time() ) );

        // headers
        $headers = "From: " . $from . PHP_EOL;
        $headers .= "Reply-To: " . $from . PHP_EOL;
        $headers .= "MIME-Version: 1.0" . PHP_EOL;
        $headers .= "Content-Type: multipart/mixed;" . PHP_EOL;
        $headers .= " boundary=\"".$boundary."\"";
         
        // content
        $message .= "--".$boundary . PHP_EOL;
        $message .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . PHP_EOL;
        $message .= "Content-Transfer-Encoding: 7bit" . PHP_EOL . PHP_EOL;
        $message .= $message_body . PHP_EOL;
         
        // attachment
        $message .= "--".$boundary . PHP_EOL;
        $message .= "Content-Type: text/plain;" . PHP_EOL;
        $message .= " name=\"".$file_name."\"" . PHP_EOL;
        $message .= "Content-Transfer-Encoding: base64" . PHP_EOL;
        $message .= "Content-Disposition: attachment;" . PHP_EOL;
        $message .= " filename=\"".$file_name."\"" . PHP_EOL . PHP_EOL;
        $message .= $content . PHP_EOL;
        $message .= "--".$boundary."--" . PHP_EOL;

        // send
        $bool = mail( $mail_to, $subject, $message, $headers );

        // delete tmp file
        unlink( $file );

        return $bool;
        
    }

    /**
    * Populate the message_content var to be used in email body and/or attachment
    *
    * @return null
    */
    private function set_message_content()
    {

        $html = "<table style='border-collapse:collapse; border: 1px solid #999'>";

        $text = "";

        foreach ( $_POST as $key => $data )
        {   
            if ( is_array( $data ) )
                $data = implode( "; ", $data );
            else
                $data = $data;

            $html .= "<tr>
                                <td style='border: 1px solid #999; padding: 4px;'>" . $this->fields[$key]['fieldName']  . "</td>
                                <td style='border: 1px solid #999; padding: 4px;'>" . nl2br( $data ) . "</td>
                            </tr>";
            $text .= $this->fields[$key]['fieldName'] . ": " . $data . PHP_EOL;
        }

        $html .= "</table>";

        $text .= PHP_EOL . "Date: _____________________________________________________" . PHP_EOL;
        $text .= PHP_EOL . "Signature: _____________________________________________________" . PHP_EOL;
        $text .= PHP_EOL . "Supervisor Signature: _____________________________________________________" . PHP_EOL;

        $this->message_content = array(
            "html" => $html,
            "text" => $text
        );
    }
    
    /**
    * Sends submitted form data to $to user.
    *
    * @return bool TRUE on success FALSE on failure
    */
    private function send()
    {           
        $from = isset( $_POST[ $this->from ] ) && !is_array( $_POST[ $this->from ] ) ? $_POST[ $this->from ] : $this->from;

        $headers = 'From:' . $from . PHP_EOL .
            'Reply-To: '. $from . PHP_EOL .
            'X-Mailer: PHP/' . phpversion() . PHP_EOL .
            'MIME-Version: 1.0' . PHP_EOL .
            'Content-type: text/html; charset=iso-8859-1';

        $msg_vars = array(
            'subject' => $this->subject,
            'html' => $this->message_content["html"]
        );

        $message = $this->get_template_part('html-email', $msg_vars, $return = true );

        return mail( $this->to, $this->subject, $message, $headers );
    }

    /**
    * Main validation method. Captcha check, field checks, etc. It needs help.
    *
    * @return string JSON encoded string for processing in ajax.php
    */
    public function validate_submission()
    {   
        // recaptcha check
        if ( true === $this->recaptcha && !isset( $_SESSION['recaptcha_correct'] ) )
        {
            if ( !empty( $_POST["g-recaptcha-response"] ) )
            {
                $recaptcha = new \ReCaptcha\ReCaptcha( $this->recaptcha_secret_key );

                $resp = $recaptcha->verify( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'] );

                if ( $resp->isSuccess() )
                    $_SESSION['recaptcha_correct'] = true;
                else
                    return 'captcha_error';
            }
            else
                return 'captcha_error';
        }
        
        // field validation
        $required = array();
        
        foreach ( $this->fields as $field_id => $data )
        {
            // return value should be numeric string or json
            $return = '0';

            /**
            * Other field validation may be performed here. Just
            * be sure to return some value to check in js.
            */

            // check email field(s). 
            if ( "email" === $data['type'] )
            {
                // cloneable email field
                if ( is_array( $_POST[ $field_id ] ) )
                {
                    foreach( $_POST[ $field_id ] as $email_address )
                    {
                        if( false === filter_var( $email_address, FILTER_VALIDATE_EMAIL ) )
                            return '2';
                    }
                }
                // single email field
                elseif( false === filter_var( $_POST[ $field_id ], FILTER_VALIDATE_EMAIL ) )
                    return '2';
            }
            // check required fields
            $is_required = empty( $_POST[ $field_id ] ) && !empty( $data['required'] );
            // checkboxes don't post data if not checked.
            $is_required_checkbox = !isset( $_POST[ $field_id ] ) && 'checkbox' === $data['type'] && !empty( $data['required'] );
            // applies to cloneable fields and other array based fields...except checkboxes since they're handled above.
            $is_required_array = isset( $_POST[ $field_id ], $_POST[ $field_id ][0] ) && is_array( $_POST[ $field_id ] ) && empty( $_POST[ $field_id ][0] ) && !empty( $data['required'] );
                                
            if ( $is_required_checkbox || $is_required_array || $is_required )
               $required[] = $field_id;

            // Can't forget to set empty checkboxes and multi select fields!!!
            if ( 'checkbox' == $data['type'] && !isset( $_POST[ $field_id ] ) )
                $_POST[ $field_id ] = array();
            if ( 'dropdown' == $data['type'] && isset( $data['multiple'] ) && true == $data['multiple'] && !isset( $_POST[ $field_id ] ) )
                $_POST[ $field_id ] = array();

            // let's keep the fields in order, shall we?
            ksort( $_POST, SORT_NATURAL );
        }
        
        // we don't want to email these!
        unset(
            $_POST['ajax_action'],
            $_POST['g-recaptcha-response']
        );
        
        if ( sizeof( $required ) === 0 )
        {
            // Set html and text content for email(s)
            $this->set_message_content();

            if ( !empty( $this->email ) && $this->send() )
            {
                // emailed
                $return = '1';
            }
            if ( !empty( $this->save['to_save'] ) )
            {
                $cascade = new \Form\Cascade_Server( $this->fields, $this->soapURL, $this->auth, $this->save );

                if ( $cascade->save() )
                    // saved
                    $return = '1';
            }
            if ( !empty( $this->send_email_attachment ) && $this->send_form_data_to_user( $this->message_content["text"], $_POST[ $this->send_email_attachment ] ) )
            {
                // attachment sent
                $return = '1';
            }
        }
        elseif ( $required )
            $return = json_encode( $required );

        if ( '1' === $return )
        {
            // unset all $_SESSION variables
            $_SESSION = array();
            // destroy the session
            session_destroy();
        }

        return $return;
    }
    
    /**
    * Prints javascript where called
    *
    * @return string Outputs javascript
    */
    public function print_scripts()
    {
        echo $this->get_template_part('scripts/javascript', array( 'id' => $this->id ), true );
    }
    
    /**
    * Prints CSS where called
    *
    * @return string Outputs CSS
    */
    public function print_styles()
    {
        echo $this->get_template_part('styles/css', array( 'stylesheet_path' => $this->stylesheet_path, 'id' => $this->id ) , true);
    }
    
    /**
    * Prints Google ReCaptcha where called
    *
    * @return string Outputs HTML
    */
    private function get_recaptcha_html()
    {
        $html = '';
        if ( true === $this->recaptcha )
        {
            $html = '<div class="g-recaptcha" data-sitekey="' . $this->recaptcha_site_key . '"></div>
            <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=en"></script>';
        }
        return $html;
    }
    
    /**
    * Prints form where called
    *
    * @return string Outputs HTML
    */
    public function get()
    {
        $form_vars = array(
            'id' => $this->id,
            'fields' => $this->list_fields(),
            'recaptcha' => $this->get_recaptcha_html()
        );
        echo $this->get_template_part( 'form', $form_vars, true );
    }
}
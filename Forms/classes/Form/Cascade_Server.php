<?php

namespace Form;

class Cascade_Server
{
	private $fields = array();

	private $soapURL = '';

	private $auth = array( 'username' => '', 'password' => '' );

	private $save = array(
		'to_save' => array( 
			'path'     => '',
			'fileName' => ''
		),
		'site' => ''
	);

	public function __construct( $fields, $soapURL, $auth, $save )
	{
       	$this->fields = $fields;

       	$this->soapURL = $soapURL;

       	$this->auth = $auth;

       	$this->save = $save;
	}

	/**
    * Escapes data for use in a .csv file
    *
    * @return string escaped data
    */
    private function escape_csv_data( $data = "" )
    {
        if ( preg_match( "/,|\n/", $data ) )
            return "\"" . $data . "\"";
        else
            return $data;
    }

	/**
    * Creates or edits .csv file content from form submission. Creates new file if form fields change.
    *
    * @return string Outputs .csv data
    */
    private function process_data_to_save( $old_data = '' )
    {
        $headers = array();
        $to_append = "";

        foreach ( $_POST as $key => $value )
        {
            $headers[] = $this->fields[$key]['fieldName'];

            if ( is_array( $value ) ) // Must be checkboxes or cloneable field
            {  
                $processed_values = array();

                foreach( $value as $val )
                    $processed_values[] = $this->escape_csv_data( $val );
                
                $to_append .= implode( "; ", $processed_values ) . ",";
            }
            else
                $to_append .= $this->escape_csv_data( $value ) . ",";
        }
        // add date/time submitted as the last column header in the csv
        array_push( $headers, 'Submitted' );
        // append the date/time to the data
        $to_append .= "\"" . date("F j, Y, g:i a") . "\"";
        // create comma seperated headers string from array
        $file_headers = implode( ",", $headers );
        // if it's a new file
        if ( empty( $old_data ) )
            $file_contents = $file_headers . "\n" . $to_append;
        // else, it's an existing file
        else
        {
            // data was passed in. we need to make it an array of line items so we can compare the headers
            $old_data = explode( "\n", $old_data );
            // if the file headers are the same append the passed in data
            if ( $old_data[0] == $file_headers )
            {   
                array_push( $old_data, $to_append );
                $file_contents = implode( "\n", $old_data );
            }
            // otherwise, if fields have been added/removed create new file content. Cascade Server will handle versioning.
            else
            {
                $new_data[0] = $file_headers;
                array_push( $new_data, $to_append );
                $file_contents = implode( "\n", $new_data );
            }
            
        }
        return $file_contents;
    }
	
	/**
    * Saves form data as .csv to Cascade Server
    *
    * @return bool TRUE on success FALSE on failure
    */
    public function save()
    {
        $client = new \SoapClient( 
            $this->soapURL, 
            array('trace' => 1, 'location' => str_replace('?wsdl', '', $this->soapURL)) 
        );

        $identifier = array(
            'path' => array(
                'path'      => $this->save['to_save']['path'] . '/' . $this->save['to_save']['fileName'],
                'siteName'  => $this->save['site']
            ),
            'type' => 'file'
        );

        // let's see if the file exists
        $readParams = array ('authentication' => $this->auth, 'identifier' => $identifier);
        $reply = $client->read( $readParams );

        // Does it exist? If so we need to edit.
        if ( $reply->readReturn->success == 'true' )
        {
            $file = $reply->readReturn->asset->file;
            $file->data = $this->process_data_to_save( $file->data );
            $editParams = array ( 'authentication' => $this->auth, 'asset' => array( 'file' => $file ) );
            $reply = $client->edit( $editParams );
            return $reply->editReturn->success == 'true';
        }
        // It doesnt' exist. Let's create it!
        else
        {
        	die('creating');
            $file = array(
                'siteName'          => $this->save['site'],
                'text'              => $this->process_data_to_save(),
                'metadataSetPath'   => '/Default',
                'parentFolderPath'  => $this->save['to_save']['path'],
                'name'              => $this->save['to_save']['fileName'],
            );
            $createParams = array ( 'authentication' => $this->auth, 'asset' => array( 'file' => $file ) );
            $reply = $client->create( $createParams );
            return $reply->createReturn->success == 'true';
        }
    }

}
<?php

/**
*    This is a PHP library that handles LDAP authentication to an AD server
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

class LDAP_auth extends Form
{
    /**
	* The username to be authenticated
	* @var string
	*/
    private $username = '';

    /**
    * Suffix to be appended to usernames during authentication. Ex: @domain.local
    * @var string
    */
    private $suffix = '';

	/**
	* The password to be authenticated
	* @var string
	*/
    private $password = '';

	/**
	* The AD server hostname to which we will connect
	* @var string
	*/
	private $ad_server = '';

	/**
	* The base distinguished name on the AD server
	* @var string
	*/
	private $base_dn = '';

	/**
	* LDAP link identifier
	* @var resource
	*/
	private $ldap;

	/**
	* An array of AD distinguished names to search. Ex: memberOf=CN=Group,OU=Organizational Unit,OU=Organizational Unit,OU=Organizational Unit
	* @var array
	*/
	private $search = array();

	/*
	* Create a configured instance to use the LDAP_Auth class.
	*
	* @param array $search An array of AD distinguished names to search. Ex: CN=Group,OU=Organizational Unit,OU=Organizational Unit,OU=Organizational Unit
	*/
	public function __construct( $options, $fields )
	{
		parent::__construct( $options, $fields );
        
        foreach( $this->ldap_auth as $key => $value )
			$this->$key = $value;

        // For calling authorize() in object context from our ajax.php file.
        $_SESSION['form'] = $this;
	}

	/**
	* Create the LDAP connection
	*
	* @return resource Positive LDAP link identifier on success, or FALSE on error
	*/
	private function ldap_connect()
	{
		$connection = ldap_connect( $this->ad_server ) or die( "Could not connect to LDAP server." );

		return $connection;
	}

	/**
	* Add options to the LDAP connection
	* 
	* @return null
	*/
	private function ldap_set_opts()
	{
		ldap_set_option( $this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3 );

    	ldap_set_option( $this->ldap, LDAP_OPT_REFERRALS, 0 );
	}

	/**
	* Create the LDAP binding (authenticate the user)
	*
	* @param string $username The username.
	* @param string $password The password.
	* @return bool Returns TRUE on success or FALSE on failure.
	*/
	private function ldap_bind( $username, $pass )
	{
		$binding = @ldap_bind( $this->ldap, $username, $pass );

		return $binding;
	}

	/**
	* Close the ldap connection
	*
	* @return null
	*/
	private function ldap_close()
	{
		@ldap_close( $this->ldap );
	}

	/**
	* Called by array_walk(). Sanatize the search string escaping parenthesis that would otherwise
	* cause the search to fail. Other types of sanatization may be added here as needed.
	*
	* @param string $str The string in which to sanatize
	* @param int $key The current array key resulting from array_walk();
	* @return string Search string(s) passed into the __construct() as array.
	*/
	private function sanatize_str( &$str = '', $key )
	{
		$str = $str . ',' . $this->base_dn;

		$replace = array(
			'find' => array(
				'(',
				')'
			),
			'replace' => array(
				'\(',
				'\)'
			)
		);

		$str = str_replace( $replace['find'], $replace['replace'], $str );
	}

	/**
	* Build the LDAP search based on user input passed into the __construct() as array
	*
	* @return string Search string(s) passed into the __construct() as array are fomatted into a proper LDAP search.
	*/
	private function ldap_build_search_string()
	{
		$search = $this->search;

		if ( sizeof( $this->search ) === 0 )
			return false;

		$search_multiple_groups = sizeof( $this->search ) !== 1;

		$str = '(&(sAMAccountName=' . $this->username;

		$search_multiple_groups ? $str .= ')(|' : $str.= ')';

		array_walk( $search, array( $this, 'sanatize_str' ) );

		foreach( $search as $data )
			$str .= '(memberOf='. $data .')';

		$str .= $search_multiple_groups ? '))' : ')';

		return $str;
	}

	/**
	* Perform the LDAP search
	*
	* @return array|false If the search contains results an array of results otherwise false.
	*/
	private function ldap_do_search()
	{
		$result = @ldap_search( $this->ldap, $this->base_dn, $this->ldap_build_search_string() );

        $info = @ldap_get_entries( $this->ldap, $result );

        return $info["count"] > 0;
	}

	/**
	* Authorize the user. This is the public method called after the LDAP_auth class is
	* instantiated. It will return a boolean based on whether the user is in the search
	* group(s) and enters the correct password.
	*
	* @param string $username Username. Probably posted from a form, but not necessarily.
	* @param string $password Password. Probably posted from a form, but not necessarily.
	* @return bool TRUE on successful binding FALSE on failure.
	*/
	public function authorize()
    {
    	$this->username = isset( $_POST['username'] ) ? $_POST['username'] : false;

		$this->password = isset( $_POST['password'] ) ? $_POST['password'] : false;

		$this->ldap = $this->ldap_connect();
		
		$this->ldap_set_opts();
		
		if ( false === $this->username || false === $this->password )
		{
			$this->ldap_close();
			return false;
		}

		$bind_success = $this->ldap_bind( $this->username . $this->suffix, $this->password );

		$in_groups = $this->ldap_do_search();

        $this->ldap_close();

        return $bind_success && $in_groups;
	}
}
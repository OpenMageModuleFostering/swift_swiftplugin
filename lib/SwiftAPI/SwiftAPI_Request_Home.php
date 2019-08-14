<?php

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request_Home
////////////////////////////////////////////////////////////////////////////////


class SwiftAPI_Request_Home extends SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $url;
	public $email;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $url, $email = NULL, $version = NULL, $date = NULL)
		{
		$this -> url   = $url;
		$this -> email = $email;

		parent::__construct($domain, SwiftAPI::OPERATION_HOME, $user, $version, $date);
		}

	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(empty($fields -> url))
			throw new SwiftAPI_Exception('SwiftAPI_Request_Home::Create(): "url" field is missing or empty.');

		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> url,
			empty($fields -> email) ? NULL : $fields -> email,
			$fields -> version,
			$fields -> date
			);
		}
	}

?>
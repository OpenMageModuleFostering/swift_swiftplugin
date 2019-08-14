<?php

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request_PastOrder
////////////////////////////////////////////////////////////////////////////////


class SwiftAPI_Request_PastOrder extends SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $email;
	public $forename;
	public $surname;
	public $products;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $email, $forename, $surname, array $products, $version = NULL, $date = NULL)
		{
		$this -> email     = $email;
		$this -> forename  = $forename;
		$this -> surname   = $surname;
		$this -> products  = $products;

		parent::__construct($domain, SwiftAPI::OPERATION_PASTORDER, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(empty($fields -> email))
			throw new SwiftAPI_Exception('SwiftAPI_Request_PastOrder::Create(): "email" field is missing or empty.');

		if(empty($fields -> forename))
			throw new SwiftAPI_Exception('SwiftAPI_Request_PastOrder::Create(): "forename" field is missing or empty.');

		if(empty($fields -> surname))
			throw new SwiftAPI_Exception('SwiftAPI_Request_PastOrder::Create(): "surname" field is missing or empty.');

		if(empty($fields -> products))
			throw new SwiftAPI_Exception('SwiftAPI_Request_PastOrder::Create(): "products" field is missing or empty.');

		if(!is_array($fields -> products))
			throw new SwiftAPI_Exception('SwiftAPI_Request_PastOrder::Create(): "products" field is not an array.');

		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> email,
			$fields -> forename,
			$fields -> surname,
			$fields -> products,
			$fields -> version,
			$fields -> date
			);
		}
	}

?>
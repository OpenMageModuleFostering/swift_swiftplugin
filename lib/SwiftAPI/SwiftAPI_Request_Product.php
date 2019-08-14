<?php

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request_Product
////////////////////////////////////////////////////////////////////////////////


class SwiftAPI_Request_Product extends SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $product;
	public $email;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $product, $email = NULL, $version = NULL, $date = NULL)
		{
		$this -> product = $product;
		$this -> email   = $email;

		parent::__construct($domain, SwiftAPI::OPERATION_PRODUCT, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(empty($fields -> product))
			throw new SwiftAPI_Exception('SwiftAPI_Request_Product::Create(): "product" field is missing or empty.');

		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields  -> product,
			empty($fields -> email) ? NULL : $fields -> email,
			$fields -> version,
			$fields -> date
			);
		}
	}

?>
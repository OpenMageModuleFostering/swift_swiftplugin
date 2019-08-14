<?php

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request_Cart
////////////////////////////////////////////////////////////////////////////////


class SwiftAPI_Request_Cart extends SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $products;
	public $email;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, array $products, $email = NULL, $version = NULL, $date = NULL)
		{
		$this -> products = $products;
		$this -> email    = $email;

		parent::__construct($domain, SwiftAPI::OPERATION_CART, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(empty($fields -> products))
			throw new SwiftAPI_Exception('SwiftAPI_Request_Cart::Create(): "products" field is missing or empty.');

		if(!is_array($fields -> products))
			throw new SwiftAPI_Exception('SwiftAPI_Request_Cart::Create(): "products" field is not an array.');

		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> products,
			empty($fields -> email) ? NULL : $fields -> email,
			$fields -> version,
			$fields -> date
			);
		}
	}

?>
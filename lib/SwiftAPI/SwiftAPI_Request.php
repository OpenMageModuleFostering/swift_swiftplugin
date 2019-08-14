<?php

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request
////////////////////////////////////////////////////////////////////////////////

require_once('SwiftAPI_Request_Home.php');
require_once('SwiftAPI_Request_Product.php');
require_once('SwiftAPI_Request_Cart.php');
require_once('SwiftAPI_Request_Order.php');
require_once('SwiftAPI_Request_PastOrder.php');
require_once('SwiftAPI_Request_Subscription.php');
require_once('SwiftAPI_Request_ViewMail.php');
require_once('SwiftAPI_Request_SendMail.php');


abstract class SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $domain;
	public $operation;
	public $user;
	public $version;
	public $date;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions.
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $operation, $user, $version = NULL, $date = NULL)
		{
		$this -> domain    = $domain;
		$this -> operation = $operation;
		$this -> user      = $user;
		$this -> version   = $version ? $version : SwiftAPI::VERSION;
		$this -> date      = $date ? $date : date_create() -> format('c');
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		switch($fields -> operation)
			{
				case SwiftAPI::OPERATION_PING:
					return SwiftAPI_Request_Ping::Create($fields);
					
				case SwiftAPI::OPERATION_HOME:
					return SwiftAPI_Request_Home::Create($fields);
					
				case SwiftAPI::OPERATION_PRODUCT:
					return SwiftAPI_Request_Product::Create($fields);
	
				case SwiftAPI::OPERATION_CART:
					return SwiftAPI_Request_Cart::Create($fields);
	
				case SwiftAPI::OPERATION_ORDER:
					return SwiftAPI_Request_Order::Create($fields);
	
				case SwiftAPI::OPERATION_PASTORDER:
					return SwiftAPI_Request_PastOrder::Create($fields);
	
				case SwiftAPI::OPERATION_SUBSCRIPTION:
					return SwiftAPI_Request_Subscription::Create($fields);
	
				case SwiftAPI::OPERATION_VIEWMAIL:
					return SwiftAPI_Request_ViewMail::Create($fields);
	
				case SwiftAPI::OPERATION_SENDMAIL:
					return SwiftAPI_Request_SendMail::Create($fields);
				case SwiftAPI::OPERATION_UNSUBSCRIBE:
					return SwiftAPI_Request_Unsubscribe::Create($fields);
					
				default:
					throw new SwiftAPI_Exception('SwiftAPI_Request::Create(): Invalid operation: "' . $fields -> operation . '".');
			}
		}


	//////////////////////////////////////////////////////////////////////////////
	// Protected functions.
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Protected: Validate()
	////////////////////////

	protected static function Validate(stdClass $fields)
		{
		// Check for domain field.
		if(empty($fields -> domain))
			throw new SwiftAPI_Exception('SwiftAPI_Request::Validate(): "domain" field is missing or empty.');

		// Check for operation field.
		if(empty($fields -> operation))
			throw new SwiftAPI_Exception('SwiftAPI_Request::Validate(): "operation" field is missing or empty.');

		// Check for user field.
		if(empty($fields -> user))
			throw new SwiftAPI_Exception('SwiftAPI_Request::Validate(): "user" field is missing or empty.');

		// Check for version field.
		if(empty($fields -> version))
			throw new SwiftAPI_Exception('SwiftAPI_Request::Validate(): "version" field is missing or empty.');

		// Check for date field.
		if(empty($fields -> date))
			throw new SwiftAPI_Exception('SwiftAPI_Request::Validate(): "date" field is missing or empty.');
		}

	}

?>
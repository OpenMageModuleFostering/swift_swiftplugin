<?php

class SwiftAPI_Request_OrderPackage extends SwiftAPI_Request {
	
	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $orderPackage;

	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $orderPackage, $version = NULL, $date = NULL)
		{
		$this -> orderPackage = $orderPackage;
		parent::__construct($domain, SwiftAPI::OPERATION_ORDERPACKAGE, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(!is_array($fields -> orderPackage))
			throw new SwiftAPI_Exception('SwiftAPI_Request_OrderPackage::Create(): "orderPackage" field is missing.');

		foreach ($fields -> orderPackage as $email_content) {
			if (!(is_object($email_content))) {
				throw new SwiftAPI_Exception('SwiftAPI_Request_OrderPackage::Create(): "orderPackage" does not contain the class SwiftAPI_Request_Order.');
			}
		}
		
		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> orderPackage,
			$fields -> version,
			$fields -> date
			);
		}
}
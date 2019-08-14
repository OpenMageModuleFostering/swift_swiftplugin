<?php

class SwiftAPI_Request_EmailPackage extends SwiftAPI_Request {
	
	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $emailPackage;
	public $site;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $site, $emailPackage, $version = NULL, $date = NULL)
		{
		$this -> emailPackage = $emailPackage;
		$this -> site = $site;
		parent::__construct($domain, SwiftAPI::OPERATION_EMAILPACKAGE, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(!is_array($fields -> emailPackage) || empty($fields -> emailPackage))
			throw new SwiftAPI_Exception('SwiftAPI_Request_EmailPackage::Create(): "emailPackage" field is missing or empty.');

		foreach ($fields -> emailPackage as $email_content) {
			if (!(is_object($email_content))) {
				throw new SwiftAPI_Exception('SwiftAPI_Request_EmailPackage::Create(): "emailPackage" does not contain the class SwiftAPI_Request_SendMail.');
			}
		}
		if (!isset($fields -> site)) {
			throw new SwiftAPI_Exception('SwiftAPI_Request_EmailPackage::Create(): "site" field is missing or empty.');
		}
		
		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> site,
			$fields -> emailPackage,
			$fields -> version,
			$fields -> date
			);
		}
	
}


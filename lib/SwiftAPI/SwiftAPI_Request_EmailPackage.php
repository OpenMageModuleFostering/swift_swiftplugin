<?php

class SwiftAPI_Request_EmailPackage extends SwiftAPI_Request {
	
	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $emailPackage;
	public $site;
	public $is_mail_function;
	public $timeDebug;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $site, $emailPackage, $is_mail_function = false, $version = NULL, $date = NULL, $timeDebug = false)
		{
		$this -> emailPackage = $emailPackage;
		$this -> site = $site;
		$this->is_mail_function = $is_mail_function;
		$this->timeDebug = $timeDebug;
		parent::__construct($domain, SwiftAPI::OPERATION_EMAILPACKAGE, $user, $version, $date);
		}
		
		/**
		 * Sets all schedules
		 * because this isn't important and is related to the SwiftCRM_Scheduling not the plugin itself I want to make this optional
		 */
		public function setSchedules($schedules) {
			$this->schedules = $schedules;
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
			$fields -> is_mail_function,
			$fields -> version,
			$fields -> date
			);
		}
	
}


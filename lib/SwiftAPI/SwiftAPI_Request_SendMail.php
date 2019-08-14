<?php 

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request_SendMail
////////////////////////////////////////////////////////////////////////////////


class SwiftAPI_Request_SendMail extends SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $email;
	public $subject;
	public $body;
	public $monitor;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $email, $subject, $body, $version = NULL, $date = NULL, $monitor = null)
		{
		$this -> email   = $email;
		$this -> subject = $subject;
		$this -> body    = $body;
		$this->monitor = $monitor;
		parent::__construct($domain, SwiftAPI::OPERATION_SENDMAIL, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(empty($fields -> email))
			throw new SwiftAPI_Exception('SwiftAPI_Request_SendMail::Create(): "email" field is missing or empty.');

		if(empty($fields -> subject))
			throw new SwiftAPI_Exception('SwiftAPI_Request_SendMail::Create(): "subject" field is missing or empty.');

		if(empty($fields -> body))
			throw new SwiftAPI_Exception('SwiftAPI_Request_SendMail::Create(): "body" field is missing or empty.');
		
		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> email,
			$fields -> subject,
			$fields -> body,
			$fields -> version,
			$fields -> date,
			$fields -> monitor
			);
		}
	}

?>
<?php 

////////////////////////////////////////////////////////////////////////////////
// Class: SwiftAPI_Request_ViewMail
////////////////////////////////////////////////////////////////////////////////


class SwiftAPI_Request_ViewMail extends SwiftAPI_Request
	{

	//////////////////////////////////////////////////////////////////////////////
	// Public properties.
	//////////////////////////////////////////////////////////////////////////////

	public $email;
	public $product;
	public $emailId;


	//////////////////////////////////////////////////////////////////////////////
	// Public functions
	//////////////////////////////////////////////////////////////////////////////

	////////////////////////
	// Public: __construct()
	////////////////////////

	public function __construct($domain, $user, $email, $product, $emailId, $version = NULL, $date = NULL)
		{
		$this -> email   = $email;
		$this -> product = $product;
		$this-> emailId = $emailId;

		parent::__construct($domain, SwiftAPI::OPERATION_VIEWMAIL, $user, $version, $date);
		}


	///////////////////
	// Public: Create()
	///////////////////

	public static function Create(stdClass $fields)
		{
		parent::Validate($fields);

		if(empty($fields -> email))
			throw new SwiftAPI_Exception('SwiftAPI_Request_ViewMail::Create(): "email" field is missing or empty.');

		if(empty($fields -> product))
			throw new SwiftAPI_Exception('SwiftAPI_Request_ViewMail::Create(): "product" field is missing or empty.');
		
		if(empty($fields -> emailId))
			throw new SwiftAPI_Exception('SwiftAPI_Request_ViewMail::Create(): "emailId" field is missing or empty.');

		return new self
			(
			$fields -> domain,
			$fields -> user,
			$fields -> email,
			$fields -> product,
			$fields -> emailId,
			$fields -> version,
			$fields -> date
			);
		}
	}

?>
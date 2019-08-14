<?php

require_once('SwiftAPI_Exception.php');
require_once('SwiftAPI_Request.php');
require_once('SwiftAPI_Product.php');

class SwiftAPI
	{

	//////////////////////////////////////////////////////////////////////////////
	// Class constants.
	//////////////////////////////////////////////////////////////////////////////

	const VERSION = 2;

	const OPERATION_HOME        = 'home';
	const OPERATION_PRODUCT     = 'product';
	const OPERATION_CART        = 'cart';
	const OPERATION_ORDER       = 'order';
	const OPERATION_PASTORDER   = 'pastorder';
	const OPERATION_SUBSCRIPTION= 'subscription';
	const OPERATION_VIEWMAIL    = 'viewmail';
	const OPERATION_SENDMAIL    = 'sendmail';
	const OPERATION_UNSUBSCRIBE = 'unsubscribe';
	const OPERATION_PING		= 'ping';
	const OPERATION_EMAILPACKAGE = 'emailpackage';

	const SWIFTAPI_CRM_URL       = '//api.swiftcrm.net';


	//////////////////////////////////////////////////////////////////////////////
	// Public functions.
	//////////////////////////////////////////////////////////////////////////////

	///////////////////
	// Public: Encode()
	///////////////////

	public static function Encode(SwiftAPI_Request $request, $key)
		{
		// Encode JSON object.
		if(!($json = json_encode($request)))
			throw new SwiftAPI_Exception('SwiftAPI::Encode(): ' . json_last_error_msg());

		// Create initialization vector.
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);

		// Encrypt data and trim trailing NULL bytes.
		$data = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $json, MCRYPT_MODE_CBC, $iv);

		// Base64 encode message.
		if(!($msg = base64_encode($iv . $data)))
			throw new SwiftAPI_Exception('SwiftAPI::Encode(): Failed to base64 encode message.');

		// Return message data.
		return $msg;
		}


	///////////////////
	// Public: Decode()
	///////////////////

	public static function Decode($version, $domain, $msg, $key)
		{
		// base64_decode message.
		if(!($message = base64_decode($msg)))
			throw new SwiftAPI_Exception('SwiftAPI::Decode(): Message is not base64 encoded.');

		// Fetch initialization vector size.
		$ivlen = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

		// Split message into data and initialization vector components.
    $iv   = substr($message, 0, $ivlen);
    $data = substr($message, $ivlen);

		// Decrypt data and trim trailing NULL bytes.
		$json = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv), "\0");

		// Decode json object.
		if(!($fields = json_decode($json)))
			throw new SwiftAPI_Exception('SwiftAPI::Decode(): ' . json_last_error_msg());

		// Create a SwiftAPI_Request object.
		$request = SwiftAPI_Request::Create($fields);

		// Validate domain.
		if($request -> domain !== $fields -> domain)
			throw new SwiftAPI_Exception('SwiftAPI::Decode(): Encoded domain does not match requested domain.');

		// Return request.
		return $request;
		}


	//////////////////
	// Public: Query()
	//////////////////

	public static function Query(SwiftAPI_Request $request, $key)
		{
		return http_build_query(array
			(
			'version' => $request -> version,
			'domain'  => $request -> domain,
			'data'    => SwiftAPI::Encode($request, $key)
			));
		}


	/////////////////
	// Public: Link()
	/////////////////

	public static function Link(SwiftAPI_Request $request, $key, $content, Site $site)
		{
		return '<a href="'. $site->getSslUrl() . self::SWIFTAPI_CRM_URL . '?' . self::Query($request, $key) . '">' . $content . '</a>';
		}


	///////////////////
	// Public: Script()
	///////////////////

	public static function Script($request, $key)
		{
		return '
		<script language="javascript" type="text/javascript">
			window.onload = function()
				{
				var query = "' . SwiftAPI::Query($request, $key) . '";
				var http;

				// IE7+, Firefox, Chrome, Opera, Safari.
				if(window.XMLHttpRequest)
					http=new XMLHttpRequest();

				// IE6, IE5.
				else
					http=new ActiveXObject("Microsoft.XMLHTTP");

				http.open("POST","'. self::SWIFTAPI_CRM_URL .'", true);

				http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				http.setRequestHeader("Content-length", query.length);
				http.setRequestHeader("Connection", "close");

				http.onreadystatechange= function()
					{
					if (http.readyState==4 && http.status==200)
						console.log("success");
					}

				http.send(query);
				}
			</script>';
		}

		public static function BareScript($request, $key)
		{
			return '
			window.onload = function()
				{
				var query = "' . SwiftAPI::Query($request, $key) . '";
				var http;
		
				// IE7+, Firefox, Chrome, Opera, Safari.
				if(window.XMLHttpRequest)
					http=new XMLHttpRequest();
		
				// IE6, IE5.
				else
					http=new ActiveXObject("Microsoft.XMLHTTP");
		
				http.open("POST","'. self::SWIFTAPI_CRM_URL .'", true);
		
				http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				http.setRequestHeader("Content-length", query.length);
				http.setRequestHeader("Connection", "close");
		
				http.onreadystatechange= function()
					{
					if (http.readyState==4 && http.status==200)
						console.log("success");
					}
		
				http.send(query);
				}
			';
		}

	////////////////////////////////////
	// Public: UserID()
	////////////////////////////////////
	// Generate a random UUIDv4 user ID.
	////////////////////////////////////

	public static function UserID()
		{
		return sprintf
			(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
			);
		}

	}

?>

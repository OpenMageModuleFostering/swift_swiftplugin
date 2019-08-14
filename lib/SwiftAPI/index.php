<?

require_once('SwiftAPI.php');

session_start();


if(!isset($_SESSION['swiftuser']) || empty($_SESSION['swiftuser']))
	$_SESSION['swiftuser'] = SwiftAPI::UserID();

$key = '8ca0KOynGlxFpX7CVHNuzO7yNGdO1bl7';
$version = '1';
$domain = 'netready.biz';
$user = $_SESSION['swiftuser'];
$date = date(DATE_ISO8601, time());
$operation = isset($_POST['operation']) ? $_POST['operation'] : NULL;
$url = $_SERVER['REQUEST_URI'];
$email = 'wayne@netready.biz';
$forename = 'Wayne';
$surname = 'Hemsley';
$product = 'test_product_1';
$quantity = '1';
$price = '1.00';
$subject = 'SwiftCRM Test Email';
$body = 'SwiftCRM Test Email';
$products = array
	(
	new SwiftAPI_Product('test_product_1', 1, 10),
	new SwiftAPI_Product('test_product_2', 2, 20),
	new SwiftAPI_Product('test_product_3', 3, 30)
	);

switch($operation)
	{
	// done
	case SwiftAPI::OPERATION_HOME:
		$request = new SwiftAPI_Request_Home($domain, $user, $url);
		break;

	// done
	case SwiftAPI::OPERATION_PRODUCT:
		$request = new SwiftAPI_Request_Product($domain, $user, $product);
		break;

	//done? the api spec has changed but swift api request is still referncing old data
	case SwiftAPI::OPERATION_CART:
		$request = new SwiftAPI_Request_Cart($domain, $user, $products);
		break;

	// done
	case SwiftAPI::OPERATION_ORDER:
		$request = new SwiftAPI_Request_Order($domain, $user, $email, $forename, $surname, $products);
		break;

	// yet to be done
	case SwiftAPI::OPERATION_PASTORDER:
		$request = new SwiftAPI_Request_PastOrder($domain, $user, $email, $forename, $surname, $products);
		break;

	// done
	case SwiftAPI::OPERATION_SUBSCRIPTION:
		$request = new SwiftAPI_Request_Subscription($domain, $user, $email);
		break;

	// havent done an operation to check for that yet
	case SwiftAPI::OPERATION_VIEWMAIL:
		$request = new SwiftAPI_Request_ViewMail($domain, $user, $product);
		break;

	// havent done an operation to check for that yet
	case SwiftAPI::OPERATION_SENDMAIL:
		$request = new SwiftAPI_Request_SendMail($domain, $user, $email, $subject, $body);
		break;

	default:
		$request = NULL;
	}


$content[] = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
</head>
<body>
	<form action="/index.php" method="post">
		<label>product<input type="radio" id="cobTransferType1" name="operation" value="product"/></label><br />
		<label>home<input type="radio" id="cobTransferType2" name="operation" value="home"/></label><br />
		<label>cart<input type="radio" id="cobTransferType3" name="operation" value="cart"/></label><br />
		<label>order<input type="radio" id="cobTransferType4" name="operation" value="order"/></label><br />
		<label>pastorder<input type="radio" id="cobTransferType5" name="operation" value="pastorder"/></label><br />
		<label>subscription<input type="radio" id="cobTransferType6" name="operation" value="subscription"/></label><br />
		<label>viewmail<input type="radio" id="cobTransferType7" name="operation" value="viewmail"/></label><br />
		<label>sendmail<input type="radio" id="cobTransferType7" name="operation" value="sendmail"/></label><br />
		<input type="submit"/>
	</form>
	<p id="Result"></p>
HTML;

if($request)
	$content[] = '
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

		http.onload = function(e)
			{
			document.getElementById("Result").innerHTML = this.responseText;
			}

		http.open("POST","http://swiftcrm.wayne.netready.lan", true);

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

$content[] = <<<HTML
</body>
</html>
HTML;

echo implode($content);
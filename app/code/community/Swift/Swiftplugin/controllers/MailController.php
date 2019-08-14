<?php

require_once(Mage::getBaseDir('lib') . '/SwiftAPI/SwiftAPI.php');

class Swift_Swiftplugin_MailController extends Mage_Core_Controller_Front_Action {

	const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';
	
    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';
	
	private $timeDebugOutput = array();

	public function triggerAction() {
		if (function_exists('mail') && $this->getRequest()->getPost()) {
			$postData = $this->getRequest()->getPost();
			if (isset($postData['version']) && isset($postData['domain']) && isset($postData['data'])) {
				
				//We time this regardless of whether debug is set as we can't yet tell if we want the debug or not
				$fetchKeyTimeStart = microtime(true);
				$key = hex2bin(Mage::helper('swift/Data')->getSwiftPrivateKey());
				$fetchKeyTimeEnd = microtime(true); 
				
				
				try {
					
					
					//Again, we can't tell yet if time debug is needed, so grab it anyway
					$decodeTimeStart = microtime(true);
					$emailPackage = SwiftAPI::Decode($postData['version'], $postData['domain'], $postData['data'], $key);
					if($emailPackage->timeDebug === true) {$decodeTimeEnd = microtime(true); $this->timeDebugOutput['SwiftAPI::Decode'] = $decodeTimeEnd-$decodeTimeStart;}
					
					//Output previous $key=hex2bin... debug as we now know whether or not we need it
					if($emailPackage->timeDebug === true) {$this->timeDebugOutput['$key = hex2bin(Mage::helper(\'swift/Data\')->getSwiftPrivateKey())'] = $fetchKeyTimeEnd-$fetchKeyTimeStart;}
					
					if($emailPackage->timeDebug === true) {$allEmailTimeStart = microtime(true);}
					foreach($emailPackage->emailPackage as $emailContent) {
						
						$body = $emailContent->body;
						$emailTo = $emailContent->email;
						$subject = $emailContent->subject;
						
						if($emailPackage->timeDebug === true) {$returnpathTimeStart = microtime(true);}
						$setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
						switch ($setReturnPath) {
							case 1:
								$returnPathEmail = Mage::getStoreConfig('trans_email/ident_sales/email');
								break;
							case 2:
								$returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
								break;
							default:
								$returnPathEmail = null;
								break;
						}
						

						if ($returnPathEmail !== null) {
							$mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
							Zend_Mail::setDefaultTransport($mailTransport);
						}
						
						if($emailPackage->timeDebug === true) {$returnPathTimeEnd = microtime(true); $this->timeDebugOutput['Setting return path for ' . $emailTo] = $returnPathTimeEnd-$returnpathTimeStart;}
						
						$mail = Mage::getModel('core/email');
												
						$mail->setToEmail($emailTo);
						$mail->setBody($body);
						$mail->setSubject($subject);
						$mail->setFromEmail(Mage::getStoreConfig('trans_email/ident_sales/email'));
						$mail->setReturnPath(Mage::getStoreConfig('trans_email/ident_sales/email'));
						$mail->setFromName(Mage::getStoreConfig('trans_email/ident_sales/name'));
						$mail->setType('html');// You can use 'html' or 'text'
						
						if($emailPackage->timeDebug === true) {$singleEmailTimeStart = microtime(true);}
						$mail->send();
						if($emailPackage->timeDebug === true) {$singleEmailTimeEnd = microtime(true); $this->timeDebugOutput['$mail->send() for ' . $emailTo] = $singleEmailTimeEnd-$singleEmailTimeStart;}
						
						
						if (property_exists($emailContent, 'monitor') && filter_var( $emailContent->monitor, FILTER_VALIDATE_EMAIL)) {
							if($emailPackage->timeDebug === true) {$monitorModeEmailStart = microtime(true);}
							$mail->setToEmail($emailContent->monitor);
							$mail->setSubject($emailTo.'_'.$subject);
							$mail->send();
							if($emailPackage->timeDebug === true) {$monitorModeEmailEnd = microtime(true); $this->timeDebugOutput['Monitor mode email for ' . $emailTo] = $monitorModeEmailEnd-$monitorModeEmailStart;}
						}
					}
					if($emailPackage->timeDebug === true) {$allEmailTimeEnd = microtime(true); $this->timeDebugOutput['foreach($emailPackage->emailPackage as $emailContent)'] = $allEmailTimeEnd-$allEmailTimeStart;}
					
					
					if($emailPackage->timeDebug === true)
					{
						echo json_encode($this->timeDebugOutput);
					}
					else
					{
						echo 'data recieved and email sent';
					}
				}
				catch (SwiftAPI_Exception $e) {
					echo $e->getMessage();
				}
				catch (Exception $e) {
					echo $e->getMessage();
					echo "Failed to send a message, incoming data could have been spoofed";
				}
			}
		}
		else {
			$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
			$this->getResponse()->setHeader('Status','404 File not found');
			// throw a 404 page if post hasnt been recieved or mail does not exist
			$pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
			if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
				$this->_forward('defaultNoRoute');
			}
		}
	}
}


?>

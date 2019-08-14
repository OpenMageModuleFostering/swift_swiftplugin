<?php

require_once(Mage::getBaseDir('lib') . '/SwiftAPI/SwiftAPI.php');

 /**
 * Helper functions called throughout the swift plugin
 *
 */

class Swift_Swiftplugin_Helper_Data extends Mage_Core_Helper_Abstract {
        /*
         * Generate the custom User Id for Swift
         *
         */
        public function generateUserId() {
                $swiftId = Mage::getSingleton('core/session')->getSwiftUserId();
                if (is_null($swiftId) || empty($swiftId)) {
                        Mage::getSingleton('core/session')->setSwiftUserId(SwiftAPI::UserID());
                }
                return Mage::getSingleton('core/session')->getSwiftUserId();
        }
        /*
         * Retrieve the first record out of the database
         *
         */
        public function getSwiftPrivateData() {
                $data = false;
				$model = Mage::getModel('swift/swift');
				$collection = $model->getCollection();
                $swiftPrivateKeyCollection = $collection->setPageSize(1);
                // if one is present look at it
                if (count($swiftPrivateKeyCollection) == 1) {
                        foreach($swiftPrivateKeyCollection as $swiftPrivateKey) {
                                // get data and swift key
                                $data = $swiftPrivateKey->getData();
                                break;
                        }
                }
                return $data;
        }
        /**
         * Extension to getSwiftPrivateData()
         * Retrieves the swift private key from the database
         */
        public function getSwiftPrivateKey() {
                $data = $this->getSwiftPrivateData();
                if (!is_bool($data) && !is_null($data)) {
                        $data = $data['swift_private_key'];
                }
                return $data;
        }
		
		/**
		* For Debugging
		*/
		public function getExtensionInformation() {
			$arr = array();
			$arr['https'] = false;
			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
				$arr['https'] = true;
			}
			$arr['plugin_version'] = (string)Mage::getConfig()->getNode()->modules->Swift_Swiftplugin->version;
			$arr['host'] =  $_SERVER['HTTP_HOST'];
			$arr['magento_version'] =  Mage::getVersion();
			$arr['php'] = phpversion();
			
			$modules = Mage::getConfig()->getNode('modules')->children();
			$modulesArray = (array)$modules;
			$module_output = array();
			foreach($modulesArray as $module_name => $module) {
				$module_output[$module_name] = $module->is('active');
			}
			$arr['modules'] = $module_output;
			
			$api_version = new SwiftAPI_Request_Version($_SERVER['HTTP_HOST'],$arr);
			$key = Mage::helper('swift/Data')->_hex2bin(Mage::helper('swift/Data')->getSwiftPrivateKey());
			if (!is_bool($key) && !is_null($key)) {
				echo SwiftAPI::Encode($api_version,  Mage::helper('swift/Data')->_hex2bin(Mage::helper('swift/Data')->getSwiftPrivateKey()));
			}
			else {
				json_encode($arr);
			}
		}
		
		public function _hex2bin($str) {
			
			if (function_exists( 'hex2bin' )) {
				return hex2bin($str);
			}
			else {
				$sbin = "";
				$len = strlen( $str );
				for ( $i = 0; $i < $len; $i += 2 ) {
					$sbin .= pack( "H*", substr( $str, $i, 2 ) );
				}

				return $sbin;
			}
			
		}
		
		public function _isCurl() {
			return function_exists('curl_version');
		}
}

?>

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
			
			return json_encode($arr);
		}
}

?>

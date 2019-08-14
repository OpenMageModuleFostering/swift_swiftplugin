<?php


class Swift_Swiftplugin_Block_Adminhtml_Swift_Edit_Tab_Instruct extends Mage_Adminhtml_Block_Widget_Form {


	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('swift_instruct_form', array('legend'=>'Register at http://account.swiftcrm.net'));
		$fieldset->addField('note', 'note', array(
	          'text'  => Mage::helper('core')->__('To start, please register at <a href="http://account.swiftcrm.net">http://account.swiftcrm.net</a> to recieve your SwiftCRM key, then proceed to step 2.'),
	        ));
        
		return parent::_prepareForm();		
	}
}

?>
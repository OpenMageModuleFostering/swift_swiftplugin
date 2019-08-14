<?php


class Swift_Swiftplugin_Block_Adminhtml_Swift_Edit_Tab_Instruct extends Mage_Adminhtml_Block_Widget_Form {


	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('swift_instruct_form', array('legend'=>'Register at http://swifterm.com'));
		$fieldset->addField('note', 'note', array(
	          'text'  => Mage::helper('core')->__('To start, please register at <a href="http://swifterm.com">http://swifterm.com</a> to recieve your SwiftERM key, then proceed to step 2.'),
	        ));
        
		return parent::_prepareForm();		
	}
}

?>
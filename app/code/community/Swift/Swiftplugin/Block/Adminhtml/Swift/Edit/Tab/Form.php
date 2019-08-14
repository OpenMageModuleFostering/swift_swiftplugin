<?php


class Swift_Swiftplugin_Block_Adminhtml_Swift_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {


	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('swift_register_key_form', array('legend'=>'Insert SwiftCRM Private Key'));
		$fieldset->addField('swift_private_key', 'text',
				array(
					'label' => 'Private Key',
					'class' => 'required-entry',
					'required' => true,
					'name' => 'swift_private_key',
					'maxlength' => 64
		));
		
		if (Mage::registry('swift_data')) {
			$data = Mage::registry('swift_data')->getData();
			if (isset($data['swift_private_key'])) {
				$data['swift_private_key'] = $data['swift_private_key'];				
			}
			$form->setValues($data);
		}
		
		
		$fieldset->addField('swift_send_history', 'checkbox' , array(
			'label' => 'Send information about past orders to SwiftCRM',
			'name' => 'swift_send_history',
			'value' => '1',
			'checked' => true
		))->setIsChecked(empty($data) || $data['swift_send_history'] == 1);
		
		return parent::_prepareForm();
	}
}

?>
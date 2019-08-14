<?php


class Swift_Swiftplugin_Block_Adminhtml_Swift_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    
	
	protected function _prepareForm() {
		$data = Mage::helper('swift/Data')->getSwiftPrivateData();
		$swiftId = null;
		if (isset($data['swift_id'])) {
			$swiftId = $data['swift_id'];
		}
	
		$form = new Varien_Data_Form(
			array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/save', array('id' => $swiftId)),
				'method' => 'post',
			)
		);
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
   }
   
}

?>
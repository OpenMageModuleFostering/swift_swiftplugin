<?php


class Swift_Swiftplugin_Block_Adminhtml_Swift_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	
	public function __construct() {
		parent::__construct();
		$this->setId('swift_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle('SwiftCRM Admin');
	}
	
	
	protected function _beforeToHtml() {
		
		$this->addTab('form_step_one_section', array(
			'label' => 'Step 1: Register At SwiftERM',
			'title' => 'Step 1: Register At SwiftERM',
			'content' => $this->getLayout()->createBlock('swift/adminhtml_swift_edit_tab_instruct')->toHtml()
		));

		$this->addTab('form_step_two_section', array(
			'label' => 'Step 2: Register Your SwiftERM Private Key',
			'title' => 'Step 2: Register Your SwiftERM Private Key',
			'content' => $this->getLayout()->createBlock('swift/adminhtml_swift_edit_tab_form')->toHtml()
		));
		 
		return parent::_beforeToHtml();
    }
}

?>
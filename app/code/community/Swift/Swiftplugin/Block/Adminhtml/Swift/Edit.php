<?php

class Swift_Swiftplugin_Block_Adminhtml_Swift_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	
	public function __construct() {
        parent::__construct();
		//where is the controller
        $this->_objectId = 'id';
        //we assign the same blockGroup as the Grid Container
        $this->_blockGroup = 'swift';
        //and the same controller
        $this->_controller = 'adminhtml_swift';
        //define the label for the save and delete button
        $this->_updateButton('save', 'label','Save SwiftERM Private Key');
		$this->_removeButton('delete');
		$this->_removeButton('back');
		$this->_removeButton('reset');
    }
	
    public function getHeaderText() {
 		return 'SwiftERM Plugin Administration';
	}
}

?>
<?php

class DemoAccountMapping_Module_Model extends Vtiger_Module_Model {

    public function isCommentEnabled() {
        return true;
    }

    /**
	* Function to check if duplicate option is allowed in DetailView
	* @param <string> $action, $recordId 
	* @return <boolean> 
	*/
	public function isDuplicateOptionAllowed($action, $recordId) {
		return false;
	}

}

?>
<?php

class ContactChannels_Module_Model extends Vtiger_Module_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 15-02-2019
     * @comment: Enabled Comment Widget
     */
    public function isCommentEnabled() {
        return true;
    }

    /**
     * @creator: Divyesh Chothani
     * @date: 11-03-2019
     * @comment: remove editing from listing page
     */
    public function isExcelEditAllowed() {
        return false;
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
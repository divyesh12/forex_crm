<?php

class LeverageHistory_Module_Model extends Vtiger_Module_Model {

    /**
     * @creator: Divyesh Chothani
     * @date: 14-10-2019
     * @comment: Enable Commnet
     */
    public function isCommentEnabled() {
        return true;
    }

    /**
     * @creator: Divyesh Chothani
     * @date: 14-10-2019
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
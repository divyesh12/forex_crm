<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class DemoAccount_Module_Model extends Vtiger_Module_Model {

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

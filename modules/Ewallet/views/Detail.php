<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ewallet_Detail_View extends Vtiger_Detail_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showRelatedRecords');
    }

    /**
      @Add_by:-Reena Hingol
      @Date:-25_11_19
      @Comment:-function for enable/disable edit in the detail page and Summery page.
     */
    public function isAjaxEnabled($recordModel) {
        return false;
    }

    /* end */
}

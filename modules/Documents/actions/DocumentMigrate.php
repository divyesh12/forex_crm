<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'include/Webservices/Create.php';
class Documents_DocumentMigrate_Action extends Vtiger_Action_Controller {
    
        function __construct() {
            $this->contactMappingArr = array
            ('3' => 680, '14' => 834, '41' => 150, '43' => 151, '45' => 239, '47' => 240, '49' => 152, '58' => 139, '72' => 153, '74' => 154, '76' => 155, '78' => 241, '85' => 242, '87' => 243, '89' => 156, '91' => 157, '93' => 244, '95' => 245, '118' => 158, '120' => 159, '124' => 160, '126' => 246, '130' => 161, '132' => 247, '136' => 248, '138' => 162, '140' => 163, '144' => 164, '156' => 165, '158' => 249, '162' => 166, '164' => 167, '166' => 250, '179' => 168, '181' => 169, '199' => 238, '210' => 170, '212' => 171, '214' => 172, '216' => 173, '218' => 174, '220' => 251, '222' => 175, '227' => 176, '233' => 177, '237' => 178, '243' => 179, '248' => 180, '254' => 252, '256' => 181, '258' => 182, '268' => 183, '270' => 184, '284' => 185, '292' => 186, '304' => 187, '308' => 188, '318' => 189, '320' => 190, '322' => 191, '324' => 192, '349' => 193, '354' => 194, '357' => 195, '359' => 196, '368' => 197, '382' => 198, '392' => 199, '404' => 200, '407' => 201, '418' => 202, '428' => 203, '430' => 204, '442' => 205, '449' => 206, '452' => 207, '454' => 208, '462' => 209, '466' => 210, '479' => 211, '481' => 212, '483' => 213, '489' => 253, '499' => 214, '530' => 215, '536' => 216, '540' => 217, '550' => 218, '556' => 219, '563' => 220, '570' => 221, '583' => 222, '593' => 223, '601' => 224, '603' => 225, '606' => 226, '615' => 227, '626' => 228, '634' => 229, '636' => 230, '638' => 231, '640' => 232, '648' => 233, '657' => 234, '659' => 235, '663' => 236, '667' => 237, '675' => 780, '686' => 777, '726' => 783, '747' => 787,);
            
            $this->exposeMethod('migrateDocs');
        }

        public function process(Vtiger_Request $request) {
            $mode = $request->getMode();
            if(!empty($mode) && $this->isMethodExposed($mode)) {
                    $this->invokeExposedMethod($mode, $request);
                    return;
            }
        }
        
        function migrateDocs(Vtiger_Request $request) {
            global $adb, $log, $current_user;
            $mycsvfile = 'vtiger_notes_test.csv';
            $count = 0;
            $currentUser = Users_Record_model::getCurrentUserModel();
            $documents = $fileDetails = array();
            if (($handle = fopen($mycsvfile, "r")) !== FALSE)
            {
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE)
                {
                    if($count == 0){$count++; continue;}
                    
                    $data[15] = $this->contactMappingArr[$data[15]];
                    $documents[$count]['contactid'] = '12x'.$data[15];
                    $documents[$count]['notes_title'] = $data[2];
                    $documents[$count]['document_type'] = 'KYC';
                    $documents[$count]['sub_document_type'] = $data[13];
                    $documents[$count]['record_status'] = $data[14];
                    $documents[$count]['request_from'] = 'CustomerPortal';
                    $documents[$count]['status_reason'] = $data[12];
                    $documents[$count]['filename'] = $data[3];
                    $documents[$count]['filelocationtype'] = $data[7];
                    $documents[$count]['filetype'] = $data[6];
                    $documents[$count]['filestatus'] = $data[9];
                    $documents[$count]['filesize'] = $data[10];
                    $documents[$count]['assigned_user_id'] = '19x1';
                    
                    $oldFilePath = $data[20];
                    $oldFilePath = str_replace('domains/crm.graphenefx.com/', '', $oldFilePath);
                    $fileDetails[$count]['file_details'] = array(
                        'name' => $data[3],
                        'type' => $data[6],
                        'tmp_name' => '/tmp/'.time(),
                        'error' => 0,
                        'size' => $data[10],
                        'original_name' => '',
                        'old_file_path' => $oldFilePath,
                        'subject' => $data[21],
                        'old_attachmedid' => $data[16],
                    );
                    $count++;
                }
            }
//            pr($documents);
            $module = 'Documents';
            $attachmentType = 'Attachment';
            $date_var = date("Y-m-d H:i:s");
            foreach($documents as $key => $documentData)
            {
                if(!empty($documentData['contactid']))
                {
                    $documentResult = vtws_create($module, $documentData, $currentUser);
                    $idComponents = explode('x', $documentResult['id']);
                    $recordId = $idComponents[1];
                    
                    /*Start: create relation entry*/
                    list($tabId, $contactId) = explode('x', $documentData['contactid']);
                    $parentId = $contactId;
                    $contact = new Contacts();
                    $contact->save_related_module('Contacts', $parentId, 'Documents', array($recordId));
                    /*End: create relation entry*/
                    
                    if(isset($fileDetails[$key]['file_details']) && !empty($fileDetails[$key]['file_details']))
                    {
                        /*Start: Entry in attachment*/
                        $current_id = $adb->getUniqueID("vtiger_crmentity");
                        $ownerid = $current_user->id;
                        $filename = $fileDetails[$key]['file_details']['name'];
                        $filetype = $fileDetails[$key]['file_details']['type'];
                        $filetmp_name = $fileDetails[$key]['file_details']['tmp_name'];
                        $upload_file_path = $fileDetails[$key]['file_details']['old_file_path'];
                        $oldAttachId = $fileDetails[$key]['file_details']['old_attachmedid'];
                        $oldFileName = $oldAttachId . '_' . $fileDetails[$key]['file_details']['name'];
                        $encryptFileName = Vtiger_Util_Helper::getEncryptedFileName($filename);
                        
                        //Add entry to crmentity
                        $sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $params1 = array($current_id, $current_user->id, $ownerid, $module." ".$attachmentType, '', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                        $adb->pquery($sql1, $params1);

                        //Add entry to attachments
                        $sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path, storedname, subject) values(?, ?, ?, ?, ?, ?, ?)";
                        $params2 = array($current_id, $filename, '', $filetype, $upload_file_path, $encryptFileName, $oldFileName);
                        $adb->pquery($sql2, $params2);

                        //Add relation
                        $sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
                        $params3 = array($recordId, $current_id);
                        $adb->pquery($sql3, $params3);
                        /*End: Entry in attachment*/
                        
                        /*Rename filename*/
                        $oldFullFilePath = $upload_file_path.$oldFileName;
                        $newFullFilePath = $upload_file_path.$current_id.'_'.$encryptFileName;
                        if(fopen($oldFullFilePath, "r"))
                        {
                            rename($oldFullFilePath,$newFullFilePath);
                        }
                        /*Rename filename*/
                    }
                }
            }
            echo 'migration completed';exit;
        }
	
}

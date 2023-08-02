<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_KycRecord_Model extends Vtiger_Record_Model {

    function getKycAnswers($contactId = "")
    {
        global $adb,$log;
        $kycAnswers = array();
        if(!empty($contactId))
        {
            $sql = "SELECT kq.id as questionid,ka.answer,kq.question,kq.question_type,kq.options,kq.sequence FROM kyc_questionnaire kq
                    LEFT JOIN kyc_answers ka ON ka.question_id = kq.id AND ka.contact_id = ?
                    WHERE kq.status = ?
                    ORDER BY kq.sequence ASC";
            $sqlResult = $adb->pquery($sql, array($contactId, 'active'));
            $queCount = $adb->num_rows($sqlResult);
            for($i=0; $i<$queCount; $i++)
            {
                $kycAnswers[$i]['question_id'] = $adb->query_result($sqlResult, $i, 'questionid');
                $kycAnswers[$i]['question'] = $adb->query_result($sqlResult, $i, 'question');
                $kycAnswers[$i]['answer'] = $adb->query_result($sqlResult, $i, 'answer');
                $kycAnswers[$i]['question_type'] = $adb->query_result($sqlResult, $i, 'question_type');
                $kycAnswers[$i]['options'] = $adb->query_result($sqlResult, $i, 'options');
                if($kycAnswers[$i]['question_type'] == 'dropdown')
                {
                    // $kycAnswers[$i]['options'] = str_replace('##', ',', $kycAnswers[$i]['options']);
                    $options = explode('##',$kycAnswers[$i]['options']);
                    $kycAnswers[$i]['options'] = $options;
                }
                $kycAnswers[$i]['sequence'] = $adb->query_result($sqlResult, $i, 'sequence');
            }
        }
        return $kycAnswers;
    }

    function saveKycAnswers($data = array())
    {
        global $adb,$log;
        $log->debug('Entering into saveKycAnswers');
        try {
            if(!empty($data))
            {
                $createdtime = date('Y-m-d H:i:s');
                $modifiedtime = date('Y-m-d H:i:s');
                foreach ($data as $key => $value)
                {
                    list($tabid, $contactId) = explode('x', $value['contact_id']);
                    $selectQuery = "SELECT id FROM `kyc_answers` WHERE question_id = ? AND contact_id = ?";
                    $selectQueryResult = $adb->pquery($selectQuery, array($value['question_id'], $contactId));
                    $noOfAnswer = $adb->num_rows($selectQueryResult);
                    if($noOfAnswer > 0)
                    {
                        $answerId = $adb->query_result($selectQueryResult,0,'id');
                        $updateQuery = "UPDATE `kyc_answers` SET answer = ?, updated_time = ? WHERE id = ?;";
                        $updateQueryResult = $adb->pquery($updateQuery, array($value['answer'], $modifiedtime, $answerId));
                    }
                    else
                    {
                        $insertValues = "('" . $value['question_id'] . "','" . $contactId . "','" . $value['answer'] . "','" . $createdtime . "','" . $modifiedtime . "')";
                        $insertQuery = "INSERT INTO `kyc_answers` (`question_id`, `contact_id`, `answer`, `created_time`, `updated_time`) VALUES " . $insertValues . ";";
                        $insertQueryResult = $adb->pquery($insertQuery, array());
                    }
                }
            }
            else
            {
                throw new Exception('Please check parameters!');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function updateKycFormStatus($contactId = '', $kycStatus = 'Pending')
    {
        global $adb,$log;
        $log->debug('Entering into updateKycFormStatus');
        try {
            if(!empty($contactId))
            {
                $extraQuery = "";
                if($kycStatus == "Sent for edit")
                {
                    $extraQuery = ",kyc_form_edit = 0";
                }
                else if($kycStatus == "Allow for edit")
                {
                    $extraQuery = ",kyc_form_edit = 0";
                    $kycStatus = "Sent for edit";
                }
                $updateQuery = "UPDATE `vtiger_contactdetails` SET kyc_form_status = ? ". $extraQuery ." WHERE contactid = ?;";
                $updateQueryResult = $adb->pquery($updateQuery, array($kycStatus, $contactId));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function getTotalKycApprovedDocCount($contactId = "")
    {
        global $adb,$log;
        $log->debug('Entering into getTotalKycApprovedDocCount');
        $noOfApprovedDoc = 0;
        if(!empty($contactId))
        {
            $query = "SELECT COUNT(vtiger_notes.notesid) AS no_of_id_proof_approved FROM `vtiger_notes` 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid 
                WHERE vtiger_crmentity.deleted = 0 AND `vtiger_notes`.`record_status` = ? AND vtiger_notes.contactid = ? AND vtiger_notes.document_type = ?";
            $result = $adb->pquery($query, array('Approved',$contactId,'KYC'));
            $row = $adb->fetchByAssoc($result);
            $noOfApprovedDoc = $row['no_of_id_proof_approved'];
        }
        $log->debug('$noOfApprovedDoc='.$noOfApprovedDoc);
        return $noOfApprovedDoc;
    }
}

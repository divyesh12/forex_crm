<?php

require_once 'modules/Vtiger/CRMEntity.php';

class VertexHelper extends CRMEntity
{
    
    public static function getVertexClientIdOld($contactid = '')
    {
        global $adb;
        $vertexClientId = '';
        if(!empty($contactid))
        {
            $sql = 'SELECT vertex_clientid FROM vertex_contact_mapping WHERE contactid = ?';
            $result = $adb->pquery($sql, array($contactid));
            $vertexClientId = $adb->query_result($result, 0, 'vertex_clientid');
        }
        return $vertexClientId;
    }
    
    public static function getVertexClientId($accountNo = '', $accountType = '')
    {
        global $adb;
        $vertexClientId = '';
        $tableName = 'vertex_liveaccount_mapping';
        $whereFieldName = 'liveaccount_number';
        if(!empty($accountType) && $accountType == 'DemoAccount')
        {
            $tableName = 'vertex_demoaccount_mapping';
            $whereFieldName = 'demoaccount_number';
        }
        if(!empty($accountNo))
        {
            $sql = 'SELECT vertex_clientid FROM '.$tableName.' WHERE '.$whereFieldName.' = ?';
            $result = $adb->pquery($sql, array($accountNo));
            $vertexClientId = $adb->query_result($result, 0, 'vertex_clientid');
        }
        return $vertexClientId;
    }

}

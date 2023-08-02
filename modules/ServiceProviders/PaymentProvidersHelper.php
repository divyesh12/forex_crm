<?php

require_once 'modules/Vtiger/CRMEntity.php';

class PaymentProvidersHelper extends CRMEntity
{

    /**
     * Function to creating the payment log
     * @param <String> $order_id Passing the order id which generated for each transaction
     * @param <array> $request Passing the data which requested by User
     * @param <array> $request_payload Passing data which sending to Payment Gateway
     * @param <array> $response_payload Passing data which generated at token creation time
     * @createdBy Sandeep Thakkar
     */
    public static function createPaymentLog($order_id, $provider_type, $provider_title, $request, $status, $event)
    {
        global $adb;

        $query = "INSERT INTO `vtiger_payment_logs` (`order_id`, `provider_type`, `provider_title`,`data`, `status`, `event`, `createdtime`)
VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())";
        $result = $adb->pquery($query, array($order_id, $provider_type, $provider_title, json_encode($request), $status, $event));
        if ($result) {
            return true;
        } else {
            return false;
        }

    }

    public static function getPaymentRecord($order_id)
    {
        global $adb;

        $query = "SELECT * FROM  `vtiger_payments` AS `p` INNER JOIN  `vtiger_crmentity` AS `e` ON `p`.`paymentsid` = `e`.`crmid` WHERE `p`.`order_id` = ? AND `p`.`payment_status` = ?";
        $result = $adb->pquery($query, array($order_id, 'Pending'));
        $rowCount = $adb->num_rows($result);
        if ($rowCount > 0) {
            return true;
        } else {
            return false;
        }

    }

    public static function generateUUID()
    {
        global $adb;
        $query = "SELECT UUID() as uuid";
        $result = $adb->pquery($query, array());
        if ($result) {
            $num_rows = $adb->num_rows($result);
            if ($num_rows > 0) {
                return $adb->query_result($result, 0, 'uuid');
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getContactDetails($contactid)
    {
        global $adb;

        $query = "SELECT `firstname`, `lastname`, `email`, `mobile`, `contact_no` FROM  `vtiger_contactdetails`  WHERE `contactid` = ?";
        $result = $adb->pquery($query, array($contactid));
        $rowCount = $adb->num_rows($result);
        $row = array();
        if ($rowCount > 0) {
            $row['firstname'] = $adb->query_result($result, 0, 'firstname');
            $row['lastname'] = $adb->query_result($result, 0, 'lastname');
            $row['email'] = $adb->query_result($result, 0, 'email');
            $row['mobile'] = $adb->query_result($result, 0, 'mobile');
            $row['contact_no'] = $adb->query_result($result, 0, 'contact_no');
            return $row;
        } else {
            return false;
        }
    }

    public static function getCurrencyRate($from_currency, $to_currency, $operation_type)
    {
        global $adb;

        $query = "SELECT `conversion_rate`  FROM  `vtiger_currencyconverter`  WHERE `from_currency` = ? AND `to_currency` = ? AND `operation_type` = ? ";
        $result = $adb->pquery($query, array($from_currency, $to_currency, $operation_type));
        $rowCount = $adb->num_rows($result);
        if ($rowCount > 0) {
            return $conversion_rate = $adb->query_result($result, 0, 'conversion_rate');
        } else {
            return false;
        }
    }

}

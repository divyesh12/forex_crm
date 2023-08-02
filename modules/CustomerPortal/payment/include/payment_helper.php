<?php

class PaymentHelper {

    private $request;

    public function __construct($request) {
        $this->request = $request;
    }

    public function getOperation() {
        return $this->request['payment_operation'];
    }

    public function isValid() {
        if (empty($this->request['payment_operation']) || empty($this->request['token'])) {
            unset($_SESSION);
            exit("Invalid Parameters11!");
        }
        $_SESSION['request'] = $this->request;
    }

    public function fetchDepositReceipt($file_id) {
        $data = array(
            '_operation' => 'DownloadFile',
            'module' => 'Documents',
            'recordId' => $file_id
        );
        $result = $this->apiProcess($data, $this->request['token']);
        if ($result !== false && !$result->hasError()) {
            return $result->getResult();
        } else {
            exit("Error in fetch deposit reciept");
        }
    }

    public function fetchLanguageLabels() {
        $data = array(
            '_operation' => 'FetchLanguageLabels'
        );
        $result = $this->apiProcess($data, $this->request['token']);
        if ($result !== false && !$result->hasError()) {
            return $result->getResult();
        } else {
            exit("Error in fetch labels!");
        }
    }

    public function fetchConfiguration() {
        $data = array(
            '_operation' => 'FetchConfiguration'
        );
        $result = $this->apiProcess($data, $this->request['token']);
        if ($result !== false && !$result->hasError()) {
            return $result->getResult();
        } else {
            exit("Error in fetch configuration!");
        }
    }

    public function describePaymentGateway() {
        $data = array(
            '_operation' => 'DescribePaymentGateway',
            'module' => 'Payments',
            'payment_operation' => $this->request['payment_operation'],
            'payment_name' => $this->request['payment_gateway'],
            'response_type' => 'List',
        );
        $result = $this->apiProcess($data, $this->request['token']);

        if ($result !== false && !$result->hasError()) {
            return $result->getResult();
        } else {
            exit("Error in DescribePaymentGateway!");
        }
    }

    public function submitProcess($post) {
        $arr_labels = $this->fetchLanguageLabels();


        $post['payment_from'] = $this->request['payment_gateway']; //P2A
        if ($post['payment_type'] == 'P2E') {
            $post['payment_to'] = 'Wallet';
        } else if ($post['payment_type'] == 'E2P') {
            $post['payment_from'] = 'Wallet';
            $post['payment_to'] = $this->request['payment_gateway'];
        } else if ($post['payment_type'] == 'A2P') {
            $post['payment_from'] = $post['payment_to'];
            $post['payment_to'] = $this->request['payment_gateway'];
        }
        $values = $post;
        $values['request_from'] = 'CustomerPortal';
        $values['is_mobile_request'] = true;
        $values['payment_operation'] = $this->request['payment_operation'];
        $data = array(
            '_operation' => 'PaymentProcess',
            'module' => 'Payments',
            'sub_operation' => "Submit",
            'values' => json_encode($values)
        );
        if (!empty($_FILES)) {
            //File verification
            //$this->verifyFileInputData($_FILES, $post['file_size'], $arr_labels);
            $data['file'] = $_FILES['file'];
        }
        $_SESSION['confirm_data'] = $post;
        $result = $this->apiProcess($data, $this->request['token']);
        $res = '';
        $response = new CustomerPortal_API_Response();
        if ($result !== false && !$result->hasError()) {
            if (!empty($result->getResult()['custom_data'])) {
                $res = $result->getResult();
                foreach ($res['custom_data'] as &$value) {
                    $value['label'] = $arr_labels['Payments'][$value['label']];
                    if ($value['type'] == 'file') {
                        $doc_data = $this->fetchDepositReceipt($value['value']);
                        if (!empty($doc_data)) {
                            $value['filename'] = $doc_data['filename'];
                        }
                    }
                }
                $response->setResult($res);
            } else {
                $response->setResult($result->getResult());
            }

            header("Content-type:text/json;charset=UTF-8");
            echo $response->emitJSON();
            exit;
        } else {
            header("Content-type:text/json;charset=UTF-8");
            $response->setError($result->getError()['code'], $result->getError()['message']);
            echo $response->emitJSON();
            exit;
        }
    }

    public function confirmProcess($post) {
        $values = $_SESSION['confirm_data'];
        $values['request_from'] = 'CustomerPortal';
        $values['is_mobile_request'] = true;
        $values['payment_operation'] = $this->request['payment_operation'];
        if (isset($post['file']) && $post['file'] != '')
            $values['file'] = $post['file'];

        $data = array(
            '_operation' => 'PaymentProcess',
            'module' => 'Payments',
            'sub_operation' => "Confirm",
            'values' => json_encode($values)
        );
        
        if ($post['otp']) {
            $data['otp'] = $post['otp'];
        }
        $result = $this->apiProcess($data, $this->request['token']);
        $response = new CustomerPortal_API_Response();
        if ($result !== false && !$result->hasError()) {
            $_SESSION['confirm_data_response'] = $result->getResult();
            $response->setResult($result->getResult());
            header("Content-type:text/json;charset=UTF-8");
            echo $response->emitJSON();
            exit;
        } else {
            header("Content-type:text/json;charset=UTF-8");
            $response->setError($result->getError()['code'], $result->getError()['message']);
            echo $response->emitJSON();
            exit;
        }
    }

    public function successProcess($post) {
        //Request to call call back CRM API
        if (!empty($post) && !empty($post['payment_response']) && $post['payment_response']['orid'] == $_SESSION['confirm_data_response']['order_id']) {
            $values = $_SESSION['confirm_data'];
            $values['request_from'] = 'CustomerPortal';
            $values['payment_operation'] = $this->request['payment_operation'];
            $values['payment_from'] = $this->request['payment_gateway'];

            $data = array(
                '_operation' => 'PaymentCallBack',
                'module' => 'Payments',
                'sub_operation' => $_SESSION['request']['payment_operation'],
                'record_id' => $_SESSION['confirm_data_response']['record_id'],
                'order_id' => $_SESSION['confirm_data_response']['order_id'],
                'payment_from' => $_SESSION['confirm_data_response']['payment_from'],
                'payment_response' => json_encode($post['payment_response'], TRUE),
                'status' => $post['sub_operation']
            );
            $result = $this->apiProcess($data, $this->request['token']);
            if ($result !== false && !$result->hasError()) {
                if ($result->getResult()['payment_status'] == 'Completed' || $result->getResult()['payment_status'] == 'PaymentSuccess') {
                    header('Location: payment_success.php?message=' . urlencode($result->getResult()['message']));
                } else if (in_array($result->getResult()['payment_status'], array('Failed', 'Cancelled'))) {
                    header('Location: payment_fail.php?message=' . urlencode($result->getResult()['message']));
                } else if (in_array($result->getResult()['payment_status'], array('Pending'))) {
                    header('Location: payment_pending.php?pm=' . $_SESSION['confirm_data_response']['payment_from'] . '&message=' . urlencode('Deposit request has been sent successfully.'));
                } else {
                    header('Location: payment_fail.php?message=' . urlencode('Error in payment confirm process. Please try again!'));
                }
            } else {
                header('Location: payment_fail.php?message=' . urlencode($result->getError()['message']));
            }
        } else {
            header('Location: payment_fail.php?message=' . urlencode('Error in payment confirm process. Please try again!'));
        }
//End
    }

    public function postProcess($post) {
        if (isset($post['sub_operation'])) {
            switch ($post['sub_operation']) {
                case "Submit":
                    $this->submitProcess($post);
                    break;
                case "Confirm":
                    $this->confirmProcess($post);
                    break;
                case "Success":
                    $this->successProcess($post);
                    break;
                case "Pending":
                    $this->successProcess($post);
                    break;
                case "Failed":
                    $this->successProcess($post);
                    break;
                default :
                    exit("Invalid Operation!");
            }
        } else {
            exit("Invalid Operation!");
        }
    }

    public function apiProcess($data, $token) {
        $user = explode(':', base64_decode($token))[0];
        $pass = explode(':', base64_decode($token))[1];

        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pass;

        $clientRequestValues = $data;
        if (get_magic_quotes_gpc()) {
            $clientRequestValues = $this->stripslashes_recursive($clientRequestValues);
        }
        
        $source = 'MOBILE';
        $clientRequestValuesRaw = array();
        return CustomerPortal_API_EntryPoint::process(new CustomerPortal_API_Request($clientRequestValues, $clientRequestValuesRaw), $source);
    }

    public function stripslashes_recursive($value) {
        $value = is_array($value) ? array_map('$this->stripslashes_recursive', $value) : stripslashes($value);
        return $value;
    }

    function verifyFileInputData($FILES, $file_size, $label_arr) {
        $file_type = array('JPEG', 'JPG', 'PNG', 'PDF');
        $file_size = $file_size * 1000000;
        $response = new CustomerPortal_API_Response();
        if (!in_array(strtoupper(pathinfo($FILES['file']['name'])['extension']), $file_type)) {
            $response->setError(1404, $label_arr['CustomerPortal_Client']['CAB_MSG_FILE_TYPE_DOES_NOT_ALLOWED']);
        } else if ($FILES['file']['size'] <= 0 || $FILES['file']['size'] > $file_size) {
            $response->setError(1404, $label_arr['CustomerPortal_Client']['CAB_MSG_FILE_SIZE_SHOULD_NOT_BE_GREATER_THAN_MB']);
        } else {
            return true;
        }
        header("Content-type:text/json;charset=UTF-8");
        echo $response->emitJSON();
        exit;
    }

    public function resendOtpProcess() {
        $response = new CustomerPortal_API_Response();
        $data = array(
            '_operation' => 'SendOtp',
            'module' => 'CustomerPortal',
            'type' => 'withdrawal'
        );
        $result = $this->apiProcess($data, $this->request['token']);
        header("Content-type:text/json;charset=UTF-8");
        if (!$result->hasError())
        {
            $response->setResult($result->getResult());
        }
        else
        {
            $response->setError($result->getError()['code'], $result->getError()['message']);   
        }
        echo $response->emitJSON();
        exit;
    }

}

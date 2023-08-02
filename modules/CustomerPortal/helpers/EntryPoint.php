<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of apiHelper
 *
 * @author Iconflux
 */
class CustomerPortal_API_EntryPoint {

    protected static function authenticate(CustomerPortal_API_Abstract $controller, CustomerPortal_API_Request $request) {
        // Fix: https://bugs.php.net/bug.php?id=35752
        if (!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['HTTP_AUTHORIZATION'])) {

            if (preg_match('/Basic\s+(.*)$/i', $_SERVER['Authorization'], $matches)) {
                list($name, $password) = explode(':', base64_decode($matches[1]));
                $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
                $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
            }
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authorization = $headers['Authorization'];
        } else {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($authorization)) {

            if (!vtlib_isModuleActive("Contacts")) {
                throw new Exception("Contacts module is disabled", 1412);
            }
            if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
                list($contactid, $token) = explode(':', base64_decode($matches[1]));
                $uname = $controller->getUserDataById($contactid);
                if ($token == "dsadsad4sad5s6ad" && $uname != NULL) {
                    $ok = $controller->authenticatePortalUser($uname, "", false);
                    if (!$ok) {
                        throw new Exception("Login failed", 141220);
                    }
                } else {
                    header('WWW-Authenticate: Basic realm="Customer Portal"');
                    header('HTTP/1.0 401 Unauthorized');
                    throw new Exception("Login Required", 141220);
                    exit;
                }
            } else {
                $ok = $controller->authenticatePortalUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], true);
                if (!$ok) {
                    throw new Exception("Login failed", 141220);
                }
            }
        } else if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Customer Portal"');
            header('HTTP/1.0 401 Unauthorized');
            throw new Exception("Login Required", 141220);
            exit;
        } else {
            // Handling the case Contacts module is disabled 
            if (!vtlib_isModuleActive("Contacts")) {
                throw new Exception("Contacts module is disabled", 1412);
            }

            //$ok = $controller->authenticatePortalUser($request->get('username'), $request->get('password'));
            $ok = $controller->authenticatePortalUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], true);
            if (!$ok) {
                throw new Exception("Login failed", 14120);
            }
        }
    }

    static function process(CustomerPortal_API_Request $request, $source = '') {        
        $operation = $request->getOperation();
        $response = false;

        if (!preg_match("/[0-9a-zA-z]*/", $operation, $match)) {
            throw new Exception("Invalid entry", 1412);
        }

        if ($operation == $match[0]) {
            $operationFile = sprintf('/../apis/%s.php', $operation);
            $operationClass = sprintf("CustomerPortal_%s", $operation);

            include_once dirname(__FILE__) . $operationFile;
            $operationController = new $operationClass;

            try {
                self::authenticate($operationController, $request);

                //setting active user language as Portal user language 
                $current_user = $operationController->getActiveUser();
                $portal_language = $request->getLanguage();
                $current_user->column_fields["language"] = $portal_language;
                $current_user->language = $portal_language;

                //set soruce by Sandeep Thakkar 11-05-2021
                $current_user->column_fields['source'] = $source;
                

                $response = $operationController->process($request);
            } catch (Exception $e) {
                $response = new CustomerPortal_API_Response();
                $response->setError($e->getCode(), $e->getMessage());
            }
        } else {
            $response = new CustomerPortal_API_Response();
            $response->setError(1404, 'Operation not found: ' . $operation);
        }

        return $response;
    }

}

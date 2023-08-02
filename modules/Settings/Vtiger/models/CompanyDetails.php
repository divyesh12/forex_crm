<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Vtiger_CompanyDetails_Model extends Settings_Vtiger_Module_Model {

	// STATIC $logoSupportedFormats = array('jpeg', 'jpg', 'png', 'gif', 'pjpeg', 'x-png');
 //    STATIC $bannerSupportedFormats = array('jpg', 'png', 'jpeg'); 
	// STATIC $faviconSupportedFormats = array('ico', 'jpg', 'png', 'jpeg', 'gif', 'pjpeg', 'x-png', 'x-icon');

	STATIC $logoSupportedFormats = array('png');
    STATIC $bannerSupportedFormats = array('jpg', 'jpeg', 'svg', 'svg+xml'); 
	STATIC $faviconSupportedFormats = array('ico', 'x-icon', 'vnd.microsoft.icon');

	var $baseTable = 'vtiger_organizationdetails';
	var $baseIndex = 'organization_id';
	var $listFields = array('organizationname');
	var $nameFields = array('organizationname');
	// var $logoPath = 'test/logo/';
	// var $bannerPath = 'test/logo/';
	// var $faviconPath = 'test/logo/';
	var $crmPath = 'test/logo/';
	var $cabinetPath = 'test/logo/cabinet/';

	var $fields = array(
		'organizationname' => 'text',
		'logoname' => 'text',
		'logo' => 'file',
		'website' => 'text',
		'address' => 'textarea',
		'city' => 'text',
		'state' => 'text',
		'code'  => 'text',
		'country' => 'text',
		'phone' => 'text',
		'fax' => 'text',
		'vatid' => 'text',
		'bannername' => 'text',
		'faviconname' => 'text', 
		'cabinetlogoname' => 'text',
		'cabinetbannername' => 'text',
		'cabinetfaviconname' => 'text', 
		'sidebar_color' => 'text',
	);

	var $companyBasicFields = array(
		'organizationname' => 'text',
		'logoname' => 'text',
		'logo' => 'file',
		'website' => 'text',
		'address' => 'textarea',
		'city' => 'text',
		'state' => 'text',
		'code'  => 'text',
		'country' => 'text',
		'phone' => 'text',
		'fax' => 'text',
		'vatid' => 'text',
		'bannername' => 'text',
		'faviconname' => 'text',
		'cabinetlogoname' => 'text',
		'cabinetbannername' => 'text',
		'cabinetfaviconname' => 'text', 
		'sidebar_color' => 'text',
	);

	var $companySocialLinks = array(
		// 'website' => 'text',
	);

	/**
	 * Function to get Edit view Url
	 * @return <String> Url
	 */
	public function getEditViewUrl() {
		return 'index.php?module=Vtiger&parent=Settings&view=CompanyDetailsEdit';
	}

	/**
	 * Function to get CompanyDetails Menu item
	 * @return menu item Model
	 */
	public function getMenuItem() {
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_COMPANY_DETAILS');
		return $menuItem;
	}

	/**
	 * Function to get Index view Url
	 * @return <String> URL
	 */
	public function getIndexViewUrl() {
		$menuItem = $this->getMenuItem();
		return 'index.php?module=Vtiger&parent=Settings&view=CompanyDetails&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
	}

	/**
	 * Function to get fields
	 * @return <Array>
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Function to get Logo path to display
	 * @return <String> path
	 */
	public function getLogoPath() {
		$logoPath = $this->crmPath;
		$handler = @opendir($logoPath);
		$logoName = decode_html($this->get('logoname'));
		if ($logoName && $handler) {
			while ($file = readdir($handler)) {
				if($logoName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$logoSupportedFormats) && $file != "." && $file!= "..") {
					closedir($handler);
					return $logoPath.$logoName;
				}
			}
		}
		return '';
	}

	public function getLogoPathCabinet() {
		$logoPath = $this->cabinetPath;
		$handler = @opendir($logoPath);
		$logoName = decode_html($this->get('cabinetlogoname'));
		if ($logoName && $handler) {
			while ($file = readdir($handler)) {
				if($logoName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$logoSupportedFormats) && $file != "." && $file!= "..") {
					closedir($handler);
					return $logoPath.$logoName;
				}
			}
		}
		return '';
	}


	/**
	 * Function to get Banner path to display
	 * @return <String> path
	 */
	public function getBannerPath() {
        $bannerPath = $this->crmPath;
        $handler = @opendir($bannerPath);
        $bannerName = decode_html($this->get('bannername'));
        if ($bannerName && $handler) {
            while ($file = readdir($handler)) {
                if ($bannerName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$bannerSupportedFormats) && $file != "." && $file != "..") {
                    closedir($handler);
                    return $bannerPath . $bannerName;
                }
            }
        }
        return '';
    }

    public function getBannerPathCabinet() {
        $bannerPath = $this->cabinetPath;
        $handler = @opendir($bannerPath);
        $bannerName = decode_html($this->get('cabinetbannername'));
        if ($bannerName && $handler) {
            while ($file = readdir($handler)) {
                if ($bannerName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$bannerSupportedFormats) && $file != "." && $file != "..") {
                    closedir($handler);
                    return $bannerPath . $bannerName;
                }
            }
        }
        return '';
    }

    /**
	 * Function to get Favicon path to display
	 * @return <String> path
	 */
    public function getFaviconPath() {
        $faviconPath = $this->crmPath;
        $handler = @opendir($faviconPath);
        $faviconName = decode_html($this->get('faviconname'));
        if ($faviconName && $handler) {
            while ($file = readdir($handler)) {
                if ($faviconName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$faviconSupportedFormats) && $file != "." && $file != "..") {
                    closedir($handler);
                    return $faviconPath . $faviconName;
                }
            }
        }
        return '';
    }

    public function getFaviconPathCabinet() {
        $faviconPath = $this->cabinetPath;
        $handler = @opendir($faviconPath);
        $faviconName = decode_html($this->get('cabinetfaviconname'));
        if ($faviconName && $handler) {
            while ($file = readdir($handler)) {
                if ($faviconName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$faviconSupportedFormats) && $file != "." && $file != "..") {
                    closedir($handler);
                    return $faviconPath . $faviconName;
                }
            }
        }
        return '';
    }


	/**
	 * Function to save the logoinfo
	 */
	public function saveLogo($logoName) {
		$uploadDir = vglobal('root_directory'). '/' .$this->crmPath;
		$logo_Extension = explode(".", $_FILES["logo"]["name"]);
		$logoName = $uploadDir . 'logo.' . $logo_Extension[1];
		move_uploaded_file($_FILES["logo"]["tmp_name"], $logoName);
		copy($logoName, $uploadDir.'application.ico');
	}

	public function saveLogoCabinet($logoNameCabinet) {
		$uploadDir = vglobal('root_directory'). '/' .$this->cabinetPath;
		$logo_Extension = explode(".", $_FILES["logocabinet"]["name"]);
		$logoNameCabinet = $uploadDir . 'logo.' . $logo_Extension[1];
		move_uploaded_file($_FILES["logocabinet"]["tmp_name"], $logoNameCabinet);
		copy($logoNameCabinet, $uploadDir.'application.ico');
	}
	
	/**
	 * Function to save the bannerinfo
	 */
	public function saveBanner($bannerName) {
		$uploadDir = vglobal('root_directory'). '/' .$this->crmPath;
		$banner_Extension = explode(".", $_FILES["banner"]["name"]);
        $bannerName = $uploadDir . 'login-background.' . $banner_Extension[1];
		move_uploaded_file($_FILES["banner"]["tmp_name"], $bannerName);
		copy($bannerName, $uploadDir.'application.ico');
    }

    public function saveBannerCabinet($bannerNameCabinet) {
		$uploadDir = vglobal('root_directory'). '/' .$this->cabinetPath;
		$banner_Extension = explode(".", $_FILES["bannercabinet"]["name"]);
        $bannerNameCabinet = $uploadDir . 'login_background.' . $banner_Extension[1];
		move_uploaded_file($_FILES["bannercabinet"]["tmp_name"], $bannerNameCabinet);
		copy($bannerNameCabinet, $uploadDir.'application.ico');
    }

    /**
	 * Function to save the faviconinfo
	 */
    public function saveFavicon($faviconName) {
    	$uploadDir = vglobal('root_directory'). '/' .$this->crmPath;
		$favicon_Extension = explode(".", $_FILES["company_favicon"]["name"]);
                $faviconName = $uploadDir . 'favicon.' . $favicon_Extension[1];
//		move_uploaded_file($_FILES["company_favicon"]["tmp_name"], $faviconName);
                copy($_FILES["company_favicon"]["tmp_name"], $faviconName);
		copy($faviconName, $uploadDir.'application.ico');
    }

    public function saveFaviconCabinet($faviconNameCabinet) {
    	$uploadDir = vglobal('root_directory'). '/' .$this->cabinetPath;
		$favicon_Extension = explode(".", $_FILES["company_faviconcabinet"]["name"]);
                $faviconNameCabinet = $uploadDir . 'favicon.' . $favicon_Extension[1];
//		move_uploaded_file($_FILES["company_faviconcabinet"]["tmp_name"], $faviconNameCabinet);
		copy($_FILES["company_faviconcabinet"]["tmp_name"], $faviconNameCabinet);
		copy($faviconNameCabinet, $uploadDir.'application.ico');
    }


	/**
	 * Function to save the Company details
	 */
	public function save() {
		$db = PearDatabase::getInstance();
		$id = $this->get('id');
		$fieldsList = $this->getFields();
		unset($fieldsList['logo']);
		$tableName = $this->baseTable;

		if ($id) {
			$params = array();

			$query = "UPDATE $tableName SET ";
			foreach ($fieldsList as $fieldName => $fieldType) {
				$query .= " $fieldName = ?, ";
				array_push($params, $this->get($fieldName));
			}
			$query .= " logo = NULL WHERE organization_id = ?";

			$logo_NameExplode = explode(".", $params[1]);
            $logo_newName = 'logo.' . $logo_NameExplode[1];

            $cabinetlogo_NameExplode = explode(".", $params[13]);
            $cabinetlogo_newName = 'logo.' . $cabinetlogo_NameExplode[1];
            
            $favicon_NameExplode = explode(".", $params[12]);
            $favicon_newName = 'favicon.' . $favicon_NameExplode[1];

            $cabinetfavicon_NameExplode = explode(".", $params[15]);
            $cabinetfavicon_newName = 'favicon.' . $cabinetfavicon_NameExplode[1];

            /* Added for banner */
            $bannerlogo_NameExplode = explode(".", $params[11]);
            $bannerlogo_newName = 'login-background.' . $bannerlogo_NameExplode[1];

            $cabinetbannerlogo_NameExplode = explode(".", $params[14]);
            $cabinetbannerlogo_newName = 'login_background.' . $cabinetbannerlogo_NameExplode[1];

            /* End */
            $logo_favicon_ReplaceName = array(1 => $logo_newName, 12 => $favicon_newName, 11 => $bannerlogo_newName, 13 => $cabinetlogo_newName, 15 => $cabinetfavicon_newName, 14 => $cabinetbannerlogo_newName);
            $params = array_replace($params, $logo_favicon_ReplaceName);
   
            array_push($params, $id);
		} else {
			$params = $this->getData();

			$query = "INSERT INTO $tableName (";
			foreach ($fieldsList as $fieldName => $fieldType) {
				$query .= " $fieldName,";
			}
			$query .= " organization_id) VALUES (". generateQuestionMarks($params). ", ?)";

			array_push($params, $db->getUniqueID($this->baseTable));
		}
		$db->pquery($query, $params);
	}

	/**
	 * Function to get the instance of Company details module model
	 * @return <Settings_Vtiger_CompanyDetais_Model> $moduleModel
	 */
	public static function getInstance($name = '') {
		$moduleModel = new self();
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_organizationdetails", array());
		if ($db->num_rows($result) == 1) {
			$moduleModel->setData($db->query_result_rowdata($result));
			$moduleModel->set('id', $moduleModel->get('organization_id'));
		}

		$moduleModel->getFields();
		return $moduleModel;
	}
}

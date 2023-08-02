<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_CompanyDetailsSave_Action extends Settings_Vtiger_Basic_Action {

	public function process(Vtiger_Request $request) {
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$reloadUrl = $moduleModel->getIndexViewUrl();

		try{
			$this->Save($request);
		} catch(Exception $e) {
			if($e->getMessage() == "LBL_INVALID_IMAGE") {
				$reloadUrl .= '&error=LBL_INVALID_IMAGE';
			} else if($e->getMessage() == "LBL_FIELDS_INFO_IS_EMPTY") {
				$reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
			}
		}
		header('Location: ' . $reloadUrl);
	}

	public function Save(Vtiger_Request $request) {
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$status = false;
		if ($request->get('organizationname')) {
			$saveLogo = $status = true;
			$saveFavicon = $status = true;
          	$saveBanner = $status = true;
          	$saveLogoCabinet = $status = true;
			$saveFaviconCabinet = $status = true;
          	$saveBannerCabinet = $status = true;
			$logoName = false;
			$bannerName = false;
			$faviconName = false;
			$logoNameCabinet = false;
			$bannerNameCabinet = false;
			$faviconNameCabinet = false;
			if(!empty($_FILES['logo']['name'])) {
				$logoDetails = $_FILES['logo'];
				$fileType = explode('/', $logoDetails['type']);
				$fileType = $fileType[1];

				if (!$logoDetails['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
					$saveLogo = false;
				}

				//mime type check
				$mimeType = mime_content_type($logoDetails['tmp_name']);
				$mimeTypeContents = explode('/', $mimeType);
				if (!$logoDetails['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
					$saveLogo = false;
				}

				// Check for php code injection
				$imageContents = file_get_contents($logoDetails["tmp_name"]);
				if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
					$saveLogo = false;
				}
				if ($saveLogo) {
					$logoName = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($logoDetails['name'], vglobal('upload_badext'))));
					$moduleModel->saveLogo($logoName);
				}
			} 

			if(!empty($_FILES['logocabinet']['name'])) {
				$logoDetailsCabinet = $_FILES['logocabinet'];
				$fileType = explode('/', $logoDetailsCabinet['type']);
				$fileType = $fileType[1];

				if (!$logoDetailsCabinet['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
					$saveLogoCabinet = false;
				}

				//mime type check
				$mimeType = mime_content_type($logoDetailsCabinet['tmp_name']);
				$mimeTypeContents = explode('/', $mimeType);
				if (!$logoDetailsCabinet['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
					$saveLogoCabinet = false;
				}

				// Check for php code injection
				$imageContents = file_get_contents($logoDetailsCabinet["tmp_name"]);
				if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
					$saveLogoCabinet = false;
				}
				if ($saveLogoCabinet) {
					$logoNameCabinet = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($logoDetailsCabinet['name'], vglobal('upload_badext'))));
					$moduleModel->saveLogoCabinet($logoNameCabinet);
				}
			} 

			if (!empty($_FILES['banner']['name'])) {
                $bannerDetails = $_FILES['banner'];
                $fileType = explode('/', $bannerDetails['type']);
                $fileType = $fileType[1];

                if (!$bannerDetails['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$bannerSupportedFormats)) {
                    $saveBanner = false;
                }

                //mime type check 
                $mimeType = mime_content_type($bannerDetails['tmp_name']);
                $mimeTypeContents = explode('/', $mimeType);
                if (!$bannerDetails['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$bannerSupportedFormats)) {
                  	$saveBanner = false;
                }

                // Check for php code injection
                $imageContents = file_get_contents($bannerDetails["tmp_name"]);
                if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
                  	$saveBanner = false;
                }

                if ($saveBanner) {
                	$bannerName = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($bannerDetails['name'], vglobal('upload_badext'))));
                  	$moduleModel->saveBanner($bannerName);
                }
          	}

          	if (!empty($_FILES['bannercabinet']['name'])) {
                $bannerDetailsCabinet = $_FILES['bannercabinet'];
                $fileType = explode('/', $bannerDetailsCabinet['type']);
                $fileType = $fileType[1];

                if (!$bannerDetailsCabinet['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$bannerSupportedFormats)) {
                    $saveBannerCabinet = false;
                }

                //mime type check 
                $mimeType = mime_content_type($bannerDetailsCabinet['tmp_name']);
                $mimeTypeContents = explode('/', $mimeType);
                if (!$bannerDetailsCabinet['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$bannerSupportedFormats)) {
                  	$saveBannerCabinet = false;
                }

                // Check for php code injection
                $imageContents = file_get_contents($bannerDetailsCabinet["tmp_name"]);
                if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
                  	$saveBannerCabinet = false;
                }

                if ($saveBannerCabinet) {
                	$bannerNameCabinet = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($bannerDetailsCabinet['name'], vglobal('upload_badext'))));
                  	$moduleModel->saveBannerCabinet($bannerNameCabinet);
                }
          	}

          	if (!empty($_FILES['company_favicon']['name'])) {
                $faviconDetails = $_FILES['company_favicon'];
                $fileType = explode('/', $faviconDetails['type']);
                $fileType = $fileType[1];

                if (!$faviconDetails['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$faviconSupportedFormats)) {
                  	$saveFavicon = false;
                }

                //mime type check 
                $mimeType = mime_content_type($faviconDetails['tmp_name']);
                $mimeTypeContents = explode('/', $mimeType);
                if (!$faviconDetails['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$faviconSupportedFormats)) {
                  	$saveFavicon = false;
                }

                // Check for php code injection
                $imageContents = file_get_contents($faviconDetails["tmp_name"]);
                if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
                  	$saveFavicon = false;
                }

                if ($saveFavicon) {
                	$faviconName = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($faviconDetails['name'], vglobal('upload_badext'))));
                  	$moduleModel->saveFavicon($faviconName);
                }
          	}

          	if (!empty($_FILES['company_faviconcabinet']['name'])) {
                $faviconDetailsCabinet = $_FILES['company_faviconcabinet'];
                $fileType = explode('/', $faviconDetailsCabinet['type']);
                $fileType = $fileType[1];

                if (!$faviconDetailsCabinet['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$faviconSupportedFormats)) {
                  	$saveFaviconCabinet = false;
                }

                //mime type check 
                $mimeType = mime_content_type($faviconDetailsCabinet['tmp_name']);
                $mimeTypeContents = explode('/', $mimeType);
                if (!$faviconDetailsCabinet['size'] || $mimeTypeContents[0] != 'image' || !in_array($mimeTypeContents[1], Settings_Vtiger_CompanyDetails_Model::$faviconSupportedFormats)) {
                  	$saveFaviconCabinet = false;
                }

                // Check for php code injection
                $imageContents = file_get_contents($faviconDetailsCabinet["tmp_name"]);
                if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
                  	$saveFaviconCabinet = false;
                }

                if ($saveFaviconCabinet) {
                	$faviconNameCabinet = ltrim(basename(' '.Vtiger_Util_Helper::sanitizeUploadFileName($faviconDetailsCabinet['name'], vglobal('upload_badext'))));
                  	$moduleModel->saveFaviconCabinet($faviconNameCabinet);
                }
          	}

			else{
				$saveLogo = true;
				$saveFavicon = true;
                $saveBanner = true;
                $saveLogoCabinet = true;
				$saveFaviconCabinet = true;
	          	$saveBannerCabinet = true;
			}
			$fields = $moduleModel->getFields();
			foreach ($fields as $fieldName => $fieldType) {
				$fieldValue = $request->get($fieldName);

				if ($fieldName === 'logoname') {
					if (!empty($logoDetails['name']) && $logoName) {
						$fieldValue = decode_html(ltrim(basename(" " . $logoName)));
					} else {
						$fieldValue = decode_html($moduleModel->get($fieldName));
					}
				}
				if ($fieldName === 'cabinetlogoname') {
					if (!empty($logoDetailsCabinet['name']) && $logoNameCabinet) {
						$fieldValue = decode_html(ltrim(basename(" " . $logoNameCabinet)));
					} else {
						$fieldValue = decode_html($moduleModel->get($fieldName));
					}
				}
                if ($fieldName === 'bannername') {
                  	if (!empty($bannerDetails['name']) && $bannerName) {
                        $fieldValue = decode_html(ltrim(basename(" " . $bannerName)));
                  	} else {
                        $fieldValue = decode_html($moduleModel->get($fieldName));
                  	}
                }
                if ($fieldName === 'cabinetbannername') {
                  	if (!empty($bannerDetailsCabinet['name']) && $bannerNameCabinet) {
                        $fieldValue = decode_html(ltrim(basename(" " . $bannerNameCabinet)));
                  	} else {
                        $fieldValue = decode_html($moduleModel->get($fieldName));
                  	}
                }
				if ($fieldName === 'faviconname') {
                  	if (!empty($faviconDetails['name']) && $faviconName) {
                        $fieldValue = decode_html(ltrim(basename(" " . $faviconName)));
                  	} else {
                        $fieldValue = decode_html($moduleModel->get($fieldName));
                  	}
                }
                if ($fieldName === 'cabinetfaviconname') {
                  	if (!empty($faviconDetailsCabinet['name']) && $faviconNameCabinet) {
                        $fieldValue = decode_html(ltrim(basename(" " . $faviconNameCabinet)));
                  	} else {
                        $fieldValue = decode_html($moduleModel->get($fieldName));
                  	}
                }

				// In OnBoard company detail page we will not be sending all the details
				// if($request->has($fieldName) || ($fieldName == "logoname")) {
					$moduleModel->set($fieldName, $fieldValue);
				// }
			}
			$moduleModel->save();
		}
		if (($saveLogo || $saveFavicon || $saveBanner || $saveLogoCabinet || $saveFaviconCabinet || $saveBannerCabinet) && $status) {
			return ;
		} else if (!$saveLogo || !$saveFavicon || !$saveBanner || !$saveLogoCabinet || !$saveFaviconCabinet || !$saveBannerCabinet) {
			throw new Exception('LBL_INVALID_IMAGE',103);
			//$reloadUrl .= '&error=';
		} else {
			throw new Exception('LBL_FIELDS_INFO_IS_EMPTY',103);
			//$reloadUrl = $moduleModel->getEditViewUrl() . '&error=';
		}
		return;
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
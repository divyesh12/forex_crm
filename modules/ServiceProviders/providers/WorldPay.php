<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once('modules/ServiceProviders/PaymentProvidersHelper.php');

class ServiceProviders_WorldPay_Provider extends ServiceProviders_AbstractPaymentGatways_Model {

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    public $sftpDartFileLocation = '/home/iconflux/Documents/worldpay/'; // Common label file
    public $supportedCurrencyConversion = array('USDEUR', 'USDGBP', 'USDAUD', 'USDSGD'); // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'entity_name', 'label' => 'Entity Name', 'type' => 'text', 'mandatory' => true),
        array('name' => 'username', 'label' => 'Username', 'type' => 'text', 'mandatory' => true),
        array('name' => 'password', 'label' => 'Password', 'type' => 'text', 'mandatory' => true),
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_url', 'label' => 'Test URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_BANK_CURRENCY', 'type' => 'text', 'block' => self::TRANSFER_DETAILS_BLOCK, 'mandatory' => true),
        array('name' => 'currency_conversion', 'label' => 'Currency Conversion Tool', 'type' => 'picklist','picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'sftp_host', 'label' => 'SFTP Host', 'type' => 'text', 'mandatory' => true),
        array('name' => 'sftp_port', 'label' => 'SFTP Port', 'type' => 'number', 'mandatory' => true),
        array('name' => 'sftp_username', 'label' => 'SFTP Username', 'type' => 'text', 'mandatory' => true),
        array('name' => 'sftp_password', 'label' => 'SFTP Password', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'card_number', 'label' => 'CAB_LBL_CARD_NUMBER', 'type' => 'number', 'required' => true, 'mandatory' => true, 'placeholder' => '4444333322221111'),
        array('name' => 'card_expiry_date', 'label' => 'CAB_LBL_CARD_EXPIRY', 'type' => 'text', 'required' => true, 'mandatory' => true, 'placeholder' => '05/2035'),
        array('name' => 'card_cvc', 'label' => 'CAB_LBL_CARD_CVC', 'type' => 'number', 'required' => true, 'mandatory' => true, 'placeholder' => 'Card CVC'),
        array('name' => 'conversion_rate', 'label' => 'CAB_LBL_CONVERSION_RATE', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
        array('name' => 'bank_amount', 'label' => 'CAB_LBL_BANK_AMOUNT', 'type' => 'hidden', 'required' => false, 'display' => true, 'mandatory' => true),
        array('name' => 'bank_currency', 'label' => 'CAB_LBL_CURRENCY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
            array("isAllow" => "true", "value" => "USD", "label" => "USD"),
            array("isAllow" => "true", "value" => "EUR", "label" => "EUR"),
            array("isAllow" => "true", "value" => "GBP", "label" => "GBP"),
            array("isAllow" => "true", "value" => "AUD", "label" => "AUD"),
            array("isAllow" => "true", "value" => "SGD", "label" => "SGD"),
        )),
//        array('name' => 'address', 'label' => 'CAB_LBL_ADDRESS', 'type' => 'text'),
//        array('name' => 'postal_code', 'label' => 'CAB_LBL_POSTAL_CODE', 'type' => 'text'),
//        array('name' => 'city', 'label' => 'CAB_LBL_CITY', 'type' => 'text'),
//        array('name' => 'country_code', 'label' => 'CAB_LBL_COUNTRY', 'type' => 'text'),
//        array('name' => 'country_code', 'label' => 'CAB_LBL_COUNTRY', 'type' => 'dropdown_depended', 'picklist' => array(
//            array("isAllow" => "true", "label" => "Select An Option", "value" => ""),
//            array("isAllow" => "true", "value" => "AX", "label" => "ALAND ISLANDS"),array("isAllow" => "true", "value" => "AL", "label" => "ALBANIA"),array("isAllow" => "true", "value" => "DZ", "label" => "ALGERIA"),array("isAllow" => "true", "value" => "AS", "label" => "AMERICAN SAMOA"),array("isAllow" => "true", "value" => "AD", "label" => "ANDORRA"),array("isAllow" => "true", "value" => "AO", "label" => "ANGOLA"),array("isAllow" => "true", "value" => "AI", "label" => "ANGUILLA"),array("isAllow" => "true", "value" => "AQ", "label" => "ANTARCTICA"),array("isAllow" => "true", "value" => "AG", "label" => "ANTIGUA AND BARBUDA"),array("isAllow" => "true", "value" => "AR", "label" => "ARGENTINA"),array("isAllow" => "true", "value" => "AM", "label" => "ARMENIA"),array("isAllow" => "true", "value" => "AW", "label" => "ARUBA"),array("isAllow" => "true", "value" => "AU", "label" => "AUSTRALIA"),array("isAllow" => "true", "value" => "AT", "label" => "AUSTRIA"),array("isAllow" => "true", "value" => "AZ", "label" => "AZERBAIJAN"),array("isAllow" => "true", "value" => "BS", "label" => "BAHAMAS"),array("isAllow" => "true", "value" => "BH", "label" => "BAHRAIN"),array("isAllow" => "true", "value" => "BD", "label" => "BANGLADESH"),array("isAllow" => "true", "value" => "BB", "label" => "BARBADOS"),array("isAllow" => "true", "value" => "BY", "label" => "BELARUS"),array("isAllow" => "true", "value" => "BE", "label" => "BELGIUM"),array("isAllow" => "true", "value" => "BZ", "label" => "BELIZE"),array("isAllow" => "true", "value" => "BJ", "label" => "BENIN"),array("isAllow" => "true", "value" => "BM", "label" => "BERMUDA"),array("isAllow" => "true", "value" => "BT", "label" => "BHUTAN"),array("isAllow" => "true", "value" => "BO", "label" => "BOLIVIA"),array("isAllow" => "true", "value" => "BQ", "label" => "BONAIRE, SINT EUSTATIUS AND SABA"),array("isAllow" => "true", "value" => "BA", "label" => "BOSNIA AND HERZEGOVINA"),array("isAllow" => "true", "value" => "BW", "label" => "BOTSWANA"),array("isAllow" => "true", "value" => "BV", "label" => "BOUVET ISLAND"),array("isAllow" => "true", "value" => "BR", "label" => "BRAZIL"),array("isAllow" => "true", "value" => "IO", "label" => "BRIT. IND. OCEAN TERRIT."),array("isAllow" => "true", "value" => "BN", "label" => "BRUNEI DARUSSALAM"),array("isAllow" => "true", "value" => "BG", "label" => "BULGARIA"),array("isAllow" => "true", "value" => "BF", "label" => "BURKINA FASO"),array("isAllow" => "true", "value" => "BI", "label" => "BURUNDI"),array("isAllow" => "true", "value" => "KH", "label" => "CAMBODIA"),array("isAllow" => "true", "value" => "CM", "label" => "CAMEROON"),array("isAllow" => "true", "value" => "CA", "label" => "CANADA"),array("isAllow" => "true", "value" => "CV", "label" => "CAPE VERDE"),array("isAllow" => "true", "value" => "KY", "label" => "CAYMAN ISLANDS"),array("isAllow" => "true", "value" => "CF", "label" => "CENTRAL AFRICAN REPUBLIC"),array("isAllow" => "true", "value" => "TD", "label" => "CHAD"),array("isAllow" => "true", "value" => "CL", "label" => "CHILE"),array("isAllow" => "true", "value" => "CN", "label" => "CHINA"),array("isAllow" => "true", "value" => "CX", "label" => "CHRISTMAS ISLAND"),array("isAllow" => "true", "value" => "CC", "label" => "COCOS (KEELING) ISLANDS"),array("isAllow" => "true", "value" => "CO", "label" => "COLOMBIA"),array("isAllow" => "true", "value" => "KM", "label" => "COMOROS"),array("isAllow" => "true", "value" => "CG", "label" => "CONGO"),array("isAllow" => "true", "value" => "CD", "label" => "CONGO, THE DEMOCRATIC REPUBLIC OF THE"),array("isAllow" => "true", "value" => "CK", "label" => "COOK ISLANDS"),array("isAllow" => "true", "value" => "CR", "label" => "COSTA RICA"),array("isAllow" => "true", "value" => "CI", "label" => "COTE D'IVOIRE"),array("isAllow" => "true", "value" => "HR", "label" => "CROATIA"),array("isAllow" => "true", "value" => "CU", "label" => "CUBA"),array("isAllow" => "true", "value" => "CW", "label" => "CURACAO"),array("isAllow" => "true", "value" => "CY", "label" => "CYPRUS"),array("isAllow" => "true", "value" => "CZ", "label" => "CZECH REPUBLIC"),array("isAllow" => "true", "value" => "DK", "label" => "DENMARK"),array("isAllow" => "true", "value" => "DJ", "label" => "DJIBOUTI"),array("isAllow" => "true", "value" => "DM", "label" => "DOMINICA"),array("isAllow" => "true", "value" => "DO", "label" => "DOMINICAN REPUBLIC"),array("isAllow" => "true", "value" => "TP", "label" => "EAST TIMOR"),array("isAllow" => "true", "value" => "EC", "label" => "ECUADOR"),array("isAllow" => "true", "value" => "EG", "label" => "EGYPT"),array("isAllow" => "true", "value" => "SV", "label" => "EL SALVADOR"),array("isAllow" => "true", "value" => "GQ", "label" => "EQUATORIAL GUINEA"),array("isAllow" => "true", "value" => "ER", "label" => "ERITREA"),array("isAllow" => "true", "value" => "EE", "label" => "ESTONIA"),array("isAllow" => "true", "value" => "ET", "label" => "ETHIOPIA"),array("isAllow" => "true", "value" => "FK", "label" => "FALKLAND ISLANDS"),array("isAllow" => "true", "value" => "FO", "label" => "FAROE ISLANDS"),array("isAllow" => "true", "value" => "FJ", "label" => "FIJI"),array("isAllow" => "true", "value" => "FI", "label" => "FINLAND"),array("isAllow" => "true", "value" => "FR", "label" => "FRANCE"),array("isAllow" => "true", "value" => "FX", "label" => "FRANCE, METROPOLITAN"),array("isAllow" => "true", "value" => "GF", "label" => "FRENCH GUIANA"),array("isAllow" => "true", "value" => "PF", "label" => "FRENCH POLYNESIA"),array("isAllow" => "true", "value" => "TF", "label" => "FRENCH SOUTHERN TERRIT."),array("isAllow" => "true", "value" => "GA", "label" => "GABON"),array("isAllow" => "true", "value" => "GM", "label" => "GAMBIA"),array("isAllow" => "true", "value" => "GE", "label" => "GEORGIA"),array("isAllow" => "true", "value" => "DE", "label" => "GERMANY"),array("isAllow" => "true", "value" => "GH", "label" => "GHANA"),array("isAllow" => "true", "value" => "GI", "label" => "GIBRALTAR"),array("isAllow" => "true", "value" => "GR", "label" => "GREECE"),array("isAllow" => "true", "value" => "GL", "label" => "GREENLAND"),array("isAllow" => "true", "value" => "GD", "label" => "GRENADA"),array("isAllow" => "true", "value" => "GP", "label" => "GUADELOUPE"),array("isAllow" => "true", "value" => "GU", "label" => "GUAM"),array("isAllow" => "true", "value" => "GT", "label" => "GUATEMALA"),array("isAllow" => "true", "value" => "GG", "label" => "GUERNSEY"),array("isAllow" => "true", "value" => "GN", "label" => "GUINEA"),array("isAllow" => "true", "value" => "GW", "label" => "GUINEA-BISSAU"),array("isAllow" => "true", "value" => "GY", "label" => "GUYANA"),array("isAllow" => "true", "value" => "HT", "label" => "HAITI"),array("isAllow" => "true", "value" => "HM", "label" => "HEARD & MC DONALD ISLS"),array("isAllow" => "true", "value" => "HN", "label" => "HONDURAS"),array("isAllow" => "true", "value" => "HK", "label" => "HONG KONG"),array("isAllow" => "true", "value" => "HU", "label" => "HUNGARY"),array("isAllow" => "true", "value" => "IS", "label" => "ICELAND"),array("isAllow" => "true", "value" => "IN", "label" => "INDIA"),array("isAllow" => "true", "value" => "ID", "label" => "INDONESIA"),array("isAllow" => "true", "value" => "IR", "label" => "IRAN"),array("isAllow" => "true", "value" => "IQ", "label" => "IRAQ"),array("isAllow" => "true", "value" => "IE", "label" => "IRELAND"),array("isAllow" => "true", "value" => "IM", "label" => "ISLE OF MAN"),array("isAllow" => "true", "value" => "IL", "label" => "ISRAEL"),array("isAllow" => "true", "value" => "IT", "label" => "ITALY"),array("isAllow" => "true", "value" => "JM", "label" => "JAMAICA"),array("isAllow" => "true", "value" => "JP", "label" => "JAPAN"),array("isAllow" => "true", "value" => "JE", "label" => "JERSEY"),array("isAllow" => "true", "value" => "JO", "label" => "JORDAN"),array("isAllow" => "true", "value" => "KZ", "label" => "KAZAKHSTAN"),array("isAllow" => "true", "value" => "KE", "label" => "KENYA"),array("isAllow" => "true", "value" => "KI", "label" => "KIRIBATI"),array("isAllow" => "true", "value" => "KS", "label" => "KOSOVO"),array("isAllow" => "true", "value" => "KW", "label" => "KUWAIT"),array("isAllow" => "true", "value" => "KG", "label" => "KYRGYZSTAN"),array("isAllow" => "true", "value" => "LA", "label" => "LAO PEOPLE'S REP."),array("isAllow" => "true", "value" => "LV", "label" => "LATVIA"),array("isAllow" => "true", "value" => "LB", "label" => "LEBANON"),array("isAllow" => "true", "value" => "LS", "label" => "LESOTHO"),array("isAllow" => "true", "value" => "LR", "label" => "LIBERIA"),array("isAllow" => "true", "value" => "LY", "label" => "LIBYAN ARAB JAMAHIRIYA"),array("isAllow" => "true", "value" => "LI", "label" => "LIECHTENSTEIN"),array("isAllow" => "true", "value" => "LT", "label" => "LITHUANIA"),array("isAllow" => "true", "value" => "LU", "label" => "LUXEMBOURG"),array("isAllow" => "true", "value" => "MO", "label" => "MACAU"),array("isAllow" => "true", "value" => "MK", "label" => "MACEDONIA"),array("isAllow" => "true", "value" => "MG", "label" => "MADAGASCAR"),array("isAllow" => "true", "value" => "MW", "label" => "MALAWI"),array("isAllow" => "true", "value" => "MY", "label" => "MALAYSIA"),array("isAllow" => "true", "value" => "MV", "label" => "MALDIVES"),array("isAllow" => "true", "value" => "ML", "label" => "MALI"),array("isAllow" => "true", "value" => "MT", "label" => "MALTA"),array("isAllow" => "true", "value" => "MH", "label" => "MARSHALL ISLANDS"),array("isAllow" => "true", "value" => "MQ", "label" => "MARTINIQUE"),array("isAllow" => "true", "value" => "MR", "label" => "MAURITANIA"),array("isAllow" => "true", "value" => "MU", "label" => "MAURITIUS"),array("isAllow" => "true", "value" => "YT", "label" => "MAYOTTE"),array("isAllow" => "true", "value" => "MX", "label" => "MEXICO"),array("isAllow" => "true", "value" => "FM", "label" => "MICRONESIA (FED. STATES)"),array("isAllow" => "true", "value" => "MD", "label" => "MOLDOVA, REPUBLIC OF"),array("isAllow" => "true", "value" => "MC", "label" => "MONACO"),array("isAllow" => "true", "value" => "MN", "label" => "MONGOLIA"),array("isAllow" => "true", "value" => "MS", "label" => "MONTSERRAT"),array("isAllow" => "true", "value" => "MA", "label" => "MOROCCO"),array("isAllow" => "true", "value" => "MZ", "label" => "MOZAMBIQUE"),array("isAllow" => "true", "value" => "MM", "label" => "MYANMAR"),array("isAllow" => "true", "value" => "NA", "label" => "NAMIBIA"),array("isAllow" => "true", "value" => "NR", "label" => "NAURU"),array("isAllow" => "true", "value" => "NP", "label" => "NEPAL"),array("isAllow" => "true", "value" => "NL", "label" => "NETHERLANDS"),array("isAllow" => "true", "value" => "NC", "label" => "NEW CALEDONIA"),array("isAllow" => "true", "value" => "NZ", "label" => "NEW ZEALAND"),array("isAllow" => "true", "value" => "NI", "label" => "NICARAGUA"),array("isAllow" => "true", "value" => "NE", "label" => "NIGER"),array("isAllow" => "true", "value" => "NG", "label" => "NIGERIA"),array("isAllow" => "true", "value" => "NU", "label" => "NIUE"),array("isAllow" => "true", "value" => "NF", "label" => "NORFOLK ISLAND"),array("isAllow" => "true", "value" => "MP", "label" => "NORTHERN MARIANA ISLANDS"),array("isAllow" => "true", "value" => "NO", "label" => "NORWAY"),array("isAllow" => "true", "value" => "OM", "label" => "OMAN"),array("isAllow" => "true", "value" => "PK", "label" => "PAKISTAN"),array("isAllow" => "true", "value" => "PW", "label" => "PALAU"),array("isAllow" => "true", "value" => "PS", "label" => "PALESTINIAN TERRITORY, OCCUPIED"),array("isAllow" => "true", "value" => "PA", "label" => "PANAMA"),array("isAllow" => "true", "value" => "PG", "label" => "PAPUA NEW GUINEA"),array("isAllow" => "true", "value" => "PY", "label" => "PARAGUAY"),array("isAllow" => "true", "value" => "KP", "label" => "PEOPLE'S REP. KOREA"),array("isAllow" => "true", "value" => "PE", "label" => "PERU"),array("isAllow" => "true", "value" => "PH", "label" => "PHILIPPINES"),array("isAllow" => "true", "value" => "PN", "label" => "PITCAIRN"),array("isAllow" => "true", "value" => "PL", "label" => "POLAND"),array("isAllow" => "true", "value" => "PT", "label" => "PORTUGAL"),array("isAllow" => "true", "value" => "PR", "label" => "PUERTO RICO"),array("isAllow" => "true", "value" => "QA", "label" => "QATAR"),array("isAllow" => "true", "value" => "KR", "label" => "REPUBLIC OF KOREA"),array("isAllow" => "true", "value" => "ME", "label" => "REPUBLIC OF MONTENEGRO"),array("isAllow" => "true", "value" => "RS", "label" => "REPUBLIC OF SERBIA"),array("isAllow" => "true", "value" => "RE", "label" => "REUNION"),array("isAllow" => "true", "value" => "RO", "label" => "ROMANIA"),array("isAllow" => "true", "value" => "RU", "label" => "RUSSIAN FEDERATION"),array("isAllow" => "true", "value" => "RW", "label" => "RWANDA"),array("isAllow" => "true", "value" => "GS", "label" => "S. GEORGIA & S. SANDWICH"),array("isAllow" => "true", "value" => "BL", "label" => "SAINT BARTHELEMY"),array("isAllow" => "true", "value" => "KN", "label" => "SAINT KITTS AND NEVIS"),array("isAllow" => "true", "value" => "LC", "label" => "SAINT LUCIA"),array("isAllow" => "true", "value" => "MF", "label" => "SAINT MARTIN (FRENCH)"),array("isAllow" => "true", "value" => "WS", "label" => "SAMOA"),array("isAllow" => "true", "value" => "SM", "label" => "SAN MARINO"),array("isAllow" => "true", "value" => "ST", "label" => "SAO TOME AND PRINCIPE"),array("isAllow" => "true", "value" => "SA", "label" => "SAUDI ARABIA"),array("isAllow" => "true", "value" => "SN", "label" => "SENEGAL"),array("isAllow" => "true", "value" => "CS", "label" => "SERBIA AND MONTENEGRO"),array("isAllow" => "true", "value" => "SC", "label" => "SEYCHELLES"),array("isAllow" => "true", "value" => "SL", "label" => "SIERRA LEONE"),array("isAllow" => "true", "value" => "SG", "label" => "SINGAPORE"),array("isAllow" => "true", "value" => "SX", "label" => "SINT MAARTEN (DUTCH)"),array("isAllow" => "true", "value" => "SK", "label" => "SLOVAKIA"),array("isAllow" => "true", "value" => "SI", "label" => "SLOVENIA"),array("isAllow" => "true", "value" => "SB", "label" => "SOLOMON ISLANDS"),array("isAllow" => "true", "value" => "SO", "label" => "SOMALIA"),array("isAllow" => "true", "value" => "ZA", "label" => "SOUTH AFRICA"),array("isAllow" => "true", "value" => "ES", "label" => "SPAIN"),array("isAllow" => "true", "value" => "LK", "label" => "SRI LANKA"),array("isAllow" => "true", "value" => "SH", "label" => "ST. HELENA"),array("isAllow" => "true", "value" => "PM", "label" => "ST. PIERRE AND MIQUELON"),array("isAllow" => "true", "value" => "VC", "label" => "ST. VINCENT & GRENADINES"),array("isAllow" => "true", "value" => "SD", "label" => "SUDAN"),array("isAllow" => "true", "value" => "SR", "label" => "SURINAME"),array("isAllow" => "true", "value" => "SJ", "label" => "SVALBARD AND JAN MAYEN"),array("isAllow" => "true", "value" => "SZ", "label" => "SWAZILAND"),array("isAllow" => "true", "value" => "SE", "label" => "SWEDEN"),array("isAllow" => "true", "value" => "CH", "label" => "SWITZERLAND"),array("isAllow" => "true", "value" => "SY", "label" => "SYRIAN ARAB REPUBLIC"),array("isAllow" => "true", "value" => "TW", "label" => "TAIWAN"),array("isAllow" => "true", "value" => "TJ", "label" => "TAJIKISTAN"),array("isAllow" => "true", "value" => "TZ", "label" => "TANZANIA"),array("isAllow" => "true", "value" => "TH", "label" => "THAILAND"),array("isAllow" => "true", "value" => "TL", "label" => "TIMOR-LESTE"),array("isAllow" => "true", "value" => "TG", "label" => "TOGO"),array("isAllow" => "true", "value" => "TK", "label" => "TOKELAU"),array("isAllow" => "true", "value" => "TO", "label" => "TONGA"),array("isAllow" => "true", "value" => "TT", "label" => "TRINIDAD AND TOBAGO"),array("isAllow" => "true", "value" => "TN", "label" => "TUNISIA"),array("isAllow" => "true", "value" => "TR", "label" => "TURKEY"),array("isAllow" => "true", "value" => "TM", "label" => "TURKMENISTAN"),array("isAllow" => "true", "value" => "TC", "label" => "TURKS AND CAICOS ISLANDS"),array("isAllow" => "true", "value" => "TV", "label" => "TUVALU"),array("isAllow" => "true", "value" => "UG", "label" => "UGANDA"),array("isAllow" => "true", "value" => "UA", "label" => "UKRAINE"),array("isAllow" => "true", "value" => "AE", "label" => "UNITED ARAB EMIRATES"),array("isAllow" => "true", "value" => "GB", "label" => "UNITED KINGDOM"),array("isAllow" => "true", "value" => "US", "label" => "UNITED STATES"),array("isAllow" => "true", "value" => "UY", "label" => "URUGUAY"),array("isAllow" => "true", "value" => "UM", "label" => "US MINOR OUTL. ISLANDS"),array("isAllow" => "true", "value" => "UZ", "label" => "UZBEKISTAN"),array("isAllow" => "true", "value" => "VU", "label" => "VANUATU"),array("isAllow" => "true", "value" => "VA", "label" => "VATICAN CITY STATE"),array("isAllow" => "true", "value" => "VE", "label" => "VENEZUELA"),array("isAllow" => "true", "value" => "VN", "label" => "VIET NAM"),array("isAllow" => "true", "value" => "VG", "label" => "VIRGIN ISLANDS (BRITISH)"),array("isAllow" => "true", "value" => "VI", "label" => "VIRGIN ISLANDS (U.S.)"),array("isAllow" => "true", "value" => "WF", "label" => "WALLIS & FUTUNA ISLANDS"),array("isAllow" => "true", "value" => "EH", "label" => "WESTERN SAHARA"),array("isAllow" => "true", "value" => "YE", "label" => "YEMEN"),array("isAllow" => "true", "value" => "YU", "label" => "YUGOSLAVIA"),array("isAllow" => "true", "value" => "ZR", "label" => "ZAIRE"),array("isAllow" => "true", "value" => "ZM", "label" => "ZAMBIA"),array("isAllow" => "true", "value" => "ZW", "label" => "ZIMBABWE")
//        ))
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
//        array('name' => 'email', 'label' => 'CAB_LBL_EMAIL', 'type' => 'email', 'required' => true, 'mandatory' => true)
    );

    private static $WORLDPAY_IP_ADDRESS = array('151.101.2.47', '151.101.66.47', '151.101.130.47', '151.101.194.47');
    
    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'WorldPay'; // don't take name with any space or special charctor
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return array_merge(self::$REQUIRED_PARAMETERS, self::DEFAULT_REQUIRED_PARAMETERS);
        //return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get deposit parameters
     * @return <array> required parameters list
     */
    public function getDepositFormParams() {
        return self::$DEPOSIT_FORM_PARAMETERS;
    }

    /**
     * Function to get withdrawal parameters
     * @return <array> required parameters list
     */
    public function getWithdrawFormParams() {
        return self::$WITHDRAW_FORM_PARAMETERS;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false) {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    public function prepareParameters() {
        foreach (self::$REQUIRED_PARAMETERS as $key => $fieldInfo) {
            if($fieldInfo['name'] == 'amount')
            {
                continue;
            }
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }
        return $params;
    }

    public function paymentProcess($request, $portal_language) {
        global $PORTAL_URL, $site_URL,$log;$log->debug('Entering into paymentProcess');

        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }

        if (!empty($request)) {
            //Get response
            $returnUrl = $PORTAL_URL . "payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";            
            $cancelUrl = $PORTAL_URL . "payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            //For call back handling which hook form third part
            $callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;
            
            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amountValue = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');
                $log->debug('$amountValue=');
                $log->debug($amountValue);
                $entityName = $provider->parameters['entity_name'];
                $username = $provider->parameters['username'];
                $password = $provider->parameters['password'];
                $test_url = $provider->parameters['test_url'];
                $base_url = $provider->parameters['base_url'];
                $bankCurrency = $request['bank_currency'];$log->debug('$bankCurrency='.$bankCurrency);
                $conversionRate = $request['conversion_rate'];$log->debug('$conversionRate='.$conversionRate);
                
                if (strtolower($bankCurrency) != 'usd' && !empty($conversionRate))
                {
                    $amountValue = ($conversionRate * $amountValue)*100;
                }$log->debug('after conversion='.$amountValue);
                
                if($bankCurrency == '')
                {
                    $bankCurrency = 'USD';
                    $amountValue = $amountValue * 100;
                }
                if ($provider->parameters['test_mode'] == 'Yes') {
                    $worldPayActionUrl = $test_url;
                } else {
                    $worldPayActionUrl = $base_url;
                }
                $log->debug('$request==');
                $log->debug($request);
                /*Prepare parameters*/
                $baseEncodedKey = base64_encode($username.":".$password);$log->debug('$baseEncodedKey=');$log->debug($baseEncodedKey);
                list($expiryMonth, $expiryYear) = explode('/',$request['card_expiry_date']);$log->debug($expiryMonth.'-'.$expiryYear);
                $authParams = array(
                    'entity_name' => $entityName,
                    'card_number' => $request['card_number'],
                    'cvc' => $request['card_cvc'],
                    'expiry_month' => $expiryMonth,
                    'expiry_year' => $expiryYear,
//                    'address' => $request['address'],
//                    'postal_code' => $request['postal_code'],
//                    'city' => $request['city'],
//                    'country_code' => $request['country_code'],
                    'amount' => (int) $amountValue,
                    'order_id' => $order_id,
                    'currency' => $bankCurrency,
                );
                /*Prepare parameters*/
                
                /*Get Dynamic Urls*/
//                $authenticatedData = $this->get3dsAuthentication($authParams, $baseEncodedKey, $worldPayActionUrl);$log->debug('$authenticatedData=');$log->debug($authenticatedData);
                $urlData = $this->accessUrls($worldPayActionUrl, $baseEncodedKey);$log->debug('$urlData');$log->debug($urlData);
                if(!empty($urlData['_links']['payments:authorize']))
                {
                    $authParams['authorize_link'] = $urlData['_links']['payments:authorize'];
                }
                else
                {
                    $res = array('success' => false, 'message' => $urlData['message']);
                    return $res;
                }
                /*Get Dynamic Urls*/
                
                /*Do Authorization*/
                $secureJsonDataArr = $this->getAuthorization($authParams, $baseEncodedKey, $worldPayActionUrl);$log->debug('$secureJsonDataArr=');$log->debug($secureJsonDataArr);
                if(isset($secureJsonDataArr['errorName']) && !empty($secureJsonDataArr['errorName']))
                {
                    $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_REQUEST', $this->module));
                    return $res;
                }
                $output = isset($secureJsonDataArr['outcome']) && !empty($secureJsonDataArr['outcome']) ? $secureJsonDataArr['outcome'] : '';$log->debug('$output=');$log->debug($output);
                $request['authorization_output'] = $secureJsonDataArr;
                if (strtolower($output) == "authorized") {
                    $settlePaymentLink = $redirectUrl = $secureJsonDataArr['_links']['payments:settle']['href'];
                    /*Settle Payment*/
                    $settledstatus = 'Failed';
                    $settledResponse = $this->settleTransaction($settlePaymentLink, $baseEncodedKey);
                    if(isset($settledResponse['_links']['payments:events']) && !empty($settledResponse['_links']['payments:events']))
                    {
                        $settledstatus = 'Success';
                    }
                    else
                    {
                        $res = array('success' => false, 'message' => $settledResponse['message']);
                        return $res;
                    }
                    
                    if (PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Created', 'Authorization')) {
                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'output' => $settledResponse, 'order_id' => $order_id));
                    } else {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
                    }
                } else if (strpos(strtolower($output), "refused") !== false) {
                    $res = array('success' => false, 'message' => vtranslate($secureJsonDataArr['description'], $this->module));
                    return $res;
                } else {
                    $cancelPaymentLink = $secureJsonDataArr['_links']['payments:cancel']['href'];
                    $cancelledResponse = $this->settleTransaction($cancelPaymentLink, $baseEncodedKey);
                    $request['error_message'] = $secureJsonDataArr['message'];
                    if (!PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $request['payment_from'], $request, 'Failed', 'Authorization')) {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module));
                    } else {
                        $res = array('success' => false, 'message' => $request['error_message']);
                    }
                }
            } else if ($request['payment_operation'] == 'Withdrawal') {
                $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Manual', 'redirect_url' => $returnUrl, 'order_id' => $order_id, 'message' => 'Withdrawal request has been sent successfully'));
            } else {
                $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->module, $portal_language));
            }
        } else {
            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_REQUEST', $this->module, $portal_language));
        }
        return $res;
    }

    public function accessUrls($worldPayActionUrl, $baseEncodedKey)
    {
        global $log;
        $log->debug('Entering into accessUrls');
        $headers = [
            "Authorization: Basic $baseEncodedKey"
        ];
        $urlData = $this->sendCurlRequest($worldPayActionUrl.'payments', 'GET', '', $headers, array());$log->debug('$urlData=');$log->debug($urlData);
        if(!is_array($urlData))
        {
            $response = json_decode($urlData, true);
            return $response;
        }
        return $urlData;
    }
    
    public function getAuthorization($params = array(), $baseEncodedKey, $worldPayActionUrl)
    {
        global $log;
        $worldPayFormParam = array(
                "transactionReference" => $params['order_id'],
                "merchant" => array(
                    "entity" => $params['entity_name']
                ),
                "instruction" => array(
                    "narrative" => array(
                        "line1" => "trading name"
                    ),
                    "value" => array(
                        "currency" => $params['currency'],
                        "amount" => $params['amount']
                    ),
                    "paymentInstrument" => array(
                        "type" => "card/plain",
                        "cardNumber" => $params['card_number'],
                        "cvc" => $params['cvc'],
                        "cardExpiryDate" => array(
                            "month" => (int) $params['expiry_month'],
                            "year" => (int) $params['expiry_year']
                        )
                    ),
                )
//                ,"customer" => array(
//                    "authentication" => array(
//                        "version" => $params['version'],
//                        "type" => "3DS",
//                        "eci" => $params['eci'],
//                        "authenticationValue" => $params['authenticationValue'],
//                        "transactionId" => $params['transactionId'],
//                )
//                )
            );

        $headers = [
            "Authorization: Basic $baseEncodedKey",
            "content-type: application/vnd.worldpay.payments-v6+json",
            "Accept: application/vnd.worldpay.payments-v6.hal+json",
        ];

        $secureJsonData = $this->sendCurlRequest($worldPayActionUrl.'payments/authorizations', 'POST', '', $headers, json_encode($worldPayFormParam));$log->debug('$secureJsonData=');$log->debug($secureJsonData);
        if(!is_array($secureJsonData))
        {
            $response = json_decode($secureJsonData, true);
            return $response;
        }
        return $secureJsonData;
    }
    
    public function settleTransaction($actionUrl, $baseEncodedKey)
    {
        global $log;
        $headers = [
            "Authorization: Basic $baseEncodedKey",
            "content-type: application/vnd.worldpay.payments-v6+json"
        ];

        $settledResponse = $this->sendCurlRequest($actionUrl, 'POST', '', $headers, array());$log->debug('$settledResponse=');$log->debug($settledResponse);
        if(!is_array($settledResponse))
        {
            $response = json_decode($settledResponse, true);
            return $response;
        }
        return $settledResponse;
    }
    
    public function get3dsAuthentication($params = array(), $baseEncodedKey, $worldPayActionUrl)
    {
        global $log;
        $threedsParam = array(
            "transactionReference" => $params['order_id'],
            "merchant" => array(
                "entity" => $params['entity_name']
            ),
            "instruction" => array(
                "paymentInstrument" => array(
                    "type" => "card/front",
                    "cardNumber" => $params['card_number'],
                    "cardExpiryDate" => array(
                        "month" => $params['expiry_month'],
                        "year" => $params['expiry_year']
                    ),
                ),
                "billingAddress" => array(
                    "address1" => $params['address'],
                    "postalCode" => $params['postal_code'],
                    "city" => $params['city'],
                    "countryCode" => $params['country_code']
                ),
                "value" => array(
                    "currency" => "USD",
                    "amount" => $params['amount']
                ),
            ),
            "deviceData" => array(
                "acceptHeader" => $_SERVER['HTTP_ACCEPT'],
                "userAgentHeader" => $_SERVER['HTTP_USER_AGENT'],
            )

        );

        $headers = [
            "Authorization: Basic $baseEncodedKey",
            "content-type: application/vnd.worldpay.payments-v6+json",
            "Accept: application/vnd.worldpay.payments-v6.hal+json",
        ];

        $authenticatedData = $this->sendCurlRequest($worldPayActionUrl.'verifications/customers/3ds/authentication', 'POST', '', $headers, json_encode($threedsParam));$log->debug('$authenticatedData=');$log->debug($authenticatedData);
        $authenticatedData = '{
            "outcome": "authenticated",
            "transactionReference": "Memory265-13/08/1876",
            "authentication": {
                "version": "2.1.0",
                "authenticationValue": "MAAAAAAAAAAAAAAAAAAAAAAAAAA=",
                "eci": "05",
                "transactionId": "c5b808e7-1de1-4069-a17b-f70d3b3b1645"
            }
        }';
        if(!is_array($authenticatedData))
        {
            $response = json_decode($authenticatedData, true);
            return $response;
        }
        return $authenticatedData;
    }
    
    public function getVerifyTransferDetails($request, $FILES, $portal_language) {
        $paymentInput = array();
        if ($request['payment_operation'] == 'Deposit') {
            $paymentInput = self::getDepositFormParams();
        } else if ($request['payment_operation'] == 'Withdrawal') {
            $paymentInput = self::getWithdrawFormParams();
        } else {
            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->module, $portal_language));
        }
        $res = array('success' => true);
        if (!empty($paymentInput)) {
            foreach ($paymentInput as $field) {
                $name = $field['name'];
                if ($field['required'] && !array_key_exists($name, $request) && $field['type'] != "file") {
                    $res = array('success' => false, 'message' => $field['name'] . vtranslate('CAB_MSG_FIELD_IS_REQUIRED', $this->module, $portal_language));
                    break;
                }

                if ($field['required'] && (!array_key_exists($name, $request) && !array_key_exists($name, $FILES)) && $field['type'] == "file") {
                    $res = array('success' => false, 'message' => $field['name'] . vtranslate('CAB_MSG_FIELD_IS_REQUIRED', $this->module, $portal_language));
                    break;
                }
                if ($field['type'] == 'file' && array_key_exists($name, $FILES)) {
                    $file_type = explode(',', $field['allowed_type']); // array('JPEG', 'JPG', 'PNG', 'PDF');                                        
                    if (!in_array(strtoupper(pathinfo($FILES[$name]['name'])['extension']), $file_type)) {
                        $res = array('success' => false, 'message' => pathinfo($FILES[$name]['name'])['extension'] . vtranslate('CAB_MSG_FILE_TYPE_DOES_NOT_ALLOWED', $this->module, $portal_language));
                        break;
                    } else if ($FILES[$name]['size'] <= 0 || $FILES[$name]['size'] > 5000000) {
                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_FILE_SIZE_SHOULD_NOT_BE_GREATER_THAN_MB', $this->translate_module, $portal_language));
                        break;
                    }
                }
            }
        }
//        if (!preg_match('/^\d+$/', $request['amount'])) {
//            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_AMOUNT_MUST_BE_INTEGER', $this->translate_module, $portal_language));
//            return $res;
//        }
            return $res;
        }

//Verify the payment response and insert to payment log table
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language) {
        global $log,$adb;
        $log->debug('Entering into paymentResponseVerification...');
        $log->debug($status);
        $log->debug($payment_response);
        $log->debug($order_id);
        $log->debug($portal_language);
        $paymentStatus = false;
        $logStatus = "Failed";
        $logResponse = array("request" => $payment_response, "response" => array("log_message" => ""));
        $errorMsg = "";
        
        if (PaymentProvidersHelper::getPaymentRecord($order_id))
        {
            $getPaymentCallbackRecord = "SELECT data FROM vtiger_payment_logs WHERE order_id = ? AND provider_type = ? AND status = ? AND event = ?";
            $resultGetPaymentCallback = $adb->pquery($getPaymentCallbackRecord, array($order_id, $payment_response['pm'], 'Created', 'Authorization'));
            $callbackJsonData = $adb->query_result($resultGetPaymentCallback,0,'data');$log->debug($callbackJsonData);
            $callbackJsonData = html_entity_decode($callbackJsonData);
            $callbackData = json_decode($callbackJsonData, true);$log->debug($callbackData);
            $statusCheckUrl = $callbackData['authorization_output']['_links']['payments:events']['href'];$log->debug($statusCheckUrl);
            if(!empty($statusCheckUrl))
            {
                $baseEncodedKey = "MjAweG1oYzVkNzB5eGlhYzplemtvaGVtcjJ3aGNxbTc0OWJ1dDg5b2l1ejRqbGljcWR0dnBpamNleHcxZGd4c2RjNGczdnh5c3gzcDBwMWto";
                $headers = [
                    "Authorization: Basic $baseEncodedKey",
                    "content-type: application/vnd.worldpay.payments-v6+json",
                ];
//                $paymentStatus = true;
                $paymentStatusJsonData = $this->sendCurlRequest($statusCheckUrl, 'GET', '', $headers, '');$log->debug('$paymentStatusJsonData=');$log->debug($paymentStatusJsonData);
                if(is_array($paymentStatusJsonData))
                {
                    $res = array('success' => false, 'payment_status' => 'Failed', 'message' => $paymentStatusJsonData['message'] );
                    return $res;
                }
                $paymentStatusData = json_decode($paymentStatusJsonData, true);
                $paymentThirdpartyStatus = $paymentStatusData['lastEvent'];$log->debug('$paymentThirdpartyStatus=');$log->debug($paymentThirdpartyStatus);
            
                if(!empty($paymentThirdpartyStatus) && strtolower($paymentThirdpartyStatus) == "sent for settlement")
                {
                    $paymentStatus = true;
                }
                else
                {
                    $logStatus = "Failed";
                    $paymentStatus = false;
                }
            }
            else
            {
                $errorMsg = vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language);
                $res = array('success' => false, 'payment_status' => 'Failed', 'message' => $errorMsg );
                return $res;
            }
        }
        else
        {
            $errorMsg = vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language);
            $res = array('success' => false, 'payment_status' => 'Failed', 'message' => $errorMsg );
            return $res;
        }
            /*Response handling*/
        if($paymentStatus)
        {
            $msg = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
            
            $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $msg );
            $logStatus = "Success";
        }
        else
        {
            $status = "Failed"; 
            $payment_response['errorMessage'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module);
            $errorMsg = isset($payment_response['errorMessage']) && !empty($payment_response['errorMessage']) ? $payment_response['errorMessage'] : 'Error while payment processing!';
            $res = array('success' => true, 'payment_status' => $status, 'message' => $errorMsg);
        }
        /*Create log*/
        PaymentProvidersHelper::createPaymentLog($order_id, self::getName(), $payment_response['pm'], $logResponse, $logStatus, "WorldPay callback");
        return $res;
    }

    public function sendCurlRequest(string $url, string $method, string $path, array $headers, $params)
    {
        global $log;
        $ch = curl_init();

        if(strtolower($method) == 'get')
        {
            curl_setopt_array($ch, [
                //CURLOPT_URL => "https://" . $node . ".b2binpay.com/" . $path,
                CURLOPT_URL => $url . $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
            ]);
        }
        else
        {
            curl_setopt_array($ch, [
                //CURLOPT_URL => "https://" . $node . ".b2binpay.com/" . $path,
                CURLOPT_URL => $url . $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => $headers,
            ]);
        }
        $response = curl_exec($ch);$log->debug('$response===');$log->debug($response);

        curl_close($ch);

        return (false === $response) ? false : json_decode($response, true);
    }
    
    public function getPendingRecordDurationQuery()
    {
        $query = " AND vtiger_crmentity.createdtime < DATE_SUB(NOW(),INTERVAL 15 MINUTE)";
        return $query;
    }
    
    public function getPendingRecordHandlerQuery()
    {
        $query = " AND vtiger_crmentity.createdtime <= DATE_SUB(NOW(),INTERVAL 48 HOUR)";
        return $query;
    }
    
    public function getPaymentCurrentStatus($paymentData = array())
    {
        global $adb,$log;
        $status = "";
        $paymentStatusResponse = array();
        $orderId = $paymentData['order_id'];
        if(!empty($orderId))
        {
            $thirdPartyPayStatus = array('success' => 'success', 'refused' => 'cancelled', 'failed' => 'failed');
            $provider = ServiceProvidersManager::getActiveInstanceByProvider($paymentData['payment_from']);
            $sftpDetail = array(
                'sftp_host' => $provider->parameters['sftp_host'],
                'sftp_port' => $provider->parameters['sftp_port'],
                'sftp_username' => $provider->parameters['sftp_username'],
                'sftp_password' => $provider->parameters['sftp_password'],
            );
            $paymentDate = date('Ymd', strtotime($paymentData['payment_date']));
            $successFileData = array(
                'filename' => '321CST',
                'transaction_date' => $paymentDate,
                'column_of_status' => 0,
                'column_of_order_id' => 24,
                'column_of_error_msg' => 0,
                );
            $successPaymentStatusData = $this->readDartFile($successFileData, $sftpDetail);
            
            $failedFileData = array(
                'filename' => '315REJ',
                'transaction_date' => $paymentDate,
                'column_of_status' => 0,
                'column_of_order_id' => 18,
                'column_of_error_msg' => 19,
                );
            $failedPaymentStatusData = $this->readDartFile($failedFileData, $sftpDetail);
            
            if(array_key_exists($orderId, $successPaymentStatusData))
            {
                $paymentStatusResponse['data'] = json_encode($successPaymentStatusData[$orderId]);
                $paymentStatusResponse['status'] = $thirdPartyPayStatus['success'];
            }
            else if(array_key_exists($orderId, $failedPaymentStatusData))
            {
                $paymentStatusResponse['data'] = json_encode($failedPaymentStatusData[$orderId]);
                $paymentStatusResponse['status'] = $thirdPartyPayStatus['failed'];
                $paymentStatusResponse['message'] = $failedPaymentStatusData[$orderId]['message'];
            }
        }
        return $paymentStatusResponse;
    }
    
    public function readDartFile($fileData = array(), $sftpDetail = array())
    {
        global $log;
        $log->debug('Entering into readDartFile...');
        $log->debug($fileData);
        $log->debug($sftpDetail);
        $fileName = $fileData['filename'];
        $transactionDate = $fileData['transaction_date'];
        $dartFile = 'WP_SSSS_'.$fileName.'_V03_'.$transactionDate.'_nnn.csv';
        $transactionData = array();
        $count = 0;
        $fieldColumnMap = array('status' => $fileData['column_of_status'], 'order_id' => $fileData['column_of_order_id'], 'error_msg' => $fileData['column_of_error_msg']);
        
        $host = $sftpDetail['sftp_host'];
        $port = $sftpDetail['sftp_port'];
        $username = $sftpDetail['sftp_username'];
        $password = $sftpDetail['sftp_password'];
        $connection = NULL;
        $remoteFilePath = $this->sftpDartFileLocation.$dartFile;$log->debug('$remoteFilePath=');$log->debug($remoteFilePath);
        try
        {
            $connection = ssh2_connect($host, $port);
            if(!$connection)
            {
                throw new \Exception("Could not connect to $host on port $port");
            }
            $auth  = ssh2_auth_password($connection, $username, $password);
            if(!$auth)
            {
                throw new \Exception("Could not authenticate with username $username and password ");  
            }
            $sftp = ssh2_sftp($connection);
            if(!$sftp)
            {
                throw new \Exception("Could not initialize SFTP subsystem.");  
            }

            if (($handle = fopen("ssh2.sftp://".$sftp.$remoteFilePath, 'r')) !== FALSE)
            {
                while (($data = fgetcsv($handle, 110000, ",")) !== FALSE)
                {
                    if($data[$count] == '0'){$count++; continue;}
                    if(!empty($data[$fieldColumnMap['order_id']]))
                    {
                        $transactionData[$data[$fieldColumnMap['order_id']]]['status'] = $data[$fieldColumnMap['status']];
                        $transactionData[$data[$fieldColumnMap['order_id']]]['message'] = $data[$fieldColumnMap['error_msg']];
                    }
                    $count++;
                }
                @fclose($data);
                $connection = NULL;
            }
            else
            {
                $log->debug('Could not open file:');
                throw new \Exception("Could not open file:");
            }
         }
         catch (Exception $e)
         {
            $log->debug('Error due to :'.$e->getMessage());
            echo "Error due to :".$e->getMessage();
         }
        $log->debug('$transactionData=');
        $log->debug($transactionData);
        return $transactionData;
    }

}

?>
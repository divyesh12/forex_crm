<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once 'modules/ServiceProviders/PaymentProvidersHelper.php';

class ServiceProviders_PayTechno_Provider extends ServiceProviders_AbstractPaymentGatways_Model
{

    protected $module = 'Payments';
    protected $translate_module = 'CustomerPortal_Client'; // Common label file
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'mandatory' => true),
        array('name' => 'api_username', 'label' => 'API UserName', 'type' => 'text', 'mandatory' => true),
        array('name' => 'api_password', 'label' => 'API Password', 'type' => 'password', 'mandatory' => true),
        array('name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'mandatory' => true),
        array('name' => 'store_id_web', 'label' => 'Store ID For Web', 'type' => 'text', 'mandatory' => true),
        array('name' => 'store_id_mob', 'label' => 'Store ID For Mobile', 'type' => 'text', 'mandatory' => true),
        array('name' => 'terminal_key_usd', 'label' => 'USD Terminal Key', 'type' => 'textarea', 'mandatory' => true),        
        array('name' => 'test_mode', 'label' => 'Test Mode', 'type' => 'picklist', 'picklistvalues' => array('Yes' => 'Yes', 'No' => 'No'), 'mandatory' => true),
        array('name' => 'test_feed_url', 'label' => 'Test Feed URL', 'type' => 'text', 'mandatory' => true),
        array('name' => 'base_feed_url', 'label' => 'Base Feed URL', 'type' => 'text', 'mandatory' => true),
    );
    private static $DEPOSIT_FORM_PARAMETERS = array(
        array('name' => 'phone', 'label' => 'CAB_LBL_PHONE_NUMBER', 'type' => 'number', 'required' => true),
        array('name' => 'street', 'label' => 'CAB_LBL_BILLING_STREET', 'type' => 'text', 'required' => true),
        array('name' => 'city', 'label' => 'CAB_LBL_BILLING_CITY', 'type' => 'text', 'allow' => 'character', 'required' => true),
        array('name' => 'zip', 'label' => 'CAB_LBL_BILLING_ZIP', 'type' => 'text', 'required' => true),
        array('name' => 'state', 'label' => 'CAB_LBL_BILLING_STATE', 'type' => 'text', 'allow' => 'character', 'required' => true),
        array('name' => 'country', 'label' => 'CAB_LBL_BILLING_COUNTRY', 'type' => 'dropdown_depended', 'picklist' => array(
            array("isAllow" => "true", "value" => "", "label" => "Select Country"),
            array("isAllow" => "true", "value" => "AF", "label" => "AFGHANISTAN"),
            array("isAllow" => "true", "value" => "AL", "label" => "ALBANIA"),
            array("isAllow" => "true", "value" => "DZ", "label" => "ALGERIA"),
            array("isAllow" => "true", "value" => "AS", "label" => "AMERICAN SAMOA"),
            array("isAllow" => "true", "value" => "AD", "label" => "ANDORRA"),
            array("isAllow" => "true", "value" => "AO", "label" => "ANGOLA"),
            array("isAllow" => "true", "value" => "AI", "label" => "ANGUILLA"),
            array("isAllow" => "true", "value" => "AQ", "label" => "ANTARCTICA"),
            array("isAllow" => "true", "value" => "AG", "label" => "ANTIGUA AND BARBUDA"),
            array("isAllow" => "true", "value" => "AR", "label" => "ARGENTINA"),
            array("isAllow" => "true", "value" => "AM", "label" => "ARMENIA"),
            array("isAllow" => "true", "value" => "AW", "label" => "ARUBA"),
            array("isAllow" => "true", "value" => "AU", "label" => "AUSTRALIA"),
            array("isAllow" => "true", "value" => "AT", "label" => "AUSTRIA"),
            array("isAllow" => "true", "value" => "AZ", "label" => "AZERBAIJAN"),
            array("isAllow" => "true", "value" => "BS", "label" => "BAHAMAS"),
            array("isAllow" => "true", "value" => "BH", "label" => "BAHRAIN"),
            array("isAllow" => "true", "value" => "BD", "label" => "BANGLADESH"),
            array("isAllow" => "true", "value" => "BB", "label" => "BARBADOS"),
            array("isAllow" => "true", "value" => "BY", "label" => "BELARUS"),
            array("isAllow" => "true", "value" => "BE", "label" => "BELGIUM"),
            array("isAllow" => "true", "value" => "BZ", "label" => "BELIZE"),
            array("isAllow" => "true", "value" => "BJ", "label" => "BENIN"),
            array("isAllow" => "true", "value" => "BM", "label" => "BERMUDA"),
            array("isAllow" => "true", "value" => "BT", "label" => "BHUTAN"),
            array("isAllow" => "true", "value" => "BO", "label" => "BOLIVIA"),
            array("isAllow" => "true", "value" => "BW", "label" => "BOTSWANA"),
            array("isAllow" => "true", "value" => "BV", "label" => "BOUVET ISLAND"),
            array("isAllow" => "true", "value" => "BR", "label" => "BRAZIL"),
            array("isAllow" => "true", "value" => "IO", "label" => "BRITISH INDIAN OCEAN TERRITORY"),
            array("isAllow" => "true", "value" => "BN", "label" => "BRUNEI DARUSSALAM"),
            array("isAllow" => "true", "value" => "BG", "label" => "BULGARIA"),
            array("isAllow" => "true", "value" => "BF", "label" => "BURKINA FASO"),
            array("isAllow" => "true", "value" => "BI", "label" => "BURUNDI"),
            array("isAllow" => "true", "value" => "KH", "label" => "CAMBODIA"),
            array("isAllow" => "true", "value" => "CM", "label" => "CAMEROON"),
            array("isAllow" => "true", "value" => "CA", "label" => "CANADA"),
            array("isAllow" => "true", "value" => "CV", "label" => "CAPE VERDE"),
            array("isAllow" => "true", "value" => "KY", "label" => "CAYMAN ISLANDS"),
            array("isAllow" => "true", "value" => "CF", "label" => "CENTRAL AFRICAN REPUBLIC"),
            array("isAllow" => "true", "value" => "TD", "label" => "CHAD"),
            array("isAllow" => "true", "value" => "CL", "label" => "CHILE"),
            array("isAllow" => "true", "value" => "CN", "label" => "CHINA"),
            array("isAllow" => "true", "value" => "CX", "label" => "CHRISTMAS ISLAND"),
            array("isAllow" => "true", "value" => "CC", "label" => "COCOS (KEELING) ISLANDS"),
            array("isAllow" => "true", "value" => "CO", "label" => "COLOMBIA"),
            array("isAllow" => "true", "value" => "KM", "label" => "COMOROS"),
            array("isAllow" => "true", "value" => "CG", "label" => "CONGO"),
            array("isAllow" => "true", "value" => "CK", "label" => "COOK ISLANDS"),
            array("isAllow" => "true", "value" => "CR", "label" => "COSTA RICA"),
            array("isAllow" => "true", "value" => "CI", "label" => "COTE D'IVOIRE"),
            array("isAllow" => "true", "value" => "CU", "label" => "CUBA"),
            array("isAllow" => "true", "value" => "CW", "label" => "CURAСAO"),
            array("isAllow" => "true", "value" => "CY", "label" => "CYPRUS"),
            array("isAllow" => "true", "value" => "CZ", "label" => "CZECH REPUBLIC"),
            array("isAllow" => "true", "value" => "DK", "label" => "DENMARK"),
            array("isAllow" => "true", "value" => "DJ", "label" => "DJIBOUTI"),
            array("isAllow" => "true", "value" => "DM", "label" => "DOMINICA"),
            array("isAllow" => "true", "value" => "DO", "label" => "DOMINICAN REPUBLIC"),
            array("isAllow" => "true", "value" => "EC", "label" => "ECUADOR"),
            array("isAllow" => "true", "value" => "EG", "label" => "EGYPT"),
            array("isAllow" => "true", "value" => "SV", "label" => "EL SALVADOR"),
            array("isAllow" => "true", "value" => "GQ", "label" => "EQUATORIAL GUINEA"),
            array("isAllow" => "true", "value" => "ER", "label" => "ERITREA"),
            array("isAllow" => "true", "value" => "EE", "label" => "ESTONIA"),
            array("isAllow" => "true", "value" => "ET", "label" => "ETHIOPIA"),
            array("isAllow" => "true", "value" => "FO", "label" => "FAROE ISLANDS"),
            array("isAllow" => "true", "value" => "FK", "label" => "FALKLAND ISLANDS (MALVINAS)"),
            array("isAllow" => "true", "value" => "FJ", "label" => "FIJI"),
            array("isAllow" => "true", "value" => "FI", "label" => "FINLAND"),
            array("isAllow" => "true", "value" => "FR", "label" => "FRANCE"),
            array("isAllow" => "true", "value" => "GF", "label" => "FRENCH GUIANA"),
            array("isAllow" => "true", "value" => "PF", "label" => "FRENCH POLYNESIA"),
            array("isAllow" => "true", "value" => "TF", "label" => "FRENCH SOUTHERN TERRITORIES"),
            array("isAllow" => "true", "value" => "GA", "label" => "GABON"),
            array("isAllow" => "true", "value" => "GM", "label" => "GAMBIA"),
            array("isAllow" => "true", "value" => "GE", "label" => "GEORGIA"),
            array("isAllow" => "true", "value" => "DE", "label" => "GERMANY"),
            array("isAllow" => "true", "value" => "GH", "label" => "GHANA"),
            array("isAllow" => "true", "value" => "GI", "label" => "GIBRALTAR"),
            array("isAllow" => "true", "value" => "GR", "label" => "GREECE"),
            array("isAllow" => "true", "value" => "GL", "label" => "GREENLAND"),
            array("isAllow" => "true", "value" => "GD", "label" => "GRENADA"),
            array("isAllow" => "true", "value" => "GP", "label" => "GUADELOUPE"),
            array("isAllow" => "true", "value" => "GU", "label" => "GUAM"),
            array("isAllow" => "true", "value" => "GT", "label" => "GUATEMALA"),
            array("isAllow" => "true", "value" => "GN", "label" => "GUINEA"),
            array("isAllow" => "true", "value" => "GW", "label" => "GUINEA-BISSAU"),
            array("isAllow" => "true", "value" => "GY", "label" => "GUYANA"),
            array("isAllow" => "true", "value" => "HT", "label" => "HAITI"),
            array("isAllow" => "true", "value" => "VA", "label" => "HOLY SEE (VATICAN CITY STATE)"),
            array("isAllow" => "true", "value" => "HN", "label" => "HONDURAS"),
            array("isAllow" => "true", "value" => "HK", "label" => "HONG KONG"),
            array("isAllow" => "true", "value" => "HU", "label" => "HUNGARY"),
            array("isAllow" => "true", "value" => "IS", "label" => "ICELAND"),
            array("isAllow" => "true", "value" => "IN", "label" => "INDIA"),
            array("isAllow" => "true", "value" => "ID", "label" => "INDONESIA"),
            array("isAllow" => "true", "value" => "IQ", "label" => "IRAQ"),
            array("isAllow" => "true", "value" => "IE", "label" => "IRELAND"),
            array("isAllow" => "true", "value" => "IL", "label" => "ISRAEL"),
            array("isAllow" => "true", "value" => "IT", "label" => "ITALY"),
            array("isAllow" => "true", "value" => "JM", "label" => "JAMAICA"),
            array("isAllow" => "true", "value" => "JP", "label" => "JAPAN"),
            array("isAllow" => "true", "value" => "JO", "label" => "JORDAN"),
            array("isAllow" => "true", "value" => "KZ", "label" => "KAZAKHSTAN"),
            array("isAllow" => "true", "value" => "KE", "label" => "KENYA"),
            array("isAllow" => "true", "value" => "KI", "label" => "KIRIBATI"),
            array("isAllow" => "true", "value" => "KP", "label" => "DEMOCRATIC PEOPLE'S REPUBLIC OF KOREA"),
            array("isAllow" => "true", "value" => "KR", "label" => "REPUBLIC OF KOREA"),
            array("isAllow" => "true", "value" => "KW", "label" => "KUWAIT"),
            array("isAllow" => "true", "value" => "KG", "label" => "KYRGYZSTAN"),
            array("isAllow" => "true", "value" => "LA", "label" => "LAO PEOPLE'S DEMOCRATIC REPUBLIC"),
            array("isAllow" => "true", "value" => "LV", "label" => "LATVIA"),
            array("isAllow" => "true", "value" => "LB", "label" => "LEBANON"),
            array("isAllow" => "true", "value" => "LS", "label" => "LESOTHO"),
            array("isAllow" => "true", "value" => "LR", "label" => "LIBERIA"),
            array("isAllow" => "true", "value" => "LY", "label" => "LIBYAN ARAB JAMAHIRIYA"),
            array("isAllow" => "true", "value" => "LI", "label" => "LIECHTENSTEIN"),
            array("isAllow" => "true", "value" => "LT", "label" => "LITHUANIA"),
            array("isAllow" => "true", "value" => "LU", "label" => "LUXEMBOURG"),
            array("isAllow" => "true", "value" => "MG", "label" => "MADAGASCAR"),
            array("isAllow" => "true", "value" => "MW", "label" => "MALAWI"),
            array("isAllow" => "true", "value" => "MY", "label" => "MALAYSIA"),
            array("isAllow" => "true", "value" => "MV", "label" => "MALDIVES"),
            array("isAllow" => "true", "value" => "ML", "label" => "MALI"),
            array("isAllow" => "true", "value" => "MT", "label" => "MALTA"),
            array("isAllow" => "true", "value" => "MH", "label" => "MARSHALL ISLANDS"),
            array("isAllow" => "true", "value" => "MQ", "label" => "MARTINIQUE"),
            array("isAllow" => "true", "value" => "MR", "label" => "MAURITANIA"),
            array("isAllow" => "true", "value" => "MU", "label" => "MAURITIUS"),
            array("isAllow" => "true", "value" => "YT", "label" => "MAYOTTE"),
            array("isAllow" => "true", "value" => "MX", "label" => "MEXICO"),
            array("isAllow" => "true", "value" => "MC", "label" => "MONACO"),
            array("isAllow" => "true", "value" => "MN", "label" => "MONGOLIA"),
            array("isAllow" => "true", "value" => "MS", "label" => "MONTSERRAT"),
            array("isAllow" => "true", "value" => "MA", "label" => "MOROCCO"),
            array("isAllow" => "true", "value" => "MZ", "label" => "MOZAMBIQUE"),
            array("isAllow" => "true", "value" => "MM", "label" => "MYANMAR"),
            array("isAllow" => "true", "value" => "NA", "label" => "NAMIBIA"),
            array("isAllow" => "true", "value" => "NR", "label" => "NAURU"),
            array("isAllow" => "true", "value" => "NP", "label" => "NEPAL"),
            array("isAllow" => "true", "value" => "NL", "label" => "NETHERLANDS"),
            array("isAllow" => "true", "value" => "AN", "label" => "NETHERLANDS ANTILLES"),
            array("isAllow" => "true", "value" => "NC", "label" => "NEW CALEDONIA"),
            array("isAllow" => "true", "value" => "NZ", "label" => "NEW ZEALAND"),
            array("isAllow" => "true", "value" => "NI", "label" => "NICARAGUA"),
            array("isAllow" => "true", "value" => "NE", "label" => "NIGER"),
            array("isAllow" => "true", "value" => "NG", "label" => "NIGERIA"),
            array("isAllow" => "true", "value" => "NU", "label" => "NIUE"),
            array("isAllow" => "true", "value" => "NF", "label" => "NORFOLK ISLAND"),
            array("isAllow" => "true", "value" => "MP", "label" => "NORTHERN MARIANA ISLANDS"),
            array("isAllow" => "true", "value" => "NO", "label" => "NORWAY"),
            array("isAllow" => "true", "value" => "OM", "label" => "OMAN"),
            array("isAllow" => "true", "value" => "PK", "label" => "PAKISTAN"),
            array("isAllow" => "true", "value" => "PW", "label" => "PALAU"),
            array("isAllow" => "true", "value" => "PS", "label" => "PALESTINIAN TERRITORY"),
            array("isAllow" => "true", "value" => "PA", "label" => "PANAMA"),
            array("isAllow" => "true", "value" => "PG", "label" => "PAPUA NEW GUINEA"),
            array("isAllow" => "true", "value" => "PY", "label" => "PARAGUAY"),
            array("isAllow" => "true", "value" => "PE", "label" => "PERU"),
            array("isAllow" => "true", "value" => "PH", "label" => "PHILIPPINES"),
            array("isAllow" => "true", "value" => "PN", "label" => "PITCAIRN"),
            array("isAllow" => "true", "value" => "PL", "label" => "POLAND"),
            array("isAllow" => "true", "value" => "PT", "label" => "PORTUGAL"),
            array("isAllow" => "true", "value" => "PR", "label" => "PUERTO RICO"),
            array("isAllow" => "true", "value" => "QA", "label" => "QATAR"),
            array("isAllow" => "true", "value" => "RE", "label" => "REUNION"),
            array("isAllow" => "true", "value" => "RO", "label" => "ROMANIA"),
            array("isAllow" => "true", "value" => "RU", "label" => "RUSSIAN FEDERATION"),
            array("isAllow" => "true", "value" => "XK", "label" => "REPUBLIC OF KOSOVO"),
            array("isAllow" => "true", "value" => "RW", "label" => "RWANDA"),
            array("isAllow" => "true", "value" => "SH", "label" => "SAINT HELENA"),
            array("isAllow" => "true", "value" => "KN", "label" => "SAINT KITTS AND NEVIS"),
            array("isAllow" => "true", "value" => "LC", "label" => "SAINT LUCIA"),
            array("isAllow" => "true", "value" => "PM", "label" => "SAINT PIERRE AND MIQUELON"),
            array("isAllow" => "true", "value" => "VC", "label" => "SAINT VINCENT AND THE GRENADINES"),
            array("isAllow" => "true", "value" => "WS", "label" => "SAMOA"),
            array("isAllow" => "true", "value" => "SM", "label" => "SAN MARINO"),
            array("isAllow" => "true", "value" => "ST", "label" => "SAO TOME AND PRINCIPE"),
            array("isAllow" => "true", "value" => "SA", "label" => "SAUDI ARABIA"),
            array("isAllow" => "true", "value" => "SN", "label" => "SENEGAL"),
            array("isAllow" => "true", "value" => "SC", "label" => "SEYCHELLES"),
            array("isAllow" => "true", "value" => "SL", "label" => "SIERRA LEONE"),
            array("isAllow" => "true", "value" => "SG", "label" => "SINGAPORE"),
            array("isAllow" => "true", "value" => "SI", "label" => "SLOVENIA"),
            array("isAllow" => "true", "value" => "SB", "label" => "SOLOMON ISLANDS"),
            array("isAllow" => "true", "value" => "SO", "label" => "SOMALIA"),
            array("isAllow" => "true", "value" => "ZA", "label" => "SOUTH AFRICA"),
            array("isAllow" => "true", "value" => "ES", "label" => "SPAIN"),
            array("isAllow" => "true", "value" => "LK", "label" => "SRI LANKA"),
            array("isAllow" => "true", "value" => "SD", "label" => "SUDAN"),
            array("isAllow" => "true", "value" => "SR", "label" => "SURINAME"),
            array("isAllow" => "true", "value" => "SZ", "label" => "SWAZILAND"),
            array("isAllow" => "true", "value" => "SE", "label" => "SWEDEN"),
            array("isAllow" => "true", "value" => "CH", "label" => "SWITZERLAND"),
            array("isAllow" => "true", "value" => "SY", "label" => "SYRIAN ARAB REPUBLIC"),
            array("isAllow" => "true", "value" => "TJ", "label" => "TAJIKISTAN"),
            array("isAllow" => "true", "value" => "TH", "label" => "THAILAND"),
            array("isAllow" => "true", "value" => "TL", "label" => "TIMOR-LESTE"),
            array("isAllow" => "true", "value" => "TG", "label" => "TOGO"),
            array("isAllow" => "true", "value" => "TK", "label" => "TOKELAU"),
            array("isAllow" => "true", "value" => "TO", "label" => "TONGA"),
            array("isAllow" => "true", "value" => "TT", "label" => "TRINIDAD AND TOBAGO"),
            array("isAllow" => "true", "value" => "TN", "label" => "TUNISIA"),
            array("isAllow" => "true", "value" => "TR", "label" => "TURKEY"),
            array("isAllow" => "true", "value" => "TM", "label" => "TURKMENISTAN"),
            array("isAllow" => "true", "value" => "TC", "label" => "TURKS AND CAICOS ISLANDS"),
            array("isAllow" => "true", "value" => "TV", "label" => "TUVALU"),
            array("isAllow" => "true", "value" => "UG", "label" => "UGANDA"),
            array("isAllow" => "true", "value" => "UA", "label" => "UKRAINE"),
            array("isAllow" => "true", "value" => "AE", "label" => "UNITED ARAB EMIRATES"),
            array("isAllow" => "true", "value" => "GB", "label" => "UNITED KINGDOM"),
            array("isAllow" => "true", "value" => "US", "label" => "UNITED STATES"),
            array("isAllow" => "true", "value" => "UM", "label" => "UNITED STATES MINOR OUTLYING ISLANDS"),
            array("isAllow" => "true", "value" => "UY", "label" => "URUGUAY"),
            array("isAllow" => "true", "value" => "UZ", "label" => "UZBEKISTAN"),
            array("isAllow" => "true", "value" => "VU", "label" => "VANUATU"),
            array("isAllow" => "true", "value" => "VE", "label" => "VENEZUELA"),
            array("isAllow" => "true", "value" => "VN", "label" => "VIET NAM"),
            array("isAllow" => "true", "value" => "CD", "label" => "CONGO"),
            array("isAllow" => "true", "value" => "EH", "label" => "WESTERN SAHARA"),
            array("isAllow" => "true", "value" => "YE", "label" => "YEMEN"),
            array("isAllow" => "true", "value" => "YU", "label" => "YUGOSLAVIA"),
            array("isAllow" => "true", "value" => "ZM", "label" => "ZAMBIA"),
            array("isAllow" => "true", "value" => "ZW", "label" => "ZIMBABWE"),
            array("isAllow" => "true", "value" => "BA", "label" => "BOSNIA AND HERZEGOVINA"),
            array("isAllow" => "true", "value" => "FM", "label" => "MICRONESIA"),
            array("isAllow" => "true", "value" => "HM", "label" => "HEARD AND MC DONALD ISLANDS"),
            array("isAllow" => "true", "value" => "HR", "label" => "CROATIA (HRVATSKA)"),
            array("isAllow" => "true", "value" => "IR", "label" => "IRAN (ISLAMIC REPUBLIC OF)"),
            array("isAllow" => "true", "value" => "MO", "label" => "MACAU"),
            array("isAllow" => "true", "value" => "MD", "label" => "MOLDOVA"),
            array("isAllow" => "true", "value" => "MK", "label" => "MACEDONIA"),
            array("isAllow" => "true", "value" => "GS", "label" => "SOUTH GEORGIA &amp; SOUTH SANDWICH ISLANDS"),
            array("isAllow" => "true", "value" => "SJ", "label" => "SVALBARD AND JAN MAYEN ISLANDS"),
            array("isAllow" => "true", "value" => "SK", "label" => "SLOVAKIA (SLOVAK REPUBLIC)"),
            array("isAllow" => "true", "value" => "TP", "label" => "EAST TIMOR"),
            array("isAllow" => "true", "value" => "TW", "label" => "TAIWAN"),
            array("isAllow" => "true", "value" => "TZ", "label" => "TANZANIA"),
            array("isAllow" => "true", "value" => "VG", "label" => "VIRGIN ISLANDS (BRITISH)"),
            array("isAllow" => "true", "value" => "VI", "label" => "VIRGIN ISLANDS (U.S.)"),
            array("isAllow" => "true", "value" => "WF", "label" => "WALLIS AND FUTUNA ISLANDS"),
            array("isAllow" => "true", "value" => "RS", "label" => "REPUBLIC OF SERBIA"),
        ), 'required' => true, 'dependency' => 'paytechno_payment_method'),
    );
    private static $WITHDRAW_FORM_PARAMETERS = array(
        
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'PayTechno'; // don't take name with any space or special charctor
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams()
    {
        return array_merge(self::$REQUIRED_PARAMETERS, self::DEFAULT_REQUIRED_PARAMETERS);
        //return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get deposit parameters
     * @return <array> required parameters list
     */
    public function getDepositFormParams()
    {
        return self::$DEPOSIT_FORM_PARAMETERS;
    }

    /**
     * Function to get withdrawal parameters
     * @return <array> required parameters list
     */
    public function getWithdrawFormParams()
    {
        return self::$WITHDRAW_FORM_PARAMETERS;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    public function prepareParameters()
    {
        foreach (self::$REQUIRED_PARAMETERS as $key => $fieldInfo) {
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }
        return $params;
    }

    public function paymentProcess($request, $portal_language)
    {
        global $PORTAL_URL, $site_URL;

        $order_id = PaymentProvidersHelper::generateUUID(); //Generated the unique order id from database

        if (!$order_id) {
            return array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_GENERATING_ORDER_ID', $this->module, $portal_language));
        }

        if (!empty($request)) {
            //Get response
            $returnUrl = $PORTAL_URL . "#/payments/success?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            $cancelUrl = $PORTAL_URL . "#/payments/fail?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";

            //For call back handling which hook form third part
            $callBackUrl = $site_URL . 'modules/CustomerPortal/thirdparty/payment_callback.php?pm=' . $request['payment_from'] . '&order_id=' . $order_id;

            if ($request['is_mobile_request']) {
                $returnUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
                $cancelUrl = $site_URL . "modules/CustomerPortal/payment_callback.php?orid=" . $order_id . "&pm=" . $request['payment_from'] . "";
            }
            if ($request['payment_operation'] == 'Deposit') {
                session_start();

                $provider = ServiceProvidersManager::getActiveInstanceByProvider($request['payment_from']);
                $amount_value = number_format($request['net_amount'], $provider->parameters['allowed_decimal'], '.', '');
                $amount_value = $amount_value * 100; // this payment gateway take * 100 passing amount from user

                $merchant_id = $provider->parameters['merchant_id'];
                $api_username = $provider->parameters['api_username'];
                $api_password = $provider->parameters['api_password'];
                $secret_key = $provider->parameters['secret_key'];
                $terminal_key_usd = $provider->parameters['terminal_key_usd'];
                $test_feed_url = $provider->parameters['test_feed_url'];
                $base_feed_url = $provider->parameters['base_feed_url'];
                    
                $store_id = $provider->parameters['store_id_web'];
                if ($request['is_mobile_request']) {
                    $store_id = $provider->parameters['store_id_mob'];
                }

                $serviceName = "PayTechno Payment";
                $IP = $_SERVER['REMOTE_ADDR'];
                $description = $provider->parameters['description'];
                $display_currency = $request['payment_currency'];
                                
                $contact_data = PaymentProvidersHelper::getContactDetails($request['contactid']);
                $firstname = $contact_data['firstname'];
                $lastname = $contact_data['lastname'];
                $email = $contact_data['email'];
                $cardBinn = substr($request['cardno'], 0, 6);
                $cardLast = substr($request['cardno'], -4);

                $signature = $merchant_id.';'.strtoupper($order_id).';;;'.$amount_value.';'.strtoupper($display_currency).';'.$terminal_key_usd.';'.strtoupper($email).';'.strtoupper($firstname).';'.strtoupper($lastname).';'.$secret_key;
                // $signature = hash("sha512", $signature);
                $signature = strtoupper(hash('sha512',$signature));

                if ($provider->parameters['test_mode'] == 'Yes') {
                    $action_url = $test_feed_url;
                } else {
                    $action_url = $base_feed_url;
                }

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $action_url . 'environment/' . $merchant_id,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "cache-control: no-cache",
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    $output = json_decode($response);
                    if ($output->errorMessage == 'Success') {
                        $authenticationAPIURL = $output->authenticationAPI->url;
                        $transactionsAPIURL = $output->transactionsAPI->url;
                        $tokenParam = array('MerchantLongId' => $merchant_id, 'ApiUserName' => $api_username, 'ApiPassword' => $api_password);
                        $tokenParam = json_encode($tokenParam);

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $authenticationAPIURL . "/api/v1/gateway/authorization",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => 2,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => $tokenParam,
                            CURLOPT_HTTPHEADER => array(
                                "cache-control: no-cache",
                                "content-type: application/json",
                            ),
                        ));
                        $responseToken = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);
                        if ($err) {
                            echo "cURL Error #:" . $err;
                        } else {
                            $outputToken = json_decode($responseToken);
                            if (isset($outputToken->errorMessage) && $outputToken->errorMessage != '') {
                                return array('success' => false, 'message' => $outputToken->errorMessage);
                            }
                            $accessToken = $outputToken->token;
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $transactionsAPIURL . "/api/v1/gateway/transaction/init",
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_SSL_VERIFYPEER => false,
                                CURLOPT_SSL_VERIFYHOST => 2,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => '{
                                    "TransactionDetails": {
                                        "OrderId": "' . $order_id . '",
                                        "ServiceName": "' . $serviceName . '",
                                        "OriginalCurrency": "' . $display_currency . '",
                                        "OriginalAmount": "' . $amount_value . '",
                                        "TerminalKey": "' . $terminal_key_usd . '",
                                    },
                                    "PayerDetails": {
                                        "FirstName": "' . $firstname . '",
                                        "LastName": "' . $lastname . '",
                                        "Email": "' . $email . '",
                                        "Phone": "' . $request['phone'] . '"
                                    },
                                    "PayerDevice": {
                                        "Ip": "' . $IP . '"
                                    },
                                    "BillingAddress": {
                                        "Street": "' . $request['street'] . '",
                                        "City": "' . $request['city'] . '",
                                        "Zip": "' . $request['zip'] . '",
                                        "State": "' . $request['state'] . '",
                                        "Country": "' . $request['country'] . '"
                                    },
                                    "StoreId": "' . $store_id . '",
                                    "Return" : "' . $returnUrl . '",
                                    "Notification" : "' . $callBackUrl . '",
                                    "Signature": "' . $signature . '"
                                }',
                                CURLOPT_HTTPHEADER => array(
                                    "authorization: Bearer " . $accessToken,
                                    "content-type: application/json",
                                ),
                            ));
                            $responseTransaction = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);

                            if ($err) {
                                echo "cURL Error #:" . $err;
                            } else {
                                $outputTransaction = json_decode($responseTransaction);
                                if (isset($outputTransaction->errorCode) && $outputTransaction->errorCode == 0) {
                                    $redirectUrl = $outputTransaction->payeerRedirectUrl;

                                    $request['redirectUrl'] = $redirectUrl;
                                    $request['PayTechno_response'] = $outputTransaction->transactionDetails;
                                    if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $request['payment_from'], $request, 'Created', 'Bill Creation')) {
                                        $res = array('success' => true, 'result' => array('payment_status' => 'Pending', 'type' => 'Redirect', 'redirect_url' => $redirectUrl, 'order_id' => $order_id));
                                    } else {
                                        $res = array('success' => false, 'message' => vtranslate('CAB_MSG_THERE_IS_AN_ISSUE_IN_DB_OPERATION', $this->module, $portal_language));
                                    }
                                } else {
                                    $errorMessage = $outputTransaction->errorMessage;
                                    $res = array('success' => false, 'message' => $errorMessage);
                                    // $res = array('success' => false, 'message' => vtranslate($errorMessage, $this->module));                  
                                }
                            }
                        }
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

    public function getVerifyTransferDetails($request, $FILES, $portal_language)
    {
        $paymentInput = array();
        if ($request['payment_operation'] == 'Deposit') {
            $paymentInput = $this->getDepositFormParams();
        } else if ($request['payment_operation'] == 'Withdrawal') {
            $paymentInput = $this->getWithdrawFormParams();
        } else {
            $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PAYMENT_OPER_DOES_NOT_MATCH', $this->module, $portal_language));
        }

        if ($request['payment_operation'] == 'Deposit') {
            if (strlen($request['phone']) < 8 || strlen($request['phone']) > 16) {
                return $res = array('success' => false, 'message' => vtranslate('CAB_MSG_PHONE_NUMBER_LENGTH_SHOULD_BE_BETWEEN_8_16', $this->module, $portal_language));
            }
            if(preg_match('/[^a-zA-Z\s]/i', $request['city'])) {
                return $res = array('success' => false, 'message' => vtranslate('CAB_MSG_INVALID_CITY_ONLY_CHARACTER_ALLOWED', $this->module, $portal_language));
            }
            if(preg_match('/[^a-zA-Z\s]/i', $request['state'])) {
                return $res = array('success' => false, 'message' => vtranslate('CAB_MSG_INVALID_STATE_ONLY_CHARACTER_ALLOWED', $this->module, $portal_language));
            }
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
        return $res;
    }

    //Verify the payment response and insert to payment log table
    public function paymentResponseVerification($status, $payment_response, $order_id, $portal_language)
    {
        if (PaymentProvidersHelper::getPaymentRecord($order_id)) {
            if ($status == 'Success') {
                $payment_response['message'] = vtranslate('CAB_MSG_PAYMENT_HAS_BEEN_COMPLETED_SUCCESS', $this->module, $portal_language);
                $res = array('success' => true, 'payment_status' => 'Confirmed', 'message' => $payment_response['message']);
            }
            if ($status == 'Failed') {
                if (!isset($payment_response['StatusMessage'])) {
                    $status = 'Cancelled';
                    $payment_response['message'] = vtranslate('CAB_MSG_CANCELLED_BY_USER', $this->module, $portal_language);
                } else {
                    $payment_response['message'] = $payment_response['StatusMessage'];
                }
                $res = array('success' => true, 'payment_status' => $status, 'message' => $payment_response['message']);
            }
            if (PaymentProvidersHelper::createPaymentLog($order_id, $this->getName(), $payment_response['pm'], $payment_response, $status, $payment_response['message'])) {

            }
        } else {
            $status == 'Failed';
            $res = array('success' => false, 'payment_status' => $status, 'message' => vtranslate('CAB_MSG_INVALID_ACTION', $this->module, $portal_language));
        }
        return $res;
    }
    
}

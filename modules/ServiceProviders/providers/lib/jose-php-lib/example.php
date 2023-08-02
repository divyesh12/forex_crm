<?php
require_once('vendor/autoload.php');

// an example of OpenID Connect ID Token implementation

class IdToken {
    var $jwt;

    function __construct($claims = array()) {
        $this->jwt = new JOSE_JWT($claims);
    }

    function sign($private_key_or_secret, $algorithm = 'RS256') {
        $this->jwt = $this->jwt->sign($private_key_or_secret, $algorithm);
        return $this;
    }

    function toString() {
        return $this->jwt->toString();
    }
}
$private_key = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQC8kGa1pSjbSYZVebtTRBLxBz5H4i2p/llLCrEeQhta5kaQu/Rn
vuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t0tyazyZ8JXw+KgXTxldMPEL9
5+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4ehde/zUxo6UvS7UrBQIDAQAB
AoGAb/MXV46XxCFRxNuB8LyAtmLDgi/xRnTAlMHjSACddwkyKem8//8eZtw9fzxz
bWZ/1/doQOuHBGYZU8aDzzj59FZ78dyzNFoF91hbvZKkg+6wGyd/LrGVEB+Xre0J
Nil0GReM2AHDNZUYRv+HYJPIOrB0CRczLQsgFJ8K6aAD6F0CQQDzbpjYdx10qgK1
cP59UHiHjPZYC0loEsk7s+hUmT3QHerAQJMZWC11Qrn2N+ybwwNblDKv+s5qgMQ5
5tNoQ9IfAkEAxkyffU6ythpg/H0Ixe1I2rd0GbF05biIzO/i77Det3n4YsJVlDck
ZkcvY3SK2iRIL4c9yY6hlIhs+K9wXTtGWwJBAO9Dskl48mO7woPR9uD22jDpNSwe
k90OMepTjzSvlhjbfuPN1IdhqvSJTDychRwn1kIJ7LQZgQ8fVz9OCFZ/6qMCQGOb
qaGwHmUK6xzpUbbacnYrIM6nLSkXgOAwv7XXCojvY614ILTK3iXiLBOxPu5Eu13k
eUz9sHyD6vkgZzjtxXECQAkp4Xerf5TGfQXGXhxIX52yH+N2LtujCdkQZjXAsGdm
B2zNzvrlgRmgBrklMTrMYgm1NPcW+bRLGcwgW2PTvNM=
-----END RSA PRIVATE KEY-----
EOD;

$public_key = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
4i2p/llLCrEeQhta5kaQu/RnvuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t
0tyazyZ8JXw+KgXTxldMPEL95+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4
ehde/zUxo6UvS7UrBQIDAQAB
-----END PUBLIC KEY-----
EOD;
//$public_key  = file_get_contents(dirname(__FILE__) . '/../test/fixtures/public_key.pem');
//$private_key = file_get_contents(dirname(__FILE__) . '/../test/fixtures/private_key.pem');

$payload = array(
    "sid" => 40,
    "card_type" => "p2p",
    "tx_action" => 'PAYOUT',
    "firstname" => 'Awepay',
    "lastname" => 'Tester',
    "city" => 'city',
    "email" => 'test@test.com',
    "amount" => '1.00',
    "currency" => 'MYR',
    "bank_code" => 'CIMB',
    "account_name" => 'cardname',
    "account_number" => '1234756789',
    "bank_branch" => 'NA',
    "bank_province" => 'NA',
    "bank_city" => 'NA',
    "postback_url" => 'https://gatewaytest.awepay.com/postback.php',
);



$json_dat=json_encode($payload);
/*$json_dat='{
  "account_number": "114197111090",
  "firstname": "cincai",
  "issueTime": "Wed Mar 10 09:47:11 HKT 2021",
  "city": "NA",
  "subject": "userUuid",
  "successurl": "https://gatewaytest.awepay.com/postback.php?fail=0",
  "item_quantity[]": 1,
  "issuer": "https://osl.com",
  "tid": "1f9c0c46-c5b6-4c7f-ba99-630995de2dss122'.rand().'",
  "jwtId": "4e2e417d-0a7e-48a4-8aec-ecef2d7fe8ce",
  "sid": 40,
  "item_desc[]": "WITHDRAWAL",
  "item_no[]": 1,
  "account_name": "CINCAI",
  "currency": "MYR",
  "email": "evegan+testing@awepay.com",
  "item_amount_unit[]": "1",
  "bank_code": "MBB.MY",
  "amount": "1",
  "address": "pj  pj Malaysia",
  "postback_url": "https://gatewaytest.awepay.com/postback.php",
  "bank_province": "NA",
  "card_type": "p2p",
  "lastname": "cincai",
  "failureurl": "https://gatewaytest.awepay.com/postback.php?fail=1",
  "notBeforeTime": "Wed Mar 10 09:47:11 HKT 2021",
  "phone": "0128987647",
  "expirationTime": "Wed Mar 10 09:57:11 HKT 2021",
  "userUuid": "5b3e9136-40be-4309-9fcb-c6da1f941ca4",
  "item_name[]": "0b10af02-5e93-4294-8c4e-e0240c29ea97",
  "bank_city": "NA",
  "tx_action": "PAYOUT",
  "item_no": [
    1
  ],
  "item_desc": [
    "WITHDRAWAL"
  ],
  "item_name": [
    "0b10af02-5e93-4294-8c4e-e0240c29ea97"
  ],
  "item_quantity": [
    1
  ],
  "item_amount_unit": [
    "1"
  ],
  "ref1": "5b3e9136-40be-4309-9fcb-c6da1f941ca4",
  "ref2": "4e2e417d-0a7e-48a4-8aec-ecef2d7fe8ce"
}'; */

//$id_token = new IdToken($payload);
//$id_token->sign($private_key);

//echo $id_token->toString();

//$jwe = new JOSE_JWE(json_encode($payload));
//$jwe->encrypt($private_key);
//$jwe->encrypt("A128CBC-HS256", 'dir');
//echo $jwe->toString();

$secretplain = "357A2E0F54470FCE1946E461F6BC2C4FE2AA7BA3B06D92D10B17741C7D752AC0";
//$secret = "A128CBC-HS256";
$secret = hex2bin('357A2E0F54470FCE1946E461F6BC2C4FE2AA7BA3B06D92D10B17741C7D752AC0');
$jwe = new JOSE_JWE($json_dat);
$jwe = $jwe->encrypt($secret, 'dir');
$jwe_decoded = JOSE_JWT::decode($jwe->toString());

echo "https://qa.secure.awepay.com/txHandlerPayout.php?token=".$jwe->toString();
//var_dump($jwe_decoded);
//var_dump($jwe->toString());

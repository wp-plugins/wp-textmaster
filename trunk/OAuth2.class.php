<?php

class TextMaster_OAuth2{

	private $urlOAuth = URL_TM_API_OAUTH;
	private $urlInfosUtilisateur = URL_TM_API_OAUTH_USER;

	private $applicationId = OAUTH_APP_ID;
	private $secret = OAUTH_APP_SECRET;

	function __construct ()	{

	}

	function init(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1);
		if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off')
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'tm-wordpress-app agent v1.0');

		return $ch;

	}

	// on recup le token
	function getToken($email='', $password=''){
		$result = '';

		$ch = $this->init();
		curl_setopt($ch, CURLOPT_URL, $this->urlOAuth );

		if ($email != '' && $password != '') {
			$aPost['grant_type'] = 'password';
			$aPost['user[email]'] = $email;
			$aPost['user[password]'] = $password;
			$aPost['client_id'] = $this->applicationId;
			$aPost['client_secret'] = $this->secret;

		}
		else{
			$aPost['grant_type'] = 'client_credentials';
			$aPost['client_id'] = $this->applicationId;
			$aPost['client_secret'] = $this->secret;
		}

//		print_r($aPost);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $aPost);
		$results = curl_exec($ch);
		$aResult = json_decode($results, TRUE);
//		print_r($results);
		$resultInfos = curl_getinfo($ch);
//		print_r($resultInfos);
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
			$results = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';


		curl_close($ch);

		$ret = FALSE;
		if (isset($aResult['access_token'])) {
			$ret =$aResult['access_token'];
		}
		return $ret;
	}

	// pour avoir les infos de l'utilisateur (les clés et secret des API)
	function getUserInfos($token){
		$result = '';

		$ch = $this->init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_URL, $this->urlInfosUtilisateur.'/me' );

		$results = curl_exec($ch);
		$resultInfos = curl_getinfo($ch);
	//	print_r($resultInfos);
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
			$results = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';

		curl_close($ch);
		$aResult = json_decode($results, TRUE);
//		print_r($aResult);
		return $aResult;
	}

	// pour creer un nouvel utilisateur TextMaster
	function createUser($arrayInfos){
		$ch = $this->init();
		$token = $this->getToken();
	//	echo $token;
	//	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'AGENT: tm-wordpress-app/agent v1.0', 'Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_URL, $this->urlInfosUtilisateur );
	//	echo $this->urlInfosUtilisateur;
		$jsonInfos = json_encode($arrayInfos);
	//	print_r($jsonInfos);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonInfos);

	//	curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch),TRUE);
//		print_r($result);
		$resultInfos = curl_getinfo($ch);
//		print_r($resultInfos);
//		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
//			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';

		curl_close($ch);
		return $result;
/*
		user: '',
			email: '',
			password: '',
			confirmation_password: '', locale: '', callback: '',
			contact_information_attributes: { first_name: '', last_name: '',
				profession: '',              company: '',
				website: '',                 vat_number: '',
				address: '',                 address2: '',
				city: '',                    zip_code: '',
				country: '',                 state_region: '',
				credentials_attributes: '',  mother_tongue: '',
				phone_number: '',            contact_options: ''}
*/
		//curl  --header "Content-type: application/json"  http://api.sandbox.textmaster.com/admin/users -X 'POST' -d '{"user": { "email": "foo@bar.com", "password": "please" }'
	}
}

?>
<?php

class TextMaster_OAuth2{

	private $urlOAuth = URL_TM_API_OAUTH;
	private $urlInfosUtilisateur = URL_TM_API_OAUTH_USER;

	private $applicationId = OAUTH_APP_ID;
	private $secret = OAUTH_APP_SECRET;

	var $chCurl = null;

	function __construct ()	{
		$this->chCurl = $this->init();
		$this->applicationId = OAUTH_APP_ID;
		$this->secret = OAUTH_APP_SECRET;
	}

	function __destruct(){
		curl_close($this->chCurl);
	}

	function init(){
		$this->chCurl = curl_init();
		curl_setopt($this->chCurl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($this->chCurl, CURLOPT_VERBOSE, true);
		curl_setopt($this->chCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->chCurl, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0);
		if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off')
			curl_setopt($this->chCurl, CURLOPT_FOLLOWLOCATION, true);
		// proxy
		if (defined('WP_PROXY_HOST')) {
			curl_setopt($this->chCurl,CURLOPT_HTTPHEADER,array("Expect:  "));
			//curl_setopt( $this->chCurl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
			curl_setopt( $this->chCurl, CURLOPT_PROXY, WP_PROXY_HOST );
			if (defined('WP_PROXY_PORT'))
				curl_setopt( $this->chCurl, CURLOPT_PROXYPORT, WP_PROXY_PORT );

			if (defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD')){
				curl_setopt( $this->chCurl, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
				curl_setopt($this->chCurl, CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME.':'.WP_PROXY_PASSWORD);
			}

		}
		curl_setopt($this->chCurl, CURLOPT_USERAGENT, 'tm-wordpress-app agent v1.0');

		return $this->chCurl;

	}

	// on recup le token
	function getToken($email='', $password=''){
		$result = '';
		$this->init();
		curl_setopt($this->chCurl, CURLOPT_URL, $this->urlOAuth );

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
		curl_setopt($this->chCurl, CURLOPT_POST, 1);
		curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, $aPost);
		$results = curl_exec($this->chCurl);
		$aResult = json_decode($results, TRUE);
//		print_r($aResult);
//		echo '<br>---------<br>';
		$resultInfos = curl_getinfo($this->chCurl);
//		print_r($resultInfos);
//		echo '<br>---------<br>';

		if(curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300)
			$results = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';


	//	curl_close($ch);

		$ret = FALSE;
		if (isset($aResult['access_token'])) {
			$ret =$aResult['access_token'];
		}
		return $ret;
	}

	// pour avoir les infos de l'utilisateur (les cles et secret des API)
	function getUserInfos($token){
		$aResult = array();
		$result = '';
//		echo $token;
//		echo '<br>---------<br>';
		$this->chCurl = $this->init();
//		var_dump($this->chCurl);
		curl_setopt($this->chCurl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($this->chCurl, CURLOPT_URL, $this->urlInfosUtilisateur.'/me' );

		$results = curl_exec($this->chCurl);
		$resultInfos = curl_getinfo($this->chCurl);
//		print_r($resultInfos);
//		echo '<br>---------<br>';
//		var_dump(curl_errno($this->chCurl));
		if(curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300)
		{
			$aResult[] = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE);
	//		print_r($aResult);
		}
		else
			$aResult = json_decode($results, TRUE);


		//curl_close($this->chCurl);


//		echo '<br>---------<br>';

		return $aResult;
	}

	// pour creer un nouvel utilisateur TextMaster
	function createUser($arrayInfos){
		$ch = $this->init();
		$token = $this->getToken();
	//	echo $token;
	//	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt($this->chCurl, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'AGENT: tm-wordpress-app/agent v1.0', 'Authorization: Bearer '.$token));
		curl_setopt($this->chCurl, CURLOPT_URL, $this->urlInfosUtilisateur );
	//	echo $this->urlInfosUtilisateur;
		$jsonInfos = json_encode($arrayInfos);
	//	print_r($jsonInfos);
		curl_setopt($this->chCurl, CURLOPT_POST, 1);
		curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, $jsonInfos);

	//	curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($this->chCurl),TRUE);
//		print_r($result);
		$resultInfos = curl_getinfo($this->chCurl);
//		print_r($resultInfos);
//		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
//			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';

	//	curl_close($ch);
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
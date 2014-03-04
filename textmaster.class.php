<?php


/*
* API textmaster
* V 0.4
*/

if (!defined('PHP_VERSION_ID')) {
	$version = explode('.',PHP_VERSION);

	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class textmaster_api{

	var $keyapi ='';
	var $secretapi ='';
	var $locale ='';
	var $dureeResyncro ='86400';

	private  $urlAPiClients = URL_TM_API_CLIENTS;
	private  $urlAPiPublic = URL_TM_API_PUBLIC;

	function __construct(){
			//$this->locale = str_replace('_', '-', get_locale());

	}

	function trierArray(&$multiArray){
		if (count($multiArray) != 0) {
			$tmp = Array();
			foreach($multiArray as $ma)
				$tmp[] = &$ma['value'];
			array_multisort($tmp, $multiArray);
		}
	}

	/*
	* Init du flux curl avec les hearders permettant l'authentification textmaster
	*/
	function init(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
//		curl_setopt($ch, CURLOPT_HEADER , true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'tm-wordpress-app agent v1.0');

		// les timeouts
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT_API); //timeout in seconds

		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->makeHearder());

		return $ch;

	}

	/*
	* Création de l'authentification textmaster
	*/
	function makeHearder(){
		date_default_timezone_set('UTC');
		$date = date('Y-m-d H:i:s');
		$signature = sha1($this->secretapi.$date);

		$headers = array('APIKEY: '.$this->keyapi,'DATE: '.$date, 'SIGNATURE: '.$signature, 'Content-Type: application/json', 'Accept: application/json', 'User-Agent: tm-wordpress-app/agent v1.0');

		return $headers;
	}

	function testAuth()
	{
		$ch = $this->init();
		$url = 'http://api.textmaster.com/test';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		$resultInfos = curl_getinfo($ch);

		if(curl_errno($ch) == CURLE_OPERATION_TIMEOUTED)
			$this->mailAlertSupport($url, FALSE, $resultInfos);

		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);

//		print_r($result);

	}

	public static function sendTracker(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		//		curl_setopt($ch, CURLOPT_HEADER , true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'tm-wordpress-app/agent v1.0');
		curl_setopt($ch, CURLOPT_URL, 'http://www.textmaster.com/?pid=5310711603e44f00020006d3' );
		$result = curl_exec($ch);
		curl_exec($ch);

	}

	/*
	* pour avoir la liste des locales disponibles sur textmaster
	*/
	function getLocales(){
		$ch = $this->init();
		$url = $this->urlAPiPublic . '/locales';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);

		// timout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (Locales)!');
		}

		$resultInfos = curl_getinfo($ch);
		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (Locales)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);
	//	print_r($result);
	}

	/*
	* pour avoir la liste des catégories sur textmaster
	*/
	function getCategories(){
		global $wpdb;

		// on resyncro tous les jours
		if (isset($_SESSION['lastSyncTmCategories']) ||  time() - $_SESSION['lastSyncTmCategories'] > $this->dureeResyncro)
		{
			$ch = $this->init();
			$url = $this->urlAPiPublic . '/categories';

			$infosUserTM = $this->getUserInfos();
			if (!is_array($infosUserTM))
			{
				$infosUserTM = array();
				$infosUserTM['locale'] = str_replace('_', '-', WPLANG);
			}


			$url .= '?locale='. $infosUserTM['locale'];

			curl_setopt($ch, CURLOPT_URL, $url );
			$result = curl_exec($ch);
			$resultInfos = curl_getinfo($ch);

			// timeout avec les API
			if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
				$this->mailAlertSupport($url, FALSE, $resultInfos);
				die('Error API timeout (categories)!');
			}

			// les api ne sont pas dispo
			if($resultInfos['http_code'] === 0) {
				$this->mailAlertSupport($url, TRUE, $resultInfos);
				die('Error API connect (categories)!');
			}
			// code d'erreur HTTP
			if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
				$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
				$this->mailAlertErrHttp($url, $result);
			}

			curl_close($ch);
			//	print_r($result);
			$arrayCats = json_decode($result, TRUE);
			$this->trierArray($arrayCats['categories']);

			$this->syncCategories($arrayCats['categories']);
		}
		else
		{
			$table_categories =  $wpdb->prefix .'tm_categories';
			$reqCats = $wpdb->prepare('SELECT * FROM '.$table_categories, '');
			$arrayCats['categories'] = $wpdb->get_results($reqCats, ARRAY_A);
		}

		return $arrayCats['categories'];
	}

	function syncCategories($arrayCats){
		global $wpdb;

		$table_categories =  $wpdb->prefix .'tm_categories';
		if (count($arrayCats) != 0) {
			$wpdb->query('DELETE FROM '.$table_categories);
			foreach ($arrayCats as $categorie) {
				$reqChk = $wpdb->prepare('SELECT value FROM '.$table_categories.' WHERE code = "%s"', $categorie['code']);
				$chk = $wpdb->get_var( $reqChk);
				if ($chk == '') {
					$sql = 'INSERT INTO '.$table_categories.' (code, value) VALUES (%s, %s)';
					$req = $wpdb->prepare($sql, $categorie['code'], $categorie['value']);
				}
				else if ($chk != $categorie['value']){
					$sql = 'UPDATE '.$table_categories.' SET value=%s WHERE code=%s)';
					$req = $wpdb->prepare($sql,  $categorie['value'], $categorie['code']);
				}

				$wpdb->query($req);
			}
			$_SESSION['lastSyncTmCategories'] = time();
		}
	}

	public static function getLibCategorie($code){
		global $wpdb;
		$table_categories =  $wpdb->prefix .'tm_categories';

		$req = $wpdb->prepare('SELECT value FROM '.$table_categories.' WHERE code = "%s"', $code);
		return $wpdb->get_var( $req);
	}

	/*
	   * pour avoir la liste des niveaux de langages sur textmaster
	*/
	public static function getLanguageLevels(){

		$arrayRet['regular'] = __('Regular','textmaster');
		$arrayRet['premium'] = __('Premium','textmaster');

		return $arrayRet;
	}

	public static function getVocabularyTypes(){
		$vocabulary_types['not_specified'] = __('Non spécifié','textmaster');
		$vocabulary_types['popular'] = __('Populaire','textmaster');
		$vocabulary_types['technical'] = __('Technique','textmaster');
		$vocabulary_types['fictional'] = __('Romancé','textmaster');

		return $vocabulary_types;
	}

	public static function getGrammaticalPersons(){
		$grammatical_persons['not_specified'] = __('Non spécifié','textmaster');
		$grammatical_persons['first_person_singular'] = __('Je > 1ère personne - Singulier','textmaster');
		$grammatical_persons['second_person_singular'] = __('Tu > 2ème Personne - Singulier','textmaster');
		$grammatical_persons['third_person_singular_masculine'] = __('Il > 3ème personne - Singulier Masculin','textmaster');
		$grammatical_persons['third_person_singular_feminine'] = __('Elle > 3ème personne - Singulier Féminin','textmaster');
		$grammatical_persons['third_person_singular_neuter'] = __('On > 3ème personne - Singulier Neutre','textmaster');
		$grammatical_persons['first_person_plural'] = __('Nous > 1ère personne - Pluriel','textmaster');
		$grammatical_persons['second_person_plural'] = __('Vous > 2ème Personne - Pluriel','textmaster');
		$grammatical_persons['third_person_plural'] = __('Ils/elles > 3ème Personne - Pluriel','textmaster');

		return $grammatical_persons;
	}

	public static function getTargetReaderGroups(){
		$target_reader_groups['not_specified'] = __('Non spécifié','textmaster');
		$target_reader_groups['children'] = __('Enfants > 13 ans et moins','textmaster');
		$target_reader_groups['teenager'] = __('Adolescent > entre 14 et 18 ans','textmaster');
		$target_reader_groups['young_adults'] = __('Jeunes adultes > entre 19 et 29 ans','textmaster');
		$target_reader_groups['adults'] = __('Adultes > entre 30 et 59 ans','textmaster');
		$target_reader_groups['old_adults'] = __('Séniors > 60 ans et plus','textmaster');

		return $target_reader_groups;
	}

	/*
	   * pour avoir la liste des langues sur textmaster
	*/
	function getLanguages(){
		global $wpdb;

		if ($_SESSION['lastSyncTmLangues'] == '' ||  time() - $_SESSION['lastSyncTmLangues'] > $this->dureeResyncro)
		{
			$ch = $this->init();
			$url = $this->urlAPiPublic . '/languages';

			$infosUserTM = $this->getUserInfos();

			if (!is_array($infosUserTM))
			{
				$infosUserTM = array();
				$infosUserTM['locale'] = str_replace('_', '-', WPLANG);
			}


			$url .= '?locale='. $infosUserTM['locale'];

			curl_setopt($ch, CURLOPT_URL, $url );
			$result = curl_exec($ch);
			$resultInfos = curl_getinfo($ch);

			// timeout api
			if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED)
			{
				$this->mailAlertSupport($url, FALSE, $resultInfos);
				die('Error API timeout (Languages)!');
			}


			// les api ne sont pas dispo
			if($resultInfos['http_code'] === 0) {
				$this->mailAlertSupport($url, TRUE, $resultInfos);
				die('Error API connect (Languages)!');
			}
			// code d'erreur HTTP
			if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
				$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
				$this->mailAlertErrHttp($url, $result);
			}

			curl_close($ch);
			$arrayLangs = json_decode($result, TRUE);
			$this->trierArray($arrayLangs['languages']);

			$this->syncLanguages($arrayLangs['languages']);
		}
		else
		{
			$table_langues =  $wpdb->prefix .'tm_langues';
			$reqLangs = $wpdb->prepare('SELECT * FROM '.$table_langues, '');
			$arrayLangs['languages'] = $wpdb->get_results($reqLangs, ARRAY_A);
		}

		return $arrayLangs['languages'];
	}

	function syncLanguages($arrayLangs){
		global $wpdb;

		$table_langues =  $wpdb->prefix .'tm_langues';
		if (count($arrayLangs) != 0) {
			$wpdb->query('DELETE FROM '.$table_langues);
			foreach ($arrayLangs as $langue) {
				$reqChk = $wpdb->prepare('SELECT value FROM '.$table_langues.' WHERE code = "%s"', $langue['code']);
				$chk = $wpdb->get_var( $reqChk);
				if ($chk == '') {
					$sql = 'INSERT INTO '.$table_langues.' (code, value) VALUES (%s, %s)';
					$req = $wpdb->prepare($sql, $langue['code'], $langue['value']);
				}
				else if ($chk != $categorie['value']){
					$sql = 'UPDATE '.$table_langues.' SET value=%s WHERE code=%s)';
					$req = $wpdb->prepare($sql,  $langue['value'], $langue['code']);
				}

				$wpdb->query($req);
			}
			$_SESSION['lastSyncTmLangues'] = time();
		}
	}

	/*
	* pour avoir la liste des mise en page
	*/
	function getTemplates(){
		$ch = $this->init();
		$url = $this->urlAPiClients . '/work_templates';

		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED)
		{
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API timeout (Templates)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (Templates)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}
		curl_close($ch);

		$datas = json_decode($result, TRUE);

		return $datas['work_templates'];
	}

	/* pour recup la liste des auteurs */
	function getAuteurs($status='',$arrayProjet = array())
	{

		$status = 'my_textmaster';
		$ret = '';

		$ch = $this->init();
		$url = $this->urlAPiClients . '/my_authors';
		if ($status != '')
			$url .= '?status='.$status;

		if (count($arrayProjet) != 0) {
		//	$project = json_encode($arrayProjet);
			$strProjet = '';
			foreach ($arrayProjet as $key => $projet) {
				if (is_array($projet)) {
					foreach ($projet as $keyOption => $option)
						$strProjet .= '&project['.$key.']['.$keyOption.']='.$option;
				}
				else
					$strProjet .= '&project['.$key.']='.$projet;
			}
			$url .= $strProjet;
		}
//		echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);

		$datas = json_decode($result, TRUE);
	//	print_r($datas);

//		$resultInfos = curl_getinfo($ch);
	//	print_r($resultInfos);

		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (authors)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (authors)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

	//	echo $result;

		curl_close($ch);

		if (is_array($datas) && key_exists('my_authors', $datas) && is_array($datas['my_authors']))
			array_unshift($datas['my_authors'], array('id' => '', 'author_id' => '', 'status' => 'my_textmaster', 'author_ref' => __('Non spécifié','textmaster')));

		if (is_array($datas) && key_exists('my_authors', $datas))
			$ret = $datas['my_authors'];

		return $ret;
	}

	/*
	*   récuperer le prix en fonction du nombre de mots
	*/
	function getPricings($word_count){
		$ch = $this->init();
		$url = $this->urlAPiPublic . '/reference_pricings';
		$infosUserTM = $this->getUserInfos();
		$url .= '/'. $infosUserTM['locale'];
		$url .= '?word_count='.$word_count;
//		echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (Pricings)!');
		}


		$resultInfos = curl_getinfo($ch);
		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (Pricings)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);
		$aPricings = json_decode($result, TRUE);
	//	print_r($aPricings);

		return $aPricings;
	}
	/*
	* créer un nouveau projet sur textmaster
	* type : copywriting, translation, proofreading
	*/
	function makeProject($name, $type, $language_from='fr',  $language_to='fr', $category, $project_briefing, $language_level, $quality  = 'false', $expertise = 'false', $priority = 'false', $work_template='Default', $vocabulary_type ='not_specified', $grammatical_person ='not_specified', $target_reader_groups = 'not_specified',$authors = ''){

		$name = str_replace('&raquo;', '"',$name);
		$name = str_replace('&laquo;', '"',$name);
		$name = str_replace('&nbsp;', ' ',$name);
		$project['name'] = str_replace('&lsquo;', "'",$name);

		$project['ctype'] = $type;

		// pour les trads et relectures on force le template
		if ($type != 'copywriting')
			$work_template = '1_title_1_paragraph';

		$project['language_from'] = $language_from;
		$project['language_to'] = $language_to;
		$project['category'] = $category;
		$project['project_briefing'] = $project_briefing;
		$project['same_author_must_do_entire_project'] = 'false';
		$project['options']['language_level'] = $language_level;
		$project['options']['quality'] = $quality;
		$project['options']['expertise'] = $expertise;
		$project['options']['priority'] = $priority;

		if ($work_template == '')
			$work_template='Default';
		$project['work_template'] = $work_template;
		if ($vocabulary_type != '')
			$project['vocabulary_type'] = $vocabulary_type;
		if ($grammatical_person != '')
			$project['grammatical_person'] = $grammatical_person;
		if ($target_reader_groups != '')
			$project['target_reader_groups'] = $target_reader_groups;

		if (is_array($authors) && count($authors) != 0 && $authors[0] != '' )
		{
			$project['textmasters'] = $authors;
		}


	//	echo json_encode(array('project' => $project, 'tracker' => '504eefc88e36150002000002'));
	//	print_r($project);
	//	$project['custom_client'] = "{tracker_id: '4f1db74529e1673829000009', token_id: '504eefc88e36150002000002'}";
		$ch = $this->init();


		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('project' => $project, 'tracker' => '504eefc88e36150002000002'))); // , JSON_HEX_APOS | JSON_HEX_QUOT
		$url = $this->urlAPiClients . '/projects';
	//	echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch),TRUE);
//		print_r($result);
		$resultInfos = curl_getinfo($ch);
//		print_r($resultInfos);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (make project)!');
		}

		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (make project)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
		{
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result, print_r( $project, TRUE));
		}


		curl_close($ch);
		return $result;
	}

	/*
	* Lancer le projet
	*/
	function launchProject($idProjet){

		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects/'.$idProjet.'/launch';

	//	echo $url;
		curl_setopt($ch, CURLOPT_INFILESIZE, 0);
		curl_setopt($ch, CURLOPT_PUT, true);

		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (launch project)!');
		}



		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (launch project)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);

		return $result;
	}

	/*
	* Pour recuperer les infos d'un projet
	*/
	function getProjetInfos($idProjet){

		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects/'.$idProjet;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (infos project)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (infos project)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);

		return $result;
	}

	function getProjetStatus($idProjet){
		$ret = '';
		$result = $this->getProjetInfos($idProjet);
//		print_r($result);
		if (is_array($result) && array_key_exists('status',$result) )
			$ret = $result['status'];
		return $ret;
	}


	public static function getLibStatus($status){
		switch ($status) {
			case 'waiting_assignment':
				$ret =  __('En attente','textmaster' );
				break;
			case 'in_creation':
				$ret =  __('En attente','textmaster' ).' - '.  __('Incomplet','textmaster' );
				break;
			case 'in_progress':
				$ret =  __('En cours','textmaster' );
				break;
			case 'quality_control':
				$ret =  __('Controle qualité','textmaster' );
				break;
			case 'in_review':
				$ret =  __('A valider','textmaster' );
				break;
			case 'incomplete':
				$ret =  __('Incomplet','textmaster' );
				break;
			case 'completed':
				$ret =  __('Terminé','textmaster' );
				break;
			case 'paused':
				$ret =  __('En pause','textmaster' );
				break;
			case 'canceled':
				$ret =  __('Annulé','textmaster' );
				break;
			default:
				$ret =  __('NC','textmaster' );
				break;
		}

		return $ret;
	}

	/*
	* Pour recuperer la liste des projets
	*/
	function getProjectList($status = '', $archived = FALSE, $page = '', $per_page = ''){
		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects';
		if ($status != '')
			$url .= '?status='.$status;
		if (!$archived) {
			if (strpos($url, '?' ) === FALSE)
				$url .= '?archived=false';
			else
				$url .= '&archived=false';
		}
		if ($page != '' ) {
			if (strpos($url, '?' ) === FALSE)
				$url .= '?page='.$page;
			else
				$url .= '&page='.$page;
		}
		if ($per_page != '') {
			if (strpos($url, '?' ) === FALSE)
				$url .= '?per_page='.$per_page;
			else
				$url .= '&per_page='.$per_page;
		}
//		echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (list project)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (list project)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);

		return $result;
	}

	function addDocument($idProjet, $title, $word_count, $original_content='', $word_count_rule ='1', $keyword_list = '', $keywords_repeat_count = ''){

		if (trim($original_content) != '' && $word_count == 0)
			$word_count = 1;


		$title = str_replace('&raquo;', '"',$title);
		$title = str_replace('&laquo;', '"',$title);
		$title = str_replace('&nbsp;', ' ',$title);
		$document['title'] = str_replace('&lsquo;', "'",$title);
		$document['word_count'] = $word_count;

//		$content = $document['title']."\n\n".$original_content;

		$document['original_content'] = $original_content;
		$document['word_count_rule'] = $word_count_rule;

		if (trim($keyword_list) != '') {
			$document['keyword_list'] = $keyword_list;
			$document['keywords_repeat_count'] = $keywords_repeat_count;
		}

	//	$document['custom_client'] = "{tracker_id: '4f1db74529e1673829000009'}";
//		print_r( $document);

		$ch = $this->init();

		curl_setopt($ch, CURLOPT_POST, 1);
		if (PHP_VERSION_ID > 50300) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('document' => $document), JSON_HEX_APOS | JSON_HEX_QUOT ));
		}
	 	else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('document' => $document)));
		}
		$url = $this->urlAPiClients . '/projects/'.$idProjet.'/documents';
//		echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch),TRUE);
//		print_r( $result);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (add document)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (add document)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
		{
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result, print_r( $document, TRUE));
		}


		curl_close($ch);
		return $result;
	}

	function delDocument($idProjet, $idDocument)
	{
		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects/'.$idProjet.'/documents/'.$idDocument;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (del document)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (del document)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);

		return $result;
	}

	/*
	   * Pour recuperer les infos d'un document
	*/
	function getDocumentInfos($idProjet, $idDocument){

		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects/'.$idProjet.'/documents/'.$idDocument;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (infos document)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (infos document)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);

		return $result;
	}

	function getDocumentStatus($idProjet, $idDocument){
		$ret = '';
		$result = $this->getDocumentInfos($idProjet, $idDocument);
		//		print_r($result);
		if (is_array($result) && array_key_exists('status',$result) )
			$ret = $result['status'];
		return $ret;
	}

	/*
	   * Pour recuperer la liste des docs
	*/
	function getDocumentList($idProjet){
		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects/'.$idProjet.'/documents';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (list document)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (list document)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}

		curl_close($ch);
//		print_r($result);
		return $result;
	}

	/*
	* Pour valider un document
	*/
	function valideDoc($idProjet, $idDocument){

		$ch = $this->init();
		$url = $this->urlAPiClients . '/projects/'.$idProjet.'/documents/'.$idDocument.'/complete';
		curl_setopt($ch, CURLOPT_INFILESIZE, 0);
		curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_URL, $url );

/*		curl_setopt($ch, CURLOPT_POST, 1);
		if (PHP_VERSION_ID > 50300) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('document' => ''), JSON_HEX_APOS | JSON_HEX_QUOT ));
		}
		else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('document' => '')));
		}*/

		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE,  $resultInfos);
			die('Error API timeout (valid document)!');
		}


		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (valid document)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error (valid doc) '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}
		curl_close($ch);

		return $result;
	}

	function getUserInfos(){

		$ch = $this->init();
		$url = $this->urlAPiClients . '/users/me';

		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		$resultInfos = curl_getinfo($ch);

		// timeout api
		if(curl_errno($ch) === CURLE_OPERATION_TIMEOUTED){
			$this->mailAlertSupport($url, FALSE, $resultInfos);
			die('Error API timeout (infos user)!');
		}



		// les api ne sont pas dispo
		if($resultInfos['http_code'] === 0) {
			$this->mailAlertSupport($url, TRUE, $resultInfos);
			die('Error API connect (infos user)!');
		}
		// code d'erreur HTTP
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300) {
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';
			$this->mailAlertErrHttp($url, $result);
		}
		curl_close($ch);

		return $result;
	}

	public static function countWords($string){

		$string = trim($string);
		$string = str_replace('&', "&amp;", $string);
		$string = str_replace('«', "", $string);
		$string = str_replace('»', "", $string);
		$string = str_replace('(', "", $string);
		$string = str_replace(')', "", $string);


		$string = preg_replace('/[^[\pL\pN]\s]*/ui',' ', $string);
//		$string = preg_replace('/[^[[:alnum:]]\s]*/ui',' ', $string);

		$string = preg_replace('!\s+!u', ' ', $string);
		//		$string = preg_replace("/[^a-zA-Z0-9]+/u", " ", $string);
//		echo $string;
//		$countWords = preg_match_all ('/[[:alnum:]]+/ui', $string, $matches);
//		$countWords = preg_match_all ('/[\pL\pN]+/ui', $string, $matches);
		$countWords = preg_match_all ('/[\\p{Zs}]+/ui', $string, $matches);
		//print_r($matches);
//		echo 'nb mots :'.$countWords.'<br>';

		return $countWords+1;
	}

	function mailAlertSupport($url, $force = FALSE, $infosCurl){
		if (MAIL_ALERT_ENABLE == TRUE || $force) {
			$to      = MAIL_ALERTE_SUPPORT;
			$subject = 'Alerte Plugin WordPress API (Timeout)';
			$message = "Le plugin WordPress n'a pas pu se connecter aux API TextMaster\n";
			if (!$force)
				$message .= "Timeout : ". TIMEOUT_API."\n";
			$message .= "Requete API : ". $url."\n";
			$message .= "URL du site : ". get_site_url()."\n";
			$message .= "Retour cURL : ". print_r($infosCurl,TRUE)."\n";
			$headers = 'From: '.MAIL_ALERTE_SUPPORT . "\r\n" .
			    'Reply-To: ' .MAIL_ALERTE_SUPPORT. "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			mail($to, $subject, $message, $headers);
		}
	}

	function mailAlertErrHttp($url, $msg, $paramsSend = ''){
		if (MAIL_ALERT_HTTP_ENABLE == TRUE) {
			$to      = MAIL_ALERTE_SUPPORT_HTTP;
			$subject = 'Alerte Plugin WordPress API (Erreur HTTP)';
			$message = "Plugin WordPress: Erreur HTTP API TextMaster\n";
			$message .= "Requete API : ". $url."\n";
			$message .= "URL du site : ". get_site_url()."\n";
			$message .= "Erreur retourner : ". $msg."\n";
			if ($paramsSend != '') {
				$message .= "Paramètres envoyés : ". $paramsSend."\n";
			}

			$headers = 'From: '.MAIL_ALERTE_SUPPORT_HTTP . "\r\n" .
			    'Reply-To: ' .MAIL_ALERTE_SUPPORT_HTTP. "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			mail($to, $subject, $message, $headers);
		}
	}
}

?>
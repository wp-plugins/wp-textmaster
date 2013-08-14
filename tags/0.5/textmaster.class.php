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

	private  $urlAPi = 'http://api.textmaster.com/beta/clients';

	function __construct(){

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
//		curl_setopt($ch, CURLOPT_HEADER , true);
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

		$headers = array('APIKEY: '.$this->keyapi,'DATE: '.$date, 'SIGNATURE: '.$signature, 'Content-Type: application/json', 'Accept: application/json');

		return $headers;
	}

	function testAuth()
	{
		$ch = $this->init();
		$url = 'http://api.textmaster.com/test';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);

//		print_r($result);

	}

	/*
	* pour avoir la liste des catégories sur textmaster
	*/
	function getCategories(){
		$ch = $this->init();
		$url = $this->urlAPi . '/categories';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, TRUE);
	}

	/*
	   * pour avoir la liste des niveaux de langages sur textmaster
	*/
	function getLanguageLevels(){
		$ch = $this->init();
		$url = $this->urlAPi . '/language_levels';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, TRUE);
	}

	/*
	   * pour avoir la liste des catégories sur textmaster
	*/
	function getLanguages(){
		$ch = $this->init();
		$url = $this->urlAPi . '/languages';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);
		$arrayLangs = json_decode($result, TRUE);
		asort($arrayLangs);

		return $arrayLangs;
	}

	/*
	* pour avoir la liste des mise en page
	*/
	function getTemplates(){
		$ch = $this->init();
		$url = $this->urlAPi . '/work_templates';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);

		$datas = json_decode($result, TRUE);

		return $datas['work_templates'];
	}

	/* pour recup la liste des auteurs */
	function getAuteurs()
	{
		$ch = $this->init();
		$url = $this->urlAPi . '/my_authors';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);

		$datas = json_decode($result, TRUE);
		array_unshift($datas['my_authors'], array('id' => '', 'author_ref' => __('Non spécifié','textmaster')));

		return $datas['my_authors'];
	}

	/*
	* créer un nouveau projet sur textmaster
	* type : copywriting, translation, proofreading
	*/
	function makeProject($name, $type, $language_from='fr',  $language_to='fr', $category, $project_briefing, $language_level, $work_template='Default', $vocabulary_type ='not_specified', $grammatical_person ='not_specified', $target_reader_groups = 'not_specified',$authors = ''){

		$name = str_replace('&raquo;', '"',$name);
		$name = str_replace('&laquo;', '"',$name);
		$name = str_replace('&nbsp;', ' ',$name);
		$project['name'] = str_replace('&lsquo;', "'",$name);

		$project['ctype'] = $type;
		$project['language_from'] = $language_from;
		$project['language_to'] = $language_to;
		$project['category'] = $category;
		$project['project_briefing'] = $project_briefing;
		$project['same_author_must_do_entire_project'] = 'false';
		$project['language_level'] = $language_level;
		if ($work_template != '')
			$work_template='Default';
		$project['work_template'] = $work_template;
		$project['vocabulary_type'] = $vocabulary_type;
		$project['grammatical_person'] = $grammatical_person;
		$project['target_reader_groups'] = $target_reader_groups;
		if ($authors != '')
			$project['textmasters'] = $authors;


	//	$project['custom_client'] = "{tracker_id: '4f1db74529e1673829000009', token_id: '504eefc88e36150002000002'}";
		$ch = $this->init();

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('project' => $project, 'tracker' => '504eefc88e36150002000002'))); // , JSON_HEX_APOS | JSON_HEX_QUOT
		$url = $this->urlAPi . '/projects';
	//	echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch),TRUE);

		$resultInfos = curl_getinfo($ch);

		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
			$result = 'Error '.$resultInfos['http_code'].' - '.print_r($result, TRUE).'';

		curl_close($ch);
		return $result;
	}

	/*
	* Lancer le projet
	*/
	function launchProject($idProjet){

		$ch = $this->init();
		$url = $this->urlAPi . '/projects/'.$idProjet.'/launch';

	//	echo $url;
		curl_setopt($ch, CURLOPT_INFILESIZE, 0);
		curl_setopt($ch, CURLOPT_PUT, true);

		curl_setopt($ch, CURLOPT_URL, $url );
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	/*
	* Pour recuperer les infos d'un projet
	*/
	function getProjetInfos($idProjet){

		$ch = $this->init();
		$url = $this->urlAPi . '/projects/'.$idProjet;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		curl_close($ch);

		return $result;
	}

	function getProjetStatus($idProjet){
		$result = $this->getProjetInfos($idProjet);

		return  $result['projects']['status'];
	}

	function getLibStatus($status){
		switch ($status) {
			case 'waiting_assignment':
				$ret =  __('En attente','textmaster' );
				break;
			case 'in_progress':
				$ret =  __('En cours','textmaster' );
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
				break;
		}

		return $ret;
	}

	/*
	* Pour recuperer la liste des projets
	*/
	function getProjectList(){
		$ch = $this->init();
		$url = $this->urlAPi . '/projects';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		curl_close($ch);

		return $result;
	}

	function addDocument($idProjet, $title, $word_count, $original_content='', $word_count_rule ='1', $keyword_list = '', $keywords_repeat_count = ''){

		$title = str_replace('&raquo;', '"',$title);
		$title = str_replace('&laquo;', '"',$title);
		$title = str_replace('&nbsp;', ' ',$title);
		$document['title'] = str_replace('&lsquo;', "'",$title);
		$document['word_count'] = $word_count;
		$document['original_content'] = $original_content;
		$document['word_count_rule'] = $word_count_rule;
		if (trim($document['keyword_list']) != '') {
			$document['keyword_list'] = $keyword_list;
			$document['keywords_repeat_count'] = $keywords_repeat_count;
		}
	//	$document['custom_client'] = "{tracker_id: '4f1db74529e1673829000009'}";
		//print_r( $document);

		$ch = $this->init();

		curl_setopt($ch, CURLOPT_POST, 1);
		if (PHP_VERSION_ID > 50300) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('document' => $document), JSON_HEX_APOS | JSON_HEX_QUOT ));
		}
	 	else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('document' => $document)));
		}
		$url = $this->urlAPi . '/projects/'.$idProjet.'/documents';
	//	echo $url;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch),TRUE);

	//	print_r( $result);

		$resultInfos = curl_getinfo($ch);
		if(curl_errno($ch) || $resultInfos['http_code'] >= 300)
			$result = 'Error '.$resultInfos['http_code'];

		curl_close($ch);
		return $result;
	}

	function delDocument($idProjet, $idDocument)
	{
		$ch = $this->init();
		$url = $this->urlAPi . '/projects/'.$idProjet.'/documents/'.$idDocument;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		curl_close($ch);

		return $result;
	}

	/*
	   * Pour recuperer les infos d'un document
	*/
	function getDocumentInfos($idProjet, $idDocument){

		$ch = $this->init();
		$url = $this->urlAPi . '/projects/'.$idProjet.'/documents/'.$idDocument;
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		curl_close($ch);

		return $result;
	}

	/*
	   * Pour recuperer la liste des docs
	*/
	function getDocumentList($idProjet){
		$ch = $this->init();
		$url = $this->urlAPi . '/projects/'.$idProjet.'/documents';
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		curl_close($ch);

		return $result;
	}

	/*
	* Pour valider un document
	*/
	function valideDoc($idProjet, $idDocument){

		$ch = $this->init();
		$url = $this->urlAPi . '/projects/'.$idProjet.'/documents/'.$idDocument.'/complete';
		curl_setopt($ch, CURLOPT_INFILESIZE, 0);
		curl_setopt($ch, CURLOPT_PUT, true);
		curl_setopt($ch, CURLOPT_URL, $url );
		$result = json_decode(curl_exec($ch), TRUE);
		curl_close($ch);

		return $result;
	}
}

?>
<?php

// The Event Location Metabox
function wp_texmaster_readproof_metaboxes() {
	global $post;

	$result ='';

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
	$idDocument = get_post_meta($post->ID,'textmasterDocumentIdTrad', TRUE);

//	if ($idDocument != '')
//		$result = $tApi->getDocumentStatus($idProjet, $idDocument);
//	elseif ($idProjet != '')
//		$result = $tApi->getProjetStatus($idProjet);
	// la trad n'est pas faite chez TM
//	if ($result != 'in_review')
		readproof_metaboxes_pre($tApi);
//	else
//		metaboxes_post($tApi, 'readproof', $idProjet, $idDocument);

}

function readproof_metaboxes_pre(&$tApi){
	global $post;

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//$tApi->keyapi =  get_option_tm('textmaster_api_key');
	$style = '';
	if (get_option_tm('textmaster_useTraduction') == 'Y' && get_option_tm('textmaster_useReadproof') == 'Y')
		$style = 'style="display:none"';
	echo '<div id="meta_readproof" '.$style.'>';

	$userTM = $tApi->getUserInfos();

	if ($tApi->secretapi == '' && $tApi->keyapi == ''){
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else if (isset($userTM['errors'])) {
		_e('Merci de v&eacute;rifier le paramétrage de votre serveur (date et heure du serveur : '.date('d/m/Y H:i:s').')','textmaster');
	}
	else
	{
		$textmaster_email = get_option_tm('textmaster_email');
		$textmaster_password = get_option_tm('textmaster_password');
		if ($textmaster_password != '' && $textmaster_email != '') {
			$oOAuth = new TextMaster_OAuth2();
			$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
			$infosUser = $oOAuth->getUserInfos($token);
			$lang = explode('_', get_locale());
			$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE.'?auth_token='.$infosUser['authentication_token'];
			$infosClient = $tApi->getUserInfos();
		}

		$categories = $tApi->getCategories();
		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCat" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorie', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorie', true);
		else
			$catSelected = get_option_tm('textmaster_readproofCategorie');


		if ($catSelected == '' || !isset($catSelected))
			$catSelected = CATEGORIE_DEFAUT;

		foreach($categories as $categorie)
		{
			if ($catSelected == $categorie['code'])
				echo '<option value="'.$categorie['code'].'" selected="selected">'.$categorie['value'].'</option>';
			else
				echo '<option value="'.$categorie['code'].'">'.$categorie['value'].'</option>';
		}
		echo '</select><br/><br/>';

		$languageLevels = $tApi->getLanguageLevels();

		echo '<label>'.__('Niveau de service:','textmaster').'</label>';
		echo '<select id="select_textmasterReadProofLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorieReadProofLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterReadProofLanguageLevel', true);
		else
			$languageLevelSelected = get_option_tm('textmaster_readproofLanguageLevel');

		foreach($languageLevels['readproof'] as $key => $languageLevel)
		{
			if ($languageLevelSelected == $languageLevel["name"])
				echo '<option value="'.$languageLevel["name"].'" selected="selected">'.$languageLevel["name"].'</option>';
			else
				echo '<option value="'.$languageLevel["name"].'">'.$languageLevel["name"].'</option>';
		}

		echo '</select><br/>';

		echo '<div>'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBaseReadproof">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';

		$languages = $tApi->getLanguages();

		echo '<label>'.__('Langue :','textmaster').'</label><br/>';
		echo '<select id="select_textmasterReadProofLang" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterReadProofLang', true) != '')
			$languageSelected = get_post_meta($post->ID, 'textmasterReadProofLang', true);
		else
			$languageSelected = get_option_tm('textmaster_readproofLanguage');

		foreach($languages as $language)
		{
			if ($languageSelected == $language['code'])
				echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
			else
				echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
		}
		echo '</select><br/><br/>';

		if (get_post_meta($post->ID, 'textmasterQualityReadproof', true) != '')
			$textmaster_qualityRedaction = get_post_meta($post->ID, 'textmasterQualityReadproof', true);
		else
			$textmaster_qualityRedaction = get_option_tm('textmaster_qualityReadproof');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_qualityRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

/*		echo '<label class="options_pricetm">'.__('Contrôle qualité :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityReadProof">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';
		if (get_post_meta($post->ID, 'textmasterExpertiseReadproof', true) != '')
			$textmaster_expertiseRedaction = get_post_meta($post->ID, 'textmasterExpertiseReadproof', true);
		else
			$textmaster_expertiseRedaction = get_option_tm('textmaster_expertiseReadproof');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_expertiseRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';
*/
//		echo '<label class="options_pricetm">'.__('Expertise :','textmaster').'</label> ';
//		echo '<input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
//		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseReadproof">NC</strong> ';
//		if (is_array($infosClient))
//			echo $infosClient['wallet']['currency_code'];
//		echo '</div><br/>';

		if (get_post_meta($post->ID, 'textmaster_priorityReadproof', true) != '')
			$textmaster_priorityRedaction = get_post_meta($post->ID, 'textmasterPriorityReadproof', true);
		else
			$textmaster_priorityRedaction = get_option_tm('textmaster_priorityReadproof');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_priorityRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

		echo '<label class="options_pricetm">'.__('Commande prioritaire :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterPriorityReadproof" class="radio_textmasterPriorityReadproof" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterPriorityReadproof" class="radio_textmasterPriorityReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		if (is_array($infosClient))
			echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriorityReadproof">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';

		if (get_post_meta($post->ID, 'textmaster_BriefingReadproof', true) != '')
			$textmasterBriefing_readproof = get_post_meta($post->ID, 'textmaster_BriefingReadproof', true);
		else
			$textmasterBriefing_readproof = get_option_tm('textmaster_readproofBriefing');

		echo '<label>'.__('Briefing :','textmaster').'</label>';
		echo '<textarea style="width:235px;height:100px;" name="text_textmasterBriefing_readproof" id="text_textmasterBriefing_readproof">'.$textmasterBriefing_readproof.'</textarea><br/>';

		$idProjet = get_post_meta($post->ID,'textmasterId', TRUE);
		$idDocument = get_post_meta($post->ID,'textmasterDocumentId', TRUE);

		$txtRet = '';
		$disabled = '';
		$hide = FALSE;
		if ($idProjet != '') {
			//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits

			if ($idDocument != '')
				$result = $tApi->getDocumentStatus($idProjet, $idDocument);
			else  if ($idProjet != '' && $result == '')
				$result = $tApi->getProjetStatus($idProjet);

			if ($result == 'in_review')
			{
				$txtRet = __('Cet article a &eacute;t&eacute; relu.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la relecture','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=readproof&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				$disabled = 'disabled=disabled';
				$hide = TRUE;
			}
			else if ($result == 'in_progress' || $result == 'waiting_assignment'  ||  $result == 'quality_control')
			{
				$txtRet = __('Cet article est en cours de relecture.','textmaster');
				$disabled = 'disabled=disabled';
				$hide = TRUE;
			}
			else if ($result == 'completed') {
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la relecture de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la relecture','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la relecture','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				$disabled = 'disabled=disabled';
				$hide = TRUE;
			}

		}
		else if ($post->post_content == '')
		{
			$ret = __('Merci de sauvegarder cet article.','textmaster');
			$disabled = 'disabled=disabled';
		}

//		wp_texmaster_readproof_options_metaboxes($tApi);
		wp_texmaster_readproof_authors_metaboxes($tApi);
		if ( checkInstalledPlugin('Meta Box'))
			wp_texmaster_readproof_pmb_metaboxes($tApi);

/*		$contentText = cleanWpTxt( $post->post_content );
   textmaster_api::countWords( $post->post_title .' '.$contentText);
   textmaster_api::countWords( $contentText);
*/
		echo '<img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmReadProof" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmReadProof"> '.__('Merci de patienter', 'textmaster').'</div> <div id="resultTextmaster" class="misc-pub-section">'.$txtRet.'</div>';
		if (!$hide)
			echo '<br/><div id="publishing-action"><input name="save" type="button" class="button button-highlighted" id="readproof" tabindex="5" accesskey="p" value="'.__('Envoyer','textmaster').'" '.$disabled.'></div>';
		echo '<div style="clear:both"></div><br/>';
		echo '<div class="misc-pub-section">'. __('Coût', 'textmaster').' : <strong id="priceTextmasterReadProof">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo ' (<span class="nbMots"></span> '.__('mots','textmaster').')</div><div class="misc-pub-section">'.__('Crédits:', 'textmaster').' <strong>';
		if (is_array($infosClient))
			echo '<span class="walletTextmaster">';
		if(is_array($infosClient))
			echo  $infosClient['wallet']['current_money'];
		echo '</span> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'] ;
		echo '</strong>';
		echo  '<a href="'.$urlAchat.'" target="_blank">'. __('Créditer mon compte', 'textmaster').'</a></div><br/><br/>';
		echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; jQuery(document).ready(function($) {getPrice("readproof");});</script>';

	}

	echo '</div>';
}

function callback_readproof(){
	global $wpdb;

	$categorie = $_POST['categorie'];
	$languageLevel = $_POST['languageLevel'];
	$language = $_POST['language'];
	$postID = $_POST['postID'];
	$authors = '';
	$checkStatut = '';
	if (isset($_POST['authors']))
		$authors = $_POST['authors'];

	$textmaster_qualityRedaction = '';
	if (isset($_POST['quality']))
		$textmaster_qualityRedaction = $_POST['quality'];
	$textmaster_expertiseRedaction = '';
	if (isset($_POST['expertise']))
		$textmaster_expertiseRedaction = $_POST['expertise'];
	$textmaster_priorityRedaction = '';
	if (isset($_POST['priority']))
		$textmaster_priorityRedaction = $_POST['priority'];

	$textmasterBriefing_readproof = $_POST['briefing'];

	$textmasterKeywords_readproof = '';
	if (isset($_REQUEST['keywords']))
		$textmasterKeywords_readproof = $_POST['keywords'];
	$textmasterKeywordsRepeatCount_readproof = '';
	if (isset($_REQUEST['keywordsRepeatCount']))
		$textmasterKeywordsRepeatCount_readproof = $_POST['keywordsRepeatCount'];
	$textmasterVocabularyType_readproof = '';
	if (isset($_REQUEST['vocabularyType']))
		$textmasterVocabularyType_readproof = $_POST['vocabularyType'];
	$textmasterGrammaticalPerson_readproof = '';
	if (isset($_REQUEST['grammaticalPerson']))
		$textmasterGrammaticalPerson_readproof = $_POST['grammaticalPerson'];
	$textmasterTargetReaderGroup_readproof = '';
	if (isset($_REQUEST['targetReaderGroup']))
		$textmasterTargetReaderGroup_readproof = $_POST['targetReaderGroup'];

	update_post_meta($postID, 'textmasterCategorie',$categorie);
	update_post_meta($postID, 'textmasterReadProofLanguageLevel',$languageLevel);
	update_post_meta($postID, 'textmasterReadProofLang',$language);
	update_post_meta($postID, 'textmasterReadProofAuthor', serialize($authors));

	update_post_meta($postID, 'textmaster_qualityReadproof',$textmaster_qualityRedaction);
	update_post_meta($postID, 'textmaster_expertiseReadproof',$textmaster_expertiseRedaction);
	update_post_meta($postID, 'textmaster_priorityReadproof',$textmaster_priorityRedaction);

	update_post_meta($postID, 'textmaster_BriefingReadproof',$textmasterBriefing_readproof);

	if (isset($_REQUEST['keywords']))
		update_post_meta($postID, 'textmasterKeywords_readproof', $_REQUEST['keywords']);
	if (isset($_REQUEST['keywordsRepeatCount']))
		update_post_meta($postID, 'textmasterKeywordsRepeatCount_readproof', $_REQUEST['keywordsRepeatCount']);
	if (isset($_REQUEST['vocabularyType']))
		update_post_meta($postID, 'textmasterVocabularyType_readproof', $_REQUEST['vocabularyType']);
	if (isset($_REQUEST['grammaticalPerson']))
		update_post_meta($postID, 'textmasterGrammaticalPerson_readproof', $_REQUEST['grammaticalPerson']);
	if (isset($_REQUEST['targetReaderGroup']))
		update_post_meta($postID, 'textmasterTargetReaderGroup_readproof', $_REQUEST['targetReaderGroup']);

	$content_post = get_post($postID);
	$content = $content_post->post_content;

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//$tApi->keyapi =  get_option_tm('textmaster_api_key');
	$idProjet = get_post_meta($postID,'textmasterId', TRUE);

	//	echo $idProjet;
	if ($idProjet != '')
		$checkStatut = $tApi->getProjetStatus($idProjet);

	$retProjet = '';
	if ($idProjet == '' || $checkStatut == 'canceled')
	{

		$retProjet = $tApi->makeProject(get_the_title($postID), 'proofreading', $language, $language, $categorie, $textmasterBriefing_readproof, $languageLevel, $textmaster_qualityRedaction, $textmaster_expertiseRedaction, $textmaster_priorityRedaction, '', $textmasterVocabularyType_readproof, $textmasterGrammaticalPerson_readproof, $textmasterTargetReaderGroup_readproof, $authors);
		if (is_array($retProjet))
			$idProjet = $retProjet['id'];
		else
			$ret = __('Erreur lors de la création de votre projet ('.$retProjet.')' ,'textmaster');
	}


	// nouveau projet
	$result = '';
	if (is_array($retProjet) ||  $idProjet != '')
	{
		//	$ret = serialize($ret);
		update_post_meta($postID, 'textmasterId', $idProjet);

		$contentText = cleanWpTxt( $content );
		$fullContent = '';
		// comptaibilité acf
		if( checkInstalledPlugin('Advanced Custom Fields')&& isset($_POST['extras']) ) {
			//	var_dump($_POST);
			$params = array();
			parse_str($_POST['extras'], $params);
			if (count($params["fields"]) != 0) {
				foreach ( $params["fields"] as $name => $param) {
					if ($name != 'acf_nonce' && trim($param) != ''){
						$arrayDocs[0]['original_content'][$name]["original_phrase"] = $param;
						$fullContent .= cleanWpTxt( $param );
					}

				}
			}

			$arrayDocs[0]['original_content']['content']["title"] = get_the_title($postID);
			$arrayDocs[0]['original_content']['content']["original_phrase"] = $content;
			if ($content_post->post_excerpt != ''){
				$arrayDocs[0]['original_content']["post_excerpt"]["original_phrase"]  = $content_post->post_excerpt;
				$fullContent .= cleanWpTxt( $content_post->post_excerpt );
			}
			$nbMots = $tApi->countWords( get_the_title($postID) .' '.$contentText.' '.$fullContent);	//		var_dump($arrayDocs);
			//		echo $nbMots;
			//		die();
		}
		if(  checkInstalledPlugin('Meta Box')&& isset($_POST['extras'])) {
			//	var_dump($_POST);
		//	$chk_tm_mb_feilds = unserialize(get_option_tm('chk_tm_mb_feilds'));
			$chk_tm_mb_feilds = array();
			$chk_tm_mb = array();
			parse_str($_POST['filtre_pmb'], $chk_tm_mb);
			if (isset($chk_tm_mb['chk_tm_mb_feilds']))
				$chk_tm_mb_feilds = $chk_tm_mb['chk_tm_mb_feilds'];

			$params = array();
			parse_str($_POST['extras'], $params);
			if (count($params) != 0) {
				foreach ( $params as $name => $param) {
					if (!in_array($name, $chk_tm_mb_feilds)){
						if (is_string($param) && trim($param) != ''){
							$arrayDocs[0]['original_content'][$name]["original_phrase"] = $param;
							$fullContent .= cleanWpTxt( $param );
						} else if (is_array($param)) {
							foreach ($param as $key => $text) {
								$arrayDocs[0]['original_content'][$name.'_tmtext_'.$key]["original_phrase"] = $text;
								$fullContent .= cleanWpTxt( $text );
							}
						}
					}
				}
			}

			$arrayDocs[0]['original_content']['content']["title"] = get_the_title($postID);
			$arrayDocs[0]['original_content']['content']["original_phrase"] = $content;
			if ($content_post->post_excerpt != ''){
				$arrayDocs[0]['original_content']["post_excerpt"]["original_phrase"]  = $content_post->post_excerpt;
				$fullContent .= cleanWpTxt( $content_post->post_excerpt );
			}
			$nbMots = $tApi->countWords( get_the_title($postID) .' '.$contentText.' '.$fullContent);
			//		var_dump($arrayDocs);
			//		echo $nbMots;
			//		die();
		}
		if( !checkInstalledPlugin('Advanced Custom Fields') && !checkInstalledPlugin('Meta Box')) {
			if ($content_post->post_excerpt != ''){
				$arrayDocs[0]['original_content']['content']["title"] = get_the_title($postID);
				$arrayDocs[0]['original_content']['content']["original_phrase"] = $content;
				$arrayDocs[0]['original_content']["post_excerpt"]["original_phrase"]  = $content_post->post_excerpt;
				$contentText = cleanWpTxt( $content_post->post_excerpt ).' '.cleanWpTxt( $content );
			}
			else
				$arrayDocs[0]['original_content'] = $content;

			$nbMots = $tApi->countWords( get_the_title($postID) .' '.$contentText);
		}
		//$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
		$nbMots = $tApi->countWords( get_the_title($postID) .' '.$contentText);
		$arrayDocs[0]['title'] = get_the_title($postID) ;
		$arrayDocs[0]['word_count'] = $nbMots;
//		$arrayDocs[0]['original_content'] = $content;
		$arrayDocs[0]['word_count_rule'] = 1;
		$arrayDocs[0]['keyword_list'] = $textmasterKeywords_readproof;
		$arrayDocs[0]['keywords_repeat_count'] = $textmasterKeywordsRepeatCount_readproof;
		$ret = $tApi->addDocument($idProjet, $arrayDocs );

//		print_r($ret);
		if (is_array($ret))
		{
			if (array_key_exists('id',$ret)){
				update_post_meta($postID, 'textmasterDocumentId', $ret['id']);
				$idDocument =$ret['id'];
			}

			else
				$result = 'error_doc';
		}
		else
			$result = 'error_doc';
	}

	if ($result != 'error_doc')
		$result = $tApi->getProjetStatus($idProjet);

	if ($result == 'paused' || $result == 'in_creation') {
		$retLaunch = $tApi->launchProject($idProjet);
		$jsonErr = trim(substr($retLaunch, (strpos($retLaunch, '-') +1 )));
	//	print_r($jsonErr);
		$errs = json_decode($jsonErr, TRUE);

		if (is_array($errs) && array_key_exists('errors',$errs))
		{
			if (is_array($errs['errors']) && array_key_exists('credits',$errs['errors']))
				$ret = __('Error').$errs['errors']['credits'][0];
			else
				$ret = __('Error').$errs['errors']['status'][0];
		}
		else if (strpos($retLaunch, 'Error') === FALSE)
		{
			$result = $tApi->getProjetStatus($idProjet);
			update_post_meta($postID, 'textmasterStatusReadproof', $result);
			$ret = __('La relecture de cet article est lancée.','textmaster');
			wp_schedule_single_event( time() + 1, 'cron_syncProjets');
			wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('waiting_assignment',1 ) );
			//syncProjets('in_progress');
		//	syncProjets('in_creation');
			wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('in_creation',1 ) );

		}
		else
			$ret = $retLaunch;
	}
	else if ($result == 'in_progress' ) {
		$ret = __('Cet article est déjà en cours de relecture.','textmaster');
	}
	else if ($result == 'completed' ) {
		$ret = __('La relecture de cet article est terminée.','textmaster');
	}

	if (strpos($ret, __('Error')) !== FALSE)
		echo '<br><div class="error">'.$ret.'</div>';
	else
		echo '<br><div class="updated">'.$ret.'</div>';
	die();
}

function wp_texmaster_readproof_options_metaboxes(&$tApi){
	global $post;


//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//$tApi->keyapi =  get_option_tm('textmaster_api_key');


	if ($tApi->secretapi == '' && $tApi->keyapi == ''){
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showOptionsReadproof"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Plus d\'options' ,'textmaster').'</a><br/>';
		echo '<div id="optionsReadproof">';
		echo '<label class="options_pricetm">'.__('Mots-clés:','textmaster').' ('.__('séparés par des virgules','textmaster').')</label>';
		echo '<textarea style="width:235px;" name="text_textmasterKeywords_readproof" id="text_textmasterKeywords_readproof">'.get_post_meta($post->ID, 'textmasterKeywords_readproof', true).'</textarea><br/>';

		if (get_post_meta($post->ID, 'textmasterKeywordsRepeatCount_readproof', true) != '')
			$KeywordsRepeatCountSelected = get_post_meta($post->ID, 'textmasterKeywordsRepeatCount_readproof', true);
		else
			$KeywordsRepeatCountSelected = 1;
		_e('A répéter :','textmaster');
		echo '<input type="text" id="text_textmasterKeywordsRepeatCount_readproof" name="text_textmasterKeywordsRepeatCount_readproof" style="width:50px;text-align:right;" value="'.$KeywordsRepeatCountSelected.'" />'. __('fois','textmaster').'<br/><br/>';

		echo '<label class="options_pricetm">'.__('Type de Vocabulaire:','textmaster').'</label>';
		echo '<select id="select_textmasterVocabularyType_readproof" name="select_textmasterVocabularyType_readproof" style="width:235px;"><br/><br/>';

		if (get_post_meta($post->ID, 'textmasterVocabularyType_readproof', true) != '')
			$vocabulary_typeSelected = get_post_meta($post->ID, 'textmasterVocabularyType_readproof', true);
		else
			$vocabulary_typeSelected = get_option_tm('textmaster_readproofVocabularyType');

		$vocabulary_types = $tApi->getVocabularyTypes();
		foreach($vocabulary_types as $key => $vocabulary_type)
		{
			if ($vocabulary_typeSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
			else
				echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
		}
		echo '</select><br/><br/>';



		echo '<label class="options_pricetm">'.__('Personne grammaticale:','textmaster').'</label>';
		echo '<select id="select_textmasterGrammaticalPerson_readproof" name="select_textmasterGrammaticalPerson_readproof" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterGrammaticalPerson_readproof', true) != '')
			$grammatical_personSelected = get_post_meta($post->ID, 'textmasterGrammaticalPerson_readproof', true);
		else
			$grammatical_personSelected = get_option_tm('textmaster_readproofGrammaticalPerson');

		$grammatical_persons = $tApi->getGrammaticalPersons();
		foreach($grammatical_persons as $key => $grammatical_person)
		{
			if ($grammatical_personSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
			else
				echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
		}
		echo '</select><br/><br/>';



		echo '<label class="options_pricetm">'.__('Public Ciblé:','textmaster').'</label>';
		echo '<select id="select_textmasterTargetReaderGroup_readproof" name="select_textmasterTargetReaderGroup_readproof" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTargetReaderGroup_readproof', true) != '')
			$target_reader_groupSelected = get_post_meta($post->ID, 'textmasterTargetReaderGroup_readproof', true);
		else
			$target_reader_groupSelected = get_option_tm('textmaster_readproofTargetReaderGroup');

		$target_reader_groups = $tApi->getTargetReaderGroups();
		foreach($target_reader_groups as $key => $target_reader_group)
		{
			if ($target_reader_groupSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$target_reader_group.'</option>';
			else
				echo '<option value="'.$key.'">'.$target_reader_group.'</option>';
		}
		echo '</select><br/><br/>';
		echo '</div>';
	}

}

function wp_texmaster_readproof_authors_metaboxes(&$tApi){
	global $post;

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showAuthorsReadproof"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Vos auteurs' ,'textmaster').'</a><br/>';
		echo '<div id="authorsReadproof">';

		echo '<ul style="display:inline-block;">';
		if (is_object($post) && get_post_meta($post->ID, 'textmasterReadProofAuthor', true) != '')
			$auteurSelected = unserialize( get_post_meta($post->ID, 'textmasterReadProofAuthor', true));
		else
			$auteurSelected = array(get_option_tm('textmaster_authorReadproof'));

		foreach($auteurs as $auteur)
		{
			$auteurDesc = '';
			if (is_array($auteur) && isset($auteur['description']))
				$auteurDesc = ' - '.$auteur['description'];

			echo '<li>';
			if ( in_array($auteur['id'], $auteurSelected) )
				$checked = 'checked="checked"';
			else
				$checked = '';
			echo '<input type="checkbox" name="check_textmasterAuthorReadproof[]" class="check_textmasterAuthor" value="'.$auteur['author_id'].'" '.$checked.'> ';
			echo $auteur['author_ref'].$auteurDesc;
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';
	}
}

function wp_texmaster_readproof_pmb_metaboxes(&$tApi){
	global $post;
	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showPMBReadproof"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Filter les meta-boxes' ,'textmaster').'</a><br/>';
		echo '<div id="pmbReadproof">';
		echo '<ul style="display:inline-block;">';

		$chk_tm_mb_feilds = unserialize(get_option_tm('chk_tm_mb_feilds'));

		$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
		if (isset($meta_boxes)  && count($meta_boxes) != 0) {
			foreach ($meta_boxes as $meta_box) {
				if (count($meta_box['fields']) != 0 && (((!isset($meta_box['post_types']) || $meta_box['post_types'] == NULL) && get_post_type($post->ID) == 'post') || (isset($meta_box['post_types']) && in_array(get_post_type($post->ID), $meta_box['post_types'])) )) {
					foreach ($meta_box['fields'] as $meta_box_fields) {

						$chked = '';
						if (is_array($chk_tm_mb_feilds) && in_array($meta_box_fields['id'], $chk_tm_mb_feilds))
							$chked = 'checked="checked"';

						//	var_dump($meta_box_fields);
						if ($meta_box_fields['type'] == 'text' || $meta_box_fields['type'] == 'textarea' || $meta_box_fields['type'] == 'wysiwyg'){
							echo '<li><label><input type="checkbox" name="chk_tm_mb_feilds[]" value="'.$meta_box_fields['id'].'" '.$chked.' class="chk_tm_mb_feilds_read"/> ';
							echo (isset($meta_box_fields['name']) != FALSE  ? ' '.$meta_box_fields['name'] .'' : '').' ('.$meta_box_fields['id'].')</label></li>';
						}
					}
				}
/*else
					_e('Aucune Meta-box','textmaster');*/
			}
		}
		else
			_e('Aucune Meta-box','textmaster');


		echo '</ul>';
		echo '</div>';
	}
}

?>
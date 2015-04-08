<?php

// The Event Location Metabox
function wp_texmaster_readproof_metaboxes() {
	global $post;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$categories = $tApi->getCategories();
	if (is_array($categories) && array_key_exists('message',$categories)) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
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

		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCat" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorie', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorie', true);
		else
			$catSelected = get_option_tm('textmaster_readproofCategorie');

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

		foreach($languageLevels as $key => $languageLevel)
		{
			if ($languageLevelSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
			else
				echo '<option value="'.$key.'">'.$languageLevel.'</option>';
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

		echo '<label class="options_pricetm">'.__('Contrôle qualité :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityReadProof">NC</strong> '. $infosClient['wallet']['currency_code'] .'</div><br/>';
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

		echo '<label class="options_pricetm">'.__('Expertise :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseReadproof">NC</strong> '. $infosClient['wallet']['currency_code'] .'</div><br/>';

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
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriorityReadproof">NC</strong> '. $infosClient['wallet']['currency_code'] .'</div><br/>';

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
			$result = $tApi->getProjetStatus($idProjet);
			if ($idDocument != '')
				$result = $tApi->getDocumentStatus($idProjet, $idDocument);

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

		wp_texmaster_readproof_options_metaboxes();
		wp_texmaster_readproof_authors_metaboxes();

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
			echo '<span class="walletTextmaster">'.$infosClient['wallet']['current_money'].'</span> '.$infosClient['wallet']['currency_code'].'</strong>';
		echo  '<a href="'.$urlAchat.'" target="_blank">'. __('Créditer mon compte', 'textmaster').'</a></div><br/><br/>';
		echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; jQuery(document).ready(function($) {getPrice("readproof");});</script>';

	}

}

function callback_readproof(){
	global $wpdb;

	$categorie = $_POST['categorie'];
	$languageLevel = $_POST['languageLevel'];
	$language = $_POST['language'];
	$postID = $_POST['postID'];
	$authors = '';
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

	$textmasterKeywords_readproof = $_POST['keywords'];
	$textmasterKeywordsRepeatCount_readproof = $_POST['keywordsRepeatCount'];
	$textmasterVocabularyType_readproof = $_POST['vocabularyType'];
	$textmasterGrammaticalPerson_readproof = $_POST['grammaticalPerson'];
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

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');
	$idProjet = get_post_meta($postID,'textmasterId', TRUE);

	//	echo $idProjet;
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
		//$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
		$nbMots = textmaster_api::countWords( get_the_title($postID) .' '.$contentText);
		$ret = $tApi->addDocument($idProjet, get_the_title($postID) , $nbMots, $content, 1, $textmasterKeywords_readproof, $textmasterKeywordsRepeatCount_readproof);
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
			syncProjets('waiting_assignment');
			//syncProjets('in_progress');
			syncProjets('in_creation');
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

function wp_texmaster_readproof_options_metaboxes(){
	global $post;


	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$categories = $tApi->getCategories();
	if (array_key_exists('message',$categories) ) {
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

function wp_texmaster_readproof_authors_metaboxes(){
	global $post;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showAuthorsReadproof"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Vos auteurs' ,'textmaster').'</a><br/>';
		echo '<div id="authorsReadproof">';

		echo '<ul style="display:inline-block;">';
		if (get_post_meta($post->ID, 'textmasterReadProofAuthor', true) != '')
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

//------------------------------------------------------------------------------------
// Les traductions
//----------------------------------------------------------------------------------
function wp_texmaster_traduction_metaboxes() {
	global $post;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$categories = $tApi->getCategories();
	if (is_array($categories) && array_key_exists('message',$categories)) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
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

		//	echo '<form name="textmaster_form" method="post" action="">';
		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCatTrad" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorieTrad', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorieTrad', true);
		else
			$catSelected = get_option_tm('textmaster_traductionCategorie');

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
		echo '<select id="select_textmasterTradLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTradLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterTradLanguageLevel', true);
		else
			$languageLevelSelected = get_option_tm('textmaster_traductionLanguageLevel');

		foreach($languageLevels as $key => $languageLevel)
		{
			if ($languageLevelSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
			else
				echo '<option value="'.$key.'">'.$languageLevel.'</option>';
		}
		echo '</select><br/>';
		echo '<div>'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBaseTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</div><br/>';

		$languages = $tApi->getLanguages();
		echo '<div style="display:inline-block;margin-right:20px;">';
		echo '<label>'.__('Langue d\'origine :','textmaster').'</label><br/>';
		echo '<select id="select_textmasterLangOrigine">';

		if (get_post_meta($post->ID, 'textmasterLangOrigine', true) != '')
			$languageSourceSelected = get_post_meta($post->ID, 'textmasterLangOrigine', true);
		else
			$languageSourceSelected = get_option_tm('textmaster_traductionLanguageSource');

		foreach($languages as $language)
		{
			if ($languageSourceSelected == $language['code'])
				echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
			else
				echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<div style="display:inline-block;">';
		echo '<label>'.__('Traduction en :','textmaster').'</label><br/>';

		echo '<select id="select_textmasterLangDestination">';

		if (get_post_meta($post->ID, 'textmasterLangDestination', true) != '')
			$languageDestinationSelected = get_post_meta($post->ID, 'textmasterLangDestination', true);
		else
			$languageDestinationSelected = get_option_tm('textmaster_traductionLanguageDestination');

		foreach($languages as $language)
		{
			if ($languageDestinationSelected == $language['code'])
				echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
			else
				echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		echo '<br/>';
		if (get_post_meta($post->ID, 'textmasterQualityTraduction', true) != '')
			$textmaster_qualityRedaction = get_post_meta($post->ID, 'textmasterQualityTraduction', true);
		else
			$textmaster_qualityRedaction = get_option_tm('textmaster_qualityTraduction');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_qualityRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

		echo '<label class="options_pricetm">'.__('Contrôle qualité :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</div><br/>';

		if (get_post_meta($post->ID, 'textmasterExpertiseReadproof', true) != '')
			$textmaster_expertiseRedaction = get_post_meta($post->ID, 'textmasterExpertiseTraduction', true);
		else
			$textmaster_expertiseRedaction = get_option_tm('textmaster_expertiseTraduction');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_expertiseRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

		echo '<label class="options_pricetm">'.__('Expertise :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</div><br/>';

		if (get_post_meta($post->ID, 'textmaster_priorityTraduction', true) != '')
			$textmaster_priorityRedaction = get_post_meta($post->ID, 'textmasterPriorityTraduction', true);
		else
			$textmaster_priorityRedaction = get_option_tm('textmaster_priorityTraduction');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_priorityRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

		echo '<label class="options_pricetm">'.__('Commande prioritaire :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriorityTrad">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';

		if (get_post_meta($post->ID, 'textmaster_briefingTraduction', true) != '')
			$textmasterBriefing_traduction = get_post_meta($post->ID, 'textmaster_briefingTraduction', true);
		else
			$textmasterBriefing_traduction = get_option_tm('textmaster_traductionBriefing');

		echo '<label>'.__('Briefing :','textmaster').'</label>';
		echo '<textarea style="width:235px;height:100px;" name="text_textmasterBriefing_traduction" id="text_textmasterBriefing_traduction">'.$textmasterBriefing_traduction.'</textarea><br/>';

		$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
		$idDocument = get_post_meta($post->ID,'textmasterDocumentIdTrad', TRUE);

		$txtRet = '';
		$disabled = '';
		$hide = FALSE;
		if ($idProjet != '') {
			//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits
			$result = $tApi->getProjetStatus($idProjet);
			if ($idDocument != '')
				$result = $tApi->getDocumentStatus($idProjet, $idDocument);
//			print_r($result);
			if ($result == 'in_review')
			{
				$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la traduction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=trad&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				$disabled = 'disabled=disabled';
				$hide = TRUE;
			}
			else if ( $result == 'in_progress' || $result == 'waiting_assignment' ||  $result == 'quality_control')
			{
				$txtRet = __('Cet article est en cours de traduction.','textmaster');
				$disabled = 'disabled=disabled';
				$hide = TRUE;
			}
			else if ($result == 'completed') {
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la traduction','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la traduction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=trad&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				$disabled = 'disabled=disabled';
				$hide = TRUE;
			}


		}
		else if ($post->post_content == '')
		{
			$ret = __('Merci de sauvegarder cet article.','textmaster');
			$disabled = 'disabled=disabled';
		}

		wp_texmaster_traduction_options_metaboxes();
		wp_texmaster_traduction_authors_metaboxes();

		echo '<img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmTrad" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmTrad"> '.__('Merci de patienter', 'textmaster').'</div> <div id="resultTextmasterTrad" class="misc-pub-section">'.$txtRet.'</div>';
		if (!$hide)
			echo '<br/><div id="publishing-action"><input name="traduction" type="button" class="button button-highlighted" id="traduction" tabindex="5" accesskey="p" value="'.__('Envoyer','textmaster').'" '.$disabled.'></div>';
		echo '<div style="clear:both"></div><br/>';
		echo '<div class="misc-pub-section">'. __('Coût', 'textmaster').' : <strong id="priceTextmasterTrad">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo ' (<span class="nbMots"></span> '.__('mots','textmaster').')</div><div class="misc-pub-section">'.__('Crédits:', 'textmaster').' <strong><span class="walletTextmaster">';
		if (is_array($infosClient))
			echo $infosClient['wallet']['current_money'].'</span> '.$infosClient['wallet']['currency_code'].'</strong>';
		echo ' <a href="'.$urlAchat.'" target="_blank">'. __('Créditer mon compte', 'textmaster').'</a></div><br/><br/>';
		echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'";  jQuery(document).ready(function($) {getPrice("traduction");});</script>';
		//	echo '</form>';

	}

}

function callback_traduction(){
	global $wpdb;

	$categorie = $_POST['categorie'];
	$languageLevel = $_POST['languageLevel'];
	$langOrigine = $_POST['langOrigine'];
	$langDestination = $_POST['langDestination'];
	$postID = $_POST['postID'];
	$authors = '';
	if (isset($_POST['authors']))
		$authors = $_POST['authors'];

	$textmaster_qualityTraduction = '';
	if (isset($_POST['quality']))
		$textmaster_qualityTraduction = $_POST['quality'];
	$textmaster_expertiseTraduction = '';
	if (isset($_POST['expertise']))
		$textmaster_expertiseTraduction = $_POST['expertise'];
	$textmaster_priorityTraduction = '';
	if (isset($_POST['priority']))
		$textmaster_priorityTraduction = $_POST['priority'];

	$textmasterBriefing_traduction = $_POST['briefing'];

	$textmasterKeywords_traduction = $_POST['keywords'];
	$textmasterKeywordsRepeatCount_traduction = $_POST['keywordsRepeatCount'];
	$textmasterVocabularyType_traduction = $_POST['vocabularyType'];
	$textmasterGrammaticalPerson_traduction = $_POST['grammaticalPerson'];
	$textmasterTargetReaderGroup_traduction = $_POST['targetReaderGroup'];

	update_post_meta($postID, 'textmasterCategorieTrad',$categorie);
	update_post_meta($postID, 'textmasterTradLanguageLevel',$languageLevel);
	update_post_meta($postID, 'textmasterLangOrigine',$langOrigine);
	update_post_meta($postID, 'textmasterLangDestination',$langDestination);
	update_post_meta($postID, 'textmasterTraductionAuthor',serialize($authors));

	update_post_meta($postID, 'textmaster_qualityTraduction',$textmaster_qualityTraduction);
	update_post_meta($postID, 'textmaster_expertiseTraduction',$textmaster_expertiseTraduction);
	update_post_meta($postID, 'textmaster_priorityTraduction',$textmaster_priorityTraduction);

	update_post_meta($postID, 'textmaster_briefingTraduction',$textmasterBriefing_traduction);

	if (isset($_REQUEST['keywords']))
		update_post_meta($postID, 'textmasterKeywords_traduction', $_REQUEST['keywords']);
	if (isset($_REQUEST['keywordsRepeatCount']))
		update_post_meta($postID, 'textmasterKeywordsRepeatCount_traduction', $_REQUEST['keywordsRepeatCount']);
	if (isset($_REQUEST['vocabularyType']))
		update_post_meta($postID, 'textmasterVocabularyType_traduction', $_REQUEST['vocabularyType']);
	if (isset($_REQUEST['grammaticalPerson']))
		update_post_meta($postID, 'textmasterGrammaticalPerson_traduction', $_REQUEST['grammaticalPerson']);
	if (isset($_REQUEST['targetReaderGroup']))
		update_post_meta($postID, 'textmasterTargetReaderGroup_traduction', $_REQUEST['targetReaderGroup']);

	$content_post = get_post($postID);
	$content = $content_post->post_content;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');
	$idProjet = get_post_meta($postID,'textmasterIdTrad', TRUE);

	$checkStatut = $tApi->getProjetStatus($idProjet);
	$retProjet = '';
	if ($idProjet == ''|| $checkStatut == 'canceled')
	{
		$retProjet = $tApi->makeProject(get_the_title($postID), 'translation', $langOrigine, $langDestination, $categorie, $textmasterBriefing_traduction, $languageLevel, $textmaster_qualityTraduction, $textmaster_expertiseTraduction, $textmaster_priorityTraduction,'', $textmasterVocabularyType_traduction, $textmasterGrammaticalPerson_traduction, $textmasterTargetReaderGroup_traduction, $authors);
		//	print_r($retProjet);
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
		update_post_meta($postID, 'textmasterIdTrad', $idProjet);

		$contentText = cleanWpTxt( $content );
	//	$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
		$nbMots = textmaster_api::countWords( get_the_title($postID) .' '.$contentText);
		$ret = $tApi->addDocument($idProjet, get_the_title($postID) , $nbMots, $content, 1, $textmasterKeywords_traduction, $textmasterKeywordsRepeatCount_traduction);
		//	print_r($ret);
		if (is_array($ret))
		{
			if (array_key_exists('id',$ret)){
				update_post_meta($postID, 'textmasterDocumentIdTrad', $ret['id']);
				$idDocument = $ret['id'];
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
			update_post_meta($postID, 'textmasterStatusTrad', $result);
			$ret = __('La traduction de cet article est lancée.','textmaster');
			wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
			syncProjets('waiting_assignment');
		//	syncProjets('in_progress');
			syncProjets('in_creation');
		}
		else
			$ret = $retLaunch;

	}
	else if ($result == 'in_progress' ) {
		$ret = __('Cet article est déjà en cours de traduction.','textmaster');
	}
	else if ($result == 'completed' ) {
		$ret = __('La traduction de cet article est terminée.','textmaster');
	}

	if (strpos($ret, __('Error')) !== FALSE)
		echo '<br><div class="error">'.$ret.'</div>';
	else
		echo '<br><div class="updated">'.$ret.'</div>';
	die();

}


function wp_texmaster_traduction_options_metaboxes(){
	global $post;


	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$categories = $tApi->getCategories();
	if (array_key_exists('message',$categories)) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showOptionsTraduction"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Plus d\'options' ,'textmaster').'</a><br/>';
		echo '<div id="optionsTraduction">';
		echo '<label class="options_pricetm">'.__('Mots-clés:','textmaster').' ('.__('séparés par des virgules','textmaster').')</label> ';
		echo '<textarea style="width:235px;" name="text_textmasterKeywords_traduction" id="text_textmasterKeywords_traduction">'.get_post_meta($post->ID, 'textmasterKeywords_traduction', true).'</textarea><br/>';

		if (get_post_meta($post->ID, 'textmasterKeywordsRepeatCount_traduction', true) != '')
			$KeywordsRepeatCountSelected = get_post_meta($post->ID, 'textmasterKeywordsRepeatCount_traduction', true);
		else
			$KeywordsRepeatCountSelected = 1;
		_e('A répéter :','textmaster');
		echo '<input type="text" id="text_textmasterKeywordsRepeatCount_traduction" name="text_textmasterKeywordsRepeatCount_traduction" style="width:50px;text-align:right;" value="'.$KeywordsRepeatCountSelected.'" />'. __('fois','textmaster').'<br/><br/>';


		echo '<label class="options_pricetm">'.__('Type de Vocabulaire:','textmaster').'</label>';
		echo '<select id="select_textmasterVocabularyType_traduction" name="select_textmasterVocabularyType_traduction" style="width:235px;"><br/><br/>';

		if (get_post_meta($post->ID, 'textmasterVocabularyType_traduction', true) != '')
			$vocabulary_typeSelected = get_post_meta($post->ID, 'textmasterVocabularyType_traduction', true);
		else
			$vocabulary_typeSelected = get_option_tm('textmaster_traductionVocabularyType');

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
		echo '<select id="select_textmasterGrammaticalPerson_traduction" name="select_textmasterGrammaticalPerson_traduction" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterGrammaticalPerson_traduction', true) != '')
			$grammatical_personSelected = get_post_meta($post->ID, 'textmasterGrammaticalPerson_traduction', true);
		else
			$grammatical_personSelected = get_option_tm('textmaster_traductionGrammaticalPerson');

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
		echo '<select id="select_textmasterTargetReaderGroup_traduction" name="select_textmasterTargetReaderGroup_traduction" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTargetReaderGroup_traduction', true) != '')
			$target_reader_groupSelected = get_post_meta($post->ID, 'textmasterTargetReaderGroup_traduction', true);
		else
			$target_reader_groupSelected = get_option_tm('textmaster_traductionTargetReaderGroup');

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

function wp_texmaster_traduction_authors_metaboxes(){
	global $post;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showAuthorsTraduction"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Vos auteurs' ,'textmaster').'</a><br/>';
		echo '<div id="authorsTraduction">';

		echo '<ul style="display:inline-block;">';
		if (get_post_meta($post->ID, 'textmasterTraductionAuthor', true) != '')
			$auteurSelected = unserialize( get_post_meta($post->ID, 'textmasterTraductionAuthor', true));
		else
			$auteurSelected = array(get_option_tm('textmaster_authorTraduction'));

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
			echo '<input type="checkbox" name="check_textmasterAuthorTraduction[]" class="check_textmasterAuthor" value="'.$auteur['author_id'].'" '.$checked.'> ';
			echo $auteur['author_ref'].$auteurDesc;
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}

function set_texmaster_columns() {
	global $pagenow;

	if ( isset($_GET['post_type']) && $_GET['post_type'] == 'page' ){
		add_filter('manage_pages_columns', 'texmaster_columns_head');
		add_action('manage_pages_custom_column', 'texmaster_columns_content', 10, 2);
	}
	else if (!isset( $_GET['post_type'] ) ||  $_GET['post_type'] != 'textmaster_redaction') {
		/*$filter = 'manage_'.$_GET['post_type'] .'_posts_columns';
		add_filter($filter, 'texmaster_columns_head');
		$action = 'manage_'.$_GET['post_type'] .'_posts_custom_column';
		add_action($action, 'texmaster_columns_content', 10, 2);*/
		add_filter('manage_posts_columns', 'texmaster_columns_head');
		add_action('manage_posts_custom_column', 'texmaster_columns_content', 10, 2);
	}
	else if ($pagenow == 'edit.php' && isset( $_GET['post_type'] ) &&  $_GET['post_type'] != 'textmaster_redaction') {
		add_filter('manage_posts_columns', 'texmaster_columns_head');
		add_action('manage_posts_custom_column', 'texmaster_columns_content', 10, 2);
	}
}

function texmaster_columns_head($columns) {
//	$new = array();
	$new = $columns;
//	foreach($columns as $key => $title) {
//		if ($key=='comments')
			$new['texmaster_status'] = __('Status TextMaster', 'textmaster');
		$new[$key] = $title;
//	}
	return $new;

}

function texmaster_columns_content($column_name, $postID) {
	global $wpdb;

/*	$tApi = new textmaster_api();
	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	$tApi->keyapi =  get_option_tm('textmaster_api_key');
*/

	if ($column_name == 'texmaster_status') {
		if (get_option_tm('textmaster_useTraduction') == 'Y') {
			$idProjet = get_post_meta($postID,'textmasterIdTrad', TRUE);
			$req = 'SELECT status FROM '.$wpdb->prefix.'tm_projets WHERE id="'.$idProjet.'"';
			$status = $wpdb->get_var( $req);
			$tmStatut = textmaster_api::getLibStatus($status);

			if ($tmStatut == 'NC')
				$tmStatut  =__('A traduire/relire', 'textmaster');

		//	$tmStatut = $tApi->getLibStatus($tApi->getProjetStatus($idProjet));
			echo __('Traduction', 'textmaster' ) .' : '.$tmStatut.'<br/>';
		}
		if ( get_option_tm('textmaster_useReadproof') == 'Y') {
			$idProjet = get_post_meta($postID,'textmasterId', TRUE);
			$req = 'SELECT status FROM '.$wpdb->prefix.'tm_projets WHERE id="'.$idProjet.'"';
			$status = $wpdb->get_var( $req);
			$tmStatut = textmaster_api::getLibStatus($status);

			if ($tmStatut == 'NC')
				$tmStatut  =__('A traduire/relire', 'textmaster');

			//$tmStatut = $tApi->getLibStatus($tApi->getProjetStatus($idProjet));
			echo __('Relecture', 'textmaster' ) .' : '.$tmStatut;
		}
	}
}

function texmaster_bulk_actions($actions){
	global $post_type;

//	if($post_type == 'post' || $post_type == 'page') {
	if ($post_type != 'textmaster_redaction') {
		if (get_option_tm('textmaster_useTraductionBulk') == 'Y' || get_option_tm('textmaster_useTraductionBulk') == '' ){
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('textmaster_TraductionBulk').text('<?php _e('Traduction TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
					jQuery('<option>').val('textmaster_TraductionBulk').text('<?php _e('Traduction TextMaster', 'textmaster') ?>').appendTo("select[name='action2']");
					jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_TraductionBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('traduction_bulk.php', __FILE__); ?>?post_ids=' + ids + '&height=500&width=630&TB_iframe=true';
							if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_TraductionBulk' )
								tb_show('<?php _e('Traduction TextMaster', 'textmaster') ?>', urlPopup);
								event.preventDefault();
						}
					});
				});
			</script>
			<?php
		}
		if (get_option_tm('textmaster_useReadproofBulk') == 'Y' || get_option_tm('textmaster_useReadproofBulk') == ''){
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('textmaster_ReadproofBulk').text('<?php _e('Relecture TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
					jQuery('<option>').val('textmaster_ReadproofBulk').text('<?php _e('Relecture TextMaster', 'textmaster') ?>').appendTo("select[name='action2']");
					jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ReadproofBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('readproof_bulk.php', __FILE__); ?>?post_ids=' + ids + '&height=500&width=630&TB_iframe=true';
							tb_show('<?php _e('Relecture TextMaster', 'textmaster') ?>' , urlPopup);
							event.preventDefault();
						}
					});
				});
			</script>
			<?php
		}

	}
	return $actions;
}

// pour avoir les infos des post concerné
function getInfosPosts($ids)
{
	$aRet = false;
	$aIds = explode(';', $ids);
	if (count($aIds) != 0) {
		$numPost = 0;
		$totalWords = 0;
		foreach ($aIds as $id) {
			if (intval($id)) {
				$aRet[$numPost]['id'] = $id;
				$aRet[$numPost]['content'] = get_post($id);
				$string = cleanWpTxt($aRet[$numPost]['content']->post_content);
		//		echo $string.'<br>';
		//		$aRet[$numPost]['wordsCount'] = str_word_count($string, 0, "àâäéèêëïîöôùüû");
				$aRet[$numPost]['wordsCount'] = textmaster_api::countWords($aRet[$numPost]['content']->post_title.' '.$string);
		//		echo $aRet[$numPost]['wordsCount'] .'<br>';
				$totalWords += $aRet[$numPost]['wordsCount'];
				$numPost++;
			}
		}
		if ($totalWords != 0)
			$aRet['totalWords'] = $totalWords;
	}

	return $aRet;
}

// pour recuper un contenu texte sans balise HTML ni sortcode Wp
function cleanWpTxt($post_content){
	$contentText = strip_shortcodes( $post_content );
	$contentText = strip_tags($contentText);
	$contentText = @html_entity_decode($contentText, ENT_COMPAT | ENT_HTML401, 'UTF-8');

	return $contentText;
}

function count_words($string) {

	//$string = htmlentities($string);
	// Return the number of words in a string.
	$string = str_replace("&#039;", "'", $string);
//	$string = str_replace("&laquo;", '"', $string);
//	$string = str_replace("&raquo;", '"', $string);
//	$string = str_replace('&amp;','&',$string);
	$string = str_replace(array('&nbsp;','&#160;'),' ',$string);


	$t= array(' ', "\t", '=', '+', '-', '*', '/', '\\', ',', '.', ';', ':', '[', ']', '{', '}', '(', ')', '<', '>', '&', '%', '$', '@', '#', '^', '!', '?', '~'); // separators
	$string= str_replace($t, " ", $string);
	$string= trim(preg_replace("/\s+/", " ", $string));
	$num= 0;

	$word_array= explode(" ", $string);
	$num= count($word_array);

	return $num;
}

function textmaster_admin_posts_filter_restrict_manage_posts(){
	global $wpdb, $post_type;

//	if($post_type == 'post' || $post_type == 'page') {
	if ($post_type != 'textmaster_redaction') {
		$req = 'SELECT DISTINCT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key="textmasterStatusReadproof" OR meta_key="textmasterStatusTrad"';
		$results = $wpdb->get_results( $req);
		if (count($results) != 0)
		{
			foreach ($results as $post) {
				if ($post->meta_value != '')
					$arrayStatus[$post->meta_value] = textmaster_api::getLibStatus($post->meta_value);
			}
		}
		$current_v = isset($_GET['textmaster_status'])? $_GET['textmaster_status']:'';
		?>
        <select name="textmaster_status" onChange="jQuery('#posts-filter').submit();">
        <option value=""><?php _e('Voir tous les status TextMaster', 'textmaster'); ?></option>
        <option value="notTM" <?php  echo 'notTM' == $current_v? ' selected="selected"':'' ?>><?php _e('A traduire/relire', 'textmaster'); ?></option>
        <?php

		foreach ($arrayStatus as $value => $label) {
			printf
			    (
			        '<option value="%s"%s>%s</option>',
			        $value,
			        $value == $current_v? ' selected="selected"':'',
			        $label
			    );
		}
		?>
        </select>
        <?php
	}
}

function textmaster_posts_filter( $query ){
	global $pagenow, $post_type;

	//if ( ($post_type == 'post' || $post_type == 'page') && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '') {
	if ($post_type != 'textmaster_redaction'  && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '' && $_GET['textmaster_status'] != 'notTM'){
	//	$query->query_vars['meta_key'] = array('textmasterStatusReadproof', 'textmasterStatusTrad');
	//	$query->query_vars['meta_key'] = "IN ('textmasterStatusReadproof','textmasterStatusTrad')";
		//$query->query_vars['meta_key'] = 'textmasterStatusTrad';
		$query->query_vars['meta_value'] =  array($_GET['textmaster_status'], $_GET['textmaster_status']);
	//	$query->query_vars['meta_value'] = $_GET['textmaster_status'];
//		print_r($query);
//		echo $query->query;
//		$query->parse_query();
//		echo $query->query;
	}
}

function textmaster_posts_where($where){
	global $pagenow, $post_type, $wpdb;

	if ($post_type != 'textmaster_redaction'  && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '' && $_GET['textmaster_status'] == 'notTM'){
			$where .= ' AND '.$wpdb->prefix.'posts.ID NOT IN (SELECT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%textmasterId%")';
	}
//	echo $where;
	return $where;
}
?>
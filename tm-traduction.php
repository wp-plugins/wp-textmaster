<?php

//------------------------------------------------------------------------------------
// Les traductions
//----------------------------------------------------------------------------------
function wp_texmaster_traduction_metaboxes() {
	global $post;

	$resultLang = '';

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

/*	$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
	$idDocument = get_post_meta($post->ID,'textmasterDocumentIdTrad', TRUE);

	if ($idDocument != '')
		$result = $tApi->getDocumentStatus($idProjet, $idDocument);
	else if ($idProjet != '')
		$result = $tApi->getProjetStatus($idProjet);*/
	// pour un post traduit via TM
	$sId_origine = get_post_meta($post->ID, 'tm_lang');
	if (count($sId_origine) != 0) {
		$aId_origine = explode(';',$sId_origine[0] );
//		print_r($aId_origine);
		$idTmProjet = get_IdProjetTrad($aId_origine[0],$aId_origine[1]);
//		echo $idTmProjet;
		$idTmDoc= get_IdDocTrad($aId_origine[0],$aId_origine[1]);
		if ($idTmDoc != '' && $idTmProjet != '')
			$resultLang = $tApi->getDocumentStatus($idTmProjet, $idTmDoc);
//		echo $resultLang;
		$supportMsgs = $tApi->getSupportMsgs($idTmProjet, $idTmDoc, TRUE);
	}

	if ($resultLang == 'incomplete' && count($supportMsgs) != 0){
		if ($idTmProjet != ''){
			metaboxes_post($tApi, 'traduction', $idTmProjet, $idTmDoc, '', $supportMsgs, $aId_origine[0] );
			echo '<script>';
			echo 'jQuery(\'#box_select_activite\').hide();';
			echo "jQuery('h2', '.wrap').before('<div class=\"update-nag\" id=\"msg_validate_please\" style=\"width:95%\">".__("Votre contenu est en attente de validation. Vous pouvez le valider dans l\'encart TextMaster.", 'textmaster')."</div>');";
			echo '</script>';
		}
	}
	else if ($resultLang == 'in_review' && get_option_tm('textmaster_useMultiLangues') == 'Y') {
		if ($idTmProjet != ''){
			metaboxes_post($tApi, 'traduction', $idTmProjet, $idTmDoc, '', $supportMsgs, $aId_origine[0] );
			echo '<script>';
			echo 'jQuery(\'#box_select_activite\').hide();';
			echo "jQuery('h2', '.wrap').before('<div class=\"update-nag\" id=\"msg_validate_please\" style=\"width:95%\">".__("Votre contenu est en attente de validation. Vous pouvez le valider dans l\'encart TextMaster.", 'textmaster')."</div>');";
			echo '</script>';
		}

		else
			traduction_metaboxes_pre($tApi);
	}
	else if (( $resultLang != 'in_review') || get_option_tm('textmaster_useMultiLangues') != 'Y')
		traduction_metaboxes_pre($tApi);
	 /*else
		metaboxes_post($tApi, 'traduction');
*/

}

function traduction_metaboxes_pre(&$tApi){
	global $post;

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	echo '<div id="meta_trad" >';

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
			$local = get_locale();
			if ($local != '')
				$lang = explode('_', $local);
			else
				$lang[0] = 'en';
			$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE.'?auth_token='.$infosUser['authentication_token'];
			$infosClient = $tApi->getUserInfos();
		}


		$categories = $tApi->getCategories();
		//	echo '<form name="textmaster_form" method="post" action="">';
		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCatTrad" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorieTrad', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorieTrad', true);
		else
			$catSelected = get_option_tm('textmaster_traductionCategorie');

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
		echo '<select id="select_textmasterTradLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTradLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterTradLanguageLevel', true);
		else
			$languageLevelSelected = get_option_tm('textmaster_traductionLanguageLevel');

		foreach($languageLevels['traduction'] as $key => $languageLevel)
		{
			if ($languageLevelSelected == $languageLevel["name"])
				echo '<option value="'.$languageLevel["name"].'" selected="selected">'.$languageLevel["name"].'</option>';
			else
				echo '<option value="'.$languageLevel["name"].'">'.$languageLevel["name"].'</option>';
		}
		echo '</select><br/>';
		echo '<div>'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBaseTrad">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';

		$languages = $tApi->getLanguages();
		echo '<div style="display:inline-block;margin-right:20px;">';
		echo '<label>'.__('Langue d\'origine :','textmaster').'</label><br/>';
		echo '<select id="select_textmasterLangOrigine">';

		$languageSourceSelected = '';
		if (isset($_REQUEST['lang']) && $_REQUEST['lang'] != '')
			$languageSourceSelected = $_REQUEST['lang'];
		else if (checkInstalledPlugin('WPML Multilingual CMS')) {
			$post_language_info = wpml_get_language_information($post->ID);
			$languageSourceSelected = strtolower(str_replace("_", "-", $post_language_info['locale']));
		}else if (get_post_meta($post->ID, 'textmasterLangOrigine', true) != '')
			$languageSourceSelected = get_post_meta($post->ID, 'textmasterLangOrigine', true);
		else
			$languageSourceSelected = get_option_tm('textmaster_traductionLanguageSource');
//		echo $languageSourceSelected;
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

		if (get_option_tm('textmaster_useMultiLangues') != 'Y')
			echo '<select id="select_textmasterLangDestination">';
		else
			echo '<select id="select_textmasterLangDestination" onchange="get_status_trad('.$post->ID.',  jQuery(this).val() )">';

		if (isset($_REQUEST['langTm']) && $_REQUEST['langTm'] != '')
			$languageDestinationSelected = $_REQUEST['langTm'];
		else if (get_post_meta($post->ID, 'textmasterLangDestination', true) != '')
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
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityTrad">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';

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

//		echo '<label class="options_pricetm">'.__('Expertise :','textmaster').'</label> ';
//		echo '<input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
//		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseTrad">NC</strong> ';
//		if (is_array($infosClient))
//			echo $infosClient['wallet']['currency_code'];
//		echo '</div><br/>';


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

//		if (get_option_tm('textmaster_useMultiLangues') != 'Y') {
//			$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
//			$idDocument = get_post_meta($post->ID,'textmasterDocumentIdTrad', TRUE);
//		}else {
			$idProjet = get_IdProjetTrad($post->ID, $languageDestinationSelected);
			$idDocument = get_IdDocTrad($post->ID, $languageDestinationSelected);
//		}

		$txtRet = '';
		$disabled = '';
		$hide = FALSE;

		if ($idProjet != '') {
			//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits

			if ($idDocument != '')
				$result = $tApi->getDocumentStatus($idProjet, $idDocument);
			else if ($idProjet != '')
				$result = $tApi->getProjetStatus($idProjet);
//						print_r($result);
			if ($result == 'in_review')
			{
				if (get_option_tm('textmaster_useMultiLangues') == 'Y')
					$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div style="clear:both"></div>';
				else
					$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" data-title="'.__('Voir / Valider la traduction','textmaster').'"  onclick="seeTrad(this);"></div><div style="clear:both"></div>';
				$disabled = 'disabled=disabled';
				if ( get_option_tm('textmaster_useMultiLangues') != 'Y'){
					$hide = TRUE;
				}
			}
			else if ( $result == 'in_progress' || $result == 'waiting_assignment' ||  $result == 'quality_control')
			{
				$txtRet = __('Cet article est en cours de traduction.','textmaster');
				$disabled = 'disabled=disabled';
				if ( get_option_tm('textmaster_useMultiLangues') != 'Y'){

					$hide = TRUE;
				}
			}
			else if ($result == 'completed') {
				if (get_option_tm('textmaster_useMultiLangues') == 'Y')
					$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div style="clear:both"></div>';
				else
					$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la traduction','textmaster').'" data-title="'.__('Voir / Valider la traduction','textmaster').'" onclick="seeTrad(this);"></div><div style="clear:both"></div>';

				$disabled = 'disabled=disabled';
				if ( get_option_tm('textmaster_useMultiLangues') != 'Y'){

					$hide = TRUE;
				}
			}


		}
		else if ($post->post_content == '')
		{
			$ret = __('Merci de sauvegarder cet article.','textmaster');
			if ( get_option_tm('textmaster_useMultiLangues') != 'Y')
				$disabled = 'disabled=disabled';
		}

//		wp_texmaster_traduction_options_metaboxes($tApi);
		wp_texmaster_traduction_authors_metaboxes($tApi);

		if ( checkInstalledPlugin('Meta Box'))
			wp_texmaster_traduction_pmb_metaboxes($tApi);

		echo '<img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmTrad" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmTrad"> '.__('Merci de patienter', 'textmaster').'</div> ';
		echo '<div id="resultTextmasterTrad" class="misc-pub-section">'.$txtRet;
		if ($hide && get_option_tm('textmaster_useMultiLangues') == 'Y')
			echo '<br/><div id="publishing-action"><input name="traduction" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Valider la traduction','textmaster').'" onclick="valideTrad(\''.$post->ID.'\', \''.$idProjet.'\', \''.$idDocument.'\', true, \''.__('Merci de patienter...', 'textmaster').'\');"></div>';
		else if (!$hide)
			echo '<br/><div id="publishing-action"><input name="traduction" type="button" class="button button-highlighted" id="traduction" tabindex="5" accesskey="p" value="'.__('Envoyer','textmaster').'" '.$disabled.'></div>';
		echo '</div><div style="clear:both"></div><br/>';
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

	echo '</div>';
}


function callback_traduction(){
	global $wpdb;

	$strMsg = '';
	$checkStatut = '';

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	if (get_option_tm('textmaster_useMultiLangues') == 'Y' && isset($_POST['valider']) && $_POST['valider'] == 'oui') {
		// on valide chez textmaster
		if ($tApi->getDocumentStatus($_POST['ProjetId'], $_POST['docId']) != 'completed' )
			$ret = $tApi->valideDoc($_POST['ProjetId'], $_POST['docId'], $_POST['select_textmasterSatisfaction'], $_POST['text_textmaster_message']);

		// Update post
		wp_publish_post( $_POST['postID'] );

		// on ajout l'auteur aux textmasters
		if ($_POST['textmaster_add_author'] == 'Y'){
			$tApi->addAuthor($_POST['auteur_description'], $_POST['select_textmasterStatutAuteur'], $_POST['auteurTmId']);
			$_SESSION['lastSyncTmAuteurs']  = '';
		}


		$strMsg = __('Cette traduction a bien été validée et ce post a été publié.','textmaster');

	} else {
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

		$textmasterKeywords_traduction = '';
		if (isset($_POST['keywords']))
			$textmasterKeywords_traduction = $_POST['keywords'];
		$textmasterKeywordsRepeatCount_traduction = '';
		if (isset($_POST['keywordsRepeatCount']))
			$textmasterKeywordsRepeatCount_traduction = $_POST['keywordsRepeatCount'];
		$textmasterVocabularyType_traduction = '';
		if (isset($_POST['vocabularyType']))
			$textmasterVocabularyType_traduction = $_POST['vocabularyType'];
		$textmasterGrammaticalPerson_traduction ='';
		if (isset($_POST['grammaticalPerson']))
			$textmasterGrammaticalPerson_traduction = $_POST['grammaticalPerson'];
		$textmasterTargetReaderGroup_traduction = '';
		if (isset($_POST['targetReaderGroup']))
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

		//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
		//	$tApi->keyapi =  get_option_tm('textmaster_api_key');
		//	$idProjet = get_post_meta($postID,'textmasterIdTrad', TRUE);
	//	if (function_exists('get_IdProjetTrad')) {
			$idProjet =  get_IdProjetTrad($postID,$langDestination);
	//	} else
	//		$idProjet = get_post_meta($postID,'textmasterIdTrad', TRUE);

		if ($idProjet != '')
			$checkStatut = $tApi->getProjetStatus($idProjet);

		$retProjet = '';
		if ($idProjet == ''|| $checkStatut == 'canceled')
		{
			$contentText = cleanWpTxt( $content );
			$work_template = '1_title_1_paragraph';
			if (trim($contentText) == '' && get_the_title($postID) != '')
				$work_template ='1_title';

			$retProjet = $tApi->makeProject(get_the_title($postID), 'translation', $langOrigine, $langDestination, $categorie, $textmasterBriefing_traduction, $languageLevel, $textmaster_qualityTraduction, $textmaster_expertiseTraduction, $textmaster_priorityTraduction, $work_template, $textmasterVocabularyType_traduction, $textmasterGrammaticalPerson_traduction, $textmasterTargetReaderGroup_traduction, $authors);
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
			$metaProjet = get_post_meta($postID, 'textmasterIdTrad', true );
			//	$ret = serialize($ret);
			if ($metaProjet != '' )
				update_post_meta($postID, 'textmasterIdTrad', $metaProjet.';'.$idProjet.'='.$langDestination);
			else
				update_post_meta($postID, 'textmasterIdTrad', $idProjet.'='.$langDestination);

			$contentText = cleanWpTxt( $content );
			$fullContent = '';


			// comptaibilité acf
			if( checkInstalledPlugin('Advanced Custom Fields') && isset($_POST['extras']) ) {
			//	var_dump($_POST);
				$params = array();
				parse_str($_POST['extras'], $params);
				if (isset($params["fields"]) && count($params["fields"]) != 0) {
					foreach ( $params["fields"] as $name => $param) {
						if ($name != 'acf_nonce' && trim($param) != ''){
							$arrayDocs[0]['original_content'][$name]["original_phrase"] = $param;
							$fullContent .= cleanWpTxt( $param );
						}

					}
				}

				$arrayDocs[0]['original_content']['content']["title"] = get_the_title($postID);
				$arrayDocs[0]['original_content']["title"]['original_phrase'] = get_the_title($postID);
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
			if(  checkInstalledPlugin('Meta Box') && isset($_POST['extras'])) {
				//	var_dump($_POST);
			//	$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
			//	var_dump($_POST['filtre_pmb']);
				$chk_tm_mb_feilds = array();
				$chk_tm_mb = array();
				// si le POST filtre_pmb les meta-boxes n'ont pas été détectés
				// on rrecup ceux des paramétrages du plugin
				if ($_POST['filtre_pmb'] == '-1') {
					$chk_option_tm_mb_feilds = unserialize(get_option_tm('chk_tm_mb_feilds'));
					if ( is_multisite())
						$chk_tm_mb_feilds = $chk_option_tm_mb_feilds[get_current_blog_id()];
					else
						$chk_tm_mb_feilds = $chk_option_tm_mb_feilds;
				}else {
					parse_str($_POST['filtre_pmb'], $chk_tm_mb);
					if (isset($chk_tm_mb['chk_tm_mb_feilds']))
						$chk_tm_mb_feilds = $chk_tm_mb['chk_tm_mb_feilds'];
				}

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
									if (trim($text) != '') {
										$arrayDocs[0]['original_content'][$name.'_tmtext_'.$key]["original_phrase"] = $text;
										$fullContent .= cleanWpTxt( $text );
									}
								}
							}
						}
					}
				}

				$arrayDocs[0]['original_content']['content']["title"] = get_the_title($postID);
				$arrayDocs[0]['original_content']["title"]['original_phrase'] = get_the_title($postID);
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
					$arrayDocs[0]['original_content']["title"]['original_phrase'] = get_the_title($postID);
					$arrayDocs[0]['original_content']['content']["original_phrase"] = $content;
					$arrayDocs[0]['original_content']["post_excerpt"]["original_phrase"]  = $content_post->post_excerpt;
					$contentText = cleanWpTxt( $content_post->post_excerpt ).' '.cleanWpTxt( $content );
				}
				else
					$arrayDocs[0]['original_content'] = $content;

				$nbMots = $tApi->countWords( get_the_title($postID) .' '.$contentText);
			}

			//	$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
			$arrayDocs[0]['title'] = get_the_title($postID);
			$arrayDocs[0]['word_count'] = $nbMots;
			$arrayDocs[0]['word_count_rule'] = 1;
			$arrayDocs[0]['keyword_list'] = $textmasterKeywords_traduction;
			$arrayDocs[0]['keywords_repeat_count'] = $textmasterKeywordsRepeatCount_traduction;

			// DEBUG FASTBOOKING -> Envoi mail
/*
			$to = 'lupuz.yonderboy@gmail.com';
			$subject = 'création trad fastbooking';
			$message = "Contenu doc\n";
			$message .= "----------------\n";
			$message .= serialize($arrayDocs). "\n";
			$message .= "----------------\n\n";
			$message .= "Metaboxes filtre defaut\n";
			$message .= "----------------\n";
			$message .= serialize($chk_tm_mb_feilds). "\n";
			$message .= "----------------\n\n";
			$message .= "Metaboxes filtre post\n";
			$message .= "----------------\n";
			$message .= $_POST['extras']. "\n";
			$message .= "----------------\n\n";
			$message .= "POST\n";
			$message .= "----------------\n";
			$message .= print_r($_POST, TRUE). "\n";

			$headers = 'From: ' . MAIL_ALERTE_SUPPORT . "\r\n" .
				'Reply-To: ' . MAIL_ALERTE_SUPPORT . "\r\n" .
				'X-Mailer: PHP/' . phpversion();

			mail($to, $subject, $message, $headers);
*/



		//	var_dump($arrayDocs);
			$ret = $tApi->addDocument($idProjet, $arrayDocs );
		//	print_r($ret);
			if (is_array($ret))
			{
				if (array_key_exists('id',$ret)){
					$metaDoc = get_post_meta($postID, 'textmasterDocumentIdTrad', true );
					if ($metaDoc != '')
						update_post_meta($postID, 'textmasterDocumentIdTrad',$metaDoc.';'. $ret['id'].'='.$langDestination);
					else
						update_post_meta($postID, 'textmasterDocumentIdTrad', $ret['id'].'='.$langDestination);
					//	update_post_meta($postID, 'tm_lang', $langDestination);
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

		$strMsg = '';
		if ($result == 'paused' || $result == 'in_creation') {
			//		echo $idProjet;
			$retLaunch = $tApi->launchProject($idProjet);
			$jsonErr = trim(substr($retLaunch, (strpos($retLaunch, '-') +1 )));
	//		print_r($jsonErr);
			$errs = json_decode($jsonErr, TRUE);

			if (is_array($errs) && array_key_exists('errors',$errs))
			{
				if (is_array($errs['errors']) && array_key_exists('credits',$errs['errors']))
					$strMsg = __('Error').$errs['errors']['credits'][0];
				else
					$strMsg = __('Error').$errs['errors']['status'][0];
			}
			else if (strpos($retLaunch, 'Error') === FALSE)
			{
				$result = $tApi->getProjetStatus($idProjet);
				update_post_meta($postID, 'textmasterStatusTrad', $result);
				$strMsg = __('La traduction de cet article est lancée.','textmaster');
				wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('waiting_assignment',1 ) );
	//			syncProjets('waiting_assignment',1);
				//	syncProjets('in_progress');
				//syncProjets('in_creation',1);
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('in_creation',1 ) );
			}
			else
				$strMsg = $retLaunch;

		}
		else if ($result == 'in_progress' ) {
			$strMsg = __('Cet article est déjà en cours de traduction.','textmaster');
		}
		else if ($result == 'completed' ) {
			$strMsg = __('La traduction de cet article est terminée.','textmaster');
		}


	}

	if (is_string($strMsg) && strpos($strMsg, __('Error')) !== FALSE)
		echo '<br><div class="error">'.$strMsg.'</div>';
	else
		echo '<br><div class="updated">'.print_r($strMsg,true).'</div>';
	die();

}


function wp_texmaster_traduction_options_metaboxes(&$tApi){
	global $post;


//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//	$tApi->keyapi =  get_option_tm('textmaster_api_key');


	if ($tApi->secretapi == '' && $tApi->keyapi == ''){
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


	/*	echo '<label class="options_pricetm">'.__('Type de Vocabulaire:','textmaster').'</label>';
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
*/


/*		echo '<label class="options_pricetm">'.__('Personne grammaticale:','textmaster').'</label>';
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
*/


/*		echo '<label class="options_pricetm">'.__('Public Ciblé:','textmaster').'</label>';
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
		echo '</select><br/><br/>';*/
		echo '</div>';
	}

}

function wp_texmaster_traduction_authors_metaboxes(&$tApi){
	global $post;

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showAuthorsTraduction"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Vos auteurs' ,'textmaster').'</a><br/>';
		echo '<div id="authorsTraduction">';

		echo '<ul style="display:inline-block;">';
		if (is_object($post) && get_post_meta($post->ID, 'textmasterTraductionAuthor', true) != '')
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

function wp_texmaster_traduction_pmb_metaboxes(&$tApi){
	global $post;
	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showPMBTraduction"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Filter les meta-boxes' ,'textmaster').'</a><br/>';
		echo '<div id="pmbTraduction">';
		echo '<ul style="display:inline-block;">';

		$chk_option_tm_mb_feilds = unserialize(get_option_tm('chk_tm_mb_feilds'));
		if (is_array($chk_option_tm_mb_feilds)) {
			if ( is_multisite())
				$chk_tm_mb_feilds = $chk_option_tm_mb_feilds[get_current_blog_id()];
			else
				$chk_tm_mb_feilds = $chk_option_tm_mb_feilds;
		}
		$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
	//	var_dump($meta_boxes);
		if (isset($meta_boxes)  && count($meta_boxes) != 0) {
			foreach ($meta_boxes as $meta_box) {
				if (count($meta_box['fields']) != 0 && (((!isset($meta_box['post_types']) || $meta_box['post_types'] == NULL) && get_post_type($post->ID) == 'post') || (isset($meta_box['post_types']) && in_array(get_post_type($post->ID), $meta_box['post_types'])) )) {
					foreach ($meta_box['fields'] as $meta_box_fields) {

						$chked = '';
						if (is_array($chk_tm_mb_feilds) && in_array($meta_box_fields['id'], $chk_tm_mb_feilds))
							$chked = 'checked="checked"';

						//	var_dump($meta_box_fields);
						if ($meta_box_fields['type'] == 'text' || $meta_box_fields['type'] == 'textarea' || $meta_box_fields['type'] == 'wysiwyg'){
							echo '<li><label><input type="checkbox" name="chk_tm_mb_feilds[]" value="'.$meta_box_fields['id'].'" '.$chked.' class="chk_tm_mb_feilds_trad"/> ';
							echo (isset($meta_box_fields['name']) != FALSE  ? ' '.$meta_box_fields['name'] .'' : '').' ('.$meta_box_fields['id'].')</label></li>';
						}
					}
				}
/*else
					_e('Aucune Meta-box','textmaster');*/
			}
			echo '<input type="hidden" name="nopmb" id="nopmb" value="0">';
		}
		else{
			if (is_array($chk_tm_mb_feilds)) {
				echo '<li><strong>'. __('Meta-box filtrées par défaut: ','textmaster').'</strong></li>';
				foreach ($chk_tm_mb_feilds as $tm_mb_feild) {
				 	echo '<li>'.$tm_mb_feild .'</li>';
				 }
			}
			else
				_e('Aucune Meta-box','textmaster');
			echo '<input type="hidden" name="nopmb" id="nopmb" value="1">';
		}


		echo '</ul>';
		echo '</div>';
	}
}

?>
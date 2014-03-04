<?php
include "../../../wp-load.php";
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="fr-FR" style="padding:0;">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="fr-FR" style="padding:0;">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel='stylesheet' href='<?php echo site_url(); ?>/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=dashicons,admin-bar,wp-admin,buttons,wp-auth-check,wp-pointer&amp;ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='thickbox-css'  href='/wp-includes/js/thickbox/thickbox.css?ver=3.4.2' type='text/css' media='all' />
<link rel='stylesheet' id='colors-css'  href='<?php echo site_url(); ?>/wp-admin/css/colors.min.css?ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='texmaster-css'  href='<?php echo plugins_url('textmaster.css' , __FILE__ ) ?>' type='text/css' media='all' />
<!--[if lte IE 7]>
<link rel='stylesheet' id='ie-css'  href='/wp-admin/css/ie.css?ver=3.4.2' type='text/css' media='all' />
<![endif]-->
<script type='text/javascript' src='<?php echo site_url(); ?>/wp-admin/load-scripts.php?c=0&amp;load=jquery,jquery-core,jquery-migrate,utils&amp;ver=<?php echo $wp_version; ?>'></script>
<script type='text/javascript' src='<?php echo plugins_url('textmaster.js' , __FILE__ ) ?>?ver=<?php echo get_tm_plugin_version();?>'></script>


</head>
<body style="padding:10px;margin:0;">
<div id="wpbody">
<form method="post" action= "traduction_bulk.php" id="tm_buckform">
<?php

add_action("admin_body_class", "set_iframe_class");

$tApi = new textmaster_api();
$tApi->secretapi = get_option('textmaster_api_secret');
$tApi->keyapi = get_option('textmaster_api_key');

$aInfosPost = getInfosPosts($_REQUEST['post_ids']);
if (count($_POST)!= 0 && $aInfosPost != FALSE) {
	echo launchReadproof($aInfosPost);
}
else if (!$aInfosPost) {
	echo '<center><strong>';
    _e('Merci de séléctionner au moins un article', 'textmaster');
	echo '</strong></center>';
}else {

	$txtRet = '';

	$textmaster_email = get_option('textmaster_email');
	$textmaster_password = get_option('textmaster_password');
	if ($textmaster_password != '' && $textmaster_email != '') {
		$oOAuth = new TextMaster_OAuth2();
		$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
		$infosUser = $oOAuth->getUserInfos($token);
		$lang = explode('_', get_locale());
		$urlAchat = 'http://' . $lang[0] . '.' . URL_TM_BOUTIQUE . '?auth_token=' . $infosUser['authentication_token'];
		$infosClient = $tApi->getUserInfos();
	}

    $categories = $tApi->getCategories();
    if (isset($categories['message']) && $categories['message'] != '') {
        _e('Merci de v&eacute;rifier vos informations de connexion à TextMaster', 'textmaster');
    }else {
    	echo '<label class="options_pricetm">'.__('Nom du projet :','textmaster').'</label>';
    	echo '<input type="text" name="textmasterNomProjet" id="textmasterNomProjet" style="width:235px;height:26px;" />' ;

		echo '<br/>' ;
        echo '<label class="options_pricetm">' . __('Categorie:', 'textmaster') . '</label>';
        echo '<select name="select_textmasterCatTrad" id="select_textmasterCatTrad" style="width:235px;height:26px;">';

        $catSelected = get_option('textmaster_traductionCategorie');

        foreach($categories as $categorie) {
            if ($catSelected == $categorie['code'])
                echo '<option value="' . $categorie['code'] . '" selected="selected">' . $categorie['value'] . '</option>';
            else
                echo '<option value="' . $categorie['code'] . '">' . $categorie['value'] . '</option>';
        }
        echo '</select><br/>';

        $languageLevels = $tApi->getLanguageLevels();

        echo '<label class="options_pricetm">' . __('Niveau de service:', 'textmaster') . '</label>';
        echo '<select id="select_textmasterTradLanguageLevel" name="select_textmasterTradLanguageLevel" style="width:235px;height:26px;">';

        $languageLevelSelected = get_option('textmaster_traductionLanguageLevel');

        foreach($languageLevels as $key => $languageLevel) {
            if ($languageLevelSelected == $key)
                echo '<option value="' . $key . '" selected="selected">' . $languageLevel . '</option>';
            else
                echo '<option value="' . $key . '">' . $languageLevel . '</option>';
        }
        echo '</select>';
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBaseTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</span><br/>';

        $languages = $tApi->getLanguages();

        echo '<label class="options_pricetm">' . __('Langue d\'origine :','textmaster') . '</label>';
        echo '<select id="select_textmasterLangOrigine" name="select_textmasterLangOrigine" style="width:235px;height:26px;">';

        $languageSelected = get_option('textmaster_traductionLanguageSource');

        foreach($languages as $language) {
            if ($languageSelected == $language['code'])
                echo '<option value="' . $language['code'] . '" selected="selected">' . $language['value'] . '</option>';
            else
                echo '<option value="' . $language['code'] . '">' . $language['value'] . '</option>';
        }
        echo '</select><br/>';

    	echo '<label class="options_pricetm">' . __('Traduction en :','textmaster') . '</label>';
    	echo '<select id="select_textmasterLangDestination" name="select_textmasterLangDestination" style="width:235px;height:26px;">';

    	$languageSelected = get_option('textmaster_traductionLanguageDestination');

    	foreach($languages as $language) {
    		if ($languageSelected == $language['code'])
    			echo '<option value="' . $language['code'] . '" selected="selected">' . $language['value'] . '</option>';
    		else
    			echo '<option value="' . $language['code'] . '">' . $language['value'] . '</option>';
    	}
    	echo '</select><br/>';


        $textmaster_qualityRedaction = get_option('textmaster_qualityTraduction');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_qualityRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Contrôle qualité :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="true" ' . $chkYes . ' /> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</span><br/>';

        $textmaster_expertiseRedaction = get_option('textmaster_expertiseTraduction');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_expertiseRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Expertise :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="true" ' . $chkYes . ' /> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</span><br/>';

        $textmaster_priorityRedaction = get_option('textmaster_priorityTraduction');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_priorityRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Commande prioritaire :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="true" ' . $chkYes . '/> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriorityTrad">NC</strong> '. $infosClient['wallet']['currency_code'] .'</span><br/>';


		wp_texmaster_traduction_options_metaboxes();
    	wp_texmaster_traduction_authors_metaboxes();

    	$textmasterBriefing_traduction = get_option('textmaster_traductionBriefing');
		echo '<br/><label>'.__('Briefing :','textmaster').'</label><br/>';
    	echo '<textarea style="width:620px;height:70px;" name="text_textmasterBriefing_traduction" id="text_textmasterBriefing_traduction">'.$textmasterBriefing_traduction.'</textarea><br/>';

    	// secu si pas assez de crédits
    	$disabled = '';
		$prixTotal = $tApi->getPricings($aInfosPost['totalWords']);
    	if ($prixTotal > $infosClient['wallet']['current_money'])
    		$disabled = 'disabled=disabled';

        echo '<br/><img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckTransalte" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckTransalte">'.__('Merci de patienter','textmaster').'</div><div id="publishing-action"><input name="save" type="submit" class="button button-highlighted" id="bulk_readproof" tabindex="5" accesskey="p" value="' . __('Traduction','textmaster') . '" '.$disabled.' onclick="jQuery(\'.ajax-loading-tmBluckTransalte\').show();"></div>';
        echo '<div style="clear:both"></div><div id="resultTextmaster" class="tmInfos">' . $txtRet . '</div>';

    	echo '<div style="font-weight:bold;">';
    	echo '<div class="tmInfos">' . __('Coût', 'textmaster') . ' : <span id="priceTextmasterTrad">NC</span> '. $infosClient['wallet']['currency_code'] .' (<span class="nbMots"></span> '.__('mots','textmaster','textmaster').')</div><div class="tmInfos">'.__('Crédits:', 'textmaster').' <span class="walletTextmaster">'.$infosClient['wallet']['current_money'].'</span> '.$infosClient['wallet']['currency_code'].' <a href="' . $urlAchat . '" target="_blank">' . __('Créditer mon compte', 'textmaster') . '</a></div>';
    	echo '</div>';

    	echo '<input type="hidden" name="forceWordsCount" id="forceWordsCount" value="'.$aInfosPost['totalWords'].'"/>';
    	echo '<input type="hidden" name="post_ids" id="post_ids" value="'.$_REQUEST['post_ids'].'"/>';
        echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; getPrice("traduction",'.$aInfosPost['totalWords'].');</script>';


    }
}

?>
</form>
</div>
</body>
</html>
<?php

// pour envoyer les infos a TextMaster
function launchReadproof($aInfosPost){
	global $tApi;

	// on créer le projet
	if ($_POST['textmasterNomProjet'] != '')
		$retProjet = $tApi->makeProject($_POST['textmasterNomProjet'], 'translation', $_POST['select_textmasterLangOrigine'], $_POST['select_textmasterLangDestination'], $_POST['select_textmasterCatTrad'], $_POST['text_textmasterBriefing_traduction'], $_POST['select_textmasterTradLanguageLevel'], $_POST['radio_textmasterQualityTraduction'], $_POST['radio_textmasterExpertiseTraduction'], $_POST['radio_textmasterPriorityTraduction'],'' ,  $_REQUEST['select_textmasterVocabularyType_traduction'], $_REQUEST['select_textmasterGrammaticalPerson_traduction'], $_REQUEST['select_textmasterTargetReaderGroup_traduction'], $_POST['check_textmasterAuthorTraduction']);
	else
		$retProjet = $tApi->makeProject('WordPress -'. date('Y-m-d'), 'translation', $_POST['select_textmasterLangOrigine'], $_POST['select_textmasterLangDestination'], $_POST['select_textmasterCatTrad'], $_POST['text_textmasterBriefing_traduction'], $_POST['select_textmasterTradLanguageLevel'], $_POST['radio_textmasterQualityTraduction'], $_POST['radio_textmasterExpertiseTraduction'], $_POST['radio_textmasterPriorityTraduction'],'' ,  $_REQUEST['select_textmasterVocabularyType_traduction'], $_REQUEST['select_textmasterGrammaticalPerson_traduction'], $_REQUEST['select_textmasterTargetReaderGroup_traduction'], $_POST['check_textmasterAuthorTraduction']);

	if (is_array($retProjet))
		$idProjet = $retProjet['id'];
	else
		$strRet .= '<div class="error">'.__('Erreur lors de la création de votre projet' ,'textmaster').' ('.$retProjet.')</div>';

	$nbDocAjouter = 0;

	if ($idProjet != '') {
		$strRet = '<ul>';
		foreach ($aInfosPost as $post) {
			if ($post['content']->ID != '') {
				$idTm = get_post_meta($post['content']->ID, 'textmasterIdTrad', true);
				if ($idTm != '') {
					$strRet .= '<li><div class="error"><strong>'.$post['content']->post_title.'</strong> '.__('La traduction de ces articles est déjà','textmaster').' : '.$tApi->getLibStatus($tApi->getProjetStatus($idTm)).'</div></li>';
				}
				else {
					update_post_meta($post['id'], 'textmasterCategorieTrad',			$_POST['select_textmasterCatTrad']);
					update_post_meta($post['id'], 'textmasterTradLanguageLevel',		$_POST['select_textmasterTradLanguageLevel']);
					update_post_meta($post['id'], 'textmasterLangOrigine',				$_POST['select_textmasterLangOrigine']);
					update_post_meta($post['id'], 'textmasterLangDestination',			$_POST['select_textmasterLangDestination']);

					if (isset($_POST['radio_textmasterQualityTraduction']))
						update_post_meta($post['id'], 'textmaster_qualityTraduction',		$_POST['radio_textmasterQualityTraduction']);
					if (isset($_POST['radio_textmasterExpertiseTraduction']))
						update_post_meta($post['id'], 'textmaster_expertiseTraduction',		$_POST['radio_textmasterExpertiseTraduction']);
					if (isset($_POST['radio_textmasterPriorityTraduction']))
						update_post_meta($post['id'], 'textmaster_priorityTraduction',		$_POST['radio_textmasterPriorityTraduction']);

					update_post_meta($post['id'], 'textmaster_briefingTraduction',		$_POST['text_textmasterBriefing_traduction']);

					if (isset($_REQUEST['text_textmasterKeywords_traduction']))
						update_post_meta($post['id'], 'textmasterKeywords_traduction', $_REQUEST['text_textmasterKeywords_traduction']);
					if (isset($_REQUEST['text_textmasterKeywordsRepeatCount_traduction']))
						update_post_meta($post['id'], 'textmasterKeywordsRepeatCount_traduction', $_REQUEST['text_textmasterKeywordsRepeatCount_traduction']);
					if (isset($_REQUEST['select_textmasterVocabularyType_traduction']))
						update_post_meta($post['id'], 'textmasterVocabularyType_traduction', $_REQUEST['select_textmasterVocabularyType_traduction']);
					if (isset($_REQUEST['select_textmasterGrammaticalPerson_traduction']))
						update_post_meta($post['id'], 'textmasterGrammaticalPerson_traduction', $_REQUEST['select_textmasterGrammaticalPerson_traduction']);
					if (isset($_REQUEST['select_textmasterTargetReaderGroup_traduction']))
						update_post_meta($post['id'], 'textmasterTargetReaderGroup_traduction', $_REQUEST['select_textmasterTargetReaderGroup_traduction']);

					if (isset($_POST['check_textmasterAuthorTraduction']))
						update_post_meta($postID, 'textmasterTraductionAuthor',serialize($_POST['check_textmasterAuthorTraduction']));

					if (is_array($retProjet) ||  $idProjet != '')
					{
						//	$ret = serialize($ret);
						update_post_meta($post['id'], 'textmasterIdTrad', $idProjet);

						$contentText = cleanWpTxt( $post['content']->post_content);
						//$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
						$nbMots = textmaster_api::countWords(  $post['content']->post_title .' '.$contentText);
						$ret = $tApi->addDocument($idProjet, $post['content']->post_title , $nbMots, $post['content']->post_content, 1, $_REQUEST['text_textmasterKeywords_traduction'], $_REQUEST['text_textmasterKeywordsRepeatCount_traduction']);
					//	print_r($ret);
					//	update_post_meta($post['id'], 'textmasterDocumentIdTrad', $ret['id']);
						if (is_array($ret))
						{
							if (array_key_exists('id',$ret))
							{
								update_post_meta($post['id'], 'textmasterDocumentIdTrad', $ret['id']);
								$nbDocAjouter++;
							}
							else
								$strRet .= '<li><strong>'.$post['content']->post_title.'</strong> <div class="error"> '.$ret.'</div></li>';
						}
						else
							$strRet .= '<li><strong>'.$post['content']->post_title.'</strong> <div class="error"> '.$ret.'</div></li>';
					}

					//		$result = $tApi->getProjetStatus($idProjet);

				}
			}

		}

		if ($nbDocAjouter >= 1) {
			$retLaunch = $tApi->launchProject($idProjet);
			$jsonErr = trim(substr($retLaunch, (strpos($retLaunch, '-') +1 )));
			//	print_r($jsonErr);
			$errs = json_decode($jsonErr, TRUE);

			if (@array_key_exists('errors',$errs))
			{
				if (@array_key_exists('credits',$errs['errors']))
					$strRet .= '<li><strong>'.$_POST['textmasterNomProjet'].'</strong><div class="error">'.__('Error').$errs['errors']['credits'][0].'</div></li>';
				else
					$strRet .= '<li><strong>'.$_POST['textmasterNomProjet'].'</strong><div class="error">'.__('Error').$errs['errors']['status'][0] .'</div></li>';

				update_post_meta($post['id'], 'textmasterIdTrad', '');
				update_post_meta($post['id'], 'textmasterDocumentIdTrad', '');
			}
			else{
				$strRet .= '<li><strong>'.$_POST['textmasterNomProjet'].'</strong><div class="updated">'.__('La traduction de ces articles est lancée.','textmaster').'</div></li>';
				wp_schedule_single_event( time() + 1, 'cron_syncProjets',array('in_progress') );
				syncProjets('waiting_assignment');
			//	syncProjets('in_progress');
				syncProjets('in_creation');
			}
		}



		$strRet .= '</ul>';
	}


	return $strRet;
}

?>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//include "../../../wp-load.php";
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if (!isset($_REQUEST['site']))
	$_REQUEST['site'] = '';
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
<style>
html {
	padding:0;
	margin:0;
}
</style>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel='stylesheet' href='<?php echo site_url(); ?>/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=dashicons,admin-bar,wp-admin,buttons,wp-auth-check,wp-pointer&amp;ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='thickbox-css'  href='/wp-includes/js/thickbox/thickbox.css?ver=3.4.2' type='text/css' media='all' />

<link rel='stylesheet' id='colors-css'  href='<?php echo site_url(); ?>/wp-admin/css/colors.min.css?ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='colors-css'  href='<?php echo plugins_url('textmaster.css' , __FILE__ ) ?>' type='text/css' media='all' />
<!--[if lte IE 7]>
<link rel='stylesheet' id='ie-css'  href='/wp-admin/css/ie.css?ver=3.4.2' type='text/css' media='all' />
<![endif]-->
<script type='text/javascript' src='<?php echo site_url(); ?>/wp-admin/load-scripts.php?c=0&amp;load=jquery,jquery-core,jquery-migrate,utils&amp;ver=<?php echo $wp_version; ?>'></script>
<script type='text/javascript' src='<?php echo plugins_url('textmaster.js' , __FILE__ ) ?>?ver=<?php echo get_tm_plugin_version();?>'></script>
</head>
<body style="padding:10px;margin:0;">
<form method="post" action="readproof_bulk.php" id="tm_buckform">
<?php
$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//$tApi->secretapi = get_option_tm('textmaster_api_secret');
//$tApi->keyapi = get_option_tm('textmaster_api_key');

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
		$urlAchat = 'http://' . $lang[0] . '.' . URL_TM_BOUTIQUE . '?auth_token=' . $infosUser['authentication_token'];
		$infosClient = $tApi->getUserInfos();
	}

    $categories = $tApi->getCategories();
    if (isset($categories['message']) && $categories['message'] != '') {
        _e('Merci de v&eacute;rifier vos informations de connexion à TextMaster', 'textmaster');
    }else {
    	echo '<label class="options_pricetm">'.__('Nom du projet :','textmaster').'</label>';
    	echo '<input type="text" name="textmasterNomProjet" id="textmasterNomProjet" style="width:235px;height:26px;" /><br/>' ;

        echo '<label class="options_pricetm">' . __('Categorie:', 'textmaster') . '</label>';
        echo '<select name="select_textmasterCat" id="select_textmasterCat" style="width:235px;">';

        $catSelected = get_option_tm('textmaster_readproofCategorie');

        foreach($categories as $categorie) {
            if ($catSelected == $categorie['code'])
                echo '<option value="' . $categorie['code'] . '" selected="selected">' . $categorie['value'] . '</option>';
            else
                echo '<option value="' . $categorie['code'] . '">' . $categorie['value'] . '</option>';
        }
        echo '</select><br/>';

        $languageLevels = $tApi->getLanguageLevels();

        echo '<label class="options_pricetm">' . __('Niveau de service:', 'textmaster') . '</label>';
        echo '<select id="select_textmasterReadProofLanguageLevel" name="select_textmasterReadProofLanguageLevel" style="width:235px;">';

        $languageLevelSelected = get_option_tm('textmaster_readproofLanguageLevel');

        foreach($languageLevels['readproof'] as $key => $languageLevel) {
            if ($languageLevelSelected == $languageLevel["name"])
                echo '<option value="' . $languageLevel["name"] . '" selected="selected">' . $languageLevel["name"] . '</option>';
            else
                echo '<option value="' . $languageLevel["name"] . '">' . $languageLevel["name"] . '</option>';
        }
        echo '</select>';
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBaseReadproof">NC</strong> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'];
    	echo '</span><br/>';

        $languages = $tApi->getLanguages();

        echo '<label class="options_pricetm">' . __('Langue :', 'textmaster') . '</label>';
        echo '<select id="select_textmasterReadProofLang" name="select_textmasterReadProofLang" style="width:235px;">';

        $languageSelected = get_option_tm('textmaster_readproofLanguage');

        foreach($languages as $language) {
            if ($languageSelected == $language['code'])
                echo '<option value="' . $language['code'] . '" selected="selected">' . $language['value'] . '</option>';
            else
                echo '<option value="' . $language['code'] . '">' . $language['value'] . '</option>';
        }
        echo '</select><br/>';

        $textmaster_qualityRedaction = get_option_tm('textmaster_qualityReadproof');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_qualityRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Contrôle qualité :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="true" ' . $chkYes . ' /> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityReadProof">NC</strong> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'];
    	echo '</span><br/>';

        $textmaster_expertiseRedaction = get_option_tm('textmaster_expertiseReadproof');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_expertiseRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

    //    echo '<label class="options_pricetm">' . __('Expertise :', 'textmaster') . '</label> ';
     //   echo '<input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="true" ' . $chkYes . ' /> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
   // 	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseReadproof">NC</strong> ';
    //	if (is_array($infosClient))
    //		echo $infosClient['wallet']['currency_code'];
    //	echo '</span><br/>';

        $textmaster_priorityRedaction = get_option_tm('textmaster_priorityReadproof');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_priorityRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Commande prioritaire :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterPriorityReadproof" class="radio_textmasterPriorityReadproof" value="true" ' . $chkYes . '/> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterPriorityReadproof" class="radio_textmasterPriorityReadproof" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
    	echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriorityReadproof">NC</strong> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'];
    	echo '</span><br/>';

//		wp_texmaster_readproof_options_metaboxes();
    	wp_texmaster_readproof_authors_metaboxes($tApi);

   		$textmasterBriefing_readproof = get_option_tm('textmaster_readproofBriefing');

    	echo '<br/><label>'.__('Briefing :','textmaster').'</label><br/>';
    	echo '<textarea style="width:620px;height:70px;" name="text_textmasterBriefing_readproof" id="text_textmasterBriefing_readproof">'.$textmasterBriefing_readproof.'</textarea><br/>';

    	// secu si pas assez de crédits
    	$disabled = '';
    	$prixTotal = $tApi->getPricings($aInfosPost['totalWords']);
    	if (is_array($infosClient) && $prixTotal > $infosClient['wallet']['current_money'])
    		$disabled = 'disabled=disabled';

        echo '<br/><img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckReadProof" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckReadProof">'.__('Merci de patienter','textmaster').'</div><div id="publishing-action"><input name="save" type="submit" class="button button-highlighted" id="bulk_readproof" tabindex="5" accesskey="p" value="' . __('Relecture', 'textmaster') . '" '.$disabled.' onclick="jQuery(\'.ajax-loading-tmBluckReadProof\').show();"></div>';
        echo '<div style="clear:both"></div><div id="resultTextmaster" class="tmInfos">' . $txtRet . '</div>';

    	echo '<div style="font-weight:bold;">';
        echo '<div class="tmInfos">' . __('Coût', 'textmaster') . ' : <span id="priceTextmasterReadProof">NC</span> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'];
    	echo ' (<span class="nbMots"></span> '.__('mots','textmaster').')</div><div class="tmInfos">'.__('Crédits:', 'textmaster').' <span class="walletTextmaster">';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['current_money'];
    	echo '</span> ';
    	if (is_array($infosClient))
    		$infosClient['wallet']['currency_code'];
    	echo ' <a href="' . $urlAchat . '" target="_blank">' . __('Créditer mon compte', 'textmaster') . '</a></div>';
       	echo '</div>';

		echo '<input type="hidden" name="forceWordsCount" id="forceWordsCount" value="'.$aInfosPost['totalWords'].'"/>';
    	echo '<input type="hidden" name="post_ids" id="post_ids" value="'.$_REQUEST['post_ids'].'"/>';
        echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; getPrice("readproof",'.$aInfosPost['totalWords'].');</script>';
    }
}

?>
</form>
</body>
</html>
<?php

// pour envoyer les infos a TextMaster
function launchReadproof($aInfosPost){
	global $tApi;

	$textmasterQualityReadproof = '';

	if (!isset($_POST['radio_textmasterExpertiseReadproof']))
		$_POST['radio_textmasterExpertiseReadproof'] = '';
	if (!isset($_POST['select_textmasterVocabularyType_readproof']))
		$_POST['select_textmasterVocabularyType_readproof'] = '';
	if (!isset($_POST['select_textmasterGrammaticalPerson_readproof']))
		$_POST['select_textmasterGrammaticalPerson_readproof'] = '';
	if (!isset($_POST['select_textmasterTargetReaderGroup_readproof']))
		$_POST['select_textmasterTargetReaderGroup_readproof'] = '';
	if (!isset($_POST['text_textmasterKeywords_readproof']))
		$_POST['text_textmasterKeywords_readproof'] = '';
	if (!isset($_POST['text_textmasterKeywordsRepeatCount_readproof']))
		$_POST['text_textmasterKeywordsRepeatCount_readproof'] = '';
	if (!isset($_POST['text_textmasterKeywords_readproof']))
		$_POST['text_textmasterKeywords_readproof'] = '';


	// on créer le projet
	if ($_POST['textmasterNomProjet'] != '')
		$retProjet = $tApi->makeProject($_POST['textmasterNomProjet'], 'proofreading', $_POST['select_textmasterReadProofLang'], $_POST['select_textmasterReadProofLang'], $_POST['select_textmasterCat'], $_POST['text_textmasterBriefing_readproof'], $_POST['select_textmasterReadProofLanguageLevel'], $_POST['radio_textmasterQualityReadproof'], $_POST['radio_textmasterExpertiseReadproof'], $_POST['radio_textmasterPriorityReadproof'],'',  $_POST['select_textmasterVocabularyType_readproof'], $_POST['select_textmasterGrammaticalPerson_readproof'], $_POST['select_textmasterTargetReaderGroup_readproof'], $_POST['check_textmasterAuthorReadproof']);
	else
		$retProjet = $tApi->makeProject('WordPress -'. date('Y-m-d'),  'proofreading', $_POST['select_textmasterReadProofLang'], $_POST['select_textmasterReadProofLang'], $_POST['select_textmasterCat'], $_POST['text_textmasterBriefing_readproof'], $_POST['select_textmasterReadProofLanguageLevel'], $_POST['radio_textmasterQualityReadproof'], $_POST['radio_textmasterExpertiseReadproof'], $_POST['radio_textmasterPriorityReadproof'],'',  $_POST['select_textmasterVocabularyType_readproof'], $_POST['select_textmasterGrammaticalPerson_readproof'], $_POST['select_textmasterTargetReaderGroup_readproof'], $_POST['check_textmasterAuthorReadproof']);

	if (is_array($retProjet))
		$idProjet = $retProjet['id'];
	else
		$strRet .= '<li><div class="error">'.__('Erreur lors de la création de votre projet' ,'textmaster').' ('.$retProjet.')</div></li>';

	$nbDocAjouter = 0;

	if ($idProjet != '') {

		$strRet = '<ul>';
		foreach ($aInfosPost as $post) {
			if (is_object($post['content']) && $post['content']->ID != '') {
				$idTm = get_post_meta_tm($post['content']->ID, 'textmasterId', true, $_REQUEST['site']);
				if ($idTm != '') {
					$strRet .= '<li><div class="error"><strong>'.$post['content']->post_title.'</strong> '.__('La relecture de ces articles est déjà', 'textmaster').' : '.$tApi->getLibStatus($tApi->getProjetStatus($idTm)).'</div></li>';
				}
				else {
					update_post_meta($post['id'], 'textmasterCategorie',				$_POST['select_textmasterCat']);
					update_post_meta($post['id'], 'textmasterReadProofLanguageLevel',	$_POST['select_textmasterReadProofLanguageLevel']);
					update_post_meta($post['id'], 'textmasterReadProofLang',			$_POST['select_textmasterReadProofLang']);

					if (isset($_POST['radio_textmasterQualityReadproof']))
						update_post_meta($post['id'], 'textmaster_qualityReadproof',		$_POST['radio_textmasterQualityReadproof']);
					if (isset($_POST['radio_textmasterExpertiseReadproof']))
						update_post_meta($post['id'], 'textmaster_expertiseReadproof',		$_POST['radio_textmasterExpertiseReadproof']);
					if (isset($_POST['radio_textmasterPriorityReadproof']))
						update_post_meta($post['id'], 'textmaster_priorityReadproof',		$_POST['radio_textmasterPriorityReadproof']);

					update_post_meta($post['id'], 'textmaster_BriefingReadproof',		$_POST['text_textmasterBriefing_readproof']);

					if (isset($_POST['text_textmasterKeywords_readproof']))
						update_post_meta($post['id'], 'textmasterKeywords_readproof', $_POST['text_textmasterKeywords_readproof']);
					if (isset($_POST['text_textmasterKeywordsRepeatCount_readproof']))
						update_post_meta($post['id'], 'textmasterKeywordsRepeatCount_readproof', $_POST['text_textmasterKeywordsRepeatCount_readproof']);
					if (isset($_POST['select_textmasterVocabularyType_readproof']))
						update_post_meta($post['id'], 'textmasterVocabularyType_readproof', $_POST['select_textmasterVocabularyType_readproof']);
					if (isset($_POST['select_textmasterGrammaticalPerson_readproof']))
						update_post_meta($post['id'], 'textmasterGrammaticalPerson_readproof', $_POST['select_textmasterGrammaticalPerson_readproof']);
					if (isset($_POST['select_textmasterTargetReaderGroup_readproof']))
						update_post_meta($post['id'], 'textmasterTargetReaderGroup_readproof', $_POST['select_textmasterTargetReaderGroup_readproof']);

					if (isset($_POST['check_textmasterAuthorReadproof']))
						update_post_meta($post['id'], 'textmasterReadProofAuthor', serialize($_POST['check_textmasterAuthorReadproof']));

					if (is_array($retProjet) ||  $idProjet != '')
					{
						//	$ret = serialize($ret);
						update_post_meta($post['id'], 'textmasterId', $idProjet);
						$contentText = cleanWpTxt( $post['content']->post_content);
						$fullContent = '';

						if( checkInstalledPlugin('Advanced Custom Fields')) {
							$fields = getExtrasFields($post['content']->ID, '', $_REQUEST['site']);
							if (count($fields) != 0) {
								foreach ( $fields as $name => $param) {
									if ($name != 'acf_nonce' && trim($param) != ''){
										$arrayDocs[$nbDocAjouter]['original_content'][$name]["original_phrase"] = $param;
										$fullContent .= cleanWpTxt( $param );
									}

								}
							}

							$arrayDocs[$nbDocAjouter]['original_content']['content']["original_phrase"] = $post['content']->post_content;
							if ($post['content']->post_excerpt != ''){
								$arrayDocs[$nbDocAjouter]['original_content']["post_excerpt"]["original_phrase"] = $post['content']->post_excerpt;
								$fullContent .= cleanWpTxt( $post['content']->post_excerpt );
							}
							$nbMots = $tApi->countWords( $post['content']->post_title .' '.$contentText.' '.$fullContent);
						}

						if(  checkInstalledPlugin('Meta Box')) {
							$fields = getExtrasFields($post['content']->ID, 'metabox', $_REQUEST['site']);
							if (count($fields) != 0) {
								foreach ( $fields as $name => $param) {
									if (is_string($param) && trim($param) != ''){
										$arrayDocs[$nbDocAjouter]['original_content'][$name]["original_phrase"] = $param;
										$fullContent .= cleanWpTxt( $param );
									}

								}
							}

							$arrayDocs[$nbDocAjouter]['original_content']['content']["original_phrase"] = $post['content']->post_content;
							if ($post['content']->post_excerpt != ''){
								$arrayDocs[$nbDocAjouter]['original_content']["post_excerpt"]["original_phrase"] = $post['content']->post_excerpt;
								$fullContent .= cleanWpTxt( $post['content']->post_excerpt );
							}
							$nbMots = $tApi->countWords( $post['content']->post_title .' '.$contentText.' '.$fullContent);
						}

						if( !checkInstalledPlugin('Advanced Custom Fields') && !checkInstalledPlugin('Meta Box')) {
							if ($post['content']->post_excerpt != ''){
								$arrayDocs[$nbDocAjouter]['original_content']['content']["original_phrase"] = $post['content']->post_content;
								$arrayDocs[$nbDocAjouter]['original_content']["post_excerpt"]["original_phrase"] = $post['content']->post_excerpt;
								$contentText = cleanWpTxt( $post['content']->post_excerpt ).' '.cleanWpTxt( $post['content']->post_content );
							}
							else
								$arrayDocs[$nbDocAjouter]['original_content'] = $post['content']->post_content;
							//$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
							$nbMots = $tApi->countWords(  $post['content']->post_title .' '.$contentText);
					//		$arrayDocs[$nbDocAjouter]['original_content'] = $post['content']->post_content;
						}

						//$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
						$nbMots = $tApi->countWords(  $post['content']->post_title .' '.$contentText);
						$arrayDocs[$nbDocAjouter]['title'] = $post['content']->post_title;
						$arrayDocs[$nbDocAjouter]['word_count'] = $nbMots;
					//	$arrayDocs[$nbDocAjouter]['original_content'] = $post['content']->post_content;
						$arrayDocs[$nbDocAjouter]['word_count_rule'] = 1;
						$arrayDocs[$nbDocAjouter]['keyword_list'] = $_POST['text_textmasterKeywords_readproof'];
						$arrayDocs[$nbDocAjouter]['keywords_repeat_count'] = $_POST['text_textmasterKeywordsRepeatCount_readproof'];

						$arrayDocsId[$nbDocAjouter]['id'] = $post['id'];
						$arrayDocsId[$nbDocAjouter]['title'] = $post['content']->post_title;
				/*		$ret = $tApi->addDocument($idProjet, $arrayDocs );
				//		print_r($ret);
						if (is_array($ret))
						{
							if (array_key_exists('id',$ret)){
								update_post_meta($post['id'], 'textmasterDocumentId', $ret['id']);
								$nbDocAjouter++;
							}
							else
								$strRet .= '<li><strong>'.$post['content']->post_title.'</strong><div class="error"> '.$ret.'</div></li>';
						}
						else
							$strRet .= '<li><strong>'.$post['content']->post_title.'</strong><div class="error"> '.$ret.'</div></li>';
*/						$nbDocAjouter++;
					}


				}
			}

		}

		if (isset($arrayDocs) && count($arrayDocs) != 0) {
			$retTotalDocs = $tApi->addDocument($idProjet, $arrayDocs );
			if (count($arrayDocs) == 1)
				$retTotal[0] = $retTotalDocs;
			else
				$retTotal = $retTotalDocs;
			//		print_r($ret);
			//	update_post_meta($post['id'], 'textmasterDocumentIdTrad', $ret['id']);
			if (is_array($retTotal))
			{
				foreach ($retTotal as $id => $ret) {
					if (is_array($ret) && array_key_exists('id',$ret))
					{
						update_post_meta($arrayDocsId[$id]['id'], 'textmasterDocumentId', $ret['id']);
									//		update_post_meta($post['id'], 'tm_lang', $_POST['select_textmasterLangDestination']);
						$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong><div class="updated">'.__('La relecture de ces articles est lancée.','textmaster').'</div></li>';
						//	$nbDocAjouter++;
					}
					else
						$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong> <div class="error"> '.$ret.'</div></li>';
				}
			}
			else if (is_object($post['content']))
				$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong> <div class="error"> '.$ret.'</div></li>';

		}
		if ($nbDocAjouter >= 1) {
			$retLaunch = $tApi->launchProject($idProjet);
			$jsonErr = trim(substr($retLaunch, (strpos($retLaunch, '-') +1 )));
			//	print_r($jsonErr);
			$errs = json_decode($jsonErr, TRUE);

			//		$result = $tApi->getProjetStatus($idProjet);

			if (@array_key_exists('errors',$errs))
			{
				if (@array_key_exists('credits',$errs['errors']))
					$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong><div class="error">'.__('Error').$errs['errors']['credits'][0].'</div></li>';
				else if (@array_key_exists('status',$errs['errors']))
					$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong><div class="error">'.__('Error').$errs['errors']['status'][0] .'</div></li>';

				update_post_meta($post['id'], 'textmasterId', '');
				update_post_meta($post['id'], 'textmasterDocumentId', '');
			}
			else{
			//
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('in_progress', 1) );
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('waiting_assignment', 1) );
			//	syncProjets('waiting_assignment');
				//syncProjets('in_progress');
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('in_creation', 1) );
			//	syncProjets('in_creation');
			}
		}

		$strRet .= '</ul>';
	}
	$strRet .= '<script>';
	$strRet .= "jQuery(document).ready(function($) {\n";
	//		$str .= "alert(jQuery('#TB_closeWindowButton', window.parent.document).length);\n";
	$strRet .= "jQuery('#TB_closeWindowButton', window.parent.document).click( function () {\n";
	//		$str .= "	alert('reload');\n";
	$strRet .= "	window.parent.location.reload();\n";
	$strRet .= "	return false; });\n";
	$strRet .= "});\n";
	$strRet .= '</script>';

	return $strRet;
}

?>
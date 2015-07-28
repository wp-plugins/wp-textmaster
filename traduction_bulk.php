<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//include "../../../wp-load.php";
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once('confs_path.php');
if (defined('PATH_WP_LOAD') && PATH_WP_LOAD != '')
	$uri_load = PATH_WP_LOAD;
else{
	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	$uri_load = $parse_uri[0];
}
require_once( $uri_load . 'wp-load.php' );

if (!isset($_REQUEST['type']) || $_REQUEST['type'] == 'post')
	$_REQUEST['type'] = '';
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

$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//$tApi->secretapi = get_option_tm('textmaster_api_secret');
//$tApi->keyapi = get_option_tm('textmaster_api_key');
//var_dump($_REQUEST);
$aInfosPost = getInfosPosts($_REQUEST['post_ids'], $_REQUEST['type'], $_REQUEST['site']);
if (count($_POST)!= 0 && $aInfosPost != FALSE) {
	echo launchTraduction($aInfosPost, $_REQUEST['site']);
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
    	echo '<input type="text" name="textmasterNomProjet" id="textmasterNomProjet" style="width:235px;height:26px;" />' ;

		echo '<br/>' ;
        echo '<label class="options_pricetm">' . __('Categorie:', 'textmaster') . '</label>';
        echo '<select name="select_textmasterCatTrad" id="select_textmasterCatTrad" style="width:235px;height:26px;">';

        $catSelected = get_option_tm('textmaster_traductionCategorie');

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

        $languageLevelSelected = get_option_tm('textmaster_traductionLanguageLevel');

        foreach($languageLevels['traduction'] as $key => $languageLevel) {
            if ($languageLevelSelected == $languageLevel["name"])
                echo '<option value="' . $languageLevel["name"] . '" selected="selected">' . $languageLevel["name"] . '</option>';
            else
                echo '<option value="' . $languageLevel["name"] . '">' . $languageLevel["name"] . '</option>';
        }
        echo '</select>';
   		echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBaseTrad">NC</strong> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'];
    	echo '</span><br/>';

        $languages = $tApi->getLanguages();

        echo '<label class="options_pricetm">' . __('Langue d\'origine :','textmaster') . '</label>';
        echo '<select id="select_textmasterLangOrigine" name="select_textmasterLangOrigine" style="width:235px;height:26px;">';

        $languageSelected = get_option_tm('textmaster_traductionLanguageSource');

        foreach($languages as $language) {
            if ($languageSelected == $language['code'])
                echo '<option value="' . $language['code'] . '" selected="selected">' . $language['value'] . '</option>';
            else
                echo '<option value="' . $language['code'] . '">' . $language['value'] . '</option>';
        }
        echo '</select><br/>';

    	echo '<label class="options_pricetm">' . __('Traduction en :','textmaster') . '</label>';
    	echo '<select id="select_textmasterLangDestination" name="select_textmasterLangDestination" style="width:235px;height:26px;">';

    	$languageSelected = get_option_tm('textmaster_traductionLanguageDestination');

    	foreach($languages as $language) {
    		if ($languageSelected == $language['code'])
    			echo '<option value="' . $language['code'] . '" selected="selected">' . $language['value'] . '</option>';
    		else
    			echo '<option value="' . $language['code'] . '">' . $language['value'] . '</option>';
    	}
    	echo '</select><br/>';


        $textmaster_qualityRedaction = get_option_tm('textmaster_qualityTraduction');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_qualityRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Contrôle qualité :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="true" ' . $chkYes . ' /> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
   		echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQualityTrad">NC</strong> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'] ;
    	echo '</span><br/>';

        $textmaster_expertiseRedaction = get_option_tm('textmaster_expertiseTraduction');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_expertiseRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

   //     echo '<label class="options_pricetm">' . __('Expertise :', 'textmaster') . '</label> ';
//        echo '<input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="true" ' . $chkYes . ' /> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
//   		echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertiseTrad">NC</strong> ';
//    	if (is_array($infosClient))
//    		echo $infosClient['wallet']['currency_code'];
//    	echo '</span><br/>';

        $textmaster_priorityRedaction = get_option_tm('textmaster_priorityTraduction');
        $chkNo = '';
        $chkYes = '';
        if ($textmaster_priorityRedaction == "false")
            $chkNo = 'checked="checked"';
        else
            $chkYes = 'checked="checked"';

        echo '<label class="options_pricetm">' . __('Commande prioritaire :', 'textmaster') . '</label> ';
        echo '<input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="true" ' . $chkYes . '/> ' . __('Oui','textmaster') . ' <input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="false" ' . $chkNo . '/> ' . __('Non','textmaster') ;
   		echo '<span class="coutOptionTm">'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriorityTrad">NC</strong> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'] ;
    	echo '</span><br/>';

		if ($_REQUEST['type'] == '') {
		//	wp_texmaster_traduction_options_metaboxes();
			wp_texmaster_traduction_authors_metaboxes($tApi);
		}

    	$textmasterBriefing_traduction = get_option_tm('textmaster_traductionBriefing');
		echo '<br/><label>'.__('Briefing :','textmaster').'</label><br/>';
    	echo '<textarea style="width:620px;height:70px;" name="text_textmasterBriefing_traduction" id="text_textmasterBriefing_traduction">'.$textmasterBriefing_traduction.'</textarea><br/>';

    	// secu si pas assez de crédits
    	$disabled = '';
		$prixTotal = $tApi->getPricings($aInfosPost['totalWords']);
    	if (is_array($infosClient) && $prixTotal > $infosClient['wallet']['current_money'])
    		$disabled = 'disabled=disabled';

        echo '<br/><img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckTransalte" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckTransalte">'.__('Merci de patienter','textmaster').'</div><div id="publishing-action"><input name="save" type="submit" class="button button-highlighted" id="bulk_readproof" tabindex="5" accesskey="p" value="' . __('Traduction','textmaster') . '" '.$disabled.' onclick="jQuery(\'.ajax-loading-tmBluckTransalte\').show();"></div>';
        echo '<div style="clear:both"></div><div id="resultTextmaster" class="tmInfos">' . $txtRet . '</div>';

    	echo '<div style="font-weight:bold;">';
    	echo '<div class="tmInfos">' . __('Coût', 'textmaster') . ' : <span id="priceTextmasterTrad">NC</span> ';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'] ;
    	echo ' (<span class="nbMots"></span> '.__('mots','textmaster','textmaster').')</div><div class="tmInfos">'.__('Crédits:', 'textmaster').' <span class="walletTextmaster">';
    	if (is_array($infosClient))
    		echo $infosClient['wallet']['current_money'];
    	echo '</span> ';
    	if(is_array($infosClient))
    		echo $infosClient['wallet']['currency_code'];
    	echo ' <a href="' . $urlAchat . '" target="_blank">' . __('Créditer mon compte', 'textmaster') . '</a></div>';
    	echo '</div>';

    	echo '<input type="hidden" name="forceWordsCount" id="forceWordsCount" value="'.$aInfosPost['totalWords'].'"/>';
    	echo '<input type="hidden" name="post_ids" id="post_ids" value="'.$_REQUEST['post_ids'].'"/>';
    	if (isset($_REQUEST['type']))
    		echo '<input type="hidden" name="type" id="type" value="'.$_REQUEST['type'].'"/>';
    	if (isset($_REQUEST['site']))
    		echo '<input type="hidden" name="site" id="site" value="'.$_REQUEST['site'].'"/>';
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
function launchTraduction($aInfosPost, $idSite){
	global $tApi, $nbDocAjouter;

	$idProjetTitles = null;
	$retProjetTitles = null;
	$strRet = '';
	$idProjet = '';

	if (isset($idSite) && $idSite > 1)
		$ret = switch_to_blog( $idSite );

	if (!isset($_POST['radio_textmasterQualityTraduction']))
		$_POST['radio_textmasterQualityTraduction'] = get_option_tm('textmaster_qualityTraduction');
	if (!isset($_POST['select_textmasterVocabularyType_traduction']))
		$_POST['select_textmasterVocabularyType_traduction'] = get_option_tm('textmaster_traductionVocabularyType');
	if (!isset($_POST['select_textmasterGrammaticalPerson_traduction']))
		$_POST['select_textmasterGrammaticalPerson_traduction'] = get_option_tm('textmaster_traductionGrammaticalPerson');
	if (!isset($_POST['select_textmasterTargetReaderGroup_traduction']))
		$_POST['select_textmasterTargetReaderGroup_traduction'] = get_option_tm('textmaster_traductionTargetReaderGroup');
	if (!isset($_POST['check_textmasterAuthorTraduction']))
		$_POST['check_textmasterAuthorTraduction'] = '';
	if (!isset($_POST['radio_textmasterExpertiseTraduction']))
		$_POST['radio_textmasterExpertiseTraduction'] = '';
	if (!isset($_POST['text_textmasterKeywords_traduction']))
		$_POST['text_textmasterKeywords_traduction'] = '';
	if (!isset($_POST['text_textmasterKeywordsRepeatCount_traduction']))
		$_POST['text_textmasterKeywordsRepeatCount_traduction'] = '';


	// on créer le projet
	if ($_POST['textmasterNomProjet'] != ''){
//		print_r($aInfosPost);
		if (checkEmptyContent($aInfosPost, $_POST['type']))
			$retProjet = $tApi->makeProject($_POST['textmasterNomProjet'].' - '.__('title only'), 'translation', $_POST['select_textmasterLangOrigine'], $_POST['select_textmasterLangDestination'], $_POST['select_textmasterCatTrad'], $_POST['text_textmasterBriefing_traduction'], $_POST['select_textmasterTradLanguageLevel'], $_POST['radio_textmasterQualityTraduction'], $_POST['radio_textmasterExpertiseTraduction'], $_POST['radio_textmasterPriorityTraduction'],'1_title' ,  $_POST['select_textmasterVocabularyType_traduction'], $_POST['select_textmasterGrammaticalPerson_traduction'], $_POST['select_textmasterTargetReaderGroup_traduction'], $_POST['check_textmasterAuthorTraduction']);
		else
			$retProjet = $tApi->makeProject($_POST['textmasterNomProjet'], 'translation', $_POST['select_textmasterLangOrigine'], $_POST['select_textmasterLangDestination'], $_POST['select_textmasterCatTrad'], $_POST['text_textmasterBriefing_traduction'], $_POST['select_textmasterTradLanguageLevel'], $_POST['radio_textmasterQualityTraduction'], $_POST['radio_textmasterExpertiseTraduction'], $_POST['radio_textmasterPriorityTraduction'],'' ,  $_POST['select_textmasterVocabularyType_traduction'], $_POST['select_textmasterGrammaticalPerson_traduction'], $_POST['select_textmasterTargetReaderGroup_traduction'], $_POST['check_textmasterAuthorTraduction']);
	}
	else{
		if (checkEmptyContent($aInfosPost, $_POST['type']))
			$retProjet = $tApi->makeProject('WordPress -'. date('Y-m-d').' - '.__('title only'), 'translation', $_POST['select_textmasterLangOrigine'], $_POST['select_textmasterLangDestination'], $_POST['select_textmasterCatTrad'], $_POST['text_textmasterBriefing_traduction'], $_POST['select_textmasterTradLanguageLevel'], $_POST['radio_textmasterQualityTraduction'], $_POST['radio_textmasterExpertiseTraduction'], $_POST['radio_textmasterPriorityTraduction'],'1_title' ,  $_POST['select_textmasterVocabularyType_traduction'], $_POST['select_textmasterGrammaticalPerson_traduction'], $_POST['select_textmasterTargetReaderGroup_traduction'], $_POST['check_textmasterAuthorTraduction']);
		else
			$retProjet = $tApi->makeProject('WordPress -'. date('Y-m-d'), 'translation', $_POST['select_textmasterLangOrigine'], $_POST['select_textmasterLangDestination'], $_POST['select_textmasterCatTrad'], $_POST['text_textmasterBriefing_traduction'], $_POST['select_textmasterTradLanguageLevel'], $_POST['radio_textmasterQualityTraduction'], $_POST['radio_textmasterExpertiseTraduction'], $_POST['radio_textmasterPriorityTraduction'],'' ,  $_POST['select_textmasterVocabularyType_traduction'], $_POST['select_textmasterGrammaticalPerson_traduction'], $_POST['select_textmasterTargetReaderGroup_traduction'], $_POST['check_textmasterAuthorTraduction']);
	}


	if (is_array($retProjet)){
		$idProjet = $retProjet['id'];
		//$idProjetTitles = $retProjetTitles['id'];
	}
	else
		$strRet .= '<div class="error">'.__('Erreur lors de la création de votre projet' ,'textmaster').' ('.print_r($retProjet, TRUE).')</div>';

	$nbDocAjouter = 0;

	if ($idProjet != '') {
		$strRet = '<ul>';
	/*	foreach ($aInfosPost as $post) {
			if ($_POST['type'] != '') {
				if (is_object($post['content']))
					$strRet = addDocsTags($post, $idProjet);

			}else {
				if ($post['content']->ID != '')
					$strRet = addDocsPosts($post, $idProjet);
			}
		}*/
		if ($_POST['type'] != '') {
			$strRet = addDocsTags($aInfosPost, $idProjet);
		}else {
			$strRet = addDocsPosts($aInfosPost, $idProjet);
		}



		if ($nbDocAjouter >= 1) {
			$retLaunch = $tApi->launchProject($idProjet);
		//	if ($idProjetTitles != null)
		//		$retLaunchTitle = $tApi->launchProject($idProjetTitles);
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
			//	$strRet .= '<li><strong>'.$_POST['textmasterNomProjet'].'</strong><div class="updated">'.__('La traduction de ces articles est lancée.','textmaster').'</div></li>';
				wp_schedule_single_event( time() + 1, 'cron_syncProjets',array('in_progress', 1) );
				wp_schedule_single_event( time() + 1, 'cron_syncProjets',array('waiting_assignment', 1) );
			//	syncProjets('waiting_assignment');
			//	syncProjets('in_progress');
				wp_schedule_single_event( time() + 1, 'cron_syncProjets',array('in_creation', 1) );
			//	syncProjets('in_creation');
			}
		}



		$strRet .= '</ul>';
	}

	if (isset($idSite) && $idSite> 1)
		restore_current_blog();

	return $strRet;
}

function addDocsPosts($aInfosPost, $idProjet){
	global $tApi, $strRet, $nbDocAjouter, $wpdb;

	if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
		$table_name = $wpdb->base_prefix . $_REQUEST['site']. "_postmeta";
	else
		$table_name = $wpdb->prefix . "postmeta";

//	$strRet = '';
	foreach ($aInfosPost as $post) {
		if (is_object($post['content'])){
			$idTm = get_IdProjetTrad($post['content']->ID, $_POST['select_textmasterLangDestination'], 'post', $table_name);
			$idTrad = get_IdTrad( $post['content']->ID, $_POST['select_textmasterLangDestination'], 'post', $table_name);

			if ($idTrad != '')
				$strRet .= '<li><div class="error"><strong>'.$post['content']->post_title.'</strong> '.__('La traduction de ces articles est déjà','textmaster').' : '.__('Fait', 'textmaster').'</div></li>';
			else if ($idTm != '')
				$strRet .= '<li><div class="error"><strong>'.$post['content']->post_title.'</strong> '.__('La traduction de ces articles est déjà','textmaster').' : '.$tApi->getLibStatus($tApi->getProjetStatus($idTm)).'</div></li>';
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
					update_post_meta($post['content']->ID, 'textmasterTraductionAuthor',serialize($_POST['check_textmasterAuthorTraduction']));

				if ($idProjet != '')
				{
					//	$ret = serialize($ret);
					$metaProjet = get_post_meta($post['content']->ID, 'textmasterIdTrad', true );
					if ($metaProjet != '' )
						update_post_meta($post['content']->ID, 'textmasterIdTrad', $metaProjet.';'.$idProjet.'='.$_POST['select_textmasterLangDestination']);
					else
						update_post_meta($post['content']->ID, 'textmasterIdTrad', $idProjet.'='.$_POST['select_textmasterLangDestination']);
					//		update_post_meta($post['id'], 'textmasterIdTrad', $idProjet.'='.$_POST['select_textmasterLangDestination']);

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

						$arrayDocs[$nbDocAjouter]['original_content']['content']["title"] = $post['content']->post_title;
						$arrayDocs[$nbDocAjouter]['original_content']["title"]['original_phrase'] = $post['content']->post_title;
						$arrayDocs[$nbDocAjouter]['original_content']['content']["original_phrase"] = $post['content']->post_content;

						if ($post['content']->post_excerpt != ''){
							$arrayDocs[$nbDocAjouter]['original_content']["post_excerpt"]["original_phrase"] = $post['content']->post_excerpt;
							$fullContent .= cleanWpTxt( $post['content']->post_excerpt );
						}
						$nbMots = $tApi->countWords( $post['content']->post_title .' '.$contentText.' '.$fullContent);
					}

					if(  checkInstalledPlugin('Meta Box')) {
						$fields = getExtrasFields($post['content']->ID, 'metabox', $_REQUEST['site']);
				//		var_dump($fields);
						if (count($fields) != 0) {
							foreach ( $fields as $name => $param) {
								if (is_string($param) && trim($param) != ''){
									$arrayDocs[$nbDocAjouter]['original_content'][$name]["original_phrase"] = $param;
									$fullContent .= cleanWpTxt( $param );
								}

							}
						}

						$arrayDocs[$nbDocAjouter]['original_content']['content']["title"] = $post['content']->post_title;
						$arrayDocs[$nbDocAjouter]['original_content']["title"]['original_phrase'] = $post['content']->post_title;
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
							$arrayDocs[$nbDocAjouter]['original_content']["title"]['original_phrase'] = $post['content']->post_title;
							$arrayDocs[$nbDocAjouter]['original_content']["post_excerpt"]["original_phrase"] = $post['content']->post_excerpt;
							$contentText = cleanWpTxt( $post['content']->post_excerpt ).' '.cleanWpTxt( $post['content']->post_content );
						}
						else
							$arrayDocs[$nbDocAjouter]['original_content'] = $post['content']->post_content;
						//$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
						$nbMots = $tApi->countWords(  $post['content']->post_title .' '.$contentText);
					//	$arrayDocs[$nbDocAjouter]['original_content'] = $post['content']->post_content;
					}



					if (trim($contentText) == '') {
						$arrayDocs[$nbDocAjouter]['title'] = $post['content']->post_title;
						$arrayDocs[$nbDocAjouter]['word_count'] = $nbMots;

						$arrayDocs[$nbDocAjouter]['word_count_rule'] = 1;
						$arrayDocs[$nbDocAjouter]['keyword_list'] =  $_POST['text_textmasterKeywords_traduction'];
						$arrayDocs[$nbDocAjouter]['keywords_repeat_count'] = $_POST['text_textmasterKeywordsRepeatCount_traduction'];
						//		$ret = $tApi->addDocument($idProjet, $arrayDocs );
					}
					else {
						$arrayDocs[$nbDocAjouter]['title'] = $post['content']->post_title;
						$arrayDocs[$nbDocAjouter]['word_count'] = $nbMots;
				//		$arrayDocs[$nbDocAjouter]['original_content'] = $post['content']->post_content;
						$arrayDocs[$nbDocAjouter]['word_count_rule'] = 1;
						$arrayDocs[$nbDocAjouter]['keyword_list'] =  $_POST['text_textmasterKeywords_traduction'];
						$arrayDocs[$nbDocAjouter]['keywords_repeat_count'] = $_POST['text_textmasterKeywordsRepeatCount_traduction'];
						//		$ret = $tApi->addDocument($idProjet, $arrayDocs );
					}

					$arrayDocsId[$nbDocAjouter]['id'] = $post['content']->ID;
					$arrayDocsId[$nbDocAjouter]['title'] = $post['content']->post_title;

					$nbDocAjouter++;
				}
			}
		}
	}

	if (isset($arrayDocs) && count($arrayDocs) != 0) {
//		var_dump($arrayDocs);
//		die();
		$retTotalDocs = $tApi->addDocument($idProjet, $arrayDocs );
		if (count($arrayDocs) == 1)
			$retTotal[0] = $retTotalDocs;
		else
			$retTotal = $retTotalDocs;


//		var_dump($retTotal);
		//	update_post_meta($post['id'], 'textmasterDocumentIdTrad', $ret['id']);
		if (is_array($retTotal))
		{
			foreach ($retTotal as $id => $ret) {
				if (is_array($ret) && array_key_exists('id',$ret))
				{
					$metaDoc = get_post_meta($arrayDocsId[$id]['id'], 'textmasterDocumentIdTrad', true );
					if ($metaDoc != '')
						update_post_meta($arrayDocsId[$id]['id'], 'textmasterDocumentIdTrad',$metaDoc.';'. $ret['id'].'='.$_POST['select_textmasterLangDestination']);
					else
						update_post_meta($arrayDocsId[$id]['id'], 'textmasterDocumentIdTrad', $ret['id'].'='.$_POST['select_textmasterLangDestination']);
					//		update_post_meta($post['id'], 'tm_lang', $_POST['select_textmasterLangDestination']);
					//	$nbDocAjouter++;
					$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong><div class="updated">'.__('La traduction de ces articles est lancée.','textmaster').'</div></li>';
				}
				else
					$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong> <div class="error"> '.$ret.'</div></li>';
			}
		}
		else
			$strRet .= '<li><div class="error"> '.$retTotal.'</div></li>';

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

function addDocsTags($aInfosPost, $idProjet){
	global $tApi, $strRet, $nbDocAjouter, $wpdb;

	if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
		$table_name = $wpdb->base_prefix . $_REQUEST['site']. "_options";
	else
		$table_name = $wpdb->prefix . "options";

//	$strRet = '';
	foreach ($aInfosPost as $post) {
		if (is_object($post['content'])) {
			$idTrad = get_IdTrad($post['content']->term_id,$_POST['select_textmasterLangDestination'], 'term', $table_name);
			$idTm = get_IdProjetTrad($post['content']->term_id,$_POST['select_textmasterLangDestination'], 'term', $table_name);
			if ($idTrad != '')
				$strRet .= '<li><div class="error"><strong>'.$post['content']->name.'</strong> '.__('La traduction de ces tags est déjà','textmaster').' : '.__('Fait','textmaster').'</div></li>';
			else if ($idTm != '')
				$strRet .= '<li><div class="error"><strong>'.$post['content']->name.'</strong> '.__('La traduction de ces tags est déjà','textmaster').' : '.$tApi->getLibStatus($tApi->getProjetStatus($idTm)).'</div></li>';
			else {
				$idTmProjet = get_option_tm('tmProjet_'.$post['content']->term_id);
				if ($idTmProjet != '')
					update_option_tm('tmProjet_'.$post['content']->term_id, $idTmProjet.';'.$idProjet.'='.$_POST['select_textmasterLangDestination']);
				else
					update_option_tm('tmProjet_'.$post['content']->term_id, $idProjet.'='.$_POST['select_textmasterLangDestination']);

				$contentText = cleanWpTxt( $post['content']->name);

				$nbMots = $tApi->countWords( $contentText);
				$arrayDocs[$nbDocAjouter]['title'] = '';
				$arrayDocs[$nbDocAjouter]['word_count'] = $nbMots;
				$arrayDocs[$nbDocAjouter]['original_content'] = $contentText;
				$arrayDocs[$nbDocAjouter]['word_count_rule'] = 1;
				$arrayDocs[$nbDocAjouter]['keyword_list'] = '';

				$arrayDocsId[$nbDocAjouter]['id'] = $post['content']->term_id;
				$arrayDocsId[$nbDocAjouter]['title'] = $post['content']->name;
				//		$arrayDocs[0]['keywords_repeat_count'] = $_REQUEST['text_textmasterKeywordsRepeatCount_traduction'];
				$nbDocAjouter++;
			}
		}
	}

	if (isset($arrayDocs) && count($arrayDocs) != 0) {
	//	var_dump($arrayDocsId);

		$retTotalDocs = $tApi->addDocument($idProjet, $arrayDocs );
		if (count($arrayDocs) == 1)
			$retTotal[0] = $retTotalDocs;
		else
			$retTotal = $retTotalDocs;
//		var_dump($retTotal);
	//	var_dump($ret);
		if (is_array($retTotal))
		{
			foreach ($retTotal as $id => $ret) {
				if (is_array($ret) && array_key_exists('id',$ret))
				{
					$idTmDoc = get_option_tm('tmDoc_'.$arrayDocsId[$id]['id']);
					if ($idTmDoc != '')
						update_option_tm('tmDoc_'.$arrayDocsId[$id]['id'],  $idTmDoc.';'.$ret['id'].'='.$_POST['select_textmasterLangDestination']);
					else
						update_option_tm('tmDoc_'.$arrayDocsId[$id]['id'], $ret['id'].'='.$_POST['select_textmasterLangDestination']);

					//		update_post_meta($post['id'], 'tm_lang', $_POST['select_textmasterLangDestination']);
					$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong><div class="updated">'.__('La traduction de ce tag est lancée.','textmaster').'</div></li>';

				}
				else
					$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong> <div class="error"> '.$ret.'</div></li>';
			}

		}
		else
			$strRet .= '<li><strong>'.$arrayDocsId[$id]['title'].'</strong> <div class="error"> '.$ret.'</div></li>';

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

function checkEmptyContent($aInfosPost, $type){
	$ret = FALSE;

	foreach ($aInfosPost as $post) {
		if ($type != '' && is_object($post['content']))
			$contentText = cleanWpTxt( $post['content']->name);
		else if (is_object($post['content']))
			$contentText = cleanWpTxt( $post['content']->post_content);
		if (trim($contentText) == '') {
			$ret = TRUE;
		//	break;
		}
	}

	return $ret;
}

?>
<?php
function texmaster_admin_actions() {

	// le menu settings
	if ( !is_multisite() )
		add_options_page("TextMaster", "TextMaster", 1, "Textmaster", "texmaster_admin");
//	if (get_option_tm('textmaster_useRedaction') == 'Y')
//	{
		add_submenu_page('edit.php?post_type=textmaster_redaction', __( 'Tous les projets TextMaster','textmaster'), __( 'Suivi des projets','textmaster'), 'manage_options', 'textmaster-projets', 'admin_textmaster_projets' );
		if ( !is_multisite() )
			add_submenu_page('edit.php?post_type=textmaster_redaction', __( 'Paramétrage TextMaster' ,'textmaster'), __( 'Réglages' ,'textmaster'), 'manage_options', 'options-general.php?page=Textmaster');//'texmaster_admin');
//	}

}

function tm_network_pages() {
	add_submenu_page('settings.php', 'TextMaster', 'TextMaster', 'manage_options', 'texmaster_admin', 'texmaster_admin');

	add_menu_page('TextMaster', 'TextMaster', 'manage_options', 'Textmaster', 'admin_textmaster_projets', plugins_url('images/tm-icon.png', __FILE__));
//	add_submenu_page('settings.php', __( 'Paramétrage TextMaster' ,'textmaster'), __( 'Réglages' ,'textmaster'), 'manage_options', 'options-general.php?page=Textmaster');//'texmaster_admin');
	add_submenu_page('Textmaster', __( 'Tous les projets TextMaster','textmaster'), __( 'Suivi des projets','textmaster'), 'manage_options', 'Textmaster', 'admin_textmaster_projets' );
	add_submenu_page('Textmaster', __( 'Paramétrage TextMaster' ,'textmaster'), __( 'Réglages' ,'textmaster'), 'manage_options', 'settings.php?page=texmaster_admin');//'texmaster_admin');



//	add_submenu_page('settings.php', 'Domains', 'Domains', 'manage_options', 'dm_domains_admin', 'dm_domains_admin');
}
add_action( 'network_admin_menu', 'tm_network_pages' );

function texmaster_admin() {

	if (strpos(URL_TM_API_PUBLIC,'sandbox' ) !== FALSE) {
		echo '<div class="update-nag" style="padding:10px;width:88%;">'.__('Le plugin est en mode sandbox.','textmaster').'</div>';

	}

	checkPluginsVersion();
//	print_r($_POST);

	if(isset($_POST['textmaster_hidden']) && $_POST['textmaster_hidden'] == 'Y') {
		$textmaster_email = $_POST['textmaster_email'];
		update_option_tm('textmaster_email', $textmaster_email);
		$textmaster_password = $_POST['textmaster_password'];
		update_option_tm('textmaster_password', $textmaster_password);



		if ($textmaster_password != '' && $textmaster_email != '') {
			$oOAuth = new TextMaster_OAuth2();
			$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
		//	print_r($token);
			if (trim($token) != '') {
				$infosUser = $oOAuth->getUserInfos($token);

		//		print_r($infosUser);
				if (isset($infosUser['api_info'])) {
					$textmaster_api_key = $infosUser['api_info']['api_key'];
					if ($textmaster_api_key != '')
						update_option_tm('textmaster_api_key', $textmaster_api_key);
					$textmaster_api_secret = $infosUser['api_info']['api_secret'];
					if ($textmaster_api_secret != '')
						update_option_tm('textmaster_api_secret', $textmaster_api_secret);
				}else {
					$textmaster_api_key ='';
					$textmaster_api_secret = '';
				}


				if ($textmaster_api_key == '' || $textmaster_api_secret == '')
					echo '<div class="error" style="padding:10px;width:89%;">'.__('Impossbile de se connecter à TextMaster, merci de vérifier votre email et mot de passe.','textmaster').' ('.print_r($infosUser, TRUE).')</div>';

				textmaster_api::sendTracker();
			}
			else
			{
				update_option_tm('textmaster_api_key', '');
				update_option_tm('textmaster_api_secret', '');
				echo '<div class="error" style="padding:10px;width:89%;">'.__('Impossbile de se connecter à TextMaster, merci de vérifier votre email et mot de passe.','textmaster').'</div>';
			}

		}

		if (isset($_POST['use_traduction']) && $_POST['use_traduction'] == 'Y')
			$textmaster_useTraduction = $_POST['use_traduction'];
		else
			$textmaster_useTraduction = 'N';
		update_option_tm('textmaster_useTraduction', $textmaster_useTraduction);
		if (isset($_POST['textmaster_useReadproofBulk']) && $_POST['textmaster_useReadproofBulk'] == 'Y')
			$textmaster_useTraductionBulk = $_POST['textmaster_useReadproofBulk'];
		else
			$textmaster_useTraductionBulk = 'N';
		update_option_tm('textmaster_useTraductionBulk', $textmaster_useTraductionBulk);

		if (isset($_POST['use_readproof']) && $_POST['use_readproof'] == 'Y')
			$textmaster_useReadproof = $_POST['use_readproof'];
		else
			$textmaster_useReadproof = 'N';
		update_option_tm('textmaster_useReadproof', $textmaster_useReadproof);
		if (isset($_POST['textmaster_useReadproofBulk']) && $_POST['textmaster_useReadproofBulk'] == 'Y')
			$textmaster_useReadproofBulk = $_POST['textmaster_useReadproofBulk'];
		else
			$textmaster_useReadproofBulk = 'N';
		update_option_tm('textmaster_useReadproofBulk', $textmaster_useReadproofBulk);

		if (isset($_POST['textmaster_useRedaction']) && $_POST['textmaster_useRedaction'] == 'Y')
			$textmaster_useRedaction = $_POST['textmaster_useRedaction'];
		else
			$textmaster_useRedaction = 'N';
		update_option_tm('textmaster_useRedaction', $textmaster_useRedaction);


		// params de redaction
		if (isset($_POST['textmaster_redactionLanguageLevel']))
			$textmaster_redactionLanguageLevel = $_POST['textmaster_redactionLanguageLevel'];
		else
			$textmaster_redactionLanguageLevel = '';
		update_option_tm('textmaster_redactionLanguageLevel', $textmaster_redactionLanguageLevel);
		if (isset($_POST['textmaster_redactionLanguage']))
			$textmaster_redactionLanguage = $_POST['textmaster_redactionLanguage'];
		else
			$textmaster_redactionLanguage = 'N';
		update_option_tm('textmaster_redactionLanguage', $textmaster_redactionLanguage);
		if (isset($_POST['textmaster_redactionCategorie']))
			$textmaster_redactionCategorie = $_POST['textmaster_redactionCategorie'];
		else
			$textmaster_redactionCategorie = '';
		update_option_tm('textmaster_redactionCategorie', $textmaster_redactionCategorie);
		$textmaster_vocabularyType = $_POST['textmaster_vocabularyType'];
		update_option_tm('textmaster_vocabularyType', $textmaster_vocabularyType);
		$textmaster_grammaticalPerson = $_POST['textmaster_grammaticalPerson'];
		update_option_tm('textmaster_grammaticalPerson', $textmaster_grammaticalPerson);
		$textmaster_targetReaderGroup = $_POST['textmaster_targetReaderGroup'];
		update_option_tm('textmaster_targetReaderGroup', $textmaster_targetReaderGroup);
		if (isset($_POST['textmaster_Template']))
			$textmaster_Template = $_POST['textmaster_Template'];
		else
			$textmaster_Template = '';
		update_option_tm('textmaster_Template', $textmaster_Template);
		if (isset($_POST['textmaster_author'])){
			$textmaster_author = $_POST['textmaster_author'];
			update_option_tm('textmaster_author', $textmaster_author);
		}

		// les options
		if (isset($_POST['radio_textmasterQualityRedaction']))
			$textmaster_qualityRedaction = $_POST['radio_textmasterQualityRedaction'];
		else
			$textmaster_qualityRedaction = 'N';
		update_option_tm('textmaster_qualityRedaction', $textmaster_qualityRedaction);
		if (isset($_POST['radio_textmasterExpertiseRedaction']))
			$textmaster_expertiseRedaction = $_POST['radio_textmasterExpertiseRedaction'];
		else
			$textmaster_expertiseRedaction = 'N';
		update_option_tm('textmaster_expertiseRedaction', $textmaster_expertiseRedaction);
		if (isset($_POST['radio_textmasterPriorityRedaction']))
			$textmaster_priorityRedaction = $_POST['radio_textmasterPriorityRedaction'];
		else
			$textmaster_priorityRedaction = 'N';
		update_option_tm('textmaster_priorityRedaction', $textmaster_priorityRedaction);

		// params de relecture
		if (isset($_POST['textmaster_readproofLanguageLevel']))
			$textmaster_readproofLanguageLevel = $_POST['textmaster_readproofLanguageLevel'];
		else
			$textmaster_readproofLanguageLevel = '';
		update_option_tm('textmaster_readproofLanguageLevel', $textmaster_readproofLanguageLevel);
		if (isset($_POST['textmaster_readproofLanguage']))
			$textmaster_readproofLanguage = $_POST['textmaster_readproofLanguage'];
		else
			$textmaster_readproofLanguage = '';
		update_option_tm('textmaster_readproofLanguage', $textmaster_readproofLanguage);

		if (isset($_POST['textmaster_readproofCategorie']))
			$textmaster_readproofCategorie = $_POST['textmaster_readproofCategorie'];
		else
			$textmaster_readproofCategorie = '';
		update_option_tm('textmaster_readproofCategorie', $textmaster_readproofCategorie);
		$textmaster_readproofBriefing = $_POST['textmaster_readproofBriefing'];
		update_option_tm('textmaster_readproofBriefing', $textmaster_readproofBriefing);
		if (isset($_POST['textmaster_readproofVocabularyType']))
			$textmaster_readproofVocabularyType = $_POST['textmaster_readproofVocabularyType'];
		else
			$textmaster_readproofVocabularyType = 'N';
		update_option_tm('textmaster_readproofVocabularyType', $textmaster_readproofVocabularyType);

		if (isset($_POST['textmaster_readproofGrammaticalPerson']))
			$textmaster_readproofGrammaticalPerson = $_POST['textmaster_readproofGrammaticalPerson'];
		else
			$textmaster_readproofGrammaticalPerson = '';
		update_option_tm('textmaster_readproofGrammaticalPerson', $textmaster_readproofGrammaticalPerson);

		if (isset($_POST['textmaster_readproofTargetReaderGroup']))
			$textmaster_readproofTargetReaderGroup = $_POST['textmaster_readproofTargetReaderGroup'];
		else
			$textmaster_readproofTargetReaderGroup = '';
		update_option_tm('textmaster_readproofTargetReaderGroup', $textmaster_readproofTargetReaderGroup);

		if (isset($_POST['textmaster_authorTraduction'])){
			$textmaster_authorTraduction = $_POST['textmaster_authorTraduction'];
			update_option_tm('textmaster_authorTraduction', $textmaster_authorTraduction);
		}
		// les options
		if (isset($_POST['radio_textmasterQualityReadproof']))
			$textmaster_qualityReadproof = $_POST['radio_textmasterQualityReadproof'];
		else
			$textmaster_qualityReadproof = 'N';
		update_option_tm('textmaster_qualityReadproof', $textmaster_qualityReadproof);

		if (isset( $_POST['radio_textmasterExpertiseReadproof']))
			$textmaster_expertiseReadproof = $_POST['radio_textmasterExpertiseReadproof'];
		else
			$textmaster_expertiseReadproof = 'N';
		update_option_tm('textmaster_expertiseReadproof', $textmaster_expertiseReadproof);

		if (isset($_POST['radio_textmasterPriorityReadproof']))
			$textmaster_priorityReadproof = $_POST['radio_textmasterPriorityReadproof'];
		else
			$textmaster_priorityReadproof = 'N';
		update_option_tm('textmaster_priorityReadproof', $textmaster_priorityReadproof);



		// params de traduction
		if (isset($_POST['textmaster_traductionLanguageLevel']))
			$textmaster_traductionLanguageLevel = $_POST['textmaster_traductionLanguageLevel'];
		else
			$textmaster_traductionLanguageLevel = '';
		update_option_tm('textmaster_traductionLanguageLevel', $textmaster_traductionLanguageLevel);

		if (isset($_POST['textmaster_traductionLanguageSource']))
			$textmaster_traductionLanguageSource = $_POST['textmaster_traductionLanguageSource'];
		else
			$textmaster_traductionLanguageSource = '';
		update_option_tm('textmaster_traductionLanguageSource', $textmaster_traductionLanguageSource);
		if (isset($_POST['textmaster_traductionLanguageSource']))
			$textmaster_traductionLanguageDestination = $_POST['textmaster_traductionLanguageDestination'];
		else
			$textmaster_traductionLanguageDestination = '';
		update_option_tm('textmaster_traductionLanguageDestination', $textmaster_traductionLanguageDestination);
		if (isset($_POST['textmaster_traductionCategorie']))
			$textmaster_traductionCategorie = $_POST['textmaster_traductionCategorie'];
		else
			$textmaster_traductionCategorie = '';
		update_option_tm('textmaster_traductionCategorie', $textmaster_traductionCategorie);
		$textmaster_traductionBriefing = $_POST['textmaster_traductionBriefing'];
		update_option_tm('textmaster_traductionBriefing', $textmaster_traductionBriefing);
		if (isset($_POST['textmaster_traductionVocabularyType']))
			$textmaster_traductionVocabularyType = $_POST['textmaster_traductionVocabularyType'];
		else
			$textmaster_traductionVocabularyType = '';
		update_option_tm('textmaster_traductionVocabularyType', $textmaster_traductionVocabularyType);

		if (isset($_POST['textmaster_traductionGrammaticalPerson']))
			$textmaster_traductionGrammaticalPerson = $_POST['textmaster_traductionGrammaticalPerson'];
		else
			$textmaster_traductionGrammaticalPerson = '';
		update_option_tm('textmaster_traductionGrammaticalPerson', $textmaster_traductionGrammaticalPerson);

		if (isset($_POST['textmaster_traductionTargetReaderGroup']))
			$textmaster_traductionTargetReaderGroup = $_POST['textmaster_traductionTargetReaderGroup'];
		else
			$textmaster_traductionTargetReaderGroup = '';
		update_option_tm('textmaster_traductionTargetReaderGroup', $textmaster_traductionTargetReaderGroup);

		if (isset($_POST['textmaster_authorReadproof'])){
			$textmaster_authorReadproof = $_POST['textmaster_authorReadproof'];
			update_option_tm('textmaster_authorReadproof', $textmaster_authorReadproof);
		}
		// les options
		if (isset($_POST['radio_textmasterQualityTraduction']))
			$textmaster_qualityTraduction = $_POST['radio_textmasterQualityTraduction'];
		else
			$textmaster_qualityTraduction = 'N';
		update_option_tm('textmaster_qualityTraduction', $textmaster_qualityTraduction);
		if (isset($_POST['radio_textmasterExpertiseTraduction']))
			$textmaster_expertiseTraduction = $_POST['radio_textmasterExpertiseTraduction'];
		else
			$textmaster_expertiseTraduction = 'N';
		update_option_tm('textmaster_expertiseTraduction', $textmaster_expertiseTraduction);
		if (isset($_POST['radio_textmasterPriorityTraduction']))
			$textmaster_priorityTraduction = $_POST['radio_textmasterPriorityTraduction'];
		else
			$textmaster_priorityTraduction = 'N';
		update_option_tm('textmaster_priorityTraduction', $textmaster_priorityTraduction);

		if (isset($_POST['textmaster_useMultiLangues'])) {
			$textmaster_useMultiLangues = $_POST['textmaster_useMultiLangues'];
		}else
			$textmaster_useMultiLangues = 'N';
		update_option_tm('textmaster_useMultiLangues', $textmaster_useMultiLangues);

		// param avancés
		$tm_metabox_pg_prefix ='';
		if (isset($_POST['tm_metabox_pg_prefix']))
			$tm_metabox_pg_prefix = $_POST['tm_metabox_pg_prefix'];
		update_option_tm('tm_metabox_pg_prefix', $tm_metabox_pg_prefix);

		$chk_tm_mb_feilds ='';
		if (isset($_POST['chk_tm_mb_feilds']))
			$chk_tm_mb_feilds = serialize($_POST['chk_tm_mb_feilds']);
		if (isset($_POST['area_tm_mb']) && $_POST['area_tm_mb'] != '') {
			$aTmMb = explode(',', $_POST['area_tm_mb']);
			$aTmMb = array_map('trim', $aTmMb);
			$chk_tm_mb_feilds = serialize($aTmMb);
		}
		update_option_tm('chk_tm_mb_feilds', $chk_tm_mb_feilds);


		// on install les pack de langues
		if (isset($_POST['textmaster_useMultiLangues']) && count($_POST['textmaster_langues']) != 0) {
			$strLanguesOk = '';
			$strLanguesNok = '';
			if (get_bloginfo('version') >= 4.0) {
				@require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
				foreach ($_POST['textmaster_langues'] as $lang) {
					if (function_exists('wp_download_language_pack')) {
						$ret = @wp_download_language_pack($lang.'_'.strtoupper($lang));
						if ($ret != FALSE)
							$strLanguesOk .= $lang.', ';
						//	else
						//		$strLanguesNok .= $lang.', ';
					}
					else
						$strLanguesNok .= $lang.', ';
				}

			}
			if ($strLanguesNok != '')
				echo '<div class="error" style="padding:10px;width:89%;">'.__('Merci d\'installer les traductions pour les langues : ','textmaster') . substr($strLanguesNok,0,-2).'</div>';
			if ($strLanguesOk != '')
				echo '<div class="updated" style="padding:10px;width:89%;">'.__('Les traductions ont été installées pour les langues : ','textmaster') . substr($strLanguesOk,0, -2).'</div>';

			$textmaster_langues = implode(';', $_POST['textmaster_langues']);
			update_option_tm('textmaster_langues', $textmaster_langues);
			$textmaster_langues = $_POST['textmaster_langues'];
			if (isset($_POST['textmaster_langueDefaut'])) {
				$textmaster_langueDefaut = $_POST['textmaster_langueDefaut'];
			}else if (get_option_tm('WPLANG') != '') {
				$wpLang = explode('_',get_option_tm('WPLANG'));
				$textmaster_langueDefaut = $wpLang[0];
			} else if (defined('WPLANG')) {
				$wpLang = explode('_',WPLANG);
				$textmaster_langueDefaut = $wpLang[0];
			}else
				$textmaster_langueDefaut = 'en';
			update_option_tm('textmaster_langueDefaut', $textmaster_langueDefaut);
		}
		else{
			$textmaster_langues = array();
			$textmaster_langueDefaut = '';
		}


		echo '<div class="updated" style="padding:10px;width:89%;">'.__('Les paramètres ont été sauvegaradés.','textmaster').'</div>';//.'</div>';
	} else {

		$textmaster_email = get_option_tm('textmaster_email');
		$textmaster_password = get_option_tm('textmaster_password');

		//$textmaster_api_key = get_option_tm('textmaster_api_key');
		//$textmaster_api_secret = get_option_tm('textmaster_api_secret');

		$textmaster_useRedaction = get_option_tm('textmaster_useRedaction');
		$textmaster_useTraduction = get_option_tm('textmaster_useTraduction');
		$textmaster_useTraductionBulk = get_option_tm('textmaster_useTraductionBulk');
		$textmaster_useReadproof = get_option_tm('textmaster_useReadproof');
		$textmaster_useReadproofBulk = get_option_tm('textmaster_useReadproofBulk');

		$textmaster_useMultiLangues = get_option_tm('textmaster_useMultiLangues');

		$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
		$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');

	}

	if ($textmaster_password != '' && $textmaster_email != '') {
		$oOAuth = new TextMaster_OAuth2();
		$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
		$infosUser = $oOAuth->getUserInfos($token);

		$lang = explode('_', get_locale());
		if (isset($infosUser['authentication_token']))
			$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE.'?auth_token='.$infosUser['authentication_token'];
		else
			$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE;
	//	echo $urlAchat;
	}
	else {
		$lang = explode('_', get_locale());
		$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE;
	}

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	//$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//$tApi->keyapi =  get_option_tm('textmaster_api_key');
	//$tApi->getLocales();

	$infosClient = $tApi->getUserInfos();

	$aPrices = $tApi->getPricings(1);

	$languageLevels = $tApi->getLanguageLevels();
	$languages = $tApi->getLanguages();
	$categories = $tApi->getCategories();


	?>
	<link href="<?php echo plugins_url('textmaster.css', __FILE__) ?>" rel="stylesheet" type="text/css" />
	<style type="text/css" media="screen">
		span.icon32-posts-textmaster_redaction {background: url(<?php echo  plugins_url('images/tm-logo.png', __FILE__) ?>) no-repeat;display:blok;}
	</style>
	<script>
		window.urlPlugin = "<?php echo plugins_url('', __FILE__) ?>";
	</script>
	<div class="wrap" id="textmaster_settings">
    <?php    echo "<span class='icon32 icon32-posts-textmaster_redaction'><br></span><h2>" . __( 'Paramétrage TextMaster' ,'textmaster') . "</h2>"; ?>
    <br/>
    <br/>
    <form name="textmaster_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="textmaster_hidden" value="Y">
        <ul class="tabs">
		<li class="t1"><a class="t1 tab" title="<?php _e('Général','textmaster'); ?>"><?php _e('Général','textmaster')?></a></li>
		<li class="t4"><a class="t4 tab" title="<?php _e('Traduction','textmaster'); ?>"><?php _e('Traduction','textmaster')?></a></li>
		<li class="t2"><a class="t2 tab" title="<?php _e('Général','textmaster'); ?>"><?php _e('Rédaction','textmaster')?></a></li>
		<li class="t3"><a class="t3 tab" title="<?php _e('Relecture','textmaster'); ?>"><?php _e('Relecture','textmaster')?></a></li>
		<?php if ( checkInstalledPlugin('Meta Box')) {?>
			<li class="t5"><a class="t5 tab" title="<?php _e('Avancés','textmaster'); ?>"><?php _e('Avancés','textmaster')?></a></li>
		<?php } ?>
		</ul>

		<!-- l'onglet général-->
		<div class="t1 settings">
	    <table class="form-table">
        <tr valign="top">
        <td colspan="2">
        <h3><?php _e('Accès TextMaster','textmaster') ?></h3>
		</td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("Email TextMaster: ", 'textmaster' ); ?></th>
        <td><input type="text" name="textmaster_email" value="<?php echo $textmaster_email; ?>" size="50"></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("Mot de passe: ",'textmaster' ); ?></th>
        <td><input type="password" name="textmaster_password" value="<?php echo $textmaster_password; ?>" size="20"></td>
        </tr>
         <tr valign="top">
        <td colspan="2">
        <?php
		 _e('Vous avez actuellement :','textmaster');
		if (is_array($infosClient)) {
			echo  ' <strong>'.$infosClient['wallet']['current_money'].' '.$infosClient['wallet']['currency_code'].'</strong>.';
			_e('Vous pouvez créditer votre compte TextMaster à cette adresse :','textmaster');
			echo ' <a href="'. $urlAchat.'" target="_blank">TextMaster</a><br/><br/>';
		}
		else
			echo  ' <strong>NC</strong>.';
		 ?>
		 <br/>
        <?php
		_e('Ces informations  correspondent à votre compte TextMaster, si vous n\'avez pas encore créé de compte, vous pouvez le faire en utilisant ce lien:','textmaster');
		echo ' <a href="javascript:void(tb_show(\''.__('Créer un compte TextMaster','textmaster').'\',\''. plugins_url('', __FILE__).'/createUser.php?height=500&width=630&TB_iframe=true\'));">';
		_e('Créer un compte TextMaster.','textmaster'); ?></a><br/>
		</td>
        </tr>
       <tr valign="top">
        <td colspan="2">
        <h3><?php _e('Support multi-langues','textmaster') ?></h3>
		</td>
        </tr>
     	<tr valign="top">
        <td colspan="2">
        <?php
        	$txt_tmml = '';
        	$disable_tmml = '';
        	if (checkInstalledPlugin('WPML Multilingual CMS') && checkActivatedPlugin('sitepress-multilingual-cms/sitepress.php')){
        		$disable_tmml = 'disabled="disabled"';
        		$txt_tmml = ' <strong><em>'. __('Vous utilisez le plugin: ').'WPML Multilingual CMS'.'</em></strong><br/>';
        		$textmaster_useMultiLangues = '';
        	}
			if (checkInstalledPlugin('Polylang') && checkActivatedPlugin('polylang/polylang.php')){
				$disable_tmml = 'disabled="disabled"';
				$txt_tmml = ' <strong><em>'. __('Vous utilisez le plugin: ').'Polylang'.'</em></strong><br/>';
				$textmaster_useMultiLangues = '';
			}
			echo $txt_tmml;
        ?>
        <input type="checkbox" id="textmaster_useMultiLangues" name="textmaster_useMultiLangues" value="Y" <?php if($textmaster_useMultiLangues == 'Y'){ echo "checked=\"checked\"";} ?> onchange="afficheLangues();" <?php echo $disable_tmml; ?>/>
        <?php _e("Activer le support multi-langues ",'textmaster' ); ?>
		</td>
		</tr>
       <tr valign="top" class="multilangues">
        <td colspan="2">
        <h3><?php _e('Langues activées sur ce site :','textmaster') ?></h3>
		</td>
        </tr>
		<tr valign="top" class="multilangues">
        <td colspan="2">
        <ul class="list_setting_langs">
        <?php

        	if (get_option_tm('WPLANG') != '') {
        		$wpLang = explode('_',get_option_tm('WPLANG'));
        		$wp_langueDefaut = $wpLang[0];
        	} else if (defined('WPLANG')) {
        		$wpLang = explode('_',WPLANG);
        		$wp_langueDefaut = $wpLang[0];
        	}else
        		$wp_langueDefaut = 'en';
        	$selectLangs = array();
			if (count($languages) != 0) {
				foreach ($languages as $lang) {
					echo '<li><input type="checkbox" name="textmaster_langues[]" value="'.$lang['code'].'"';
					if((isset($textmaster_langues) && in_array($lang['code'],$textmaster_langues )) || $lang['code'] == $wp_langueDefaut){
						echo "checked=\"checked\"";
						$selectLangs[$lang['code']] = $lang['value'];
					}
					echo '/>'.$lang['value'] .'</li>' ;
				}

			}

	 	?>
	 	</ul>
		</td>
        </tr>
		<tr valign="top" class="multilangues">
        <td colspan="2">
		<?php _e("Langue par défaut ",'textmaster' ); ?> : <select name="textmaster_langueDefaut"<?php if (count($selectLangs) == 0)  {  echo 'disabled="disabled"';	} ?>>

		<?php
			if (count($selectLangs) != 0) {
				foreach ($selectLangs as $code => $selectLang) {
					echo '<option value="'.$code.'" ';
					if ($textmaster_langueDefaut == $code)
						echo 'selected="selected"';
					echo '>'.$selectLang.'</option>';

				}
			}
			else
				echo '<option value="#">'.__("Sélectionnez vos langues",'textmaster' ).'</option>';
		?>
		</select>
		</td>
        </tr>
        <tr>
        <td colspan="2"><?php _e("Fonction à utiliser dans votre thème pour afficher le sélécteur de langues : ",'textmaster' ); ?>&lt;?php display_langs_selector(); ?&gt; <?php _e("(vous devez activer les Permalinks)",'textmaster' ); ?> </td>
        </tr>
        <tr valign="top">
        <td colspan="2">
        <h3><?php _e('Pour quelles activités ','textmaster') ?></h3>
		</td>
        </tr>
       <tr valign="top">
        <td colspan="2">
        <input type="checkbox" id="textmaster_useRedaction" name="textmaster_useRedaction" value="Y" <?php if($textmaster_useRedaction != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Rédaction ",'textmaster' ); ?>
		</td>
        </tr>
   		<tr valign="top">
        <td colspan="2">
        <span class="settingActivites">
        <input type="checkbox" id="use_readproof" name="use_readproof" value="Y" <?php if($textmaster_useReadproof != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Relecture ",'textmaster' ); ?>
        </span>
       	<input type="checkbox" id="textmaster_useReadproofBulk" name="textmaster_useReadproofBulk" value="Y" <?php if($textmaster_useReadproofBulk != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Activer les actions groupées ",'textmaster' ); ?>
		</td>
        </tr>
        <tr valign="top">
        <td colspan="2">
        <span class="settingActivites">
        <input type="checkbox" id="use_traduction" name="use_traduction" value="Y" <?php if($textmaster_useTraduction != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Traduction: ",'textmaster' ); ?>
        </span>
		</td>
        </tr>
        </table>
        <br/>
	</div>
	<?php
//	print_r($aPrices);
	if (!is_array($infosClient)){
		$infosClient = array();
		$infosClient['wallet']['currency_code'] = 'Euro';
	}


	afficheRedaction($tApi, $languageLevels, $languages, $categories, $infosClient, $aPrices['copywriting']);
	afficheTraduction($tApi, $languageLevels, $languages, $categories, $infosClient, $aPrices['translation']);
	afficheReadProof($tApi, $languageLevels, $languages, $categories, $infosClient, $aPrices['proofreading']);
	if ( checkInstalledPlugin('Meta Box'))
		afficheAvances();
	?>
		<div style="clear:both;"></div>
		<br/>
		<br/>
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Sauvegardez les paramètres','textmaster' ) ?>" />
        </p>
    </form>
    </div>
    <script>
    afficheLangues();
    </script>
	<?php
}

function afficheRedaction(&$tApi, &$languageLevels, &$languages, &$categories, &$infosClient, &$aPrices){

	// params de redaction
	$textmaster_redactionLanguageLevel = get_option_tm('textmaster_redactionLanguageLevel');
	$textmaster_redactionLanguage = get_option_tm('textmaster_redactionLanguage');
	$textmaster_redactionCategorie = get_option_tm('textmaster_redactionCategorie');
	if ($textmaster_redactionCategorie == '')
		$textmaster_redactionCategorie = CATEGORIE_DEFAUT;
	$textmaster_vocabularyType = get_option_tm('textmaster_vocabularyType');
	$textmaster_grammaticalPerson = get_option_tm('textmaster_grammaticalPerson');
	$textmaster_targetReaderGroup = get_option_tm('textmaster_targetReaderGroup');
	$textmaster_Template = get_option_tm('textmaster_Template');
	$textmaster_author = get_option_tm('textmaster_author');
	// les options
	$textmaster_qualityRedaction = get_option_tm('textmaster_qualityRedaction');
	$textmaster_expertiseRedaction = get_option_tm('textmaster_expertiseRedaction');
	$textmaster_priorityRedaction = get_option_tm('textmaster_priorityRedaction');

	?>
	     	<!-- l'onglet rédaction -->
		<div class="t2 settings">
		<table class="form-table">
			<tr valign="top">
	        <td colspan="2">
	        <h3><?php _e('Paramètres de rédaction par défaut ','textmaster') ?></h3>
			</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row"><?php _e("Niveau de service: ",'textmaster' ); ?></th>
	        <td>
			<select name="textmaster_redactionLanguageLevel" id="select_textmasterLanguageLevel" style="width:235px;">
			<?php
	foreach($languageLevels['copywrite'] as $key => $languageLevel)
	{
		if ($textmaster_redactionLanguageLevel == $languageLevel["name"])
			echo '<option value="'.$languageLevel["name"].'" selected="selected">'.$languageLevel["name"].'</option>';
		else
			echo '<option value="'.$languageLevel["name"].'">'.$languageLevel["name"].'</option>';
	}
	?>
			</select><span class="coutOptionTm" id="priceTextmasterBase">NC</span> <span><?php echo $infosClient['wallet']['currency_code'] ?> / <?php _e('mot','textmaster') ?></span>
    	    <script>jQuery(document).ready(function($) {getPrice("redaction",1 );});</script>
			</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row"><?php _e("Langue: ",'textmaster' ); ?></th>
	        <td>
	        <?php
	if (isset($languages['message']) && $languages['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
				<select name="textmaster_redactionLanguage" style="width:235px;">
				<?php
		if (count($languages) != 0) {
			foreach($languages as $language)
			{
				if ($textmaster_redactionLanguage == $language['code'])
					echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
				else
					echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
			}
		}

		?>
				</select>
				<?php
	}
	?>
			</td>
	     	</tr>
	 		<tr valign="top">
	        <th scope="row"><?php _e("Categorie: ",'textmaster' ); ?></th>
	        <td>
		 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
					<select name="textmaster_redactionCategorie" style="width:235px;">
					<?php
		foreach($categories as $categorie)
		{
			if ($textmaster_redactionCategorie == $categorie['code'])
				echo '<option value="'.$categorie['code'].'" selected="selected">'.$categorie['value'].'</option>';
			else
				echo '<option value="'.$categorie['code'].'">'.$categorie['value'].'</option>';
		}
		?>
				</select>
				<?php
	}
	?>
        </td>
        </tr>
 		<tr valign="top">
        <th scope="row"><?php _e("Type de Vocabulaire:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_vocabularyType" style="width:235px;">
			<?php
		$vocabulary_types = $tApi->getVocabularyTypes();

		foreach($vocabulary_types as $key => $vocabulary_type)
		{
			if ($textmaster_vocabularyType == $key)
				echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
			else
				echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
		}
		?>
					</select>
					<?php
	}
	?>
        </td>
        </tr>
 		<tr valign="top">
        <th scope="row"><?php _e("Personne grammaticale:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
				<select name="textmaster_grammaticalPerson" style="width:235px;">
				<?php
		$grammatical_persons = $tApi->getGrammaticalPersons();

		foreach($grammatical_persons as $key => $grammatical_person)
		{
			if ($textmaster_grammaticalPerson == $key)
				echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
			else
				echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
		}
		?>
						</select>
						<?php
	}
	?>
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e("Public Ciblé:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
					<select name="textmaster_targetReaderGroup" style="width:235px;">
					<?php
		$target_reader_groups = $tApi->getTargetReaderGroups();


		foreach($target_reader_groups as $key => $target_reader_group)
		{
			if ($textmaster_targetReaderGroup == $key)
				echo '<option value="'.$key.'" selected="selected">'.$target_reader_group.'</option>';
			else
				echo '<option value="'.$key.'">'.$target_reader_group.'</option>';
		}
		?>
							</select>
							<?php
	}
	?>
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e("Auteur:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
						<select name="textmaster_author" style="width:235px;">
						<?php
		$auteurs = $tApi->getAuteurs();

		if (count($auteurs) != 0) {
			foreach($auteurs as $auteur)
			{
				$auteurDesc = '';
				if (isset($auteur['description']) && $auteur['description'] != '')
					$auteurDesc = ' - '.$auteur['description'];

				if ($textmaster_author == $auteur['author_id'])
					echo '<option value="'.$auteur['author_id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
				else
					echo '<option value="'.$auteur['author_id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
			}
		}

		?>
								</select>
								<?php
	}
	?>
        </td>
        </tr>
        	<tr valign="top">
        <th scope="row"><?php
	echo '<label>'.__('Contrôle qualité :','textmaster').'</label> ';
	?></th>
        <td>
        <?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_qualityRedaction == "true")
		$chkYes = 'checked="checked"';
	else
		$chkNo = 'checked="checked"';
	echo '<input type="radio" name="radio_textmasterQualityRedaction" class="radio_textmasterQuality" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityRedaction" class="radio_textmasterQuality" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[2]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> / <?php _e('mot','textmaster') ?>
        </td>
     </tr>
        	<tr valign="top">
  <!--      <th scope="row"><?php
		echo '<label>'.__('Expertise :','textmaster').'</label> ';
	?></th>
        <td>
        <?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_expertiseRedaction =="true")
		$chkYes = 'checked="checked"';
	else
		$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterExpertiseRedaction" class="radio_textmasterExpertise" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseRedaction" class="radio_textmasterExpertise" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[3]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
        </td>
     </tr>-->
        	<tr valign="top">
        <th scope="row"><?php
		echo '<label>'.__('Commande prioritaire :','textmaster').'</label> ';
	?></th>
        <td>
        <?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_priorityRedaction =="true")
		$chkYes = 'checked="checked"';
	else
		$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterPriorityRedaction" class="radio_textmasterPriority" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterPriorityRedaction" class="radio_textmasterPriority" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[6]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
        </td>
     </tr>

		<tr valign="top">
        <th scope="row"><?php _e("Mise en page",'textmaster' ); ?></th>
        <td>
	 	<?php
	$templates = $tApi->getTemplates();
	if (count($templates) == 0) {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{

		if ($textmaster_Template == '')
			$textmaster_Template = 'Libre';

		echo '<ul style="display:inline-block;">';
		foreach($templates as $key => $template)
		{
			echo '<li style="width:150px;display:inline-block;min-height:340px;vertical-align:top;margin:10px;">';
			if ($textmaster_Template == $template['name'])
				$checked = 'checked="checked"';
			else
				$checked = '';
			echo '<input type="radio" name="textmaster_Template" id="textmaster_Template" value="'.$template['name'].'" '.$checked.'>';
			echo '<img src="'.$template['image_preview_path'].'" />';
			echo $template['description'];
			echo '</li>';
		}
		echo '</ul>';
	}
	?>
        </td>
        </tr>
		</table>
		<br/>
		</div>

	<?php
}

function afficheTraduction(&$tApi, &$languageLevels, &$languages, &$categories, &$infosClient, &$aPrices){

	// params de traduction
	$textmaster_traductionLanguageLevel = get_option_tm('textmaster_traductionLanguageLevel');
	$textmaster_traductionLanguageSource = get_option_tm('textmaster_traductionLanguageSource');
	$textmaster_traductionLanguageDestination = get_option_tm('textmaster_traductionLanguageDestination');
	$textmaster_traductionCategorie = get_option_tm('textmaster_traductionCategorie');
	if ($textmaster_traductionCategorie == '')
		$textmaster_traductionCategorie = CATEGORIE_DEFAUT;
	$textmaster_traductionBriefing = get_option_tm('textmaster_traductionBriefing');
	if ($textmaster_traductionBriefing == '')
		$textmaster_traductionBriefing = __("Bonjour,\nMerci de procéder à la traduction du texte. Il est impératif de conserver le style et le type de vocabulaire.\nMerci");
	$textmaster_traductionVocabularyType = get_option_tm('textmaster_traductionVocabularyType');
	$textmaster_traductionGrammaticalPerson = get_option_tm('textmaster_traductionGrammaticalPerson');
	$textmaster_traductionTargetReaderGroup = get_option_tm('textmaster_traductionTargetReaderGroup');
	$textmaster_authorTraduction = get_option_tm('textmaster_authorTraduction');

	// les options
	$textmaster_qualityTraduction = get_option_tm('textmaster_qualityTraduction');
	$textmaster_expertiseTraduction = get_option_tm('textmaster_expertiseTraduction');
	$textmaster_priorityTraduction = get_option_tm('textmaster_priorityTraduction');

	?>
	  	<!-- l'onglet traduction -->
		<div class="t4 settings">
        <table class="form-table">
     	<tr valign="top">
        <td colspan="2">
        <h3><?php _e('Paramètres de traduction par défaut ','textmaster') ?></h3>
		</td>
        </tr>
		 <th scope="row"><?php _e("Niveau de service: ",'textmaster' ); ?></th>
        <td>
		<select name="textmaster_traductionLanguageLevel" id="select_textmasterTradLanguageLevel" style="width:235px;">
		<?php
	foreach($languageLevels['traduction'] as $key => $languageLevel)
	{
		if ($textmaster_traductionLanguageLevel == $languageLevel["name"])
			echo '<option value="'.$languageLevel["name"].'" selected="selected">'.$languageLevel["name"].'</option>';
		else
			echo '<option value="'.$languageLevel["name"].'">'.$languageLevel["name"].'</option>';
	}
	?>
		</select><span class="coutOptionTm" id="priceTextmasterBaseTrad">NC</span> <span><?php echo $infosClient['wallet']['currency_code'] ?> / <?php _e('mot','textmaster') ?></span>
		<input type="hidden" name="forceWordsCount" id="forceWordsCount" value="1"/>
		<script>jQuery(document).ready(function($) {getPrice("traduction",1 );});</script>
		</td>
        </tr>
       	<tr valign="top">
        <th scope="row"><?php _e("Langue source: ",'textmaster' ); ?></th>
        <td>
        <?php
	if (isset($languages['message']) && $languages['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_traductionLanguageSource" style="width:235px;">
			<?php
		if (count($languages) != 0) {
			foreach($languages as $language)
			{
				if ($textmaster_traductionLanguageSource == $language['code'])
					echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
				else
					echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
			}
		}

		?>
			</select>
			<?php
	}
	?>
		</td>
     	</tr>
   		<tr valign="top">
        <th scope="row"><?php _e("Langue destination: ",'textmaster' ); ?></th>
        <td>
        <?php
	if (isset($languages['message']) && $languages['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_traductionLanguageDestination" style="width:235px;">
			<?php
		foreach($languages as $language)
		{
			if ($textmaster_traductionLanguageDestination == $language['code'])
				echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
			else
				echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
		}
		?>
			</select>
			<?php
	}
	?>
		</td>
     	</tr>
		<tr valign="top">
        <th scope="row"><?php _e("Categorie: ",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
				<select name="textmaster_traductionCategorie" style="width:235px;">
				<?php
		if (count($categories) != 0) {
			foreach($categories as $categorie)
			{
				if ($textmaster_traductionCategorie == $categorie['code'])
					echo '<option value="'.$categorie['code'].'" selected="selected">'.$categorie['value'].'</option>';
				else
					echo '<option value="'.$categorie['code'].'">'.$categorie['value'].'</option>';
			}
		}

		?>
				</select>
				<?php
	}
	?>
        </td>
        </tr>
    <!--
    <tr valign="top">
        <th scope="row"><?php _e("Type de Vocabulaire:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_traductionVocabularyType" style="width:235px;">
			<?php
		$vocabulary_types = $tApi->getVocabularyTypes();

		foreach($vocabulary_types as $key => $vocabulary_type)
		{
			if ($textmaster_traductionVocabularyType == $key)
				echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
			else
				echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
		}
		?>
					</select>
					<?php
	}
	?>
        </td>
        </tr>
        -->
 	<!--
 	<tr valign="top">
        <th scope="row"><?php _e("Personne grammaticale:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
				<select name="textmaster_traductionGrammaticalPerson" style="width:235px;">
				<?php
		$grammatical_persons = $tApi->getGrammaticalPersons();

		foreach($grammatical_persons as $key => $grammatical_person)
		{
			if ($textmaster_traductionGrammaticalPerson == $key)
				echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
			else
				echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
		}
		?>
						</select>
						<?php
	}
	?>
        </td>
        </tr>
        -->
		<!--
		<tr valign="top">
        <th scope="row"><?php _e("Public Ciblé:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
					<select name="textmaster_traductionTargetReaderGroup" style="width:235px;">
					<?php
		$target_reader_groups = $tApi->getTargetReaderGroups();


		foreach($target_reader_groups as $key => $target_reader_group)
		{
			if ($textmaster_traductionTargetReaderGroup == $key)
				echo '<option value="'.$key.'" selected="selected">'.$target_reader_group.'</option>';
			else
				echo '<option value="'.$key.'">'.$target_reader_group.'</option>';
		}
		?>
							</select>
							<?php
	}
	?>
        </td>
        </tr>
        -->
	<tr valign="top">
        <th scope="row"><?php _e("Auteur:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
						<select name="textmaster_authorTraduction" style="width:235px;">
						<?php
		$auteurs = $tApi->getAuteurs();

		if (count($auteurs) != 0) {
			foreach($auteurs as $auteur)
			{
				$auteurDesc = '';
				if (isset($auteur['description']) && $auteur['description'] != '')
					$auteurDesc = ' - '.$auteur['description'];

				if ($textmaster_authorTraduction == $auteur['author_id'])
					echo '<option value="'.$auteur['author_id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
				else
					echo '<option value="'.$auteur['author_id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
			}
		}

		?>
								</select>
								<?php
	}
	?>
        </td>
        </tr>
    	<tr valign="top">
        <th scope="row"><?php
	echo '<label>'.__('Contrôle qualité :','textmaster').'</label> ';
	?></th>
        <td>
        <?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_qualityTraduction =="true")
		$chkYes = 'checked="checked"';
	else
		$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityTraduction" class="radio_textmasterQualityTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[2]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
        </td>
     </tr>
 <!--       	<tr valign="top">
        <th scope="row"><?php
	echo '<label>'.__('Expertise :','textmaster').'</label> ';
	?></th>
        <td>
        <?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_expertiseTraduction =="true")
		$chkYes = 'checked="checked"';
	else
		$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseTraduction" class="radio_textmasterExpertiseTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[3]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
        </td>
     </tr>-->
        	<tr valign="top">
        <th scope="row"><?php
	echo '<label>'.__('Commande prioritaire :','textmaster').'</label> ';
	?></th>
        <td>
        <?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_priorityTraduction =="true")
		$chkYes = 'checked="checked"';
	else
		$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterPriorityTraduction" class="radio_textmasterPriorityTraduction" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?> <span class="coutOptionTm">+ </span><span><?php echo $aPrices[6]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
        </td>
     </tr>
        <tr valign="top">
        <td colspan="2">
        <?php _e('Message de briefing: ','textmaster') ?>
        <textarea class="large-text" name="textmaster_traductionBriefing" rows="3"><?php echo $textmaster_traductionBriefing; ?></textarea>
		</td>
        </tr>
        </table>
        <br/>
		</div>

	<?php

}

function afficheReadProof(&$tApi, &$languageLevels, &$languages, &$categories, &$infosClient,&$aPrices){

	// params de relecture
	$textmaster_readproofLanguageLevel = get_option_tm('textmaster_readproofLanguageLevel');
	$textmaster_readproofLanguage = get_option_tm('textmaster_readproofLanguage');
	$textmaster_readproofCategorie = get_option_tm('textmaster_readproofCategorie');
	if ($textmaster_readproofCategorie == '')
		$textmaster_readproofCategorie = CATEGORIE_DEFAUT;
	$textmaster_readproofBriefing = get_option_tm('textmaster_readproofBriefing');
	if ($textmaster_readproofBriefing == '')
		$textmaster_readproofBriefing = __("Bonjour,\nMerci de procéder à la correction du texte. Il est impératif de conserver le style et le type de vocabulaire.\nMerci");
	$textmaster_readproofVocabularyType = get_option_tm('textmaster_readproofVocabularyType');
	$textmaster_readproofGrammaticalPerson = get_option_tm('textmaster_readproofGrammaticalPerson');
	$textmaster_readproofTargetReaderGroup = get_option_tm('textmaster_readproofTargetReaderGroup');
	$textmaster_authorReadproof = get_option_tm('textmaster_authorReadproof');
	// les options
	$textmaster_qualityReadproof = get_option_tm('textmaster_qualityReadproof');
	$textmaster_expertiseReadproof = get_option_tm('textmaster_expertiseReadproof');
	$textmaster_priorityReadproof = get_option_tm('textmaster_priorityReadproof');

	?>
	<!-- l'onglet relecture -->
	<div class="t3 settings">
	<table class="form-table">
	<tr valign="top">
	        <td colspan="2">
	        <h3><?php _e('Paramètres de relecture par défaut ','textmaster') ?></h3>
	</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row"><?php _e("Niveau de service: ",'textmaster' ); ?></th>
	        <td>
	<select name="textmaster_readproofLanguageLevel" id="select_textmasterReadProofLanguageLevel" style="width:235px;">
	<?php

		if (isset($languageLevels['readproof'])) {
			foreach($languageLevels['readproof'] as $key => $languageLevel)
			{
				if ($textmaster_readproofLanguageLevel == $languageLevel["name"])
					echo '<option value="'.$languageLevel["name"].'" selected="selected">'.$languageLevel["name"].'</option>';
				else
					echo '<option value="'.$languageLevel["name"].'">'.$languageLevel["name"].'</option>';
			}
		}

		?>
	</select> <span class="coutOptionTm" id="priceTextmasterBaseReadproof">NC</span> <span><?php echo $infosClient['wallet']['currency_code'] ?> / <?php _e('mot','textmaster') ?></span>
	<script>jQuery(document).ready(function($) {getPrice("readproof",1 );});</script>
	</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row"><?php _e("Langue: ",'textmaster' ); ?></th>
	        <td>
	        <?php
		if (isset($languages['message']) && $languages['message'] != '') {
			_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
		}
		else
		{
	?>
	<select name="textmaster_readproofLanguage" style="width:235px;">
	<?php
	foreach($languages as $language)
	{
		if ($textmaster_readproofLanguage == $language['code'])
			echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
		else
			echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
	}
	?>
	</select>
	<?php
		}
		?>
	</td>
	     	</tr>
	 		<tr valign="top">
	        <th scope="row"><?php _e("Categorie: ",'textmaster' ); ?></th>
	        <td>
		 	<?php
		if (isset($categories['message']) && $categories['message'] != '') {
			_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
		}
		else
		{
	?>
		<select name="textmaster_readproofCategorie" style="width:235px;">
		<?php
	foreach($categories as $categorie)
	{
		if ($textmaster_readproofCategorie == $categorie['code'])
			echo '<option value="'.$categorie['code'].'" selected="selected">'.$categorie['value'].'</option>';
		else
			echo '<option value="'.$categorie['code'].'">'.$categorie['value'].'</option>';
	}
	?>
		</select>
		<?php
		}
		?>
	        </td>
	 <!--       </tr>
	        	<tr valign="top">
	        <th scope="row"><?php _e("Type de Vocabulaire:",'textmaster' ); ?></th>
	        <td>
		 	<?php
		if (isset($categories['message']) && $categories['message'] != '') {
			_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
		}
		else
		{
	?>
		<select name="textmaster_readproofVocabularyType" style="width:235px;">
		<?php
	$vocabulary_types = $tApi->getVocabularyTypes();

	foreach($vocabulary_types as $key => $vocabulary_type)
	{
		if ($textmaster_readproofVocabularyType == $key)
			echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
		else
			echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
	}
	?>
				</select>
				<?php
		}
		?>
	        </td>
	        </tr>-->
	 <!--		<tr valign="top">
	        <th scope="row"><?php _e("Personne grammaticale:",'textmaster' ); ?></th>
	        <td>
		 	<?php
		if (isset($categories['message']) && $categories['message'] != '') {
			_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
		}
		else
		{
	?>
			<select name="textmaster_readproofGrammaticalPerson" style="width:235px;">
			<?php
	$grammatical_persons = $tApi->getGrammaticalPersons();

	foreach($grammatical_persons as $key => $grammatical_person)
	{
		if ($textmaster_readproofGrammaticalPerson == $key)
			echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
		else
			echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
	}
	?>
					</select>
					<?php
		}
		?>
	        </td>
	        </tr>-->
<!--	<tr valign="top">
	        <th scope="row"><?php _e("Public Ciblé:",'textmaster' ); ?></th>
	        <td>
		 	<?php
		if (isset($categories['message']) && $categories['message'] != '') {
			_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
	?>
	<select name="textmaster_readproofTargetReaderGroup" style="width:235px;">
	<?php
	$target_reader_groups = $tApi->getTargetReaderGroups();


	foreach($target_reader_groups as $key => $target_reader_group)
	{
	if ($textmaster_readproofTargetReaderGroup == $key)
	echo '<option value="'.$key.'" selected="selected">'.$target_reader_group.'</option>';
	else
	echo '<option value="'.$key.'">'.$target_reader_group.'</option>';
	}
	?>
	</select>
	<?php
	}
	?>
	</td>
	</tr>-->
	<tr valign="top">
        <th scope="row"><?php _e("Auteur:",'textmaster' ); ?></th>
        <td>
	 	<?php
	if (isset($categories['message']) && $categories['message'] != '') {
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		?>
						<select name="textmaster_authorReadproof" style="width:235px;">
						<?php
		$auteurs = $tApi->getAuteurs();

		if (count($auteurs) != 0) {
			foreach($auteurs as $auteur)
			{
				$auteurDesc = '';
				if (isset($auteur['description']) && $auteur['description'] != '')
					$auteurDesc = ' - '.$auteur['description'];

				if ($textmaster_authorReadproof == $auteur['author_id'])
					echo '<option value="'.$auteur['author_id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
				else
					echo '<option value="'.$auteur['author_id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
			}
		}

		?>
								</select>
								<?php
	}
	?>
        </td>
        </tr>
<!--	<tr valign="top">
	<th scope="row"><?php
	echo '<label>'.__('Contrôle qualité :','textmaster').'</label> ';
	?></th>
	<td>
	<?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_qualityReadproof =="true")
	$chkYes = 'checked="checked"';
	else
	$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[2]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
	</td>
	</tr>-->
<!--	<tr valign="top">
	<th scope="row"><?php
	echo '<label>'.__('Expertise :','textmaster').'</label> ';
	?></th>
	<td>
	<?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_expertiseReadproof =="true")
	$chkYes = 'checked="checked"';
	else
	$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertiseReadproof" class="radio_textmasterExpertiseReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[3]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
	</td>
	</tr>-->
	<tr valign="top">
	<th scope="row"><?php
	echo '<label>'.__('Commande prioritaire :','textmaster').'</label> ';
	?></th>
	<td>
	<?php
	$chkNo = '';
	$chkYes = '';
	if($textmaster_priorityReadproof =="true")
	$chkYes = 'checked="checked"';
	else
	$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterPriorityReadproof" class="radio_textmasterPriorityReadproof" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterPriorityReadproof" class="radio_textmasterPriorityReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster');
		?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[6]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
	        </td>
	     </tr>
	     	<tr valign="top">
	        <td colspan="2">
	        <?php _e('Message de briefing: ','textmaster') ?>
	<textarea class="large-text" name="textmaster_readproofBriefing" rows="3"><?php echo $textmaster_readproofBriefing; ?></textarea>
	</td>
	</tr>
	</table>
	<br/>
	</div>
	<?php
}

function afficheAvances(){

	$meta_boxes_def = array();
	$rep_fonctions = array(get_template_directory().'/functions.php');
	if ( is_multisite()) {
		$metaB = array();

		$sites = wp_get_sites();
		foreach ($sites as $site) {
		//	var_dump($site);
			switch_to_blog($site['blog_id']);
	//		$tpl = apply_filters( 'template_directory',  array() );
	//		var_dump($tpl);
			$theme_name = get_template_directory();
			if (!in_array($theme_name.'/functions.php', $rep_fonctions)) {
				require($theme_name.'/functions.php');
				$rep_fonctions[]= $theme_name.'/functions.php';
			//	echo '<br>'.$theme_name.'/functions.php<br>-------------------------------<br>';
				$metaB = array_merge( apply_filters( 'set_tm_metaboxes', array() ), $metaB);
		//		var_dump($meta_boxes_room);
			}

		//	var_dump($metaB);
			//	echo $theme_name;		//	var_dump(apply_filters( 'rwmb_meta_boxes', array() ));
	//		$metaB = array_merge( apply_filters( 'rwmb_meta_boxes', array() ), $metaB);
		}
		restore_current_blog();
//		var_dump($metaB);
		$meta_boxes_def = array_map("unserialize", array_unique(array_map("serialize", $metaB)));
	//	var_dump($meta_boxes);
	}else{
	//	$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
		$meta_boxes_def = apply_filters( 'set_tm_metaboxes', '', $meta_boxes_def );

	}

	$chk_tm_mb_feilds = unserialize(get_option_tm('chk_tm_mb_feilds'));

/*	if ($tm_metabox_pg_prefix == '')
		$tm_metabox_pg_prefix = 'your_prefix';
*/
	?>
	<!-- l'onglet avancés -->
	<div class="t5 settings">
	<table class="form-table">
		<tr valign="top">
	        <td colspan="2">
	        <h3><?php _e('Paramètres avancés ','textmaster') ?></h3>
	</td>
    </tr>
    <tr valign="top">
    <td scope="row" colspan="2"><strong><?php _e("Ne pas utiliser les champs Meta box: ",'textmaster' ); ?></strong></td>
    </tr>
    <tr valign="top">
    <td colspan="2">
    <?php
		if (count($meta_boxes_def) != 0) {
			echo '<ul>';
			foreach ($meta_boxes_def as $meta_box) {
				if (count($meta_box['fields']) != 0) {
					foreach ($meta_box['fields'] as $meta_box_fields) {
						$chked = '';
						if (is_array($chk_tm_mb_feilds) && in_array($meta_box_fields['id'], $chk_tm_mb_feilds))
							$chked = 'checked="checked"';

					//	var_dump($meta_box_fields);
						if ($meta_box_fields['type'] == 'text' || $meta_box_fields['type'] == 'textarea' || $meta_box_fields['type'] == 'wysiwyg'){
							echo '<li>'.'<input type="checkbox" name="chk_tm_mb_feilds[]" value="'.$meta_box_fields['id'].'" '.$chked.'/> ';
							echo $meta_box_fields['id'].''.(isset($meta_box_fields['name']) != FALSE  ? ' ('.$meta_box_fields['name'] .')' : '').'</li>';
						}

					}
				}

			}
			echo '</ul>';
		}else {
			echo __('Lister les meta-box à filtrer (<strong>field id</strong> séparés par des virgules): ', 'textmaster').'<br/>';
			echo '<textarea name="area_tm_mb" style="width:90%">'.implode(', ', $chk_tm_mb_feilds).'</textarea><br/>';
		}
//	echo '<em>'. __('Pour passer vos meta-box au plugin TextMaster utilisez le filtre : add_filter( "set_tm_metaboxes", "votre_fontion" )', 'textmaster').'</em><br/>';
//	echo '<br/>'.__('Fichiers functions.php inclus:', 'textmaster').'<ul><li>'.implode('</li><li>', $rep_fonctions).'</li></ul>';
	?>

	</tr>
	</table>
	</div>
	<?php
}


function checkPluginsVersion(){
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$str = '';
	$all_plugins = get_plugins();
	if (count($all_plugins) != 0) {
		foreach ($all_plugins as $plugin) {
			if ($plugin['Name'] == 'Meta Box'){
				if( version_compare(PLUGIN_METABOX_VERSION, $plugin['Version']) == -1)
					$str .= __('Vous utilisez le plugin: ').'<strong>'.$plugin['Name'].'</strong> '.__('le plugin TextMaster peut ne pas être pleinement compatible avec votre version').' ('.$plugin['Version'].' > '.PLUGIN_METABOX_VERSION.')<br/>';
		//		if (get_option_tm('tm_metabox_pg_prefix') == '')
		//			$str .= __('Merci de renseigner votre prefix dans l\'onglet Avancé pour le plugin: ').'<strong>'.$plugin['Name'].'</strong><br/>';
			}

			if ($plugin['Name'] == 'WPML Multilingual CMS' && version_compare(PLUGIN_WPML_VERSION, $plugin['Version']) == -1) {
				$str .= __('Vous utilisez le plugin: ').'<strong>'.$plugin['Name'].'</strong> '.__('le plugin TextMaster peut ne pas être pleinement compatible avec votre version').' ('.$plugin['Version'].' > '.PLUGIN_WPML_VERSION.')<br/>';
			}

			if ($plugin['Name'] == 'Advanced Custom Fields' && version_compare(PLUGIN_ACF_VERSION, $plugin['Version']) == -1) {
				$str .= __('Vous utilisez le plugin: ').'<strong>'.$plugin['Name'].'</strong> '.__('le plugin TextMaster peut ne pas être pleinement compatible avec votre version').' ('.$plugin['Version'].' > '.PLUGIN_ACF_VERSION.')<br/>';
			}
		}
	}
//	print_r($all_plugins);
//	if (class_exists('SitePress')) {
//	}

	if ($str != '') {
		echo '<div class="update-nag" style="padding:10px;width:88%;">'.$str.'</div>';

	}
}
?>
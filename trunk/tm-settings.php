<?php
function texmaster_admin_actions() {

	add_options_page("TextMaster", "TextMaster", 1, "Textmaster", "texmaster_admin");
//	if (get_option('textmaster_useRedaction') == 'Y')
//	{
		add_submenu_page('edit.php?post_type=textmaster_redaction', __( 'Tous les projets TextMaster','textmaster'), __( 'Suivi des projets','textmaster'), 'manage_options', 'textmaster-projets', 'admin_textmaster_projets' );
		add_submenu_page('edit.php?post_type=textmaster_redaction', __( 'Paramétrage TextMaster' ,'textmaster'), __( 'Réglages' ,'textmaster'), 'manage_options', 'options-general.php?page=Textmaster');//'texmaster_admin');
//	}


}



function texmaster_admin() {

	if(isset($_POST['textmaster_hidden']) && $_POST['textmaster_hidden'] == 'Y') {
		$textmaster_email = $_POST['textmaster_email'];
		update_option('textmaster_email', $textmaster_email);
		$textmaster_password = $_POST['textmaster_password'];
		update_option('textmaster_password', $textmaster_password);

		if ($textmaster_password != '' && $textmaster_email != '') {
			$oOAuth = new TextMaster_OAuth2();
			$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
			if (trim($token) != '') {
				$infosUser = $oOAuth->getUserInfos($token);

				$textmaster_api_key = $infosUser['api_info']['api_key'];
				update_option('textmaster_api_key', $textmaster_api_key);
				$textmaster_api_secret = $infosUser['api_info']['api_secret'];
				update_option('textmaster_api_secret', $textmaster_api_secret);

				textmaster_api::sendTracker();
			}
			else
			{
				update_option('textmaster_api_key', '');
				update_option('textmaster_api_secret', '');
				echo '<div class="error" style="padding:10px;">'.__('Impossbile de se connecter à TextMaster, merci de vérifier votre email et mot de passe.','textmaster').'</div>';
			}

		}

		if (isset($_POST['use_traduction']) && $_POST['use_traduction'] == 'Y')
			$textmaster_useTraduction = $_POST['use_traduction'];
		else
			$textmaster_useTraduction = 'N';
		update_option('textmaster_useTraduction', $textmaster_useTraduction);
		if (isset($_POST['textmaster_useTraductionBulk']) && $_POST['textmaster_useTraductionBulk'] == 'Y')
			$textmaster_useTraductionBulk = $_POST['textmaster_useTraductionBulk'];
		else
			$textmaster_useTraductionBulk = 'N';
		update_option('textmaster_useTraductionBulk', $textmaster_useTraductionBulk);

		if (isset($_POST['use_readproof']) && $_POST['use_readproof'] == 'Y')
			$textmaster_useReadproof = $_POST['use_readproof'];
		else
			$textmaster_useReadproof = 'N';
		update_option('textmaster_useReadproof', $textmaster_useReadproof);
		if (isset($_POST['textmaster_useReadproofBulk']) && $_POST['textmaster_useReadproofBulk'] == 'Y')
			$textmaster_useReadproofBulk = $_POST['textmaster_useReadproofBulk'];
		else
			$textmaster_useReadproofBulk = 'N';
		update_option('textmaster_useReadproofBulk', $textmaster_useReadproofBulk);

		if (isset($_POST['textmaster_useRedaction']) && $_POST['textmaster_useRedaction'] == 'Y')
			$textmaster_useRedaction = $_POST['textmaster_useRedaction'];
		else
			$textmaster_useRedaction = 'N';
		update_option('textmaster_useRedaction', $textmaster_useRedaction);


		// params de redaction
		$textmaster_redactionLanguageLevel = $_POST['textmaster_redactionLanguageLevel'];
		update_option('textmaster_redactionLanguageLevel', $textmaster_redactionLanguageLevel);
		$textmaster_redactionLanguage = $_POST['textmaster_redactionLanguage'];
		update_option('textmaster_redactionLanguage', $textmaster_redactionLanguage);
		$textmaster_redactionCategorie = $_POST['textmaster_redactionCategorie'];
		update_option('textmaster_redactionCategorie', $textmaster_redactionCategorie);
		$textmaster_vocabularyType = $_POST['textmaster_vocabularyType'];
		update_option('textmaster_vocabularyType', $textmaster_vocabularyType);
		$textmaster_grammaticalPerson = $_POST['textmaster_grammaticalPerson'];
		update_option('textmaster_grammaticalPerson', $textmaster_grammaticalPerson);
		$textmaster_targetReaderGroup = $_POST['textmaster_targetReaderGroup'];
		update_option('textmaster_targetReaderGroup', $textmaster_targetReaderGroup);
		if (isset($_POST['textmaster_Template'])){
			$textmaster_Template = $_POST['textmaster_Template'];
			update_option('textmaster_Template', $textmaster_Template);
		}
		if (isset($_POST['textmaster_author'])){
			$textmaster_author = $_POST['textmaster_author'];
			update_option('textmaster_author', $textmaster_author);
		}

		// les options
		if (isset($_POST['radio_textmasterQualityRedaction'])) {
			$textmaster_qualityRedaction = $_POST['radio_textmasterQualityRedaction'];
			update_option('textmaster_qualityRedaction', $textmaster_qualityRedaction);
		}
		$textmaster_expertiseRedaction = $_POST['radio_textmasterExpertiseRedaction'];
		update_option('textmaster_expertiseRedaction', $textmaster_expertiseRedaction);
		$textmaster_priorityRedaction = $_POST['radio_textmasterPriorityRedaction'];
		update_option('textmaster_priorityRedaction', $textmaster_priorityRedaction);

		// params de relecture
		$textmaster_readproofLanguageLevel = $_POST['textmaster_readproofLanguageLevel'];
		update_option('textmaster_readproofLanguageLevel', $textmaster_readproofLanguageLevel);
		$textmaster_readproofLanguage = $_POST['textmaster_readproofLanguage'];
		update_option('textmaster_readproofLanguage', $textmaster_readproofLanguage);
		$textmaster_readproofCategorie = $_POST['textmaster_readproofCategorie'];
		update_option('textmaster_readproofCategorie', $textmaster_readproofCategorie);
		$textmaster_readproofBriefing = $_POST['textmaster_readproofBriefing'];
		update_option('textmaster_readproofBriefing', $textmaster_readproofBriefing);
		$textmaster_readproofVocabularyType = $_POST['textmaster_readproofVocabularyType'];
		update_option('textmaster_readproofVocabularyType', $textmaster_readproofVocabularyType);
		$textmaster_readproofGrammaticalPerson = $_POST['textmaster_readproofGrammaticalPerson'];
		update_option('textmaster_readproofGrammaticalPerson', $textmaster_readproofGrammaticalPerson);
		$textmaster_readproofTargetReaderGroup = $_POST['textmaster_readproofTargetReaderGroup'];
		update_option('textmaster_readproofTargetReaderGroup', $textmaster_readproofTargetReaderGroup);
		if (isset($_POST['textmaster_authorTraduction'])){
			$textmaster_authorTraduction = $_POST['textmaster_authorTraduction'];
			update_option('textmaster_authorTraduction', $textmaster_authorTraduction);
		}
		// les options
		if (isset($_POST['radio_textmasterQualityReadproof'])) {
			$textmaster_qualityReadproof = $_POST['radio_textmasterQualityReadproof'];
			update_option('textmaster_qualityReadproof', $textmaster_qualityReadproof);
		}
		$textmaster_expertiseReadproof = $_POST['radio_textmasterExpertiseReadproof'];
		update_option('textmaster_expertiseReadproof', $textmaster_expertiseReadproof);
		$textmaster_priorityReadproof = $_POST['radio_textmasterPriorityReadproof'];
		update_option('textmaster_priorityReadproof', $textmaster_priorityReadproof);



		// params de traduction
		$textmaster_traductionLanguageLevel = $_POST['textmaster_traductionLanguageLevel'];
		update_option('textmaster_traductionLanguageLevel', $textmaster_traductionLanguageLevel);
		$textmaster_traductionLanguageSource = $_POST['textmaster_traductionLanguageSource'];
		update_option('textmaster_traductionLanguageSource', $textmaster_traductionLanguageSource);
		$textmaster_traductionLanguageDestination = $_POST['textmaster_traductionLanguageDestination'];
		update_option('textmaster_traductionLanguageDestination', $textmaster_traductionLanguageDestination);
		$textmaster_traductionCategorie = $_POST['textmaster_traductionCategorie'];
		update_option('textmaster_traductionCategorie', $textmaster_traductionCategorie);
		$textmaster_traductionBriefing = $_POST['textmaster_traductionBriefing'];
		update_option('textmaster_traductionBriefing', $textmaster_traductionBriefing);
		$textmaster_traductionVocabularyType = $_POST['textmaster_traductionVocabularyType'];
		update_option('textmaster_traductionVocabularyType', $textmaster_traductionVocabularyType);
		$textmaster_traductionGrammaticalPerson = $_POST['textmaster_traductionGrammaticalPerson'];
		update_option('textmaster_traductionGrammaticalPerson', $textmaster_traductionGrammaticalPerson);
		$textmaster_traductionTargetReaderGroup = $_POST['textmaster_traductionTargetReaderGroup'];
		update_option('textmaster_traductionTargetReaderGroup', $textmaster_traductionTargetReaderGroup);
		if (isset($_POST['textmaster_authorReadproof'])){
			$textmaster_authorReadproof = $_POST['textmaster_authorReadproof'];
			update_option('textmaster_authorReadproof', $textmaster_authorReadproof);
		}
		// les options
		if (isset($_POST['radio_textmasterQualityTraduction'])) {
			$textmaster_qualityTraduction = $_POST['radio_textmasterQualityTraduction'];
			update_option('textmaster_qualityTraduction', $textmaster_qualityTraductionf);
		}
		$textmaster_expertiseTraduction = $_POST['radio_textmasterExpertiseTraduction'];
		update_option('textmaster_expertiseTraduction', $textmaster_expertiseTraduction);
		$textmaster_priorityTraduction = $_POST['radio_textmasterPriorityTraduction'];
		update_option('textmaster_priorityTraduction', $textmaster_priorityTraduction);

		echo '<div class="updated" style="padding:10px;">'.__('Les paramètres ont été sauvegaradés.','textmaster').'</div>';
	} else {

		$textmaster_email = get_option('textmaster_email');
		$textmaster_password = get_option('textmaster_password');

		//$textmaster_api_key = get_option('textmaster_api_key');
		//$textmaster_api_secret = get_option('textmaster_api_secret');

		$textmaster_useRedaction = get_option('textmaster_useRedaction');
		$textmaster_useTraduction = get_option('textmaster_useTraduction');
		$textmaster_useTraductionBulk = get_option('textmaster_useTraductionBulk');
		$textmaster_useReadproof = get_option('textmaster_useReadproof');
		$textmaster_useReadproofBulk = get_option('textmaster_useReadproofBulk');

	}

	if ($textmaster_password != '' && $textmaster_email != '') {
		$oOAuth = new TextMaster_OAuth2();
		$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
		$infosUser = $oOAuth->getUserInfos($token);

		$lang = explode('_', get_locale());
		$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE.'?auth_token='.$infosUser['authentication_token'];
	//	echo $urlAchat;
	}
	else {
		$lang = explode('_', get_locale());
		$urlAchat = 'http://'.$lang[0].'.'.URL_TM_BOUTIQUE;
	}

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');
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
		</ul>

		<!-- l'onglet général-->
		<div class="t1">
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
			echo  '<strong>'.$infosClient['wallet']['current_money'].' '.$infosClient['wallet']['currency_code'].'</strong>.';
			_e('Vous pouvez créditer votre compte TextMaster à cette adresse :','textmaster');
			echo '<a href="'. $urlAchat.'" target="_blank">TextMaster</a><br/><br/>';
		}
		 ?>
        <?php
		_e('Ces informations  correspondent à votre compte TextMaster, si vous n\'avez pas encore créé de compte, vous pouvez le faire en utilisant ce lien:','textmaster');
		echo ' <a href="javascript:void(tb_show(\''.__('Créer un compte TextMaster','textmaster').'\',\''. plugins_url('', __FILE__).'/createUser.php?height=700&width=630&TB_iframe=true\'));">';
		_e('Créer un compte TextMaster.','textmaster'); ?></a><br/>
		</td>
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
       <input type="checkbox" id="textmaster_useTraductionBulk" name="textmaster_useTraductionBulk" value="Y" <?php if($textmaster_useTraductionBulk != 'N'){ echo "checked=\"checked\"";} ?> />
        <?php _e("Activer les actions groupées ",'textmaster' ); ?>
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
	?>
		<div style="clear:both;"></div>
		<br/>
		<br/>
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Sauvegardez les paramètres','textmaster' ) ?>" />
        </p>
    </form>
    </div>
	<?php
}

function afficheRedaction(&$tApi, &$languageLevels, &$languages, &$categories, &$infosClient, &$aPrices){

	// params de redaction
	$textmaster_redactionLanguageLevel = get_option('textmaster_redactionLanguageLevel');
	$textmaster_redactionLanguage = get_option('textmaster_redactionLanguage');
	$textmaster_redactionCategorie = get_option('textmaster_redactionCategorie');
	$textmaster_vocabularyType = get_option('textmaster_vocabularyType');
	$textmaster_grammaticalPerson = get_option('textmaster_grammaticalPerson');
	$textmaster_targetReaderGroup = get_option('textmaster_targetReaderGroup');
	$textmaster_Template = get_option('textmaster_Template');
	$textmaster_author = get_option('textmaster_author');
	// les options
	$textmaster_qualityRedaction = get_option('textmaster_qualityRedaction');
	$textmaster_expertiseRedaction = get_option('textmaster_expertiseRedaction');
	$textmaster_priorityRedaction = get_option('textmaster_priorityRedaction');

	?>
	     	<!-- l'onglet rédaction -->
		<div class="t2">
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
	foreach($languageLevels as $key => $languageLevel)
	{
		if ($textmaster_redactionLanguageLevel == $key)
			echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
		else
			echo '<option value="'.$key.'">'.$languageLevel.'</option>';
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
		foreach($languages as $language)
		{
			if ($textmaster_redactionLanguage == $language['code'])
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


		foreach($auteurs as $auteur)
		{
			$auteurDesc = '';
			if ($auteur['description'] != '')
				$auteurDesc = ' - '.$auteur['description'];

			if ($textmaster_author == $auteur['author_id'])
				echo '<option value="'.$auteur['author_id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
			else
				echo '<option value="'.$auteur['author_id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
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
        <th scope="row"><?php
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
     </tr>
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
	$textmaster_traductionLanguageLevel = get_option('textmaster_traductionLanguageLevel');
	$textmaster_traductionLanguageSource = get_option('textmaster_traductionLanguageSource');
	$textmaster_traductionLanguageDestination = get_option('textmaster_traductionLanguageDestination');
	$textmaster_traductionCategorie = get_option('textmaster_traductionCategorie');
	$textmaster_traductionBriefing = get_option('textmaster_traductionBriefing');
	if ($textmaster_traductionBriefing == '')
		$textmaster_traductionBriefing = __("Bonjour,\nMerci de procéder à la traduction du texte. Il est impératif de conserver le style et le type de vocabulaire.\nMerci");
	$textmaster_traductionVocabularyType = get_option('textmaster_traductionVocabularyType');
	$textmaster_traductionGrammaticalPerson = get_option('textmaster_traductionGrammaticalPerson');
	$textmaster_traductionTargetReaderGroup = get_option('textmaster_traductionTargetReaderGroup');
	$textmaster_authorTraduction = get_option('textmaster_authorTraduction');

	// les options
	$textmaster_qualityTraduction = get_option('textmaster_qualityTraduction');
	$textmaster_expertiseTraduction = get_option('textmaster_expertiseTraduction');
	$textmaster_priorityTraduction = get_option('textmaster_priorityTraduction');

	?>
	  	<!-- l'onglet traduction -->
		<div class="t4">
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
	foreach($languageLevels as $key => $languageLevel)
	{
		if ($textmaster_traductionLanguageLevel == $key)
			echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
		else
			echo '<option value="'.$key.'">'.$languageLevel.'</option>';
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
		foreach($languages as $language)
		{
			if ($textmaster_traductionLanguageSource == $language['code'])
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
		foreach($categories as $categorie)
		{
			if ($textmaster_traductionCategorie == $categorie['code'])
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


		foreach($auteurs as $auteur)
		{
			$auteurDesc = '';
			if ($auteur['description'] != '')
				$auteurDesc = ' - '.$auteur['description'];

			if ($textmaster_authorTraduction == $auteur['author_id'])
				echo '<option value="'.$auteur['author_id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
			else
				echo '<option value="'.$auteur['author_id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
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
        	<tr valign="top">
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
     </tr>
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
	$textmaster_readproofLanguageLevel = get_option('textmaster_readproofLanguageLevel');
	$textmaster_readproofLanguage = get_option('textmaster_readproofLanguage');
	$textmaster_readproofCategorie = get_option('textmaster_readproofCategorie');
	$textmaster_readproofBriefing = get_option('textmaster_readproofBriefing');
	if ($textmaster_readproofBriefing == '')
		$textmaster_readproofBriefing = __("Bonjour,\nMerci de procéder à la correction du texte. Il est impératif de conserver le style et le type de vocabulaire.\nMerci");
	$textmaster_readproofVocabularyType = get_option('textmaster_readproofVocabularyType');
	$textmaster_readproofGrammaticalPerson = get_option('textmaster_readproofGrammaticalPerson');
	$textmaster_readproofTargetReaderGroup = get_option('textmaster_readproofTargetReaderGroup');
	$textmaster_authorReadproof = get_option('textmaster_authorReadproof');
	// les options
	$textmaster_qualityReadproof = get_option('textmaster_qualityReadproof');
	$textmaster_expertiseReadproof = get_option('textmaster_expertiseReadproof');
	$textmaster_priorityReadproof = get_option('textmaster_priorityReadproof');

	?>
	<!-- l'onglet relecture -->
	<div class="t3">
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
		foreach($languageLevels as $key => $languageLevel)
		{
	if ($textmaster_readproofLanguageLevel == $key)
		echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
	else
		echo '<option value="'.$key.'">'.$languageLevel.'</option>';
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
						<select name="textmaster_authorReadproof" style="width:235px;">
						<?php
		$auteurs = $tApi->getAuteurs();


		foreach($auteurs as $auteur)
		{
			$auteurDesc = '';
			if ($auteur['description'] != '')
				$auteurDesc = ' - '.$auteur['description'];

			if ($textmaster_authorReadproof == $auteur['author_id'])
				echo '<option value="'.$auteur['author_id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
			else
				echo '<option value="'.$auteur['author_id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
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
	if($textmaster_qualityReadproof =="true")
	$chkYes = 'checked="checked"';
	else
	$chkNo = 'checked="checked"';

	echo '<input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQualityReadproof" class="radio_textmasterQualityReadproof" value="false" '.$chkNo.'/> '.__('Non','textmaster');
	?><span class="coutOptionTm">+ </span><span><?php echo $aPrices[2]['value'] ?></span> <?php echo $infosClient['wallet']['currency_code'] ?> /  <?php _e('mot','textmaster') ?>
	</td>
	</tr>
	<tr valign="top">
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
	</tr>
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

?>
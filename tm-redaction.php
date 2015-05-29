<?php

function textmaster_redaction_type() {
	$args = array(
	'labels' => array(
		'name' => __('TextMaster','textmaster'),
		'all_items' => __('Rédactions TM','textmaster'),
		'singular_name' => __('Rédaction TM','textmaster'),
		'add_new' => __('Nouvelle rédaction','textmaster'),
		'add_new_item' => __('Créer une rédaction TextMaster','textmaster'),
		'edit_item' => __('Editer une rédaction TextMaster','textmaster'),
		'not_found' => __('Aucune rédaction TextMaster trouvée','textmaster')
	),
	'public' => false,
	'show_ui' => true,
	'menu_position' => 7,
	'menu_icon' => plugins_url('images/tm-icon.png', __FILE__),
	'_builtin' => false, // It's a custom post type, not built in
	'_edit_link' => 'post.php?post=%d',
	'capability_type' => 'post',
	'hierarchical' => false,
	'rewrite' => array("slug" => "textmaster_redaction"),
	'query_var' => "textmaster_redaction", // This goes to the WP_Query schema
	'supports' => array('title', 'editor')
	);
	register_post_type( 'textmaster_redaction' , $args ); // enregistrement de l'entité projet basé sur les arguments ci-dessus
}

function textmaster_redaction_updated_messages($messages){
	global $post, $post_ID;

	$messages['textmaster_redaction'] = array(
		1 =>  __('Projet de rédaction sauvegardé.', 'textmaster'),
		2 => '',
		3 => '',
		4 => __('Projet de rédaction sauvegardé.', 'textmaster'),
		5 => '',
		6 =>  __('Projet de rédaction sauvegardé.', 'textmaster'),
		7 => __('Projet de rédaction sauvegardé.', 'textmaster'),
		8 => '',
		9 => '',
		10 => '',
	);

	return $messages;
}

function add_new_textmaster_redaction_columns($columns){
	$temp = $columns['date'];
	unset( $columns['date']);
	$columns['status'] = __('Status','textmaster' );
	$columns['Date'] = $temp;
	return $columns;
}

function manage_textmaster_redaction_columns($column_name, $id){
	global $wpdb;

	switch ($column_name) {
		case 'status':
			if (get_post_meta($id, 'textmasterId', true) == '')
				echo __('Brouillon','textmaster' );
			else
			{
				$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
			//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
			//	$tApi->keyapi =  get_option_tm('textmaster_api_key');
			//	$idProjet = get_post_meta($id,'textmasterId', TRUE);
				echo $tApi->getLibStatus( get_post_meta($id,'textmasterStatusRedaction', TRUE));
			}
			break;
		case 'Date':
			echo date_i18n(get_option_tm('date_format') ,get_the_time( 'U', $post_id ));
			break;
		default:
			break;
	}
}

function textmaster_redaction_action_row($actions, $post){
	if ($post->post_type =="textmaster_redaction"){
		$temp = $actions['edit'];
		unset($actions);
		$actions['edit'] = $temp;
	}
	return $actions;
}

function wpt_textmaster_icons() {
	?>
    <style type="text/css" media="screen">
		#icon-edit.icon32-posts-textmaster_redaction {background: url(<?php echo  plugins_url('images/tm-logo.png', __FILE__) ?>) no-repeat;}
	</style>
	<link href="<?php echo plugins_url('textmaster.css' , __FILE__ ) ?>" rel="stylesheet" type="text/css" />
	<?php
}

function remove_all_media_buttons(){
	global $current_screen;

	if ('textmaster_redaction' === $current_screen->post_type)
	{
		remove_all_actions('media_buttons');
		add_filter( 'user_can_richedit', create_function('' , 'return false;') , 50);
	}
}

function textmaster_defaut_content( $content ) {
	global $current_screen;

	if ('textmaster_redaction' === $current_screen->post_type)
	{
			$content = __("C'est ici que vous donnez les instructions concernant votre demande de rédaction.",'textmaster');
	}
	return $content;
}


function wp_texmaster_redaction_defaut_metaboxes(){
	global $post;

	$result = '';

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

//	$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
//	$idDocument = get_post_meta($post->ID,'textmasterDocumentIdTrad', TRUE);

//	if ($idDocument != '')
//		$result = $tApi->getDocumentStatus($idProjet, $idDocument);
//	else if ($idProjet != '')
//		$result = $tApi->getProjetStatus($idProjet);
	// la trad n'est pas faite chez TM
//	if ($result != 'in_review')
		redaction_defaut_metaboxes_pre($tApi);
//	else
//		metaboxes_post($tApi, 'redaction',$idProjet, $idDocument);

}

function redaction_defaut_metaboxes_pre(&$tApi){
	global $post;

	$txtRet = '';

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//	$tApi->keyapi =  get_option_tm('textmaster_api_key');
	$userTM = $tApi->getUserInfos();

	if ($tApi->secretapi == '' && $tApi->keyapi == '') {
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
		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCat" name="select_textmasterCat" style="width:235px;">';
		if (get_post_meta($post->ID, 'textmasterCategorie', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorie', true);
		else
			$catSelected = get_option_tm('textmaster_redactionCategorie');

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
		echo '<select id="select_textmasterLanguageLevel" name="select_textmasterLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterLanguageLevel', true);
		else
			$languageLevelSelected = get_option_tm('textmaster_redactionLanguageLevel');

		foreach($languageLevels['copywrite'] as $key => $languageLevel)
		{
			if ($languageLevelSelected == $languageLevel["name"])
				echo '<option value="'.$languageLevel["name"].'" selected="selected">'.$languageLevel["name"].'</option>';
			else
				echo '<option value="'.$languageLevel["name"].'">'.$languageLevel["name"].'</option>';
		}
		echo '</select><br/>';
		echo '<div>'. __('Coût', 'textmaster').' : <strong id="priceTextmasterBase">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';


		$languages = $tApi->getLanguages();

		echo '<label>'.__('Langue :','textmaster').'</label><br/>';
		echo '<select id="select_textmasterLang" name="select_textmasterLang" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterLang', true) != '')
			$languageSelected = get_post_meta($post->ID, 'textmasterLang', true);
		else
			$languageSelected = get_option_tm('textmaster_redactionLanguage');

		foreach($languages as $language)
		{
			if ($languageSelected == $language['code'])
				echo '<option value="'.$language['code'].'" selected="selected">'.$language['value'].'</option>';
			else
				echo '<option value="'.$language['code'].'">'.$language['value'].'</option>';
		}
		echo '</select><br/><br/>';

		$aWordCountRule[0] = __('+/- 10%','textmaster');
		$aWordCountRule[1] = __('Au moins','textmaster');
		$aWordCountRule[2] = __('Maximum','textmaster');

		if (get_post_meta($post->ID, 'textmasteWordCountRule', true) != '')
			$wordCountRuleSelected = get_post_meta($post->ID, 'textmasteWordCountRule', true);
		else
			$wordCountRuleSelected = 1;

		echo '<label>'.__('Nombre de mot :','textmaster').'</label><br/>';
		echo '<select id="select_textmasteWordCountRule" name="select_textmasteWordCountRule" >';
		foreach($aWordCountRule as $key => $wordCountRule)
		{
			if ($wordCountRuleSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$wordCountRule.'</option>';
			else
				echo '<option value="'.$key.'">'.$wordCountRule.'</option>';
		}
		echo '</select>';
		echo '<input type="text" id="text_textmasterWordCount" name="text_textmasterWordCount" style="width:145px;text-align:right;" value="'.get_post_meta($post->ID, 'textmasterWordCount', true).'" /><br/><br/>';

		if (get_post_meta($post->ID, 'textmasterQualityRedaction', true) != '')
			$textmaster_qualityRedaction = get_post_meta($post->ID, 'textmasterQualityRedaction', true);
		else
			$textmaster_qualityRedaction = get_option_tm('textmaster_qualityRedaction');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_qualityRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

		echo '<label class="options_pricetm">'.__('Contrôle qualité :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterQuality" class="radio_textmasterQuality" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterQuality" class="radio_textmasterQuality" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterQuality">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo '</div><br/>';

		if (get_post_meta($post->ID, 'textmasterExpertiseRedaction', true) != '')
			$textmaster_expertiseRedaction = get_post_meta($post->ID, 'textmasterExpertiseRedaction', true);
		else
			$textmaster_expertiseRedaction = get_option_tm('textmaster_expertiseRedaction');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_expertiseRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

//		echo '<label class="options_pricetm">'.__('Expertise :','textmaster').'</label> ';
//		echo '<input type="radio" name="radio_textmasterExpertise" class="radio_textmasterExpertise" value="true" '.$chkYes.' /> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterExpertise" class="radio_textmasterExpertise" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
//		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterExpertise">NC</strong> ';
//		if (is_array($infosClient))
//			echo $infosClient['wallet']['currency_code'] ;
//		echo '</div><br/>';

		if (get_post_meta($post->ID, 'textmaster_priorityRedaction', true) != '')
			$textmaster_priorityRedaction = get_post_meta($post->ID, 'textmasterPriorityRedaction', true);
		else
			$textmaster_priorityRedaction = get_option_tm('textmaster_priorityRedaction');
		$chkNo = '';
		$chkYes = '';
		if($textmaster_priorityRedaction =="false")
			$chkNo = 'checked="checked"';
		else
			$chkYes = 'checked="checked"';

		echo '<label class="options_pricetm">'.__('Commande prioritaire :','textmaster').'</label> ';
		echo '<input type="radio" name="radio_textmasterPriority" class="radio_textmasterPriority" value="true" '.$chkYes.'/> '.__('Oui','textmaster').' <input type="radio" name="radio_textmasterPriority" class="radio_textmasterPriority" value="false" '.$chkNo.'/> '.__('Non','textmaster').'<br/>';
		echo '<div>'. __('Coût', 'textmaster').' : + <strong id="priceTextmasterPriority">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'] ;
		echo '</div><br/>';

		$disableRedaction = '';
		$idProjet = get_post_meta($post->ID,'textmasterId', TRUE);
		if ($idProjet != '') {
			//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits
			$result = $tApi->getProjetStatus($idProjet);

			if ($result == 'in_review')
			{
				$txtRet = __('Cet article a &eacute;t&eacute; rédigé.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				$disableRedaction = 'disabled=disabled';
			}
			else if ( $result == 'in_progress' )
			{
				$txtRet = __('Cet article est en cours de rédaction.','textmaster');
				$disableRedaction = 'disabled=disabled';
			}
			else if ($result == 'completed') {
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la rédaction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la rédaction','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				$disableRedaction = 'disabled=disabled';
			}
		}
		else if ($post->post_content == '' ||  $post->post_title == '')
			$disableRedaction = 'disabled="disabled"';


		echo wp_texmaster_redaction_options_metaboxes($tApi);
		echo wp_texmaster_redaction_authors_metaboxes($tApi);

		echo '<br/><img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmRedaction" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmRedaction"> '.__('Merci de patienter', 'textmaster').'</div>';
		?>

		<br/><br/>
		<div class="major-publishing-actions">
		<div id="publishing-action">
		<input name="original_publish" type="hidden" id="original_publish" value="Publier">
		<input type="submit" name="publish" id="publish" class="button-primary" value="<?php echo __('Sauver','textmaster') ?>" tabindex="5" accesskey="p">
		<br/><br/><input name="redaction" type="button" class="button button-highlighted" id="redaction" tabindex="6" accesskey="r" value="<?php echo __('Lancer la rédaction','textmaster') ?>" <?php echo $disableRedaction ?>>
		</div>
		</div>
		<div style="clear:both"></div>
<?php

		echo '<br/><div id="resultTextmaster" class="misc-pub-section">'.$txtRet.'</div>';
		echo '<div class="misc-pub-section">'. __('Coût', 'textmaster').' : <strong id="priceTextmaster">NC</strong> ';
		if (is_array($infosClient))
			echo $infosClient['wallet']['currency_code'];
		echo ' (<span class="nbMots"></span> '.__('mots','textmaster').')</div><div class="misc-pub-section">'.__('Crédits:', 'textmaster');
		if (is_array($infosClient))
			echo ' <strong>'. $infosClient['wallet']['current_money'].' '.$infosClient['wallet']['currency_code'].'</strong> ';
		echo '<a href="'.$urlAchat.'" target="_blank">'. __('Créditer mon compte', 'textmaster').'</a></div><br/><br/>';
		echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; getPrice("redaction");</script>';


	}
}

function wp_texmaster_redaction_options_metaboxes(&$tApi){
	global $post;


//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
//	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$categories = $tApi->getCategories();
	if ($tApi->secretapi == '' && $tApi->keyapi == ''){
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else
	{
		echo '<a id="showOptionsRedaction"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Plus d\'options' ,'textmaster').'</a><br/>';
		echo '<div id="optionsRedaction">';
		echo '<label>'.__('Mots-clés:','textmaster').'</label> ('.__('séparés par des virgules','textmaster').')';
		echo '<textarea style="width:235px;" name="text_textmasterKeywords" id="text_textmasterKeywords">'.get_post_meta($post->ID, 'textmasterKeywords', true).'</textarea><br/>';

		if (get_post_meta($post->ID, 'textmasterKeywordsRepeatCount', true) != '')
			$KeywordsRepeatCountSelected = get_post_meta($post->ID, 'textmasterKeywordsRepeatCount', true);
		else
			$KeywordsRepeatCountSelected = 1;
		_e('A répéter :','textmaster');
		echo '<input type="text" id="text_textmasterKeywordsRepeatCount" name="text_textmasterKeywordsRepeatCount" style="width:50px;text-align:right;" value="'.$KeywordsRepeatCountSelected.'" />'. __('fois','textmaster').'<br/><br/>';

		echo '<label>'.__('Type de Vocabulaire:','textmaster').'</label>';
		echo '<select id="select_textmasterVocabularyType" name="select_textmasterVocabularyType" style="width:235px;"><br/><br/>';

		if (get_post_meta($post->ID, 'textmasterVocabularyType', true) != '')
			$vocabulary_typeSelected = get_post_meta($post->ID, 'textmasterVocabularyType', true);
		else
			$vocabulary_typeSelected = get_option_tm('textmaster_vocabularyType');

		$vocabulary_types = $tApi->getVocabularyTypes();
		foreach($vocabulary_types as $key => $vocabulary_type)
		{
			if ($vocabulary_typeSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
			else
				echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
		}
		echo '</select><br/><br/>';


		echo '<label>'.__('Personne grammaticale:','textmaster').'</label>';
		echo '<select id="select_textmasterGrammaticalPerson" name="select_textmasterGrammaticalPerson" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterGrammaticalPerson', true) != '')
			$grammatical_personSelected = get_post_meta($post->ID, 'textmasterGrammaticalPerson', true);
		else
			$grammatical_personSelected = get_option_tm('textmaster_grammaticalPerson');

		$grammatical_persons = $tApi->getGrammaticalPersons();
		foreach($grammatical_persons as $key => $grammatical_person)
		{
			if ($grammatical_personSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
			else
				echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
		}
		echo '</select><br/><br/>';


		echo '<label>'.__('Public Ciblé:','textmaster').'</label>';
		echo '<select id="select_textmasterTargetReaderGroup" name="select_textmasterTargetReaderGroup" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTargetReaderGroup', true) != '')
			$target_reader_groupSelected = get_post_meta($post->ID, 'textmasterTargetReaderGroup', true);
		else
			$target_reader_groupSelected = get_option_tm('textmaster_targetReaderGroup');

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

function wp_texmaster_redaction_templates_metaboxes(){
	global $post;


	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
//	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$templates = $tApi->getTemplates();

	if ($tApi->secretapi == '' && $tApi->keyapi == '')
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	else
	{
		if (get_post_meta($post->ID, 'textmasterTemplate', true) != '')
			$templateSelected = get_post_meta($post->ID, 'textmasterTemplate', true);
		else
			$templateSelected = get_option_tm('textmaster_Template');


		echo '<ul style="display:inline-block;">';
		if (count($templates) != 0) {
			foreach($templates as $key => $template)
			{
				echo '<li style="width:150px;display:inline-block;min-height:340px;vertical-align:top;margin:10px;">';
				if ($templateSelected == $template['name'])
					$checked = 'checked="checked"';
				else
					$checked = '';
				echo '<input type="radio" name="radio_textmasterTemplate" id="radio_textmasterTemplate" value="'.$template['name'].'" '.$checked.'>';
				echo '<img src="'.$template['image_preview_path'].'" />';
				echo $template['description'];
				echo '</li>';
			}
		}

		echo '</ul>';
	}
}

function wp_texmaster_redaction_authors_metaboxes(&$tApi){
	global $post;

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
//	$tApi->keyapi =  get_option_tm('textmaster_api_key');

	$auteurs = $tApi->getAuteurs();

	if ($tApi->secretapi == '' && $tApi->keyapi == '')
		_e('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	else
	{
		echo '<a id="showAuthorsRedaction"><img src="'.plugins_url('', __FILE__).'/images/plus.png" />' .__('Vos auteurs' ,'textmaster').'</a><br/>';
		echo '<div id="authorsRedaction">';

		echo '<ul style="display:inline-block;">';
		if (get_post_meta($post->ID, 'textmasterAuthor', true) != '')
			$auteurSelected = unserialize( get_post_meta($post->ID, 'textmasterAuthor', true));
		else
			$auteurSelected = array(get_option_tm('textmaster_author'));

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
			echo '<input type="checkbox" name="check_textmasterAuthor[]" class="check_textmasterAuthor" value="'.$auteur['author_id'].'" '.$checked.'> ';
			echo $auteur['author_ref'].$auteurDesc;
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}

function textmaster_redaction_save( $post_id ){

	if ('textmaster_redaction' === get_post_type($post_id))
	{
 		if (isset($_REQUEST['select_textmasterCat']))
			update_post_meta($post_id, 'textmasterCategorie', $_REQUEST['select_textmasterCat']);
		if (isset($_REQUEST['select_textmasterLanguageLevel']))
			update_post_meta($post_id, 'textmasterLanguageLevel', $_REQUEST['select_textmasterLanguageLevel']);
		if (isset($_REQUEST['select_textmasterLang']))
			update_post_meta($post_id, 'textmasterLang', $_REQUEST['select_textmasterLang']);

		if (isset($_REQUEST['text_textmasterKeywords']))
			update_post_meta($post_id, 'textmasterKeywords', $_REQUEST['text_textmasterKeywords']);
		if (isset($_REQUEST['text_textmasterKeywordsRepeatCount']))
			update_post_meta($post_id, 'textmasterKeywordsRepeatCount', $_REQUEST['text_textmasterKeywordsRepeatCount']);
		if (isset($_REQUEST['select_textmasterVocabularyType']))
			update_post_meta($post_id, 'textmasterVocabularyType', $_REQUEST['select_textmasterVocabularyType']);
		if (isset($_REQUEST['select_textmasterGrammaticalPerson']))
			update_post_meta($post_id, 'textmasterGrammaticalPerson', $_REQUEST['select_textmasterGrammaticalPerson']);
		if (isset($_REQUEST['select_textmasterTargetReaderGroup']))
			update_post_meta($post_id, 'textmasterTargetReaderGroup', $_REQUEST['select_textmasterTargetReaderGroup']);
		if (isset($_REQUEST['radio_textmasterTemplate']))
			update_post_meta($post_id, 'textmasterTemplate', $_REQUEST['radio_textmasterTemplate']);
		if (isset($_REQUEST['select_textmasteWordCountRule']))
			update_post_meta($post_id, 'textmasteWordCountRule', $_REQUEST['select_textmasteWordCountRule']);
		if (isset($_REQUEST['text_textmasterWordCount']))
			update_post_meta($post_id, 'textmasterWordCount', $_REQUEST['text_textmasterWordCount']);
		if (isset($_REQUEST['check_textmasterAuthor']))
			update_post_meta($post_id, 'textmasterAuthor', serialize($_REQUEST['check_textmasterAuthor']));
		// le soptions
		if (isset($_REQUEST['radio_textmasterQuality']))
			update_post_meta($post_id, 'textmasterQualityRedaction', $_REQUEST['radio_textmasterQuality']);
		if (isset($_REQUEST['radio_textmasterExpertise']))
			update_post_meta($post_id, 'textmasterExpertiseRedaction', $_REQUEST['radio_textmasterExpertise']);
		if (isset($_REQUEST['radio_textmasterPriority']))
			update_post_meta($post_id, 'textmasterPriorityRedaction', $_REQUEST['radio_textmasterPriority']);


		/* on garde le projet dans la liste des textmaster */
		if (isset($_REQUEST['select_textmasterCat']) && isset($_REQUEST['select_textmasterLang'])) {
			$content_post = get_post($post_id);
			$content = $content_post->post_content;

			$projet['name'] 						= get_the_title($post_id);
			$projet['language_from'] 				= $_REQUEST['select_textmasterLang'];
			$projet['language_to'] 					= $_REQUEST['select_textmasterLang'];
			$projet['category'] 					= $_REQUEST['select_textmasterCat'];
			$projet['vocabulary_type'] 				= $_REQUEST['select_textmasterVocabularyType'];
			$projet['target_reader_groups'] 		= $_REQUEST['select_textmasterTargetReaderGroup'];
			$projet['options']['language_level'] 	= $_REQUEST['select_textmasterLanguageLevel'];
			$projet['options']['expertise'] 		= $_REQUEST['radio_textmasterExpertise'];
			$projet['grammatical_person'] 			= $_REQUEST['select_textmasterGrammaticalPerson'];
			$projet['project_briefing'] 			= $content;
			$projet['priority'] 					= $_REQUEST['radio_textmasterPriority'];
			$projet['status'] 						= __('NC', 'textmaster');
			$projet['total_word_count'] 			= $_REQUEST['text_textmasterWordCount'];
			$projet['same_author_must_do_entire_project'] = false;
			$projet['cost_in_credits'] 			= '';
			$projet['ctype'] 						= 'copywriting';
			$projet['creation_channel'] 			= 'api';
			$projet['reference'] 					= '';
			$projet['work_template']['name'] 		= $_REQUEST['radio_textmasterTemplate'];
			$projet['created_at']['full'] 			= date('Y-m-d H:i:s');
			$projet['updated_at']['full'] 			= '';
			$projet['completed_at']['full']			= '';
			$projet['launched_at']['full'] 			= '';
			$projet['archived']						= '';
			$projet['IdDoc']						= 'wp_'.$post_id;
			$projet['id'] 							= 'wp_'.$post_id;
			wpSaveProjet($projet);
//			die();
		}
	}
}


function textmaster_redaction_notice(){
	global $post, $pagenow;

	if ($pagenow == 'post.php' && 'textmaster_redaction' === get_post_type($post->ID))
	{
		$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
		//$tApi->secretapi = get_option_tm('textmaster_api_secret');
		//$tApi->keyapi =  get_option_tm('textmaster_api_key');
		$idProjet = get_post_meta($post->ID,'textmasterId', TRUE);
		$status = $tApi->getProjetStatus($idProjet);

		if ($status == 'in_review') {
			echo '<div class="updated">
			   <p>'.__('Ce projet a été rédigé, vous pouvez le','textmaster'). '<a href="javascript:void(tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=500&width=630&TB_iframe=true\'));">'.__('valider dès maintenant','textmaster').'</a></p>
			</div>';
		}
	}
}

function callback_redaction(){

	$categorie = $_POST['categorie'];
	$languageLevel = $_POST['languageLevel'];
	$language = $_POST['language'];
	$wordCountRule = $_POST['wordCountRule'];
	$wordCount = $_POST['wordCount'];

	$checkStatut = '';

	if (isset($_POST['authors']))
		$authors = $_POST['authors'];
	else
		$authors = "";
	$textmasterQualityRedaction = '';
	if (isset($_REQUEST['quality']))
		$textmasterQualityRedaction = $_REQUEST['quality'];
	$textmasterExpertiseRedaction = '';
	if (isset($_REQUEST['expertise']))
		$textmasterExpertiseRedaction = $_REQUEST['expertise'];
	$textmasterPriorityRedaction = '';
	if (isset($_REQUEST['priority']))
		$textmasterPriorityRedaction = $_REQUEST['priority'];

	$keywords = $_POST['keywords'];
	$keywordsRepeatCount = $_POST['keywordsRepeatCount'];
	if (trim($keywords) != '' && trim($keywordsRepeatCount) == '')
		$keywordsRepeatCount = 1;

	$vocabularyType = $_POST['vocabularyType'];
	$grammaticalPerson = $_POST['grammaticalPerson'];
	$targetReaderGroup = $_POST['targetReaderGroup'];

	$templateTM = $_POST['templateTM'];

	$post_id = $_POST['postID'];
	$content_post = get_post($post_id);
	$content = $content_post->post_content;

	update_post_meta($post_id, 'textmasterCategorie', $categorie);
	update_post_meta($post_id, 'textmasterLanguageLevel', $languageLevel);
	update_post_meta($post_id, 'textmasterLang', $language);
	update_post_meta($post_id, 'textmasteWordCountRule', $wordCountRule);
	update_post_meta($post_id, 'textmasterWordCount', $wordCount);

	update_post_meta($post_id, 'textmasterKeywords', $keywords);
	update_post_meta($post_id, 'textmasterKeywordsRepeatCount', $keywordsRepeatCount);
	update_post_meta($post_id, 'textmasterVocabularyType', $vocabularyType);
	update_post_meta($post_id, 'textmasterGrammaticalPerson', $grammaticalPerson);
	update_post_meta($post_id, 'textmasterTargetReaderGroup', $targetReaderGroup);
	update_post_meta($post_id, 'textmasterAuthor', serialize($authors));

	// le soptions
	if (isset($textmasterQualityRedaction))
		update_post_meta($post_id, 'textmasterQualityRedaction', $textmasterQualityRedaction);
	if (isset($textmasterExpertiseRedaction))
		update_post_meta($post_id, 'textmasterExpertiseRedaction', $textmasterExpertiseRedaction);
	if (isset($textmasterPriorityRedaction))
		update_post_meta($post_id, 'textmasterPriorityRedaction', $textmasterPriorityRedaction);

	update_post_meta($post_id, 'textmasterTemplate', $templateTM);

	if ($wordCount == '') {
			$ret = 'Error : Merci de saisir un nombre de mot.' ;
	}
	else{
		$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
		//$tApi->secretapi = get_option_tm('textmaster_api_secret');
		//$tApi->keyapi =  get_option_tm('textmaster_api_key');
		$idProjet = get_post_meta($post_id,'textmasterId', TRUE);

		if ($textmasterQualityRedaction == 'true')
			$qualityRedaction = 'true';
		else
			$qualityRedaction = 'false';
		if ($textmasterExpertiseRedaction == 'true')
			$expertiseRedaction = 'true';
		else
			$expertiseRedaction = 'false';
		if ($textmasterPriorityRedaction == 'true')
			$priorityRedaction = 'true';
		else
			$priorityRedaction = 'false';

		if ($idProjet != '')
			$checkStatut = $tApi->getProjetStatus($idProjet);
		if ($idProjet == '' || $checkStatut == 'canceled')
		{
			$retProjet = $tApi->makeProject(get_the_title($post_id), 'copywriting', $language, $language, $categorie, $content, $languageLevel, $qualityRedaction, $expertiseRedaction, $priorityRedaction, $templateTM, $vocabularyType, $grammaticalPerson, $targetReaderGroup, $authors);
			if (is_array($retProjet))
				$idProjet = $retProjet['id'];
			else
				$ret = __('Erreur lors de la création de votre projet ('.$retProjet.')' ,'textmaster');
		//	var_dump($retProjet);
		}

		// nouveau projet
		if ((isset($retProjet) && is_array($retProjet)) ||  $idProjet != '')
		{
			//	$ret = serialize($ret);
			update_post_meta($post_id, 'textmasterId', $idProjet);
			$arrayDocs[0]['title'] = get_the_title($post_id);
			$arrayDocs[0]['word_count'] = $wordCount;
			$arrayDocs[0]['original_content'] = '';
			$arrayDocs[0]['word_count_rule'] = $wordCountRule;
			$arrayDocs[0]['keyword_list'] = $keywords;
			$arrayDocs[0]['keywords_repeat_count'] = $keywordsRepeatCount;
			$ret = $tApi->addDocument($idProjet, $arrayDocs, 'copywriting' );
		//	var_dump($ret);
			update_post_meta($post_id, 'textmasterDocumentId', $ret['id']);
		}

		$result = $tApi->getProjetStatus($idProjet);

		if ($result == 'paused' || $result == 'in_creation') {
			$retLaunch = $tApi->launchProject($idProjet);

			$retLaunch = json_decode($retLaunch, TRUE);

			if (is_array($retLaunch) && array_key_exists('errors',$retLaunch))
			{
				if (array_key_exists('credits',$retLaunch['errors']))
					$ret = 'Error '.$retLaunch['errors']['credits'][0];
				else
					$ret = 'Error '.$retLaunch['errors']['status'][0];
			}
			else
			{
				$result = $tApi->getProjetStatus($idProjet);
				update_post_meta($post_id, 'textmasterStatusRedaction', $result);
				$ret = __('La rédaction de cet article est lancée.','textmaster');
				wpDelTempProjet('wp_'.$post_id);
				wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
		//		syncProjets('waiting_assignment',1);
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('waiting_assignment',1 ) );

				//	syncProjets('in_progress');
			//	syncProjets('in_creation');
				wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('in_creation',1 ) );

			}

		}
		else if ($result == 'in_progress' ) {
			$ret = __('Cet article est déjà en cours de rédaction.','textmaster');
		}
		else if ($result == 'completed' ) {
			$ret = __('La rédaction de cet article est terminée.','textmaster');
		}
	}


//	print_r($ret);
	if (strpos($ret, 'Error') !== FALSE)
		echo '<br><div class="error">'.$ret.'</div>';
	else
		echo '<br><div class="updated">'.$ret.'</div>';
	die();
}


function textmasterRedactions_admin_posts_filter_restrict_manage_posts(){
	global $wpdb, $post_type;

	if($post_type == 'textmaster_redaction') {
		$req = 'SELECT DISTINCT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key="textmasterStatusRedaction"';
		$results = $wpdb->get_results( $req);
		if (count($results) != 0)
		{
			foreach ($results as $post) {
				if ($post->meta_value != '')
					$arrayStatus[$post->meta_value] = textmaster_api::getLibStatus($post->meta_value);
			}
		}
		?>
        <select name="textmaster_status">
        <option value=""><?php _e('Voir tous les status TextMaster', 'textmaster'); ?></option>
        <?php
		$current_v = isset($_GET['textmaster_status'])? $_GET['textmaster_status']:'';
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

function textmasterRedactions_posts_filter( $query ){
	global $pagenow, $post_type;

	if ( $post_type == 'textmaster_redaction' && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '') {
		$query->query_vars['meta_key'] = 'textmasterStatusRedaction';
		//$query->query_vars['meta_key'] = 'textmasterStatusTrad';
		$query->query_vars['meta_value'] =  $_GET['textmaster_status'];
		//	$query->query_vars['meta_value'] = $_GET['textmaster_status'];

	}
}
?>
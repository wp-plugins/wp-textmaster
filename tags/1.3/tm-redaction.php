<?php

function textmaster_redaction_type() {
	$args = array(
	'labels' => array(
		'name' => __('TextMaster','textmaster'),
		'all_items' => __('Rédactions TM','textmaster'),
		'singular_name' => __('Rédaction TM','textmaster'),
		'add_new' => __('Ajouter','textmaster'),
		'add_new_item' => __('Créer une rédaction TextMaster','textmaster'),
		'edit_item' => __('Editer une rédaction TextMaster','textmaster'),
		'not_found' => __('Aucune rédaction TextMaster trouvée','textmaster')
	),
	'public' => false,
	'show_ui' => true,
	'menu_position' => 7,
	'menu_icon' => plugins_url('wp-textmaster/images/tm-icon.png'),
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
			{	$tApi = new textmaster_api();
				$tApi->secretapi = get_option('textmaster_api_secret');
				$tApi->keyapi =  get_option('textmaster_api_key');
				$idProjet = get_post_meta($id,'textmasterId', TRUE);
				echo $tApi->getLibStatus($tApi->getProjetStatus($idProjet));
			}
			break;
		case 'Date':
			echo date_i18n(get_option('date_format') ,get_the_time( 'U', $post_id ));
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
		#icon-edit.icon32-posts-textmaster_redaction {background: url(<?php echo plugins_url('wp-textmaster/images/tm-logo.png') ?>) no-repeat;}
	</style>
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

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');

	$categories = $tApi->getCategories();
	if ($categories['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCat" name="select_textmasterCat" style="width:235px;">';
		if (get_post_meta($post->ID, 'textmasterCategorie', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorie', true);
		else
			$catSelected = get_option('textmaster_redactionCategorie');

		foreach($categories as $key => $categorie)
		{
			if ($catSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$categorie.'</option>';
			else
				echo '<option value="'.$key.'">'.$categorie.'</option>';
		}
		echo '</select><br/><br/>';


		$languageLevels['basic_language_level'] = __('Basic','textmaster');
		$languageLevels['standard_language_level'] = __('Standard','textmaster');
		$languageLevels['expert_language_level'] = __('Expert','textmaster');
		echo '<label>'.__('Niveau de service:','textmaster').'</label>';
		echo '<select id="select_textmasterLanguageLevel" name="select_textmasterLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterLanguageLevel', true);
		else
			$languageLevelSelected = get_option('textmaster_redactionLanguageLevel');

		foreach($languageLevels as $key => $languageLevel)
		{
			if ($languageLevelSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
			else
				echo '<option value="'.$key.'">'.$languageLevel.'</option>';
		}
		echo '</select><br/><br/>';

		$languages = $tApi->getLanguages();

		echo '<label>'.__('Langue :','textmaster').'</label><br/>';
		echo '<select id="select_textmasterLang" name="select_textmasterLang" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterLang', true) != '')
			$languageSelected = get_post_meta($post->ID, 'textmasterLang', true);
		else
			$languageSelected = get_option('textmaster_redactionLanguage');

		foreach($languages as $key => $language)
		{
			if ($languageSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
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
		echo '<input type="text" id="text_textmasterWordCount" name="text_textmasterWordCount" style="width:145px;text-align:right;" value="'.get_post_meta($post->ID, 'textmasterWordCount', true).'" />';

		$disableRedaction = '';
		$idProjet = get_post_meta($post->ID,'textmasterId', TRUE);
		if ($idProjet != '') {
			//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits
			$result = $tApi->getProjetStatus($idProjet);

			if ($result == 'in_review')
			{
				$txtRet = __('Cet article a &eacute;t&eacute; rédigé.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=700&width=630\');"></div>';
				$disableRedaction = 'disabled=disabled';
			}
			else if ( $result == 'in_progress' )
			{
				$txtRet = __('Cet article est en cours de rédaction.','textmaster');
				$disableRedaction = 'disabled=disabled';
			}
			else if ($result == 'completed') {
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la rédaction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la rédaction','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=700&width=630\');"></div>';
				$disableRedaction = 'disabled=disabled';
			}
		}
		else if ($post->post_content == '' ||  $post->post_title == '')
		 	$disableRedaction = 'disabled="disabled"';


?>
		<br/><br/>
		<div class="major-publishing-actions">
		<div id="publishing-action">
		<img src="/wp-admin/images/wpspin_light.gif" class="ajax-loading" id="ajax-loading" alt="">
		<input name="original_publish" type="hidden" id="original_publish" value="Publier">
		<input type="submit" name="publish" id="publish" class="button-primary" value="<? echo __('Sauver','textmaster') ?>" tabindex="5" accesskey="p">
		<br/><br/><input name="redaction" type="button" class="button button-highlighted" id="redaction" tabindex="6" accesskey="r" value="<? echo __('Lancer la rédaction','textmaster') ?>" <? echo $disableRedaction ?>>
		</div>
		</div>
		<div style="clear:both"></div>
<?
		echo '<br/><div id="resultTextmaster">'.$txtRet.'</div><br/>';
	}
}

function wp_texmaster_redaction_options_metaboxes(){
	global $post;


	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');

	$categories = $tApi->getCategories();
	if ($categories['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{

		echo '<label>'.__('Mots-clés:','textmaster').'</label> ('.__('séparés par des virgules','textmaster').')';
		echo '<textarea style="width:235px;" name="text_textmasterKeywords" id="text_textmasterKeywords">'.get_post_meta($post->ID, 'textmasterKeywords', true).'</textarea><br/>';

		if (get_post_meta($post->ID, 'textmasterKeywordsRepeatCount', true) != '')
			$KeywordsRepeatCountSelected = get_post_meta($post->ID, 'textmasterKeywordsRepeatCount', true);
		else
			$KeywordsRepeatCountSelected = 1;
		_e('A répéter :','textmaster');
		echo '<input type="text" id="text_textmasterKeywordsRepeatCount" name="text_textmasterKeywordsRepeatCount" style="width:50px;text-align:right;" value="'.$KeywordsRepeatCountSelected.'" />'. __('fois','textmaster').'<br/><br/>';

		$vocabulary_types['not_specified'] = __('Non spécifié','textmaster');
		$vocabulary_types['popular'] = __('Populaire','textmaster');
		$vocabulary_types['technical'] = __('Technique','textmaster');
		$vocabulary_types['fictional'] = __('Romancé','textmaster');

		echo '<label>'.__('Type de Vocabulaire:','textmaster').'</label>';
		echo '<select id="select_textmasterVocabularyType" name="select_textmasterVocabularyType" style="width:235px;"><br/><br/>';

		if (get_post_meta($post->ID, 'textmasterVocabularyType', true) != '')
			$vocabulary_typeSelected = get_post_meta($post->ID, 'textmasterVocabularyType', true);
		else
			$vocabulary_typeSelected = get_option('textmaster_vocabularyType');

		foreach($vocabulary_types as $key => $vocabulary_type)
		{
			if ($vocabulary_typeSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
			else
				echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
		}
		echo '</select><br/><br/>';


		$grammatical_persons['not_specified'] = __('Non spécifié','textmaster');
		$grammatical_persons['first_person_singular'] = __('Je > 1ère personne - Singulier','textmaster');
		$grammatical_persons['second_person_singular'] = __('Tu > 2ème Personne - Singulier','textmaster');
		$grammatical_persons['third_person_singular_masculine'] = __('Il > 3ème personne - Singulier Masculin','textmaster');
		$grammatical_persons['third_person_singular_feminine'] = __('Elle > 3ème personne - Singulier Féminin','textmaster');
		$grammatical_persons['third_person_singular_neuter'] = __('On > 3ème personne - Singulier Neutre','textmaster');
		$grammatical_persons['first_person_plural'] = __('Nous > 1ère personne - Pluriel','textmaster');
		$grammatical_persons['second_person_plural'] = __('Vous > 2ème Personne - Pluriel','textmaster');
		$grammatical_persons['third_person_plural'] = __('Ils/elles > 3ème Personne - Pluriel','textmaster');

		echo '<label>'.__('Personne grammaticale:','textmaster').'</label>';
		echo '<select id="select_textmasterGrammaticalPerson" name="select_textmasterGrammaticalPerson" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterGrammaticalPerson', true) != '')
			$grammatical_personSelected = get_post_meta($post->ID, 'textmasterGrammaticalPerson', true);
		else
			$grammatical_personSelected = get_option('textmaster_grammaticalPerson');

		foreach($grammatical_persons as $key => $grammatical_person)
		{
			if ($grammatical_personSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
			else
				echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
		}
		echo '</select><br/><br/>';


		$target_reader_groups['not_specified'] = __('Non spécifié','textmaster');
		$target_reader_groups['children'] = __('Enfants > 13 ans et moins','textmaster');
		$target_reader_groups['teenager'] = __('Adolescent > entre 14 et 18 ans','textmaster');
		$target_reader_groups['young_adults'] = __('Jeunes adultes > entre 19 et 29 ans','textmaster');
		$target_reader_groups['adults'] = __('Adultes > entre 30 et 59 ans','textmaster');
		$target_reader_groups['old_adults'] = __('Séniors > 60 ans et plus','textmaster');

		echo '<label>'.__('Public Ciblé:','textmaster').'</label>';
		echo '<select id="select_textmasterTargetReaderGroup" name="select_textmasterTargetReaderGroup" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTargetReaderGroup', true) != '')
			$target_reader_groupSelected = get_post_meta($post->ID, 'textmasterTargetReaderGroup', true);
		else
			$target_reader_groupSelected = get_option('textmaster_targetReaderGroup');

		foreach($target_reader_groups as $key => $target_reader_group)
		{
			if ($target_reader_groupSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$target_reader_group.'</option>';
			else
				echo '<option value="'.$key.'">'.$target_reader_group.'</option>';
		}
		echo '</select><br/><br/>';

	}

}

function wp_texmaster_redaction_templates_metaboxes(){
	global $post;


	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');

	$templates = $tApi->getTemplates();

	if (count($templates) == 0) {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		if (get_post_meta($post->ID, 'textmasterTemplate', true) != '')
			$templateSelected = get_post_meta($post->ID, 'textmasterTemplate', true);
		else
			$templateSelected = get_option('textmaster_Template');


		echo '<ul style="display:inline-block;">';
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
		echo '</ul>';
	}
}

function wp_texmaster_redaction_authors_metaboxes(){
	global $post;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');

	$auteurs = $tApi->getAuteurs();

	if (count($auteurs) == 0) {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		echo '<ul style="display:inline-block;">';
		if (get_post_meta($post->ID, 'textmasterAuthor', true) != '')
			$auteurSelected = unserialize( get_post_meta($post->ID, 'textmasterAuthor', true));
		else
			$auteurSelected = array(get_option('textmaster_author'));

		foreach($auteurs as $auteur)
		{
			$auteurDesc = '';
			if ($auteur['description'] != '')
				$auteurDesc = ' - '.$auteur['description'];

			echo '<li>';
			if ( in_array($auteur['id'], $auteurSelected) )
				$checked = 'checked="checked"';
			else
				$checked = '';
			echo '<input type="checkbox" name="check_textmasterAuthor[]" class="check_textmasterAuthor" value="'.$auteur['id'].'" '.$checked.'> ';
			echo $auteur['author_ref'].$auteurDesc;
			echo '</li>';
		}
		echo '</ul>';
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
	}
}


function textmaster_redaction_notice(){
	global $post, $pagenow;

	if ($pagenow == 'post.php' && 'textmaster_redaction' === get_post_type($post->ID))
	{
		$tApi = new textmaster_api();
		$tApi->secretapi = get_option('textmaster_api_secret');
		$tApi->keyapi =  get_option('textmaster_api_key');
		$idProjet = get_post_meta($post->ID,'textmasterId', TRUE);
		$status = $tApi->getProjetStatus($idProjet);

		if ($status == 'in_review') {
			echo '<div class="updated">
			   <p>'.__('Ce projet a été rédigé, vous pouvez le','textmaster'). '<a href="javascript:void(tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=700&width=630\'));">'.__('valider','textmaster').'</a>' .__('dès maintenant.').'</p>
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
	$authors = $_POST['authors'];

	$keywords = $_POST['keywords'];
	$keywordsRepeatCount = $_POST['keywordsRepeatCount'];
	if (trim($keywords) != '' && trim($keywordsRepeatCount) == '')
		$keywordsRepeatCount = 1;

	$vocabularyType = $_POST['vocabularyType'];
	$grammaticalPerson = $_POST['grammaticalPerson'];
	$targetReaderGroup = $_POST['targetReaderGroup'];

	$templateTM = $_POST['templateTM'];

	$postID = $_POST['postID'];
	$content_post = get_post($postID);
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

	update_post_meta($post_id, 'textmasterTemplate', $templateTM);

	if ($wordCount == '') {
			$ret = 'Error : Merci de saisir un nombre de mot.' ;
	}
	else{
		$tApi = new textmaster_api();
		$tApi->secretapi = get_option('textmaster_api_secret');
		$tApi->keyapi =  get_option('textmaster_api_key');
		$idProjet = get_post_meta($postID,'textmasterId', TRUE);

		$checkStatut = $tApi->getProjetStatus($idProjet);
		if ($idProjet == '' || $checkStatut == 'canceled')
		{
			$retProjet = $tApi->makeProject(get_the_title($postID), 'copywriting', $language, $language, $categorie, $content, $languageLevel,$templateTM,$vocabularyType,$grammaticalPerson,$targetReaderGroup,$authors);
			$idProjet = $retProjet['projects']['id'];
		}

		// nouveau projet
		if (is_array($retProjet))
		{
			//	$ret = serialize($ret);
			update_post_meta($postID, 'textmasterId', $idProjet);
			$ret = $tApi->addDocument($idProjet, get_the_title($postID) ,$wordCount, '', $wordCountRule, $keywords, $keywordsRepeatCount);
			update_post_meta($postID, 'textmasterDocumentId', $ret['documents']['id']);
		}

		$result = $tApi->getProjetStatus($idProjet);
		if ($result == 'paused' || $result == 'in_creation') {
			$retLaunch = $tApi->launchProject($idProjet);
			$retLaunch = json_decode($retLaunch, TRUE);
			if (array_key_exists('error',$retLaunch))
			{

				$ret = 'Error '.utf8_decode($retLaunch['error'][0]) ;
			}
			else
				$ret = utf8_decode(__('La rédaction de cet article est lancée.','textmaster'));
		}
		else if ($result == 'in_progress' ) {
			$ret = utf8_decode(__('Cet article est déjà en cours de rédaction.','textmaster'));
		}
		else if ($result == 'completed' ) {
			$ret = utf8_decode(__('La rédaction de cet article est terminée.','textmaster'));
		}
	}



	if (strpos($ret, 'Error') !== FALSE)
		echo '<br><div class="error">'.htmlentities($ret).'</div>';
	else
		echo '<br><div class="updated">'.htmlentities($ret).'</div>';
	die();
}
?>
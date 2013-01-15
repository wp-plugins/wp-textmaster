<?php
/*
Plugin Name: TextMaster plugin
Plugin URI: http://www.c1blog.com/plugin-textmaster-pour-wordpress/
Description: Plugin for TextMaster copywriting, readproof and translation
Author: Lupuz
Version: 1.3
Author URI: http://www.c1blog.com
Text Domain: textmaster
*/

include( plugin_dir_path( __FILE__ ). '/textmaster.class.php' );
include( plugin_dir_path( __FILE__ ). '/tm-redaction.php' );
include( plugin_dir_path( __FILE__ ). '/tm-settings.php' );

// pour verifir que curl est installé
function dependent_activate()
{

	if  (!in_array('curl', get_loaded_extensions()))
	{
		// deactivate dependent plugin
		deactivate_plugins( __FILE__);
		//   throw new Exception('Requires another plugin!');
		//  exit();
		exit (__('Merci d\'installer l\'extension crul pour php.','textmaster').' ( <a href="http://www.php.net/manual/en/curl.installation.php" target="_blank">http://www.php.net/manual/en/curl.installation.php</a> )');
	}
}

function texmaster_add_metaboxes() {
	if (get_option('textmaster_useReadproof') == 'Y')
	{
		add_meta_box('wp_textmaster_readproof', __('TextMaster Relecture', 'textmaster'), 'wp_texmaster_readproof_metaboxes', 'post', 'side', 'default');
		add_meta_box('wp_textmaster_readproof', __('TextMaster Relecture', 'textmaster'), 'wp_texmaster_readproof_metaboxes', 'page', 'side', 'default');
	}
	if (get_option('textmaster_useTraduction') == 'Y')
	{
		add_meta_box('wp_textmaster_traduction', __('TextMaster traduction', 'textmaster'), 'wp_texmaster_traduction_metaboxes', 'post', 'side', 'default');
		add_meta_box('wp_textmaster_traduction', __('TextMaster traduction', 'textmaster'), 'wp_texmaster_traduction_metaboxes', 'page', 'side', 'default');
	}

	add_meta_box('wp_textmaster_redaction_defaut', __('Lancer la rédation', 'textmaster'), 'wp_texmaster_redaction_defaut_metaboxes', 'textmaster_redaction', 'side', 'default');
	add_meta_box('wp_textmaster_redaction_options', __('Options', 'textmaster'), 'wp_texmaster_redaction_options_metaboxes', 'textmaster_redaction', 'side', 'default');
	add_meta_box('wp_texmaster_redaction_templates', __('Mise en page', 'textmaster'), 'wp_texmaster_redaction_templates_metaboxes', 'textmaster_redaction', 'normal', 'default');
	add_meta_box('wp_texmaster_redaction_authors', __('Auteurs', 'textmaster'), 'wp_texmaster_redaction_authors_metaboxes', 'textmaster_redaction', 'side', 'default');
	remove_meta_box( 'submitdiv', 'textmaster_redaction', 'side' );
}

// The Event Location Metabox
function wp_texmaster_readproof_metaboxes() {
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
		echo '<select id="select_textmasterCat" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorie', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorie', true);
		else
			$catSelected = get_option('textmaster_readproofCategorie');

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
		echo '<select id="select_textmasterReadProofLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorieReadProofLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterReadProofLanguageLevel', true);
		else
			$languageLevelSelected = get_option('textmaster_readproofLanguageLevel');

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
		echo '<select id="select_textmasterReadProofLang" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterReadProofLang', true) != '')
			$languageSelected = get_post_meta($post->ID, 'textmasterReadProofLang', true);
		else
			$languageSelected = get_option('textmaster_readproofLanguage');

		foreach($languages as $key => $language)
		{
			if ($languageSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		echo '</select>';

		$idProjet = get_post_meta($post->ID,'textmasterId', TRUE);
		if ($idProjet != '') {
		//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits
			$result = $tApi->getProjetStatus($idProjet);

			if ($result == 'in_review')
			{
				$txtRet = __('Cet article a &eacute;t&eacute; relu.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la relecture','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&&type=readproof&height=700&width=630\');"></div>';
				$disabled = 'disabled=disabled';
			}
			else if ($result == 'in_progress' )
			{
				$txtRet = __('Cet article est en cours de relecture.','textmaster');
				$disabled = 'disabled=disabled';
			}
			else if ($result == 'completed') {
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la relecture de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la relecture','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la relecture','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&height=700&width=630\');"></div>';
				$disabled = 'disabled=disabled';
			}

		}
		else if ($post->post_content == '')
		{
			$ret = __('Merci de sauvegarder cet article.','textmaster');
			$disabled = 'disabled=disabled';
		}

		echo '<br/><br/><div id="publishing-action"><input name="save" type="button" class="button button-highlighted" id="readproof" tabindex="5" accesskey="p" value="'.__('Relecture','textmaster').'" '.$disabled.'></div>';
		echo '<div style="clear:both"></div><br/><div id="resultTextmaster">'.$txtRet.'</div><br/><br/>';
	}

}

function wp_texmaster_traduction_metaboxes() {
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
	//	echo '<form name="textmaster_form" method="post" action="">';
		echo '<label>'.__('Categorie:','textmaster').'</label>';
		echo '<select id="select_textmasterCatTrad" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterCategorieTrad', true) != '')
			$catSelected = get_post_meta($post->ID, 'textmasterCategorieTrad', true);
		else
			$catSelected = get_option('textmaster_traductionCategorie');

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
		echo '<select id="select_textmasterTradLanguageLevel" style="width:235px;">';

		if (get_post_meta($post->ID, 'textmasterTradLanguageLevel', true) != '')
			$languageLevelSelected = get_post_meta($post->ID, 'textmasterTradLanguageLevel', true);
		else
			$languageLevelSelected = get_option('textmaster_traductionLanguageLevel');

		foreach($languageLevels as $key => $languageLevel)
		{
			if ($languageLevelSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
			else
				echo '<option value="'.$key.'">'.$languageLevel.'</option>';
		}
		echo '</select><br/><br/>';

		$languages = $tApi->getLanguages();
		echo '<div style="float:left;margin-right:35px;">';
		echo '<label>'.__('Langue d\'origine :','textmaster').'</label><br/>';
		echo '<select id="select_textmasterLangOrigine">';

		if (get_post_meta($post->ID, 'textmasterLangOrigine', true) != '')
			$languageSourceSelected = get_post_meta($post->ID, 'textmasterLangOrigine', true);
		else
			$languageSourceSelected = get_option('textmaster_traductionLanguageSource');

		foreach($languages as $key => $language)
		{
			if ($languageSourceSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		echo '</select><br/>';
		echo '</div>';
		echo '<label>'.__('Traduction en :','textmaster').'</label><br/>';

		echo '<select id="select_textmasterLangDestination">';

		if (get_post_meta($post->ID, 'textmasterLangDestination', true) != '')
			$languageDestinationSelected = get_post_meta($post->ID, 'textmasterLangDestination', true);
		else
			$languageDestinationSelected = get_option('textmaster_traductionLanguageDestination');

		foreach($languages as $key => $language)
		{
			if ($languageDestinationSelected == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		echo '</select>';
		echo '<div style="clear:both;"></div>';
		echo '<br/><br/>';


		$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
		if ($idProjet != '') {
			//	print_r($tApi->getProjetInfos($idProjet));
			//project_cost_in_credits
			$result = $tApi->getProjetStatus($idProjet);

			if ($result == 'in_review')
			{
				$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la traduction','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&type=trad&height=700&width=630\');"></div>';
				$disabled = 'disabled=disabled';
			}
			else if ( $result == 'in_progress' )
			{
				$txtRet = __('Cet article est en cours de traduction.','textmaster');
				$disabled = 'disabled=disabled';
			}
			else if ($result == 'completed') {
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la traduction','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la traduction','textmaster').'\',\'/wp-content/plugins/wp-textmaster/approuve_doc.php?post_id='.$post->ID.'&type=trad&height=700&width=630\');"></div>';
				$disabled = 'disabled=disabled';
			}


		}
		else if ($post->post_content == '')
		{
			$ret = __('Merci de sauvegarder cet article.','textmaster');
			$disabled = 'disabled=disabled';
		}

		echo '<div id="publishing-action"><input name="traduction" type="button" class="button button-highlighted" id="traduction" tabindex="5" accesskey="p" value="'.__('Traduction','textmaster').'" '.$disabled.'></div>';
		echo '<div style="clear:both"></div><br/><div id="resultTextmasterTrad">'.$txtRet.'</div><br/><br/>';
	//	echo '</form>';
	}

}

function textmaster_javascript() {
	wp_enqueue_script(
	'custom-script',
	plugin_dir_url(__FILE__) . '/textmaster.js',
	array('jquery')
		);
}

function textmaster_callback() {

	if ($_POST['typeTxtMstr'] == 'traduction')
		callback_traduction();
	else if ($_POST['typeTxtMstr'] == 'proofread')
		callback_readproof();
	else
		callback_redaction();
}

function callback_readproof(){
	global $wpdb;

	$categorie = $_POST['categorie'];
	$languageLevel = $_POST['languageLevel'];
	$language = $_POST['language'];
	$postID = $_POST['postID'];

	update_post_meta($postID, 'textmasterCategorie',$categorie);
	update_post_meta($postID, 'textmasterReadProofLanguageLevel',$languageLevel);
	update_post_meta($postID, 'textmasterReadProofLang',$language);

	$content_post = get_post($postID);
	$content = $content_post->post_content;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');
	$idProjet = get_post_meta($postID,'textmasterId', TRUE);

//	echo $idProjet;
	$checkStatut = $tApi->getProjetStatus($idProjet);
	if ($idProjet == '' || $checkStatut == 'canceled')
	{
		$retProjet = $tApi->makeProject(get_the_title($postID), 'proofreading', $language, $language, $categorie, get_option('textmaster_readproofBriefing'), $languageLevel);
		$idProjet = $retProjet['projects']['id'];
	}


	// nouveau projet
	if (is_array($retProjet))
	{
		//	$ret = serialize($ret);
		update_post_meta($postID, 'textmasterId', $idProjet);
		$ret = $tApi->addDocument($idProjet, get_the_title($postID) , str_word_count($content),$content);
		//	print_r($ret);
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
			$ret = utf8_decode(__('La relecture de cet article est lancée.','textmaster'));
	}
	else if ($result == 'in_progress' ) {
		$ret = utf8_decode(__('Cet article est déjà en cours de relecture.','textmaster'));
	}
	else if ($result == 'completed' ) {
		$ret = utf8_decode(__('La relecture de cet article est terminée.','textmaster'));
	}

	if (strpos($ret, 'Error') !== FALSE)
		echo '<br><div class="error">'.htmlentities($ret).'</div>';
	else
		echo '<br><div class="updated">'.htmlentities($ret).'</div>';
	die();
}

function callback_traduction(){
	global $wpdb;

	$categorie = $_POST['categorie'];
	$languageLevel = $_POST['languageLevel'];
	$langOrigine = $_POST['langOrigine'];
	$langDestination = $_POST['langDestination'];
	$postID = $_POST['postID'];

	update_post_meta($postID, 'textmasterCategorieTrad',$categorie);
	update_post_meta($postID, 'textmasterTradLanguageLevel',$languageLevel);
	update_post_meta($postID, 'textmasterLangOrigine',$langOrigine);
	update_post_meta($postID, 'textmasterLangDestination',$langDestination);

	$content_post = get_post($postID);
	$content = $content_post->post_content;

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');
	$idProjet = get_post_meta($postID,'textmasterIdTrad', TRUE);

	$checkStatut = $tApi->getProjetStatus($idProjet);
	if ($idProjet == ''|| $checkStatut == 'canceled')
	{
		$retProjet = $tApi->makeProject(get_the_title($postID), 'translation', $langOrigine, $langDestination, $categorie, get_option('textmaster_traductionBriefing'), $languageLevel);
		//	print_r($retProjet);
		$idProjet = $retProjet['projects']['id'];
	}

	// nouveau projet
	if (is_array($retProjet))
	{
		//	$ret = serialize($ret);
		update_post_meta($postID, 'textmasterIdTrad', $idProjet);
		$ret = $tApi->addDocument($idProjet, get_the_title($postID) , str_word_count($content),$content);
		//	print_r($ret);
		update_post_meta($postID, 'textmasterDocumentIdTrad', $ret['documents']['id']);
	}

	$result = $tApi->getProjetStatus($idProjet);
	if ($result == 'paused' || $result == 'in_creation') {
		$retLaunch = $tApi->launchProject($idProjet);
		$retLaunch = json_decode($retLaunch, TRUE);
		if (array_key_exists('error',$retLaunch))
		{

			$ret = 'Error '.utf8_decode($retLaunch['error'][0]);
		}
		else
			$ret = utf8_decode(__('La traduction de cet article est lancée.','textmaster'));
	}
	else if ($result == 'in_progress' ) {
		$ret = utf8_decode(__('Cet article est déjà en cours de traduction.','textmaster'));
	}
	else if ($result == 'completed' ) {
		$ret = utf8_decode(__('La traduction de cet article est terminée.','textmaster'));
	}

	if (strpos($ret, 'Error') !== FALSE)
		echo '<br><div class="error">'.htmlentities($ret).'</div>';
	else
		echo '<br><div class="updated">'.htmlentities($ret).'</div>';
	die();

}



register_activation_hook( __FILE__, 'dependent_activate' );

function texmaster_init() {
	$langs_dir = basename(dirname(__FILE__)) .'/I18n/';
	load_plugin_textdomain( 'textmaster', false, $langs_dir );
}

// les actions wp
add_action('plugins_loaded', 'texmaster_init');
// pour le menu de confs
add_action('admin_menu', 'texmaster_admin_actions');
// pour le menu dans les articles
add_action('add_meta_boxes', 'texmaster_add_metaboxes' );
// memo des infos textmaster
add_action('admin_enqueue_scripts', 'textmaster_javascript');

add_action('wp_ajax_textmaster', 'textmaster_callback');

if (get_option('textmaster_useRedaction') == 'Y') {
	add_action('admin_head', 'remove_all_media_buttons', 10, 2);
	add_action('init', 'textmaster_redaction_type');
	add_filter('post_updated_messages', 'textmaster_redaction_updated_messages', 10, 2);
	add_filter('manage_edit-textmaster_redaction_columns', 'add_new_textmaster_redaction_columns');
	add_action('manage_textmaster_redaction_posts_custom_column', 'manage_textmaster_redaction_columns', 10, 2);
	add_filter('post_row_actions','textmaster_redaction_action_row', 10, 2);
	add_action( 'admin_head', 'wpt_textmaster_icons' );
	add_action( 'save_post', 'textmaster_redaction_save' );
//	add_action('edit_post', 'textmaster_redaction_edit');
	add_action('admin_notices', 'textmaster_redaction_notice');
	add_filter( 'default_content', 'textmaster_defaut_content' );
}


?>
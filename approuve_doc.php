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

if (isset($_GET['valide']) && $_GET['valide'] == 1) {

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
//	$tApi->keyapi =  get_option_tm('textmaster_api_key');

//	print_r($_GET);
	$post_origine = get_post($_GET['post_id_origine']);

	if ($_GET['type'] == 'redaction' || $_GET['new_article'] == 1 || $_GET['new_article'] == 2) {
		$text = '';

		$infos = $tApi->getDocumentInfos($_GET['projectId'], $_GET['docId']);

	//	var_dump($infos);
	//	die();
		// on créer un article avec le contenu
		if (key_exists('documents', $infos))
			$work = $infos['documents'][0];
		else
			$work = $infos;

		if (isset($work['author_work']['title']) && $work['author_work']['title'] != '')
			$new_post['post_title'] = $work['author_work']['title'];
//		else if ($work['title'] != '')
//			$new_post['post_title'] = $work['title'];
		else
			$new_post['post_title'] = __('Untitled');

		if (isset($work['author_work']['post_excerpt']) && $work['author_work']['post_excerpt'] != '')
			$new_post['post_excerpt'] = $work['author_work']['post_excerpt'];

		foreach ( $work['author_work'] as $element => $paragraphes) {
			if ($element != 'title')
				$text .= '<p>'.nl2br($paragraphes).'</p>';
		}
		$new_post['post_content'] = $text;
		// acf
		$extras = array();
		if( checkInstalledPlugin('Advanced Custom Fields') ) {
			$contentFound = FALSE;
			$text = '';
			//	var_dump($work['author_work']);
			foreach ( $work['author_work'] as $element => $paragraphes) {
				 if ($element == 'content'){
					$text .= '<p>'.nl2br($paragraphes).'</p>';
					$contentFound = TRUE;
				}
				else {
					$field = $wpdb->get_var( "SELECT meta_key FROM $table_meta WHERE meta_value = '".$element."'");
					$extras[substr($field,1)]['val'] = $paragraphes;
					$extras[substr($field,1)]['field'] = $element;
				}

			}

			if (!$contentFound) {
				foreach ( $work['author_work'] as $element => $paragraphes) {
					if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}
			}
		}
		if( checkInstalledPlugin('Meta Box')) {
			$contentFound = FALSE;
			$text = '';
	//		var_dump($work['author_work']);
			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'content'){
					$text .= '<p>'.nl2br($paragraphes).'</p>';
					$contentFound = TRUE;
				}
				else {
					$extras[$element]['val'] = $paragraphes;
					$extras[$element]['field'] = $element;
				}
			}

			if (!$contentFound) {
				foreach ( $work['author_work'] as $element => $paragraphes) {
				 if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}

			}
		}else {
			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element != 'title')
					$text .= '<p>'.nl2br($paragraphes).'</p>';
			}
		}

		$new_post['post_content'] = $text;
	//	var_dump($_GET);
	//	die();

		if (checkInstalledPlugin('WPML Multilingual CMS') && isset($_GET['lang_icl']) && $_GET['lang_icl'] != '') {
			$new_post['post_type'] = $post_origine->post_type; //get_post_type( $_GET['post_id_origine'] );
		}
		else if ($_GET['new_article'] == 2)
			$new_post['post_type'] = 'page';
		else
			$new_post['post_type'] = 'post';

//		var_dump($new_post);
//		die();
		$post_id = wp_insert_post($new_post);
//echo 'Error ';
		if (checkInstalledPlugin('WPML Multilingual CMS')) {
			$infosProjet = $tApi->getProjetInfos($_GET['projectId']);
	//		var_dump($infosProjet);
			if (isset($_GET['lang_icl']) && $_GET['lang_icl'] != '') {
				include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
				if (is_multisite()) {
					$blog_id = get_current_blog_id();
					$tableIclTrads = $wpdb->base_prefix . $blog_id."_icl_translations";
				}else
					$tableIclTrads = $wpdb->base_prefix.'icl_translations';
			/*	$req = 'INSERT INTO '.$tableIclTrads.' (element_type, element_id, trid, language_code, source_language_code)
						VALUES ("post_'.$new_post['post_type'].'", "'.$post_id.'", "'.$_GET['post_id_origine'].'", "'.$_GET['lang_icl'].'", "'.$infosProjet['language_from'].'")';
//				echo $req;
				$wpdb->query($req);

				$req = 'UPDATE '.$tableIclTrads.' SET  language_code="'.$_GET['lang_icl'].'", source_language_code="'.$infosProjet['language_from'].'", trid="'.$_GET['post_id_origine'].'"
						WHERE element_type="post_'.$new_post['post_type'].'" AND element_id="'.$post_id.'"';
				$wpdb->query($req);
//				echo $req;

				$req = 'UPDATE '.$tableIclTrads.' SET source_language_code="'.$infosProjet['language_from'].'",element_id="'.$post_id.'"
						WHERE element_type="post_'.$new_post['post_type'].'" AND trid="'.$_GET['post_id_origine'].'" AND language_code="'.$_GET['lang_icl'].'"';
				$wpdb->query($req);
//				echo $req;
*/
				$post_language_information = wpml_get_language_information($_GET['post_id_origine']);
				$trid = wpml_get_content_trid( 'post_' . $new_post['post_type'], $_GET['post_id_origine'] );
			//	var_dump($post_language_information);
//				echo 'trid '.$trid.'<br>';
			//	echo  'post_' . $new_post['post_type'].' / '. $post_id.' / '. $_GET['lang_icl'].' / '. $trid;
				$ret_wpml_add = wpml_add_translatable_content( 'post_' . $new_post['post_type'], $post_id, $_GET['lang_icl'], $trid );
			//	echo 'ret add ' .$ret_wpml_add;
			//	if ($ret_wpml_add == WPML_API_ERROR) {
			//		echo 'error';
					$wpdb->update( $tableIclTrads, array( 'trid' => $trid, 'language_code' => $_GET['lang_icl'], 'source_language_code' => $post_language_information['locale'] ), array( 'element_id' => $post_id ) );
			//	}
			}
		}
		//die();

		if( checkInstalledPlugin('Advanced Custom Fields')&& count($extras) != 0) {
			foreach ($extras as $key => $extra) {
				$post_id_base = $wpdb->get_var( "SELECT post_id FROM $table_meta WHERE meta_key = '".$extra['field']."'");
				$idT = get_IdTrad($post_id_base,$projet_infos['language_to']);
				$fieldTrad = $wpdb->get_var( "SELECT meta_key FROM $table_meta WHERE post_id = '".$idT."' AND meta_value LIKE '%key%field_%name%".$key."%type%' AND meta_key LIKE 'field_%'");

				update_post_meta( $post_id, '_'.$key, $fieldTrad );
				update_post_meta( $post_id, $key, $extra['val'] );
			}
		}
		if( checkInstalledPlugin('Meta Box')&& count($extras) != 0) {
			$aTextF = array();
			foreach ($extras as $key => $extra) {
				if (strpos($extra['field'], '_tmtext_') !== FALSE) {
					$field = substr($extra['field'], 0, strpos($extra['field'], '_tmtext_'));
					//$num = str_replace('_tmtext_', '', $extra['field']);
					$aTextF = array($extra['val']);
					update_post_meta( $post_id, $field, $aTextF);
				}else
					update_post_meta( $post_id, $extra['field'], $extra['val'] );
				}
//			if (count($aTextF) != 0) {
//				update_post_meta( $post_id, , $aTextF);
//			}
		}
		// on valide chez textmaster
		if ($tApi->getDocumentStatus($_GET['projectId'], $_GET['docId']) != 'completed' )
			$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId']);
	//	print_r($ret);
		if (isset($ret) && !is_array($ret ) &&  strpos($ret, 'Error') !== FALSE){
			if (strpos($ret, 'could not transition from completed via status_complete') !== FALSE) {
		//		echo $ret;
				echo $post_id;
			}
			else
				echo $ret;
		}
		else{
			echo $post_id;
		//	syncProjets();
			wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('', 1) );
		}
	}
	else {

		// on valide chez textmaster
		if ($tApi->getDocumentStatus($_GET['projectId'], $_GET['docId']) != 'completed' )
			$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId'], $_POST['select_textmasterSatisfaction'], $_POST['text_textmaster_message']);
		// on ajout l'auteur aux textmasters
		if ($_POST['textmaster_add_author'] == 'Y'){
			$tApi->addAuthor($_POST['auteur_description'], $_POST['select_textmasterStatutAuteur'], $_POST['auteurTmId']);
			$_SESSION['lastSyncTmAuteurs']  = '';
		}

//		print_r($ret);
		if (strpos($ret, 'Error') !== FALSE){
			if (strpos($ret, 'could not transition from completed via status_complete') !== FALSE) {
		//		echo $ret;
				echo $post_id;
			}
			else
				echo $ret;
		}
		else{
			echo $post_id;
//		print_r($ret);
			//syncProjets();
			wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('', 1) );
		}
	}
}
else
{

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="fr-FR" style="padding:0;">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="fr-FR" style="padding:0;">
<!--<![endif]-->
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
<div style="margin:10px;overflow:none;">
<?php


$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//$tApi->secretapi = get_option_tm('textmaster_api_secret');
//$tApi->keyapi =  get_option_tm('textmaster_api_key');
if ($_GET['type'] == 'redaction'){
	$idProjet = get_post_meta($_GET['post_id'],'textmasterId', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentId', TRUE);

	$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	if (!is_array($infos) || count($infos) == 0) {
		$table_name = $wpdb->base_prefix . "tm_projets";
		$textmasterDocumentId = $wpdb->get_var('SELECT idDocument FROM  ' . $table_name . ' WHERE id='.$idProjet.' AND ctype="copywriting" AND name like="%'.get_the_title($_GET['post_id']).'%"');
		$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	}

	if (key_exists('documents', $infos)){
		$work = $infos['documents'][0];
		$infos['id'] = $infos['documents'][0]['id'];
	}
	else
		$work = $infos;

}
else if ($_GET['type'] == 'trad'){
	$idProjet = get_IdProjetTrad($_GET['post_id'], $_GET['lang'], 'post'); //get_post_meta($_GET['post_id'],'textmasterIdTrad', TRUE);
	$textmasterDocumentId = get_IdDocTrad($_GET['post_id'], $_GET['lang']);//$_GET['idDocument'];// get_post_meta($_GET['post_id'],'textmasterDocumentIdTrad', TRUE);

	$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
//	print_r($infos);

	if (!is_array($infos) || count($infos) == 0) {
	//	$infosListe = $tApi->getDocumentList($idProjet);
	//	$infos = $infosListe['documents'][0];
		$table_name = $wpdb->base_prefix . "tm_projets";
		$textmasterDocumentId = $wpdb->get_var('SELECT idDocument FROM  ' . $table_name . ' WHERE id='.$idProjet.' AND ctype="translation" AND name like="%'.get_the_title($_GET['post_id']).'%"');
		$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	}

	if (@key_exists('documents', $infos)){
		$work = $infos['documents'][0];
		$infos['id'] = $infos['documents'][0]['id'];
	}
	else
		$work = $infos;

}
else
{
	$idProjet = get_post_meta($_GET['post_id'],'textmasterId', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentId', TRUE);

	$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
//	print_r($infos);
	if (!is_array($infos) || count($infos) == 0) {
		//$infosListe = $tApi->getDocumentList($idProjet);
		//$infos = $infosListe['documents'][0];
		$table_name = $wpdb->base_prefix . "tm_projets";
		$textmasterDocumentId = $wpdb->get_var('SELECT idDocument FROM  ' . $table_name . ' WHERE id='.$idProjet.' AND ctype="proofreading" AND name like="%'.get_the_title($_GET['post_id']).'%"');
		$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	}

	if (key_exists('documents', $infos)){
		$work = $infos['documents'][0];
		$infos['id'] = $infos['documents'][0]['id'];
	}

	else
		$work = $infos;

}

$text = '';
//var_dump($work);
if (count($work) != 0) {
	if (@key_exists('title', $work['author_work'])) {
		foreach ( $work['author_work'] as $element => $paragraphes) {
			//echo $element.' '.print_r($paragraphes, TRUE).'<br>';
			if ($element == 'title')
				$titre = '<strong id="titreTm">'.$paragraphes.'</strong>';
			else
				$text .= '<p>'.nl2br($paragraphes).'</p>';
		}
	}
	else {
		$textBrute = '';
		$textStruct = '';
		$titre = '';

		if (is_array($work['author_work'])) {
			foreach ( $work['author_work'] as $element => $paragraphes) {
				$textBrute .= '<p>'.$paragraphes.'</p>';
			}
		}


	//	$lignes = explode("\n", $textBrute );
	//	print_r($lignes);
	//	if (is_array($lignes)) {
	//		foreach ($lignes as $ligne) {
	//			if ($titre == '')
	//				$titre = $ligne;
	//			else
	//				$textStruct .= $ligne.'<br/>';
	//		}
	//d	}

		/*$textBrute = nl2br($textBrute);
		if (strpos($textBrute, "<br/><br/>") !== FALSE)
			$titre = substr($textBrute, strpos($textBrute, "<br/><br/>"));

		echo $titre;
		if ($titre == '' && strpos($textBrute, "<br><br>") !== FALSE)
			$titre = substr($textBrute, strpos($textBrute, "<br><br>"));
		echo strpos($textBrute, "<br><br>");
		*/
		$titre = '<strong style="font-weight:bold;">'.$titre.'</strong>';
		$text .= '<p>'.nl2br(trim($textBrute)).'</p>';

	}

}
//print_r($infos['documents'][0]['work']);
echo '<br/><div style="overflow:auto;max-height:80%;display:block;min-height:500px;" id="textmasterWork">';
echo $titre;
echo $text;
echo '</div><br/>';
echo metaboxes_post($tApi, $_GET['type'] , $idProjet, $textmasterDocumentId, $_GET['post_id']);
echo '<input type="hidden" id="post_id_origine" name="post_id_origine" value="'. $_GET['post_id'].'">';
echo '<input type="hidden" id="docId" name="docId" value="'. $infos['id'].'">';
echo '<input type="hidden" id="projectId" name="projectId" value="'. $idProjet.'">';
echo '<input type="hidden" id="textmaster_type" name="textmaster_type" value="'. $_GET['type'].'">';
echo '<img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" id="ajax-loading-validate" alt="">';
echo '<span id="waitMsgTm" style="display:none;vertical-align:middle;padding-top:5px;height:20px;">'.__('Merci de patienter', 'textmaster').'</span>';
//echo '<div id="publishing-action"><input name="Valider" type="button" class="button button-highlighted" id="useDocTextmaster" tabindex="5" accesskey="v" value="'.__('Valider','textmaster').'"></div>';
if (checkInstalledPlugin('WPML Multilingual CMS') && $_GET['type'] == 'trad') {
	$aLangsIcl = icl_get_languages();
	if (count($aLangsIcl) != 0) {
		echo '<select name="lang_icl" id="lang_icl">';
		foreach ($aLangsIcl as $langsIcl) {
			$lang = explode('-', $_GET['lang']);
			$selected = '';
			if ($langsIcl['language_code'] == $lang[0])
				$selected = ' selected="selected"';

			echo '<option value="'.$langsIcl['language_code']. '" '.$selected.'>'. $langsIcl['native_name'].'</option>';
		}
		echo '</select>';
		echo '<input name="useDocTextmasterIcl" type="button" class="button button-highlighted" id="useDocTextmasterIcl" tabindex="6" accesskey="c" value="'.__('Utiliser comme traduction','textmaster').'">';
	}
}
echo '<div id="publishing-action" style="margin-right:5px;"><input name="ValiderPlus" type="button" class="button button-highlighted" id="useDocTextmasterPlus" tabindex="6" accesskey="c" value="'.__('Valider et créer un article','textmaster').'"></div> ';
echo '<div id="publishing-action" style="margin-right:5px;"><input name="ValiderPlus" type="button" class="button button-highlighted" id="useDocTextmasterPlusPage" tabindex="6" accesskey="c" value="'.__('Valider et créer une page','textmaster').'"></div> ';
echo '<div style="clear:both;"></div><br/>';

echo '<div id="resultTM"></div>';
_e('En validant ce travail, il sera appouvé sur Textmaster et copier comme contenu de votre article. Il vous faudra sauvegarder ce dernier pour utiliser ce contenu.','textmaster');
echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; window.urlAdmin = "'. admin_url().'"; </script>';
?>
</div>
</body>
</html>
<?php
	// display:inline-block;


}
?>
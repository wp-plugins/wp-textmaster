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

if (!isset($_GET['idSite']))
	$_GET['idSite'] = '';


if (isset($_GET['valide']) && $_GET['valide'] == 1) {

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	if ($_GET['detail'] == 'true') {
		$post_id = approveTrad($tApi, $_GET['post_id'], $_GET['projectId'], $_GET['docId'], FALSE,  $_GET['idSite']);
	}else {
		$post_id = approveTrad($tApi, $_GET['post_id'], $_GET['projectId'], $_GET['docId'], TRUE,  $_GET['idSite']);
	}

/*	$post_origine = get_post($_GET['post_id']);
	$projet_infos = $tApi->getProjetInfos($_GET['projectId']);

	// on vverif si il y a déjà une trad existante
	$idTrad = get_IdTrad($_GET['post_id'], $projet_infos['language_to']);

	// on valide chez textmaster
	if ($tApi->getDocumentStatus($_GET['projectId'], $_GET['docId']) != 'completed' )
		$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId']);

	$infos = $tApi->getDocumentInfos($_GET['projectId'], $_GET['docId']);
	//	print_r($infos);
	// on créer un article avec le contenu
	if (key_exists('documents', $infos))
		$work = $infos['documents'][0];
	else
		$work = $infos;

	if ($work['author_work']['title'] != '')
		$new_post['post_title'] = $work['author_work']['title'];
	else  if ($infos['title'] != '')
		$new_post['post_title'] = $work['title'];

	foreach ( $work['author_work'] as $element => $paragraphes) {
		if ($element != 'title')
			$text .= '<p>'.nl2br($paragraphes).'</p>';
	}
	$new_post['post_content'] = $text;

	$new_post['post_type'] = $post_origine->post_type;
	$new_post['post_status'] = 'draft';

	if ($idTrad == '') {
		$post_id = wp_insert_post($new_post);
		$tm_lang = $_GET['post_id'].';'.$projet_infos['language_to'];
		update_post_meta($post_id, 'tm_lang', $tm_lang);
	}else {
		$new_post['ID'] = $idTrad;
		wp_update_post( $new_post );
		$post_id = $idTrad;
	}*/
	echo $post_id;
}
?>
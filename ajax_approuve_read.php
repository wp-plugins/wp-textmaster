<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//include "../../../wp-load.php";
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if (isset($_GET['valide']) && $_GET['valide'] == 1) {

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	$infos = $tApi->getDocumentInfos($_GET['id_projet'], $_GET['id_doc']);
	// on créer un article avec le contenu
	if (key_exists('documents', $infos))
		$work = $infos['documents'][0];
	else
		$work = $infos;

	if (isset($work['author_work']['title']) && $work['author_work']['title'] != '')
		$new_post['post_title'] = $work['author_work']['title'];
	else if ($infos['title'] != '')
		$new_post['post_title'] = $work['title'];

	if (is_array($work['author_work']) && count($work['author_work']) != 0) {
		// acf
		$extras = array();
		if( checkInstalledPlugin('Advanced Custom Fields') ) {
			$contentFound = FALSE;
			//	var_dump($work['author_work']);
			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'post_excerpt'){
					$new_post['post_excerpt'] = $work['post_excerpt'];
				} else 	if ($element == 'content'){
					$text .= '<p>'.nl2br($paragraphes).'</p>';
					$contentFound = TRUE;
				}
				else {
					$field = $wpdb->get_var( "SELECT meta_key FROM $wpdb->postmeta WHERE meta_value = '".$element."'");
					$extras[substr($field,1)]['val'] = $paragraphes;
					$extras[substr($field,1)]['field'] = $element;
				}

			}

			if (!$contentFound) {
				foreach ( $work['author_work'] as $element => $paragraphes) {
					if ($element == 'post_excerpt'){
						$new_post['post_excerpt'] = $work['post_excerpt'];
					} else 	if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}
			}
		}
		if( checkInstalledPlugin('Meta Box')) {
			$contentFound = FALSE;
			//	var_dump($work['author_work']);
			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'post_excerpt'){
					$new_post['post_excerpt'] = $work['post_excerpt'];
				} else 	if ($element == 'content'){
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
					if ($element == 'post_excerpt'){
						$new_post['post_excerpt'] = $work['post_excerpt'];
					} else 	if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}
			}
		}
		if( !checkInstalledPlugin('Advanced Custom Fields') && !checkInstalledPlugin('Meta Box')) {

			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'post_excerpt'){
					$new_post['post_excerpt'] = $work['post_excerpt'];
				} else 	if ($element != 'title')
					$text .= '<p>'.nl2br($paragraphes).'</p>';
			}
		}

		$new_post['ID'] = $_GET['post_id'];
		$new_post['post_content'] = $text;
		wp_update_post($new_post);

		if( checkInstalledPlugin('Advanced Custom Fields') && count($extras) != 0) {
			foreach ($extras as $key => $extra) {
				$post_id_base = $wpdb->get_var( "SELECT post_id FROM $table_meta WHERE meta_key = '".$extra['field']."'");
				$idT = get_IdTrad($post_id_base,$projet_infos['language_to']);
				$fieldTrad = $wpdb->get_var( "SELECT meta_key FROM $table_meta WHERE post_id = '".$idT."' AND meta_value LIKE '%key%field_%name%".$key."%type%' AND meta_key LIKE 'field_%'");

				update_post_meta( $post_id, '_'.$key, $fieldTrad );
				update_post_meta( $post_id, $key, $extra['val'] );
			}
		}
		if( checkInstalledPlugin('Meta Box') && count($extras) != 0) {
			$aTextF = array();
			foreach ($extras as $key => $extra) {
				if (strpos($extra['field'], '_tmtext_') !== FALSE) {
					$num = str_replace('_tmtext_', '', $extra['field']);
					$aTextF[$num] = $extra['val'];
					update_post_meta( $post_id, $extra['field'], $aTextF);
				}else
					update_post_meta( $post_id, $extra['field'], $extra['val'] );
			}

		}
		$ret = $tApi->valideDoc($_GET['id_projet'], $_GET['id_doc']);
	}
	echo $ret;
}
?>
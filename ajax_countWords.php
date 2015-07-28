<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//require( '../../../wp-load.php' );
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once('confs_path.php');
if (defined('PATH_WP_LOAD') && PATH_WP_LOAD != '')
	$uri_load = PATH_WP_LOAD;
else{
	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	$uri_load = $parse_uri[0];
}
require_once( $uri_load . 'wp-load.php' );

//include( plugin_dir_path( __FILE__ ). '/textmaster.class.php' );

if (isset( $_POST['contenu'])) {
	$post = get_post($_POST['post_id']);
	$tm_count = get_post_meta($_POST['post_id'], 'tm_count_words', TRUE);
	$tm_count_date = get_post_meta($_POST['post_id'], 'tm_count_words_date', TRUE);
//	echo strtotime($post->post_modified) .'<br>';
//	echo strtotime($tm_count_date) .'<br>';
	if ($tm_count != 0 && strtotime($post->post_modified) < strtotime($tm_count_date)) {
		echo $tm_count;
	} else {
		$content = $_POST['contenu'].' '.$post->excerpt;
		$contentText = cleanWpTxt( $content );
		//	$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
		//echo $contentText;
		$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
		$nbMots = $tApi->countWords( $contentText, $_POST['post_id']);
		echo $nbMots;
	}
}
else {	// les retours des api
	$data = json_decode(file_get_contents("php://input"), true);
	if (count($data) != 0) {
	//	logDatas($_SERVER['REMOTE_ADDR'].' : '.print_r($data, TRUE));

		update_post_meta($data['custom_data']['id_post'], 'tm_count_words', $data['word_count']);
		update_post_meta($data['custom_data']['id_post'], 'tm_count_words_date', date('Y-m-d H:i:s'));
		echo 'ok';
	}
}
?>
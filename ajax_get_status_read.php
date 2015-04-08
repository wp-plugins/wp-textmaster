<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//require( '../../../wp-load.php' );
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

$txtRet = '';

if (isset($_GET['id_projet']) && $_GET['id_projet'] != ''){ // && isset($_GET['id_doc']) && $_GET['id_doc'] != '') {
	$result = $tApi->getProjetStatus($_GET['id_projet']);
	if (isset($_GET['id_doc']) && $_GET['id_doc'] != '')
		$result = $tApi->getDocumentStatus($_GET['id_projet'], $_GET['id_doc']);

	$txtRet = $result;
}
echo $txtRet;
?>
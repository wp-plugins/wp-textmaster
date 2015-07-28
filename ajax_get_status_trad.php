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

$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
if ( isset($_GET['idSite']) && $_GET['idSite'] != '') {
	$table_meta = $wpdb->base_prefix . $_GET['idSite']."_postmeta";
}else{
	$table_meta = $wpdb->prefix . "postmeta";
}

$idProjet = get_IdProjetTrad($_GET['post_id'], $_GET['lang'],'post', $table_meta);
$idDocument = get_IdDocTrad($_GET['post_id'], $_GET['lang'], 'post', $table_meta);
$txtRet = '';

if ($idProjet != '') {
	//	print_r($tApi->getProjetInfos($idProjet));
	//project_cost_in_credits
	$result = $tApi->getProjetStatus($idProjet);
	if ($idDocument != '')
		$result = $tApi->getDocumentStatus($idProjet, $idDocument);
//	print_r($result);
	if ($result == 'in_review')
	{
		if (!isset($_GET['noText']) || $_GET['noText'] == ''){
			if (isset($_GET['defaultBtn']) && $_GET['defaultBtn'] != '')
				$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" data-title="'.__('Voir / Valider la traduction','textmaster').'" onclick="seeTrad(this);" ></div><div style="clear:both"></div>';
			else
				$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="valideTrad(\''.$_GET['post_id'].'\', \''.$idProjet.'\', \''.$idDocument.'\');"></div><div style="clear:both"></div>';
		}
		else
			$txtRet = $result;
	}
	else if ( $result == 'in_progress' || $result == 'waiting_assignment' ||  $result == 'quality_control')
	{
		if (!isset($_GET['noText']) || $_GET['noText'] == '')
			$txtRet = __('Cet article est en cours de traduction.','textmaster');
		else
			$txtRet = $result;
	}
	else if ($result == 'completed') {
		if (!isset($_GET['noText']) || $_GET['noText'] == ''){
			if (get_option_tm('textmaster_useMultiLangues') == 'Y')
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Valider la traduction','textmaster').'" onclick="valideTrad(\''.$_GET['post_id'].'\', \''.$idProjet.'\', \''.$idDocument.'\', true, \''.__('Merci de patienter...', 'textmaster').'\');"></div><div style="clear:both"></div>';
			else
				$txtRet = __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la traduction','textmaster').'" data-title="'.__('Voir / Valider la traduction','textmaster').'" onclick="seeTrad(this);" ></div><div style="clear:both"></div>';
		}else {
			$txtRet = $result;
		}
	}
	else {
		if (isset($_GET['defaultBtn']) && $_GET['defaultBtn'] != '')
			$txtRet =  '<br/><div id="publishing-action"><input name="traduction" type="button" class="button button-highlighted" id="traduction" tabindex="5" accesskey="p" value="'.__('Envoyer','textmaster').'" ></div>';

	}
}
else {
	if (isset($_GET['defaultBtn']) && $_GET['defaultBtn'] != '')
		$txtRet =  '<br/><div id="publishing-action"><input name="traduction" type="button" class="button button-highlighted" id="traduction" tabindex="5" accesskey="p" value="'.__('Envoyer','textmaster').'" onClick="launchTrad();"></div>';

}
echo $txtRet;
?>
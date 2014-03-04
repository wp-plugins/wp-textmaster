<?php
include "../../../wp-load.php";

if (isset($_GET['valide']) && $_GET['valide'] == 1) {

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');


	if ($_GET['type'] == 'redaction' || $_GET['new_article'] == 1 || $_GET['new_article'] == 2) {

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
		else
			$new_post['post_title'] = __('Untitled');

		foreach ( $work['author_work'] as $element => $paragraphes) {
			if ($element != 'title')
				$text .= '<p>'.nl2br($paragraphes).'</p>';
		}
		$new_post['post_content'] = $text;

		if ($_GET['new_article'] == 2)
			$new_post['post_type'] = 'page';
		else
			$new_post['post_type'] = 'post';

		$post_id = wp_insert_post($new_post);

		// on valide chez textmaster
		$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId']);
	//	print_r($ret);
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
		//	syncProjets();
			wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
		}
	}
	else {
		// on valide chez textmaster
		$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId']);
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
			wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
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


$tApi = new textmaster_api();
$tApi->secretapi = get_option('textmaster_api_secret');
$tApi->keyapi =  get_option('textmaster_api_key');
if ($_GET['type'] == 'redaction'){
	$idProjet = get_post_meta($_GET['post_id'],'textmasterId', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentId', TRUE);

	$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
//	print_r($infos);
	if (!is_array($infos) || count($infos) == 0) {
		$table_name = $wpdb->prefix . "tm_projets";
		$textmasterDocumentId = $wpdb->get_var('SELECT idDocument FROM  ' . $table_name . ' WHERE id='.$idProjet.' AND ctype="copywriting" AND name like="%'.get_the_title($_GET['post_id']).'%"');
		$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	}

	if (key_exists('documents', $infos))
		$work = $infos['documents'][0];
	else
		$work = $infos;

}
else if ($_GET['type'] == 'trad'){
	$idProjet = get_post_meta($_GET['post_id'],'textmasterIdTrad', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentIdTrad', TRUE);

	$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);


	if (!is_array($infos) || count($infos) == 0) {
	//	$infosListe = $tApi->getDocumentList($idProjet);
	//	$infos = $infosListe['documents'][0];
		$table_name = $wpdb->prefix . "tm_projets";
		$textmasterDocumentId = $wpdb->get_var('SELECT idDocument FROM  ' . $table_name . ' WHERE id='.$idProjet.' AND ctype="translation" AND name like="%'.get_the_title($_GET['post_id']).'%"');
		$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	}

	if (key_exists('documents', $infos))
		$work = $infos['documents'][0];
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
		$table_name = $wpdb->prefix . "tm_projets";
		$textmasterDocumentId = $wpdb->get_var('SELECT idDocument FROM  ' . $table_name . ' WHERE id='.$idProjet.' AND ctype="proofreading" AND name like="%'.get_the_title($_GET['post_id']).'%"');
		$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
	}

	if (key_exists('documents', $infos))
		$work = $infos['documents'][0];
	else
		$work = $infos;

}

$text = '';
//print_r($work);
if (count($work) != 0) {

	if (@key_exists('title', $work['author_work'])) {
		foreach ( $work['author_work'] as $element => $paragraphes) {
			if ($element == 'title')
				$text .= '<strong id="titreTm">'.$paragraphes.'</strong>';
			else
				$text .= '<p>'.nl2br($paragraphes).'</p>';
		}
	}
	else {
		$textBrute = '';
		$textStruct = '';
		$titre = '';

		foreach ( $work['author_work'] as $element => $paragraphes) {
			$textBrute .= $paragraphes;
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
		$text .= '<strong style="font-weight:bold;">'.$titre.'</strong>';
		$text .= '<p>'.nl2br(trim($textBrute)).'</p>';

	}

}
//print_r($infos['documents'][0]['work']);
echo '<br/><div style="overflow:auto;max-height:80%;display:block;min-height:500px;" id="textmasterWork">';
echo $text;
echo '</div><br/>';
echo '<input type="hidden" id="docId" name="docId" value="'. $infos['id'].'">';
echo '<input type="hidden" id="projectId" name="projectId" value="'. $idProjet.'">';
echo '<input type="hidden" id="textmaster_type" name="textmaster_type" value="'. $_GET['type'].'">';
echo '<img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" id="ajax-loading-validate" alt="">';
echo '<span id="waitMsgTm" style="display:none;vertical-align:middle;padding-top:5px;height:20px;">'.__('Merci de patienter', 'textmaster').'</span>';
echo '<div id="publishing-action"><input name="Valider" type="button" class="button button-highlighted" id="useDocTextmaster" tabindex="5" accesskey="v" value="'.__('Valider','textmaster').'"></div>';
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
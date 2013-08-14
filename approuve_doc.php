<?php
include "../../../wp-load.php";

if ($_GET['valide'] == 1) {

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');


	if ($_GET['type'] == 'redaction') {
		// on créer un article avec le contenu
		$infos = $tApi->getDocumentList($_GET['projectId']);

		if ($infos['documents'][0]['work']['title'] != '')
			$new_post['post_title'] = $infos['documents'][0]['work']['title'];
		else
			$new_post['post_title'] = __('Untitled');

		foreach ( $infos['documents'][0]['work'] as $element => $paragraphes) {
			if ($element != 'title')
				$text .= '<p>'.$paragraphes.'</p>';
		}
		$new_post['post_content'] = $text;

		$post_id = wp_insert_post($new_post);

		// on valide chez textmaster
		$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId']);
		echo $post_id;

	}
	else {
		// on valide chez textmaster
		$ret = $tApi->valideDoc($_GET['projectId'], $_GET['docId']);
	}
}
else
{

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="fr-FR">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="fr-FR">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel='stylesheet' href='/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=admin-bar,wp-admin&amp;ver=3.4.2' type='text/css' media='all' />
<link rel='stylesheet' id='thickbox-css'  href='/wp-includes/js/thickbox/thickbox.css?ver=3.4.2' type='text/css' media='all' />
<link rel='stylesheet' id='colors-css'  href='/wp-admin/css/colors-fresh.css?ver=3.4.2' type='text/css' media='all' />
<!--[if lte IE 7]>
<link rel='stylesheet' id='ie-css'  href='/wp-admin/css/ie.css?ver=3.4.2' type='text/css' media='all' />
<![endif]-->
<script type='text/javascript' src='/wp-admin/load-scripts.php?c=0&amp;load=jquery,utils&amp;ver=3.4.2'></script>
<script type='text/javascript' src='/wp-content/plugins/wp-textmaster/textmaster.js?ver=3.4.2'></script>
</head>
<body>
<div style="margin:10px;overflow:none;">
<?php


$tApi = new textmaster_api();
$tApi->secretapi = get_option('textmaster_api_secret');
$tApi->keyapi =  get_option('textmaster_api_key');
if ($_GET['type'] == 'redaction'){
	$idProjet = get_post_meta($_GET['post_id'],'textmasterId', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentId', TRUE);
	$infos = $tApi->getDocumentList($idProjet);
	$text = '';
	if (count($infos['documents'][0]['work'] ) != 0) {
		foreach ( $infos['documents'][0]['work'] as $element => $paragraphes) {
			if ($element == 'title')
				$text .= '<strong>'.$paragraphes.'</strong>';
			else
				$text .= '<p>'.$paragraphes.'</p>';
		}
	}
}
else if ($_GET['type'] == 'trad'){
	$idProjet = get_post_meta($_GET['post_id'],'textmasterIdTrad', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentIdTrad', TRUE);
	$infos = $tApi->getDocumentList($idProjet);
	$text = $infos['documents'][0]['work']['text'];
}
else
{
	$idProjet = get_post_meta($_GET['post_id'],'textmasterId', TRUE);
	$textmasterDocumentId = get_post_meta($_GET['post_id'],'textmasterDocumentId', TRUE);
	$infos = $tApi->getDocumentList($idProjet);
	$text = $infos['documents'][0]['work']['text'];
}

//print_r($infos['documents'][0]['work']);
echo '<br/><div style="overflow:auto;height:570px;" id="textmasterWork">';
echo $text;
echo '</div><br/>';
echo '<input type="hidden" id="docId" name="docId" value="'. $infos['documents'][0]['id'].'">';
echo '<input type="hidden" id="projectId" name="projectId" value="'. $idProjet.'">';
echo '<input type="hidden" id="textmaster_type" name="textmaster_type" value="'. $_GET['type'].'">';
echo '<div id="publishing-action"><input name="Valider" type="button" class="button button-highlighted" id="useDocTextmaster" tabindex="5" accesskey="p" value="'.__('Valider','textmaster').'"></div>';
echo '<div style="clear:both;"></div><br/>';
_e('En validant ce travail, il sera appouvé sur Textmaster et copier comme contenu de votre article. Il vous faudra sauvegarder ce dernier pour utiliser ce contenu.','textmaster');
?>
</div>
</body>
</html>
<?php
}
?>
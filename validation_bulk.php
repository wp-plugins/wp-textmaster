<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//include "../../../wp-load.php";
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if (!isset($_REQUEST['type']))
	$_REQUEST['type'] = '';
if (!isset($_REQUEST['trad_only']))
	$_REQUEST['trad_only'] = '';
if (!isset($_REQUEST['read_only']))
	$_REQUEST['read_only'] = '';
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="fr-FR" style="padding:0;">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="fr-FR" style="padding:0;">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel='stylesheet' href='<?php echo site_url(); ?>/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=dashicons,admin-bar,wp-admin,buttons,wp-auth-check,wp-pointer&amp;ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='thickbox-css'  href='/wp-includes/js/thickbox/thickbox.css?ver=3.4.2' type='text/css' media='all' />
<link rel='stylesheet' id='colors-css'  href='<?php echo site_url(); ?>/wp-admin/css/colors.min.css?ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='texmaster-css'  href='<?php echo plugins_url('textmaster.css' , __FILE__ ) ?>' type='text/css' media='all' />
<!--[if lte IE 7]>
<link rel='stylesheet' id='ie-css'  href='/wp-admin/css/ie.css?ver=3.4.2' type='text/css' media='all' />
<![endif]-->
<script type='text/javascript' src='<?php echo site_url(); ?>/wp-admin/load-scripts.php?c=0&amp;load=jquery,jquery-core,jquery-migrate,utils&amp;ver=<?php echo $wp_version; ?>'></script>
<script type='text/javascript' src='<?php echo plugins_url('textmaster.js' , __FILE__ ) ?>?ver=<?php echo get_tm_plugin_version();?>'></script>


</head>
<body style="padding:10px;margin:0;">
<div id="wpbody">
<form method="post" action= "validation_bulk.php" id="tm_buckform">
<?php
if (isset($_POST['langues']) && count($_POST['langues']) != 0 ) {
	$arrayIds = explode(';', $_REQUEST['post_ids'] );
	echo '<strong>'.__('Traduction', 'textmaster').'</strong><br/>';
	echo '<div class="progessBar" id="pbTrads" data-value="0" total-value="'.(count($arrayIds)-1)*count($_POST['langues']).'" style="width:99%;float:none;"><div class="fill"></div><span class="pourcentTrad"style="margin-left: 48%;"> <span id="current">0</span> / '.(count($arrayIds)-1)*count($_POST['langues']).' </span></div><br/>';
}
if (isset($_POST['valider_corrctions']) && $_POST['valider_corrctions'] == 'oui'){
	$arrayIds = explode(';', $_REQUEST['post_ids'] );
	echo '<strong>'.__('Corrections', 'textmaster').'</strong><br/>';
	echo '<div class="progessBar" id="pbReads" data-value="0" total-value="'.(count($arrayIds)-1).'" style="width:99%;float:none;"><div class="fill"></div><span class="pourcentTrad"style="margin-left: 48%;"> <span id="current">0</span> / '.(count($arrayIds)-1).' </span></div><br/>';
}
// traductions
add_action("admin_body_class", "set_iframe_class");
if ($_REQUEST['read_only'] != 'yes')
	echo '<h2>'.__('Traduction', 'textmaster').'</h2>';
$languesSelected = TRUE;
$valide = FALSE;
if (isset($_POST['langues']) && count($_POST['langues']) != 0 ) {
	$arrayIds = explode(';', $_REQUEST['post_ids'] );
	//echo '<br/><div class="progessBar" id="pbTrads" data-value="0" total-value="'.(count($arrayIds)-1)*count($_POST['langues']).'" style="width:99%;float:none;"><div class="fill"></div><span class="pourcentTrad"style="margin-left: 48%;"> <span id="current">0</span> / '.(count($arrayIds)-1)*count($_POST['langues']).' </span></div>';
	echo '<ul id="tradValidees">';
	echo '</ul>';

	echo '<script>';
	echo 'window.urlPlugin = "'.plugins_url('' , __FILE__ ).'";';
	echo validateTraduction($_POST['type']);
	echo '</script>';

	$valide = TRUE;
}
else if (count($_POST) != 0 && (!isset( $_POST['valider_corrctions'] ) || $_POST['valider_corrctions'] != 'oui' ))
	$languesSelected = FALSE;

// afichage de base
if (count($_REQUEST['post_ids']) == 0 || $_REQUEST['post_ids'] == '') {
	echo '<center><div class="error">';
	_e('Merci de séléctionner au moins un article', 'textmaster');
	echo '</strong></div>';
}
else if (!$valide && $_REQUEST['read_only'] != 'yes' ) {
	if (!$languesSelected)
		echo '<div class="error">'.__('Merci de séléction au moins une langue','textmaster').'</div>';

	echo '<label class="options_pricetm">'.__('Valider les langues :','textmaster').'</label>';
	$table_langues =  $wpdb->base_prefix .'tm_langues';
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));

	if (count($textmaster_langues) != 0) {
		echo '<ul>';
		echo '<li style="width:30%;float:left;"><input type="checkbox" onClick="chekcAllLangs(this);"';
		echo '/><strong>'.__('Toutes les langues','textmaster') .'</strong></li>' ;
		foreach ($textmaster_langues as $lang) {

			if ($textmaster_langueDefaut != $lang) {
				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$lang'" );

				echo '<li style="width:30%;float:left;"><input type="checkbox" name="langues[]" value="'.$lang.'" class="lang_validate"';
				echo '/>'.$langue_str .'</li>' ;
			}

		}
		echo '</ul>';
	}


	echo '<div style="clear:both"></div>';
	echo '<hr/>';
}
// relectures

if ($_REQUEST['trad_only'] != 'yes' && $_REQUEST['post_ids'] != '') {
	echo '<h2>'.__('Corrections', 'textmaster').'</h2>';
	if (isset($_POST['valider_corrctions']) && $_POST['valider_corrctions'] == 'oui'){
		echo '<ul id="readValidees">';
		echo '</ul>';
		echo '<script>';
		echo 'window.urlPlugin = "'.plugins_url('' , __FILE__ ).'";';
		echo validateCorrection();
		echo '</script>';
	}	else {
		echo '<ul>';
		echo '<li><input type="checkbox" name="valider_corrctions" value="oui" />'.__('Valider toutes les corrections et remplacer les posts','textmaster').'</li>';
		echo '</ul>';
	}
}

echo '<div style="clear:both"></div>';
echo '<input type="hidden" name="post_ids" id="post_ids" value="'.$_REQUEST['post_ids'].'"/>';
echo '<input type="hidden" name="trad_only" id="trad_only" value="'.$_REQUEST['trad_only'].'"/>';
echo '<input type="hidden" name="read_only" id="read_only" value="'.$_REQUEST['read_only'].'"/>';
if (isset($_REQUEST['type']))
	echo '<input type="hidden" name="type" id="type" value="'.$_REQUEST['type'].'"/>';
if (isset($_REQUEST['site']))
	echo '<input type="hidden" name="site" id="site" value="'.$_REQUEST['site'].'"/>';
echo '<br/><img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckTransalte" alt="">';
echo '<div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-tmBluckTransalte">'.__('Merci de patienter','textmaster').'</div>';
echo '<div style="clear:both"></div>';
echo '<div id="publishing-action"><input name="save" type="submit" class="button button-highlighted" id="bulk_readproof" tabindex="5" accesskey="p" value="' . __('Valider','textmaster') . '" onclick="jQuery(\'.ajax-loading-tmBluckTransalte\').show();"></div>';

?>
</form>
</div>
</body>
</html>
<?php

function validateTraduction($type){
	global $wpdb;

	$_REQUEST['site'] = '';
	if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
		$ret = switch_to_blog( $_REQUEST['site'] );
	if (!isset($_REQUEST['tmsite']) && is_multisite())
		$_REQUEST['site'] = get_current_blog_id();

	$table_langues =  $wpdb->base_prefix .'tm_langues';
	$ret = FALSE;
	$str = '';
	$totalItems = (count($_REQUEST['post_ids'])-1)*count($_REQUEST['langues']);

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	$aInfosPost = getInfosPosts($_REQUEST['post_ids'], $type, $_REQUEST['site']);

	foreach ($aInfosPost as $post) {
		if (is_object($post['content'])) {
			if ($type == ''  || $type == 'post') { // les posts
				if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
					$table_name = $wpdb->base_prefix . $_REQUEST['site']. "_postmeta";
				else
					$table_name = $wpdb->prefix . "postmeta";

				$numPost = 1;
				foreach ($_POST['langues'] as $lang) {
					$idDocTrad = get_IdDocTrad($post['content']->ID, $lang, $type, $table_name);
					$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$lang'" );

					$post_id = FALSE;
					$idTrad = get_IdProjetTrad($post['content']->ID, $lang, $type, $table_name);
					if ($idTrad != ''){

					//	echo $idDocTrad;
					//	$status = $tApi->getProjetStatus($idTrad);
						$str .= "jQuery.ajax({\n";
						$str .= "type: 'GET',\n";
						$str .= "url: '".plugins_url('ajax_get_status_trad.php' , __FILE__ )."',\n";
						$str .= "data: { post_id: '".$post['content']->ID."', lang: '".$lang."', noText: 'noText', idSite: '".$_REQUEST['site']."'}\n";
						$str .= "}).done(function(data ) {\n";
						//$str .= "	alert(data);\n";
						$str .= "	if (data == 'in_review') {\n";
						$str .= "		valideTrad('".$post['content']->ID."', '".$idTrad."', '".$idDocTrad."', false, '', '".$_REQUEST['site']."');\n";
						$str .= "		jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
						$str .= "		jQuery('#tradValidees').prepend('<li><div class=\"updated\"><strong>".str_replace("'", "\'", $post['content']->post_title) ."</strong>". __(' a été validé en ', 'textmaster')." ".$langue_str."</div></li>');\n";
						$str .= "		progessBar();\n";
						$str .= "	}else {\n";
						$str .= "		jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
						$str .= "		jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->post_title) ."</strong>". __(' ne peut être validé en ', 'textmaster')." ".$langue_str." ('+data+') </div></li>').delay(".($numPost*100).");\n";
						$str .= "		progessBar();\n";
						$str .= "	}\n";
						$str .= "});\n";
						// on valide
				//		if ($status == 'in_review') {
						//	$post_id = approveTrad($tApi, $post['content']->ID, $idTrad, $idDocTrad);
					//		if ($post_id !== FALSe) {
					//			wp_publish_post( $post_id );
					//			$str .= "jQuery('#tradValidees').prepend('<li><div class=\"updated\"><strong>".$post['content']->post_title ."</strong>". __(' a été validé en ', 'textmaster')." ".$lang."</div></li>');\n";
					//		}else
					//			$str .= "jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".$post['content']->post_title ."</strong>". __(' ne peut être validé en ', 'textmaster')." ".$lang." (".$tApi->getLibStatus($status).") </div></li>').delay(".($numPost*100).");\n";


				//		}else {
				//			$str .= "jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".$post['content']->post_title ."</strong>". __(' ne peut être validé en ', 'textmaster')." ".$lang." (".$tApi->getLibStatus($status).") </div></li>').delay(".($numPost*100).");\n";
				//		}
					}else {
						$str .= "jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
						$str .= "jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->post_title) ."</strong>". __(' ne peut être validé en', 'textmaster')." ".$langue_str."</div></li>').delay(".($numPost*100).");\n";
						$str .= "progessBar();\n";
					}
					$numPost++;
				}
			} else { // les tags
				if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
					$table_name = $wpdb->base_prefix . $_REQUEST['site']. "_options";
				else
					$table_name = $wpdb->prefix . "options";
				$numPost = 1;
				foreach ($_POST['langues'] as $lang) {
					$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$lang'" );
					$idDocTrad = get_IdDocTrad($post['content']->term_id, $lang, $type, $table_name);

					$post_id = FALSE;
					$idTrad = get_IdProjetTrad($post['content']->term_id ,$lang, $type, $table_name);
					if ($idTrad != ''){
						$status = $tApi->getProjetStatus($idTrad);
						// on valide
						if ($status == 'in_review') {
							$post_id = approveTradTag($tApi, $post['content']->term_id, $idTrad, $idDocTrad);
							if ($post_id != FALSE){
								$str .= "jQuery('#tradValidees').prepend('<li><div class=\"updated\"><strong>".str_replace("'", "\'", $post['content']->name) ."</strong>". __(' a été validé en ', 'textmaster')." ".$langue_str."</div></li>').delay(".($numPost*100).");\n";
								$str .= "jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
								$str .= "progessBar();\n";
							}
							else{
								$str .= "jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->name) ."</strong>". __(' ne peut être validé en ', 'textmaster')." ".$langue_str."</div></li>').delay(".($numPost*100).");\n";
								$str .= "jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
								$str .= "progessBar();\n";
						}
						}else {
							$str .= "jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->name) ."</strong>". __(' ne peut être validé en ', 'textmaster')." ".$langue_str." (".$tApi->getLibStatus($status).") </div></li>').delay(".($numPost*100).");\n";
							$str .= "jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
							$str .= "progessBar();\n";
						}

					}else {
						$str .= "jQuery('#tradValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->name) ."</strong>". __(' ne peut être validé en', 'textmaster')." ".$langue_str."</div></li>').delay(".($numPost*100).");\n";
						$str .= "jQuery('#pbTrads').attr('data-value', parseInt(jQuery('#pbTrads').attr('data-value'))+1);\n";
						$str .= "progessBar().delay(".($numPost*100).");\n";
					}
				}
				$numPost++;
			}
		}

	}
	if ($numPost != 1) {
		$str .= "jQuery(document).ready(function($) {\n";
//		$str .= "alert(jQuery('#TB_closeWindowButton', window.parent.document).length);\n";
		$str .= "jQuery('#TB_closeWindowButton', window.parent.document).click( function () {\n";
//		$str .= "	alert('reload');\n";
		$str .= "	window.parent.location.reload();\n";
		$str .= "	return false; });\n";
		$str .= "});\n";
	}

	$ret = $str;

	if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
		restore_current_blog();

	return $ret;
}

function validateCorrection(){
	$ret = FALSE;
	$str = '';

	$_REQUEST['site'] = '';
	if (isset($_REQUEST['site']) && $_REQUEST['site'] > 1)
		$ret = switch_to_blog( $_REQUEST['site'] );
	if (!isset($_REQUEST['tmsite']) && is_multisite())
		$_REQUEST['site'] = get_current_blog_id();

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	$aInfosPost = getInfosPosts($_REQUEST['post_ids'], '', $_REQUEST['site']);

	$numPost =1;
	foreach ($aInfosPost as $post) {
		if (is_object($post['content'])) {
			$text = '';

			$idProjet = get_post_meta_tm($post['content']->ID,'textmasterId', TRUE, $_REQUEST['site']);
			$textmasterDocumentId = get_post_meta_tm($post['content']->ID,'textmasterDocumentId', TRUE, $_REQUEST['site']);
			echo $idProjet. ' / '.$textmasterDocumentId."\n";
			//	echo $idDocTrad;
			if ($idProjet != '') {
				$str .= "jQuery.ajax({\n";
				$str .= "type: 'GET',\n";
				$str .= "url: '".plugins_url('ajax_get_status_read.php' , __FILE__ )."',\n";
				$str .= "data: { id_projet: '".$idProjet."', id_doc: '".$textmasterDocumentId."'}\n";
				$str .= "}).done(function(data ) {\n";
				//$str .= "	alert(data);\n";
				$str .= "	if (data == 'in_review') {\n";
				$str .= "		valideRead('".$post['content']->ID."', '".$idProjet."', '".$textmasterDocumentId."');\n";
				$str .= "		jQuery('#pbReads').attr('data-value', parseInt(jQuery('#pbReads').attr('data-value'))+1);\n";
				$str .= "		jQuery('#readValidees').prepend('<li><div class=\"updated\"><strong>".str_replace("'", "\'", $post['content']->post_title) ."</strong>". __(' a été validé', 'textmaster')."</div></li>');\n";
				$str .= "		progessBar();\n";
				$str .= "	}else {\n";
				$str .= "		jQuery('#pbReads').attr('data-value', parseInt(jQuery('#pbReads').attr('data-value'))+1);";
				$str .= "		jQuery('#readValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->post_title) ."</strong>". __(' ne peut être validé', 'textmaster')." ('+data+') </div></li>').delay(".($numPost*100).");\n";
				$str .= "		progessBar();\n";
				$str .= "	}\n";
				$str .= "});\n";
			}else {
				$str .= "		jQuery('#pbReads').attr('data-value', parseInt(jQuery('#pbReads').attr('data-value'))+1);";
				$str .= "		jQuery('#readValidees').prepend('<li><div class=\"error\"><strong>".str_replace("'", "\'", $post['content']->post_title) ."</strong>". __(' ne peut être validé', 'textmaster')." </div></li>').delay(".($numPost*100).");\n";
				$str .= "		progessBar();\n";
			}


			$numPost++;



/*			$status = $tApi->getDocumentStatus($idProjet, $textmasterDocumentId);
			// on valide
			if ($status == 'in_review') {
				$infos = $tApi->getDocumentInfos($idProjet, $textmasterDocumentId);
				// on créer un article avec le contenu
				if (key_exists('documents', $infos))
					$work = $infos['documents'][0];
				else
					$work = $infos;

				if (isset($work['author_work']['title']) && $work['author_work']['title'] != '')
					$new_post['post_title'] = $work['author_work']['title'];
				else  if ($infos['title'] != '')
					$new_post['post_title'] = $work['title'];

				foreach ( $work['author_work'] as $element => $paragraphes) {
					if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}
				$new_post['ID'] = $post['content']->ID;
				$new_post['post_content'] = $text;
				wp_update_post($new_post);

				$ret = $tApi->valideDoc($idProjet, $textmasterDocumentId);

				if (!is_array($ret ) &&  strpos($ret, 'Error') === FALSE){
					echo '<div class="error">'. __(' ce post ne peut être validé ', 'textmaster').' ('.$tApi->getLibStatus($status).') </div>';

				}else
					echo '<div class="updated">'. __(' ce post a été validé ', 'textmaster').' </div>';
*/

//			}else {
//				$str .= "jQuery('#readValidees').prepend('<li><div class=\"error\"><strong>".$post['content']->post_title ."</strong> ". __(' ce post ne peut être validé ', 'textmaster'). " ('".$tApi->getLibStatus($status)."') </div></li>');\n";
//			}
		}
	}
	if ($numPost != 1) {
		$str .= "jQuery(document).ready(function($) {\n";
		//		$str .= "alert(jQuery('#TB_closeWindowButton', window.parent.document).length);\n";
		$str .= "jQuery('#TB_closeWindowButton', window.parent.document).click( function () {\n";
		//		$str .= "	alert('reload');\n";
		$str .= "	window.parent.location.reload();\n";
		$str .= "	return false; });\n";
		$str .= "});\n";
	}
	return $str;
}

?>
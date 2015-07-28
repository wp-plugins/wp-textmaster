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
<body style="padding:10px;padding-right:25px;margin:0;">
<?php
$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

if (isset($_POST) && count($_POST) != 0) {
	$tApi->addSupportMsg($_POST['idProjet'], $_POST['idDoc'], $_POST['msg']);
	wp_schedule_single_event( time() + 1, 'cron_syncProjets', array('incomplete') );
}

$msgs = $tApi->getSupportMsgs($_REQUEST['idProjet'], $_REQUEST['idDoc']);
if (count($msgs["support_messages"]) != 0) {
	foreach ($msgs["support_messages"] as $msg) {
		if ($msg['written_by_you'] == 1) {
			echo '<div class="msg-support-moi">';
			echo 'Moi - '.date_i18n( get_option_tm( 'date_format' ), strtotime( $msg["created_at"]['full'] ) ).'<br/>';
		}
		else if ($msg['written_by_author'] == 1) {
			echo '<div class="msg-support-auteur">';
			echo $msg['author_ref'].' - '.date_i18n( get_option_tm( 'date_format' ), strtotime( $msg["created_at"]['full'] ) ).'<br/>';
		}

		echo $msg['message'];
		echo '</div>';
	}
//	var_dump($msgs);
	echo '<hr/>';
}


echo '<form id="supportTm" action="support_tm.php" method="post">';
echo '<label>Message</label><textarea name="msg" style="width:95%">'.$_REQUEST['msg'].'</textarea>';
echo '<input type="hidden" name="idProjet" value="'.$_REQUEST['idProjet'].'"/>';
echo '<input type="hidden" name="idDoc" value="'.$_REQUEST['idDoc'].'"/>';
echo '<br/><div id="publishing-action"><input name="valideTm" type="submit" class="button button-highlighted" id="valideTm" tabindex="5" accesskey="p" value="'.__('Envoyer','textmaster').'"></div>';

echo '</form>';
?>
</body>
</html>
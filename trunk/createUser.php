<?php
include "../../../wp-load.php";

$results = FALSE;

// créer avec le form + locale wp
if (count($_POST) != 0) {
//	print_r($_POST);
	unset($_POST['Submit']);
	$_POST['locale'] = str_replace('_', '-', get_locale());
	$_POST['group'] = 'clients';
	//$_POST[''] = 'api';
	//print_r($_POST);
	$oOAuth = new TextMaster_OAuth2();
	$results = $oOAuth->createUser(array('user' => $_POST));

//	print_r($results);
}
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
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel='stylesheet' href='<?php echo site_url(); ?>/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=dashicons,admin-bar,wp-admin,buttons,wp-auth-check,wp-pointer&amp;ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='thickbox-css'  href='/wp-includes/js/thickbox/thickbox.css?ver=3.4.2' type='text/css' media='all' />

<link rel='stylesheet' id='colors-css'  href='<?php echo site_url(); ?>/wp-admin/css/colors.min.css?ver=<?php echo $wp_version; ?>' type='text/css' media='all' />
<link rel='stylesheet' id='colors-css'  href='<?php echo plugins_url('textmaster.css' , __FILE__ ) ?>' type='text/css' media='all' />
<!--[if lte IE 7]>
<link rel='stylesheet' id='ie-css'  href='/wp-admin/css/ie.css?ver=3.4.2' type='text/css' media='all' />
<![endif]-->
<style>
html {
	padding:0;
	margin:0;
}
body {
	margin:10px;
}
td {
	margin:0;
	padding:0;
}
</style>

<script type='text/javascript' src='/wp-admin/load-scripts.php?c=0&amp;load%5B%5D=jquery-core,jquery-migrate,utils&amp;ver=3.6'></script>
<script type='text/javascript' src='/wp-admin/load-scripts.php?c=0&amp;load=jquery,utils&amp;ver=3.4.2'></script>
<script type='text/javascript' src='/wp-content/plugins/wp-textmaster/textmaster.js?ver=<?php echo get_tm_plugin_version();?>'></script>
</head>
<body>
<?php echo "<h2>" . __('Créer un compte TextMaster' , 'textmaster') . "</h2>"; ?>
<form name="textmaster_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<?php
if (is_array($results) && key_exists('errors', $results) && count($results['errors']) != 0) {
	echo '<div class="error" style="padding:10px;">'.__('Impossible de créer votre compte, merci de corriger les erreurs.','textmaster').'</div>';
}
else if (is_array($results) && key_exists('authentication_token', $results)) {
	echo '<div class="updated" style="padding:10px;">'.__('Votre compte a bien été créé.','textmaster').'</div>';
	textmaster_api::sendTracker();
}
?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e("Prénom: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[first_name]" value="<?php
if (isset($_POST['contact_information_attributes']['first_name']))
	echo $_POST['contact_information_attributes']['first_name'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['first_name']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['first_name'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Nom: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[last_name]" value="<?php
if (isset($_POST['contact_information_attributes']['last_name']))
	echo $_POST['contact_information_attributes']['last_name'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['last_name']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['last_name'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Société: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[company]" value="<?php
if (isset($_POST['contact_information_attributes']['company']))
	echo $_POST['contact_information_attributes']['company'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['company']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['company'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Adresse: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[address]" value="<?php
if (isset($_POST['contact_information_attributes']['address']))
	echo $_POST['contact_information_attributes']['address'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['address']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['address'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Code Postal: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[zip_code]" value="<?php
if (isset($_POST['contact_information_attributes']['zip_code']))
	echo $_POST['contact_information_attributes']['zip_code'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['zip_code']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['zip_code'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Ville: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[city]" value="<?php
if (isset($_POST['contact_information_attributes']['zip_code']))
	echo $_POST['contact_information_attributes']['city'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['city']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['city'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Pays: ", 'textmaster'); ?></th>

<td><select name="contact_information_attributes[country]" ><?php
if (isset($_POST['contact_information_attributes']['country']))
	$paysselected = $_POST['contact_information_attributes']['country'];
else {
	$paysWp = explode('_',get_locale()) ;
	$paysselected = $paysWp[1];
}

$selected = '';
foreach ($arrayPays as $key => $pays) {
	if ($paysselected == $key)
		$selected = 'selected="selected"';
	else
		$selected = '';
	echo '<option value="'.$key.'" '.$selected.'>'.$pays.'</option>';
}

?>"></select><?php
if (is_array($results) && isset($results['errors']['country']) != 0)
	 echo '<div class="error" style="padding:10px;">'. $results['errors']['country'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Numéro de téléphone: ", 'textmaster'); ?></th>
<td><input type="text" name="contact_information_attributes[phone_number]" value="<?php
if (isset($_POST['contact_information_attributes']['phone_number']))
	echo $_POST['contact_information_attributes']['phone_number'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['phone_number']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['phone_number'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Email: ", 'textmaster'); ?></th>
<td><input type="text" name="email" value="<?php
if (isset($_POST['email']))
	echo $_POST['email'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['email']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['email'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Mot de passe: ", 'textmaster'); ?></th>
<td><input type="password" name="password" value="<?php
if (isset($_POST['password']))
	echo $_POST['password'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['password']) != 0)
	 echo '<div class="error" style="padding:10px;">'. $results['errors']['password'][0].'</div>';
?></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e("Confirmer votre mot de passe: ", 'textmaster'); ?></th>
<td><input type="password" name="confirmation_password" value="<?php
if (isset($_POST['password']))
	echo $_POST['confirmation_password'];
?>" size="50"><br/><?php
if (is_array($results) && isset($results['errors']['confirmation_password']) != 0)
	echo '<div class="error" style="padding:10px;">'. $results['errors']['confirmation_password'][0].'</div>';
?></td>
</tr>
</table>
<br/>
		<br/>
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Créer mon compte','textmaster' ) ?>" />
        </p>
</form>
</body>
</html>

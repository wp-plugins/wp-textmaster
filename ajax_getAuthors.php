<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//require( '../../../wp-load.php' );
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

//include( plugin_dir_path( __FILE__ ). '/textmaster.class.php' );

$arrayRet = array();

if (!isset($_POST['postID']))
	$_POST['postID'] = '';


$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//$tApi->secretapi = get_option_tm('textmaster_api_secret');
//$tApi->keyapi =  get_option_tm('textmaster_api_key');
$arrayProjet = array();
$arrayProjet['ctype'] = '';

if ($_POST['type'] == 'redaction')
	$arrayProjet['ctype'] = 'copywriting';
else if ($_POST['type'] == 'traduction')
	$arrayProjet['ctype'] = 'translation';
else if ($_POST['type'] == 'readproof')
	$arrayProjet['ctype'] = 'proofreading';

if (isset( $_POST['language_from']))
	$arrayProjet['language_from'] = $_POST['language_from'];
if (isset( $_POST['language_to']))
	$arrayProjet['language_to'] = $_POST['language_to'];
if (isset( $_POST['category']))
	$arrayProjet['category'] = $_POST['category'];

$arrayProjet['options']['language_level'] = $_POST['languageLevel'];
$arrayProjet['options']['quality'] = $_POST['quality'];
$arrayProjet['options']['expertise'] = $_POST['expertise'];
$arrayProjet['options']['priority'] = $_POST['priority'];
//$arrayProjet = array();
$arrayAuteurs = $tApi->getAuteurs('',$arrayProjet);
$nbChecked = 0;
if (is_array($arrayAuteurs) && count($arrayAuteurs) != 0) {
	foreach ($arrayAuteurs as $auteurs) {
		if ($auteurs['status'] == 'my_textmaster' || $auteurs['author_id'] == '') {
			$auteurs['checked'] = checkedAuthor($_POST['postID'], $arrayProjet['ctype'], $auteurs['author_id']);
			if ($auteurs['checked'] ==  'true')
				$nbChecked++;

			// si il n'y a que l'auteur 'non definit'
			if (count($arrayAuteurs) == 1 && $auteurs['author_id'] == '')
				$auteurs['checked'] = 'true';

			$auteurs['noCheckBox'] = 'false';
			$arrayRet[] = $auteurs;

		}
	}
}

// si aucun auteur n'est séléctionné on check le non definit
if ($nbChecked == 0 && count($arrayRet) > 1 && $arrayRet[0]['author_id'] == '')
	$arrayRet[0]['checked'] = 'true';

// le message si il n'y a par d'auteur correspondant
if (count($arrayAuteurs) == 1) {
	$auteurs['noCheckBox'] = 'true';
	$auteurs['author_ref'] = __('Aucun de de vos TextMasters ne correspond aux critères du projet','textmaster');
	$arrayRet[] = $auteurs;
}
echo json_encode($arrayRet);


function checkedAuthor($postId, $type,$auteurId){

	$auteurSelected ='';

	if ($type == 'translation') {
		if (get_post_meta($postId, 'textmasterTraductionAuthor', true) != '')
			$auteurSelected = @unserialize( get_post_meta($postId, 'textmasterTraductionAuthor', true));
		else
			$auteurSelected = array(get_option_tm('textmaster_authorTraduction'));
	}
	else if ($type == 'copywriting') {
		if (get_post_meta($postId, 'textmasterAuthor', true) != '')
			$auteurSelected = @unserialize( get_post_meta($postId, 'textmasterAuthor', true));
		else
			$auteurSelected = array(get_option_tm('textmaster_author'));
	}
	else if ($type == 'proofreading') {
		if (get_post_meta($postId, 'textmasterReadProofAuthor', true) != '')
			$auteurSelected = @unserialize( get_post_meta($postId, 'textmasterReadProofAuthor', true));
		else
			$auteurSelected = array(get_option_tm('textmaster_authorReadproof'));
	}

	if (!is_array($auteurSelected))
		$ret = 'false';
	else if ( in_array($auteurId, $auteurSelected) )
		$ret = 'true';
	else
		$ret = 'false';

	return $ret;
}

?>
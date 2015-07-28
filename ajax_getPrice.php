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

$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
//$tApi->secretapi = get_option_tm('textmaster_api_secret');
//$tApi->keyapi =  get_option_tm('textmaster_api_key');

//print_r($_POST);

if (!isset($_POST['quality']))
	$_POST['quality'] = 'false';


$aPrices = $tApi->getPricings($_POST['wordsCount']);

switch ($_POST['type']) {
	case 'redaction':
		$prices = $aPrices['copywriting'];
		break;
	case 'readproof':
		$prices = $aPrices['proofreading'];
		break;
	case 'traduction':
		$prices = $aPrices['translation'];
		break;
	default:
		$prices = false;
} // switch

//print_r($prices);
$arrayJson = array();
if (count($prices) != 0) {
	$arrayJson['price'] = 0;
	foreach ($prices as $price) {
		// le prix de base
		if (isset($_POST['languageLevel']) && $price['name'] == $_POST['languageLevel']) {
			$arrayJson['price'] = $price['value'];
			$arrayJson['priceBase'] = $price['value'];
		}
		// ajout de l'option quality
		if (isset($_POST['languageLevel']) && $price['name'] == 'quality' && $_POST['quality'] == 'true'){ // && $_POST['languageLevel'] == 'premium') {
			$arrayJson['price'] += $price['value'];
			$arrayJson['quality'] = $price['value'];
		} else 	if ($price['name'] == 'quality') {
			$arrayJson['quality'] = 0;
		}

		// ajout de l'option expertise
		if (isset($_POST['expertise']) && $price['name'] == 'expertise' && $_POST['expertise'] == 'true') {
			$arrayJson['price'] += $price['value'];
			$arrayJson['expertise'] = $price['value'];
		} else if ($price['name'] == 'expertise') {
			$arrayJson['expertise'] = 0;
		}
		// ajout de l'option priority
		if (isset($_POST['priority']) &&  $price['name'] == 'priority' && $_POST['priority'] == 'true') {
			$arrayJson['price'] += $price['value'];
			$arrayJson['priority'] = $price['value'];
		} else if ($price['name'] == 'priority') {
			$arrayJson['priority'] = 0;
		}

	}
}

echo json_encode($arrayJson);
?>
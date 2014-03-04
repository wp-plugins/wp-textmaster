<?php
require( '../../../wp-load.php' );
//include( plugin_dir_path( __FILE__ ). '/textmaster.class.php' );

$tApi = new textmaster_api();
$tApi->secretapi = get_option('textmaster_api_secret');
$tApi->keyapi =  get_option('textmaster_api_key');

//print_r($_POST);

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

//print_r($aPrices);
$arrayJson = array();
if (count($prices) != 0) {

	foreach ($prices as $price) {
		// le prix de base
		if (isset($_POST['languageLevel']) && $price['name'] == $_POST['languageLevel']) {
			$arrayJson['price'] = $price['value'];
			$arrayJson['priceBase'] = $price['value'];
		}
		// ajout de l'option quality
		if (isset($_POST['languageLevel']) && $price['name'] == 'quality' && $_POST['quality'] == 'true' && $_POST['languageLevel'] == 'premium') {
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
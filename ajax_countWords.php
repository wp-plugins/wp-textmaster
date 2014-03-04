<?php
require( '../../../wp-load.php' );
//include( plugin_dir_path( __FILE__ ). '/textmaster.class.php' );

$content = $_POST['contenu'];
$contentText = cleanWpTxt( $content );
//	$nbMots = str_word_count($contentText, 0, "àâäéèêëïîöôùüû");
//echo $contentText;
$nbMots = textmaster_api::countWords( $contentText);

echo $nbMots;
?>
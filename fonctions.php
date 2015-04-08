<?php

// pour recuper un contenu texte sans balise HTML ni sortcode Wp
function cleanWpTxt($post_content){
	//	$contentText = strip_shortcodes( $post_content );
	$contentText = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $post_content);  # strip shortcodes, keep shortcode content

	$contentText = stripslashes($contentText);
	$contentText = strip_tags($contentText);
	$contentText = @html_entity_decode($contentText, ENT_COMPAT | ENT_HTML401, 'UTF-8');

	return $contentText;
}

function count_words($string) {

	//$string = htmlentities($string);
	// Return the number of words in a string.
	$string = str_replace("&#039;", "'", $string);
	//	$string = str_replace("&laquo;", '"', $string);
	//	$string = str_replace("&raquo;", '"', $string);
	//	$string = str_replace('&amp;','&',$string);
	$string = str_replace(array('&nbsp;','&#160;'),' ',$string);


	$t= array(' ', "\t", '=', '+', '-', '*', '/', '\\', ',', '.', ';', ':', '[', ']', '{', '}', '(', ')', '<', '>', '&', '%', '$', '@', '#', '^', '!', '?', '~'); // separators
	$string= str_replace($t, " ", $string);
	$string= trim(preg_replace("/\s+/", " ", $string));
	$num= 0;

	$word_array= explode(" ", $string);
	$num= count($word_array);

	return $num;
}

function checkProjetsStatus(){
	global $wpdb;

	$req = 'SELECT count(*) FROM '.$wpdb->prefix.'postmeta WHERE (meta_key="textmasterStatusReadproof" AND meta_value="in_review") OR (meta_key="textmasterStatusTrad" AND meta_value="in_review")';
	$nbInReview = $wpdb->get_var( $req);
	//	$nbInReview = count($results);

	$ret = FALSE;
	$ret['new'] = FALSE;

	$arrayListeTradReads = array();
	$arrayListeRedactions = array();

	$req = 'SELECT * FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%textmasterId%" AND meta_value is not null AND meta_value!=""';
	$tmIds = $wpdb->get_results( $req);
	if (count($tmIds) != 0) {
/*		$tApi = new textmaster_api();
   $tApi->secretapi = get_option_tm('textmaster_api_secret');
   $tApi->keyapi = get_option_tm('textmaster_api_key');
   $projets = $tApi->getProjectList();*/

		//	print_r($projets);
		/*if (count($projets['projects'])) {

		   foreach ($projets['projects'] as $projetInReview) {
		   $aProjetInReview[$projetInReview['id']] = $projetInReview['status'];
		   }*/

		foreach ($tmIds as $tmId) {
			// une trad
			if (strpos($tmId->meta_key, 'Trad') !== FALSE) {
				$satus = get_post_meta($tmId->post_id,'textmasterStatusTrad', TRUE);
				if ($satus != 'canceled' && $satus != 'completed') {
					$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$tmId->meta_value.'"';
					$status = $wpdb->get_var( $req);
					update_post_meta($tmId->post_id, 'textmasterStatusTrad', $status);
					//	update_post_meta($tmId->post_id, 'textmasterStatusTrad', $tApi->getProjetStatus($tmId->meta_value));
				}
			}
			/*	else if (strpos($tmId->meta_key, 'Redaction') !== FALSE) {
			   $satus = get_post_meta($tmId->post_id,'textmasterStatusRedaction', TRUE);
			   if ($satus != 'canceled' && $satus != 'completed') {
			   update_post_meta($tmId->post_id, 'textmasterStatusRedaction', $tApi->getProjetStatus($tmId->meta_value));
			   }
			   }*/
			else {
				$type = get_post_type( $tmId->post_id );
				if ($type == 'textmaster_redaction') {
					$satus = get_post_meta($tmId->post_id,'textmasterStatusRedaction', TRUE);
					if ($satus != 'canceled' && $satus != 'completed') {
						$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$tmId->meta_value.'"';
						$status = $wpdb->get_var( $req);
						update_post_meta($tmId->post_id, 'textmasterStatusRedaction', $status);
						//	update_post_meta($tmId->post_id, 'textmasterStatusRedaction', $tApi->getProjetStatus($tmId->meta_value));
					}
				}
				else
				{
					$satus = get_post_meta($tmId->post_id,'textmasterStatusReadproof', TRUE);
					if ($satus != 'canceled' && $satus != 'completed') {
						$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$tmId->meta_value.'"';
						$status = $wpdb->get_var( $req);
						update_post_meta($tmId->post_id, 'textmasterStatusReadproof', $status);
						//	update_post_meta($tmId->post_id, 'textmasterStatusReadproof', $tApi->getProjetStatus($tmId->meta_value));
					}
				}
			}
			//	}
		}

	}
	$req = 'SELECT * FROM '.$wpdb->prefix.'postmeta WHERE (meta_key="textmasterStatusReadproof" AND meta_value="in_review") OR (meta_key="textmasterStatusTrad" AND meta_value="in_review")';
	$results = $wpdb->get_results( $req);
	if (count($results) != 0)
	{
		$ret['ListeTradReads'] = array();
		foreach ($results as $post) {
			$arrayListeTradReads[] = $post->post_id;
		}
		$ret['ListeTradReads'] = array_unique($arrayListeTradReads);
		$ret['TradRead'] = TRUE;
	}


	$req = 'SELECT * FROM '.$wpdb->prefix.'postmeta WHERE meta_key="textmasterStatusRedaction" AND meta_value="in_review"';
	$results = $wpdb->get_results( $req);
	if (count($results) != 0)
	{
		$ret['ListeRedactions'] = array();
		foreach ($results as $post) {
			$arrayListeRedactions[] = $post->post_id;
		}
		$ret['ListeRedactions'] = array_unique($arrayListeRedactions);
		$ret['Redaction'] = TRUE;
	}

	if ($nbInReview < count($results))
		$ret['new'] = TRUE;

	return $ret;

}

function syncProjets($status = '',$nbPage=null){
	global $wpdb;
/*
   $time = microtime();
   $time = explode(' ', $time);
   $time = $time[1] + $time[0];
   $start = $time;
*/

	//	$wpdb->show_errors();
	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
	//	$tApi->secretapi = get_option_tm('textmaster_api_secret');
	//	$tApi->keyapi = get_option_tm('textmaster_api_key');

	$arryIdsProjets = array();

	$projets = $tApi->getProjectList($status, FALSE, '', NB_SYNC_PROJETS);
//	print_r($projets);
	$totalPage = 0;
	if (isset($projets['count']))
		$totalPage = ($projets['count'] / NB_SYNC_PROJETS) +1;

	if ($nbPage == null)
		$lastPage = 1;
	else
		$lastPage = $totalPage -$nbPage;

	for ($page=$totalPage; $page >= 1; $page--){
		$projets = $tApi->getProjectList($status, FALSE, $page, NB_SYNC_PROJETS);
		//		print_r($projets);
		if (isset($projets['projects']) && count($projets['projects']) != 0) {
			foreach ($projets['projects']  as $projets) {
				$arryIdsProjets[] = $projets['id'];
				$projetInfos = $tApi->getProjetInfos($projets['id']);
				//		print_r($projetInfos);
				$docs = $tApi->getDocumentList($projets['id']);
				if (count($docs['documents']) == 1) {
					$projets['status'] =  $docs['documents'][0]['status'];
					$projets['IdDoc'] = $docs['documents'][0]['id'];
					$projets['completed_at']['full'] = $docs['documents'][0]['completed_at']['full'];
					$projets['archived'] = 0;
					// 	print_r($docs['documents']);

					wpSaveProjet($projets);
				}
				else {
					$projet_name = $projets['name'];
					foreach ($docs['documents'] as $document) {
						$projets['status'] = $document['status'];
						$projets['name'] = $document['title']. ' ('.$projet_name .')';
						$projets['completed_at']['full'] = $document['completed_at']['full'];
						$projets['IdDoc'] = $document['id'];
						$projets['archived'] = 0;

						wpSaveProjet($projets);
					}
				}

			}
		}
	}

	//	print_r($arryIdsProjets);

/*	if (count($arryIdsProjets) != 0 && $status == '') {
   // on met les projetsquinesont pas dans la liste en archivé
   $table_name = $wpdb->base_prefix . "tm_projets";
   $req = 'UPDATE '.$table_name.' SET archived=1 WHERE id NOT IN ("'.implode('", "',$arryIdsProjets ).'") AND id NOT LIKE "%wp_%";';
   //		echo $req;
   $wpdb->query($req);
   //	print_r($req);
   $_SESSION['lastSyncTmProjets'] = time();
   }*/


/*
   $time = microtime();
   $time = explode(' ', $time);
   $time = $time[1] + $time[0];
   $finish = $time;
   $total_time = round(($finish - $start), 4);
   echo 'Page generated in '.$total_time.' seconds.';
*/
}

function syncSupportMsgs(){
	global $wpdb;

	$table_support_messages = $wpdb->base_prefix . 'tm_support_messages';
	$req = $wpdb->prepare('SELECT idProjet, idDocument FROM ' . $table_support_messages. ' GROUP BY idDocument', '');
	$support_messages = $wpdb->get_results($req, ARRAY_A);
//	var_dump($support_messages);
	if (count($support_messages) != 0) {
		$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));
		foreach ($support_messages as $msg) {
			$tApi->getSupportMsgs($msg['idProjet'], $msg['idDocument']);
		}
	}
}
// redaction
// readproof
// traduction
function metaboxes_post(&$tApi, $type, $idProjet, $idDoc, $idPost='', $supportMsgs = array(), $idPostOrigine=''){
	global $post;

	$txtRet = '';
	if ($tApi->secretapi == '' && $tApi->keyapi == '') {
		$txtRet = __('Merci de v&eacute;rifier vos informations de connexion à TextMaster','textmaster');
	}
	else {
//		echo $idProjet;
//		$idProjet = get_post_meta($post->ID,'textmasterIdTrad', TRUE);
//		$idDocument = get_post_meta($post->ID,'textmasterDocumentIdTrad', TRUE);
		$result = $tApi->getDocumentStatus($idProjet, $idDoc);

		$infosDoc = $tApi->getDocumentInfos($idProjet, $idDoc);
//		print_r($infosDoc);
		// le retour client
	//	'neutral' , 'positive' or 'negative'


		// readproof
/*		if ($type == 'readproof') {
			if ($result == 'completed')
				$txtRet .= __('Vous avez d&eacute;j&agrave; valid&eacute; la relecture de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la relecture','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la relecture','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
			else
				$txtRet .= __('Cet article a &eacute;t&eacute; relu.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la relecture','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=readproof&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
		}
*
		// traduction
		if ($type == 'traduction') {
			if (get_option_tm('textmaster_useMultiLangues') == 'Y'){
				if ($result == 'completed')
					$txtRet .= __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Valider la traduction','textmaster').'" onclick="valideTrad('.$post->ID.', '.$idProjet.','.$idDocument.');"></div><div style="clear:both"></div>';
				else{
					$idTrad = get_IdTrad($post->ID, get_post_meta($post->ID, 'textmasterLangOrigine'));
					$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="post.php?post='.$post->ID.'&lang='.	get_post_meta($post->ID, 'textmasterLangDestination', TRUE).'&action=edit;"></div><div style="clear:both"></div>';
				}

			} else {
				if ($result == 'completed')
					$txtRet .= __('Vous avez d&eacute;j&agrave; valid&eacute; la traduction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la traduction','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la traduction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=trad&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
				else
					$txtRet = __('Cet article a &eacute;t&eacute; traduit.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la traduction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=trad&height=500&width=630&TB_iframe=true\');"></div><div style="clear:both"></div>';
			}

		}


		// redaction
		if ($type == 'redaction') {
			if ($result == 'completed')
				$txtRet .= __('Vous avez d&eacute;j&agrave; valid&eacute; la rédaction de cet article.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir la rédaction','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=700&width=630\');"></div><div style="clear:both"></div>';
			else
				$txtRet .= __('Cet article a &eacute;t&eacute; rédigé.','textmaster').'<div id="publishing-action"><input name="Voir" type="button" class="button button-highlighted" id="see" tabindex="5" accesskey="p" value="'.__('Voir / valider','textmaster').'" onclick="tb_show(\''.__('Voir / Valider la rédaction','textmaster').'\',\''. plugins_url('', __FILE__).'/approuve_doc.php?post_id='.$post->ID.'&type=redaction&height=700&width=630\');"></div><div style="clear:both"></div>';

		}*/

		if ($idPost == '')
			$idPost = $post->ID;

		$width = '99%';
		if ($type == 'traduction')
			$width= '235px';


		$txtRet .= '<div id="meta_trad" >';
		$satisfactions = $tApi->getSatisfactions();
		$txtRet .= '<label>'.__('Noter l\'auteur :','textmaster').'</label><br/>';
		$txtRet .= '<select id="select_textmasterSatisfaction" name="select_textmasterSatisfaction" style="width:'.$width.'">';
		$satisfactionSelected = '';
		if (get_post_meta($idPost, 'textmasterSatisfaction', true) != '')
			$satisfactionSelected = get_post_meta($post->ID, 'textmasterSatisfaction', true);

		if ($satisfactionSelected == '')
			$satisfactionSelected = 'neutral';


		foreach($satisfactions as $key => $satisfaction)
		{
			if ($satisfactionSelected == $key)
				$txtRet .= '<option value="'.$key.'" selected="selected">'.$satisfaction.'</option>';
			else
				$txtRet .= '<option value="'.$key.'">'.$satisfaction.'</option>';
		}
		$txtRet .= '</select><br/><br/>';

		$txtRet .= '<label>'.__('Message facultatif à destination des auteurs :','textmaster').'</label><br/>';
		$txtRet .= '<textarea style="width:'.$width.';height:100px;" name="text_textmaster_message" id="text_textmaster_message">'.get_post_meta($idPost, 'textmasterAuthorMessage', true).'</textarea><br/>';
		$txtRet .=  '<input type="hidden" id="postID" name="postID" value="'. $idPost.'">';
		$txtRet .=  '<input type="hidden" id="docId" name="docId" value="'. $idDoc.'">';
		$txtRet .=  '<input type="hidden" id="ProjetId" name="ProjetId" value="'. $idProjet.'">';
		$txtRet .=  '<input type="hidden" id="valider" name="valider" value="oui">';
		$txtRet .=  '<input type="checkbox" id="textmaster_add_author" name="textmaster_add_author" value="Y" />';
     	$txtRet .= __("Ajouter à mes auteurs favoris ou ma liste noire",'textmaster' );
		$txtRet .= '<br/><div id="tm_add_auteur" style="display:none;">';
		$txtRet .= '<label>'.__('Description:','textmaster').'</label><br/>';
		$txtRet .=  '<input type="text" id="auteur_description" name="auteur_description" value="" style="width:'.$width.';"><br/>';
		$txtRet .= '<label>'.__('Statut :','textmaster').'</label><br/>';
		$txtRet .= '<select id="select_textmasterStatutAuteur" name="select_textmasterStatutAuteur" style="width:'.$width.';">';
		$txtRet .= '<option value="my_textmaster">'.__('Mes textmaster','textmaster').'</option>';
		$txtRet .= '<option value="blacklisted">'.__('Ma liste noire','textmaster').'</option>';
//		$txtRet .= '<option value="uncategorized">'.__('Uncategorized','textmaster').'</option>';
		$txtRet .= '</select>';
		$txtRet .=  '<input type="hidden" id="auteurTmId" name="auteurTmId" value="'. $infosDoc["author_id"].'">';
		$txtRet .= '</div>';
		// traduction
		if ($type == 'traduction') {
			$txtRet .= '<img src="/wp-admin/images/wpspin_light.gif" style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-validate" alt=""><div style="display:none;float:left;margin-top:5px;margin-right:5px;" class="ajax-loading-validate"> '.__('Merci de patienter', 'textmaster').'</div> <div id="resultTextmasterValide" class="misc-pub-section"></div>';

			if ($infosDoc['status'] != 'incomplete')
				$txtRet .= '<br/><div id="publishing-action"><input name="valideTm" type="button" class="button button-highlighted" id="valideTm" tabindex="5" accesskey="p" value="'.__('Valider','textmaster').'"></div> ';

			if ($infosDoc['status'] == 'in_review' && count($supportMsgs["support_messages"]) != 0)
				$txtRet .= '<div id="publishing-action"><input name="valideRevTm" type="button" class="button button-highlighted" id="valideRevTm" tabindex="5" accesskey="p" value="'.__('Voir la révision','textmaster').'" onclick="valideTrad(\''. $idPostOrigine.'\', \''. $idProjet.'\', \''. $idDoc.'\', true, \''.__('Merci de patienter...', 'textmaster').'\');jQuery(\'.ajax-loading-validate\').show();"></div> ';
	//	 var_dump($supportMsgs);
			if (count($supportMsgs["support_messages"]) != 0)
				$txtRet .= '<br/><br/><div id="publishing-action"><input name="supportTM" type="button" class="button button-highlighted" id="supportTM" tabindex="5" accesskey="p" value="'.__('Discussion avec le rédacteur','textmaster').'"></div>';
			else
				$txtRet .= '<br/><br/><div id="publishing-action"><input name="supportTM" type="button" class="button button-highlighted" id="supportTM" tabindex="5" accesskey="p" value="'.__('Demander une révision','textmaster').'"></div>';
		}
		$txtRet .= '<div style="clear:both"></div><br/>';
		$txtRet .= '</div>';
	};

	echo $txtRet;
}


function get_IdTrad($id_origine,$lang, $type='post', $table=''){
	global $wpdb;

	$idTrad = '';

	if ($table == '') {
		$tablePost =$wpdb->postmeta ;
		$tableOption =$wpdb->options ;
	}else{
		$tablePost =$table ;
		$tableOption =$table ;
	}

	if ($type == 'post')
		$idTrad = $wpdb->get_var( "select post_id from $tablePost where meta_key = 'tm_lang' AND meta_value LIKE '$id_origine;$lang'" );
	else
		$idTrad = $wpdb->get_var( 'select option_value from '.$tableOption.' where option_name=CONCAT("taxonomy_", "'.$id_origine.'") AND option_value LIKE "%'.$lang.'=%"' );
	//	echo 'select option_value from '.$wpdb->options.' where option_name=CONCAT("taxonomy_", "'.$id_origine.'") AND option_value LIKE "%'.$lang.'=%"';
	//	echo "select post_id from $tablePost where meta_key = 'tm_lang' AND meta_value LIKE '$id_origine;$lang'";
	return $idTrad;
}

function get_IdProjetTrad($id_origine,$lang, $type='post', $table='' ){
	global $wpdb;

	if ($table == '') {
		$tablePost =$wpdb->postmeta ;
		$tableOption =$wpdb->options ;
	}else{
		$tablePost =$table ;
		$tableOption =$table ;
	}

	if ($type == 'post')
		$idProjet = $wpdb->get_var( "select meta_value from $tablePost where meta_key = 'textmasterIdTrad' AND post_id='$id_origine' AND meta_value LIKE '%=$lang%'" );
	else
		$idProjet = $wpdb->get_var( "select option_value from $tableOption where option_name = 'tmProjet_$id_origine'" );
	//	echo "select option_value from $wpdb->options where option_name = 'tmProjet_$id_origine'";
	//	echo "select meta_value from $wpdb->postmeta where meta_key = 'textmasterIdTrad' AND post_id='$id_origine' AND meta_value LIKE '%=$lang%'";


	if (strpos($idProjet, ';') !== FALSE) {
		$ids = explode(';', $idProjet);
		foreach ($ids as $id) {
			if ($lang != '' && strpos($id, $lang) !== FALSE) {
				$idTm = explode('=', $id);
				$idProjet = $idTm[0];
			}
		}
	}else {

		if ($lang != '' && strpos($idProjet, $lang) !== FALSE) {
			$idTm = explode('=', $idProjet);
			$idProjet = $idTm[0];
		}
		else if ($lang == '') {
			$idTm = explode('=', $idProjet);
			$idProjet = $idTm[0];
		}
		else
			$idProjet = '';
	}
/*	else
   $idProjet = '';
*/

	//	echo "select post_id from $wpdb->postmeta where meta_key = 'tm_lang' AND meta_value = '$id_origine;$lang'";
	return $idProjet;
}

function get_IdDocTrad($id_origine,$lang, $type='post', $table=''){
	global $wpdb;

	$idProjet = '';

	if ($table == '') {
		$tablePost =$wpdb->postmeta ;
		$tableOption =$wpdb->options ;
	}else{
		$tablePost =$table ;
		$tableOption =$table ;
	}

	if ($type == 'post')
		$idProjet = $wpdb->get_var( "select meta_value from $tablePost where meta_key = 'textmasterDocumentIdTrad' AND post_id='$id_origine' AND meta_value LIKE '%=$lang%'" );
	else
		$idProjet = $wpdb->get_var( "select option_value from $tableOption where option_name = 'tmDoc_$id_origine'" );

	if (strpos($idProjet, ';') !== FALSE) {
		$ids = explode(';', $idProjet);
		foreach ($ids as $id) {
			if ($lang != '' && strpos($id, $lang) !== FALSE) {
				$idTm = explode('=', $id);
				$idProjet = $idTm[0];
			}

		}
	}else {
		if ($lang != '' && strpos($idProjet, $lang) !== FALSE) {
			$idTm = explode('=', $idProjet);
			$idProjet = $idTm[0];
			//	echo '<br/>'.$lang.' / '.$idProjet;
		}
		else if ($lang == '') {
			$idTm = explode('=', $idProjet);
			$idProjet = $idTm[0];
		}
		else
			$idProjet = '';
	}


	//	echo "select post_id from $wpdb->postmeta where meta_key = 'textmasterDocumentIdTrad' AND post_id='$id_origine' AND meta_value LIKE '%=$lang%'";
	return $idProjet;
}


function approveTrad(&$tApi, $post_id, $projectId, $docId, $valid = TRUE, $idSite = ''){
	global $wpdb;

	$text = '';

	$post_origine = get_post($post_id);
	$projet_infos = $tApi->getProjetInfos($projectId);

	if ($idSite != '' && is_multisite()) {
		switch_to_blog( $idSite );
		$table_name = $wpdb->base_prefix . $idSite."_posts";
		$table_meta = $wpdb->base_prefix . $idSite."_postmeta";
	}else{
		$table_name = $wpdb->prefix . "posts";
		$table_meta = $wpdb->prefix . "postmeta";
	}

	// on vverif si il y a déjà une trad existante
	$idTrad = get_IdTrad($post_id, $projet_infos['language_to'], 'post', $table_name);

	$infos = $tApi->getDocumentInfos($projectId, $docId);
//	print_r($infos);
//	die();
	// on valide chez textmaster
	if ($tApi->getDocumentStatus($projectId, $docId) != 'completed' && $valid )
		$ret = $tApi->valideDoc($projectId, $docId);
	//	print_r($ret);

	// on créer un article avec le contenu
	if (is_array($infos) && key_exists('documents', $infos))
		$work = $infos['documents'][0];
	else
		$work = $infos;

	if (isset( $work['author_work']['title']) && $work['author_work']['title'] != '')
		$new_post['post_title'] = $work['author_work']['title'];
	else  if (isset($work['title']) && $infos['title'] != '')
		$new_post['post_title'] = $work['title'];

	if (is_array($work['author_work']) && count($work['author_work']) != 0) {
		// acf
		$extras = array();
		if( checkInstalledPlugin('Advanced Custom Fields')) {
			$contentFound = FALSE;
			$text = '';
			//	var_dump($work['author_work']);
			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'post_excerpt'){
					$new_post['post_excerpt'] = $work['post_excerpt'];
				} else if ($element == 'content'){
					$text .= '<p>'.nl2br($paragraphes).'</p>';
					$contentFound = TRUE;
				}
				else {
					$field = $wpdb->get_var( "SELECT meta_key FROM $table_meta WHERE meta_value = '".$element."'");
					$extras[substr($field,1)]['val'] = $paragraphes;
					$extras[substr($field,1)]['field'] = $element;
				}

			}

			if (!$contentFound) {
				foreach ( $work['author_work'] as $element => $paragraphes) {
					if ($element == 'post_excerpt'){
						$new_post['post_excerpt'] = $work['post_excerpt'];
					} else 	if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}
			}
		}
		if( checkInstalledPlugin('Meta Box')) {
			$contentFound = FALSE;
			$text = '';
			//	var_dump($work['author_work']);
			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'post_excerpt'){
					$new_post['post_excerpt'] = $work['post_excerpt'];
				} else 	if ($element == 'content'){
					$text .= '<p>'.nl2br($paragraphes).'</p>';
					$contentFound = TRUE;
				}
				else {
					$extras[$element]['val'] = $paragraphes;
					$extras[$element]['field'] = $element;
				}
			}

			if (!$contentFound) {
				foreach ( $work['author_work'] as $element => $paragraphes) {
					if ($element == 'post_excerpt'){
						$new_post['post_excerpt'] = $work['post_excerpt'];
					} else 	if ($element != 'title')
						$text .= '<p>'.nl2br($paragraphes).'</p>';
				}
			}
		}
		if( !checkInstalledPlugin('Advanced Custom Fields') && !checkInstalledPlugin('Meta Box')) {

			foreach ( $work['author_work'] as $element => $paragraphes) {
				if ($element == 'post_excerpt'){
					$new_post['post_excerpt'] = $work['post_excerpt'];
				} else 	if ($element != 'title')
					$text .= '<p>'.nl2br($paragraphes).'</p>';
			}
		}

		$new_post['post_content'] = $text;

		$new_post['post_type'] = $post_origine->post_type;
		$new_post['post_status'] = 'draft';

		if ($idTrad == '') {
			$post_id_new = wp_insert_post($new_post);
			$tm_lang = $post_id.';'.$projet_infos['language_to'];
			update_post_meta($post_id_new, 'tm_lang', $tm_lang);
			$post_id = $post_id_new;
		}else {
			$new_post['ID'] = $idTrad;
			wp_update_post( $new_post );
			$post_id = $idTrad;
		}

		if( checkInstalledPlugin('Advanced Custom Fields') && count($extras) != 0) {
			foreach ($extras as $key => $extra) {
				$post_id_base = $wpdb->get_var( "SELECT post_id FROM $table_meta WHERE meta_key = '".$extra['field']."'");
				$idT = get_IdTrad($post_id_base,$projet_infos['language_to']);
				$fieldTrad = $wpdb->get_var( "SELECT meta_key FROM $table_meta WHERE post_id = '".$idT."' AND meta_value LIKE '%key%field_%name%".$key."%type%' AND meta_key LIKE 'field_%'");

				update_post_meta( $post_id, '_'.$key, $fieldTrad );
				update_post_meta( $post_id, $key, $extra['val'] );
			}
		}
		if( checkInstalledPlugin('Meta Box') && count($extras) != 0) {
			$aTextF = array();
			foreach ($extras as $key => $extra) {
				if (strpos($extra['field'], '_tmtext_') !== FALSE) {
					$num = str_replace('_tmtext_', '', $extra['field']);
					$aTextF[$num] = $extra['val'];
					update_post_meta( $post_id, $extra['field'], $aTextF);
				}else
					update_post_meta( $post_id, $extra['field'], $extra['val'] );
			}

		}
	}else {
		$post_id = FALSE;
	}

	if ($idSite != ''&& is_multisite())
		restore_current_blog();

	return $post_id;
}

function approveTradTag(&$tApi, $t_id, $projectId, $docId, $valid = TRUE){
	$text ='';

	$projet_infos = $tApi->getProjetInfos($projectId);
	//	print_r($projet_infos);
	// on vverif si il y a déjà une trad existante
	$idTrad = get_IdTrad($t_id, $projet_infos['language_to']);
	if ($docId != '') {
		$infos = $tApi->getDocumentInfos($projectId, $docId);
		//print_r($infos);
		if (is_array($infos)) {
			// on valide chez textmaster
			if ($tApi->getDocumentStatus($projectId, $docId) != 'completed' && $valid )
				$ret = $tApi->valideDoc($projectId, $docId);
			//		print_r($ret);

			if (is_array($infos) && key_exists('documents', $infos))
				$work = $infos['documents'][0];
			else
				$work = $infos;

			if (is_array($work['author_work']) && count($work['author_work']) != 0) {
				foreach ( $work['author_work'] as $element => $paragraphes) {
					if ($element != 'title')
						$text .= nl2br($paragraphes);
				}
			}
			$str_meta = get_option_tm( "taxonomy_$t_id");
			if ($str_meta != '')
				$str_meta .= $projet_infos['language_to'].'='.$text.';';
			else
				$str_meta = $projet_infos['language_to'].'='.$text.';';

			update_option_tm( "taxonomy_$t_id", $str_meta );
		}
		else
			$t_id = FALSE;
	}else
		$t_id = FALSE;

	return $t_id;
}

function getExtrasFields($postId, $type="acf", $site = ''){
	global $wpdb;

	if ($site != '' && $site > 1)
		$table_name = $wpdb->base_prefix . $site. "_postmeta";
	else
		$table_name = $wpdb->postmeta;


	$datas = array();

	if ($type == 'metabox') {
		$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
		$chk_tm_mb_feilds = unserialize(get_option_tm('chk_tm_mb_feilds'));
//		var_dump($meta_boxes);
		if (count($meta_boxes) != 0) {
			foreach ($meta_boxes as $meta_box) {
				if (count($meta_box['fields']) != 0) {
					foreach ($meta_box['fields'] as $meta_box_fields) {
						if (is_array($chk_tm_mb_feilds) && !in_array($meta_box_fields['id'], $chk_tm_mb_feilds)){
							$meta_value = get_post_meta( $postId, $meta_box_fields['id'], TRUE );
						//	var_dump($meta_value);
							if (is_string($meta_value) && trim($meta_value) != ''){

									$datas[$meta_box_fields['id']] = $meta_value;

							}else {
								$texts = $meta_value;
								//var_dump($texts);
								if (count($texts) != 0 && is_array($texts)) {
									foreach ($texts as $key => $text) {
										$datas[$meta_box_fields['id'].'_tmtext_'.$key] = $text;
									}
								}
							}
						}

					}
				}
			}
		}
	}else {
		$extras = $wpdb->get_results("SELECT meta_key, meta_value FROM $table_name WHERE meta_value LIKE 'field_%' AND post_id = $postId"	);
		if ( $extras ){
			foreach ( $extras as $extra ){
				//	var_dump($extra);
				//	echo'-----';
				$infosField = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE meta_key = '".$extra->meta_value ."'");
				//	echo "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '".$extra->meta_value ."'";
				$aInfosField = @unserialize($infosField);

				if ($aInfosField['type'] == 'textarea' || $aInfosField['type'] == 'text' || $aInfosField['type'] == 'wysiwyg')
					$datas[$extra->meta_value] = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE meta_key = '".substr($extra->meta_key,1)."' AND post_id = $postId");

			}
		}
	}

//	var_dump($datas);

	return $datas;
}

function get_option_tm($key, $default = false){
	if ( is_multisite() ) {
		$ret = get_site_option( $key, $default);
	}else {
		$ret = get_option($key, $default);
	}
	return $ret;
}

function update_option_tm($option, $value ){
	if ( is_multisite() ) {
		$ret = update_site_option($option, $value );
	}else {
		$ret = update_option($option, $value );
	}
	return $ret;
}

function get_post_status_tm($id, $idSite=''){
	global $wpdb;

	if ( !is_multisite() ) {
		$ret = get_post_status($id);
	}else {
		if ($idSite != '')
			$table_name = $wpdb->base_prefix . $idSite."_posts";
		else
			$table_name = $wpdb->base_prefix ."posts";
		$ret = $wpdb->get_var('SELECT post_status FROM '.$table_name.' WHERE ID='.$id);

	}
	return $ret;
}
function get_the_title_tm($id, $idSite=''){
	global $wpdb;

	if ( !is_multisite() ) {
		$ret = get_the_title($id);
	}else {
		if ($idSite != '')
			$table_name = $wpdb->base_prefix . $idSite."_posts";
		else
			$table_name = $wpdb->base_prefix . "posts";
		$ret = $wpdb->get_var('SELECT post_title FROM '.$table_name.' WHERE ID='.$id);

	}
	return $ret;
}

function get_post_meta_tm($post, $key, $single=TRUE, $idSite=''){
	global $wpdb;

	if ( !is_multisite() ) {
		$meta_value = get_post_meta($post, $key, $single);
	}else {
		if ($idSite != '')
			$table_name = $wpdb->base_prefix . $idSite.'_postmeta';
		else
			$table_name = $wpdb->base_prefix . 'postmeta';

		$query = "SELECT meta_value FROM {$table_name} WHERE post_id ='{$post}' AND meta_key='{$key}'";
//		echo $query;
		$meta_value = $wpdb->get_var($query);
	}

	return $meta_value;
}

function checkInstalledPlugin($plugin_name){
	$ret = FALSE;

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$all_plugins = get_plugins();
	if (count($all_plugins) != 0) {
		foreach ($all_plugins as $plugin) {
			if ($plugin['Name'] == $plugin_name)
				$ret = TRUE;
		}
	}

	return $ret;
}

function checkActivatedPlugin($plugin_name){
	$ret = FALSE;

	if ( !is_multisite() ) {
		if ( ! function_exists( 'is_plugin_active' ) )
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( $plugin_name ) )
			$ret = TRUE;
	}else {
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		if ( is_plugin_active_for_network( $plugin_name ) )
			$ret = TRUE;
	}

	return $ret;
}

/* pour les wordress < 3.7 */
if (!function_exists('wp_get_sites')) {
	function wp_get_sites( $args = array() ) {
		global $wpdb;

		if ( wp_is_large_network() )
			return array();

		$defaults = array(
		    'network_id' => $wpdb->siteid,
		    'public'     => null,
		    'archived'   => null,
		    'mature'     => null,
		    'spam'       => null,
		    'deleted'    => null,
		    'limit'      => 100,
		    'offset'     => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT * FROM $wpdb->blogs WHERE 1=1 ";

		if ( isset( $args['network_id'] ) && ( is_array( $args['network_id'] ) || is_numeric( $args['network_id'] ) ) ) {
			$network_ids = implode( ',', wp_parse_id_list( $args['network_id'] ) );
			$query .= "AND site_id IN ($network_ids) ";
		}

		if ( isset( $args['public'] ) )
			$query .= $wpdb->prepare( "AND public = %d ", $args['public'] );

		if ( isset( $args['archived'] ) )
			$query .= $wpdb->prepare( "AND archived = %d ", $args['archived'] );

		if ( isset( $args['mature'] ) )
			$query .= $wpdb->prepare( "AND mature = %d ", $args['mature'] );

		if ( isset( $args['spam'] ) )
			$query .= $wpdb->prepare( "AND spam = %d ", $args['spam'] );

		if ( isset( $args['deleted'] ) )
			$query .= $wpdb->prepare( "AND deleted = %d ", $args['deleted'] );

		if ( isset( $args['limit'] ) && $args['limit'] ) {
			if ( isset( $args['offset'] ) && $args['offset'] )
				$query .= $wpdb->prepare( "LIMIT %d , %d ", $args['offset'], $args['limit'] );
			else
				$query .= $wpdb->prepare( "LIMIT %d ", $args['limit'] );
		}

		$site_results = $wpdb->get_results( $query, ARRAY_A );

		return $site_results;
	}
}

function logDatas($data){
	$file = plugin_dir_path( __FILE__).'log.txt';
	@file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
}
?>
<?php
//define('WP_DEBUG', true);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
$_SESSION['lastSyncTmProjets'] = 0;
function admin_textmaster_projets(){
	global $wpdb;

	// affichage de la liste des projets
	$listeProjets = new textmaster_projets_Table();
    $listeProjets->prepare_items();

	$html = '';
	// le filtre par site pour le network
	if ( is_network_admin() ) {
		if (function_exists('wp_get_sites')) {
			$sites = wp_get_sites();
			if (count($sites) != 0) {
				$html .= '<select name="tmsite" onChange="jQuery(\'#tmsite\').val(jQuery(this).val());document.location.href=\''. network_admin_url('admin.php?page=Textmaster&tmsite=').'\'+jQuery(this).val()+\'&tmt_type=\'+jQuery(\'#tmt\').val();">';//jQuery(\'#formTMProjets\').submit();
				foreach ($sites as $site) {
					$detail = get_blog_details($site['blog_id']);
					$html .= '<option value="'.$site['blog_id'].'" ';
					if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] == $site['blog_id'])
						$html .= 'selected="selected"';
					$html .= '>'. $detail->blogname .'</option>';
				}
				$html .= '</select>';
			}
		}


		$urlTmt = network_admin_url('admin.php?page=Textmaster&tmt=');
		$urlTmt_type = network_admin_url('admin.php?page=Textmaster&tmt_type=');
	}else{
		$urlTmt = admin_url('edit.php?post_type=textmaster_redaction&page=textmaster-projets&tmt=');
		$urlTmt_type = admin_url('edit.php?post_type=textmaster_redaction&page=textmaster-projets&tmt=translation&tmt_type=');
	}
	?>

			<div class="wrap" id="textmaster_projets">
				<?php echo "<div id='icon-edit' class='icon32 icon32-posts-textmaster_redaction'><br></div><h2>" . __('Tous les projets TextMaster' , 'textmaster') .' '. $html ."</h2>"; ?>

			<form method="post" id="formTMProjets">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
			<input type="hidden" name="tms" value="<?php echo $_REQUEST['tms']; ?>"/>
			<input type="hidden" name="tmt" value="<?php echo $_REQUEST['tmt']; ?>"/>
			<input type="hidden" name="tmc" value="<?php echo $_REQUEST['tmc']; ?>"/>
			<input type="hidden" name="tmsite" value="<?php echo $_REQUEST['tmsite']; ?>"/>
			<?php
				$listeProjets->display();

			?>
				</form>
				</div>
			<?php
}

class textmaster_projets_Table extends WP_List_Table {

	function __construct() {
       	add_action('admin_head', array(&$this, 'admin_header'));
		parent::__construct( array(
			'singular'  => __('projet'),
			'plural'    => __('projets'),
			'ajax'      => false
		) );
	}

	function admin_header()
	{
		$page = false;
		if (isset($_GET['page'])) {
			$page = esc_attr($_GET['page']) ;
		}

		if ('textmaster-projets' == $page)
		{
			echo '<style type="text/css">';
			echo '.wp-list-table .column-Nom { width: 30%; }';
			echo '.wp-list-table .column-ctype { width: 20%; }';
			echo '.wp-list-table .column-category { width: 20%; }';
			echo '.wp-list-table .column-status { width: 10%; }';
			echo '.wp-list-table .column-created_at { width: 10%; }';
			echo '.wp-list-table .column-completed_at { width: 10%; }';
			echo '</style>';
		}
	}

	function extra_tablenav( $which ) {
				global $wpdb;

		if ( $which == "top" ){

			// le filtre par type
			?><select name="tmt" onChange="jQuery('#formTMProjets').submit();">
			 <option value=""><?php _e('Voir tous les type de projet', 'textmaster'); ?></option>
			 <option value="translation" <?php if ($_REQUEST['tmt'] == 'translation') echo 'selected="selected"'; ?>><?php _e('translation', 'textmaster'); ?></option>
			 <option value="copywriting" <?php if ($_REQUEST['tmt'] == 'copywriting') echo 'selected="selected"'; ?>><?php _e('copywriting', 'textmaster'); ?></option>
			 <option value="proofreading" <?php if ($_REQUEST['tmt'] == 'proofreading') echo 'selected="selected"'; ?>><?php _e('proofreading', 'textmaster'); ?></option>
			</select>
			<?php

			// le filtre par status
			?><select name="tms" onChange="jQuery('#formTMProjets').submit();">
			 <option value=""><?php _e('Voir tous les status TextMaster', 'textmaster'); ?></option>
			 <option value="waiting_assignment" <?php if ($_REQUEST['tms'] == 'waiting_assignment') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('waiting_assignment'); ?></option>
			 <option value="in_creation" <?php if ($_REQUEST['tms'] == 'in_creation') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('in_creation'); ?></option>
			 <option value="in_progress" <?php if ($_REQUEST['tms'] == 'in_progress') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('in_progress'); ?></option>
			 <option value="in_review" <?php if ($_REQUEST['tms'] == 'in_review') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('in_review'); ?></option>
			 <option value="paused" <?php if ($_REQUEST['tms'] == 'paused') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('paused'); ?></option>
			 <option value="canceled" <?php if ($_REQUEST['tms'] == 'canceled') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('canceled'); ?></option>
			 <option value="completed" <?php if ($_REQUEST['tms'] == 'completed') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('completed'); ?></option>
			 <option value="incomplete" <?php if ($_REQUEST['tms'] == 'incomplete') echo 'selected="selected"'; ?>><?php echo textmaster_api::getLibStatus('incomplete'); ?></option>
			</select>
			<?php

			// le filtre par catégories
			$table_categories =  $wpdb->base_prefix .'tm_categories';
			$reqCats = 'SELECT * FROM '.$table_categories;
			$arrayCats = $wpdb->get_results($reqCats, ARRAY_A);
			if (count($arrayCats) != 0) {
				echo '<select name="tmc" onChange="jQuery(\'#formTMProjets\').submit();">';
			 	echo '<option value="">'. __('Voir toutes les catégories', 'textmaster').'</option>';
				foreach ($arrayCats as $cat) {
					$selected = '';
					if ($_REQUEST['tmc'] == $cat['code']) {
						$selected = 'selected="selected"';
					}
					echo '<option value="'.$cat['code'].'"' .$selected .'>'.$cat['value'].'</option>';
				}
				echo '</select>';
			}
		}
	}

	function get_columns()
	{
		$columns = array(
		    'name' => __('Nom', 'textmaster'),
		    'ctype' => __('Type', 'textmaster'),
	//	    'language_from' => __('Langue d\'origine','textmaster'),
	//	    'language_to' => __('Langue de destination','textmaster'),
		    'category' => __('Category', 'textmaster'),
		    'status' => __('Status', 'textmaster'),
		    'created_at' => __('Créé le', 'textmaster'),
	//	    'launched_at' => __('Démarré le', 'textmaster'),
		    'completed_at' => __('Terminé le', 'textmaster')
		    );
		return $columns;
	}

	function get_sortable_columns()
	{

		$sortable_columns = array(
		    'name' => array('name', false),
		//	'ctype' =>  array('ctype', false),
		//	'status' =>  array('status', false),
			'created_at' =>  array('created_at', true),
	//		'launched_at' =>  array('launched_at', false),
			'completed_at' =>  array('completed_at', false)
		    );
		return $sortable_columns;

	}

	function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'name':
			case 'ctype':

		//	case 'language_from':
		//	case 'language_to':
			case 'category':
			case 'status':
				return $item[ $column_name ];
			case 'created_at':
				if ($item[ $column_name ]  != ''&& $item[ $column_name ]  != '0000-00-00 00:00:00')
					return date_i18n(get_option_tm('date_format') ,strtotime($item[ $column_name ]));
				else
					return 'NC';
			/*case 'launched_at':
				if ($item[ $column_name ]  != '')
					return date_i18n(get_option_tm('date_format') ,strtotime($item[ $column_name ]));
				else
					return 'NC';*/
			case 'completed_at':
				if ($item[ $column_name ]  != '' && $item[ $column_name ]  != '0000-00-00 00:00:00')
					return date_i18n(get_option_tm('date_format') ,strtotime($item[ $column_name ]));
				else
					return 'NC';
			default:
				return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	function prepare_items(){
		global $wpdb;

/*		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;
*/
		$table_name = $wpdb->base_prefix . "tm_projets";
		$per_page = 40;
		$adminUrl ='';
		if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1) {
			$table_post = $wpdb->base_prefix . $_REQUEST['tmsite']."_posts";
			$table_meta = $wpdb->base_prefix . $_REQUEST['tmsite']."_postmeta";
			if ( is_network_admin() ){
				$detail = get_blog_details($_REQUEST['tmsite']);
				$adminUrl = $detail->siteurl.'/wp-admin/';
			}
		}else {
			if ( is_network_admin())
				$_REQUEST['tmsite'] = 1;
			$table_post = $wpdb->prefix . "posts";
			$table_meta = $wpdb->prefix . "postmeta";
			if ( is_network_admin() && isset($_REQUEST['tmsite'])){
				$detail = get_blog_details($_REQUEST['tmsite']);
				$adminUrl = $detail->siteurl.'/wp-admin/';
			}
			else
				$adminUrl ='';
		}
	/*	$tApi = new textmaster_api();
		$tApi->secretapi = get_option_tm('textmaster_api_secret');
		$tApi->keyapi = get_option_tm('textmaster_api_key');

		$projets = $tApi->getProjectList($_REQUEST['tms'], FALSE, '', 9999999);
		$total_items = count($projets['projects']);

		$projets = $tApi->getProjectList($_REQUEST['tms'], FALSE, $_REQUEST['paged'], $per_page );*/
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
		$orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'created_at';
		$order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

		$where = '';
		if (isset($_REQUEST['tms']) && $_REQUEST['tms'] != '')
			$where .= ' AND status="'.$_REQUEST['tms'] .'"';
		if (isset($_REQUEST['tmt']) && $_REQUEST['tmt'] != '')
			$where .= ' AND ctype="'.$_REQUEST['tmt'] .'"';
		if (isset($_REQUEST['tmc']) && $_REQUEST['tmc'] != '')
			$where .= ' AND category="'.$_REQUEST['tmc'] .'"';


		$total_items = $wpdb->get_var('SELECT COUNT(id) FROM  ' . $table_name . '
						LEFT JOIN  '.$table_meta.' ON meta_key LIKE "%textmasterDocumentId%" AND meta_value=' . $table_name . '.idDocument
						 WHERE ( creation_channel="api" OR id LIKE "wp_%%") AND archived!=1 AND name NOT LIKE "%(junk)%" '.$where);



	//	$req = $wpdb->prepare('SELECT * FROM ' . $table_name . '
	//							LEFT JOIN  '.$wpdb->prefix.'postmeta ON meta_key LIKE "%textmasterDocumentId%" AND meta_value=' . $table_name . '.idDocument
	//							WHERE (creation_channel="api" OR id LIKE "wp_%%")  AND name NOT LIKE "%(junk)%" AND archived!=1 '.$where.' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d, %d', ($paged * $per_page), $per_page);

		$req = 'SELECT * FROM ' . $table_name . '
								LEFT JOIN  '.$table_meta.' ON meta_key LIKE "%textmasterDocumentId%" AND meta_value=' . $table_name . '.idDocument
								WHERE (creation_channel="api" OR id LIKE "wp_%%") AND archived!=1 AND name NOT LIKE "%(junk)%" '.$where.' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT '.($paged * $per_page).', '. $per_page.'';

	//	$req = $wpdb->prepare('SELECT * FROM ' . $table_name . ' WHERE creation_channel="api" '.$where.' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT %d, %d', ($paged * $per_page), $per_page);
//		echo 'req '.$req;
		//(id IN (SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%%textmasterId%%" AND meta_value is not null) AND
		//(id IN (SELECT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%textmasterId%" AND meta_value is not null) AND
		$projetsTM = $wpdb->get_results( $req, ARRAY_A);

//		print_r($projetsTM);

		/*$req = 'SELECT * FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%textmasterId%" AND meta_value is not null AND meta_value!=""';
		$tmIds = $wpdb->get_results( $req);*/

		$textmaster_email = get_option_tm('textmaster_email');
		$textmaster_password = get_option_tm('textmaster_password');
		$oOAuth = new TextMaster_OAuth2();
		$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
		$infosUser = $oOAuth->getUserInfos($token);

		foreach ($projetsTM  as $projets) {
	//		print_r($projets);
			$idPost = '';
			//$req = 'SELECT * FROM '.$wpdb->prefix.'postmeta WHERE  meta_key LIKE "%textmasterDocumentId%" AND meta_value="'.$projets['idDocument'].'"'; //meta_key LIKE "%textmasterId%" AND meta_value="'.$projets['id'].'" AND
		//	echo $req;
			//$wpId = $wpdb->get_results( $req);
		//	echo $projets['idDocument'];
		//	print_r($wpId);
			$idPost =$projets['post_id'];
			if ($projets['post_id'] == '') {
			//	$table_post = $wpdb->prefix . "posts";
				$titres = explode('(',$projets['name'] );
				$titre = trim($titres[0]);
				$Id = $wpdb->get_var('SELECT ID FROM  ' . $table_post . ' WHERE 	post_title="'.$titre.'" AND post_status!="inherit"');
			//	echo 'SELECT ID FROM  ' . $table_post . ' WHERE 	post_title="'.$titre.'" AND post_status!="inherit"';

				if ($Id != '')
					$idPost = $Id;
			}



			if ($idPost != '')
				$item['name'] = '<a href="'.$adminUrl.'post.php?post='.$idPost.'&action=edit">'.$projets['name'].'</a>';
			else if ( strpos($projets['id'], 'wp_') !== FALSE) {
				$item['name'] = '<a href="'.$adminUrl.'post.php?post='.str_replace('wp_', '', $projets['id']).'&action=edit">'.$projets['name'].'</a>';
			}
			else
			{


				$lang = explode('_', get_locale());
				$urlProjet = 'http://'.$lang[0].'.'.URL_TM_PROJET.$projets['id'].'/overview/?auth_token='.$infosUser['authentication_token'];
				$item['name'] = '<a href="'.$urlProjet.'" target="_blank">'.$projets['name'].'</a>';
			}

		//	$item['ctype'] = $projets['ctype'];
			// traduction des types
			if ($projets['ctype'] == 'translation')
				$item['ctype'] = __('translation', 'textmaster');
			else if ($projets['ctype'] == 'proofreading')
				$item['ctype'] =  __('proofreading', 'textmaster');
			else if ($projets['ctype'] == 'copywriting')
				$item['ctype'] = __('copywriting', 'textmaster');

			if ($projets['ctype'] == 'translation')
				$item['ctype'] .= ' ( ' .$projets['language_from']. ' > '. $projets['language_to'].' )';
			else
				$item['ctype'] .= ' ( '. $projets['language_to'].' )';
		//	$item['language_from'] = $projets['language_from'];
		//	$item['language_to'] = $projets['language_to'];
			$item['category'] = textmaster_api::getLibCategorie($projets['category']);
			$item['created_at'] = $projets['created_at'];
	//		$item['updated_at'] = $projets['updated_at'];
			$item['completed_at'] = $projets['completed_at'];
			$item['status'] = textmaster_api::getLibStatus($projets['status']);
			$this->items[] = $item;
		}

/*		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);
		echo 'Page generated in '.$total_time.' seconds.';
*/
		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));

	}

}


function wpSaveProjet($projets){
	global $wpdb;

	$table_name = $wpdb->base_prefix . "tm_projets";
	$sqlUpdate = 'UPDATE '.$table_name.' SET
					  name = %s,
					  language_from = %s,
					  language_to = %s,
					  category = %s,
					  vocabulary_type = %s,
					  target_reader_groups = %s,
					  language_level = %s,
					  expertise = %s,
					  grammatical_person = %s,
					  project_briefing = %s,
					  priority = %s,
					  status = %s,
					  total_word_count = %s,
					  same_author_must_do_entire_project = %s,
					  cost_in_credits = %s,
					  ctype = %s,
					  creation_channel = %s,
					  reference = %s,
					  work_template = %s,
					  created_at = %s,
					  updated_at = %s,
					  completed_at = %s,
					  launched_at = %s,
					  archived = %s
					  WHERE idDocument = %s AND id = %s';

	$sqlInsert = 'INSERT INTO '.$table_name.' (name, language_from, language_to, category, vocabulary_type,  target_reader_groups, language_level, expertise, grammatical_person, project_briefing,
					priority, status, total_word_count, same_author_must_do_entire_project, cost_in_credits, ctype, creation_channel, reference, work_template, created_at, updated_at, completed_at, launched_at, archived, idDocument, id)
				  	VALUES (%s, %s, %s, %s, %s,  %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';

	$req = 'SELECT idDocument FROM '.$table_name;
	$tmIds = $wpdb->get_results( $req, ARRAY_A);

	if (!isset($projets['options']['expertise']))
		$projets['options']['expertise'] = '';

	$datas = array( $projets['name'], $projets['language_from'], $projets['language_to'], $projets['category'], $projets['vocabulary_type'], $projets['target_reader_groups'], $projets['options']['language_level'],
				   $projets['options']['expertise'], $projets['grammatical_person'], $projets['project_briefing'], $projets['priority'], $projets['status'], $projets['total_word_count'], $projets['same_author_must_do_entire_project'],
				   $projets['cost_in_credits'], $projets['ctype'], $projets['creation_channel'], $projets['reference'], $projets['work_template']['name'], $projets['created_at']['full'], $projets['updated_at']['full'],
				   $projets['completed_at']['full'], $projets['launched_at']['full'], $projets['archived'], $projets['IdDoc'], $projets['id'] );

		if (in_array(array('idDocument' => $projets['IdDoc']), $tmIds)) {
			$reqUpdate = $wpdb->prepare($sqlUpdate, $datas);
//			echo $reqUpdate.'<br>';
			$res = $wpdb->query($reqUpdate);
			//		echo $res;
		}
		else {
			$reqInsert =  $wpdb->prepare($sqlInsert, $datas);
	//		echo $reqInsert.'<br>';
			$wpdb->query($reqInsert);
		}
}

function wpDelTempProjet($id){
	global $wpdb;

	if ($id != '') {
		$table_name = $wpdb->base_prefix . "tm_projets";
		$sqlDel = 'DELETE FROM '.$table_name.'  WHERE id = "%s"';
		$reqDel = $wpdb->prepare($sqlDel, $id);
		$wpdb->query($reqDel);
	}
}
?>
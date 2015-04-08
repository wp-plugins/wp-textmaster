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

	$adminUrl = admin_url();

	if (!isset($_REQUEST['tmc']))
		$_REQUEST['tmc'] = '';
	if (!isset($_REQUEST['tmsite']))
		$_REQUEST['tmsite'] = '';
	if (!isset($_REQUEST['tmt'] ))
		$_REQUEST['tmt'] = 'translation';
	else
		$_SESSON['tmt'] = $_REQUEST['tmt'];

	$html = '';

	// le filtre par site pour le network
	if ( is_network_admin() ) {
		if (function_exists('wp_get_sites')) {
			$sites = wp_get_sites();
			if (count($sites) != 0) {
				$html .= '<select name="tmsite" onChange="jQuery(\'#tmsite\').val(jQuery(this).val());document.location.href=\''. network_admin_url('admin.php?page=Textmaster&tmsite=').'\'+jQuery(this).val()+\'&tmt=\'+jQuery(\'#tmt\').val();">';//jQuery(\'#formTMProjets\').submit();
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

	// le filtre par type
	$html .= '<select name="tmt" onChange="jQuery(\'#tmt\').val(jQuery(this).val());document.location.href=\''.$urlTmt .'\'+jQuery(this).val()+\'&tmsite=\'+jQuery(\'#tmsite\').val();">';//jQuery(\'#formTMProjets\').submit();
	$html .= '<option value="translation" ';
	if (isset($_REQUEST['tmt']) && $_REQUEST['tmt'] == 'translation')
		$html .= 'selected="selected"';
	$html .= '>'. __('Translation', 'textmaster') .'</option>';
	$html .= '<option value="copywriting" ';
	if (isset($_REQUEST['tmt']) && $_REQUEST['tmt'] == 'copywriting')
		$html .='selected="selected"';
	$html .= '>'. __('Copywriting', 'textmaster') .'</option>';
	$html .= '<option value="proofreading" ';
	if (isset($_REQUEST['tmt']) && $_REQUEST['tmt'] == 'proofreading')
		$html .= 'selected="selected"';
	$html .=  '>'.__('Proofreading', 'textmaster').'</option>';
	$html .= '</select>';
	if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') {
		$html .= '<select name="tmt_type" onChange="jQuery(\'#tmt_type\').val(jQuery(this).val());document.location.href=\''.$urlTmt_type .'\'+jQuery(this).val();">';//jQuery(\'#formTMProjets\').submit();
		$html .= '<option value="post" ';
		if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'post')
			$html .= 'selected="selected"';
		$html .= '>'. __('Articles / Pages', 'textmaster') .'</option>';
		$html .= '<option value="term" ';
		if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term')
			$html .= 'selected="selected"';
		$html .= '>'. __('Catégories / Tags', 'textmaster') .'</option>';
		$html .= '</select>';
	}
	?>

			<div class="wrap" id="textmaster_projets">
			<?php echo "<div id='icon-edit' class='icon32 icon32-posts-textmaster_redaction'><br></div><h2>" . __('Tous les projets TextMaster' , 'textmaster') .' '. $html ."</h2>"; ?>
			<form method="post" id="formTMProjets">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>"/>
			<input type="hidden" name="tms" value="<?php echo $_REQUEST['tms']; ?>"/>
			<input type="hidden" name="tmt" id="tmt" value="<?php echo $_REQUEST['tmt']; ?>"/>
			<input type="hidden" name="tmt_type" id="tmt_type" value="<?php echo $_REQUEST['tmt_type']; ?>"/>
			<input type="hidden" name="tmc" value="<?php echo $_REQUEST['tmc']; ?>"/>
			<input type="hidden" name="tml" value="<?php echo $_REQUEST['tml']; ?>"/>
			<input type="hidden" name="tmsite" id="tmsite" value="<?php echo $_REQUEST['tmsite']; ?>"/>
			<?php
	$listeProjets->display();
	if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1) {
			if ( is_network_admin() ){
			$detail = get_blog_details($_REQUEST['tmsite']);
			$adminUrl = $detail->siteurl.'/wp-admin/';
			switch_to_blog($_REQUEST['tmsite']);
		}
	}else {
		if ( is_network_admin() && isset($_REQUEST['tmsite'])){
			$detail = get_blog_details($_REQUEST['tmsite']);
			$adminUrl = $detail->siteurl.'/wp-admin/';
		}
		else
			$adminUrl = admin_url();
	}
	?>
				</form>
				</div>
			<script>window.urlPlugin = "<?php echo plugins_url('', __FILE__) ?>"; window.urlAdmin = "<?php echo $adminUrl ?>";</script>';
			<script type="text/javascript">
				jQuery(document).ready(function() {
				<?php if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') { ?>
						jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_TraductionBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('traduction_bulk.php', __FILE__); ?>?post_ids=' + ids + '&type=<?php echo $_REQUEST['tmt_type']; ?>&site=<?php echo $_REQUEST['tmsite']; ?>&height=500&width=630&TB_iframe=true';
							if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_TraductionBulk' )
								tb_show('<?php _e('Traduction TextMaster', 'textmaster') ?>', urlPopup);
								event.preventDefault();
						}

						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('validation_bulk.php', __FILE__); ?>?trad_only=yes&post_ids=' + ids + '&type=<?php echo $_REQUEST['tmt_type']; ?>&site=<?php echo $_REQUEST['tmsite']; ?>&height=400&width=600&TB_iframe=true';
							if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' )
								tb_show('<?php _e('Validation TextMaster', 'textmaster') ?>', urlPopup);
								event.preventDefault();
						}
					});
				<?php }   else if ($_REQUEST['tmt'] == 'proofreading' ) { ?>
					jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('validation_bulk.php', __FILE__); ?>?read_only=yes&post_ids=' + ids + '&type=<?php echo $_REQUEST['tmt_type']; ?>&height=400&width=600&TB_iframe=true';
							if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' )
								tb_show('<?php _e('Validation TextMaster', 'textmaster') ?>', urlPopup);
								event.preventDefault();
						}
					});
				<?php } ?>
				});

			</script>
			<?php
		if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1)
			restore_current_blog();
}

class textmaster_projets_Table extends WP_List_Table {

	function __construct() {
		add_action('admin_head', array(&$this, 'admin_header'));
		parent::__construct( array(
			'singular'  => __('projet'),
			'plural'    => __('projets'),
			'ajax'      => false
		) );


		if (!isset($_REQUEST['tmt'] ))
			$_REQUEST['tmt'] = 'translation';
		else
			$_SESSON['tmt'] = $_REQUEST['tmt'];

	}

	function admin_header()
	{
		$page = false;
		if (isset($_GET['page'])) {
			$page = esc_attr($_GET['page']) ;
		}

		if ('textmaster-projets' == $page)
		{

			if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') {
				$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
				echo '<style type="text/css">';
				echo '.wp-list-table .column-'.$textmaster_langueDefaut.' { width: 30%; }';
				echo '</style>';

			} else {
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
	}

	function extra_tablenav( $which ) {
		global $wpdb;

		$table_langues =  $wpdb->base_prefix .'tm_langues';

		if ( $which == "top" ){

	//		$html .= '<br/>';
				$html = '';

			// le filtre par status
			$html .= '<select name="tms" onChange="if (jQuery(this).val() != \'\') jQuery(\'#tml\').removeAttr(\'disabled\');else jQuery(\'#tml\').attr(\'disabled\',\'disabled\');">';
			$html .= '<option value="">'. __('Voir tous les status TextMaster', 'textmaster').'</option>';
			$html .= '<option value="to_translate" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'to_translate')
				$html .= 'selected="selected"';
			$html .= '>'.__('A traduire', 'textmaster') .'</option>';
			$html .= '<option value="waiting_assignment" ';
 			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'waiting_assignment')
 			 	$html .= 'selected="selected"';
			$html .= '>'.textmaster_api::getLibStatus('waiting_assignment') .'</option>';
			$html .= '<option value="in_creation"';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'in_creation')
				$html .= 'selected="selected"';
			$html .= '>'.textmaster_api::getLibStatus('in_creation') .'</option>';
			$html .= '<option value="in_progress" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'in_progress')
				$html .= 'selected="selected"';
			$html .= '>'.textmaster_api::getLibStatus('in_progress') .'</option>';
			$html .= '<option value="in_review" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'in_review')
				$html .= 'selected="selected"';
			$html .= '>'.textmaster_api::getLibStatus('in_review') .'</option>';
			$html .= '<option value="paused" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'paused')
				$html .= 'selected="selected"';
			$html .= '>'.textmaster_api::getLibStatus('paused').'</option>';
			$html .= '<option value="canceled" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'canceled')
				$html .= 'selected="selected"';
			$html .= '>'.textmaster_api::getLibStatus('canceled').'</option>';
			$html .= '<option value="completed" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'completed')
				$html .= 'selected="selected"';
				$html .= '>'.textmaster_api::getLibStatus('completed').'</option>';
			$html .= '<option value="incomplete" ';
			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] == 'incomplete')
				$html .=  'selected="selected"';
			$html .= '>'. textmaster_api::getLibStatus('incomplete').'</option>';
			$html .= '</select>';

			$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
			$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));

			$disable = '';
			if (!isset($_REQUEST['tms']) || ($_REQUEST['tms'] == '#' || $_REQUEST['tms'] == ''))
				$disable = 'disabled="disabled"';

			$html .= '<select name="tml" id="tml" onChange="jQuery(\'#formTMProjets\').submit();" '.$disable.'>';
			$html .= '<option value="" ';
			if (isset($_REQUEST['tml']) && ($_REQUEST['tml'] == '#' || $_REQUEST['tml'] == ''))
				$html .= 'selected="selected"';
			$html .= '>'.__('Toutes les langues', 'textmaster') .'</option>';
			if (count($textmaster_langues) != 0) {
				foreach ($textmaster_langues as $langue) {
					if ($textmaster_langueDefaut != $langue) {
						$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
						$html .= '<option value="'.$langue.'" ';
						if (isset($_REQUEST['tml']) && $_REQUEST['tml'] ==$langue)
							$html .=  'selected="selected"';
						$html .= '>'. $langue_str.'</option>';
					}
				}
				$html .= '</select>';
			}
			$html .= '<input type="submit" name="" id="doactionFilter" class="button action" value="Apply">';
			echo $html;
		}


	}

	function get_columns()	{
		global $wpdb;


		if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') {

			$columns['cb'] = '<input type="checkbox" />';

			$table_langues =  $wpdb->base_prefix .'tm_langues';

			$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
			$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$textmaster_langueDefaut'" );
			$columns[$textmaster_langueDefaut] = $langue_str;
			$aPercents = getPercentLangs($this->items);
			$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
			if (count($textmaster_langues) != 0) {
				foreach ($textmaster_langues as $langue) {
					if ($textmaster_langueDefaut != $langue) {
						$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
						$columns[$langue] = $langue_str. ' <div class="barPourcent" data-value="'.$aPercents[$langue].'"><div class="fill"></div><span class="pourcentTrad">'.$aPercents[$langue].' %</span></div>';
					}
				}
			}

		} else if ($_REQUEST['tmt'] == 'proofreading' ) {
			$columns = array(
				'cb' => '<input type="checkbox" />',
			    'name' => __('Nom', 'textmaster'),
			//	    'language_from' => __('Langue d\'origine','textmaster'),
			//	    'language_to' => __('Langue de destination','textmaster'),
			    'category' => __('Category', 'textmaster'),
			    'status' => __('Status', 'textmaster'),
			    'created_at' => __('Créé le', 'textmaster'),
			//	    'launched_at' => __('Démarré le', 'textmaster'),
			    'completed_at' => __('Terminé le', 'textmaster')
			    );
		} else {
			$columns = array(
			    'name' => __('Nom', 'textmaster'),
			//	    'language_from' => __('Langue d\'origine','textmaster'),
			//	    'language_to' => __('Langue de destination','textmaster'),
			    'category' => __('Category', 'textmaster'),
			    'status' => __('Status', 'textmaster'),
			    'created_at' => __('Créé le', 'textmaster'),
			//	    'launched_at' => __('Démarré le', 'textmaster'),
			    'completed_at' => __('Terminé le', 'textmaster')
			    );
		}

		return $columns;
	}

	function get_sortable_columns()
	{
		if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') {
			$sortable_columns = array();
	//		$sortable_columns['cb'] =  array('cb', false);
			//$columns = $this->get_columns();
	/*		foreach ($columns as $key => $col) {
				$sortable_columns[$key]  = array($key, false);
			}*/
		} else {
			$sortable_columns = array(
			'name' => array('name', false),
			//	'ctype' =>  array('ctype', false),
			//	'status' =>  array('status', false),
			'created_at' =>  array('created_at', true),
			//		'launched_at' =>  array('launched_at', false),
			'completed_at' =>  array('completed_at', false)
		);
		}

		return $sortable_columns;

	}

	function column_cb($item)
	{
		$ret = '';
		if ($item['ID'] != '') {
			$ret = sprintf(
		    '<input type="checkbox" name="id[]" value="%s" />',
		    $item['ID']
		    );
		}

		return $ret;
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
		//		echo $column_name;
				return $item[ $column_name ];
		}
	}

	function get_bulk_actions()
	{
		$actions = array();
		if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') {
			$actions = array(
			    'textmaster_TraductionBulk' => __('Traduire', 'textmaster'),
			    'textmaster_ValidationBulk' => __('Valider', 'textmaster')
			    );
		}
		else if ($_REQUEST['tmt'] == 'proofreading' ) {
			$actions = array(
			'textmaster_ValidationBulk' => __('Valider', 'textmaster')
		);
		}
		return $actions;
	}

	function process_bulk_action()
	{
		global $wpdb;

		if ('textmaster_TraductionBulk' === $this->current_action()) {
		/*	$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$table = $wpdb->prefix . $this->table_name ;
				$wpdb->query("DELETE FROM $table WHERE id IN($ids)");
			}*/
			echo 'trad';
		}
	}

	function prepare_items(){
		global $wpdb;

		if (!isset($_REQUEST['tmt_type']))
			$_REQUEST['tmt_type'] = 'post';
		if (!isset($_REQUEST['tmsite']))
			$_REQUEST['tmsite'] = get_current_blog_id();
//		else
//			'';
//		$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

		$per_page = 40;
		if ($_REQUEST['tmt'] == 'translation' || $_REQUEST['tmt'] == '') {

			$table_Projet = $wpdb->base_prefix . "tm_projets";


			$where = '';

			if (isset($_REQUEST['tms']) && $_REQUEST['tms'] != ''){
				if ($_REQUEST['tms'] == 'to_translate'){
					$where .= ' AND ('.$table_Projet.'.status IS NULL';
					if (isset($_REQUEST['tml']) && $_REQUEST['tml'] != '')
						$where .= ' OR language_to!="'.$_REQUEST['tml'] .'"';
					$where .= ')';
				}
				else  {
					$where .= ' AND '.$table_Projet.'.status="'.$_REQUEST['tms'] .'"';
					if (isset($_REQUEST['tml']) && $_REQUEST['tml'] != '')
						$where .= ' AND language_to="'.$_REQUEST['tml'] .'"';

				}
			}


			if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term') {
				$adminUrl ='';
				if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1) {
					$table_name = $wpdb->base_prefix . $_REQUEST['tmsite']. "_terms";
					$table_meta = $wpdb->base_prefix . $_REQUEST['tmsite']. "_options";
					$table_taxonomy = $wpdb->base_prefix . $_REQUEST['tmsite']."_term_taxonomy";
			//		if ( is_network_admin() ){
						$detail = get_blog_details($_REQUEST['tmsite']);
						$adminUrl = $detail->siteurl.'/wp-admin/';
			//		}
				}else {
					if ( is_network_admin())
						$_REQUEST['tmsite'] = 1;
					$table_name = $wpdb->prefix . "terms";
					$table_meta = $wpdb->prefix . "options";
					$table_taxonomy = $wpdb->prefix . "term_taxonomy";
					if ( is_network_admin() && isset($_REQUEST['tmsite'])){
						$detail = get_blog_details($_REQUEST['tmsite']);
						$adminUrl = $detail->siteurl.'/wp-admin/';
					}

				}
				$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
				$orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
				$order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

				$total_items = $wpdb->get_var('SELECT COUNT(' . $table_name . '.term_id) FROM  ' . $table_name .'
												JOIN '.$table_taxonomy.' ON '.$table_taxonomy.'.term_id='.$table_name.'.term_id AND taxonomy!="nav_menu"');

				$req = 'SELECT * FROM ' . $table_name . '
						JOIN '.$table_taxonomy.' ON '.$table_taxonomy.'.term_id='.$table_name.'.term_id AND taxonomy!="nav_menu"
						'.$where.'
						ORDER BY ' . $orderby . ' ' . $order . ' LIMIT '.($paged * $per_page).', '. $per_page.'';
		//		echo $req;
				$posts = $wpdb->get_results( $req, ARRAY_A);
			}else {
				$adminUrl ='';
				if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1) {
					$table_name = $wpdb->base_prefix . $_REQUEST['tmsite']."_posts";
					$table_meta = $wpdb->base_prefix . $_REQUEST['tmsite']."_postmeta";
					if ( is_network_admin() ){
						$detail = get_blog_details($_REQUEST['tmsite']);
						$adminUrl = $detail->siteurl.'/wp-admin/';
					}
				}else {
					if ( is_network_admin())
						$_REQUEST['tmsite'] = 1;
					$table_name = $wpdb->prefix . "posts";
					$table_meta = $wpdb->prefix . "postmeta";
					if ( is_network_admin() && isset($_REQUEST['tmsite'])){
						$detail = get_blog_details($_REQUEST['tmsite']);
						$adminUrl = $detail->siteurl.'/wp-admin/';
					}
				}


				$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
				$orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'post_date';
				$order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';


				$total = $wpdb->get_results('SELECT DISTINCT ' . $table_name . '.ID FROM  ' . $table_name . '
						LEFT JOIN '.$table_meta.' ON '.$table_meta.'.post_id='.$table_name.'.ID AND meta_key="textmasterDocumentIdTrad"
						LEFT JOIN '.$table_Projet.' ON  '.$table_meta.'.meta_value LIKE CONCAT(\'%\','.$table_Projet.'.idDocument ,\'%\') AND ctype="translation" AND name NOT LIKE "%(junk)%"
						 WHERE ' . $table_name . '.ID NOT IN (SELECT post_id FROM '.$table_meta.' WHERE meta_key="tm_lang" AND meta_value!="'.get_option_tm('textmaster_langueDefaut').'" )
						 AND post_type!="textmaster_redaction" AND post_type!="acf" AND post_type!="nav_menu_item" AND (post_status!="trash" AND post_status!="auto-draft" AND post_status!="inherit")
						 AND post_type!="revision"   '.$where.' ORDER BY ' . $orderby . ' ');
				$total_items = count($total);
		//		var_dump($total);
				$req = 'SELECT DISTINCT ' . $table_name . '.ID, ' . $table_name . '.* FROM ' . $table_name . '
					LEFT JOIN '.$table_meta.' ON '.$table_meta.'.post_id='.$table_name.'.ID AND meta_key="textmasterDocumentIdTrad"
					LEFT JOIN '.$table_Projet.' ON  '.$table_meta.'.meta_value LIKE CONCAT(\'%\','.$table_Projet.'.idDocument ,\'%\') AND ctype="translation"  AND name NOT LIKE "%(junk)%"
					WHERE ' . $table_name . '.ID NOT IN (SELECT post_id FROM '.$table_meta.' WHERE meta_key="tm_lang" AND meta_value!="'.get_option_tm('textmaster_langueDefaut').'" )
					AND post_type!="textmaster_redaction" AND post_type!="acf" AND post_type!="nav_menu_item" AND (post_status!="trash" AND post_status!="auto-draft" AND post_status!="inherit")
					AND post_type!="revision" '.$where.'  ORDER BY ' . $orderby . ' ' . $order . ' LIMIT '.($paged * $per_page).', '. $per_page.'';
	//			echo $req;
				$posts = $wpdb->get_results( $req, ARRAY_A);
			}



			$textmaster_email = get_option_tm('textmaster_email');
			$textmaster_password = get_option_tm('textmaster_password');
			$oOAuth = new TextMaster_OAuth2();
			$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
			$infosUser = $oOAuth->getUserInfos($token);

			foreach ($posts  as $post) {
				$idTradTM = '';
				if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term') {
					$idPost = $post['term_id'];
					$item['ID'] = $post['term_id'];
				}else {
					$idPost = $post['ID'];
					$item['ID'] = $post['ID'];
				}
				if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term' && $idPost != '') {
					$item[get_option_tm('textmaster_langueDefaut')] = '<a href="'.$adminUrl.'edit-tags.php?taxonomy='.$post['taxonomy'].'">'.$post['name'].' ('.$post['taxonomy'].')</a>';
				}
				else if ($idPost != ''){
					//var_dump($post);
					$item[get_option_tm('textmaster_langueDefaut')] = '<a href="'.$adminUrl.'post.php?post='.$idPost.'&action=edit">'.$post['post_title'].' ('.$post['post_type'].') '.$post['post_status'].'</a>';
				}


				$columns = $this->get_columns();
				foreach ($columns as $key => $col) {

					$idProjetTradTM = get_IdProjetTrad($idPost, $key,$_REQUEST['tmt_type'], $table_meta);
					$idTrad =  get_IdTrad($idPost, $key, $_REQUEST['tmt_type'],$table_meta);
					$idTradTM = get_IdDocTrad($idPost, $key,$_REQUEST['tmt_type'], $table_meta);
					if ($idTradTM != '' && $key != get_option_tm('textmaster_langueDefaut')){
					//	$supportMsgs = $tApi->getSupportMsgs($idProjetTradTM, $idTradTM, TRUE);
						$table_support_messages = $wpdb->base_prefix . 'tm_support_messages';
						$req = $wpdb->prepare('SELECT * FROM ' . $table_support_messages. ' WHERE idProjet=%s AND idDocument=%s  ORDER BY created_at', $idProjetTradTM, $idTradTM);
						$supportMsgs["support_messages"] = $wpdb->get_results($req, ARRAY_A);

						$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjetTradTM.'" AND idDocument="'.$idTradTM.'"';
						$status = $wpdb->get_var( $req);
						if ($status == 'incomplete' && count($supportMsgs["support_messages"]) != 0) {
							$title = get_the_title_tm($idTrad, $_REQUEST['tmsite']);
							$item[$key]  = '<a href="'.$adminUrl.'post.php?post='.$idTrad.'&action=edit">'.$title.'</a> - '.count($supportMsgs["support_messages"]).' '.__('Message(s)', 'textmaster');
						}
						else if ($status == 'in_review' && isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term')
							$item[$key]  = '<a href="'.$adminUrl.'edit-tags.php?taxonomy='.$post['taxonomy'].'">'.textmaster_api::getLibStatus($status).'</a> ';
						else if ($status == 'in_review')
							$item[$key]  = '<a href="#" onclick="valideTrad(\''.$idPost.'\', \''.$idProjetTradTM.'\', \''.$idTradTM.'\', true, \''.__('Merci de patienter...', 'textmaster').'\', \''.$_REQUEST['tmsite'].'\');">'. textmaster_api::getLibStatus($status).'</a> ';
						else if ($status == 'completed' && $idTrad != ''){
							$title = get_the_title_tm($idTrad, $_REQUEST['tmsite']);
							$item[$key]  = '<a href="'.$adminUrl.'post.php?post='.$idPost.'&action=edit">'.$title.'</a> (' .get_post_status_tm( $idTrad, $_REQUEST['tmsite'] ).' - '.textmaster_api::getLibStatus($status).')';
						}
						else
							$item[$key]  = textmaster_api::getLibStatus($status); //__('En cours TM', 'textmaster'). print_r($post, true);
					}
					/*if ($key == $post['language_to'] && $key != get_option_tm('textmaster_langueDefaut')) {
						$item[$key]  = strtoupper($post['language_from']) .' => '. strtoupper($post['language_to']) .' / '.textmaster_api::getLibStatus($post['status']);
					}*/
					else if ($key != get_option_tm('textmaster_langueDefaut')){
						$idTrad = get_IdTrad($idPost, $key, $_REQUEST['tmt_type'], $table_meta);
						$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjetTradTM.'" AND idDocument="'.$idTradTM.'"';
						$status = $wpdb->get_var( $req);
						if ($idTrad != '')
							$item[$key]  = __('Fait', 'textmaster');
						else if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term')
							$item[$key]  =  '<a href="'.$adminUrl.'edit-tags.php?taxonomy='.$post['taxonomy'].'">'.__('A traduire', 'textmaster').'</a> ';
						else
							$item[$key]  =  '<a href="'.$adminUrl.'post.php?post='.$idPost.'&langTm='.$key.'&action=edit">'.__('A traduire', 'textmaster').'</a> ';
					}


				}

				$this->items[] = $item;
			}


		}
		else {
			$table_name = $wpdb->base_prefix . "tm_projets";

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

			$total_items = $wpdb->get_var('SELECT COUNT(id) FROM  ' . $table_name . '
						LEFT JOIN  '.$table_meta.' ON meta_key LIKE "%textmasterDocumentId%" AND meta_value=' . $table_name . '.idDocument
						WHERE ( creation_channel="api" OR id LIKE "wp_%%") AND archived!=1 AND name NOT LIKE "%(junk)%" '.$where);
			//						 WHERE ID NOT IN (SELECT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key="tm_lang" AND meta_value!="'.get_option_tm('textmaster_langueDefaut').'" )

			$req = 'SELECT * FROM ' . $table_name . '
					LEFT JOIN  '.$table_meta.' ON meta_key LIKE "%textmasterDocumentId%" AND meta_value=' . $table_name . '.idDocument
					WHERE (creation_channel="api" OR id LIKE "wp_%%") AND archived!=1  AND name NOT LIKE "%(junk)%" '.$where.' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT '.($paged * $per_page).', '. $per_page.'';
// 	WHERE ID NOT IN (SELECT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key="tm_lang" AND meta_value!="'.get_option_tm('textmaster_langueDefaut').'" )
//			echo $req;
			$projetsTM = $wpdb->get_results( $req, ARRAY_A);

			$textmaster_email = get_option_tm('textmaster_email');
			$textmaster_password = get_option_tm('textmaster_password');
			$oOAuth = new TextMaster_OAuth2();
			$token = $oOAuth->getToken($textmaster_email, $textmaster_password);
			$infosUser = $oOAuth->getUserInfos($token);

			foreach ($projetsTM  as $projets) {
				//		print_r($projets);
				$idPost = '';
				$item['ID'] = $projets['post_id'];
				$idPost =$projets['post_id'];
				if ($projets['post_id'] == '') {
					$table_post = $wpdb->prefix . "posts";
					$titres = explode('(',$projets['name'] );
					$titre = trim($titres[0]);
					$Id = $wpdb->get_var('SELECT ID FROM  ' . $table_post . ' WHERE post_title="'.$titre.'" AND post_status!="inherit"');

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

		}

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);


		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));
	}
}

function getPercentLangs($items){
	global $wpdb;

	$where = '';

	$percent = array();
	if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term') {
		if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1) {
			$table_name = $wpdb->base_prefix . $_REQUEST['tmsite']."_terms";
			$table_meta = $wpdb->base_prefix . $_REQUEST['tmsite']."_options";
			$table_taxonomy = $wpdb->base_prefix . $_REQUEST['tmsite']."_term_taxonomy";
		}else {
			$table_name = $wpdb->prefix . "terms";
			$table_meta = $wpdb->prefix . "options";
			$table_taxonomy = $wpdb->prefix . "term_taxonomy";
		}
		$total_items = $wpdb->get_var('SELECT COUNT(' . $table_name . '.term_id) FROM  ' . $table_name .'
												JOIN '.$table_taxonomy.' ON '.$table_taxonomy.'.term_id='.$table_name.'.term_id AND taxonomy!="nav_menu"');
	}else {
		if (isset($_REQUEST['tmsite']) && $_REQUEST['tmsite'] > 1) {
			$table_name = $wpdb->base_prefix . $_REQUEST['tmsite']."_posts";
			$table_meta = $wpdb->base_prefix . $_REQUEST['tmsite']."_postmeta";
			$table_Projet = $wpdb->base_prefix . "tm_projets";

		}else {
			$table_name = $wpdb->prefix . "posts";
			$table_meta = $wpdb->prefix . "postmeta";
			$table_Projet = $wpdb->base_prefix . "tm_projets";
		}

		if (isset($_REQUEST['tms']) && $_REQUEST['tms'] != ''){
			if ($_REQUEST['tms'] == 'to_translate'){
				$where .= ' AND ('.$table_Projet.'.status IS NULL';
				if (isset($_REQUEST['tml']) && $_REQUEST['tml'] != '')
					$where .= ' OR language_to!="'.$_REQUEST['tml'] .'"';
				$where .= ')';
			}
			else  {
				$where .= ' AND '.$table_Projet.'.status="'.$_REQUEST['tms'] .'"';
				if (isset($_REQUEST['tml']) && $_REQUEST['tml'] != '')
					$where .= ' AND language_to="'.$_REQUEST['tml'] .'"';

			}
		}

		$total = $wpdb->get_results('SELECT DISTINCT ' . $table_name . '.ID FROM  ' . $table_name . '
						LEFT JOIN '.$table_meta.' ON '.$table_meta.'.post_id='.$table_name.'.ID AND meta_key="textmasterDocumentIdTrad"
						LEFT JOIN '.$table_Projet.' ON  '.$table_meta.'.meta_value LIKE CONCAT(\'%\','.$table_Projet.'.idDocument ,\'%\') AND ctype="translation"
						 WHERE ' . $table_name . '.ID NOT IN (SELECT post_id FROM '.$table_meta.' WHERE meta_key="tm_lang" AND meta_value!="'.get_option_tm('textmaster_langueDefaut').'" )
						 AND post_type!="textmaster_redaction" AND post_type!="acf" AND name NOT LIKE "%(junk)%" AND post_type!="nav_menu_item" AND (post_status!="trash" AND post_status!="auto-draft" AND post_status!="inherit")
						 AND post_type!="revision" '.$where);

		$total_items = count($total);
//		echo $total_items;
	}
	$aIdsItems = array();
	if (count($items) != 0) {
		foreach ($items as $item) {
			$aIdsItems[] = $item['ID'];
		}
	}


	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	if (count($textmaster_langues) != 0) {
		foreach ($textmaster_langues as $langue) {
			if (isset($_REQUEST['tmt_type']) && $_REQUEST['tmt_type'] == 'term') {
				$tad_items = $wpdb->get_var('SELECT COUNT(term_id) FROM  ' . $table_name . '
								JOIN '.$table_meta.' ON option_name=CONCAT("taxonomy_", term_id) AND '.$table_meta.'.option_value LIKE "%'.$langue.'=%"
								'.$where);
				//echo 'SELECT COUNT(term_id) FROM  ' . $table_name . '
				//				JOIN '.$table_meta.' ON option_name=CONCAT("taxonomy_", term_id) AND '.$table_meta.'.option_value LIKE "'.$langue.'="
				//				'.$where;
			} else {
				$tadItems = $wpdb->get_results('SELECT ID FROM  ' . $table_name . '
								JOIN '.$table_meta.' ON meta_key="tm_lang" AND '.$table_meta.'.meta_value = CONCAT('.$table_name.'.ID ,";'.$langue.'")
								WHERE post_type!="textmaster_redaction" AND post_type!="acf" AND post_type!="nav_menu_item" AND (post_status!="trash" AND post_status!="auto-draft" AND post_status!="inherit")
								AND post_type!="revision" AND ID IN ('.implode(', ', $aIdsItems).')
								GROUP BY ID');
				$tad_items = count($tadItems);

			}
			//echo 'SELECT ID FROM  ' . $table_name . '
			//					JOIN '.$table_meta.' ON meta_key="tm_lang" AND '.$table_meta.'.meta_value = CONCAT('.$table_name.'.ID ,";'.$langue.'")
			//					WHERE post_type!="textmaster_redaction" AND post_type!="acf" AND post_type!="nav_menu_item" AND (post_status!="trash" AND post_status!="auto-draft" AND post_status!="inherit")
			//					AND post_type!="revision" AND ID IN ('.implode(', ', $aIdsItems).')
			//					GROUP BY ID';
	//		echo $langue.' : '. $tad_items .' / '.$total_items.'<br>';
			if ($total_items == 0) {
				$percent[$langue] =  '';
			}
			else {
	//			echo $langue .' - '.$tad_items.' - '.$total_items.'<br/>';
				if ($tad_items != 0)
					$percent[$langue] = round( $tad_items*100/$total_items, 2);
				else
					$percent[$langue] = 0;

			}

			if ($percent[$langue] > 100) {
				$percent[$langue] = 100;
			}
		}
	}

//	print_r($pecent);

	return $percent;
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

function wp_texmaster_multilangs_metaboxes(){
	global $wpdb, $post, $post_type;

	// les custom post
	$strLinkType = '';
	$post_type = get_post_type( $post->ID );
	if ($post_type != 'page' && $post_type != 'post')
		$strLinkType = '&post_type='.$post_type;

	$table_langues =  $wpdb->base_prefix .'tm_langues';
	$metas_lang = get_post_meta($post->ID, 'tm_lang');
//	print_r($metas_lang);
	$id_origine = '';
	if (count($metas_lang) != 0) {
		$a_metas_lang = explode(';', $metas_lang[0]);
		$id_origine = $a_metas_lang[0];
		$_REQUEST['langTm'] = $a_metas_lang[1];
	}
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	$langueDefaut_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$textmaster_langueDefaut'" );

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));

	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	echo '<script>window.urlPlugin = "'. plugins_url('', __FILE__).'"; window.urlAdmin = "'. admin_url().'"; </script>';
	echo '<ul>';
	// on est sur le post d'origine

	if ($id_origine == '' && (!isset($_REQUEST['langTm']) || $_REQUEST['langTm'] == '')) {
		$id_origine = $post->ID;
		echo '<li><strong>'.$langueDefaut_str.'</strong></li>';
		if (count($textmaster_langues) != 0) {
			foreach ($textmaster_langues as $langue) {

				if ($textmaster_langueDefaut != $langue) {
					$result = "";
					// on recup le status de la trad chez textmaster
					$idProjet = get_IdProjetTrad($id_origine, $langue);
					if ($idProjet != '') {
						$idDocument = get_IdDocTrad($id_origine, $langue);
						$result = $tApi->getProjetStatus($idProjet);
						if ($idDocument != '')
							$result = $tApi->getDocumentStatus($idProjet, $idDocument);
					}
					$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );

					$id_trad =  get_IdTrad($id_origine,$langue);
					if ($result == 'in_review')
						$langue_str = $langue_str;
					else if ($id_trad != '')
						$langue_str = '<a href="post.php?post='.$id_trad.'&langTm='.$langue.'&action=edit'.$strLinkType.'">'.$langue_str.'</a>';
					else
						$langue_str = '<a href="post-new.php?langTm='.$langue.'&id_origine='.$id_origine.''.$strLinkType.'">'.$langue_str.'</a>';

					if ($idProjet != '') {
						if ($result == 'in_review')
							$langue_str .= ' > <a href="#" onclick="valideTrad(\''.$id_origine.'\', \''.$idProjet.'\', \''.$idDocument.'\', true, \''.__('Merci de patienter...', 'textmaster').'\');">'.__('Valider la traduction','textmaster').'</a>';
						else
							$langue_str .= ' > '.textmaster_api::getLibStatus($result);
					}
				}
				else if ($textmaster_langueDefaut != $langue)
					$langue_str = '<strong>'.$langue_str.'</strong>';
				if ($textmaster_langueDefaut != $langue)
					echo '<li>'.$langue_str.'</li>';
			}
		}

	}
	else {
		if (isset($_REQUEST['id_origine']) && $id_origine == '' )
			$id_origine = $_REQUEST['id_origine'];
		echo '<li><a href="post.php?post='.$id_origine.'&action=edit">'.$langueDefaut_str.'</a></li>';
		if (count($textmaster_langues) != 0) {
			foreach ($textmaster_langues as $langue) {
				$result = '';
				// on recup le status de la trad chez textmaster
				$idProjet = get_IdProjetTrad($id_origine, $langue);
				if ($idProjet != '') {
					$idDocument = get_IdDocTrad($id_origine, $langue);
					$result = $tApi->getProjetStatus($idProjet);
					if ($idDocument != '')
						$result = $tApi->getDocumentStatus($idProjet, $idDocument);
				}
				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );

				if ($_REQUEST['langTm'] != $langue && $textmaster_langueDefaut != $langue) {

					$id_trad =  get_IdTrad($id_origine,$langue);
					if ($result == 'in_review')
						$langue_str = $langue_str;
					else if ($id_trad != '')
						$langue_str = '<a href="post.php?post='.$id_trad.'&langTm='.$langue.'&action=edit'.$strLinkType.'">'.$langue_str.'</a>';
					else
						$langue_str = '<a href="post-new.php?langTm='.$langue.'&id_origine='.$id_origine.''.$strLinkType.'">'.$langue_str.'</a>';

					if ($idProjet != '') {
						if ( $result == 'in_review')
							$langue_str .= ' > <a href="#" onclick="valideTrad(\''.$id_origine.'\', \''.$idProjet.'\', \''.$idDocument.'\', true, \''.__('Merci de patienter...', 'textmaster').'\');">'.__('Valider la traduction','textmaster').'</a>';
						else
							$langue_str .= ' > '.textmaster_api::getLibStatus($result);
					}


				}
				else if ($textmaster_langueDefaut != $langue)
					$langue_str = '<strong>'.$langue_str.'</strong>';

				if ($textmaster_langueDefaut != $langue)
					echo '<li>'.$langue_str.'</li>';
			}
		}
	}
	echo '</ul>';
	echo '<input type="hidden" name="id_origine" value="'.$id_origine.'" />';
	if (isset($_REQUEST['langTm']))
		echo '<input type="hidden" name="langTm" value="'.$_REQUEST['langTm'].'" />';

	// info acf
	if ($post_type == 'acf') {
	 	echo __('Pour être traduit par TextMaster, les champs doivent avoir le même nom dans les différentes langues.','textmaster');
	}

}

function callback_tm_langue($post_id){

	if (isset($_POST['id_origine']) && $_POST['id_origine']  != '' && isset($_POST['langTm']) && $_POST['langTm'] != '') {

		if ($post_id == '')
			$post_id = $_POST['post_ID'];
		$tm_lang = $_POST['id_origine'].';'.$_POST['langTm'];
		update_post_meta($post_id, 'tm_lang', $tm_lang);

		// on garde le même type que l'original
		set_post_type($post_id, get_post_type($_POST['id_origine']));
	}
}

// gestion des categories
if (get_option_tm('textmaster_useMultiLangues') == 'Y'){
	add_action('category_edit_form_fields','category_edit_form_fields');
	add_action('category_add_form_fields','category_add_form_fields');
	add_action('edited_category', 'save_extra_taxonomy_fileds');
	add_filter('manage_edit-category_columns', 'add_category_columns');
	add_filter('manage_category_custom_column', 'add_category_column_content', 10, 3);
}
function category_edit_form_fields ($tag) {
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	if (is_object($tag)) {
		$t_id = $tag->term_id;
		$str_meta = get_option_tm( "taxonomy_$t_id");
		$aMetas = explode(';', $str_meta);
		$aLangs = array();
		if (count($aMetas) != 0) {
			foreach ($aMetas as $meta) {
				if ($meta != ''){
					$aLangs = explode('=', $meta);
					$metas_lang[$aLangs[0]] = $aLangs[1];
				}
			}
		}
	}

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	if (count($textmaster_langues) != 0) {
		echo '<tr class="form-field">';
		echo '<th valign="top" scope="row" colspan="2">';
		echo __('Traductions','textmaster');
		echo '</th>';
		echo '</tr>';
		foreach ($textmaster_langues as $langue) {
			if ($textmaster_langueDefaut != $langue) {
				if (!isset($metas_lang[$langue]))
					$metas_lang[$langue] = '';

				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
				echo '<tr class="form-field">';
				echo '<th valign="top" scope="row">';
				echo '<label for="'.$langue.'">'.$langue_str.'</label>';
				echo '</th>';
				echo '<td>';
				echo '<input type="text" id="cat_'.$langue.'" name="cat_'.$langue.'" value="'.$metas_lang[$langue].'"/>';
				echo '</td>';
				echo '</tr>';
			}
		}
	}
}
function category_add_form_fields ($tag) {
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	if (is_object($tag)) {
		$t_id = $tag->term_id;
		$str_meta = get_option_tm( "taxonomy_$t_id");
		$aMetas = explode(';', $str_meta);
		$aLangs = array();
		if (count($aMetas) != 0) {
			foreach ($aMetas as $meta) {
				if ($meta != ''){
					$aLangs = explode('=', $meta);
					$metas_lang[$aLangs[0]] = $aLangs[1];
				}
			}
		}
	}

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	if (count($textmaster_langues) != 0) {
		echo '<h3>';
		echo __('Traductions','textmaster');
		echo '</h3>';
		foreach ($textmaster_langues as $langue) {
			if ($textmaster_langueDefaut != $langue) {
				if (!isset($metas_lang[$langue]))
					$metas_lang[$langue] = '';

				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
				echo '<div class="form-field">';
				echo '<label for="'.$langue.'">'.$langue_str.'</label>';
				echo '<input type="text" id="cat_'.$langue.'" name="cat_'.$langue.'" value="'.$metas_lang[$langue].'"/>';
				echo '</div>';
			}
		}
	}
}
function add_category_columns($columns){
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	if (count($textmaster_langues) != 0) {
		foreach ($textmaster_langues as $langue) {
			if ($textmaster_langueDefaut != $langue) {
				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
				$columns[$langue] = $langue_str;
			}
		}
	}

	return $columns;
}
function add_category_column_content($content, $name , $term_id ){
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	$str_meta = get_option_tm( "taxonomy_$term_id");
	$aMetas = explode(';', $str_meta);
	$aLangs = array();
	if (count($aMetas) != 0) {
		foreach ($aMetas as $meta) {
			if ($meta != ''){
				$aLangs = explode('=', $meta);
				$metas_lang[$aLangs[0]] = $aLangs[1];
			}
		}
	}

	$idTmProjet = get_IdProjetTrad($term_id, $name, 'term');
	$idTmDoc = get_IdDocTrad($term_id, $name, 'term');

	if (isset($metas_lang[$name]))
		$content = 	$metas_lang[$name];
	else if ($idTmDoc != '' && $idTmProjet != '') {
		$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idTmProjet.'" AND idDocument="'.$idTmDoc.'"';
		$status = $wpdb->get_var( $req);
		$content = textmaster_api::getLibStatus($status);
	}
	else
		$content = 	'NC';

	return $content;
}
// gestion des tags
if (get_option_tm('textmaster_useMultiLangues') == 'Y'){
	add_action('post_tag_edit_form_fields','post_tag_edit_form_fields');
	add_action('post_tag_add_form_fields','post_tag_add_form_fields');
	add_action('edited_post_tag', 'save_extra_taxonomy_fileds');
	add_filter('manage_edit-post_tag_columns', 'add_post_tag_columns');
	add_filter('manage_post_tag_custom_column', 'add_post_tag_column_content', 10, 3);
}
function post_tag_edit_form_fields ($tag) {
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	if (is_object($tag)) {
		$t_id = $tag->term_id;
		$str_meta = get_option_tm( "taxonomy_$t_id");
		$aMetas = explode(';', $str_meta);
		$aLangs = array();
		if (count($aMetas) != 0) {
			foreach ($aMetas as $meta) {
				if ($meta != ''){
					$aLangs = explode('=', $meta);
					$metas_lang[$aLangs[0]] = $aLangs[1];
				}
			}
		}
	}

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	if (count($textmaster_langues) != 0) {
		echo '<tr class="form-field">';
		echo '<th valign="top" scope="row" colspan="2">';
		echo __('Traductions','textmaster');
		echo '</th>';
		echo '</tr>';
		foreach ($textmaster_langues as $langue) {
			if ($textmaster_langueDefaut != $langue) {
				if (!isset($metas_lang[$langue]))
					$metas_lang[$langue] = '';

				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
				echo '<tr class="form-field">';
				echo '<th valign="top" scope="row">';
				echo '<label for="'.$langue.'">'.$langue_str.'</label>';
				echo '</th>';
				echo '<td>';
				echo '<input type="text" id="tag_'.$langue.'" name="cat_'.$langue.'" value="'.$metas_lang[$langue].'"/>';
				echo '</td>';
				echo '</tr>';
			}
		}
	}
}
function post_tag_add_form_fields ($tag) {
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	if (is_object($tag)) {
		$t_id = $tag->term_id;
		$str_meta = get_option_tm( "taxonomy_$t_id");
		$aMetas = explode(';', $str_meta);
		$aLangs = array();
		if (count($aMetas) != 0) {
			foreach ($aMetas as $meta) {
				if ($meta != ''){
					$aLangs = explode('=', $meta);
					$metas_lang[$aLangs[0]] = $aLangs[1];
				}
			}
		}
	}

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	if (count($textmaster_langues) != 0) {
		echo '<h3>';
		echo __('Traductions','textmaster');
		echo '</h3>';
		foreach ($textmaster_langues as $langue) {
			if ($textmaster_langueDefaut != $langue) {
				if (!isset($metas_lang[$langue]))
					$metas_lang[$langue] = '';

				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
				echo '<div class="form-field">';
				echo '<label for="'.$langue.'">'.$langue_str.'</label>';
				echo '<input type="text" id="tag_'.$langue.'" name="cat_'.$langue.'" value="'.$metas_lang[$langue].'"/>';
				echo '</div>';
			}
		}
	}
}
function add_post_tag_columns($columns){
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	if (count($textmaster_langues) != 0) {
		foreach ($textmaster_langues as $langue) {
			if ($textmaster_langueDefaut != $langue) {
				$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
				$columns[$langue] = $langue_str;
			}
		}
	}

	return $columns;
}
function add_post_tag_column_content($content, $name , $term_id ){
	global $wpdb;

	$table_langues =  $wpdb->base_prefix .'tm_langues';

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	$str_meta = get_option_tm( "taxonomy_$term_id");
	$aMetas = explode(';', $str_meta);
	$aLangs = array();
	if (count($aMetas) != 0) {
		foreach ($aMetas as $meta) {
			if ($meta != ''){
				$aLangs = explode('=', $meta);
				$metas_lang[$aLangs[0]] = $aLangs[1];
			}
		}
	}

	if (isset($metas_lang[$name]) && $metas_lang[$name] != '')
		$content = 	$metas_lang[$name];
	else{
		$idProjetTradTM = get_IdProjetTrad($term_id, $name, 'term');
		$idTradTM = get_IdDocTrad($term_id, $name,'term');
		if ($idTradTM != '' && $name != get_option_tm('textmaster_langueDefaut')){
			$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjetTradTM.'" AND idDocument="'.$idTradTM.'"';
			$status = $wpdb->get_var( $req);

			$content = textmaster_api::getLibStatus($status); //__('En cours TM', 'textmaster'). print_r($post, true);
		}
		else
			$content = __('A traduire', 'textmaster');
	}


	return $content;
}
// save extra taxonomy fields callback function
function save_extra_taxonomy_fileds( $term_id ) {
	//print_r($_POST);
	//die();

	if (count($_POST)) {
		$t_id = $term_id;

		$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
		if (count($textmaster_langues) != 0) {
			$str_meta = '';
			foreach ($textmaster_langues as $langue) {
				if(isset( $_POST['cat_'.$langue] ))
					$str_meta .= $langue.'='.$_POST['cat_'.$langue].';';
				if(isset( $_POST['tag_'.$langue] ))
					$str_meta .= $langue.'='.$_POST['tag_'.$langue].';';
			}
			update_option_tm( "taxonomy_$t_id", $str_meta );
		}
	}

}

add_filter('get_terms', 'get_terms_filter', 10, 3);
function get_terms_filter( $terms, $taxonomies, $args ){
	global $wpdb;

//	print_r($terms);
//	print_r($taxonomies);
//	print_r($args);
//	echo get_query_var('lang');

	$lang = get_query_var('lang');
	if ($lang == ''){
		if (isset($_REQUEST['langTm']) && $_REQUEST['langTm'] != '')
			$lang = $_REQUEST['langTm'];
		else
			$lang = get_option_tm('textmaster_langueDefaut');
	}



	$taxonomy = $taxonomies[0];
	if ( ! is_array($terms) && count($terms) < 1 )
		return $terms;

	if ($lang != get_option_tm('textmaster_langueDefaut')) {
		if (count($terms) != 0) {
			foreach ($terms as $key =>$term) {
				$t_id = $term->term_id;
				$str_meta = get_option_tm( "taxonomy_$t_id");
				$aMetas = explode(';', $str_meta);
				$aLangs = array();
				if (count($aMetas) != 0) {
					foreach ($aMetas as $meta) {
						if ($meta != ''){
							$aLangs = explode('=', $meta);
							$metas_lang[$aLangs[0]] = $aLangs[1];
						}
					}
				}
				if (isset($metas_lang[$lang]))
					$terms[$key]->name = $metas_lang[$lang];
			}
		}
		return $terms;
	}
	else
		return $terms;
}


?>
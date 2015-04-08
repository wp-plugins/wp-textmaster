<?php
/*
Plugin Name: TextMaster plugin
Plugin URI: http://www.textmaster.com/wordpress-translation-plugin/?pid=5310711603e44f00020006d3
Description: Plugin for TextMaster copywriting, proofreading and translation services.
Author: TextMaster SA
Version: 2.1.1
Author URI: http://www.textmaster.com/?pid=5310711603e44f00020006d3
Text Domain: textmaster
*/

/*
 ToDo
   - mettre toutes les infos des projets TM dans la table tm_projets (supprimer tous les postmeta)
*/

include( plugin_dir_path( __FILE__ ). '/confs.php' );
//include( plugin_dir_path( __FILE__ ). '/confs.sandbox.php' );
include( plugin_dir_path( __FILE__ ). '/textmaster.class.php' );
include( plugin_dir_path( __FILE__ ). '/OAuth2.class.php' );
include( plugin_dir_path( __FILE__ ). '/fonctions.php' );
// on charge les fichiers qu'en admi (optimisation)
include( plugin_dir_path( __FILE__ ). '/tm-redaction.php' );
include( plugin_dir_path( __FILE__ ). '/tm-settings.php' );
include( plugin_dir_path( __FILE__ ). '/tm-relecture.php' );
include( plugin_dir_path( __FILE__ ). '/tm-traduction.php' );

if(!class_exists('WP_List_Table'))
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
if (get_option_tm('textmaster_useMultiLangues') == 'Y' ){
	include( plugin_dir_path( __FILE__ ). '/tm-multilangues.php' );
	include( plugin_dir_path( __FILE__ ). '/tm-front-multilangues.php' );
}
else
	include( plugin_dir_path( __FILE__ ). '/tm-projets.php' );

// pour verifir que curl est installé
function dependent_activate()
{

	if  (!in_array('curl', get_loaded_extensions()))
	{
		// deactivate dependent plugin
		deactivate_plugins( __FILE__);
		//   throw new Exception('Requires another plugin!');
		//  exit();
		exit (__('Merci d\'installer l\'extension crul pour php.','textmaster').' ( <a href="http://www.php.net/manual/en/curl.installation.php" target="_blank">http://www.php.net/manual/en/curl.installation.php</a> )');
	}
//	if ( is_multisite() ) {
//		deactivate_plugins( __FILE__);

//		exit (__('Ce sera multi-site bientôt ! ','textmaster'));

//	}
//	else
		textmaster_install();


}

function tm_deactivate() {
	flush_rewrite_rules();
}

function textmaster_install() {
	global $wpdb, $bk_db_version;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$table_name = $wpdb->base_prefix . "tm_projets";
	$table_categories =  $wpdb->base_prefix .'tm_categories';
	$table_langues =  $wpdb->base_prefix .'tm_langues';
	$table_templates =  $wpdb->base_prefix .'tm_templates';
	$table_languageLevels =  $wpdb->base_prefix .'tm_languageLevels';
	$table_reference_pricings =  $wpdb->base_prefix .'tm_reference_pricings';
	$table_auteurs =  $wpdb->base_prefix .'tm_auteurs';
	$table_support_messages =  $wpdb->base_prefix .'tm_support_messages';

$sqlProjets = "CREATE TABLE $table_name (
 			`id` varchar(100) NOT NULL,
 			`name` VARCHAR(250) NULL,
 			`language_from` VARCHAR(250) NULL,
 			`language_to` VARCHAR(250) NULL,
 			`category` VARCHAR(250) NULL,
 			`vocabulary_type` VARCHAR(250) NULL,
 			`target_reader_groups` VARCHAR(250) NULL,
 			`language_level` VARCHAR(250) NULL,
 			`expertise` VARCHAR(250) NULL,
 			`grammatical_person` VARCHAR(250) NULL,
 			`project_briefing` TEXT NULL,
 			`priority` VARCHAR(250) NULL,
 			`status` VARCHAR(250) NULL,
 			`total_word_count` VARCHAR(250) NULL,
 			`same_author_must_do_entire_project` VARCHAR(250) NULL,
 			`cost_in_credits` VARCHAR(250) NULL,
  			`ctype` VARCHAR(250) NULL,
  			`creation_channel` VARCHAR(250) NULL,
  			`reference` VARCHAR(250) NULL,
 			`work_template` VARCHAR(250) NULL,
 			`created_at` DATETIME NULL,
 			`updated_at` DATETIME NULL,
 			`completed_at` DATETIME NULL,
 			`launched_at` DATETIME NULL,
 			`idDocument` varchar(100) NOT NULL,
 			`archived` TINYINT(1) NULL,
 			PRIMARY KEY  (`id`, `idDocument`),
			UNIQUE KEY `id_tm_projets` (`id`, `idDocument`)
    		);";
	dbDelta( $sqlProjets );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) != $table_name)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_name.' ('.$err.')'), E_USER_ERROR);

	$sqlCategories = "CREATE TABLE $table_categories (
 			`code` varchar(250) NOT NULL,
 			`value` VARCHAR(250) NULL,
			UNIQUE KEY `code_categorie` (`code`)
    		);";
	dbDelta( $sqlCategories );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_categories'" ) != $table_categories)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_categories.' ('.$err.')'), E_USER_ERROR);


	$sqlLangues = "CREATE TABLE $table_langues (
 			`code` varchar(250) NOT NULL,
 			`value` VARCHAR(250) NULL,
			UNIQUE KEY `code_lang` (`code`)
    		);";
	dbDelta( $sqlLangues );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_langues'" ) != $table_langues)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_langues.' ('.$err.')'), E_USER_ERROR);

	$sqlTemplates = "CREATE TABLE $table_templates (
 			`name` varchar(250) NOT NULL,
 			`description` VARCHAR(250) NULL,
 			`image_preview_path` VARCHAR(250) NULL,
 			`ctype` VARCHAR(250) NULL,
			UNIQUE KEY `name_template` (`name`)
    		);";
	dbDelta( $sqlTemplates );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_templates'" ) != $table_templates)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_templates.' ('.$err.')'), E_USER_ERROR);

	$sqlLanguageLevels = "CREATE TABLE $table_languageLevels  (
 			`name` varchar(250) NOT NULL
    		);";
 			//UNIQUE KEY `name_language_level` (`name`)
	dbDelta( $sqlLanguageLevels );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_languageLevels'" ) != $table_languageLevels)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_languageLevels.' ('.$err.')'), E_USER_ERROR);

	$sqlReference_pricings = "CREATE TABLE $table_reference_pricings  (
				`type` varchar(100) NOT NULL,
				`name` varchar(250) NOT NULL,
				`value` varchar(250) NOT NULL,
				UNIQUE KEY `reference_pricings` (`type`, `name`)
				);";
	dbDelta( $sqlReference_pricings );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_reference_pricings'" ) != $table_reference_pricings)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_reference_pricings.' ('.$err.')'), E_USER_ERROR);

	$sqlAuteurs = "CREATE TABLE $table_auteurs  (
				`description` varchar(250) NOT NULL,
				`tags` varchar(250) NOT NULL,
				`status` varchar(250) NOT NULL,
				`id` varchar(100) NOT NULL,
				`author_id` varchar(250) NOT NULL,
				`author_ref` varchar(250) NOT NULL,
				`latest_activity` varchar(250) NOT NULL,
				`created_at` varchar(250) NOT NULL,
				`updated_at` varchar(250) NOT NULL,
				UNIQUE KEY `tm_auteurs` (`id`, `author_id`)
				);";
	dbDelta( $sqlAuteurs );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_auteurs'" ) != $table_auteurs)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_auteurs.' ('.$err.')'), E_USER_ERROR);

	$sqlSupportMsgs = "CREATE TABLE $table_support_messages  (
	 			`idProjet` varchar(100) NOT NULL,
 				`idDocument` varchar(100) NOT NULL,
				`content` TEXT NOT NULL,
				`message` TEXT NOT NULL,
				`author_id` varchar(100) NOT NULL,
				`written_by_you` varchar(250) NOT NULL,
				`written_by_author` varchar(250) NOT NULL,
				`author_ref` varchar(250) NOT NULL,
				`created_at` datetime NOT NULL,
				UNIQUE KEY `tm_support_msgs` (`idProjet`, `idDocument`, `created_at`, `author_id`)
				);";
	dbDelta( $sqlSupportMsgs );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_support_messages'" ) != $table_support_messages)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_support_messages.' ('.$err.')'), E_USER_ERROR);
	// id_wp_tm_project int(10) NOT NULL AUTO_INCREMENT,
	// PRIMARY KEY (id_wp_tm_project),
//	$keys = $wpdb->get_var('SHOW KEYS FROM  '.$table_name.' WHERE Key_name="id"');
//	print_r($keys);

	// supprime l'index unique existant avant les actions groupées
	$wpdb->query('DROP INDEX id ON '.$table_name.'');

	wp_schedule_single_event( time() + 1, 'cron_syncProjets' );

	updateTradMetas();
}

// mise à jour depuis la version 1.0 pour les trads
function updateTradMetas(){
	global $wpdb;

	$table_metas= $wpdb->prefix . "postmeta";

	$req = 'SELECT * FROM '.$table_metas.' WHERE meta_key="textmasterIdTrad"';
//	echo $req;
	$postsTrads = $wpdb->get_results( $req, ARRAY_A);
	if (count($postsTrads) != 0) {
		foreach ($postsTrads  as $post) {

			$idDoc = get_post_meta($post['post_id'],'textmasterDocumentIdTrad', TRUE);
			$lang = get_post_meta($post['post_id'],'textmasterLangDestination', TRUE);

			if (strpos($post['meta_value'], '=') === FALSE)
				update_post_meta($post['post_id'], 'textmasterIdTrad', $post['meta_value'].'='.$lang);
			if (strpos($idDoc, '=') === FALSE)
				update_post_meta($post['post_id'], 'textmasterDocumentIdTrad', $idDoc.'='.$lang);
		}
	}
}


function textmaster_javascript() {
	wp_enqueue_script(
	'custom-script',
	plugin_dir_url(__FILE__) . 'textmaster.js',
	array('jquery')
		);
}

function textmaster_callback() {

	if (isset($_POST['typeTxtMstr']) && $_POST['typeTxtMstr'] == 'traduction')
		callback_traduction();
	else if (isset($_POST['typeTxtMstr']) && $_POST['typeTxtMstr'] == 'proofread')
		callback_readproof();
	else if (isset($_POST['typeTxtMstr']) && $_POST['typeTxtMstr'] == 'redaction')
		callback_redaction();

}


function texmaster_add_metaboxes() {
/*	if (get_option_tm('textmaster_useTraduction') == 'Y')
	{
		//		add_meta_box('wp_textmaster_traduction', __('TextMaster traduction', 'textmaster'), 'wp_texmaster_traduction_metaboxes', 'post', 'side', 'default');
		//		add_meta_box('wp_textmaster_traduction', __('TextMaster traduction', 'textmaster'), 'wp_texmaster_traduction_metaboxes', 'page', 'side', 'default');

		$post_types = get_post_types();
		foreach ( $post_types as $post_type )
			if ($post_type != 'textmaster_redaction')
			{
				add_meta_box('wp_textmaster_traduction', __('TextMaster Traduction', 'textmaster'), 'wp_texmaster_traduction_metaboxes', $post_type, 'side', 'default');
				//		add_meta_box('wp_textmaster_options_traduction', __('Options TextMaster Traduction', 'textmaster'), 'wp_texmaster_traduction_options_metaboxes', $post_type, 'side', 'default');
			}


	}
	if (get_option_tm('textmaster_useReadproof') == 'Y')
	{
		//		add_meta_box('wp_textmaster_readproof', __('TextMaster Relecture', 'textmaster'), 'wp_texmaster_readproof_metaboxes', 'post', 'side', 'default');
		//		add_meta_box('wp_textmaster_readproof', __('TextMaster Relecture', 'textmaster'), 'wp_texmaster_readproof_metaboxes', 'page', 'side', 'default');

		$post_types = get_post_types();
		foreach ( $post_types as $post_type )
			if ($post_type != 'textmaster_redaction')
			{
				add_meta_box('wp_textmaster_readproof', __('TextMaster Relecture', 'textmaster'), 'wp_texmaster_readproof_metaboxes',  $post_type, 'side', 'default');
				//	add_meta_box('wp_textmaster_options_readproof', __('Options TextMaster Relecture', 'textmaster'), 'wp_texmaster_readproof_options_metaboxes',  $post_type, 'side', 'default');
			}


	}*/

	$post_types = get_post_types();
	foreach ( $post_types as $post_type )
		if ($post_type != 'textmaster_redaction')
		{
			if (get_option_tm('textmaster_useMultiLangues') == 'Y')
				add_meta_box('wp_textmaster_langues', __('Langues', 'textmaster'), 'wp_texmaster_multilangs_metaboxes', $post_type, 'side', 'core');
			if ($post_type != 'acf')
				add_meta_box('wp_textmaster_traduction', __('TextMaster', 'textmaster'), 'wp_texmaster_metaboxes', $post_type, 'side', 'core');
			//		add_meta_box('wp_textmaster_options_traduction', __('Options TextMaster Traduction', 'textmaster'), 'wp_texmaster_traduction_options_metaboxes', $post_type, 'side', 'default');


		}else {
			add_meta_box('wp_textmaster_redaction_defaut', __('Lancer la rédation', 'textmaster'), 'wp_texmaster_redaction_defaut_metaboxes', 'textmaster_redaction', 'side', 'default');
			//	add_meta_box('wp_textmaster_redaction_options', __('Options', 'textmaster'), 'wp_texmaster_redaction_options_metaboxes', 'textmaster_redaction', 'side', 'default');
			add_meta_box('wp_texmaster_redaction_templates', __('Mise en page', 'textmaster'), 'wp_texmaster_redaction_templates_metaboxes', 'textmaster_redaction', 'normal', 'default');
			//	add_meta_box('wp_texmaster_redaction_authors', __('Auteurs', 'textmaster'), 'wp_texmaster_redaction_authors_metaboxes', 'textmaster_redaction', 'side', 'default');
			remove_meta_box( 'submitdiv', 'textmaster_redaction', 'side' );
		}

	add_meta_box('wp_textmaster_redaction_defaut', __('Lancer la rédation', 'textmaster'), 'wp_texmaster_redaction_defaut_metaboxes', 'textmaster_redaction', 'side', 'default');
//	add_meta_box('wp_textmaster_redaction_options', __('Options', 'textmaster'), 'wp_texmaster_redaction_options_metaboxes', 'textmaster_redaction', 'side', 'default');
	add_meta_box('wp_texmaster_redaction_templates', __('Mise en page', 'textmaster'), 'wp_texmaster_redaction_templates_metaboxes', 'textmaster_redaction', 'normal', 'default');
//	add_meta_box('wp_texmaster_redaction_authors', __('Auteurs', 'textmaster'), 'wp_texmaster_redaction_authors_metaboxes', 'textmaster_redaction', 'side', 'default');
	remove_meta_box( 'submitdiv', 'textmaster_redaction', 'side' );
}

function wp_texmaster_metaboxes(){

	if (get_option_tm('textmaster_useTraduction') == 'Y' && get_option_tm('textmaster_useReadproof') == 'Y') {
		echo '<div id="box_select_activite">';
		echo '<label id="tm_activite">'.__('Activité:','textmaster').'</label>';
		echo '<select id="select_ActiviteTm" style="width:235px;">';
		echo '<option value="Traduction" selected="selected">'.__("Traduction ",'textmaster' ) .'</option>';
		echo '<option value="Relecture">'.__("Relecture ",'textmaster' ).'</option>';
		echo '</select><hr/>';
		echo '</div>';
	}

	if (get_option_tm('textmaster_useTraduction') == 'Y')
		wp_texmaster_traduction_metaboxes();
	if (get_option_tm('textmaster_useReadproof') == 'Y')
		wp_texmaster_readproof_metaboxes();
}

function texmaster_plugin_action_links($links, $file) {
	static $this_plugin;

	if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin && !is_multisite()) {
		// The "page" query string value must be equal to the slug
		// of the Settings admin page we defined earlier, which in
		// this case equals "myplugin-settings".
		$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=Textmaster">'. __( 'Réglages' ,'textmaster').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}




function texmaster_init() {
	global $domain;

	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$langs_dir = basename(dirname(__FILE__)) .'/I18n/';

	// si la traduction existe
	if (file_exists(WP_PLUGIN_DIR .'/'.$langs_dir. 'textmaster-' . $locale . '.mo'))
		load_plugin_textdomain( 'textmaster', false, $langs_dir );
	else
		load_textdomain( 'textmaster', WP_PLUGIN_DIR.'/'. $langs_dir. 'textmaster-en_US.mo');

}

function texmaster_tiny_mce_before_init( $initArray ){
	$initArray['setup'] = <<<JS
[function(ed) {
	ed.onKeyDown.add(function(ed, e) {
		  	getPrice('readproof');
		  	getPrice('traduction');
	});

}][0]
JS;
	return $initArray;
}

function enqueue_textmaster_admin_scripts() {

	wp_enqueue_style('wp-pointer');
	wp_enqueue_script('wp-pointer');
	add_action('admin_print_footer_scripts', 'textmaster_print_footer_scripts' );

	add_thickbox();

}

function textmaster_print_footer_scripts() {

	if (!isset($_SESSION['AlerteTradRead']))
		$_SESSION['AlerteTradRead'] = '';
	if (!isset($_SESSION['AlerteRedaction']))
		$_SESSION['AlerteRedaction'] = '';

//	echo  time() - $_SESSION['lastSyncTmProjets'];
	// on resyncro les projet toutes les x temps
//	if ($_SESSION['lastSyncTmProjets'] == '' ||  time() - $_SESSION['lastSyncTmProjets'] > TMPS_SYNC_PROJETS)
//	syncProjets();
	$ret = array();
	$checkProjets = checkProjetsStatus();

	?>
	<script type="text/javascript">
	jQuery(document).ready( function($) {
		$('#menu-posts-textmaster_redaction a.menu-top').attr('href','edit.php?post_type=textmaster_redaction&page=textmaster-projets');
		<?php if (get_option_tm('textmaster_useRedaction') != 'Y') { ?>
			$('#menu-posts-textmaster_redaction ul li:nth-child(3)').hide();
		<?php } ?>
	});
	</script>
	<?php

	if (isset($ret['new']) && $ret['new'] == TRUE)
		 $_SESSION['AlerteTradRead'] = '';


	if ((isset($checkProjets['TradRead'] ) && $checkProjets['TradRead']) && $_SESSION['AlerteTradRead'] != 'Done') {
		$pointer_content = '<h3>'.__('Informations TextMaster','textmaster').'</h3>';
		$pointer_content .= '<p>'.__('De nouvelles traduction ou relecture sont disponibles. Vous pouvez les valider dès maintenant.','textmaster').'<ul style="padding-left:15px">';
		foreach ($checkProjets['ListeTradReads'] as $tradReads) {
			$pointer_content .= '<li><a href="post.php?post='.$tradReads.'&action=edit">'.get_the_title($tradReads).'</a></li>';
		}
		$pointer_content .= '</ul></p>'
		?>
		<script type="text/javascript">
		jQuery(document).ready( function($) {
		    $('#menu-posts').pointer({
		        content: '<?php echo $pointer_content; ?>',
		        position: 'top',
		        close: function() {
		            // This function is fired when you click the close button
		        }
		      }).pointer('open');
		   });
		</script>
		<?php
		$_SESSION['AlerteTradRead'] = 'Done';
	}

	if ((isset($checkProjets['Redaction'] ) && $checkProjets['Redaction']) && $_SESSION['AlerteRedaction'] != 'Done') {
		$pointer_content = '<h3>'.__('Informations TextMaster','textmaster').'</h3>';
		$pointer_content .= '<p>'.__('De nouvelles rédactions sont disponibles. Vous pouvez les valider dès maintenant.','textmaster').'<ul style="padding-left:15px">';
		foreach ($checkProjets['ListeRedactions'] as $redaction) {
			$pointer_content .= '<li><a href="post.php?post='.$redaction.'&action=edit">'.get_the_title($redaction).'</a></li>';
		}
		$pointer_content .= '</ul></p>'
		?>
		<script type="text/javascript">
		jQuery(document).ready( function($) {
		    $('#menu-posts-textmaster_redaction').pointer({
		        content: '<?php echo $pointer_content; ?>',
		        position: 'top',
		        close: function() {
		            // This function is fired when you click the close button
		        }
		      }).pointer('open');
		   });
		</script>
		<?php
		$_SESSION['AlerteRedaction'] = 'Done';
	}
}

function get_tm_plugin_version(){
	if (function_exists('get_plugin_data'))
	{
		$pluginfo = get_plugin_data( __FILE__ );
		$ret = $pluginfo['Version'];
	}
	else
	{
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		$ret = $plugin_folder[$plugin_file]['Version'];
	}

	return $ret;
}

function session_manager() {
	if (!session_id()) {
		session_start();
	}
}

function session_logout() {
	session_destroy();
}

function set_texmaster_columns() {
	global $pagenow;

	if ( isset($_GET['post_type']) && $_GET['post_type'] == 'page' ){
		add_filter('manage_pages_columns', 'texmaster_columns_head');
		add_action('manage_pages_custom_column', 'texmaster_columns_content', 10, 2);
	}
	else if (!isset( $_GET['post_type'] ) ||  $_GET['post_type'] != 'textmaster_redaction') {
		/*$filter = 'manage_'.$_GET['post_type'] .'_posts_columns';
		   add_filter($filter, 'texmaster_columns_head');
		   $action = 'manage_'.$_GET['post_type'] .'_posts_custom_column';
		   add_action($action, 'texmaster_columns_content', 10, 2);*/
		add_filter('manage_posts_columns', 'texmaster_columns_head');
		add_action('manage_posts_custom_column', 'texmaster_columns_content', 10, 2);
	}
	else if ($pagenow == 'edit.php' && isset( $_GET['post_type'] ) &&  $_GET['post_type'] != 'textmaster_redaction') {
		add_filter('manage_posts_columns', 'texmaster_columns_head');
		add_action('manage_posts_custom_column', 'texmaster_columns_content', 10, 2);
	}
}

function texmaster_columns_head($columns) {
	global $wpdb;
	//	$new = array();
	$new = $columns;
	//	foreach($columns as $key => $title) {
	//		if ($key=='comments')

	if (get_option_tm('textmaster_useMultiLangues') == 'Y'){
		$lang_filtre = get_query_var('lang');

		$table_langues =  $wpdb->base_prefix .'tm_langues';

		$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
		$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$textmaster_langueDefaut'" );
		$columns[$textmaster_langueDefaut] = $langue_str;

		$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
		if (count($textmaster_langues) != 0) {
			foreach ($textmaster_langues as $langue) {
				if ($lang_filtre != $langue) {
					$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$langue'" );
					$new[$langue] = $langue_str;
				}
			}
		}
	}else
		$new['texmaster_status'] = __('Status TextMaster', 'textmaster');


	$new[$key] = $title;
	//	}
	return $new;

}

function texmaster_columns_content($column_name, $postID) {
	global $wpdb;

//	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	if (get_option_tm('textmaster_useMultiLangues') == 'Y' && $column_name != 'texmaster_status'){
		$lang = get_query_var('lang');
		$idTrad = '';
		$idProjetTrad = '';

		if ($lang == get_option_tm('textmaster_langueDefaut')) {
			$idTrad =  get_IdTrad($postID, $column_name);
			$idProjetTrad =  get_IdProjetTrad($postID, $column_name);
			$idDocTrad =  get_IdDocTrad($postID, $column_name);
		} else {
			$fIdTrad = $wpdb->get_var( "select meta_value from $wpdb->postmeta where meta_key = 'tm_lang' AND post_id='$postID'" );
			$aIdTrad = explode(';', $fIdTrad);
			if (get_option_tm('textmaster_langueDefaut') == $column_name){
				$idTrad = $aIdTrad[0];
				$idDocTrad = '';
			}


			$postID = $aIdTrad[0];
		}

		if ($idTrad != '' || $idProjetTrad != ''){
			$title = get_the_title($idTrad);
			$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjetTrad.'" AND idDocument="'.$idDocTrad.'"';
			$status = $wpdb->get_var( $req);
			$tmStatut = textmaster_api::getLibStatus($status);
			if ($idTrad != ''){
		//		$supportMsgs = $tApi->getSupportMsgs($idProjetTrad, $idDocTrad, TRUE);
				$table_support_messages = $wpdb->base_prefix . 'tm_support_messages';
				$req = $wpdb->prepare('SELECT * FROM ' . $table_support_messages. ' WHERE idProjet=%s AND idDocument=%s  ORDER BY created_at', $idProjetTrad, $idDocTrad);
				$supportMsgs["support_messages"] = $wpdb->get_results($req, ARRAY_A);

				if ($status == 'incomplete' && count($supportMsgs["support_messages"]) != 0)
					echo '<a href="post.php?post='.$idTrad.'&lang='.$column_name.'&action=edit">'.$title.'</a><br/>'.count($supportMsgs["support_messages"]).' '.__('Message(s)', 'textmaster');
				else
					echo '<a href="post.php?post='.$idTrad.'&lang='.$column_name.'&action=edit">'.$title.'</a><br/>'.get_the_date(__( 'Y/m/d' ), $idTrad).'<br/>'.get_post_status( $idTrad ).' - '.$tmStatut;
			}
			else{

				if ($tmStatut == 'NC')
					echo '<a href="post.php?post='.$postID.'&langTm='.$column_name.'&action=edit">'.__('A traduire', 'textmaster' ).'</a>';
				else if ($status == 'in_review')
					echo '<a href="#" onclick="valideTrad(\''.$postID.'\', \''.$idProjetTrad.'\', \''.$idDocTrad.'\', true, \''.__('Merci de patienter...', 'textmaster').'\',\''.get_current_blog_id().'\');">'.$tmStatut.'</a>';

				else
					echo $tmStatut;
			}

		}
		else
			echo '<a href="post.php?post='.$postID.'&langTm='.$column_name.'&action=edit">'.__('A traduire', 'textmaster' ).'</a>';
		//	echo 'NC';
	}
	else {
		if ($column_name == 'texmaster_status') {
			if (get_option_tm('textmaster_useTraduction') == 'Y' && get_option_tm('textmaster_useMultiLangues')!= 'Y') {
				$tmStatut ='';
				if (checkInstalledPlugin('WPML Multilingual CMS')) {
					$aLangsIcl = icl_get_languages();
					if (count($aLangsIcl) != 0) {
						foreach ($aLangsIcl as $langsIcl) {
							if($langsIcl['active'] != 1){
								$idProjet = get_IdProjetTrad($postID, $langsIcl['language_code']);
								$idDocument = get_IdDocTrad($postID, $langsIcl['language_code']);
								$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjet.'" AND idDocument="'.$idDocument.'"';
								$status = $wpdb->get_var( $req);
								$strStatus = textmaster_api::getLibStatus($status);
								if ($strStatus == 'NC')
									$strStatus  =__('A traduire/relire', 'textmaster');
								$tmStatut .= '<br/>'.$langsIcl['language_code']. ': '.$strStatus;
							}

						}
					}
				}else {
					//$idProjet = get_post_meta($postID,'textmasterIdTrad', TRUE);
					$idProjet = get_IdProjetTrad($postID, '');
					$idDocument = get_IdDocTrad($postID, '');
					$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjet.'" AND idDocument="'.$idDocument.'"';
					$status = $wpdb->get_var( $req);
					$tmStatut = textmaster_api::getLibStatus($status);

					if ($tmStatut == 'NC')
						$tmStatut  =__('A traduire/relire', 'textmaster');

				}

				//	$tmStatut = $tApi->getLibStatus($tApi->getProjetStatus($idProjet));
				echo __('Traduction', 'textmaster' ) .' : '.$tmStatut.'<br/>';
			}

			if ( get_option_tm('textmaster_useReadproof') == 'Y') {

				$idProjet = get_post_meta($postID,'textmasterId', TRUE);
				$idDocument = get_post_meta($postID,'textmasterDocumentId', TRUE);
				$req = 'SELECT status FROM '.$wpdb->base_prefix.'tm_projets WHERE id="'.$idProjet.'" AND idDocument="'.$idDocument.'"';
				$status = $wpdb->get_var( $req);
				$tmStatut = textmaster_api::getLibStatus($status);

				if ($tmStatut == 'NC')
					$tmStatut  =__('A traduire/relire', 'textmaster');

				//$tmStatut = $tApi->getLibStatus($tApi->getProjetStatus($idProjet));
				echo __('Relecture', 'textmaster' ) .' : '.$tmStatut;
			}
		}
	}

}

function texmaster_bulk_actions($actions){
	global $wpdb, $post_type;

	//	if($post_type == 'post' || $post_type == 'page') {
	if ($post_type != 'textmaster_redaction') {
		if (get_option_tm('textmaster_useTraductionBulk') == 'Y' || get_option_tm('textmaster_useTraductionBulk') == '' ){
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('textmaster_TraductionBulk').text('<?php _e('Traduction TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
					jQuery('<option>').val('textmaster_TraductionBulk').text('<?php _e('Traduction TextMaster', 'textmaster') ?>').appendTo("select[name='action2']");
					jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_TraductionBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('traduction_bulk.php', __FILE__); ?>?post_ids=' + ids + '&height=500&width=630&TB_iframe=true';
							if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_TraductionBulk' )
								tb_show('<?php _e('Traduction TextMaster', 'textmaster') ?>', urlPopup);
								event.preventDefault();
						}
					});
				});
			</script>
			<?php
		}
		if (get_option_tm('textmaster_useReadproofBulk') == 'Y' || get_option_tm('textmaster_useReadproofBulk') == ''){
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('textmaster_ReadproofBulk').text('<?php _e('Relecture TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
					jQuery('<option>').val('textmaster_ReadproofBulk').text('<?php _e('Relecture TextMaster', 'textmaster') ?>').appendTo("select[name='action2']");
					jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ReadproofBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('readproof_bulk.php', __FILE__); ?>?post_ids=' + ids + '&height=500&width=630&TB_iframe=true';
							tb_show('<?php _e('Relecture TextMaster', 'textmaster') ?>' , urlPopup);
							event.preventDefault();
						}
					});
				});

			</script>
			<?php
		}

		// ajout des filtres / langues
		if (get_option_tm('textmaster_useMultiLangues') == 'Y'){
			$table_langues =  $wpdb->base_prefix .'tm_langues';
			$html = '';

			$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
			$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));

			if (count($textmaster_langues) != 0) {
				$html .= '<ul class="lang_menus" style="margin:0">';
				foreach ($textmaster_langues as $lang) {
					$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$lang'" );
					$post_type = get_query_var('post_type');
					$lang_active = get_query_var('lang');
					if ($lang == $lang_active) {
						$html .= '<li><strong>'.$langue_str.'</strong></li>';
					}
					else if ($post_type != '')
						$html .= '<li><a href="edit.php?post_type='.$post_type.'&lang='.$lang.'">'.$langue_str.'</a></li>';
					else
						$html .= '<li><a href="edit.php?lang='.$lang.'">'.$langue_str.'</a></li>';
				}
				$html .= '</ul>';
			}
			?>
			<script>window.urlPlugin = "<?php echo plugins_url('', __FILE__) ?>"; window.urlAdmin = "<?php echo admin_url() ?>";</script>';
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('ul.subsubsub').before('<?php echo $html;?>');
					jQuery('<option>').val('textmaster_ValidationBulk').text('<?php _e('Validation TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
					jQuery('#doaction, #doaction2').click(function(event){
						var n = jQuery(this).attr('id').substr(2);
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' ){
							var ids = '';
							jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
								if ( jQuery(this).prop('checked') )
									ids = ids + ';' + jQuery(this).val();
							});
							var urlPopup = '<?php echo plugins_url('validation_bulk.php', __FILE__); ?>?post_ids=' + ids + '&height=400&width=600&TB_iframe=true';
							if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' )
								tb_show('<?php _e('Validation TextMaster', 'textmaster') ?>', urlPopup);
								event.preventDefault();
						}
					});
				});
			</script>
			<?php
		}
	}
	return $actions;
}

function texmaster_bulk_actions_tags($actions){
	?>
	<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('textmaster_Bulk').text('<?php _e('Traduction TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
				<?php	if (get_option_tm('textmaster_useMultiLangues') == 'Y'){ ?>
				jQuery('<option>').val('textmaster_ValidationBulk').text('<?php _e('Validation TextMaster', 'textmaster') ?>').appendTo("select[name='action']");
				<?php } ?>
				jQuery('#doaction, #doaction2').click(function(event){
					var n = jQuery(this).attr('id').substr(2);
					if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_Bulk' ){
						var ids = '';
						jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
							if ( jQuery(this).prop('checked') )
								ids = ids + ';' + jQuery(this).val();
						});
						var urlPopup = '<?php echo plugins_url('traduction_bulk.php', __FILE__); ?>?post_ids=' + ids + '&type=<?php echo $_GET['taxonomy']; ?>&&height=500&width=630&TB_iframe=true';
						tb_show('<?php _e('Traduction TextMaster', 'textmaster') ?>' , urlPopup);
						event.preventDefault();
					}
				<?php	if (get_option_tm('textmaster_useMultiLangues') == 'Y'){ ?>
					if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' ){
						var ids = '';
						jQuery('tbody th.check-column input[type="checkbox"]').each(function(){
							if ( jQuery(this).prop('checked') )
								ids = ids + ';' + jQuery(this).val();
						});
						var urlPopup = '<?php echo plugins_url('validation_bulk.php', __FILE__); ?>?trad_only=yes&post_ids=' + ids + '&type=<?php echo $_GET['taxonomy']; ?>&height=400&width=600&TB_iframe=true';
						if ( jQuery('select[name="'+n+'"]').val() == 'textmaster_ValidationBulk' )
							tb_show('<?php _e('Validation TextMaster', 'textmaster') ?>', urlPopup);
							event.preventDefault();
					}
				<?php } ?>
				});

			});
	</script>
	<?php
}

function texmaster_actions_menus(){
	global $wpdb, $nav_menu_selected_id ;

//	$locations = get_nav_menu_locations();
	$id = $nav_menu_selected_id;
//	var_dump( $nav_menu_selected_id );

	$table_langues =  $wpdb->base_prefix .'tm_langues';
	$table_options =  $wpdb->prefix .'options';
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	$lang_menu = '';
	$id_origine = '';
//	echo $nav_menu_selected_id;
	$fLang_menu = get_option_tm("menu_lang_$id");
//	echo $fLang_menu;
	if ($fLang_menu != '') {
		$alang_menu = explode(';', $fLang_menu);
		$lang_menu = $alang_menu[0];
		if ($alang_menu[1] != 0)
			$id_origine = $alang_menu[1];
	}
	if ($lang_menu == '' && isset($_GET['langTmMenu']))
		$lang_menu = $_GET['langTmMenu'];

	if ($id_origine == '')
		$id_origine = $id;


//	echo $id_origine;

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));
	if (count($textmaster_langues) != 0) {
		$strHtml = ' '.__('Langues', 'textmaster').' <select name="tm_lang">';
		$strLi = ' '.__('Traduire en ', 'textmaster').' <ul class="lang_menus">';
		foreach ($textmaster_langues as $lang) {
			$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$lang'" );

			$selected = '';
			if ($lang_menu == $lang)
				$selected = 'selected="selected"';
			$id_trad = $wpdb->get_var("SELECT option_name FROM wp_options WHERE option_name LIKE 'menu_lang_%' AND option_value='$lang;$id_origine'");
			//echo "SELECT option_name FROM wp_options WHERE option_name LIKE 'menu_lang_%' option_value='$lang;$id'";
			$strHtml .=  '<option value="'.$lang.'" '.$selected.'>'.$langue_str.'</option>';
			if ($id_trad != '') {
				$id_trad = str_replace('menu_lang_','', $id_trad);
				$strLi .=  '<li><a href="nav-menus.php?action=edit&menu='.$id_trad.'">'.$langue_str.'</a></li>';
			}
			else if (($lang_menu != $lang  && $textmaster_langueDefaut != $lang))
				$strLi .=  '<li><a href="nav-menus.php?action=edit&menu=0&id_origine='.$id_origine.'&langTmMenu='.$lang.'">'.$langue_str.'</a></li>';

		}
		$strHtml .= '</select>';
		$strLi .= '</ul>';
	}

//	$id_origine = '';
	if (isset($_GET['id_origine']))
		$id_origine = $_GET['id_origine'];
	?>
	<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#nav-menu-header .major-publishing-actions').append('<label class="menu-name-label open-label"><?php echo $strHtml ?></label><label class="menu-name-label open-label"><?php echo $strLi ?></label><input type="hidden" name="id_origine" value="<?php echo $id_origine; ?>" />');
			});
	</script>
	<?php
}


function check_nav_menu_updates( $id ){
//	global $nav_menu_selected_id;

/*	var_dump($action);
	if ( ( 'update-nav_menu' != $action ) )//or ! isset( $_POST['menu-locations'] ) )
	{
		return;
	}
*/
//	var_dump($nav_menu_selected_id);
//	var_dump($id);
//	$id = $_POST['menu'];
	$lang     = $_POST['tm_lang'];
	$id_origine     = $_POST['id_origine'];

	update_option_tm( "menu_lang_$id", $lang.';'. $id_origine);
	// do something awesome with it.
}

function tm_get_nav_menus($menus){
	global $wpdb;
	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
//	var_dump($menus);
	if (count($menus) != 0) {
		foreach ($menus as $key => $menu) {
			if (is_object($menu)) {
				$fLang_menu = get_option_tm("menu_lang_$menu->term_id");
				if ($fLang_menu != '') {
					$alang_menu = explode(';', $fLang_menu);
					$lang_menu = $alang_menu[0];
					$id_origine = $alang_menu[1];
					if ($lang_menu!= $textmaster_langueDefaut) {
						unset($menus[$key]);
					}
				}
			}
		}
	}
//	var_dump($menus);

	return $menus;
}

function tm_nav_menu_objects($objs){
	global $wpdb;

	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
//	var_dump($objs);
	$lang = get_query_var('lang');
	$locations = get_nav_menu_locations();
	$ret = $objs;

	if ($lang != '' && $lang != $textmaster_langueDefaut)  {
//		var_dump($objs);

		if (count($locations) != 0) {
			foreach ($locations as $key => $id ) {
				$id_trad = $wpdb->get_var("SELECT option_name FROM wp_options WHERE option_name LIKE 'menu_lang_%' AND option_value='$lang;$id'");
				$id_trad = str_replace('menu_lang_','', $id_trad);
				$ret = wp_get_nav_menu_items( $id_trad, array( 'update_post_term_cache' => false ) );

				if ($ret != FALSE) {
					foreach ($ret as $key => $item) {
						$urlSite = site_url();
						if (strpos($ret[$key]->url, $urlSite.'/'.$textmaster_langueDefaut ) === FALSE)
							$ret[$key]->url = str_replace($urlSite, $urlSite.'/'.$lang ,$ret[$key]->url);
					}
				}
	//			var_dump($ret);
			}
		}
//
	}else {
		if (count($ret) != 0) {
			foreach ($ret as $key => $item) {
				$urlSite = site_url();
				if (strpos($ret[$key]->url, $urlSite.'/'.$textmaster_langueDefaut ) === FALSE)
					$ret[$key]->url = str_replace($urlSite, $urlSite.'/'.$textmaster_langueDefaut ,$ret[$key]->url);
			}
		}
	}

//	var_dump($ret);
	return $ret;
}
/*
function tm_nav_menu_pages($objs){
	global $wpdb, $nav_menu_selected_id ;
	//echo ' - '.$nav_menu_selected_id.' - ';

	$ret = $objs;

	$textmaster_langueDefaut = get_option_tm('textmaster_langueDefaut');
	$fLang_menu = $wpdb->get_var("SELECT option_value FROM wp_options WHERE option_name='menu_lang_$nav_menu_selected_id'");
	if ($fLang_menu != '') {
		$alang_menu = explode(';', $fLang_menu);
		$lang_menu = $alang_menu[0];
		$id_origine = $alang_menu[1];
	}

	if ($lang_menu != '' && $lang_menu != $textmaster_langueDefaut)  {
		$req = 'SELECT * FROM '.$wpdb->prefix.'post JOIN
					'.$wpdb->prefix.'postmeta ON '.$wpdb->prefix.'post.ID='.$wpdb->prefix.'postmeta.post_id
					WHERE meta_key="tm_lang" OR meta_value LIKE "%;'.$lang_menu.'%"';
		echo $req;
		$results = $wpdb->get_results( $req);
		echo ' - '.$lang_menu.' - ';
		var_dump($results);
	}





	return $ret;
}
*/
// pour avoir les infos des posts concernés
function getInfosPosts($ids, $type='', $site_id='')
{
	$tApi = new textmaster_api(get_option_tm('textmaster_api_key'), get_option_tm('textmaster_api_secret'));

	$aRet = false;
	if ($site_id != '' && is_multisite())
		$ret = switch_to_blog( $site_id );

	$aIds = explode(';', $ids);
	if (count($aIds) != 0) {
		$numPost = 0;
		$totalWords = 0;
		foreach ($aIds as $id) {
			$fullContent = '';
			if (intval($id)) {
				$aRet[$numPost]['id'] = $id;
				if ($type == '' || $type == 'post'){
					$aRet[$numPost]['content'] = get_post($id);

					if( checkInstalledPlugin('Advanced Custom Fields')) {
						$fields = getExtrasFields($id);
						if (count($fields) != 0) {
							foreach ( $fields as $name => $param) {
								if ($name != 'acf_nonce' && trim($param) != ''){
									$fullContent .= cleanWpTxt( $param );
								}

							}
						}
					}else if(  checkInstalledPlugin('Meta Box')) {
						$fields = getExtrasFields($id, 'metabox');
						if (count($fields) != 0) {
							foreach ( $fields as $name => $param) {
								if (is_string($param) && trim($param) != ''){
									$fullContent .= cleanWpTxt( $param );
								}
							}
						}
					}
					$string = cleanWpTxt($aRet[$numPost]['content']->post_content);
					$aRet[$numPost]['wordsCount'] = $tApi->countWords($aRet[$numPost]['content']->post_title.' '.$string.' '.$fullContent);
				}else {
					$taxonomies=get_taxonomies();
					foreach ($taxonomies as $taxonomy) {
						$aRet[$numPost]['content'] = get_term($id, $taxonomy);
						if (!is_wp_error($aRet[$numPost]['content']) && is_object($aRet[$numPost]['content'])) {
							$string = cleanWpTxt($aRet[$numPost]['content']->name);
							$aRet[$numPost]['wordsCount'] = $tApi->countWords($string);
							break;
						}
					}
				}
				//		echo $string.'<br>';
				//		$aRet[$numPost]['wordsCount'] = str_word_count($string, 0, "àâäéèêëïîöôùüû");
				//		echo $aRet[$numPost]['wordsCount'] .'<br>';
				if (isset($aRet[$numPost]['wordsCount']))
					$totalWords += $aRet[$numPost]['wordsCount'];
				$numPost++;
			}
		}
		if ($totalWords != 0)
			$aRet['totalWords'] = $totalWords;
	}
	if ($site_id != '' && is_multisite())
		restore_current_blog();

	return $aRet;
}


function textmaster_admin_posts_filter_restrict_manage_posts(){
	global $wpdb, $post_type;

	//	if($post_type == 'post' || $post_type == 'page') {
	if ($post_type != 'textmaster_redaction') {
		$req = 'SELECT DISTINCT meta_value FROM '.$wpdb->prefix.'postmeta WHERE meta_key="textmasterStatusReadproof" OR meta_key="textmasterStatusTrad"';
		$results = $wpdb->get_results( $req);
		if (count($results) != 0)
		{
			foreach ($results as $post) {
				if ($post->meta_value != '')
					$arrayStatus[$post->meta_value] = textmaster_api::getLibStatus($post->meta_value);
			}
		}
		$current_v = isset($_GET['textmaster_status'])? $_GET['textmaster_status']:'';
		?>
        <select name="textmaster_status" onChange="jQuery('#posts-filter').submit();">
        <option value=""><?php _e('Voir tous les status TextMaster', 'textmaster'); ?></option>
        <option value="notTM" <?php  echo 'notTM' == $current_v? ' selected="selected"':'' ?>><?php _e('A traduire/relire', 'textmaster'); ?></option>
        <?php

		foreach ($arrayStatus as $value => $label) {
			printf
			    (
			        '<option value="%s"%s>%s</option>',
			        $value,
			        $value == $current_v? ' selected="selected"':'',
			        $label
			    );
		}
		?>
        </select>
        <?php
	}
}

function textmaster_posts_filter( $query ){
	global $pagenow, $post_type, $wpdb;



	//if ( ($post_type == 'post' || $post_type == 'page') && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '') {
//	if ($post_type != 'textmaster_redaction'  && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '' && $_GET['textmaster_status'] != 'notTM'){
		//	$query->query_vars['meta_key'] = array('textmasterStatusReadproof', 'textmasterStatusTrad');
		//	$query->query_vars['meta_key'] = "IN ('textmasterStatusReadproof','textmasterStatusTrad')";
		//$query->query_vars['meta_key'] = 'textmasterStatusTrad';
//		$query->query_vars['meta_value'] =  array($_GET['textmaster_status'], $_GET['textmaster_status']);
		//	$query->query_vars['meta_value'] = $_GET['textmaster_status'];
//		print_r($query);
		//		echo $query->query;
		//		$query->parse_query();
		//		echo $query->query;
//	}

}

function textmaster_posts_where($where){
	global $pagenow, $wpdb, $post_type, $wp_query;

	$table_meta = $wpdb->prefix . "postmeta";

	if($pagenow=='edit.php' || $pagenow=='edit-pages.php'){
		$lang = get_query_var('lang');

		if ($post_type != 'textmaster_redaction'  && is_admin() && $pagenow=='edit.php' && isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '' && $_GET['textmaster_status'] == 'notTM'){
			$where .= ' AND '.$wpdb->prefix.'posts.ID NOT IN (SELECT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key LIKE "%textmasterId%")';
		}
		if ($lang == get_option_tm('textmaster_langueDefaut') && get_option_tm('textmaster_useMultiLangues') == 'Y' )
			$where .= " AND ".$wpdb->prefix."posts.ID NOT IN (SELECT post_id FROM ".$table_meta." WHERE meta_key='tm_lang' AND meta_value NOT LIKE '%".get_option_tm('textmaster_langueDefaut')."' )";
		else if ( get_option_tm('textmaster_useMultiLangues') == 'Y' )
			$where .= " AND ".$wpdb->prefix."posts.ID IN (SELECT post_id FROM ".$table_meta." WHERE meta_key='tm_lang' AND meta_value LIKE '%".$lang."' )";

		if (isset($_GET['textmaster_status']) && $_GET['textmaster_status'] != '') {
			$table_meta = $wpdb->prefix . "postmeta";
			$table_Projet = $wpdb->base_prefix . "tm_projets";
			$table_name = $wpdb->prefix . "posts";

			if ($_GET['textmaster_status'] == 'notTM') {
				$where .= ' AND '.$wpdb->prefix.'posts.ID IN (SELECT DISTINCT '.$wpdb->prefix.'posts.ID FROM  '.$wpdb->prefix.'posts
							LEFT JOIN '.$table_meta.' ON '.$table_meta.'.post_id='.$table_name.'.ID AND meta_key="textmasterDocumentIdTrad"
							LEFT JOIN '.$table_Projet.' ON  '.$table_meta.'.meta_value LIKE CONCAT(\'%\','.$table_Projet.'.idDocument ,\'%\')
							WHERE '.$table_Projet.'.status IS NULL)';

			}else {
				$where .= ' AND '.$wpdb->prefix.'posts.ID IN (SELECT DISTINCT '.$wpdb->prefix.'posts.ID FROM  '.$wpdb->prefix.'posts
							JOIN '.$table_meta.' ON '.$table_meta.'.post_id='.$table_name.'.ID AND meta_key="textmasterDocumentIdTrad"
							JOIN '.$table_Projet.' ON  '.$table_meta.'.meta_value LIKE CONCAT(\'%\','.$table_Projet.'.idDocument ,\'%\') AND '.$table_Projet.'.status="'.$_GET['textmaster_status'] .'")';
			}

		}
	}
	else if (get_option_tm('textmaster_useMultiLangues') == 'Y' ) {
		// la langue
		if (is_object($wp_query))
			$lang = get_query_var('lang');
		if ((!$lang || $lang == '') && isset($_GET['langTm']))
			$lang = $_GET['langTm'];
		if ((!$lang || $lang == '') && isset($_GET['post'])){
			$langTrad = $wpdb->get_var( "select meta_value from $wpdb->postmeta where post_id='".$_GET['post']."' AND meta_key = 'tm_lang'" );
			//echo  "select meta_value from $wpdb->postmeta where post_id='".$_GET['post']."' meta_key = 'tm_lang'";
		//	echo $langTrad;
			$langs = explode(';', $langTrad);
			if (is_array($langs) && isset( $langs[1]))
				$lang = $langs[1];
		}

		if(!$lang || $lang == '') $lang = get_option_tm('textmaster_langueDefaut');
		// determine post type
		if(empty($post_type)){
			$db = debug_backtrace();
			foreach($db as $o){
				if($o['function']=='apply_filters_ref_array' && $o['args'][0]=='posts_where'){
					$post_type =  $o['args'][1][1]->query_vars['post_type'];
					break;
				}
			}
		}


		// case of taxonomy archive
		if(empty($post_type) && is_tax()){
			$tax = get_query_var('taxonomy');
			$post_type = $wp_taxonomies[$tax]->object_type;
			if(empty($post_type)) return $where;  // don't filter
		}

		if(!$post_type) $post_type = 'post';

		if('any' != $post_type){
			if ($lang != get_option_tm('textmaster_langueDefaut'))
				$where .= " AND ".$wpdb->prefix."posts.ID IN (SELECT post_id FROM ".$table_meta." WHERE meta_key='tm_lang' AND meta_value LIKE '%".$lang."')";
			else
				$where .= " AND ".$wpdb->prefix."posts.ID NOT IN (SELECT post_id FROM ".$table_meta." WHERE meta_key='tm_lang')";
		}
	}



//	echo $where;
	return $where;
}

function tm_scripts() {
	wp_enqueue_style( 'textmaster',  plugins_url( '/css/front.css', __FILE__ ));
}

function tm_config_notice() {

	if (get_option_tm('textmaster_email') == '' && get_option_tm('textmaster_password') == '') {
		if( is_multisite() )
			$url = network_admin_url('settings.php?page=texmaster_admin');
		else
			$url='options-general.php?page=Textmaster';
		?>
	    <div class="updated">
	        <p><a href="<?php echo $url; ?>"><?php _e( 'Merci de configurer le plugin TextMaster', 'textmaster' ); ?></a></p>
	    </div>
	    <?php

	}
}
if( is_network_admin() )
	add_action('network_admin_notices', 'tm_config_notice');
else
	add_action( 'admin_notices', 'tm_config_notice' );


add_action('init', 'session_manager');

add_action('wp_logout', 'session_logout');

add_filter('plugin_action_links', 'texmaster_plugin_action_links', 10, 2);

register_activation_hook( __FILE__, 'dependent_activate' );
register_deactivation_hook( __FILE__, 'tm_deactivate' );

add_action('admin_enqueue_scripts', 'enqueue_textmaster_admin_scripts');
// detection des modifications dans le contenu
add_filter( 'tiny_mce_before_init', 'texmaster_tiny_mce_before_init' );
// les actions wp
add_action('plugins_loaded', 'texmaster_init');
// pour le menu de confs
add_action('admin_menu', 'texmaster_admin_actions');
// les actions groupées
add_action('admin_footer-edit.php', 'texmaster_bulk_actions');
if (get_option_tm('textmaster_useMultiLangues') == 'Y'){
	add_action('admin_footer-edit-tags.php', 'texmaster_bulk_actions_tags');
	add_action('admin_footer-nav-menus.php', 'texmaster_actions_menus');
	add_action( 'wp_update_nav_menu', 'check_nav_menu_updates', 11, 1 );
	add_action( 'wp_create_nav_menu', 'check_nav_menu_updates', 11, 1 );

	add_filter( 'wp_get_nav_menus', 'tm_get_nav_menus' );
	add_filter( 'wp_nav_menu_objects', 'tm_nav_menu_objects' );
//	add_filter( 'nav_menu_items_page', 'tm_nav_menu_pages' );
}

//add_action('load-edit.php',         'do_custom_bulk_action');
// ajout des colonnes status
if (get_option_tm('textmaster_useTraduction') == 'Y' || get_option_tm('textmaster_useReadproof') == 'Y') {

	add_action('init', 'set_texmaster_columns');

/*	add_filter('manage_posts_columns', 'texmaster_columns_head');
	add_filter('manage_pages_columns', 'texmaster_columns_head');
	add_action('manage_posts_custom_column', 'texmaster_columns_content', 10, 2);
	add_action('manage_pages_custom_column', 'texmaster_columns_content', 10, 2);

	$args = array(
  	 'public'   => true,
 	  '_builtin' => false
	);
	$cpost_types = get_post_types( $args, 'names');
	if (isset($cpost_types)) {
		foreach ( $cpost_types as $post_type ) {
			add_filter('manage_'.$post_type.'_posts_columns', 'texmaster_columns_head');
			add_action('manage_'.$post_type.'_posts_custom_column', 'texmaster_columns_content', 10, 2);
		}
	}*/

	// le filtre par status textmaster
	add_action( 'restrict_manage_posts', 'textmaster_admin_posts_filter_restrict_manage_posts' );
//	add_filter( 'parse_query', 'textmaster_posts_filter' );
	add_filter( 'posts_where', 'textmaster_posts_where' );
	add_action( 'restrict_manage_posts', 'textmasterRedactions_admin_posts_filter_restrict_manage_posts' );
	add_filter( 'parse_query', 'textmasterRedactions_posts_filter' );
}
// pour le menu dans les articles
add_action('add_meta_boxes', 'texmaster_add_metaboxes' );
// memo des infos textmaster
add_action('admin_enqueue_scripts', 'textmaster_javascript');
add_action( 'wp_enqueue_scripts', 'tm_scripts' );

add_action('wp_ajax_textmaster', 'textmaster_callback');


add_action('admin_head', 'remove_all_media_buttons', 10, 2);
add_action('init', 'textmaster_redaction_type');
add_filter('post_updated_messages', 'textmaster_redaction_updated_messages', 10, 2);
add_filter('manage_edit-textmaster_redaction_columns', 'add_new_textmaster_redaction_columns');
add_action('manage_textmaster_redaction_posts_custom_column', 'manage_textmaster_redaction_columns', 10, 2);
add_filter('post_row_actions','textmaster_redaction_action_row', 10, 2);
add_action( 'admin_head', 'wpt_textmaster_icons' );
// les sauvegarde de posts
add_action( 'save_post', 'textmaster_redaction_save' );
// la gestion mutli-langues
if (get_option_tm('textmaster_useMultiLangues') == 'Y')
	add_action( 'save_post', 'callback_tm_langue' );
add_filter( 'default_content', 'textmaster_defaut_content' );

if (get_option_tm('textmaster_useRedaction') == 'Y') {
//	add_action('edit_post', 'textmaster_redaction_edit');
	add_action('admin_notices', 'textmaster_redaction_notice');

}


add_action( 'cron_syncProjets','syncProjets' );
add_action( 'cron_syncSupports','syncSupportMsgs' );
if ($_SESSION['lastSyncTmProjets'] == '' ||  time() - $_SESSION['lastSyncTmProjets'] > TMPS_SYNC_PROJETS){
	wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
	wp_schedule_single_event( time() + 1, 'cron_syncSupports' );
//	syncProjets();
//	syncSupportMsgs();
}



?>
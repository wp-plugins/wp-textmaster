<?php
/*
Plugin Name: TextMaster plugin
Plugin URI: http://www.textmaster.com/?pid=5310711603e44f00020006d3
Description: Plugin for TextMaster copywriting, proofreading and translation services.
Author: TextMaster SA
Version: 1.0
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
include( plugin_dir_path( __FILE__ ). '/tm-redaction.php' );
include( plugin_dir_path( __FILE__ ). '/tm-settings.php' );
include( plugin_dir_path( __FILE__ ). '/tm-trad_relecture.php' );
if(!class_exists('WP_List_Table'))
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
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
	if ( is_multisite() ) {
		deactivate_plugins( __FILE__);

		exit (__('Ce sera multi-site bientôt ! ','textmaster'));

	}
	else
		textmaster_install();
}


function textmaster_install() {
	global $wpdb, $bk_db_version;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$table_name = $wpdb->prefix . "tm_projets";
	$table_categories =  $wpdb->prefix .'tm_categories';
	$table_langues =  $wpdb->prefix .'tm_langues';
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
			UNIQUE KEY `code` (`code`)
    		);";
	dbDelta( $sqlCategories );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_categories'" ) != $table_categories)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_categories.' ('.$err.')'), E_USER_ERROR);


	$sqlLangues = "CREATE TABLE $table_langues (
 			`code` varchar(250) NOT NULL,
 			`value` VARCHAR(250) NULL,
			UNIQUE KEY `code` (`code`)
    		);";
	dbDelta( $sqlLangues );
	$err = $wpdb->last_error;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_langues'" ) != $table_langues)
		trigger_error(__('Erreur : impossible de créer la table : '.$table_langues.' ('.$err.')'), E_USER_ERROR);

	// id_wp_tm_project int(10) NOT NULL AUTO_INCREMENT,
	// PRIMARY KEY (id_wp_tm_project),
//	$keys = $wpdb->get_var('SHOW KEYS FROM  '.$table_name.' WHERE Key_name="id"');
//	print_r($keys);

	// supprime l'index unique existant avant les actions groupées
	$wpdb->query('DROP INDEX id ON '.$table_name.'');

	wp_schedule_single_event( time() + 1, 'cron_syncProjets' );
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
	else
		callback_redaction();
}


function texmaster_add_metaboxes() {
	if (get_option('textmaster_useTraduction') == 'Y')
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
	if (get_option('textmaster_useReadproof') == 'Y')
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


	}


	add_meta_box('wp_textmaster_redaction_defaut', __('Lancer la rédation', 'textmaster'), 'wp_texmaster_redaction_defaut_metaboxes', 'textmaster_redaction', 'side', 'default');
//	add_meta_box('wp_textmaster_redaction_options', __('Options', 'textmaster'), 'wp_texmaster_redaction_options_metaboxes', 'textmaster_redaction', 'side', 'default');
	add_meta_box('wp_texmaster_redaction_templates', __('Mise en page', 'textmaster'), 'wp_texmaster_redaction_templates_metaboxes', 'textmaster_redaction', 'normal', 'default');
//	add_meta_box('wp_texmaster_redaction_authors', __('Auteurs', 'textmaster'), 'wp_texmaster_redaction_authors_metaboxes', 'textmaster_redaction', 'side', 'default');
	remove_meta_box( 'submitdiv', 'textmaster_redaction', 'side' );
}


function texmaster_plugin_action_links($links, $file) {
	static $this_plugin;

	if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin) {
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
		$tApi->secretapi = get_option('textmaster_api_secret');
		$tApi->keyapi = get_option('textmaster_api_key');
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
						$req = 'SELECT status FROM '.$wpdb->prefix.'tm_projets WHERE id="'.$tmId->meta_value.'"';
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
							$req = 'SELECT status FROM '.$wpdb->prefix.'tm_projets WHERE id="'.$tmId->meta_value.'"';
							$status = $wpdb->get_var( $req);
							update_post_meta($tmId->post_id, 'textmasterStatusRedaction', $status);
						//	update_post_meta($tmId->post_id, 'textmasterStatusRedaction', $tApi->getProjetStatus($tmId->meta_value));
						}
					}
					else
					{
						$satus = get_post_meta($tmId->post_id,'textmasterStatusReadproof', TRUE);
						if ($satus != 'canceled' && $satus != 'completed') {
							$req = 'SELECT status FROM '.$wpdb->prefix.'tm_projets WHERE id="'.$tmId->meta_value.'"';
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

function enqueue_textmaster_admin_scripts() {

	wp_enqueue_style('wp-pointer');
	wp_enqueue_script('wp-pointer');
	add_action('admin_print_footer_scripts', 'textmaster_print_footer_scripts' );

	add_thickbox();

}

function textmaster_print_footer_scripts() {

//	echo  time() - $_SESSION['lastSyncTmProjets'];
	// on resyncro les projet toutes les x temps
//	if ($_SESSION['lastSyncTmProjets'] == '' ||  time() - $_SESSION['lastSyncTmProjets'] > TMPS_SYNC_PROJETS)
//		syncProjets();
	$ret = array();
	$checkProjets = checkProjetsStatus();

	?>
	<script type="text/javascript">
	jQuery(document).ready( function($) {
		$('#menu-posts-textmaster_redaction a.menu-top').attr('href','edit.php?post_type=textmaster_redaction&page=textmaster-projets');
		<?php if (get_option('textmaster_useRedaction') != 'Y') { ?>
			$('#menu-posts-textmaster_redaction ul li:nth-child(3)').hide();
		<?php } ?>
	});
	</script>
	<?php

	if (isset($ret['new']) && $ret['new'] == TRUE)
		 $_SESSION['AlerteTradRead'] = '';


	if ($checkProjets['TradRead'] && $_SESSION['AlerteTradRead'] != 'Done') {
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

	if ($checkProjets['Redaction'] && $_SESSION['AlerteRedaction'] != 'Done') {
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

add_action('init', 'session_manager');

add_action('wp_logout', 'session_logout');

add_filter('plugin_action_links', 'texmaster_plugin_action_links', 10, 2);

register_activation_hook( __FILE__, 'dependent_activate' );

add_action('admin_enqueue_scripts', 'enqueue_textmaster_admin_scripts');
// detection des modifications dans le contenu
add_filter( 'tiny_mce_before_init', 'texmaster_tiny_mce_before_init' );
// les actions wp
add_action('plugins_loaded', 'texmaster_init');
// pour le menu de confs
add_action('admin_menu', 'texmaster_admin_actions');
// les actions groupées
add_action('admin_footer-edit.php', 'texmaster_bulk_actions');
//add_action('load-edit.php',         'do_custom_bulk_action');
// ajout des colonnes status
if (get_option('textmaster_useTraduction') == 'Y' || get_option('textmaster_useReadproof') == 'Y') {

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
	add_filter( 'parse_query', 'textmaster_posts_filter' );
	add_filter( 'posts_where', 'textmaster_posts_where' );
	add_action( 'restrict_manage_posts', 'textmasterRedactions_admin_posts_filter_restrict_manage_posts' );
	add_filter( 'parse_query', 'textmasterRedactions_posts_filter' );
}
// pour le menu dans les articles
add_action('add_meta_boxes', 'texmaster_add_metaboxes' );
// memo des infos textmaster
add_action('admin_enqueue_scripts', 'textmaster_javascript');

add_action('wp_ajax_textmaster', 'textmaster_callback');


add_action('admin_head', 'remove_all_media_buttons', 10, 2);
add_action('init', 'textmaster_redaction_type');
add_filter('post_updated_messages', 'textmaster_redaction_updated_messages', 10, 2);
add_filter('manage_edit-textmaster_redaction_columns', 'add_new_textmaster_redaction_columns');
add_action('manage_textmaster_redaction_posts_custom_column', 'manage_textmaster_redaction_columns', 10, 2);
add_filter('post_row_actions','textmaster_redaction_action_row', 10, 2);
add_action( 'admin_head', 'wpt_textmaster_icons' );
add_action( 'save_post', 'textmaster_redaction_save' );
add_filter( 'default_content', 'textmaster_defaut_content' );

if (get_option('textmaster_useRedaction') == 'Y') {
//	add_action('edit_post', 'textmaster_redaction_edit');
	add_action('admin_notices', 'textmaster_redaction_notice');

}


?>
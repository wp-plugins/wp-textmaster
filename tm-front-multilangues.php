<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null;

class lou_rewrite_takeover {
	protected static $add_rules = array();

	public static function pre_init() {

	/*	if(!isset($wp_rewrite)){
			require_once ABSPATH . WPINC . '/rewrite.php';
			$wp_rewrite = new WP_Rewrite();
		}*/
		// debug
		add_action('admin_footer-options-permalink.php', array(__CLASS__, 'qsart_rewrite_debug'));

		// add rw tag
		add_action('init', array(__CLASS__, 'add_directory_rewrite'));

		// rw rule adds
		add_filter(is_admin() ? 'setup_theme' : 'do_parse_request', array(__CLASS__, 'do_parse_request'), 0);
		add_filter('post_rewrite_rules', array(__CLASS__, 'post_rewrite_rules'));
		add_filter('date_rewrite_rules', array(__CLASS__, 'date_rewrite_rules'));
		add_filter('root_rewrite_rules', array(__CLASS__, 'root_rewrite_rules'));
		add_filter('comments_rewrite_rules', array(__CLASS__, 'comments_rewrite_rules'));
		add_filter('search_rewrite_rules', array(__CLASS__, 'search_rewrite_rules'));
		add_filter('author_rewrite_rules', array(__CLASS__, 'author_rewrite_rules'));
		add_filter('page_rewrite_rules', array(__CLASS__, 'page_rewrite_rules'));
		add_filter('rewrite_rules_array', array(__CLASS__, 'final_rules_correction'), PHP_INT_MAX, 1);

		// query vars
		add_filter('query_vars', array(__CLASS__, 'add_lang_query_var'), 10, 1);
		add_filter('request', array(__CLASS__, 'default_language'), 9);
		add_filter('query', array(__CLASS__, 'filter_queries'), 9);
        add_filter('posts_join', array(__CLASS__,'posts_join_filter'));

		// fix permalinks
		$link_filters_needing_rewrite = array(
		  'post_link',
		  'post_type_link',
		  'page_link',
		  'attachment_link',
		  'search_link',
		  'post_type_archive_link',
		  'year_link',
		  'month_link',
		  'day_link',
		  'feed_link',
		  'author_link',
		  'term_link',
		  'category_feed_link',
		  'term_feed_link',
		  'taxonomy_feed_link',
		  'author_feed_link',
		  'search_feed_link',
		  'post_type_archive_feed_link',
		);
		add_filter('pre_post_link', array(__CLASS__, 'change_permalink_structure'), 10, 3);
		foreach ($link_filters_needing_rewrite as $link_filter)
			add_filter($link_filter, array(__CLASS__, 'rewrite_lang_in_permalink'), 11, 3);

	}

	public static function do_parse_request($cur) {
		self::get_page_permastruct();
		self::get_author_permastruct();
		self::correct_extras();
		return $cur;
	}

	public static function get_supported_langs() {
		return apply_filters('lou-get-supported-languages', explode(';', get_option_tm('textmaster_langues')));
	}

	public static function add_directory_rewrite() {
		global $wp_rewrite;
		$supported_languages = self::get_supported_langs();
		add_rewrite_tag('%lang%', '('.implode('|', $supported_languages).')');
	}

	public static function unleadingslashit($str) {
		return ltrim($str, '/');
	}

	public static function final_rules_correction($rules) {
		global $wp_rewrite;

		$new_rules = array();
		$supported_languages = self::get_supported_langs();
		$find = implode('|', $supported_languages);
		$find_find = '#(?<!\()('.preg_quote($find, '#').')#';
		$preg_node = str_replace('%%%', '(\d+)', preg_quote($wp_rewrite->preg_index('%%%'), '#'));

		foreach ($rules as $k => $v) {
			if (preg_match($find_find, $k)) {
				$nk = preg_replace($find_find, '('.$find.')', $k);
				$parts = explode('?', $v);
				$index = array_shift($parts);
				$pv = implode('?', $parts);
			//	$pv = preg_replace_callback('#'.$preg_node.'#', function ($matches) use ($wp_rewrite) {
			//		return $wp_rewrite->preg_index($matches[1]+1);
			//	}, $pv);
				$pv = preg_replace_callback('#'.$preg_node.'#', array(__CLASS__,'callback_rules_correction'), $pv);
				$nv = $index.'?lang='.$wp_rewrite->preg_index(1).(!empty($pv) ? '&'.$pv : '');
				$new_rules[$nk] = $nv;
			} else {
				$new_rules[$k] = $v;
			}
		}

		return $new_rules;
	}

	public static function callback_rules_correction($matches) {
		global $wp_rewrite;

		return $wp_rewrite->preg_index($matches[1]+1);
	}

	public static function change_permalink_structure($struct) {
		$struct = self::unleadingslashit($struct);
		$struct = preg_replace('#^%lang%/?#', '', $struct);
		return '/%lang%/'.$struct;
	}

	public static function extras_rewrite_rules($rules, $struct) {
		global $wp_rewrite;

		if ( is_array( $struct ) ) {
			if ( count( $struct ) == 2 )
				$new_rules = $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($struct[0]), $struct[1] );
			else
				$new_rules = $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($struct['struct']), $struct['ep_mask'], $struct['paged'], $struct['feed'], $struct['forcomments'], $struct['walk_dirs'], $struct['endpoints'] );
		} else {
			$new_rules = $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($struct) );
		}

		return $new_rules + $rules;
	}

	public static function post_rewrite_rules($rules) {
		global $wp_rewrite;

		// hack to add code for extras type urls (usually created by other plugins)
		$func = array(__CLASS__, 'extras_rewrite_rules');
		foreach ($wp_rewrite->extra_permastructs as $type => $struct) {
			$filter = ($type == 'post_tag' ? 'tag' : $type).'_rewrite_rules';
		//	add_filter($filter, function ($rules) use ($struct, $func) { return call_user_func_array($func, array($rules, $struct)); });
			add_filter($filter, array(__CLASS__, 'callback_post_rewrite_rules'), array($rules,$struct, $func));
		}

		return $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($wp_rewrite->permalink_structure), EP_PERMALINK ) + $rules;
	}

	function callback_post_rewrite_rules($rules,$struct, $func) {

		return call_user_func_array($func, array($rules, $struct));
	}

	public static function date_rewrite_rules($rules) {
		global $wp_rewrite;
		return $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($wp_rewrite->get_date_permastruct()), EP_DATE) + $rules;
	}

	public static function root_rewrite_rules($rules) {
		global $wp_rewrite;
		return $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($wp_rewrite->get_date_permastruct()), EP_DATE) + $rules;
	}

	public static function comments_rewrite_rules($rules) {
		global $wp_rewrite;
		return $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($wp_rewrite->root . $wp_rewrite->comments_base), EP_COMMENTS, false, true, true, false) + $rules;
	}

	public static function search_rewrite_rules($rules) {
		global $wp_rewrite;
		return $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($wp_rewrite->get_search_permastruct()), EP_SEARCH) + $rules;
	}

	public static function author_rewrite_rules($rules) {
		global $wp_rewrite;
		return $wp_rewrite->generate_rewrite_rules( self::change_permalink_structure($wp_rewrite->get_author_permastruct()), EP_AUTHORS) + $rules;
	}

	public static function page_rewrite_rules($rules) {
		global $wp_rewrite;
		$page_structure = self::get_page_permastruct();

		return $wp_rewrite->generate_rewrite_rules( $page_structure, EP_PAGES, true, true, false, false ) + $rules;
	}

	protected static function get_page_permastruct() {
		global $wp_rewrite;

		if (empty($wp_rewrite->permalink_structure)) {
			$wp_rewrite->page_structure = '';
			return false;
		}

		$wp_rewrite->page_structure = self::change_permalink_structure($wp_rewrite->root . '%pagename%');

		return $wp_rewrite->page_structure;
	}

	protected static function get_author_permastruct() {
		global $wp_rewrite;

		if ( empty($wp_rewrite->permalink_structure) ) {
			$wp_rewrite->author_structure = '';
			return false;
		}

		$wp_rewrite->author_structure = self::change_permalink_structure($wp_rewrite->front . $wp_rewrite->author_base . '/%author%');

		return $wp_rewrite->author_structure;
	}

	protected static function correct_extras() {
		global $wp_rewrite;

		foreach ($wp_rewrite->extra_permastructs as $k => $v)
			$wp_rewrite->extra_permastructs[$k]['struct'] = self::change_permalink_structure($v['struct']);
	}

	public static function get_default_post_lang($post) {
		global $wp_query;

	//	var_dump($post);
	//	echo get_class($post);
		$metas_lang = array();
		if (is_object($post) && get_class($post) == 'WP_Post')
			$metas_lang = get_post_meta($post->ID, 'tm_lang');
		else if (is_int($post))
			$metas_lang = get_post_meta($post, 'tm_lang');

		$_REQUEST['lang'] = get_option_tm('textmaster_langueDefaut');
		if (count($metas_lang) != 0) {
			$a_metas_lang = explode(';', $metas_lang[0]);
			$_REQUEST['lang'] = $a_metas_lang[1];
		}
		else
			$_REQUEST['lang'] = get_query_var('lang', get_option_tm('textmaster_langueDefaut'));


		return ( $lang = $_REQUEST['lang'] ) ? $lang : get_option_tm('textmaster_langueDefaut');
	}

	public static function rewrite_lang_in_permalink($permalink, $post=0, $leavename=false) {
		// find the default post language via a function you have created to
		// determine the default language url. this could be based on the current
		// language the user has selected on the frontend, or based on the current
		// url, or based on the post itself. it is up to you
		$lang = self::get_default_post_lang($post);

		// once you have the default language, it is a simple search and replace
		return str_replace('%lang%', $lang, $permalink);
	}

	public static function add_lang_query_var($vars) {
		// tell WP to expect the lang query_var, which you can then later use
		$vars[] = 'lang';

		// return the new list of query vars, which includes our lang param
		return array_unique($vars);
	}

	public static function default_language($vars) {

		if (array_diff( array_keys($vars), array('preview', 'page', 'paged', 'cpage') ))
			$vars['lang'] = !empty($vars['lang']) ? $vars['lang'] : get_option_tm('textmaster_langueDefaut');
		return $vars;
	}

	public static function filter_queries($sql){
//		echo $sql;

		return $sql;
	}

	public static function posts_join_filter($sql){
        global $wpdb, $pagenow, $wp_taxonomies;

		// determine post type
		$db = debug_backtrace();
		foreach($db as $o){
			if($o['function']=='apply_filters_ref_array' && $o['args'][0]=='posts_join'){
				$post_type =  $wpdb->escape($o['args'][1][1]->query_vars['post_type']);
				break;
			}
		}

	//	var_dump($post_type);

	}

	public static function qsart_rewrite_debug() {
		if (isset($_COOKIE['rwdebug']) && $_COOKIE['rwdebug'] == 1) {
			global $wp_rewrite;
			echo '<pre style="background-color:#ffffff; font-size:10px;">';
			var_dump($GLOBALS['wp_rewrite']);
			var_dump($wp_rewrite);
			print_r($wp_rewrite->rules);
			echo '</pre>';
		}
	}


}

if (defined('ABSPATH') && function_exists('add_action')) {
	tm_add_rewrite_rules();
}
function tm_add_rewrite_rules(){

	//	echo 'tt';

//	$_COOKIE['rwdebug'] =1;

	lou_rewrite_takeover::pre_init();
//	lou_rewrite_takeover::qsart_rewrite_debug();
}
add_action('generate_rewrite_rules', 'tm_add_rewrite_rules');
// -------------------------------------------------------------------------
// on charge les bonnes locales
add_filter( 'locale', 'set_tm_locale' );
function set_tm_locale( $lang ) {
	if (!is_admin() ) {

		$aUrl = explode('/', $_SERVER['REQUEST_URI']);

	//	$lang =  get_query_var('lang');
		return $aUrl[1].'_'.strtoupper($aUrl[1]);
	} else {
		// return original language
//		return $lang;
	}
}


// le sélécteur de langues
function display_langs_selector(){
	global $wpdb, $post;

	$table_langues =  $wpdb->base_prefix .'tm_langues';
	$html = '<div id="tm_langs_selector">';

	$textmaster_langues = explode(';', get_option_tm('textmaster_langues'));

	if (count($textmaster_langues) != 0) {
		$html .= '<ul>';
		foreach ($textmaster_langues as $lang) {
			$langue_str = $wpdb->get_var( "SELECT value FROM $table_langues WHERE code='$lang'" );

			if (file_exists(plugin_dir_path( __FILE__ ).'/images/flags-iso/shiny/24/'.strtoupper($lang).'.png'))
				$html .= '<li><a href="'.site_url().'/'.$lang.'/"><img src="' . plugins_url( '/images/flags-iso/shiny/24/'.strtoupper($lang).'.png', __FILE__ ) . '" alt="'.$langue_str.'" /></a></li>';
			else
				$html .= '<li><a href="'.site_url().'/'.$lang.'/">'.$langue_str.'</a></li>';

		}
		$html .= '</ul>';
	}
	$html .= '</div>';
	echo $html;
}

?>
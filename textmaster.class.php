<?php

/*
* API textmaster
* V 0.5
*/

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

class textmaster_api {
    var $keyapi = '';
    var $secretapi = '';
    var $locale = '';
    var $dureeResyncro = '86400';
    var $chCurl = null;

    private $urlAPiClients = URL_TM_API_CLIENTS;
    private $urlAPiPublic = URL_TM_API_PUBLIC;

    function __construct($keyapi, $secretapi)
    {
        // echo $keyapi .' - ';
        $this->keyapi = $keyapi;
        // echo $this->keyapi;
        $this->secretapi = $secretapi;
        // $this->locale = str_replace('_', '-', get_locale());
        $this->chCurl = $this->init();
        if ($this->keyapi != '' && $this->secretapi != '')
            $this->make_junk_project();

        if (!isset($_SESSION['lastSyncTmCategories']))
            $_SESSION['lastSyncTmCategories'] = '';
        if (!isset($_SESSION['lastSyncTmLangues']))
            $_SESSION['lastSyncTmLangues'] = '';
        if (!isset($_SESSION['lastSyncTmTemplates']))
            $_SESSION['lastSyncTmTemplates'] = '';
        if (!isset($_SESSION['lastSyncTmLevel']))
            $_SESSION['lastSyncTmLevel'] = '';
        if (!isset($_SESSION['lastSyncPricings']))
            $_SESSION['lastSyncPricings'] = '';
        if (!isset($_SESSION['lastSyncTmAuteurs']))
            $_SESSION['lastSyncTmAuteurs'] = '';
    }

    function __destruct()
    {
        curl_close($this->chCurl);
    }

    function trierArray(&$multiArray)
    {
        if (count($multiArray) != 0) {
            $tmp = Array();
            foreach($multiArray as $ma)
            $tmp[] = &$ma['value'];
            array_multisort($tmp, $multiArray);
        }
    }

    /*
	* Init du flux curl avec les hearders permettant l'authentification textmaster
	*/
    function init()
    {
        $this->chCurl = curl_init();
        curl_setopt($this->chCurl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($this->chCurl, CURLOPT_VERBOSE, true);
        curl_setopt($this->chCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->chCurl, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0);
        curl_setopt($this->chCurl, CURLOPT_ENCODING, 'gzip');
        // curl_setopt($this->chCurl, CURLOPT_HEADER , true);
        // proxy
        if (defined('WP_PROXY_HOST')) {
        	curl_setopt($this->chCurl,CURLOPT_HTTPHEADER,array("Expect:  "));
        	curl_setopt($this->chCurl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($this->chCurl, CURLOPT_PROXY, WP_PROXY_HOST);
            if (defined('WP_PROXY_PORT'))
                curl_setopt($this->chCurl, CURLOPT_PROXYPORT, WP_PROXY_PORT);

            if (defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD')) {
                curl_setopt($this->chCurl, CURLOPT_PROXYAUTH, CURLAUTH_ANY);
                curl_setopt($this->chCurl, CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME . ':' . WP_PROXY_PASSWORD);
            }
        }

        curl_setopt($this->chCurl, CURLOPT_USERAGENT, 'tm-wordpress-app agent v1.0');
        // les timeouts
        if (TIMEOUT_API != 0) {
            curl_setopt($this->chCurl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($this->chCurl, CURLOPT_TIMEOUT, TIMEOUT_API); //timeout in seconds
        }

        curl_setopt($this->chCurl, CURLOPT_HTTPHEADER, $this->makeHearder());

        return $this->chCurl;
    }

    /*
	* Création de l'authentification textmaster
	*/
    function makeHearder()
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s');
        $signature = sha1($this->secretapi . $date);

        $headers = array('APIKEY: ' . $this->keyapi, 'DATE: ' . $date, 'SIGNATURE: ' . $signature, 'Content-Type: application/json', 'Accept: application/json', 'User-Agent: tm-wordpress-app/agent v1.0');

        return $headers;
    }

    function testAuth()
    {
        // $this->chCurl = $this->init();
        $url = 'http://api.textmaster.com/test';
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = curl_exec($this->chCurl);
        $resultInfos = curl_getinfo($this->chCurl);

        if (curl_errno($this->chCurl) == CURLE_OPERATION_TIMEOUTED)
            $this->mailAlertSupport($url, false, $resultInfos);

        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        // print_r($result);
    }

    public static function sendTracker()
    {
        $chCurl = curl_init();
        curl_setopt($chCurl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($chCurl, CURLOPT_VERBOSE, true);
        curl_setopt($chCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chCurl, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1);
        curl_setopt($chCurl, CURLOPT_ENCODING, 'gzip');
        // curl_setopt($this->chCurl, CURLOPT_HEADER , true);
        curl_setopt($chCurl, CURLOPT_USERAGENT, 'tm-wordpress-app/agent v1.0');
        curl_setopt($chCurl, CURLOPT_URL, 'http://www.textmaster.com/?pid=5310711603e44f00020006d3');
        $result = curl_exec($chCurl);
        curl_exec($chCurl);
        // curl_close($chCurl);
    }

    /*
	* pour avoir la liste des locales disponibles sur textmaster
	*/
    function getLocales()
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiPublic . '/locales';
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = curl_exec($this->chCurl);
        // timout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (Locales)!');
        }

        $resultInfos = curl_getinfo($this->chCurl);
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (Locales)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        // print_r($result);
    }

    /*
	* pour avoir la liste des catégories sur textmaster
	*/
    function getCategories()
    {
        global $wpdb;

        $table_categories = $wpdb->base_prefix . 'tm_categories';

        $reqCats = $wpdb->prepare('SELECT * FROM ' . $table_categories, '');
        $arrayRet = $wpdb->get_results($reqCats, ARRAY_A);
        // on resyncro tous les jours
        if (isset($_SESSION['lastSyncTmCategories']) || time() - $_SESSION['lastSyncTmCategories'] > $this->dureeResyncro || count($arrayRet) == 0) {
            $this->chCurl = $this->init();
            $url = $this->urlAPiPublic . '/categories';

            $infosUserTM = $this->getUserInfos();
            if (!is_array($infosUserTM)) {
                $infosUserTM = array();
                if (defined('WPLANG'))
                    $infosUserTM['locale'] = str_replace('_', '-', WPLANG);
                else
                    $infosUserTM['locale'] = 'en-US';
            }

            $url .= '?locale=' . $infosUserTM['locale'];

            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = curl_exec($this->chCurl);
            $resultInfos = curl_getinfo($this->chCurl);
            // timeout avec les API
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (categories)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (categories)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            // curl_close($this->chCurl);
            // print_r($result);
            $arrayCats = json_decode($result, true);
            $this->trierArray($arrayCats['categories']);

            $this->syncCategories($arrayCats['categories']);
        }else {
            $table_categories = $wpdb->base_prefix . 'tm_categories';
            $reqCats = $wpdb->prepare('SELECT * FROM ' . $table_categories, '');
            $arrayCats['categories'] = $wpdb->get_results($reqCats, ARRAY_A);
        }

        return $arrayCats['categories'];
    }

    function syncCategories($arrayCats)
    {
        global $wpdb;

        $table_categories = $wpdb->base_prefix . 'tm_categories';
        if (count($arrayCats) != 0) {
            $wpdb->query('DELETE FROM ' . $table_categories);
            foreach ($arrayCats as $categorie) {
                $reqChk = $wpdb->prepare('SELECT value FROM ' . $table_categories . ' WHERE code = "%s"', $categorie['code']);
                $chk = $wpdb->get_var($reqChk);
                if ($chk == '') {
                    $sql = 'INSERT INTO ' . $table_categories . ' (code, value) VALUES (%s, %s)';
                    $req = $wpdb->prepare($sql, $categorie['code'], $categorie['value']);
                }else if ($chk != $categorie['value']) {
                    $sql = 'UPDATE ' . $table_categories . ' SET value=%s WHERE code=%s)';
                    $req = $wpdb->prepare($sql, $categorie['value'], $categorie['code']);
                }

                $wpdb->query($req);
            }
            $_SESSION['lastSyncTmCategories'] = time();
        }
    }

    public static function getLibCategorie($code)
    {
        global $wpdb;
        $table_categories = $wpdb->base_prefix . 'tm_categories';

        $req = $wpdb->prepare('SELECT value FROM ' . $table_categories . ' WHERE code = "%s"', $code);
        return $wpdb->get_var($req);
    }

    /*
	   * pour avoir la liste des niveaux de langages sur textmaster
	*/
    public function getLanguageLevels()
    {
        global $wpdb;
        $table_languageLevels = $wpdb->base_prefix . 'tm_languageLevels';

		if($wpdb->get_var("SHOW TABLES LIKE '$table_languageLevels'" ) == $table_languageLevels){
			$reqCats = $wpdb->prepare('SELECT * FROM ' . $table_languageLevels, '');
			$arrayRet = $wpdb->get_results($reqCats, ARRAY_A);
		}else
			$_SESSION['lastSyncTmLevel'] = '';


        if ($_SESSION['lastSyncTmLevel'] == '' || time() - $_SESSION['lastSyncTmLevel'] > $this->dureeResyncro || count($arrayRet) == 0) {
            $this->chCurl = $this->init();
            $url = $this->urlAPiClients . '/language_levels';

            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = curl_exec($this->chCurl);
            $resultInfos = curl_getinfo($this->chCurl);
            // timeout avec les API
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (categories)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (categories)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            // curl_close($this->chCurl);
            $arrayRet = json_decode($result, true);
            // var_dump($arrayRet);
            // $arrayRet['regular'] = __('Regular','textmaster');
            // $arrayRet['premium'] = __('Premium','textmaster');
        	if($wpdb->get_var("SHOW TABLES LIKE '$table_languageLevels'" ) == $table_languageLevels)
        		$this->syncLanguageLevels($arrayRet["language_levels"]);
        }else {
            $reqCats = $wpdb->prepare('SELECT * FROM ' . $table_languageLevels, '');
            $arrayRet["language_levels"] = $wpdb->get_results($reqCats, ARRAY_A);
        }
        // print_r( $arrayRet["language_levels"]);
        // on ajout le niv 'enterprise' qui n'est pas retournée par l'api sandbox
        $entrepriseFound = false;
        if (is_array($arrayRet["language_levels"]) && count($arrayRet["language_levels"]) != 0) {
            foreach ($arrayRet["language_levels"] as $key => $val) {
                if ($val['name'] == 'enterprise') {
                    $entrepriseFound = true;
                    break;
                }
            }
        }
        if (!$entrepriseFound && is_array($arrayRet))
            $arrayRet["language_levels"][]['name'] = 'enterprise';
        // $arrayRet["language_levels"] = array_unique($arrayRet["language_levels"]);
        // on trie les language_levels par type de service
        if (is_array($arrayRet) && count($arrayRet["language_levels"]) != 0) {
            foreach ($arrayRet["language_levels"] as $key => $val) {
                if ($val['name'] != 'enterprise') {
                    $arrayRet["language_levels"]['traduction'][$key] = $val;
                    $arrayRet["language_levels"]['readproof'][$key] = $val;
                    $arrayRet["language_levels"]['copywrite'][$key] = $val;
                }else {
                    $arrayRet["language_levels"]['traduction'][$key] = $val;
                    $arrayRet["language_levels"]['copywrite'][$key] = $val;
                }
            }
        }
		else 
			$arrayRet["language_levels"] = array();
        // return $arrayCats['categories'];
        // var_dump($arrayRet["language_levels"]);
        return $arrayRet["language_levels"];
    }

    function syncLanguageLevels($array)
    {
        global $wpdb;

        $table_languageLevels = $wpdb->base_prefix . 'tm_languageLevels';
        if (count($array) != 0) {
            $wpdb->query('DELETE FROM ' . $table_languageLevels);
            foreach ($array as $level) {
                $sql = 'INSERT INTO ' . $table_languageLevels . ' (name) VALUES (%s)';
                $req = $wpdb->prepare($sql, $level['name']);

                $wpdb->query($req);
            }
            $_SESSION['lastSyncTmLevel'] = time();
        }
    }

    public static function getVocabularyTypes()
    {
        $vocabulary_types['not_specified'] = __('Non spécifié', 'textmaster');
        $vocabulary_types['popular'] = __('Populaire', 'textmaster');
        $vocabulary_types['technical'] = __('Technique', 'textmaster');
        $vocabulary_types['fictional'] = __('Romancé', 'textmaster');

        return $vocabulary_types;
    }

    public static function getGrammaticalPersons()
    {
        $grammatical_persons['not_specified'] = __('Non spécifié', 'textmaster');
        $grammatical_persons['first_person_singular'] = __('Je > 1ère personne - Singulier', 'textmaster');
        $grammatical_persons['second_person_singular'] = __('Tu > 2ème Personne - Singulier', 'textmaster');
        $grammatical_persons['third_person_singular_masculine'] = __('Il > 3ème personne - Singulier Masculin', 'textmaster');
        $grammatical_persons['third_person_singular_feminine'] = __('Elle > 3ème personne - Singulier Féminin', 'textmaster');
        $grammatical_persons['third_person_singular_neuter'] = __('On > 3ème personne - Singulier Neutre', 'textmaster');
        $grammatical_persons['first_person_plural'] = __('Nous > 1ère personne - Pluriel', 'textmaster');
        $grammatical_persons['second_person_plural'] = __('Vous > 2ème Personne - Pluriel', 'textmaster');
        $grammatical_persons['third_person_plural'] = __('Ils/elles > 3ème Personne - Pluriel', 'textmaster');

        return $grammatical_persons;
    }

    public static function getTargetReaderGroups()
    {
        $target_reader_groups['not_specified'] = __('Non spécifié', 'textmaster');
        $target_reader_groups['children'] = __('Enfants > 13 ans et moins', 'textmaster');
        $target_reader_groups['teenager'] = __('Adolescent > entre 14 et 18 ans', 'textmaster');
        $target_reader_groups['young_adults'] = __('Jeunes adultes > entre 19 et 29 ans', 'textmaster');
        $target_reader_groups['adults'] = __('Adultes > entre 30 et 59 ans', 'textmaster');
        $target_reader_groups['old_adults'] = __('Séniors > 60 ans et plus', 'textmaster');

        return $target_reader_groups;
    }

    /*
	   * pour avoir la liste des langues sur textmaster
	*/
    function getLanguages()
    {
        global $wpdb;

        $table_langues = $wpdb->base_prefix . 'tm_langues';

        $reqLangs = $wpdb->prepare('SELECT * FROM ' . $table_langues, '');
        // echo 'SELECT * FROM '.$table_langues;
        $arrayLangs = $wpdb->get_results($reqLangs, ARRAY_A);

        if ($_SESSION['lastSyncTmLangues'] == '' || time() - $_SESSION['lastSyncTmLangues'] > $this->dureeResyncro || count($arrayLangs) == 0) {
            $this->chCurl = $this->init();
            $url = $this->urlAPiPublic . '/languages';

            $infosUserTM = $this->getUserInfos();
            // Wordpress < 4.0
            if (!is_array($infosUserTM) && get_option_tm('WPLANG') != '') {
                $infosUserTM = array();
                $infosUserTM['locale'] = str_replace('_', '-', get_option_tm('WPLANG'));
            } else if (!is_array($infosUserTM) && defined('WPLANG')) {
                $infosUserTM = array();
                $infosUserTM['locale'] = str_replace('_', '-', WPLANG);
            }

            $url .= '?locale=' . $infosUserTM['locale'];
            // echo $url;
            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = curl_exec($this->chCurl);
            $resultInfos = curl_getinfo($this->chCurl);
            // timeout api
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (Languages)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (Languages)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            // curl_close($this->chCurl);
            $arrayLangs = json_decode($result, true);

            $this->trierArray($arrayLangs['languages']);
            $arrayLangs['languages'] = $this->syncLanguages($arrayLangs['languages']);
        }else {
            $reqLangs = $wpdb->prepare('SELECT * FROM ' . $table_langues, '');

            $arrayLangs['languages'] = $wpdb->get_results($reqLangs, ARRAY_A);

            if (count($arrayLangs['languages']) == 0) {
                $_SESSION['lastSyncTmLangues'] = '';
            }
        }
        // var_dump($arrayLangs['languages']);
        return $arrayLangs['languages'];
    }

    function syncLanguages($arrayLangs)
    {
        global $wpdb;

        $table_langues = $wpdb->base_prefix . 'tm_langues';
        if (count($arrayLangs) != 0) {
            $wpdb->query('DELETE FROM ' . $table_langues);
            foreach ($arrayLangs as $langue) {
                $reqChk = $wpdb->prepare('SELECT value FROM ' . $table_langues . ' WHERE code = "%s"', $langue['code']);
                $chk = $wpdb->get_var($reqChk);
                if ($chk == '') {
                    $sql = 'INSERT INTO ' . $table_langues . ' (code, value) VALUES (%s, %s)';
                    $req = $wpdb->prepare($sql, $langue['code'], $langue['value']);
                }else if ($chk != $categorie['value']) {
                    $sql = 'UPDATE ' . $table_langues . ' SET value=%s WHERE code=%s)';
                    $req = $wpdb->prepare($sql, $langue['value'], $langue['code']);
                }

                $wpdb->query($req);
            }
        	// suppresion des langues non iso (bug api)
        	$wpdb->query('DELETE FROM ' . $table_langues. ' WHERE code NOT LIKE "%-%"');
            $_SESSION['lastSyncTmLangues'] = time();
        }
    	$reqLangs = $wpdb->prepare('SELECT * FROM ' . $table_langues, '');
    	$arrayLangs['languages'] = $wpdb->get_results($reqLangs, ARRAY_A);

    	return $arrayLangs['languages'];
    }

    /*
	* pour avoir la liste des mise en page
	*/
    function getTemplates()
    {
        global $wpdb;

        $table_templates = $wpdb->base_prefix . 'tm_templates';
        $reqTemplates = $wpdb->prepare('SELECT * FROM ' . $table_templates, '');
        $arrayTemplates = $wpdb->get_results($reqTemplates, ARRAY_A);

        if ($_SESSION['lastSyncTmTemplates'] == '' || time() - $_SESSION['lastSyncTmTemplates'] > $this->dureeResyncro || count($arrayTemplates) == 0) {
            $this->chCurl = $this->init();
            $url = $this->urlAPiClients . '/work_templates';

            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = curl_exec($this->chCurl);
            $resultInfos = curl_getinfo($this->chCurl);
            // timeout api
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API timeout (Templates)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (Templates)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            // curl_close($this->chCurl);
            $datas = json_decode($result, true);

            $this->syncTemplates($datas['work_templates']);
        }else {
            $table_templates = $wpdb->base_prefix . 'tm_templates';
            $reqLangs = $wpdb->prepare('SELECT * FROM ' . $table_templates, '');
            $datas['work_templates'] = $wpdb->get_results($reqLangs, ARRAY_A);

            if (count($datas['work_templates']) == 0) {
                $_SESSION['lastSyncTmTemplates'] = '';
            }
        }
        return $datas['work_templates'];
    }

    function syncTemplates($arrayTemplates)
    {
        global $wpdb;

        $table_templates = $wpdb->base_prefix . 'tm_templates';
        if (count($arrayTemplates) != 0) {
            $wpdb->query('DELETE FROM ' . $table_templates);
            foreach ($arrayTemplates as $templates) {
                $reqChk = $wpdb->prepare('SELECT value FROM ' . $table_templates . ' WHERE name = "%s"', $templates['name']);
                $chk = $wpdb->get_var($reqChk);
                if ($chk == '') {
                    $sql = 'INSERT INTO ' . $table_templates . ' (name, description, image_preview_path, ctype) VALUES (%s, %s, %s, %s)';
                    $req = $wpdb->prepare($sql, $templates['name'], $templates['description'], $templates['image_preview_path'], $templates['ctype']);
                }else if ($chk != $categorie['value']) {
                    $sql = 'UPDATE ' . $table_templates . ' SET description=%s, image_preview_path=%s, ctype=%s WHERE name=%s)';
                    $req = $wpdb->prepare($sql, $templates['description'], $templates['image_preview_path'], $templates['ctype'], $templates['name']);
                }

                $wpdb->query($req);
            }
            $_SESSION['lastSyncTmTemplates'] = time();
        }
    }

    /* pour recup la liste des auteurs */
    function getAuteurs($status = '', $arrayProjet = array())
    {
        global $wpdb;

        $table_auteurs = $wpdb->base_prefix . 'tm_auteurs';
        $req = $wpdb->prepare('SELECT * FROM ' . $table_auteurs, '');
        $arrayAuteurs = $wpdb->get_results($req, ARRAY_A);

        if (($_SESSION['lastSyncTmAuteurs'] == '' || time() - $_SESSION['lastSyncTmAuteurs'] > $this->dureeResyncro || count($arrayAuteurs) == 0) && count($arrayProjet) == 0) {
            $status = 'my_textmaster';
            $ret = '';

            $this->chCurl = $this->init();
            $url = $this->urlAPiClients . '/my_authors';
            // echo $url;
            if ($status != '')
                $url .= '?status=' . $status;

            if (count($arrayProjet) != 0) {
                // $project = json_encode($arrayProjet);
                $strProjet = '';
                foreach ($arrayProjet as $key => $projet) {
                    if (is_array($projet)) {
                        foreach ($projet as $keyOption => $option)
                        $strProjet .= '&project[' . $key . '][' . $keyOption . ']=' . $option;
                    }else
                        $strProjet .= '&project[' . $key . ']=' . $projet;
                }
                $url .= $strProjet;
            }
            // echo $url;
            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = curl_exec($this->chCurl);
            // var_dump($result);
            $datas = json_decode($result, true);
            // print_r($datas);
            // $resultInfos = curl_getinfo($this->chCurl);
            // print_r($resultInfos);
            $resultInfos = curl_getinfo($this->chCurl);
            // timeout api
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (authors)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (authors)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            if (isset($datas['my_authors']))
                $this->syncAuteurs($datas['my_authors']);
            // echo $result;
            // curl_close($this->chCurl);
        }else {
            $table_auteurs = $wpdb->base_prefix . 'tm_auteurs';
            $reqLangs = $wpdb->prepare('SELECT * FROM ' . $table_auteurs, '');
            $datas['my_authors'] = $wpdb->get_results($reqLangs, ARRAY_A);

            if (count($datas['my_authors']) == 0) {
                $_SESSION['lastSyncTmAuteurs'] = '';
            }
        }
        if (is_array($datas) && key_exists('my_authors', $datas) && is_array($datas['my_authors']))
            array_unshift($datas['my_authors'], array('id' => '', 'author_id' => '', 'status' => 'my_textmaster', 'author_ref' => __('Non spécifié', 'textmaster')));

        if (is_array($datas) && key_exists('my_authors', $datas))
            $ret = $datas['my_authors'];

        return $ret;
    }

    function syncAuteurs($array)
    {
        global $wpdb;
        $table_auteurs = $wpdb->base_prefix . 'tm_auteurs';
        if (count($array) != 0) {
            $wpdb->query('DELETE FROM ' . $table_auteurs);
            foreach ($array as $auteur) {
                $sql = 'INSERT INTO ' . $table_auteurs . ' (`description`, `tags`, `status`, `id`, `author_id`, `author_ref`, `latest_activity`, `created_at`, `updated_at`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)';
                $req = $wpdb->prepare($sql, $auteur['description'], json_encode($auteur['tags']), $auteur['status'], $auteur['id'], $auteur['author_id'], $auteur['author_ref'], $auteur['latest_activity'], $auteur['created_at']['full'], $auteur['updated_at']['full']);
                $wpdb->query($req);
            }
            $_SESSION['lastSyncTmAuteurs'] = time();
        }
    }

    function addAuthor($description, $status, $author_id)
    {
        $this->chCurl = $this->init();

        $url = $this->urlAPiClients . '/my_authors/' . $author_id;

        curl_setopt($this->chCurl, CURLOPT_INFILESIZE, 0);
        // curl_setopt($this->chCurl, CURLOPT_PUT, true);
        // curl_setopt($this->chCurl, CURLOPT_URL, $url );
        curl_setopt($this->chCurl, CURLOPT_CUSTOMREQUEST, "PUT");

        $my_author['description'] = $description;
        $my_author['status'] = $status;
        // $my_author['author_id'] = $author_id;
        // echo json_encode(array('my_author' => $my_author));
        // curl_setopt($this->chCurl, CURLOPT_POST, 1);
        if (PHP_VERSION_ID > 50300) {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('my_author' => $my_author), JSON_HEX_APOS | JSON_HEX_QUOT));
        }else {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('my_author' => $my_author)));
        }
        // echo $url;
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = curl_exec($this->chCurl);
        // print_r($result);
        $resultInfos = curl_getinfo($this->chCurl);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (add  author)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (add  author)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result, print_r($result, true));
        }

        $_SESSION['lastSyncTmAuteurs'] = '';
        // curl_close($this->chCurl);
        return $result;
    }

    /*
	*   récuperer le prix en fonction du nombre de mots
	*/
    function getPricings($word_count)
    {
        global $wpdb;
        // $this->chCurl = $this->init();
        // $_SESSION['lastSyncPricings']  = '';
        if (($_SESSION['lastSyncPricings'] == '' || time() - $_SESSION['lastSyncPricings'] > $this->dureeResyncro) || $word_count > 1) {
            $this->chCurl = $this->init();

            $url = $this->urlAPiPublic . '/reference_pricings';
            $infosUserTM = $this->getUserInfos();
            if (!isset($infosUserTM['locale']))
                $infosUserTM['locale'] = '';
            $url .= '/' . $infosUserTM['locale'];
            $url .= '?word_count=' . $word_count;
            // echo $url;
            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = curl_exec($this->chCurl);
            // timeout api
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (Pricings)!');
            }

            $resultInfos = curl_getinfo($this->chCurl);
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (Pricings)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            // print_r($result);
            // curl_close($this->chCurl);
            $aPricings = json_decode($result, true);
            if (isset($aPricings))
                $this->syncPricings($aPricings);
            // print_r($aPricings);
        } else {
            $table_templates = $wpdb->base_prefix . 'tm_reference_pricings';
            $reqLangs = $wpdb->prepare('SELECT * FROM ' . $table_templates, ' ORDER BY type');
            $datas = $wpdb->get_results($reqLangs, ARRAY_A);
            if (count($datas) != 0) {
                $numPrice = 0;
                $lastType = $datas[0]['type'];
                foreach ($datas as $data) {
                    if ($lastType != $data['type'])
                        $numPrice = 0;

                    $aPricings[$data['type']][$numPrice]['name'] = $data['name'];
                    $aPricings[$data['type']][$numPrice]['value'] = $data['value'];

                    $lastType = $data['type'];
                    $numPrice++;
                }
            }

            if (count($aPricings) == 0) {
                $_SESSION['lastSyncPricings'] = '';
            }
        }
        return $aPricings;
    }

    function syncPricings($array)
    {
        global $wpdb;

        $table_reference_pricings = $wpdb->base_prefix . 'tm_reference_pricings';
        if (is_array($array) && count($array) != 0 ) {
            // $wpdb->query('DELETE FROM '.$table_reference_pricings);
            foreach ($array as $type => $typePrice) {
                if (count($typePrice) != 0) {
                    foreach ($typePrice as $price) {
                        $req = '';
                        $reqChk = $wpdb->prepare('SELECT value FROM ' . $table_reference_pricings . ' WHERE type = "%s" AND name = "%s"', $type, $price['name']);
                        $chk = $wpdb->get_var($reqChk);
                        if ($chk == '') {
                            $sql = 'INSERT INTO ' . $table_reference_pricings . ' (type, name, value) VALUES (%s, %s, %s)';
                            $req = $wpdb->prepare($sql, $type, $price['name'], $price['value']);
                        }else if ($chk != $price['value']) {
                            $sql = 'UPDATE ' . $table_reference_pricings . ' SET value=%s WHERE type = "%s" AND name=%s)';
                            $req = $wpdb->prepare($sql, $price['value'], $type, $price['name']);
                        }

                        if ($req != '')
                            $wpdb->query($req);
                    }
                }
            }
            // $_SESSION['lastSyncPricings'] = time();
        }
    }

    /*
	* créer un nouveau projet sur textmaster
	* type : copywriting, translation, proofreading
	*/
    function makeProject($name, $type, $language_from = 'fr', $language_to = 'fr', $category, $project_briefing, $language_level, $quality = 'false', $expertise = 'false', $priority = 'false', $work_template = 'Default', $vocabulary_type = 'not_specified', $grammatical_person = 'not_specified', $target_reader_groups = 'not_specified', $authors = '')
    {
        $name = str_replace('&raquo;', '"', $name);
        $name = str_replace('&laquo;', '"', $name);
        $name = str_replace('&nbsp;', ' ', $name);
        $project['name'] = str_replace('&lsquo;', "'", $name);

        $project['ctype'] = $type;
        // pour les trads et relectures on force le template
        if ($type != 'copywriting' && $work_template == 'Default')
            $work_template = '1_title_1_paragraph';

        $project['language_from'] = $language_from;
        $project['language_to'] = $language_to;
        $project['category'] = $category;
        $project['project_briefing'] = $project_briefing;
        $project['same_author_must_do_entire_project'] = 'false';
 //   	$project['ability'] = 'test';

        $project['options']['language_level'] = $language_level;
        $project['options']['quality'] = $quality;
        $project['options']['expertise'] = $expertise;
        $project['options']['priority'] = $priority;

        if ($work_template == '')
            $work_template = 'Default';
        $project['work_template'] = $work_template;
        if ($vocabulary_type != '')
            $project['vocabulary_type'] = $vocabulary_type;
        if ($grammatical_person != '')
            $project['grammatical_person'] = $grammatical_person;
        if ($target_reader_groups != '')
            $project['target_reader_groups'] = $target_reader_groups;

        if (is_array($authors) && count($authors) != 0 && $authors[0] != '') {
            $project['textmasters'] = $authors;
        }
        // echo json_encode(array('project' => $project, 'tracker' => '504eefc88e36150002000002'));
        // print_r($project);
        // $project['custom_client'] = "{tracker_id: '4f1db74529e1673829000009', token_id: '504eefc88e36150002000002'}";
        $this->chCurl = $this->init();

        curl_setopt($this->chCurl, CURLOPT_POST, 1);
        curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('project' => $project, 'tracker' => '504eefc88e36150002000002'))); // , JSON_HEX_APOS | JSON_HEX_QUOT
        $url = $this->urlAPiClients . '/projects';
        // echo $url;
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
  //      print_r($result);
        $resultInfos = curl_getinfo($this->chCurl);
   //     print_r($resultInfos);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (make project)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (make project)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result, print_r($project, true));
        }
        // curl_close($this->chCurl);
        return $result;
    }

    /*
	* Lancer le projet
	*/
    function launchProject($idProjet)
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/launch';
        // echo $url;
        curl_setopt($this->chCurl, CURLOPT_INFILESIZE, 0);
        curl_setopt($this->chCurl, CURLOPT_PUT, true);
    	$aHeaders = $this->makeHearder();
    	$aHeaders[] ='Content-Length: 0';
    	curl_setopt($this->chCurl, CURLOPT_HTTPHEADER, $aHeaders);

        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = curl_exec($this->chCurl);
        $resultInfos = curl_getinfo($this->chCurl);
 //       print_r($result);
 //       print_r($resultInfos);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (launch project)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (launch project)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }

    /*
	* Pour recuperer les infos d'un projet
	*/
    function getProjetInfos($idProjet)
    {
        if ($idProjet != '') {
            $this->chCurl = $this->init();

            $url = $this->urlAPiClients . '/projects/' . $idProjet;
            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = json_decode(curl_exec($this->chCurl), true);
            $resultInfos = curl_getinfo($this->chCurl);
 //           print_r($result);
            // print_r($resultInfos);
            // timeout api
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (infos project)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (infos project)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
        } else {
            $result = 'Error no project id';
        }
        // curl_close($this->chCurl);
        return $result;
    }

    function getProjetStatus($idProjet)
    {
        $ret = '';
        $result = $this->getProjetInfos($idProjet);
        // print_r($result);
        if (is_array($result) && array_key_exists('status', $result))
            $ret = $result['status'];
        return $ret;
    }

    public static function getLibStatus($status)
    {
        switch ($status) {
            case 'waiting_assignment':
                $ret = __('En attente', 'textmaster');
                break;
            case 'in_creation':
                $ret = __('En attente', 'textmaster') . ' - ' . __('Incomplet', 'textmaster');
                break;
            case 'in_progress':
                $ret = __('En cours', 'textmaster');
                break;
            case 'quality_control':
                $ret = __('Controle qualité', 'textmaster');
                break;
            case 'in_review':
                $ret = __('A valider', 'textmaster');
                break;
            case 'incomplete':
                $ret = __('Incomplet', 'textmaster');
                break;
            case 'completed':
                $ret = __('Terminé', 'textmaster');
                break;
            case 'paused':
                $ret = __('En pause', 'textmaster');
                break;
            case 'canceled':
                $ret = __('Annulé', 'textmaster');
                break;
            default:
                $ret = __('NC', 'textmaster');
                break;
        }

        return $ret;
    }

    /*
	* Pour recuperer la liste des projets
	*/
    function getProjectList($status = '', $archived = false, $page = '', $per_page = '')
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects';
        if ($status != '')
            $url .= '?status=' . $status;
        if (!$archived) {
            if (strpos($url, '?') === false)
                $url .= '?archived=false';
            else
                $url .= '&archived=false';
        }
        if ($page != '') {
            if (strpos($url, '?') === false)
                $url .= '?page=' . $page;
            else
                $url .= '&page=' . $page;
        }
        if ($per_page != '') {
            if (strpos($url, '?') === false)
                $url .= '?per_page=' . $per_page;
            else
                $url .= '&per_page=' . $per_page;
        }
        // echo $url;
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (list project)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (list project)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }

    function addDocument($idProjet, $arrayDocs, $type = '')
    {
        $numDoc = 0;
        foreach ($arrayDocs as $arrayDoc) {
            if (!is_array($arrayDoc['original_content']) && trim($arrayDoc['original_content']) != '' && $arrayDoc['word_count'] == 0)
                $arrayDoc['word_count'] = 1;

            $arrayDoc['title'] = str_replace('&raquo;', '"', $arrayDoc['title']);
            $arrayDoc['title'] = str_replace('&laquo;', '"', $arrayDoc['title']);
            $arrayDoc['title'] = str_replace('&nbsp;', ' ', $arrayDoc['title']);
            $document[$numDoc]['title'] = str_replace('&lsquo;', "'", $arrayDoc['title']);
            $document[$numDoc]['word_count'] = $arrayDoc['word_count'];
            // $document[$numDoc]['perform_word_count'] = true;
            // $document[$numDoc]['word_count_finished'] =  plugins_url('ajax_countWords.php', __FILE__);
            // $content = $document['title']."\n\n".$original_content;
            if (is_array($arrayDoc['original_content'])) {
                $document[$numDoc]['type'] = 'key_value';
                $document[$numDoc]['original_content'] = $arrayDoc['original_content']; //json_encode($arrayDoc['original_content']);
            }else if ($arrayDoc['original_content'] != '')
                $document[$numDoc]['original_content'] = $arrayDoc['original_content'];
            else if ($type != 'copywriting')
                $document[$numDoc]['original_content'] = $arrayDoc['title'];
            $document[$numDoc]['word_count_rule'] = $arrayDoc['word_count_rule'];

            if (trim($arrayDoc['keyword_list']) != '') {
                $document[$numDoc]['keyword_list'] = $arrayDoc['keyword_list'] ;
                $document[$numDoc]['keywords_repeat_count'] = $arrayDoc['keywords_repeat_count'];
            }
            $numDoc++;
            // $this->countWords($arrayDoc['title'].' '.$arrayDoc['original_content'], $idProjet);
        }
        // $document['custom_client'] = "{tracker_id: '4f1db74529e1673829000009'}";
        // print_r( $document);
        $this->chCurl = $this->init();

        curl_setopt($this->chCurl, CURLOPT_POST, 1);
        if (PHP_VERSION_ID > 50300) {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('documents' => $document), JSON_HEX_APOS | JSON_HEX_QUOT));
        }else {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('documents' => $document)));
        }
        // echo json_encode(array('documents' => $document));
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/batch/documents';
        // echo $url;
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
//        print_r($result);
        // compatibilité ancienne version (sans batch)
        if ($numDoc == 1 && isset($result[0]))
            $result = $result[0];
        // print_r( $result);
        $resultInfos = curl_getinfo($this->chCurl);
//        print_r($resultInfos);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (add document)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (add document)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result, print_r($document, true));
        }
        // curl_close($this->chCurl);
        return $result;
    }

    function delDocument($idProjet, $idDocument)
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents/' . $idDocument;

        curl_setopt($this->chCurl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (del document)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (del document)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }

    /*
	   * Pour recuperer les infos d'un document
	*/
    function getDocumentInfos($idProjet, $idDocument)
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents/' . $idDocument;
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (infos document)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (infos document)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }

    function getDocumentStatus($idProjet, $idDocument)
    {
        $ret = '';
        $result = $this->getDocumentInfos($idProjet, $idDocument);
        // print_r($result);
        if (is_array($result) && array_key_exists('status', $result))
            $ret = $result['status'];
        return $ret;
    }

    /*
	   * Pour recuperer la liste des docs
	*/
    function getDocumentList($idProjet)
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents';
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (list document)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (list document)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        // print_r($result);
        return $result;
    }

    /*
	* Pour valider un document
	*/
    function valideDoc($idProjet, $idDocument, $satisfaction = '', $message = '')
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents/' . $idDocument . '/complete';
        // echo $url;
        curl_setopt($this->chCurl, CURLOPT_INFILESIZE, 0);
        curl_setopt($this->chCurl, CURLOPT_PUT, true);
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
 //       curl_setopt($this->chCurl, CURLOPT_CUSTOMREQUEST, "PUT");

        $arrayFeedback['satisfaction'] = $satisfaction;
        $arrayFeedback['message'] = $message;

//    	$aHeaders = $this->makeHearder();

        curl_setopt($this->chCurl, CURLOPT_POST, 1);
        if (PHP_VERSION_ID > 50300) {
        	$datasPost = json_encode($arrayFeedback, JSON_HEX_APOS | JSON_HEX_QUOT);
        }else {
        	$datasPost = json_encode($arrayFeedback);
        }
    	curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, $datasPost);
   // 	$aHeaders[] ='Content-Length: '.strlen($datasPost);
    //	curl_setopt($this->chCurl, CURLOPT_HTTPHEADER, $aHeaders);

        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
        //print_r($result);
        //print_r($resultInfos);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (valid document)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (valid document)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error (valid doc) ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }

    function getUserInfos()
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/users/me';

        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
  //  	print_r($result);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (infos user)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (infos user)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }
    function getSatisfactions()
    {
        $satisfaction['negative'] = __('Negative', 'textmaster');
        $satisfaction['neutral'] = __('Neutral', 'textmaster');
        $satisfaction['positive'] = __('Positive', 'textmaster');

        return $satisfaction;
    }
    // le support
    function addSupportMsg($idProjet, $idDocument, $message)
    {
        $this->chCurl = $this->init();
        $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents/' . $idDocument . '/support_messages';
        curl_setopt($this->chCurl, CURLOPT_INFILESIZE, 0);
        curl_setopt($this->chCurl, CURLOPT_URL, $url);

        $arraySuppot['support_message']['message'] = $message;
        curl_setopt($this->chCurl, CURLOPT_POST, 1);
        if (PHP_VERSION_ID > 50300) {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode($arraySuppot, JSON_HEX_APOS | JSON_HEX_QUOT));
        }else {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode($arraySuppot));
        }

        $result = json_decode(curl_exec($this->chCurl), true);
        $resultInfos = curl_getinfo($this->chCurl);
        // print_r($result);
        // timeout api
        if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
            $this->mailAlertSupport($url, false, $resultInfos);
            die('Error API timeout (valid document)!');
        }
        // les api ne sont pas dispo
        if ($resultInfos['http_code'] === 0) {
            $this->mailAlertSupport($url, true, $resultInfos);
            die('Error API connect (add support)!');
        }
        // code d'erreur HTTP
        if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
            $result = 'Error (valid doc) ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
            $this->mailAlertErrHttp($url, $result);
        }
        // curl_close($this->chCurl);
        return $result;
    }

    function getSupportMsgs($idProjet, $idDocument, $cache = false)
    {
    	global $wpdb;

		if (!$cache) {
            $this->chCurl = $this->init();
            $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents/' . $idDocument . '/support_messages';
            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = json_decode(curl_exec($this->chCurl), true);
            $resultInfos = curl_getinfo($this->chCurl);
            // timeout api
            if (curl_errno($this->chCurl) === CURLE_OPERATION_TIMEOUTED) {
                $this->mailAlertSupport($url, false, $resultInfos);
                die('Error API timeout (infos user)!');
            }
            // les api ne sont pas dispo
            if ($resultInfos['http_code'] === 0) {
                $this->mailAlertSupport($url, true, $resultInfos);
                die('Error API connect (infos user)!');
            }
            // code d'erreur HTTP
            if (curl_errno($this->chCurl) || $resultInfos['http_code'] >= 300) {
                $result = 'Error ' . $resultInfos['http_code'] . ' - ' . print_r($result, true) . '';
                $this->mailAlertErrHttp($url, $result);
            }
            // curl_close($this->chCurl);
            if (isset($result["support_messages"]))
                $this->syncSupportMsgs($idProjet, $idDocument, $result["support_messages"]);
        }else {
        	$table_support_messages = $wpdb->base_prefix . 'tm_support_messages';
        	$req = $wpdb->prepare('SELECT * FROM ' . $table_support_messages. ' WHERE idProjet=%s AND idDocument=%s  ORDER BY created_at', $idProjet, $idDocument);
			$result["support_messages"] = $wpdb->get_results($req, ARRAY_A);
        }
  //  	var_dump($result);

        return $result;
    }

    function syncSupportMsgs($idProjet, $idDoc, $array)
    {
        global $wpdb;

        $table_support_messages = $wpdb->base_prefix . 'tm_support_messages';

        if (count($array) != 0) {
            foreach ($array as $msg) {
                $sql = 'INSERT INTO ' . $table_support_messages . ' (idProjet, idDocument, content, message, author_id, written_by_you, written_by_author, author_ref, created_at)
						VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)';
                $req = $wpdb->prepare($sql, $idProjet, $idDoc, $msg['content'], $msg['message'], $msg['author_id'], $msg['written_by_you'], $msg['written_by_author'], $msg['author_ref'], $msg['created_at']['full']);
                $wpdb->query($req);
            }
        }
    }

    function setCallback()
    {
        $infosuser = $this->getUserInfos();
        $url = $this->urlAPiClients . '/users/' . $infosuser['id'];
        // print_r($infosuser);
        curl_setopt($this->chCurl, CURLOPT_INFILESIZE, 0);
        // curl_setopt($this->chCurl, CURLOPT_PUT, true);
        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        curl_setopt($this->chCurl, CURLOPT_CUSTOMREQUEST, "PUT");

        $user['callback']['word_count_finished']['url'] = plugins_url('ajax_countWords.php', __FILE__);
        $user['callback']['word_count_finished']['format'] = 'json';
        // echo  json_encode(array('user' => $user));
        // var_dump($this->chCurl);
        // curl_setopt($this->chCurl, CURLOPT_POST, 1);
        if (PHP_VERSION_ID > 50300) {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('user' => $user), JSON_HEX_APOS | JSON_HEX_QUOT));
        }else {
            curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('user' => $user)));
        }

        curl_setopt($this->chCurl, CURLOPT_URL, $url);
        $result = json_decode(curl_exec($this->chCurl), true);
        // print_r($result);
        $resultInfos = curl_getinfo($this->chCurl);
    }

    function make_junk_project()
    {
        $idJunk = get_option_tm('tm_junk_project');
        // print_r($idJunk);
        if ($idJunk == '' || $this->getProjetStatus($idJunk) != 'in_creation' || strpos($idJunk, 'Error') !== false) {
            $idJunk = $this->makeProject('junk', 'proofreading', 'fr', 'fr', CATEGORIE_DEFAUT, 'Utiliser pour le comptage de mots - merci de ne pas modifier ce projet', 'regular');
            update_option_tm('tm_junk_project', $idJunk['id']);

            $this->setCallback();
        }

        return $idJunk;
    }

    public function countWords($string, $id_post = '')
    {
        $countWords = 0;
        // calcul via textmaster
        $idProjet = get_option_tm('tm_junk_project');
        if ($idProjet != '') {
            // $idProjet = '545110a2051e950002002310';
            $title = '';
            $url = $this->urlAPiClients . '/projects/' . $idProjet . '/documents';

            $title = str_replace('&raquo;', '"', $title);
            $title = str_replace('&laquo;', '"', $title);
            $title = str_replace('&nbsp;', ' ', $title);
            $document['title'] = str_replace('&lsquo;', "'", $title);
            // $content = $document['title']."\n\n".$original_content;
            $document['custom_data'] = array('id_post' => $id_post);
            $document['original_content'] = $string;
            $document['word_count_rule'] = 0;
            $document['perform_word_count'] = true;
            // $document['callback']['word_count_finished'] =  plugins_url('ajax_countWords.php', __FILE__);
            // var_dump($document);
            $ch = $this->init();

            curl_setopt($this->chCurl, CURLOPT_POST, 1);
            if (PHP_VERSION_ID > 50300) {
                curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('document' => $document), JSON_HEX_APOS | JSON_HEX_QUOT));
            }else {
                curl_setopt($this->chCurl, CURLOPT_POSTFIELDS, json_encode(array('document' => $document)));
            }

            curl_setopt($this->chCurl, CURLOPT_URL, $url);
            $result = json_decode(curl_exec($this->chCurl), true);
            $resultInfos = curl_getinfo($this->chCurl);
            // var_dump($result);
            // var_dump($resultInfos);
        }
        // calcul interne
        $string = trim($string);
        $string = str_replace('&', "&amp;", $string);
        $string = str_replace('«', " ", $string);
        $string = str_replace('»', " ", $string);
        $string = str_replace('(', " ", $string);
        $string = str_replace(')', " ", $string);

        $string = preg_replace('/[^[\pL\pN]\s]*/ui', ' ', $string);
        // $string = preg_replace('/[^[[:alnum:]]\s]*/ui',' ', $string);
        $string = preg_replace('!\s+!u', ' ', $string);
        // $string = preg_replace("/[^a-zA-Z0-9]+/u", " ", $string);
        // echo $string;
        // $countWords = preg_match_all ('/[[:alnum:]]+/ui', $string, $matches);
        // $countWords = preg_match_all ('/[\pL\pN]+/ui', $string, $matches);
        $countWords = preg_match_all ('/[\\p{Zs}]+/ui', $string, $matches);
        // print_r($matches);
        // echo 'nb mots :'.$countWords.'<br>';
        $countWords++;

        return $countWords;
    }

    function mailAlertSupport($url, $force = false, $infosCurl)
    {
        if (MAIL_ALERT_ENABLE == true || $force) {
            $to = MAIL_ALERTE_SUPPORT;
            $subject = 'Alerte Plugin WordPress API (Timeout)';
            $message = "Le plugin WordPress n'a pas pu se connecter aux API TextMaster\n";
            if (!$force)
                $message .= "Timeout : " . TIMEOUT_API . "\n";
            $message .= "Requete API : " . $url . "\n";
            $message .= "URL du site : " . get_site_url() . "\n";
            $message .= "Retour cURL : " . print_r($infosCurl, true) . "\n";
            $headers = 'From: ' . MAIL_ALERTE_SUPPORT . "\r\n" .
            'Reply-To: ' . MAIL_ALERTE_SUPPORT . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);
        }
    }

    function mailAlertErrHttp($url, $msg, $paramsSend = '')
    {
        if (MAIL_ALERT_HTTP_ENABLE == true) {
            $to = MAIL_ALERTE_SUPPORT_HTTP;
            $subject = 'Alerte Plugin WordPress API (Erreur HTTP)';
            $message = "Plugin WordPress: Erreur HTTP API TextMaster\n";
            $message .= "Requete API : " . $url . "\n";
            $message .= "URL du site : " . get_site_url() . "\n";
            $message .= "URL  : " . $_SERVER['REQUEST_URI'] . "\n";
            $message .= "Erreur retourner : " . $msg . "\n";
            if ($paramsSend != '') {
                $message .= "Paramètres envoyés : " . $paramsSend . "\n";
            }

            $headers = 'From: ' . MAIL_ALERTE_SUPPORT_HTTP . "\r\n" .
            'Reply-To: ' . MAIL_ALERTE_SUPPORT_HTTP . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);
        }
    }
}

?>
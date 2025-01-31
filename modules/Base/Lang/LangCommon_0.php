<?php
/**
 * Lang class.
 *
 * This class provides translations manipulation.
 *
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-base
 * @subpackage lang
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

/**
 * This class provides translations manipulation.
 * Translation files are kept in 'modules/Lang/translations'.
 * Http server user should have write access to those files.
 */
class Base_LangCommon extends ModuleCommon {
	public static function get_complete_languages() {
		return explode(',', file_get_contents('modules/Base/Lang/complete'));
	}
	/**
	 * Don not use this function to translate, use the __() call instead.
	 */
	public static function t($original, array $arg=array()) { return self::translate(null,$original,$arg); }
	/**
	 * Don not use this function to translate, use the __() call instead.
	 */
	public static function ts($group, $original, array $arg=array()) { return self::translate($original, $arg); }
	/**
	 * Don not use this function to translate, use the __() call instead.
	 */
	public static function translate($original, array $arg=array(), $translate = true) {
		if (!$original) return '';
//		if ($original[0]=='*') trigger_error('Re-translation '.$original);
		global $translations;
		global $custom_translations;

		if(!isset($translations))
			self::load();

		if(isset($translations[$original]) && $translations[$original] && $translate)
			$translated = $translations[$original];
		else
			$translated = $original;
		
		if (isset($custom_translations[$original]) && $custom_translations[$original] && $translate)
			$translated = $custom_translations[$original];
			
		if (!isset($translations[$original]) && !isset($custom_translations[$original])) {
			$custom_translations[$original] = '';
			Base_LangCommon::append_custom(null, array($original => ''));
		}

		$translated = @vsprintf($translated,$arg);
		if ($original && !$translated) $translated = '<b>Invalid translation, misused char % (use double %%)</b>';
		
		return $translated;
	}
	
	public static function print_flag($code, $label, $href='') {
		$file = 'modules/Base/Lang/theme/flag_'.$code.'.png';
		if (!file_exists($file))
			$file = 'modules/Base/Lang/theme/flag_placeholder.png';
		print(	'<a '.$href.' class="flag_button">'.
					'<img class="flag" src="'.$file.'" />'.
					'<span class="label">'.$label.'</span>'.
				'</a>');
	}

    public static function get_base_languages() {
        $files = scandir('modules/Base/Lang/lang');
        $langs = array();
        $all_langs = self::get_all_languages();
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'php' && strlen($file) == 6) {
                $lang_code = substr($file, 0, 2);
                if (isset($all_langs[$lang_code])) {
                    $langs[$lang_code] = $all_langs[$lang_code];
                }
            }
        }
        return $langs;
    }

    public static function get_all_languages() {
        return array (
            'ab' => 'Abkhaz',
            'aa' => 'Afar',
            'af' => 'Afrikaans',
            'ak' => 'Akan',
            'sq' => 'Albanian',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'an' => 'Aragonese',
            'hy' => 'Armenian',
            'as' => 'Assamese',
            'av' => 'Avaric',
            'ae' => 'Avestan',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'bm' => 'Bambara',
            'ba' => 'Bashkir',
            'eu' => 'Basque',
            'be' => 'Belarusian',
            'bn' => 'Bengali',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bs' => 'Bosnian',
            'br' => 'Breton',
            'bg' => 'Bulgarian',
            'my' => 'Burmese',
            'ca' => 'Catalan',
            'ch' => 'Chamorro',
            'ce' => 'Chechen',
            'ny' => 'Chichewa',
            'zh' => 'Chinese',
            'cv' => 'Chuvash',
            'kw' => 'Cornish',
            'co' => 'Corsican',
            'cr' => 'Cree',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'dv' => 'Divehi',
            'nl' => 'Dutch',
            'dz' => 'Dzongkha',
            'en' => 'English',
            'eo' => 'Esperanto',
            'et' => 'Estonian',
            'ee' => 'Ewe',
            'fo' => 'Faroese',
            'fj' => 'Fijian',
            'fi' => 'Finnish',
            'fr' => 'French',
            'ff' => 'Fula',
            'gl' => 'Galician',
            'ka' => 'Georgian',
            'de' => 'German',
            'el' => 'Greek',
            'gn' => 'Guaraní',
            'gu' => 'Gujarati',
            'ht' => 'Haitian',
            'ha' => 'Hausa',
            'he' => 'Hebrew',
            'hz' => 'Herero',
            'hi' => 'Hindi',
            'ho' => 'Hiri Motu',
            'hu' => 'Hungarian',
            'ia' => 'Interlingua',
            'id' => 'Indonesian',
            'ie' => 'Interlingue',
            'ga' => 'Irish',
            'ig' => 'Igbo',
            'ik' => 'Inupiaq',
            'io' => 'Ido',
            'is' => 'Icelandic',
            'it' => 'Italian',
            'iu' => 'Inuktitut',
            'ja' => 'Japanese',
            'jv' => 'Javanese',
            'kl' => 'Kalaallisut',
            'kn' => 'Kannada',
            'kr' => 'Kanuri',
            'ks' => 'Kashmiri',
            'kk' => 'Kazakh',
            'km' => 'Khmer',
            'ki' => 'Kikuyu',
            'rw' => 'Kinyarwanda',
            'ky' => 'Kyrgyz',
            'kv' => 'Komi',
            'kg' => 'Kongo',
            'ko' => 'Korean',
            'ku' => 'Kurdish',
            'kj' => 'Kwanyama',
            'la' => 'Latin',
            'lb' => 'Luxembourgish',
            'lg' => 'Ganda',
            'li' => 'Limburgish',
            'ln' => 'Lingala',
            'lo' => 'Lao',
            'lt' => 'Lithuanian',
            'lu' => 'Luba-Katanga',
            'lv' => 'Latvian',
            'gv' => 'Manx',
            'mk' => 'Macedonian',
            'mg' => 'Malagasy',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mt' => 'Maltese',
            'mi' => 'Māori',
            'mr' => 'Marathi (Marāṭhī)',
            'mh' => 'Marshallese',
            'mn' => 'Mongolian',
            'na' => 'Nauru',
            'nv' => 'Navajo',
            'nb' => 'Norwegian Bokmål',
            'nd' => 'North Ndebele',
            'ne' => 'Nepali',
            'ng' => 'Ndonga',
            'nn' => 'Norwegian Nynorsk',
            'no' => 'Norwegian',
            'ii' => 'Nuosu',
            'nr' => 'South Ndebele',
            'oc' => 'Occitan',
            'oj' => 'Ojibwe',
            'cu' => 'Old Church Slavonic',
            'om' => 'Oromo',
            'or' => 'Oriya',
            'os' => 'Ossetian',
            'pa' => 'Panjabi',
            'pi' => 'Pāli',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'ps' => 'Pashto',
            'pt' => 'Portuguese',
            'qu' => 'Quechua',
            'rm' => 'Romansh',
            'rn' => 'Kirundi',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sa' => 'Sanskrit (Saṁskṛta)',
            'sc' => 'Sardinian',
            'sd' => 'Sindhi',
            'se' => 'Northern Sami',
            'sm' => 'Samoan',
            'sg' => 'Sango',
            'sr' => 'Serbian',
            'gd' => 'Scottish Gaelic',
            'sn' => 'Shona',
            'si' => 'Sinhala',
            'sk' => 'Slovak',
            'sl' => 'Slovene',
            'so' => 'Somali',
            'st' => 'Southern Sotho',
            'es' => 'Spanish',
            'su' => 'Sundanese',
            'sw' => 'Swahili',
            'ss' => 'Swati',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'tg' => 'Tajik',
            'th' => 'Thai',
            'ti' => 'Tigrinya',
            'bo' => 'Tibetan Standard',
            'tk' => 'Turkmen',
            'tl' => 'Tagalog',
            'tn' => 'Tswana',
            'to' => 'Tonga',
            'tr' => 'Turkish',
            'ts' => 'Tsonga',
            'tt' => 'Tatar',
            'tw' => 'Twi',
            'ty' => 'Tahitian',
            'ug' => 'Uighur',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            've' => 'Venda',
            'vi' => 'Vietnamese',
            'vo' => 'Volapük',
            'wa' => 'Walloon',
            'cy' => 'Welsh',
            'wo' => 'Wolof',
            'fy' => 'Western Frisian',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'yo' => 'Yoruba',
            'za' => 'Zhuang',
            'zu' => 'Zulu',
        );
    }

	/**
	 * For internal use only.
	 */
	public static function update_translations() {
		global $translations;
		set_time_limit(0);
		$ret = DB::Execute('SELECT * FROM modules');
		$trans_backup = $translations;
		$trans = array();
		while($row = $ret->FetchRow()) {
			$mod_name = $row['name'];
			if ($mod_name=='Base') continue;
			if ($mod_name=='Tests') continue;
			$directory = 'modules/'.str_replace('_','/',$mod_name).'/lang';
			if (!is_dir($directory)) continue;
			$content = scandir($directory);
			foreach ($content as $name){
				if($name == '.' || $name == '..' || preg_match('/^[\.~]/',$name)) continue;
				$dot = strpos($name,'.');
				$langcode = substr($name,0,$dot);
				if (strtolower(substr($name,$dot+1))!='php') continue;
				if(!isset($trans[$langcode]))
					$trans[$langcode] = array();
				$translations = $trans[$langcode];
				ob_start();
				include($directory.'/'.$name);
				ob_get_clean();
				$trans[$langcode] = $translations;
			}
		}	
		foreach($trans as $langcode=>$ttt) {
			Base_LangCommon::save_base($langcode, $ttt);
		}
		$translations = $trans_backup;
        self::refresh_cache();
	}
	
	public static function append_base($lang, $arr) {
		self::append('base', $lang, $arr);
	}
	public static function append_custom($lang, $arr) {
		self::append('custom', $lang, $arr);
	}
	private static function append($to='base', $lang, $arr) {
		if ($to!=='base') $to = 'custom';
		if ($lang===null) $lang = self::get_lang_code();
		if (!$lang) return;
		$exists = file_exists(DATA_DIR.'/Base_Lang/'.$to.'/'.$lang.'.php');
		$f = @fopen(DATA_DIR.'/Base_Lang/'.$to.'/'.$lang.'.php', 'a');
		if(!$f)	return false;
		if(!$exists) fwrite($f,"<?php\n".'global $custom_translations;'."\n");
		if (flock($f, LOCK_EX)) {
			foreach($arr as $k=>$v)
				fwrite($f, '$'.($to=='custom'?'custom_':'').'translations[\''.addcslashes($k,'\\\'').'\']=\''.addcslashes($v,'\\\'')."';\n");

			flock($f, LOCK_UN);
			fclose($f);
		}
		return true;
	}

	/**
	 * For internal use only.
	 */
	public static function save_base($lang = null, $arr = array()) {
		global $translations;
		//save translations file
		if (!isset($lang)) $lang = self::get_lang_code();
		$f = @fopen(DATA_DIR.'/Base_Lang/base/'.$lang.'.php', 'w');
		if(!$f)	return false;

		fwrite($f, "<?php\n");
		fwrite($f, "/**\n * Translation file.\n * @package epesi-translations\n * @subpackage $lang\n */\n");
		fwrite($f, 'global $translations;'."\n");
		foreach($arr as $k=>$v) {
		        if(is_array($v) || is_array($k)) continue;
			fwrite($f, '$translations[\''.addcslashes($k,'\\\'').'\']=\''.addcslashes($v,'\\\'')."';\n");
		}

		fclose($f);
		return true;
	}

	/**
	 * For internal use only.
	 */
	public static function load($lang_code = null) {
		global $translations;
		global $custom_translations;
        self::$lang_code = $lang_code;
        if ($lang_code === null)
            $lang_code = self::get_lang_code();
		if (!$lang_code) return;

		$translations = array();
		$custom_translations = array();

		@include(DATA_DIR.'/Base_Lang/base/'. $lang_code .'.php');
		if(!is_array($translations))
			$translations=array();

		@include(DATA_DIR.'/Base_Lang/custom/'. $lang_code .'.php');
		if(!is_array($custom_translations))
			$custom_translations=array();
			
		eval_js_once('Epesi.default_indicator="'.__('Loading...').'";');
	}
	
	private static $lang_code;

	public static function get_lang_code() {
		if(defined('FORCE_LANG_CODE')) return FORCE_LANG_CODE;
		if(!isset(self::$lang_code)) {
			if (!Acl::is_user() ||
				ModuleManager::is_installed('Base/User/Settings')==-1 ||
				!Variable::get('allow_lang_change', false))
					return Variable::get('default_lang');
			if(class_exists('Base_User_SettingsCommon'))
				self::$lang_code = Base_User_SettingsCommon::get('Base_Lang_Administrator','language');
		}
		return self::$lang_code;
	}

	/**
	 * For internal use only.
	 */
	public static function install_translations($mod_name,$lang_dir='lang') {
		global $translations;
		$directory = 'modules/'.str_replace('_','/',$mod_name).'/'.$lang_dir;
		if (!is_dir($directory)) return;
		$content = scandir($directory);
		$trans_backup = $translations;
		self::update_translations(); // cleanup translations file
		foreach ($content as $name){
			if($name == '.' || $name == '..' || preg_match('/^[\.~]/',$name)) continue;
			$langcode = substr($name,0,strpos($name,'.'));
			$translations = array(); // prepare to receive translations
			include($directory.'/'.$name); // read translations
			Base_LangCommon::append_base($langcode, $translations); // extend base translations
		}
		$translations = $trans_backup;
		self::refresh_cache();
	}

	/**
	 * For internal use only.
	 */
	public static function new_langpack($code) {
		file_put_contents(DATA_DIR.'/Base_Lang/base/'.$code.'.php',file_get_contents(DATA_DIR.'/Base_Lang/base/en.php'));
		file_put_contents(DATA_DIR.'/Base_Lang/custom/'.$code.'.php','<?php'."\n".'global $custom_translations;'."\n");
		self::refresh_cache();
	}

	/**
	 * For internal use only.
	 */
	public static function get_langpack($code, $s='base') {
		if (!is_file(DATA_DIR.'/Base_Lang/'.$s.'/'.$code.'.php')) return array();
		global $translations;
		global $custom_translations;
		if ($s=='base')
			$translations = array();
		else
			$custom_translations = array();
		include(DATA_DIR.'/Base_Lang/'.$s.'/'.$code.'.php');
		if ($s=='base') {
			$langpack = $translations;
			$translations = array();
		} else {
			$langpack = $custom_translations;
			$custom_translations = array();
		}
		include_once(DATA_DIR.'/Base_Lang/'.$s.'/'.self::get_lang_code().'.php');
		return $langpack;
	}

	public static function refresh_cache() {
		$ls_langs = scandir(DATA_DIR.'/Base_Lang/base');
		$langs = array();
		foreach ($ls_langs as $entry)
			if (pathinfo($entry, PATHINFO_EXTENSION) == 'php') {
				$lang = basename($entry, '.php');
				$langs[] = $lang;
			}
		file_put_contents(DATA_DIR.'/Base_Lang/cache',implode(',',$langs));
	}

	public static function get_installed_langs() {
		$ls_langs = explode(',',@file_get_contents(DATA_DIR.'/Base_Lang/cache'));
        $all_langs = self::get_all_languages();
        $ret = array();
        foreach ($ls_langs as $lang_code) {
            if (isset($all_langs[$lang_code]))
                $ret[$lang_code] = $all_langs[$lang_code] . " ($lang_code)";
        }
		return $ret;
	}
}

function __($string, $arg2=array()) {
	return Base_LangCommon::translate($string, $arg2);
}
function _V($string, $arg2=array()) { // ****** _V Definition - variable translations
	return Base_LangCommon::translate($string, $arg2);
}
function _M($string, $arg2=array()) { // ****** _M Definition - mark translations - doesn't translate, only marks string to translate
	return Base_LangCommon::translate($string, $arg2, false);
}

Module::register_method('t',array('Base_LangCommon','ts')); // DEPRECATED
Module::register_method('ht',array('Base_LangCommon','ts')); // DEPRECATED
on_init(array('Base_LangCommon','load'));

?>

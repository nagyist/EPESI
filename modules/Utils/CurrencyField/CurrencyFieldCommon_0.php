<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-utils
 * @subpackage CurrencyField
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_CurrencyFieldCommon extends ModuleCommon {
	public static function format($val, $currency=null) {
        static $params_cache = array();
		if (!isset($currency) || !$currency) {
			$val = self::get_values($val);
			$currency = $val[1];
			$val = $val[0];
		}
        if (!isset($params_cache[$currency]))
            $params_cache[$currency] = DB::GetRow('SELECT * FROM utils_currency WHERE id=%d', array($currency));
		$params = $params_cache[$currency];
		$dec_delimiter = $params['decimal_sign'];
		$thou_delimiter = $params['thousand_sign'];
		$dec_digits = $params['decimals'];
		$currency = $params['symbol'];
		$pos_before = $params['pos_before'];
		
		if (!$val) $val = '0';
		$val = str_replace(array('.',','),$dec_delimiter,$val);
		if (!strrchr($val,$dec_delimiter)) $val .= $dec_delimiter; 
		$cur = explode($dec_delimiter, $val);
		if (!isset($cur[1])) $cur[1] = ''; 
		$cur[1] = str_pad($cur[1], $dec_digits, '0');
		$val = $cur[0].'.'.$cur[1];
		$ret = number_format($val, $dec_digits, $dec_delimiter, $thou_delimiter);
		if ($pos_before) $ret = $currency.'&nbsp;'.$ret;
		else $ret = $ret.'&nbsp;'.$currency;
		return $ret;
	}

    public static function is_empty($p) {
        return strpos($p,'__') === 0;
    }
	
	public static function get_values($p) {
		if (!is_array($p)) $p = explode('__', $p);
		if (!isset($p[1])) $p[1] = Base_User_SettingsCommon::get('Utils_CurrencyField', 'default_currency');
		return $p;
	}
	
	public static function format_default($v, $c=null) {
		if ($c===null) {
			$c = explode('__',$v);
			if (!isset($c[1])) {
				$c = 1;
			} else {
				$v = $c[0];
				$c = $c[1];
			}
		}
		$v = round($v,self::get_precission($c));
		$v = str_replace('.', self::get_decimal_point($c), $v);
		return $v.'__'.$c;
	}

	public static function user_settings() {
		$currency_options = DB::GetAssoc('SELECT id, code FROM utils_currency WHERE active=1');
		$decimal_point_options = DB::GetAssoc('SELECT id, decimal_sign FROM utils_currency WHERE active=1');
		return array(__('Regional Settings')=>array(
				array('name'=>'currency_header', 'label'=>__('Currency'), 'type'=>'header'),
				array('name'=>'default_currency','label'=>__('Default currency'),'type'=>'select','values'=>$currency_options,'default'=>1),
				array('name'=>'decimal_point','label'=>__('Currency decimal point'),'type'=>'select','values'=>$decimal_point_options,'default'=>1)
					));
	}
	
	public static function get_decimal_point($c = null) {
	    if ($c!==null) return DB::GetOne('SELECT decimal_sign FROM utils_currency WHERE id=%d', array($c));
		static $cache = null;
		if ($cache==null) $cache = DB::GetOne('SELECT decimal_sign FROM utils_currency WHERE id=%d', array(Base_User_SettingsCommon::get('Utils_CurrencyField','decimal_point')));
		return $cache;
	}
	
	public static function get_thousand_point($c) {
		return DB::GetOne('SELECT thousand_sign FROM utils_currency WHERE id=%d', array($c));
	}
	
	public static function get_id_by_code($code) {
		static $cache;
		if(!isset($cache)) $cache = array();
		if(!isset($cache[$code]))
			$cache[$code] = DB::GetOne('SELECT id FROM utils_currency WHERE code=%s', array($code));
		return $cache[$code];
	}
	
	public static function get_code($id) {
		static $cache;
		if(!isset($cache)) $cache = array();
		if(!isset($cache[$id]))
			$cache[$id] = DB::GetOne('SELECT code FROM utils_currency WHERE id=%d', array($id));
		return $cache[$id];
	}
	
	public static function get_precission($arg) {
		static $cache = array();
		if (!isset($cache[$arg])) $cache[$arg] = DB::GetOne('SELECT decimals FROM utils_currency WHERE id=%d', array($arg));
		return $cache[$arg];
	}
	
	public static function get_currencies() {
		static $cache=null;
		if ($cache===null) $cache = DB::GetAssoc('SELECT id, code FROM utils_currency WHERE active=1');
		return $cache;
	}
	
	public static function admin_caption() {
		return array('label'=>__('Currencies'), 'section'=>__('Regional Settings'));
	}
	
	public static function get_symbol($arg) {
		static $cache = array();
		if (!isset($cache[$arg])) $cache[$arg] = DB::GetOne('SELECT symbol FROM utils_currency WHERE id=%d', array($arg));
		return $cache[$arg];
	}
	public static function get_symbol_position($arg) {
		static $cache = array();
		if (!isset($cache[$arg])) $cache[$arg] = DB::GetOne('SELECT pos_before FROM utils_currency WHERE id=%d', array($arg));
		return $cache[$arg];
	}

    /**
     * Parse currency using existing currencies set in the system.
     *
     * @param $string Currency string to parse
     *
     * @return array|null null on failure and array(value, currency_id) on success - like get_values returns.
     */
    public static function parse_currency($string) {
        $string = html_entity_decode($string);
        $string = preg_replace('/[\pZ\pC\s]/u', '', $string); // remove whitespaces, including unicode nbsp
        $currencies = Utils_CurrencyFieldCommon::get_currencies();
        foreach (array_keys($currencies) as $cur_id) {
            $symbol = Utils_CurrencyFieldCommon::get_symbol($cur_id);
            $symbol_pos_before = Utils_CurrencyFieldCommon::get_symbol_position($cur_id);
            // check for symbol
            if ($symbol_pos_before) {
                if (strpos($string, $symbol) === 0) {
                    $string = substr($string, strlen($symbol));
                } else continue;
            } else {
                $pos_of_sym = strlen($string) - strlen($symbol);
                if (strrpos($string, $symbol) == $pos_of_sym) {
                    $string = substr($string, 0, $pos_of_sym);
                } else continue;
            }
            // separate by decimal point
            $exp = explode(Utils_CurrencyFieldCommon::get_decimal_point($cur_id), $string);
            if (count($exp) > 2)
                continue;
            $fraction = count($exp) == 2 ? $exp[1] : '0';
            $int = $exp[0];
            if (!preg_match('/^\d+$/', $fraction))
                continue;
            $th_point = Utils_CurrencyFieldCommon::get_thousand_point($cur_id);
            if (strlen($th_point)) {
                $thparts = explode($th_point, $int);
                if (count($thparts) > 1) {
                    for ($i = 1; $i < count($thparts); $i++)
                        if (strlen($thparts[$i]) != 3)
                            continue 2;
                }
                $int = str_replace($th_point, '', $int);
            }
            if (preg_match('/^\d+$/', $int)) {
                return array($int . '.' . $fraction, $cur_id);
            }
        }
        return null;
    }

}

$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['currency'] = array('modules/Utils/CurrencyField/currency.php','HTML_QuickForm_currency');

?>

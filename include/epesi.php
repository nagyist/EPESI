<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @version 1.0
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @license SPL
 * @package epesi-base
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Epesi {
	private static $jses = array();
	private static $load_jses = array();
	private static $load_csses = array();
	private static $txts = '';

	/**
	 * Returns ajax temporary session.
	 *
	 * @return mixed ajax temporary session
	 */
	/**
	 * Executes list of javascrpit commands gathered with js() function.
	 */
	public final static function send_output() {
		print(self::get_output());
	}

	public final static function get_output() {
		$ret = '';
		foreach(self::$load_csses as $f)
			$ret .= 'Epesi.load_css(\''.self::escapeJS($f,false).'\');';
		foreach(self::$load_jses as $f)
			$ret .= 'Epesi.load_js(\''.self::escapeJS($f,false).'\');';
		$ret .= self::$txts;
		$jjj = '';
		foreach(self::$jses as $cc) {
			$x = rtrim($cc[0],';');
			if($x) $jjj.=$x.';';
		}
		if($jjj!=='')
			$ret .= 'Epesi.append_js(\''.self::escapeJS($jjj,false).'\');';
		self::clean();
		//file_put_contents('data/jses',implode(self::$jses,"\n\n\n"));
		return $ret;
	}

	public final static function clean() {
		self::$txts = '';
		self::$jses = array();
		self::$load_jses = array();
	}

	public final static function load_js($file) {
		if(!is_string($file) || strlen($file)==0) return false;
		if (!isset($_SESSION['client']['__loaded_jses__'][$file])) {
			self::$load_jses[] = $file;
			$_SESSION['client']['__loaded_jses__'][$file] = true;
			return true;
		}
		return false;
	}

	public final static function load_css($u) {
		if(!is_string($u) || strlen($u)==0) return false;
		if (!isset($_SESSION['client']['__loaded_csses__'][$u])) {
			self::$load_csses[] = $u;
			$_SESSION['client']['__loaded_csses__'][$u] = true;
			return true;
		}
		return false;
	}

	public final static function text($txt,$id,$type='instead') {
		self::$txts .= 'Epesi.text(\''.self::escapeJS($txt,false).'\',\''.self::escapeJS($id,false).'\',\''.self::escapeJS($type{0},false).'\');';
	}

	public final static function alert($txt,$del = true) {
		self::js('alert(\''.self::escapeJS($txt,false).'\')',$del);
	}

	public final static function redirect($addr='') {
		self::js('document.location=\''.self::escapeJS($addr,false).'\'');
	}

	/**
	 * Extends list of javascript commands to execute
	 *
	 * @param string javascript code
	 */
	public final static function js($js,$del_on_loc=true) {
		if(!is_string($js) || strlen($js)==0) return false;
		$js = rtrim($js,';');
		if(STRIP_OUTPUT)
			$js = strip_js($js);
		self::$jses[] = array($js,$del_on_loc);
		return true;
	}

	/**
	 * Escapes special characters in js code.
	 *
	 * @param string js code to escape
	 * @return string escaped js code
	 */
	public final static function escapeJS($str,$double=true,$single=true) {
		$arr = array (
			'\\' => '\\\\',
			"\r" => '\\r',
			"\n" => '\\n',
			'</' => '<\/'
		);
		if($single)
			$arr["'"] = "\\'";
		if($double)
			$arr['"'] = '\\"';
		// borrowed from smarty
		return strtr($str, $arr);
	}

	//============================================
	public static $content;

	private static function check_firstrun() {
		$first_run = false;

		foreach(ModuleManager::$modules as $name=>$version) {
			if($name=='FirstRun') $first_run=true;
		}
		ob_start();
		if(!$first_run && !ModuleManager :: install('FirstRun')) {
			$x = ob_get_contents();
			ob_end_clean();
			trigger_error('Unable to install default module: '.$x,E_USER_ERROR);
		}
		ob_end_clean();
	}

	private static function go(& $m) {
		//define key so it's first in array
		$path = $m->get_path();

		if(method_exists($m,'construct')) {
			ob_start();
			call_user_func_array(array($m,'construct'),array());
			ob_end_clean();
		}

		self::$content[$path]['span'] = 'main_content';
		self::$content[$path]['module'] = & $m;
		if(MODULE_TIMES)
		    $time = microtime(true);
		//go
		ob_start();
		if (!$m->check_access('body')) {
			print ('You don\'t have permission to access default module! It\'s probably wrong configuration.');
		} else
			$m->body();
		self::$content[$path]['value'] = ob_get_contents();
		ob_end_clean();
		self::$content[$path]['js'] = $m->get_jses();

		if(MODULE_TIMES)
		    self::$content[$path]['time'] = microtime(true)-$time;
	}

	public static function debug($msg=null) {
		if(DEBUG) {
			static $msgs = '';
			if($msg) $msgs .= $msg;
			return $msgs;
		}
	}

	public static function process($url, $history_call=false,$refresh=false) {
		if(MODULE_TIMES)
			$time = microtime(true);

		$url = str_replace('&amp;','&',$url);

		if($url) {
			parse_str($url, $_POST);
			$_GET = $_REQUEST = & $_POST;
		}

		ModuleManager::load_modules();
		self::check_firstrun();

		if($history_call==='0')
		    History::clear();
		elseif($history_call)
		    History::set_id($history_call);

		//on init call methods...
		$ret = on_init(null,null,null,true);
		foreach($ret as $k)
			call_user_func_array($k['func'],$k['args']);

		$root = & ModuleManager::create_root();
		self::go($root);

		//on exit call methods...
		$ret = on_exit(null,null,null,true);
		foreach($ret as $k)
			call_user_func_array($k['func'],$k['args']);

		//go somewhere else?
		$loc = location(null,true);
		if($loc!==false) {
			if(isset($_REQUEST['__action_module__']))
				$loc['__action_module__'] = $_REQUEST['__action_module__'];

			//clean up
			foreach(self::$content as $k=>$v)
				unset(self::$content[$k]);

			foreach(self::$jses as $k=>$v)
				if($v[1]) unset(self::$jses[$k]);

			//go
			return self::process('__location&' . http_build_query($loc),false,true);
		}

		if(DEBUG || MODULE_TIMES || SQL_TIMES) {
			$debug = '';
			if(DEBUG && ($debug_diff = @include_once('tools/Diff.php'))) {
				require_once 'tools/Text/Diff/Renderer/inline.php';
				$diff_renderer = &new Text_Diff_Renderer_inline();
			}
		}

		//clean up old modules
		if(isset($_SESSION['client']['__module_content__'])) {
			$to_cleanup = array_keys($_SESSION['client']['__module_content__']);
			foreach($to_cleanup as $k) {
				$mod = ModuleManager::get_instance($k);
				if($mod === null) {
					$xx = explode('/',$k);
					$yy = explode('|',$xx[count($xx)-1]);
					$mod = $yy[0];
					if(!is_callable(array($mod.'Common','destroy')) || !call_user_func(array($mod.'Common','destroy'),$k,isset($_SESSION['client']['__module_vars__'][$k])?$_SESSION['client']['__module_vars__'][$k]:null)) {
						if(DEBUG)
							$debug .= 'Clearing mod vars & module content '.$k.'<br>';
						unset($_SESSION['client']['__module_vars__'][$k]);
						unset($_SESSION['client']['__module_content__'][$k]);
					}
				}
			}
		}

		$reloaded = array();
		foreach (self::$content as $k => $v) {
			$reload = $v['module']->get_reload();
			$parent = $v['module']->get_parent_path();
			if(DEBUG && REDUCING_TRANSFER) {
				$debug .= '<hr style="height: 3px; background-color:black">';
				$debug .= '<b> Checking '.$k.', &nbsp;&nbsp;&nbsp; parent='.$v['module']->get_parent_path().'</b><ul>'.
					'<li>Force - '.(isset($reload)?print_r($reload,true):'not set').'</li>'.
					'<li>First display - '.(isset ($_SESSION['client']['__module_content__'][$k])?'no</li>'.
					'<li>Content changed - '.(($_SESSION['client']['__module_content__'][$k]['value'] !== $v['value'])?'yes':'no').'</li>'.
					'<li>JS changed - '.(($_SESSION['client']['__module_content__'][$k]['js'] !== $v['js'])?'yes':'no'):'yes').'</li>'.
					'<li>Parent reloaded - '.(isset($reloaded[$parent])?'yes':'no').'</li>'.
					'<li>History call - '.(($history_call)?'yes':'no').'</li>'.
					'</ul>';
			}
			if (!REDUCING_TRANSFER
				 || ((!isset($reload) && (!isset ($_SESSION['client']['__module_content__'][$k])
				 || $_SESSION['client']['__module_content__'][$k]['value'] !== $v['value'] //content differs
				 || $_SESSION['client']['__module_content__'][$k]['js'] !== $v['js']))
				 || $history_call
				 || $reload == true || isset($reloaded[$parent]))) { //force reload or parent reloaded
				if(DEBUG && isset($_SESSION['client']['__module_content__'])){
					$debug .= '<b>Reloading: '.(isset($v['span'])?';&nbsp;&nbsp;&nbsp;&nbsp;span='.$v['span'].',':'').'&nbsp;&nbsp;&nbsp;&nbsp;triggered='.(($reload==true)?'force':'auto').',&nbsp;&nbsp;</b><hr><b>New value:</b><br><pre>'.htmlspecialchars($v['value']).'</pre>'.(isset($_SESSION['client']['__module_content__'][$k]['value'])?'<hr><b>Old value:</b><br><pre>'.htmlspecialchars($_SESSION['client']['__module_content__'][$k]['value']).'</pre>':'');
					if($debug_diff && isset($_SESSION['client']['__module_content__'][$k]['value'])) {
						$xxx = new Text_Diff(explode("\n",$_SESSION['client']['__module_content__'][$k]['value']),explode("\n",$v['value']));
						$debug .= '<hr><b>Diff:</b><br><pre>'.$diff_renderer->render($xxx).'</pre>';
					}
					$debug .= '<hr style="height: 5px; background-color:black">';
				}

				if(isset($v['span']))
					self::text($v['value'], $v['span']);
				if($v['js'])
					self::js(join(";",$v['js']));
				if (REDUCING_TRANSFER) {
					$_SESSION['client']['__module_content__'][$k]['value'] = $v['value'];
					$_SESSION['client']['__module_content__'][$k]['js'] = $v['js'];
				}
				$_SESSION['client']['__module_content__'][$k]['parent'] = $parent;
				$reloaded[$k] = true;
				if(method_exists($v['module'],'reloaded')) $v['module']->reloaded();
			}
		}

		foreach($_SESSION['client']['__module_content__'] as $k=>$v)
			if(!array_key_exists($k,self::$content) && isset($reloaded[$v['parent']])) {
				if(DEBUG)
					$debug .= 'Reloading missing '.$k.'<hr>';
				if(isset($v['span']))
					self::text($v['value'], $v['span']);
				if($v['js'])
					self::js(join(";",$v['js']));
				$reloaded[$k] = true;
			}

		if(DEBUG) {
			$debug .= 'vars '.CID.': '.print_r($_SESSION['client']['__module_vars__'],true).'<br>';
			$debug .= 'user='.Acl::get_user().'<br>';
			if(isset($_REQUEST['__action_module__']))
				$debug .= 'action module='.$_REQUEST['__action_module__'].'<br>';
			$debug .= self::debug();
		}

		if(MODULE_TIMES) {
			foreach (self::$content as $k => $v)
				$debug .= 'Time of loading module <b>'.$k.'</b>: <i>'.$v['time'].'</i><br>';
			$debug .= 'Page renderered in '.(microtime(true)-$time).'s<hr>';
		}

		if(SQL_TIMES) {
			$debug .= '<font size="+1">QUERIES</font><br>';
			$queries = DB::GetQueries();
			foreach($queries as $q) {
				$style='color:red;font-weight:bold';
				if ($q['time']<0.5) $style = 'color:yellow;font-weight:bold';
				if ($q['time']<0.05) $style = 'color:green';
				$debug .= '<span style="'.$style.';">'.'<b>'.$q['func'].'</b> '.htmlspecialchars(var_export($q['args'],true)).' <i>'.$q['time'].'</i><br>'.'</span>';
			}
		}
		if(DEBUG || MODULE_TIMES || SQL_TIMES)
			self::text($debug,'debug');

		if(!$history_call && !History::soft_call()) {
		        History::set();
		}

		if(!$history_call) {
			self::js('history_add('.History::get_id().')');
		}

		self::send_output();
	}
}
?>

<?php
/**
 * SearchInstall class.
 * 
 * This class provides initialization data for Search module.
 * 
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @licence SPL
 * @package epesi-base-extra
 * @subpackage search
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Base_SearchInstall extends ModuleInstall {
	public function install() {
		Base_ThemeCommon::install_default_theme('Base/Search');
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme('Base/Search');
		return true;
	}
	
	public function version() {
		return array('0.9.1');
	}
	public function requires($v) {
		return array(
			array('name'=>'Libs/QuickForm','version'=>0), 
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Base/Box','version'=>0));
	}
}

?>

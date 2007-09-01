<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @version 1.0
 * @licence SPL
 * @package epesi-libs
 * @subpackage QuickForm
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Libs_QuickFormInstall extends ModuleInstall {
	public function install() {
		Base_ThemeCommon::install_default_theme('Libs/QuickForm');
		return true;
	}

	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme('Libs/QuickForm');
		return true;
	}

	public function version() {
		return array('3.2.7');
	}
	public function requires($v) {
		return array(array('name'=>'Base/Theme','version'=>0));
	}
}

?>

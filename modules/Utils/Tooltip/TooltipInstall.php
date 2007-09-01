<?php
/** 
 * @author Kuba Slawinski <kslawinski@telaxus.com> and Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC 
 * @version 0.9
 * @licence SPL 
 * @package epesi-utils 
 * @subpackage tooltip
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_TooltipInstall extends ModuleInstall {
	public function install() {
		Base_ThemeCommon::install_default_theme('Utils/Tooltip');
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme('Utils/Tooltip');
		return true;
	}
	
	public function version() {
		return array('1.0.0');
	}
	public function requires($v) {
		return array(
			array('name'=>'Base/Theme', 'version'=>0)
		    );
	}
}

?>

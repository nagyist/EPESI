<?php
/**
 * Simple mail client
 * @author pbukowski@telaxus.com
 * @copyright pbukowski@telaxus.com
 * @license SPL
 * @version 0.1
 * @package apps-mail
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Apps_MailInstall extends ModuleInstall {

	public function install() {
		$ret = true;
		$ret &= DB::CreateTable('apps_mail_accounts','
			id I4 AUTO KEY,
			user_login_id I4 NOTNULL,
			login C(127) NOTNULL,
			mail C(255) NOTNULL,
			password C(127) NOTNULL,
			smtp_server C(255),
			incoming_server C(255) NOTNULL,
			incoming_protocol I1 NOTNULL,
			smtp_auth I1 NOTNULL DEFAULT 1,
			smtp_ssl I1 NOTNULL DEFAULT 0,
			incoming_ssl I1 NOTNULL DEFAULT 0',
			array('constraints'=>', FOREIGN KEY (user_login_id) REFERENCES user_login(ID)'));
		if(!$ret){
			print('Unable to create table apps_mail_accounts.<br>');
			return false;
		}
		return $ret;
	}
	
	public function uninstall() {
		$ret = true;
		$ret &= DB::DropTable('apps_mail_accounts');
		return $ret;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/ActionBar','version'=>0),
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Base/User','version'=>0),
			array('name'=>'Base/User/Settings','version'=>0),
			array('name'=>'Libs/QuickForm','version'=>0),
			array('name'=>'Utils/FileUpload','version'=>0),
			array('name'=>'Utils/GenericBrowser','version'=>0),
			array('name'=>'Utils/Tooltip','version'=>0),
			array('name'=>'Utils/Wizard','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Simple mail client',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return true;
	}
	
}

?>
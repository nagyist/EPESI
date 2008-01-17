<?php
/**
 * Popup message to the user
 * @author pbukowski@telaxus.com
 * @copyright pbukowski@telaxus.com
 * @license SPL
 * @version 0.1
 * @package utils-messenger
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_MessengerInstall extends ModuleInstall {

	public function install() {
		$ret = true;
		$ret &= DB::CreateTable('utils_messenger_message','
			id I4 AUTO KEY,
			topic C(128) NOTNULL,
			message X,
			created_by I4 NOTNULL,
			created_on T NOTNULL,
			alert_on T,
			page_id C(32) NOTNULL',
			array('constraints'=>', FOREIGN KEY (created_by) REFERENCES user_login(ID)'));
		if(!$ret){
			print('Unable to create table utils_messenger_message.<br>');
			return false;
		}
		$ret &= DB::CreateTable('utils_messenger_users','
			message_id I4,
			user_login_id I4',
			array('constraints'=>', FOREIGN KEY (created_by) REFERENCES user_login(ID), FOREIGN KEY (message_id) REFERENCES utils_messenger_message(id), FOREIGN KEY (user_login_id) REFERENCES user_login(ID)'));
		if(!$ret){
			print('Unable to create table utils_messenger_users.<br>');
			return false;
		}
		return $ret;
	}
	
	public function uninstall() {
		$ret = true;
		$ret &= DB::DropTable('utils_messenger_users');
		$ret &= DB::DropTable('utils_messenger_message');
		return $ret;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Utils/GenericBrowser','version'=>0),
			array('name'=>'Utils/PopupCalendar','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Popup message to the user',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'SPL');
	}
	
	public static function simple_setup() {
		return false;
	}
	
}

?>
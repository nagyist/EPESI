<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @version 1.0
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @license SPL
 * @package epesi-base
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

require_once('database.php');

class DBSession {
    private static $lifetime;
    private static $name;

    public static function open($path, $name) {
        self::$lifetime = ini_get("session.gc_maxlifetime");
        return true;
    }

    public static function close() {
        self::gc(self::$lifetime);
        return true;
    }
    
    public static function read($name) {
    	$data = DB::GetOne('SELECT data FROM session WHERE name = %s AND expires > %d', array($name, time()-self::$lifetime));
//	file_put_contents('/tmp/sess_l',$data);
        return $data;
    }

    public static function write($name, $data) {
//	file_put_contents('/tmp/sess',$data);
	$ret = DB::Replace('session',array('expires'=>time(),'data'=>$data,'name'=>$name),'name',true);
        return ($ret>0)?true:false;
    }
    
    public static function write_client() {
	DB::Replace('session_client',array('data'=>serialize($_SESSION['client']),'session_name'=>session_id(),'client_id'=>CID),array('session_name','client_id'),true);
	unset($_SESSION['client']);
    }

    public static function destroy($name) {
    	DB::StartTrans();
    	DB::Execute('DELETE FROM history WHERE session_name=%s',array($name));
    	DB::Execute('DELETE FROM session_client WHERE session_name=%s',array($name));
    	DB::Execute('DELETE FROM session WHERE name=%s',array($name));
    	DB::CompleteTrans();
    	return true;
    }

    public static function gc($lifetime) {
    	$t = time()-$lifetime;
	DB::Execute('DELETE FROM history WHERE session_name IN (SELECT name FROM session WHERE expires < %d)',array($t));
   	DB::Execute('DELETE FROM session WHERE expires < %d',array($t));
        return true;
    }
}

session_set_save_handler(array('DBSession','open'),
                             array('DBSession','close'),
                             array('DBSession','read'),
                             array('DBSession','write'),
                             array('DBSession','destroy'),
                             array('DBSession','gc'));

$subdir = substr(getcwd(),strlen(dirname(dirname(__FILE__))));
$fulldir = dirname($_SERVER['SCRIPT_NAME']);
$document_root = substr($fulldir,0,strlen($fulldir)-strlen($subdir));
$document_root = trim($document_root,'/');
if($document_root) $document_root = '/'.$document_root.'/';
	else $document_root = '/';

if(!defined('CID')) {
	if(isset($_SERVER['HTTP_X_CLIENT_ID']))
		define('CID', $_SERVER['HTTP_X_CLIENT_ID']);
	else
		trigger_error('Invalid request without client id');
}

session_set_cookie_params(0,$document_root);
session_start();
if(CID!==false) {
	$_SESSION['client'] = unserialize(DB::GetOne('SELECT data FROM session_client WHERE session_name = %s AND client_id=%d', array(session_id(),CID)));
	register_shutdown_function(array('DBSession','write_client'));
}
?>

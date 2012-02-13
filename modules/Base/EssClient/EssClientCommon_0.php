<?php

/**
 * @author Adam Bukowski <abukowski@telaxus.com>
 * @copyright Telaxus LLC
 * @license MIT
 * @version 20111207
 * @package epesi-Base
 * @subpackage EssClient
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Base_EssClientCommon extends Base_AdminModuleCommon {
    const VAR_LICENSE_KEY = 'license_key';

    public static function menu() {
        if (!Base_AclCommon::i_am_sa())
            return;
        $text = 'Epesi registration';
        if (!self::get_license_key()) {
            $text = 'Register Epesi!';
        }
        return array('Help' => array('__submenu__' => 1, $text => array()));
    }
    
    public static function get_server_url() {
        return 'https://ess.epesibim.com/';
    }
    
    public static function get_payments_url() {
        return 'https://ess.epesibim.com/payments/';
    }

    /**
     * Get first(by id) user that is super administrator and get it's first
     * and last name from crm_contacts
     *
     * This function uses DB query to get users and generally it should be
     * easier way to get super admin.
     *
     * @return array with keys admin_email, admin_first_name, admin_last_name
     */
    public static function get_possible_admin() {
        $users = DB::GetAll('select id, mail from user_login inner join user_password on user_login_id = id');
        foreach ($users as $u) {
            if (Base_AclCommon::is_user_in_group(
                            Base_AclCommon::get_acl_user_id($u['id']), 'Super administrator')) {
                $x = array('admin_email' => $u['mail']);
                $contact = CRM_ContactsCommon::get_contact_by_user_id($u['id']);
                if ($contact) {
                    $x['admin_first_name'] = $contact['first_name'];
                    $x['admin_last_name'] = $contact['last_name'];
                }
                return $x;
            }
        }
        return null;
    }

    public static function get_license_key() {
        $ret = Variable::get(self::VAR_LICENSE_KEY, false);
        if(is_array($ret)) {
            $serv = self::get_server_url();
            $ret = array_key_exists($serv, $ret) ? $ret[$serv] : '';
        }
        return $ret;
    }

    public static function set_license_key($license_key) {
        return Variable::set(self::VAR_LICENSE_KEY, $license_key);
    }

    /** @var IClient */
    protected static $client_requester = null;

    /**
     * Get server connection object to perform requests
     * @param boolean $recreate_object force to recreate object
     * @return IClient server requester
     */
    public static function server($recreate_object = false) {
        if (self::$client_requester == null || $recreate_object == true) {
            // include php file
            $dir = self::Instance()->get_module_dir();
            require_once $dir . 'ClientRequester.php';
            // create object
            self::$client_requester = new ClientRequester(self::get_server_url());
            self::$client_requester->set_client_license_key(self::get_license_key());
        }
        return self::$client_requester;
    }

    public static function admin_access() {
        return Base_AclCommon::i_am_sa();
    }

    public static function admin_caption() {
        return "Epesi Registration";
    }

    public static function get_support_email() {
        $email = 'bugs@telaxus.com'; // FIXME
        if (ModuleManager::is_installed('CRM_Roundcube') >= 0) {
            $email = CRM_RoundcubeCommon::get_mailto_link($email);
        } else {
            $email = '<a href="mailto:' . $email . '">' . $email . '</a>';
        }
        return $email;
    }

    /**
     * Add client messages
     * @param array $messages Array of arrays in order info, warning, error
     */
    public static function add_client_messages($messages) {
        $msgs = Module::static_get_module_variable('Base/EssClient', 'messages', array(array(), array(), array()));
        foreach ($msgs as $k => &$v) {
            $v = array_merge($v, $messages[$k]);
            $v = array_unique($v);
        }
        Module::static_set_module_variable('Base/EssClient', 'messages', $msgs);
    }
    
    public static function client_messages_frame($only_frame = true) {
        return '<div id="ess_messages_frame">' . ($only_frame ? '' : self::format_client_messages()) . '</div>';
    }

    public static function client_messages_load_by_js() {
        eval_js('$("ess_messages_frame").innerHTML = ' . json_encode(self::format_client_messages()));
    }
    
    public static function format_client_messages($cleanup = true) {
        $msgs = Module::static_get_module_variable('Base/EssClient', 'messages', array(array(), array(), array()));
        $ret = '';
        // error msgs
        if (count($msgs[2])) {
            $ret .= '<div class="important_notice" style="background-color:#FFCCCC">';
            $ret .= Base_LangCommon::ts('Base/EssClient', 'Error messages from server:');
            foreach ($msgs[2] as $m)
                $ret .= '<div class="important_notice_frame">' . $m . '</div>';
            $ret .= '</div>';
        }
        // warn msgs
        if (count($msgs[1])) {
            $ret .= '<div class="important_notice" style="background-color:#FFDD99">';
            $ret .= Base_LangCommon::ts('Base/EssClient', 'Warning messages from server:');
            foreach ($msgs[1] as $m)
                $ret .= '<div class="important_notice_frame">' . $m . '</div>';
            $ret .= '</div>';
        }
        // info msgs
        if (count($msgs[0])) {
            $ret .= '<div class="important_notice" style="background-color:#DDFF99">';
            $ret .= Base_LangCommon::ts('Base/EssClient', 'Information messages from server:');
            foreach ($msgs[0] as $m)
                $ret .= '<div class="important_notice_frame">' . $m . '</div>';
            $ret .= '</div>';
        }

        if ($cleanup) {
            Module::static_unset_module_variable('Base/EssClient', 'messages');
        }
        return $ret;
    }

}

?>
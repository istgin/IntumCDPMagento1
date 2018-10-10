<?php
/**
 * Created by Intrum.
 * User: i.sutugins
 * Date: 14.2.9
 * Time: 10:28
 */
class ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumLogger
{
    private static $instance = NULL;
    private $logs;

    private function __construct() {
        $this->logs = array();
    }

    public static function getInstance() {
        if(self::$instance === NULL) {
            self::$instance = new ZZZZIntrum_Cdp_Helper_Api_Classes_IntrumLogger();
        }
        return self::$instance;
    }

    public function log($array) {
        $sql = '
                INSERT INTO `'._DB_PREFIX_.'intrum_logs` (
                  `firstname`,
                  `lastname`,
                  `town`,
                  `postcode`,
                  `street`,
                  `country`,
                  `ip`,
                  `status`,
                  `request_id`,
                  `type`,
                  `error`,
                  `response`,
                  `request`
                )
                VALUES
                (
                    \''.pSQL($array['firstname']).'\',
                    \''.pSQL($array['lastname']).'\',
                    \''.pSQL($array['town']).'\',
                    \''.pSQL($array['postcode']).'\',
                    \''.pSQL($array['street']).'\',
                    \''.pSQL($array['country']).'\',
                    \''.pSQL($array['ip']).'\',
                    \''.pSQL($array['status']).'\',
                    \''.pSQL($array['request_id']).'\',
                    \''.pSQL($array['type']).'\',
                    \''.pSQL($array['error'], true).'\',
                    \''.pSQL($array['response'], true).'\',
                    \''.pSQL($array['request'], true).'\'
                )
        ';
        Db::getInstance()->Execute($sql);
    }
};
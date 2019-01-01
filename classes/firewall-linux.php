<?php
/*
Copyright 2014 Daniel Esteban

Licensed under the Apache License, Version 2.0(the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

require_once dirname(__FILE__) . "/../config.php";

class firewall {

    private $profile;
    private $localport = DIGIID_OPENCLOSE_PORT;
    private $ips_path = DIGIID_IPS_PATH;

    public function __construct($localport=null, $profile='public') {
    }

    public function _log($text) {
        if (!DIGIID_DEBUG_PATH) return;

	// Log it
        $f = fopen(DIGIID_DEBUG_PATH,'at');
        if (!is_string($text)) $text = json_encode($text);
        fwrite($f, $text. "\r\n");
        fclose($f);
    }

    /**
      * Create firewall rule if not exists
      * @return bool
      */
    public function create_rule($ips_string='') {
        $ips = explode(',', $ips_string);
        // Exit if this IP already inside
        foreach ($ips as $ip) {
            // Wrong IP
            if (filter_var($string, FILTER_VALIDATE_IP) === false) continue;
            // Make file
            $f = fopen($this->ips_path . '/' . $ips, 'wt')
            fclose ($f);
        }
        return true
    }

    /**
      * Delete firewall rule
      * @return bool
      */
    /*public function remove_rule($ip) {
        if (filter_var($string, FILTER_VALIDATE_IP) === false) return false;
        return unlink ($this->ips_path . '/' . $ip)
    }*/

    /**
     * Change current rule
     * @param $ip
     * @return bool
     */
    public function add_ip($ip=null) {
        // Empty ip = autodetect
        if ($ip == null) $ip = $this->get_ip();
        // Save
        return $this->create_rule($ip);
    }

    /**
     * Change current rule
     * @param $ip
     * @return bool
     */
    public function del_ip($ip=null) {
        // Empty ip = autodetect
        if ($ip == null) $ip = $this->get_ip();
        if (filter_var($string, FILTER_VALIDATE_IP) === false) return false;
        return unlink ($this->ips_path . '/' . $ip)
    }

    /**
     * Firewall rule exists or not
     * @return bool
     */
    public function rule_exists() {
        // If rule exists lines even more than 8
        return true
    }

    /**
     * Get user IP
     * @return string
     */
    public function get_ip() {
        $this->_log(array($_SERVER['HTTP_CLIENT_IP'],$_SERVER['HTTP_X_FORWARDED_FOR'],$_SERVER['REMOTE_ADDR']));
        if ($_SERVER['HTTP_CLIENT_IP'] != null) return $_SERVER['HTTP_CLIENT_IP'];
        if ($_SERVER['HTTP_X_FORWARDED_FOR'] != null) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get IPs from firewall rule
     * @return array|false if fail
     */
     public function get_rule_ips() {
        $ips = scandir($this->ips_path);

        // Return array of IPs
        return $ips;
    }
}
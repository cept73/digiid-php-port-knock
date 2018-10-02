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

    private $rule_name = 'RDP DIGIID';
    private $system32path = '%SYSTEMROOT%\system32\\';
    private $profile;
    private $localport = 80;

    public function __construct($localport, $profile='public') {
        // Port to open/close
        $this->localport = $localport;
        // public|private|domain|any|,,
        $this->profile = $profile;
        // If rule don't exists, create it
        if(!$this->rule_exists()) $this->create_rule();
    }

    public function _log($text) {
        //return; // Turned off
        $f = fopen('F:\site\domains\query.log','at');
        if (!is_string($text)) $text = json_encode($text);
        fwrite($f, $text. "\r\n");
        fclose($f);
    }

    public function build_query($act, $params) {
        // Build command to netsh.exe
        $result = $this->system32path . "netsh advfirewall firewall $act rule ";
        foreach ($params as $key=>$val) {
            // Some text with spaces -> "Some text with spaces"
            if (strpos($val, ' ')) $val = '"'.$val.'"';
            if ($key == 'new remoteip' && empty($val)) $val = 'any';
            // Fill the line
            $result .= "$key=$val ";
        }
        $this->_log($result . "\n");
        // Return without spaces at the end
        return rtrim($result);
    }

    /**
      * Create firewall rule if not exists
      * @return bool
      */
    public function create_rule($ips='') {
        return shell_exec($this->build_query(
            'add', 
            array(
                'name'=>$this->rule_name, 
                'dir'=>'in', 
                'action'=>(empty($ips)) ? 'block' : 'allow', 
                'protocol'=>'TCP', 
                'profile'=>$this->profile,
                'localport'=>$this->localport, 
                'remoteip'=>$ips
            )
        ));
    }

    /**
      * Delete firewall rule
      * @return bool
      */
    public function remove_rule() {
        return shell_exec($this->build_query(
            'delete', 
            array(
                'name'=>$this->rule_name, 
                'dir'=>'in', 
                'protocol'=>'TCP', 
                'localport'=>$this->localport 
            )
        ));
    }

    /**
     * Change current rule
     * @param $ip
     * @return bool
     */
    public function add_ip($ip=null) {
        // Empty ip = autodetect
        if ($ip == null) $ip = $this->get_ip();
        // Get current rule
        $ips = $this->get_rule_ips();
        // Exit if this IP already inside
        foreach ($ips as $k=>$v) {
            if ($ip == $v) return false;
            // empty element
            if ($v == '127.0.0.1') unset($ips[$k]);
        }
        // Add IP there
        $ips[] = $ip;
        $this->_log('ADD:');
        $this->_log($ips);
        // Unpack array to string again
        $ips_string = implode($ips, ','); 
        // Save
        return shell_exec($this->build_query(
            'set', 
            array(
                'name'=>$this->rule_name,
                'new remoteip'=>$ips_string,
                'action'=>'allow'
            )
        ));
    }

    /**
     * Change current rule
     * @param $ip
     * @return bool
     */
    public function del_ip($ip=null) {
        // Empty ip = autodetect
        if ($ip == null) $ip = $this->get_ip();
        // Get current rule and remove this IP
        $ips = $this->get_rule_ips();
        // Remove current IP from rule
        foreach ($ips as $k=>$v) if ($ip == $v) unset($ips[$k]);
        // Pack again
        $ips_string = implode($ips, ',');
        // No deny rule, only allow list, at least empty elem
        if (empty($ips_string)) $ips_string = '127.0.0.1';
        // What to do
        $action = !empty($ips_string) ? 'allow' : 'block';

        // Save
        return shell_exec($this->build_query(
            'set', 
            array(
                'name'=>$this->rule_name,
                'new remoteip'=>$ips_string,
                'action'=>$action
            )
        ));
    }

    /**
     * Firewall rule exists or not
     * @return bool
     */
    public function rule_exists() {
        // If rule exists lines even more than 8
        return (count($this->get_rule_info()) >= 8);
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
     * Get rule info from firewall
     *
     * @return array|false if fail
     */
    public function get_rule_info() {
        // Get info about rule
        $exec = shell_exec($this->build_query(
            'show', 
            array(
                'name'=>$this->rule_name
            )
        ));
        // Make each line - one item of array
        return explode("\n", $exec);
    }

    /**
     * Get IPs from firewall rule
     * @return array|false if fail
     */
     public function get_rule_ips() {
        $info = $this->get_rule_info();
        $this->_log ('GET_RULE_IPS 1');
        $this->_log ($info);
       // Small amount means - rule don't exists
        if (count($info) < 8) return false;
        // Get line with IPs
        $line_with_ips = $info[8];
        // Value inside the string
        $pos = strrpos($line_with_ips, ' ') + 1;
        // Make array from right part
        $ips = explode(',', substr($line_with_ips, $pos));
        $this->_log ('GET_RULE_IPS 2');
        $this->_log ($ips);

        // Check
        foreach ($ips as $k=>$ip) {
          if (!$ip) {
            unset($ips[$k]);
            continue;
          }
          $this->_log ('GET_RULE_IPS 3');
          $this->_log ($ip);
          $slash = strrpos($ip, '/'); if ($slash !== false) $ip = substr($ip, 0, $slash);
          $slash = strrpos($ip, '\\'); if ($slash !== false) $ip = substr($ip, 0, $slash);
          $this->_log ($ip);
          $ips[$k] = $ip;
          if (!filter_var($ip, FILTER_VALIDATE_IP)) unset($ips[$k]);
          else
            $this->_log ('validated');
        }

        $this->_log ('GET_RULE_IPS 4');
        $this->_log ($ips);
        $this->_log (implode($ips, ','));
        $this->_log ('END');

        // Return array of IPs
        return $ips;
    }
}
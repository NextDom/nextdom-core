<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

use NextDom\Exceptions\CoreException;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\PluginManager;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Update;

/**
 * Class NetworkHelper
 *
 * TODO: Dépendance avec le plugin OpenVPN
 *
 * @package NextDom\Helpers
 */
class NetworkHelper
{
    /**
     * @return string
     * @throws \Exception
     */
    public static function getUserLocation()
    {
        $client_ip = self::getClientIp();
        $nextdom_ip = self::getNetworkAccess('internal', 'ip', '', false);
        if (!filter_var($nextdom_ip, FILTER_VALIDATE_IP)) {
            return 'external';
        }
        $nextdom_ips = explode('.', $nextdom_ip);
        if (count($nextdom_ips) != 4) {
            return 'external';
        }
        if (ConfigManager::byKey('network::localip') != '') {
            $localIps = explode(';', ConfigManager::byKey('network::localip'));
            foreach ($localIps as $localIp) {
                if (self::netMatch($localIp, $client_ip)) {
                    return 'internal';
                }
            }
        }
        $match = $nextdom_ips[0] . '.' . $nextdom_ips[1] . '.' . $nextdom_ips[2] . '.*';
        return self::netMatch($match, $client_ip) ? 'internal' : 'external';
    }

    /**
     * @return string
     */
    public static function getClientIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }

    /**
     * @param string $_mode
     * @param string $_protocol
     * @param string $_default
     * @param bool $_test
     * @return mixed|string
     * @throws \Exception
     */
    public static function getNetworkAccess($_mode = 'auto', $_protocol = '', $_default = '', $_test = false)
    {
        if ($_mode == 'auto') {
            $_mode = self::getUserLocation();
        }
        if ($_mode == 'internal' && ConfigManager::byKey('internalAddr', 'core', '') == '') {
            self::checkConf($_mode);
        }
        if ($_mode == 'external' && ConfigManager::byKey('market::allowDNS') != 1 && ConfigManager::byKey('externalAddr', 'core', '') == '') {
            self::checkConf($_mode);
        }
        if ($_test && !self::test($_mode)) {
            self::checkConf($_mode);
        }
        if ($_mode == 'internal') {
            if (strpos(ConfigManager::byKey('internalAddr', 'core', $_default), 'http://') !== false || strpos(ConfigManager::byKey('internalAddr', 'core', $_default), 'https://') !== false) {
                ConfigManager::save('internalAddr', str_replace(array('http://', 'https://'), '', ConfigManager::byKey('internalAddr', 'core', $_default)));
            }
            if ($_protocol == 'ip' || $_protocol == 'dns') {
                return ConfigManager::byKey('internalAddr', 'core', $_default);
            }
            if ($_protocol == 'ip:port' || $_protocol == 'dns:port') {
                return ConfigManager::byKey('internalAddr') . ':' . ConfigManager::byKey('internalPort', 'core', 80);
            }
            if ($_protocol == 'proto:ip' || $_protocol == 'proto:dns') {
                return ConfigManager::byKey('internalProtocol') . ConfigManager::byKey('internalAddr');
            }
            if ($_protocol == 'proto:ip:port' || $_protocol == 'proto:dns:port') {
                return ConfigManager::byKey('internalProtocol') . ConfigManager::byKey('internalAddr') . ':' . ConfigManager::byKey('internalPort', 'core', 80);
            }
            if ($_protocol == 'proto:127.0.0.1:port:comp') {
                return trim(ConfigManager::byKey('internalProtocol') . '127.0.0.1:' . ConfigManager::byKey('internalPort', 'core', 80) . '/' . trim(ConfigManager::byKey('internalComplement'), '/'), '/');
            }
            if ($_protocol == 'http:127.0.0.1:port:comp') {
                return trim('http://127.0.0.1:' . ConfigManager::byKey('internalPort', 'core', 80) . '/' . trim(ConfigManager::byKey('internalComplement'), '/'), '/');
            }
            return trim(ConfigManager::byKey('internalProtocol') . ConfigManager::byKey('internalAddr') . ':' . ConfigManager::byKey('internalPort', 'core', 80) . '/' . trim(ConfigManager::byKey('internalComplement'), '/'), '/');

        }
        if ($_mode == 'dnsnextdom') {
            return ConfigManager::byKey('nextdom::url');
        }
        if ($_mode == 'external') {
            if ($_protocol == 'ip') {
                if (ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                    return self::getIpFromString(ConfigManager::byKey('nextdom::url'));
                }
                return self::getIpFromString(ConfigManager::byKey('externalAddr'));
            }
            if ($_protocol == 'ip:port') {
                if (ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                    $url = parse_url(ConfigManager::byKey('nextdom::url'));
                    if (isset($url['host'])) {
                        if (isset($url['port'])) {
                            return self::getIpFromString($url['host']) . ':' . $url['port'];
                        } else {
                            return self::getIpFromString($url['host']);
                        }
                    }
                }
                return ConfigManager::byKey('externalAddr') . ':' . ConfigManager::byKey('externalPort', 'core', 80);
            }
            if ($_protocol == 'proto:dns:port' || $_protocol == 'proto:ip:port') {
                if (ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                    $url = parse_url(ConfigManager::byKey('nextdom::url'));
                    $return = '';
                    if (isset($url['scheme'])) {
                        $return = $url['scheme'] . '://';
                    }
                    if (isset($url['host'])) {
                        if (isset($url['port'])) {
                            return $return . $url['host'] . ':' . $url['port'];
                        } else {
                            return $return . $url['host'];
                        }
                    }
                }
                return ConfigManager::byKey('externalProtocol') . ConfigManager::byKey('externalAddr') . ':' . ConfigManager::byKey('externalPort', 'core', 80);
            }
            if ($_protocol == 'proto:dns' || $_protocol == 'proto:ip') {
                if (ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                    $url = parse_url(ConfigManager::byKey('nextdom::url'));
                    $return = '';
                    if (isset($url['scheme'])) {
                        $return = $url['scheme'] . '://';
                    }
                    if (isset($url['host'])) {
                        if (isset($url['port'])) {
                            return $return . $url['host'];
                        } else {
                            return $return . $url['host'];
                        }
                    }
                }
                return ConfigManager::byKey('externalProtocol') . ConfigManager::byKey('externalAddr');
            }
            if ($_protocol == 'dns:port') {
                if (ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                    $url = parse_url(ConfigManager::byKey('nextdom::url'));
                    if (isset($url['host'])) {
                        if (isset($url['port'])) {
                            return $url['host'] . ':' . $url['port'];
                        } else {
                            return $url['host'];
                        }
                    }
                }
                return ConfigManager::byKey('externalAddr') . ':' . ConfigManager::byKey('externalPort', 'core', 80);
            }
            if ($_protocol == 'proto') {
                if (ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                    $url = parse_url(ConfigManager::byKey('nextdom::url'));
                    if (isset($url['scheme'])) {
                        return $url['scheme'] . '://';
                    }
                }
                return ConfigManager::byKey('externalProtocol');
            }
            if (ConfigManager::byKey('dns::token') != '' && ConfigManager::byKey('market::allowDNS') == 1 && ConfigManager::byKey('nextdom::url') != '' && ConfigManager::byKey('network::disableMangement') == 0) {
                return trim(ConfigManager::byKey('nextdom::url') . '/' . trim(ConfigManager::byKey('externalComplement', 'core', ''), '/'), '/');
            }
            return trim(ConfigManager::byKey('externalProtocol') . ConfigManager::byKey('externalAddr') . ':' . ConfigManager::byKey('externalPort', 'core', 80) . '/' . trim(ConfigManager::byKey('externalComplement'), '/'), '/');
        }
        return '';
    }

    /**
     * @param string $_mode
     * @throws \Exception
     */
    public static function checkConf($_mode = 'external')
    {
        if (ConfigManager::byKey($_mode . 'Protocol') == '') {
            ConfigManager::save($_mode . 'Protocol', 'http://');
        }
        if (ConfigManager::byKey($_mode . 'Port') == '') {
            ConfigManager::save($_mode . 'Port', 80);
        }
        if (ConfigManager::byKey($_mode . 'Protocol') == 'https://' && ConfigManager::byKey($_mode . 'Port') == 80) {
            ConfigManager::save($_mode . 'Port', 443);
        }
        if (ConfigManager::byKey($_mode . 'Protocol') == 'http://' && ConfigManager::byKey($_mode . 'Port') == 443) {
            ConfigManager::save($_mode . 'Port', 80);
        }
        if (trim(ConfigManager::byKey($_mode . 'Complement')) == '/') {
            ConfigManager::save($_mode . 'Complement', '');
        }
        if ($_mode == 'internal') {
            foreach (self::getInterfacesList() as $interface) {
                if ($interface == 'lo') {
                    continue;
                }
                $ip = self::getInterfaceIp($interface);
                if (!self::netMatch('127.0.*.*', $ip) && $ip != '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    ConfigManager::save('internalAddr', $ip);
                    break;
                }
            }
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getInterfacesList()
    {
        $interfaces = explode("\n", shell_exec(SystemHelper::getCmdSudo() . "ip -o link show | awk -F': ' '{print $2}'"));
        $result = [];
        foreach ($interfaces as $interface) {
            if (trim($interface) == '') {
                continue;
            }
            $result[] = $interface;
        }
        return $result;
    }

    /*     * *********************DNS************************* */

    /**
     * @param $_interface
     * @return bool|string
     * @throws \Exception
     */
    public static function getInterfaceIp($_interface)
    {
        $ip = trim(shell_exec(SystemHelper::getCmdSudo() . "ip addr show " . $_interface . " | grep \"inet .*" . $_interface . "\" | awk '{print $2}' | cut -d '/' -f 1"));
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        return false;
    }

    /**
     * @param string $network
     * @param string $ip
     *
     * @return bool
     */
    public static function netMatch($network, $ip)
    {
        $ip = trim($ip);
        if ($ip === trim($network)) {
            return true;
        }
        $network = str_replace(' ', '', $network);
        if (strpos($network, '*') !== false) {
            if (strpos($network, '/') !== false) {
                $asParts = explode('/', $network);
                if ($asParts[0]) {
                    $network = $asParts[0];
                } else {
                    $network = null;
                }
            }
            $nCount = substr_count($network, '*');
            $network = str_replace('*', '0', $network);
            if ($nCount == 1) {
                $network .= '/24';
            } elseif ($nCount == 2) {
                $network .= '/16';
            } elseif ($nCount == 3) {
                $network .= '/8';
            } elseif ($nCount > 3) {
                return true; // if *.*.*.*, then all, so matched
            }
        }

        $d = strpos($network, '-');
        if ($d === false) {
            if (strpos($network, '/') === false) {
                if ($ip == $network) {
                    return true;
                }
                return false;
            }
            $ip_arr = explode('/', $network);
            if (!preg_match("@\d*\.\d*\.\d*\.\d*@", $ip_arr[0], $matches)) {
                $ip_arr[0] .= ".0"; // Alternate form 194.1.4/24
            }
            $network_long = ip2long($ip_arr[0]);
            $x = ip2long($ip_arr[1]);
            $mask = long2ip($x) == $ip_arr[1] ? $x : (0xffffffff << (32 - $ip_arr[1]));
            $ip_long = ip2long($ip);
            return ($ip_long & $mask) == ($network_long & $mask);
        } else {
            $from = trim(ip2long(substr($network, 0, $d)));
            $to = trim(ip2long(substr($network, $d + 1)));
            $ip = ip2long($ip);
            return ($ip >= $from && $ip <= $to);
        }
    }

    /**
     * @param string $_mode
     * @param int $_timeout
     * @return bool
     * @throws \Exception
     */
    public static function test($_mode = 'external', $_timeout = 5)
    {
        if (ConfigManager::byKey('network::disableMangement') == 1) {
            return true;
        }
        if ($_mode == 'internal' && self::netMatch('127.0.*.*', self::getNetworkAccess($_mode, 'ip', '', false))) {
            return false;
        }
        $url = trim(self::getNetworkAccess($_mode, '', '', false), '/') . '/public/here.html';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($_mode == 'external') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            LogHelper::add('network', 'debug', 'Erreur sur ' . $url . ' => ' . curl_errno($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if (trim($data) != 'ok') {
            LogHelper::add('network', 'debug', 'Retour NOK sur ' . $url . ' => ' . $data);
            return false;
        }
        return true;
    }

    /**
     * @param $_string
     * @return bool|mixed|string
     */
    public static function getIpFromString($_string)
    {
        $result = parse_url($_string);
        if (isset($result['host'])) {
            $_string = $result['host'];
        } else {
            $_string = str_replace(array('https://', 'http://'), '', $_string);
            if (strpos($_string, '/') !== false) {
                $_string = substr($_string, 0, strpos($_string, '/'));
            }
            if (strpos($_string, ':') !== false) {
                $_string = substr($_string, 0, strpos($_string, ':'));
            }
        }
        if (!filter_var($_string, FILTER_VALIDATE_IP)) {
            $_string = gethostbyname($_string);
        }
        return $_string;
    }

    public static function dnsStart()
    {
        if (ConfigManager::byKey('dns::token') == '') {
            return;
        }
        if (ConfigManager::byKey('market::allowDNS') != 1) {
            return;
        }
        $openvpn = self::dnsCreate();
        $cmd = $openvpn->getCmd('action', 'start');
        if (!is_object($cmd)) {
            throw new CoreException(__('La commande de démarrage du DNS est introuvable'));
        }
        $cmd->execCmd();
        $interface = $openvpn->getInterfaceName();
        if ($interface !== null && $interface != '' && $interface !== false) {
            shell_exec(SystemHelper::getCmdSudo() . 'iptables -A INPUT -i ' . $interface . ' -p tcp  --destination-port 80 -j ACCEPT');
            if (ConfigManager::byKey('dns::openport') != '') {
                foreach (explode(',', ConfigManager::byKey('dns::openport')) as $port) {
                    if (is_nan($port)) {
                        continue;
                    }
                    try {
                        shell_exec(SystemHelper::getCmdSudo() . 'iptables -A INPUT -i ' . $interface . ' -p tcp  --destination-port ' . $port . ' -j ACCEPT');
                    } catch (\Exception $e) {

                    }
                }
            }
            shell_exec(SystemHelper::getCmdSudo() . 'iptables -A INPUT -i ' . $interface . ' -j DROP');
        }
    }

    /**
     * @return array|mixed|\openvpn
     * @throws CoreException
     * @throws \Throwable
     */
    public static function dnsCreate()
    {
        if (ConfigManager::byKey('dns::token') == '') {
            return null;
        }
        try {
            $plugin = PluginManager::byId('openvpn');
            if (!is_object($plugin)) {
                $update = UpdateManager::byLogicalId('openvpn');
                if (!is_object($update)) {
                    $update = new Update();
                }
                $update->setLogicalId('openvpn');
                $update->setSource('market');
                $update->setConfiguration('version', 'stable');
                $update->save();
                $update->doUpdate();
                $plugin = PluginManager::byId('openvpn');
            }
        } catch (\Exception $e) {
            $update = UpdateManager::byLogicalId('openvpn');
            if (!is_object($update)) {
                $update = new Update();
            }
            $update->setLogicalId('openvpn');
            $update->setSource('market');
            $update->setConfiguration('version', 'stable');
            $update->save();
            $update->doUpdate();
            $plugin = PluginManager::byId('openvpn');
        }
        if (!is_object($plugin)) {
            throw new CoreException(__('Le plugin OpenVPN doit être installé'));
        }
        if (!$plugin->isActive()) {
            $plugin->setIsEnable(1);
            $plugin->dependancy_install();
        }
        if (!$plugin->isActive()) {
            throw new CoreException(__('Le plugin OpenVPN doit être actif'));
        }
        $openvpn = EqLogicManager::byLogicalId('dnsnextdom', 'openvpn');
        if (!is_object($openvpn)) {
            /** @noinspection PhpUndefinedClassInspection */
            $openvpn = new \openvpn();
            $openvpn->setName('DNS NextDom');
        }
        $openvpn->setIsEnable(1);
        $openvpn->setLogicalId('dnsnextdom');
        $openvpn->setEqType_name('openvpn');
        $openvpn->setConfiguration('dev', 'tun');
        $openvpn->setConfiguration('proto', 'udp');
        if(ConfigManager::byKey('dns::vpnurl') != ''){
            $openvpn->setConfiguration('remote_host', ConfigManager::byKey('dns::vpnurl'));
        }else{
            $openvpn->setConfiguration('remote_host', 'vpn.dns' . ConfigManager::byKey('dns::number', 'core', 1) . '.jeedom.com');
        }
        $openvpn->setConfiguration('username', NextDomHelper::getHardwareKey());
        $openvpn->setConfiguration('password', ConfigManager::byKey('dns::token'));
        $openvpn->setConfiguration('compression', 'comp-lzo');
        $openvpn->setConfiguration('remote_port', ConfigManager::byKey('vpn::port', 'core', 1194));
        $openvpn->setConfiguration('auth_mode', 'password');
        $openvpn->save(true);
        if (!file_exists(NEXTDOM_ROOT . '/plugins/openvpn/data')) {
            shell_exec('mkdir -p ' . NEXTDOM_ROOT . '/plugins/openvpn/data');
        }
        $path_ca = NEXTDOM_ROOT . '/plugins/openvpn/data/ca_' . $openvpn->getConfiguration('key') . '.crt';
        if (file_exists($path_ca)) {
            unlink($path_ca);
        }
        copy(NEXTDOM_ROOT . '/script/ca_dns.crt', $path_ca);
        if (!file_exists($path_ca)) {
            throw new CoreException(__('Impossible de créer le fichier  : ') . $path_ca);
        }
        return $openvpn;
    }

    /**
     * @return bool
     * @throws CoreException
     */
    /**
     * @return bool
     * @throws CoreException
     * @throws \Throwable
     */
    /**
     * @return bool
     * @throws CoreException
     * @throws \Throwable
     */
    public static function dnsRun()
    {
        if (ConfigManager::byKey('dns::token') == '') {
            return false;
        }
        if (ConfigManager::byKey('market::allowDNS') != 1) {
            return false;
        }
        try {
            $openvpn = self::dnsCreate();
        } catch (\Exception $e) {
            return false;
        }
        $cmd = $openvpn->getCmd('info', 'state');
        if (!is_object($cmd)) {
            throw new CoreException(__('La commande de statut du DNS est introuvable'));
        }
        return $cmd->execCmd();
    }

    public static function dnsStop()
    {
        if (ConfigManager::byKey('dns::token') == '') {
            return;
        }
        $openvpn = self::dnsCreate();
        $cmd = $openvpn->getCmd('action', 'stop');
        if (!is_object($cmd)) {
            throw new CoreException(__('La commande d\'arrêt du DNS est introuvable'));
        }
        $cmd->execCmd();
    }

    /**
     * @param $_interface
     * @return bool|string
     * @throws \Exception
     */
    /**
     * @param $_interface
     * @return bool|string
     * @throws \Exception
     */
    /**
     * @param $_interface
     * @return bool|string
     * @throws \Exception
     */
    public static function getInterfaceMac($_interface)
    {
        $valid_mac = "([0-9A-F]{2}[:-]){5}([0-9A-F]{2})";
        $mac = trim(shell_exec(SystemHelper::getCmdSudo() . "ip addr show " . $_interface . " 2>&1 | grep ether | awk '{print $2}'"));
        if (preg_match("/" . $valid_mac . "/i", $mac)) {
            return $mac;
        }
        return false;
    }

    public static function cron5()
    {
        if (ConfigManager::byKey('network::disableMangement') == 1) {
            return;
        }
        if (!self::test('internal')) {
            self::checkConf('internal');
        }
        if (!self::test('external')) {
            self::checkConf('external');
        }
        if (!NextDomHelper::isCapable('sudo') || NextDomHelper::getHardwareName() == 'docker') {
            return;
        }
        exec(SystemHelper::getCmdSudo() . 'ping -n -c 1 -t 255 8.8.8.8 2>&1 > /dev/null', $output, $return_val);
        if ($return_val == 0) {
            return;
        }
        $gw = shell_exec("ip route show default | awk '/default/ {print $3}'");
        if ($gw == '') {
            LogHelper::addError('network', __('Souci réseau détecté, redémarrage du réseau. Aucune gateway de trouvée'));
            exec(SystemHelper::getCmdSudo() . 'service networking restart');
            return;
        }
        exec(SystemHelper::getCmdSudo() . 'ping -n -c 1 -t 255 ' . $gw . ' 2>&1 > /dev/null', $output, $return_val);
        if ($return_val == 0) {
            return;
        }
        exec(SystemHelper::getCmdSudo() . 'ping -n -c 1 -t 255 ' . $gw . ' 2>&1 > /dev/null', $output, $return_val);
        if ($return_val == 0) {
            return;
        }
        LogHelper::addError('network', __('Souci réseau détecté, redémarrage du réseau. La gateway ne répond pas au ping : ') . $gw);
        exec(SystemHelper::getCmdSudo() . 'service networking restart');
    }
}

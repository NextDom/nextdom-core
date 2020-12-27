<?php


namespace NextDom\Helpers;


use NextDom\Exceptions\CoreException;
use NextDom\Managers\UpdateManager;
use NextDom\Model\Entity\Update;
use NextDom\Repo\RepoMarket;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\EqLogicManager;
use NextDom\Managers\PluginManager;

class DnsHelper
{
    /**
     * @return openvpn|\NextDom\Model\Entity\EqLogic|\NextDom\Model\Entity\EqLogic[]|null
     * @throws CoreException
     * @throws \ReflectionException
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
                $plugin = self::updateOpenVpn();
            }
        } catch (CoreException $e) {
            $plugin = self::updateOpenVpn();
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
        $openvpn = EqLogicManager::byLogicalId('dnsjeedom', 'openvpn');
        $direct = true;
        if (!is_object($openvpn)) {
            $direct = false;
            $openvpn = new openvpn();
            $openvpn->setName('DNS Jeedom');
        }
        $openvpn->setIsEnable(1);
        $openvpn->setLogicalId('dnsjeedom');
        $openvpn->setEqType_name('openvpn');
        $openvpn->setConfiguration('dev', 'tun');
        $openvpn->setConfiguration('proto', 'udp');
        if (ConfigManager::byKey('dns::vpnurl') != '') {
            $openvpn->setConfiguration('remote_host', ConfigManager::byKey('dns::vpnurl'));
        } else {
            $openvpn->setConfiguration('remote_host', 'vpn.dns' . ConfigManager::byKey('dns::number', 'core', 1) . '.jeedom.com');
        }
        $openvpn->setConfiguration('username', NextDomHelper::getHardwareKey());
        $openvpn->setConfiguration('password', ConfigManager::byKey('dns::token'));
        $openvpn->setConfiguration('compression', 'comp-lzo');
        $openvpn->setConfiguration('remote_port', ConfigManager::byKey('vpn::port', 'core', 1194));
        $openvpn->setConfiguration('auth_mode', 'password');
        if (ConfigManager::byKey('connection::4g') == 1) {
            $openvpn->setConfiguration('optionsAfterStart', 'sudo ip link set dev #interface# mtu ' . ConfigManager::byKey('market::dns::mtu'));
        } else {
            $openvpn->setConfiguration('optionsAfterStart', '');
        }
        $openvpn->save($direct);
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
    }

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
        } catch (CoreException $e) {
            return false;
        }
        $cmd = $openvpn->getCmd('info', 'state');
        if (!is_object($cmd)) {
            throw new CoreException(__('La commande de statut du DNS est introuvable'));
        }
        try {
            self::dns2Start();
        } catch (CoreException $e) {

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
            throw new CoreException(__('La commande d\'arrêt du DNS est introuvable', __FILE__));
        }
        $cmd->execCmd();
    }

    public static function dns2Start()
    {
        if (ConfigManager::byKey('service::tunnel::enable') != 1) {
            return;
        }
        if (ConfigManager::byKey('market::allowDNS') != 1) {
            return;
        }
        self::dns2Stop();
        LogHelper::clear('tunnel');
        $exec = 'tunnel-linux-' . SystemHelper::getArch();
        $dir = NEXTDOM_ROOT . '/script/tunnel';
        if (!file_exists($dir . '/' . $exec)) {
            echo shell_exec('cd ' . $dir . ';wget https://images.jeedom.com/resources/tunnel/' . $exec . ' > ' . LogHelper::getPathToLog('tunnel') . ' 2>&1');
        }
        if (!file_exists($dir . '/' . $exec)) {
            throw new CoreException(__('Impossible de télécharger : ', __FILE__) . 'https://images.jeedom.com/resources/tunnel/' . $exec);
        }
        if (filesize($dir . '/' . $exec) < 7000000) {
            unlink($dir . '/' . $exec);
            throw new CoreException(__('Taille invalide pour : ', __FILE__) . $dir . '/' . $exec);
        }
        shell_exec('chmod +x ' . $dir . '/' . $exec);
        if (!file_exists($dir . '/client.crt') || !file_exists($dir . '/client.key')) {
            shell_exec('cd ' . $dir . ';openssl req -x509 -nodes -newkey rsa:2048 -sha256 -keyout client.key -out client.crt -subj "/C=EU/ST=FR/L=Paris/O=Jeedom, Inc./OU=IT/CN=jeedom.com"> ' . LogHelper::getPathToLog('tunnel') . ' 2>&1');
            if (!file_exists($dir . '/client.crt') || !file_exists($dir . '/client.key')) {
                throw new CoreException(__('Impossible de générer le certificat et la clef privé', __FILE__));
            }
        }
        $replace = array(
            '#URL#' => str_replace('https://', '', ConfigManager::byKey('service::tunnel::host')),
            '#PORT#' => 80,
            '#SERVER_ADDR#' => ConfigManager::byKey('service::tunnel::eu::backend::1')
        );
        for ($i = 1; $i < 3; $i++) {
            $infos = explode(':', ConfigManager::byKey('service::tunnel::eu::backend::' . $i));
            LogHelper::add('tunnel', 'debug', 'Test access to ' . $infos[0] . ' on port ' . $infos[1]);
            if (count($infos) < 2) {
                break;
            }
            if (NetworkHelper::checkOpenPort($infos[0], $infos[1])) {
                LogHelper::add('tunnel', 'debug', 'Access is open, used it');
                $replace['#SERVER_ADDR#'] = ConfigManager::byKey('service::tunnel::eu::backend::' . $i);
                break;
            }
            LogHelper::add('tunnel', 'debug', 'Access is close test next');
        }
        if (file_exists($dir . '/tunnel.yml')) {
            unlink($dir . '/tunnel.yml');
        }
        file_put_contents($dir . '/tunnel.yml', str_replace(array_keys($replace), $replace, file_get_contents($dir . '/tunnel.tmpl.yml')));
        $client_id = shell_exec('cd ' . $dir . ';./' . $exec . ' id');
        LogHelper::add('tunnel', 'debug', 'Client id is : ' . $client_id);
        try {
            RepoMarket::sendTunnelClientId(trim($client_id));
        } catch (CoreException $e) {
            LogHelper::add('tunnel', 'debug', 'Error on on send tunnel id to market : ' . $e->getMessage());
        }
        $replace['#URL#'] = str_replace('https://', '', ConfigManager::byKey('service::tunnel::host'));
        if (file_exists($dir . '/tunnel.yml')) {
            unlink($dir . '/tunnel.yml');
        }
        file_put_contents($dir . '/tunnel.yml', str_replace(array_keys($replace), $replace, file_get_contents($dir . '/tunnel.tmpl.yml')));
        shell_exec('cd ' . $dir . ';nohup ./' . $exec . ' start-all >> ' . LogHelper::getPathToLog('tunnel') . ' 2>&1 &');
    }

    public static function dns2Run()
    {
        if (ConfigManager::byKey('service::tunnel::enable') != 1) {
            return false;
        }
        if (ConfigManager::byKey('market::allowDNS') != 1) {
            return false;
        }
        $exec = 'tunnel-linux-' . SystemHelper::getArch();
        return (shell_exec('ps ax | grep ' . $exec . ' | grep  -c -v grep') > 0);
    }

    public static function dns2Stop()
    {
        if (ConfigManager::byKey('service::tunnel::enable') != 1) {
            return;
        }
        exec("(ps ax || ps w) | grep -ie 'tunnel-linux-" . SystemHelper::getArch() . "' | grep -v grep | awk '{print $1}' | xargs sudo kill -9 > /dev/null 2>&1");
        return;
    }

    /**
     * @return mixed|\NextDom\Model\Entity\Plugin
     * @throws CoreException
     * @throws \Throwable
     */
    private static function updateOpenVpn()
    {
        $update = UpdateManager::byLogicalId('openvpn');
        if (!is_object($update)) {
            $update = new Update();
        }
        $update->setLogicalId('openvpn')
               ->setSource('market')
               ->setConfiguration('version', 'stable');
        $update->save();
        $update->doUpdate();
        return PluginManager::byId('openvpn');
    }

}

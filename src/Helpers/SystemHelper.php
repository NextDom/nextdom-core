<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Helpers;

/**
 * Class SystemHelper
 * @package NextDom\Helpers
 */
class SystemHelper
{
    /**
     * @var string Linux distribution name
     */
    private static $distrib = null;
    /**
     * @var array Distribution specific commands
     */
    private static $commands = [
        'suse' => ['cmd_check' => ' rpm -qa | grep ', 'cmd_install' => ' zypper in --non-interactive ', 'www-uid' => 'wwwrun', 'www-gid' => 'www', 'type' => 'zypper'],
        'sles' => ['cmd_check' => ' rpm -qa | grep ', 'cmd_install' => ' zypper in --non-interactive ', 'www-uid' => 'wwwrun', 'www-gid' => 'www', 'type' => 'zypper'],
        'redhat' => ['cmd_check' => ' rpm -qa | grep ', 'cmd_install' => ' yum install ', 'www-uid' => 'www-data', 'www-gid' => 'www-data', 'type' => 'yum'],
        'fedora' => ['cmd_check' => ' rpm -qa | grep ', 'cmd_install' => ' dnf install ', 'www-uid' => 'www-data', 'www-gid' => 'www-data', 'type' => 'dnf'],
        'debian' => ['cmd_check' => ' dpkg --get-selections | grep -v deinstall | grep ', 'cmd_install' => ' apt-get install -y ', 'www-uid' => 'www-data', 'www-gid' => 'www-data', 'type' => 'apt'],
    ];

    /**
     * @return int
     */
    public static function vsystem()
    {
        $status = 0;
        $args = func_get_args();
        $format = $args[0];
        $params = array_slice($args, 1);
        $cmd = vsprintf($format, $params);

        system($cmd, $status);
        return $status;
    }

    /**
     * Get command key
     *
     * @param string $commandKey Command key
     *
     * @return mixed
     */
    public static function getCommand($commandKey = '')
    {
        $result = '';
        if (isset(self::$commands[self::getDistrib()]) && isset(self::$commands[self::getDistrib()][$commandKey])) {
            $result = self::$commands[self::getDistrib()][$commandKey];
        }
        if ($result == '') {
            if ($commandKey == 'www-uid') {
                $processUser = posix_getpwuid(posix_geteuid());
                $result = $processUser['name'];
            }
            if ($commandKey == 'www-gid') {
                $processGroup = posix_getgrgid(posix_getegid());
                $result = $processGroup['name'];
            }
        }
        return $result;
    }

    /**
     *  Get distribution name
     *
     * @return string Distribution name
     */
    public static function getDistrib(): string
    {
        self::loadCommand();
        if (isset(self::$commands['custom'])) {
            return 'custom';
        }
        if (self::$distrib === null) {
            self::$distrib = trim(shell_exec('grep CPE_NAME /etc/os-release | cut -d \'"\' -f 2 | cut -d : -f 3 '));
            if (self::$distrib == '') {
                self::$distrib = trim(shell_exec('grep -e "^ID" /etc/os-release | cut -d \'=\' -f 2'));
            }
            if (self::$distrib == '' || !isset(self::$commands[self::$distrib])) {
                self::$distrib = 'debian';
            }
        }
        return self::$distrib;
    }

    /**
     * Load system from system_cmd.json if file exists.
     *
     * @return array List of commands
     */
    public static function loadCommand(): array
    {
        if (file_exists(NEXTDOM_DATA . '/config/system_cmd.json')) {
            $content = file_get_contents(NEXTDOM_DATA . '/config/system_cmd.json');
            if (Utils::isJson($content)) {
                self::$commands['custom'] = json_decode($content, true);
            }
        }
        return self::$commands;
    }

    /**
     * Kill all process which using file
     *
     * @param string $filename
     * @throws \Exception
     */
    public static function killProcessesWhichUsingFile(string $filename)
    {
        exec(SystemHelper::getCmdSudo() . 'fuser -k ' . $filename . ' > /dev/null 2>&1');
    }

    /**
     * Get sudo command
     *
     * @return string
     * @throws \Exception
     */
    public static function getCmdSudo(): string
    {
        if (!NextDomHelper::isCapable('sudo')) {
            return '';
        }
        return 'sudo ';
    }

    /**
     * Kill all process which using port
     *
     * @param $port
     * @param string $protocol
     * @throws \Exception
     */
    public static function killProcessesWhichUsingPort($port, $protocol = 'tcp')
    {
        exec(SystemHelper::getCmdSudo() . 'fuser -k ' . $port . '/' . $protocol . ' > /dev/null 2>&1');
    }

    /**
     * Find a process
     *
     * @param string $find Pattern to find
     * @param mixed $without
     * @return array
     */
    public static function ps(string $find, $without = null)
    {
        $return = [];
        $cmd = '(ps ax || ps w) | grep -ie "' . $find . '" | grep -v "grep"';
        if ($without !== null) {
            if (!is_array($without)) {
                $without = [$without];
            }
            foreach ($without as $value) {
                $cmd .= ' | grep -v "' . $value . '"';
            }
        }
        $results = explode("\n", trim(shell_exec($cmd)));
        if (!is_array($results) || count($results) == 0) {
            return $return;
        }
        $order = ['pid', 'tty', 'stat', 'time', 'command'];
        foreach ($results as $result) {
            if (trim($result) == '') {
                continue;
            }
            $explodes = explode(" ", $result);
            $info = [];
            $i = 0;
            foreach ($explodes as $value) {
                if (trim($value) == '') {
                    continue;
                }
                if (isset($order[$i])) {
                    $info[$order[$i]] = trim($value);
                } else {
                    $info[end($order)] = $info[end($order)] . ' ' . trim($value);

                }
                $i++;
            }
            $return[] = $info;
        }
        return $return;
    }

    /**
     * Kill process
     *
     * @param string $find Process to find
     * @param bool $forceKill Force kill flag
     *
     * @return mixed
     * @throws \Exception
     */
    public static function kill($find = '', $forceKill = true)
    {
        if (trim($find) == '') {
            return null;
        }
        if (is_numeric($find)) {
            $kill = posix_kill($find, 15);
            if ($kill) {
                return true;
            }
            if ($forceKill) {
                usleep(100);
                $kill = posix_kill($find, 9);
                if ($kill) {
                    return true;
                }
                usleep(100);
                exec(SystemHelper::getCmdSudo() . 'kill -9 ' . $find);
            } else {
                posix_kill($find, 15);
            }
            return null;
        }
        if ($forceKill) {
            $cmd = "(ps ax || ps w) | grep -ie '" . $find . "' | grep -v grep | awk '{print $1}' | xargs " . SystemHelper::getCmdSudo() . "kill -9 > /dev/null 2>&1";
        } else {
            $cmd = "(ps ax || ps w) | grep -ie '" . $find . "' | grep -v grep | awk '{print $1}' | xargs " . SystemHelper::getCmdSudo() . "kill > /dev/null 2>&1";
        }
        exec($cmd);
        return true;
    }

    /**
     * Get apache group id
     *
     * @return string Apache group id
     */
    public static function getWWWGid(): string
    {
        return self::getWWWUid();
    }

    /**
     * Get apache user id name
     *
     * @return string User id name
     */
    public static function getWWWUid(): string
    {
        $distrib = self::getDistrib();
        if ($distrib == 'debian') {
            return 'www-data';
        }
        return 'apache';
    }

    /**
     * Get proccessor cores count
     *
     * @return string
     */
    public static function getProcessorCoresCount(): string
    {
        $cmd = "uname";
        $os = strtolower(trim(shell_exec($cmd)));
        switch ($os) {
            case('linux'):
                $cmd = "cat /proc/cpuinfo | grep processor | wc -l";
                break;
            case('freebsd'):
                $cmd = "sysctl -a | grep 'hw.ncpu' | cut -d ':' -f2";
                break;
            default:
                unset($cmd);
                break;
        }
        if (isset($cmd) && $cmd != '') {
            $cpuCoreNb = intval(trim(shell_exec($cmd)));
        }
        return empty($cpuCoreNb) ? 1 : $cpuCoreNb;
    }

    /**
     * Get HTTP connections count
     *
     * @return string
     */
    public static function getHttpConnectionsCount(): string
    {
        return shell_exec('ss -t | grep http | grep ESTAB | wc -l');
    }

    /**
     * Get process count
     *
     * @return string Count
     */
    public static function getProcessCount(): string
    {
        return shell_exec('ps -aux | wc -l');
    }

    /**
     * Get uptime
     *
     * @return string
     */
    public static function getUptime(): string
    {
        $uptime = preg_replace('/\.[0-9]+/', '', file_get_contents('/proc/uptime'));
        return intval($uptime);
    }

    /**
     * @return array
     */
    public static function getMemInfo()
    {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = [];
        foreach ($data as $line) {
            $info = explode(":", $line);
            if (count($info) != 2) {
                continue;
            }
            $value = explode(' ', trim($info[1]));
            $meminfo[$info[0]] = trim($value[0]);
        }
        return $meminfo;
    }

    /**
     * Recursively delete given path
     *
     * @param $path
     * @return bool false when error occurs
     */
    public static function rrmdir($path)
    {
        $status = 0;
        $cmd = sprintf("rm -rf %s", $path);

        system($cmd, $status);
        return ($status == 0);
    }

    /**
     *
     */
    public static function consistency()
    {
        LogHelper::clear('consistency');
        $cmd = __DIR__ . '/../../install/consistency.php';
        $cmd .= ' >> ' . LogHelper::getPathToLog('consistency') . ' 2>&1 &';
        self::php($cmd, true);
    }

    /**
     * Execute PHP command
     *
     * @param string $arguments
     * @param bool $elevatedPrivileges Use elevated privileges
     *
     * @return string Result of the command
     * @throws \Exception
     */
    public static function php(string $arguments, $elevatedPrivileges = false)
    {
        $exec = "";
        if ($elevatedPrivileges) {
            $exec .= self::getCmdSudo() . ' ';
        }
        $exec .= 'php ' . $arguments;
        return exec($exec);
    }

    /**
     * Stop a service
     *
     * @param string serviceName
     * @return string Result of the command
     * @throws \Exception
     */
    public static function stopService(string $serviceName)
    {
        return exec(self::getCmdSudo() . ' service ' . $serviceName . ' stop');
    }

    /**
     * Start a service
     *
     * @param string serviceName
     * @return string Result of the command
     * @throws \Exception
     */
    public static function startService(string $serviceName)
    {
        return exec(self::getCmdSudo() . ' service ' . $serviceName . ' start');
    }

    public static function getSystemInformations()
    {
        return shell_exec('uname -a');
    }

    /**
     *
     */
    public static function cleanFileSystemRight()
    {
        //nothing to do for NextDom
    }

    public static function getArch()
    {
        $arch = php_uname('m');
        if($arch === 'x86_64') {
            return 'amd64';
        }
        if($arch === 'aarch64') {
            return 'arm64';
        }
        if($arch == 'armv7l' || $arch == 'armv6l') {
            return 'arm';
        }
        return $arch;
    }
    
    public static function getInstalledPackages($packagesManager)
    {
        if (isset(self::$_installPackage[$packagesManager])) {
            return self::$_installPackage[$packagesManager];
        }
        self::$_installPackage[$packagesManager] = array();
        switch ($packagesManager) {
            case 'apt':
                $lines = explode("\n", shell_exec('dpkg -l | tail -n +6'));
                foreach ($lines as $line) {
                    $infos = array_values(array_filter(explode("  ", $line)));
                    if (!isset($infos[1])) {
                        continue;
                    }
                    self::$_installPackage[$packagesManager][$infos[1]] = array(
                        'version' => $infos[2]
                    );
                }
                break;
            case 'pip2':
                $lines = explode("\n", shell_exec('pip2 list --format=columns | tail -n +3'));
                foreach ($lines as $line) {
                    $infos = array_values(array_filter(explode("  ", $line)));
                    if (!isset($infos[0]) || !isset($infos[1])) {
                        continue;
                    }
                    self::$_installPackage[$packagesManager][$infos[0]] = array(
                        'version' => $infos[1]
                    );
                }
                break;
            case 'pip3':
                $lines = explode("\n", shell_exec('pip3 list --format=columns | tail -n +3'));
                foreach ($lines as $line) {
                    $infos = array_values(array_filter(explode("  ", $line)));
                    if (!isset($infos[0])) {
                        continue;
                    }
                    self::$_installPackage[$packagesManager][$infos[0]] = array(
                        'version' => $infos[1]
                    );
                }
                break;
        }
        return self::$_installPackage[$packagesManager];
    }

    public static function checkAndInstall($_packages, $_fix = false, $_foreground = false)
    {
        $return = [];
        foreach ($_packages as $type => $value) {
            $installPackage = self::getInstallPackage($type);
            foreach ($_packages[$type] as $package => $info) {
                $found = 0;
                $alternative_found = '';
                $version = '';
                if (isset($installPackage[$package])) {
                    $found = 1;
                    $version = $installPackage[$package]['version'];
                } elseif (isset($info['alternative'])) {
                    foreach ($info['alternative'] as $alternative) {
                        if (isset($installPackage[$alternative])) {
                            $found = 2;
                            $alternative_found = $alternative;
                            $version = $installPackage[$alternative]['version'];
                            break;
                        }
                        $keys = array_values(preg_grep($alternative, array_keys($installPackage)));
                        if (is_array($keys) && count($keys) > 0) {
                            $found = 2;
                            $alternative_found = $keys[0];
                            $version = $installPackage[$keys[0]]['version'];
                            break;
                        }
                    }
                }
                $needUpdate = false;
                if (isset($info['version']) && version_compare($version, $info['version']) < 0) {
                    $found = 0;
                    $needUpdate = true;
                }
                $return[$type . '::' . $package] = array(
                    'name' => $package,
                    'status' => $found,
                    'version' => $version,
                    'type' => $type,
                    'needUpdate' => $needUpdate,
                    'needVersion' => isset($info['version']) ? $info['version'] : '',
                    'alternative_found' => $alternative_found,
                    'optional' => isset($info['optional']) ? $info['optional'] : false,
                    'fix' => ($found == 0) ? self::installPackage($type, $package) : ''
                );
            }
        }
        if (!$_fix) {
            return $return;
        }
        $cmd = "set -x\n";
        $cmd .= " echo '*******************Begin of package installation******************'\n";
        if ($_foreground) {
            if (self::checkInstallationLog() != '') {
                echo shell_exec(self::checkInstallationLog() . ' 2>&1');
            }
        } else {
            $cmd .= self::checkInstallationLog();
        }
        if ($_foreground) {
            echo shell_exec(self::getCmdSudo() . " apt update 2>&1");
        } else {
            $cmd .= self::getCmdSudo() . " apt update\n";
        }

        foreach ($return as $package => $info) {
            if ($info['status'] != 0 || $info['optional']) {
                continue;
            }
            if ($_foreground) {
                echo shell_exec(self::installPackage($info['type'], $info['name']) . ' 2>&1');
            } else {
                $cmd .= self::installPackage($info['type'], $info['name']) . "\n";
            }
        }
        if ($_foreground) {
            return;
        }
        $cmd .= " echo '*******************End of package installation******************'\n";
        if (file_exists('/tmp/nextdom_fix_package')) {
            shell_exec(self::getCmdSudo() . ' rm /tmp/nextdom_fix_package');
        }
        file_put_contents('/tmp/nextdom_fix_package', $cmd);
        self::launchScriptPackage();
    }

    public static function launchScriptPackage()
    {
        if (count(self::ps('dpkg')) > 0 || count(self::ps('apt')) > 0) {
            throw new CoreException(__('Installation de package impossible car il y a déjà une installation en cours'));
        }
        shell_exec(self::getCmdSudo() . ' chmod +x /tmp/nextdom_fix_package');
        if (class_exists('log')) {
            $log = LogHelper::getPathToLog('packages');
            LogHelper::clear('packages');
        } else {
            $log = '/tmp/nextdom_fix_package_log';
        }
        if (exec('which at | wc -l') == 0) {
            exec(self::getCmdSudo() . '/bin/bash /tmp/nextdom_fix_package >> ' . $log . ' 2>&1 &');
        } else {
            if (!file_exists($log)) {
                touch($log);
            }
            exec('echo "/bin/bash /tmp/nextdom_fix_package >> ' . $log . ' 2>&1" | ' . self::getCmdSudo() . ' at now');
        }
    }

    public static function installPackage($packagesManager, $packageName)
    {
        switch ($packagesManager) {
            case 'apt':
                return self::getCmdSudo() . ' apt install -y ' . $packageName;
            case 'pip2':
                return self::getCmdSudo() . ' pip2 install ' . $packageName;
            case 'pip3':
                return self::getCmdSudo() . ' pip3 install ' . $packageName;
        }
    }

    public static function checkInstallationLog()
    {
        if (class_exists('log')) {
            $log = LogHelper::getPathToLog('packages');
        } else {
            $log = '/tmp/nextdom_fix_package_log';
        }
        if (file_exists($log)) {
            $data = file_get_contents($log);
            if (strpos($data, 'dpkg configure -a')) {
                return "sudo dpkg --configure -a --force-confdef\n";
            }
        }
        return '';
    }

}

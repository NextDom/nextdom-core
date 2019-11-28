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
     * Load system from /core/config/system_cmd.json if file exists.
     *
     * @return array List of commands
     */
    public static function loadCommand(): array
    {
        if (file_exists(NEXTDOM_ROOT . '/core/config/system_cmd.json')) {
            $content = file_get_contents(NEXTDOM_ROOT . '/core/config/system_cmd.json');
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
        if ($elevatedPrivileges) {
            return exec(self::getCmdSudo() . ' php ' . $arguments);
        }
        return exec('php ' . $arguments);
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

}

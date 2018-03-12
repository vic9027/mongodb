<?php
/**
 * @copyright (c) 2018, sunny-daisy 
 * all rights reserved.
 *
 * a package of php mongodb library
 *
 * @author      wenqiang1 <wenqiang1@staff.sina.com.cn>
 * @createdate  2018-03-06
 */

namespace Daisy\MongoDB;

use \MongoDB\Driver\Exception\ConnectionTimeoutException;

final class MongoDB
{
    private $client;
    private $variables;
    private static $clients;

    /**
     * construct method, initialize client
     */
    public function __construct(string $db = 'db0', array $uriOptions = [], array $driverOptions = [])
    {
        $dbKey = md5($db . serialize($uriOptions) . serialize($driverOptions));

        if (!isset(self::$clients[$dbKey]) || !self::$clients[$dbKey] instanceOf \MongoDB\Client) {
            $client = $this->getClient($db, $uriOptions, $driverOptions);
            if ($client == false) {
                return false;
            }
            self::$clients[$dbKey] = $client;
        }

        $this->client = self::$clients[$dbKey];
    }
    
    /**
     * @return Mongo\Client | boolean
     */
    private function getClient(string $db = 'db0', array $uriOptions = [], array $driverOptions = [])
    {
        $uri = $this->buildUri($db);
        
        if ($uri == false) {
            return false; 
        }

        try {
            $ret = new \MongoDB\Client($uri, $uriOptions, $driverOptions);
        } catch (Exception $e) {
            $msg = sprintf('[errno]%d,[error]%s', $e->getCode(), $e->getMessage());
            $ret = $this->error(90311, $msg);
        }
        
        return $ret; 
    }

    /**
     * support multi database
     * @return string
     */
    private function buildUri(string $db = 'db0')
    {
        if (!isset(\SysInitConfig::$config['mongodb'][$db])) {
            $this->error(90311, $db . ' config empty');
            return false;
        }
        $config = \SysInitConfig::$config['mongodb'][$db];

        if (!isset($config['host']) || !$config['host']) {
            $this->error(90311, 'mongodb host empty, db:' . $db);
            return false;
        }
        $host = $config['host'];

        $auth = '';
        if (isset($config['user']) && $config['user'] && isset($config['pass']) && $config['pass']) {
            $auth = $config['user'] . ':' . $config['pass'] . '@';
        }

        $port = isset($config['port']) && $config['port'] ? ':' . $config['port'] : '';
        $db   = isset($config['db'])   && $config['db']   ? $config['db'] : '';

        $option = ''; 
        if (isset($config['option']) && is_array($config['option']) && $config['option']) {
            foreach ($config['option'] as $k => $v) {
                if (!empty($v)) {
                    $option .= $k . '='  . $v . '&';
                }
            }
            $option = $option ? '?' . rtrim($option, '&') : '';
        }

        $uri = rtrim('mongodb://' . $auth . $host . $port . '/' . $db . $option, '/');
        return $uri;
    }

    /**
     * magic method, get database and collection name 
     */
    public function __get(string $name)
    {
        $this->variables[] = $name;
        return $this;
    }

    /**
     * magic method, call function 
     */
    public function __call(string $func = '', array $args = []) 
    {
        $db      = isset($this->variables[0]) ? $this->variables[0] : '';
        $collect = isset($this->variables[1]) ? $this->variables[1] : '';

        $startRunTime = microtime(true);
        try {
            $mongo = new \MongoDB\Collection($this->client->getManager(), $db, $collect);
            $ret = call_user_func_array(array($mongo, $func), $args);
            $runTime = \BaseModelCommon::addStatInfo('mongodb', $startRunTime);
            \BaseModelCommon::debug($args, 'mongodb_method_' . $func);
        } catch (ConnectionTimeoutException $e) {
            $runTime = \BaseModelCommon::addStatInfo('mongodb', $startRunTime);  
            $msg = sprintf('[errno]%d,[error]%s', $e->getCode(), $e->getMessage());
            $ret = $this->error(90314, $msg);
        } catch (Exception $e) {
            $runTime = \BaseModelCommon::addStatInfo('mongodb', $startRunTime);
            $msg = sprintf('[errno]%d,[error]%s', $e->getCode(), $e->getMessage());
            $ret = $this->error(90315, $msg);
        }
        
        $this->variables = [];
        return $ret;
    }

    /**
     * @return boolean
     */
    private function error($errno = 0, $error = '')
    {
        \BaseModelCommon::debug($error, 'mongodb_error');
        \BaseModelLog::sendLog($errno, $error, '', \BaseModelLog::ERROR_MODEL_ID_DB);
        return false;
    }

    /**
     * used to phpunit debug
     */
    public function __debugInfo()
    {
         return ['clients' => self::$clients, 'client' => $this->client];
    }
}

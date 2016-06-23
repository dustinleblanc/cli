<?php

namespace Pantheon\Terminus\Services;

use Pantheon\Terminus\Config\Config;
use Pantheon\Terminus\Services\Caches\FileCache;
use Pantheon\Terminus\Models\User;

class Session extends TerminusService
{
    /**
     * @var Session
     */
    public static $instance;
    /**
     * @var object
     */
    protected $data;
    protected $cache;
    protected $expireTime;

    /**
     * Instantiates object, sets session data
     */
    public function __construct()
    {
        parent::__construct();
        $this->cache = $this->getContainer()->get('FileCache');
        $this->data = $this->getCache()->getData('session') ?: new \stdClass();
        if (property_exists($this->data, 'expires_at')) {
            $this->setExpireTime($this->data->expires_at);
        }
        self::$instance = $this;
    }

    /**
     * Returns given data property or default if DNE.
     *
     * @param string $key Name of property to return
     * @param mixed $default Default return value in case property DNE
     * @return mixed
     */
    public function get($key = 'session', $default = false)
    {
        if (isset($this->data) && isset($this->data->$key)) {
            return $this->data->$key;
        }
        return $default;
    }

    /**
     * Retrieves session data
     *
     * @return object
     */
    public static function getData()
    {
        return self::instance()->data;
    }

    /**
     * Returns session data indicated by the key
     *
     * @param string $key Name of session property to retrieve
     * @return mixed
     */
    public static function getValue($key)
    {
        return self::instance()->get($key);
    }

    /**
     * Returns self, instantiating self if necessary
     *
     * @return Session
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sets a keyed value to be part of the data property object
     *
     * @param string $key Name of data property
     * @param mixed $value Value of property to set
     * @return Session
     */
    public function set($key, $value = null)
    {
        $this->data->$key = $value;
        return $this;
    }

    /**
     * Saves session data to cache
     *
     * @param array $data Session data to save
     * @return $this
     */
    public function setData($data)
    {
        if (empty($data)) {
            return $this;
        }
        $this->getCache()->putData('session', $data);
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
        return $this;
    }

    /**
     * Returns a user with the current session user id
     *
     * @return [user] $session user
     */
    public static function getUser()
    {
        return User::findOrCreate(
            self::getValue('user_id')
        );
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param mixed $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return mixed
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * @param mixed $expireTime
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;
    }

}

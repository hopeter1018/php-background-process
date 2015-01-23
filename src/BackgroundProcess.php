<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess;

use Closure;
use Exception;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;
use Hopeter1018\BackgroundProcess\Enum\InstanceType;
use Hopeter1018\BackgroundProcess\Enum\IntervalStrategy;
use Hopeter1018\BackgroundProcess\PostAdapter\PostAdapter;

/**
 * Description of BackgroundProcess
 *
 * $bp->setEnabled(false);<br />
 * @version $id$
 * @author peter.ho
 */
final class BackgroundProcess
{

// <editor-fold defaultstate="collapsed" desc="properties">

    /**
     *
     * @var InstanceType
     */
    private $instanceType = null;

    /**
     *
     * @var IntervalStrategy
     */
    private $intervalStrategy = null;

    /**
     *
     * @var int
     */
    private $interval;

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var Cache
     */
    private $cache;

    /**
     *
     * @var int
     */
    private $instanceIndex = 0;

// </editor-fold>

    /**
     * 
     * @param Closure $func
     * @param string $name
     * @param Cache $cache
     * @param int $interval
     * @param InstanceType $instanceType Default: InstanceType::single()
     * @param IntervalStrategy $intervalStrategy Default: IntervalStrategy::repeatedWaitNext()
     */
    public function __construct(Closure $func, $name = null, Cache $cache, $interval = 60, InstanceType $instanceType = null, IntervalStrategy $intervalStrategy = null)
    {
        $this->checkAndBindCache($cache);

        if (! $this->isFromSelfPost()) {
            $this->preparation($name, $interval, $instanceType, $intervalStrategy);
            $this->registerFirstPost();
        } elseif (self::isRunFromSelfPost()) {
            $this->run($func);
        } else {
            $this->scheduler();
        }
    }

    /**
     * 
     * @param string $name
     * @param int $interval
     * @param InstanceType $instanceType
     * @param IntervalStrategy $intervalStrategy
     */
    private function preparation($name, $interval, InstanceType $instanceType = null, IntervalStrategy $intervalStrategy = null)
    {
        $this->name = $name ?: ("ZMS_SCHEDULER_" . $_SERVER['REQUEST_TIME']);
        $this->instanceType = $instanceType ?: InstanceType::single();
        $this->intervalStrategy = $intervalStrategy ?: IntervalStrategy::repeatedWaitNext();
        $this->interval = $interval;
        $this->instanceIndex = $this->getCache('index', 0) + 1;
        if ($this->instanceIndex > PHP_INT_MAX) {
            $this->instanceIndex = 1;
        }

        $this->initCache();
    }

    private function checkAndBindCache(Cache $cache = null)
    {
        if ($cache instanceof ArrayCache) {
            throw new Exception("ArrayCache is not supported as it is not a persistent cache");
        }

        $this->cache = $cache;
    }

    private function registerFirstPost()
    {
        $me = $this;
        register_shutdown_function(function () use ($me) {
            if (! $me->intervalStrategy->isValue(IntervalStrategy::ONCE)) {
                PostAdapter::postScheduler($me);
            }
            PostAdapter::postRun($me);
        });
    }

    private function initCache()
    {
        $this->setCache('index', $this->instanceIndex);
        $this->setCache('enabled', true);
        $this->setInstanceCache('runTotal', 0);
    }

// <editor-fold defaultstate="collapsed" desc="Process: Scheduler">

    /**
     * 
     */
    private function scheduler()
    {
        if ($this->getCache('enabled')
            AND InstanceType::isAllowed($this)
            AND IntervalStrategy::isAllowed($this)
        ) {
            $this->schedulerSleepPost();
            PostAdapter::postRun($this);
        } else {
            $this->deleteInstanceCache('runTotal');
        }
    }

    /**
     * 
     */
    private function schedulerSleepPost()
    {
        try {
            sleep($this->interval);

            PostAdapter::postScheduler($this);
            $this->setCache('scheduler', microtime(true));
        } catch (Exception $ex) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " error:" . $ex->getMessage());
        }
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Process: Run">

    /**
     * Run the task.
     * <ol>
     * <li>Mark run
     * <li>Mark last run time
     * </ol>
     * @param Closure $func
     */
    private function run(Closure $func)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        $this->registerRunTick();

        $this->setInstanceCache('runTotal', $this->getInstanceCache('runTotal') + 1);
        $this->setCache('running', true);
        $func($this);
        $this->setCache('running', false);
    }

    /**
     * Register tick function during "Process: run"
     */
    private function registerRunTick()
    {
        declare(ticks = 1)
        $me = $this;
        register_tick_function(function() use($me) {
            $me->setCache('tick', microtime(true));
        });
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Status handling">

    public function setEnabled($boolean)
    {
        $this->setCache('enabled', $boolean);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $lifeTime
     */
    private function setCache($key, $value, $lifeTime = 0)
    {
        $this->cache->save("{$this->name}::{$key}", $value, $lifeTime);
    }

    /**
     * 
     * @param string $key
     * @param mixed $default
     * @param int $lifeTime
     * @return mixed
     */
    private function getCache($key, $default = null, $lifeTime = 0)
    {
        $cacheKey = "{$this->name}::{$key}";
        if (! $this->cache->contains($cacheKey) and $default !== null) {
            $this->cache->save($key, $default, $lifeTime);
        }
        return $this->cache->fetch($cacheKey);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $lifeTime
     */
    private function setInstanceCache($key, $value, $lifeTime = 0)
    {
        $this->cache->save("{$this->name}::{$this->instanceIndex}::{$key}", $value, $lifeTime);
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $lifeTime
     */
    private function deleteInstanceCache($key)
    {
        $this->cache->delete("{$this->name}::{$this->instanceIndex}::{$key}");
    }

    /**
     * 
     * @param string $key
     * @param mixed $default
     * @param int $lifeTime
     * @return mixed
     */
    public function getInstanceCache($key, $default = null, $lifeTime = 0)
    {
        $cacheKey = "{$this->name}::{$this->instanceIndex}::{$key}";
        if (! $this->cache->contains($cacheKey)) {
            $this->cache->save($cacheKey, $default, $lifeTime);
        }
        return $this->cache->fetch($cacheKey);
    }

    /**
     * 
     * @return boolean
     */
    public function isRunning()
    {
        return $this->getCache('running');
    }

    /**
     * @return boolean
     */
    public function isLastTickExpired()
    {
        return microtime(true) - $this->getCache('tick') > 60;
    }

    public function getRunTotal()
    {
        return $this->getInstanceCache('runTotal', 0);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Status from http-header">

    /**
     * Check if the request is from self-post
     * By checking the HttpRequest header
     * 
     * @link self::fromJsonStr
     * @return boolean
     */
    private function isFromSelfPost()
    {
        $fromSelfPostSuccess = false;
        if (isset($_SERVER['HTTP_ZMS_SCHEDULER'])) {
            try {
                $this->fromJsonStr(base64_decode($_SERVER['HTTP_ZMS_SCHEDULER']));

                $fromSelfPostSuccess = true;
            } catch (Exception $ex) {
                error_log($ex->getMessage());
            }
        }
        return $fromSelfPostSuccess;
    }

    /**
     * Check if the request is from self-post and is a "run" call
     * 
     * @return boolean
     */
    private static function isRunFromSelfPost()
    {
        return isset($_SERVER['HTTP_ZMS_SCHEDULER_RUN']);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Getters">
    
    public function getInstanceType()
    {
        return $this->instanceType;
    }

    public function getIntervalStrategy()
    {
        return $this->intervalStrategy;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function getName()
    {
        return $this->name;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Json serialize">

    /**
     * 
     * @return string|json
     */
    public function toJson()
    {
        return json_encode(array(
            'name' => $this->name,
            'instanceType' => $this->instanceType->toArray(),
            'intervalStrategy' => $this->intervalStrategy->toArray(),
            'interval' => $this->interval,
            'instanceIndex' => $this->instanceIndex,
        ));
    }

    /**
     * Get properties from HTTP Header
     * <ul>
     * <li>name
     * <li>interval
     * <li>instanceType
     * <li>intervalStrategy
     * <li>instanceIndex
     * </ul>
     * @param string $jsonStr
     * @return \self
     */
    public function fromJsonStr($jsonStr)
    {
        $json = json_decode($jsonStr);
        if (is_object($json)) {
            $this->name = $json->name;
            $this->interval = $json->interval;
            $this->instanceType = InstanceType::fromArray($json->instanceType);
            $this->intervalStrategy = IntervalStrategy::fromArray($json->intervalStrategy);
            $this->instanceIndex = $json->instanceIndex;
        } else {
            throw new Exception("Can't initiate from the JsonStr");
        }
    }

// </editor-fold>

}

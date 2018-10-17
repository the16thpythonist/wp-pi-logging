<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 15.08.18
 * Time: 15:19
 */

namespace Log;


/**
 * Class VoidLog
 *
 * This is a class that implements the LogInterface. It can be used in the place of any other log. The difference is,
 * that objects of this class dont do anything, all the methods are just stubs.
 * This class can be used in instances, when a LogInterface like object is expected as a parameter of a function/method
 * but the content is not actually supposed to be logged.
 * All log messages passed to this will just disappear.
 *
 * CHANGELOG
 *
 * Added 15.08.2018
 *
 * @since 0.0.0.8
 *
 * @package Log
 */
class VoidLog implements LogInterface
{

    public function start()
    {
        null;
    }

    public function stop()
    {
        null;
    }

    public function load()
    {
        null;
    }

    public function getTotalTime()
    {
        return 0;
    }

    public function getPastEntries(int $number)
    {
        return array();
    }

    public function info(string $message)
    {
        null;
    }

    public function debug(string $message)
    {
        null;
    }

    public function warning(string $message)
    {
        null;
    }

    public function error(string $message)
    {
        null;
    }

    /**
     * Returns a new instance of a VoidLog object
     *
     * CHANGELOG
     *
     * Added 15.08.2018
     *
     * @since 0.0.0.8
     *
     * @return mixed
     */
    public static function newInstance() {
        $class = self::class;
        $void_log = new $class();
        return $void_log;
    }

}
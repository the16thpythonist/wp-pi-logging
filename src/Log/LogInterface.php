<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 24.06.18
 * Time: 13:27
 */

namespace Log\LogInterface;

interface LogInterface
{
    public function start();
    public function stop();
    public function load();
    public function getTotalTime();
    public function getPastEntries(int $number);
    public function info(string $message);
    public function debug(string $message);
    public function warning(string $message);
    public function error(string $message);
}
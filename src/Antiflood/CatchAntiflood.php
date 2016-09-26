<?php

namespace Antiflood;
use DateInterval;

class CatchAntiflood
{
    private $storage;
    const TIMEOUT = "PT1H";
    private $timeout;

    public function __construct($timeout = self::TIMEOUT, $storage = array())
    {
        $this->timeout = new DateInterval($timeout);
        $this->storage = $storage;
    }

    public function antiflood($data, $details = array())
    {
        return (bool)$this->getAntiflood($data, $details);
    }

    public function getAntiflood($data, $details = array())
    {
        $key = md5(serialize($data));
        $fresh = !isset($this->storage[$key]);
        $stored = !$fresh ? $this->storage[$key] : array('repetitions' => 0);
        $stored['repetitions']++;
        $timed_out = !$fresh && ($details['time'] > date_add($stored['last_time'], $this->timeout));
        $stored['last_time'] = $details['time'];
        $this->storage[$key] = $stored;
        return $fresh || $timed_out ? $stored : null;
    }
}
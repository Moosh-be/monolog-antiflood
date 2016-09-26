<?php
namespace Antiflood;

class CatchAntifloodsTest extends \PHPUnit_Framework_TestCase
{
    public function testFirstAntifloodPasses()
    {
        $catchAntiflood = new \Antiflood\CatchAntiflood();
        $this->assertTrue($catchAntiflood->antiflood($this->exception(), array('time' => new \DateTime())));
    }

    public function testSameAntifloodCaught()
    {
        $catchAntiflood = new \Antiflood\CatchAntiflood();
        $catchAntiflood->antiflood($this->exception(), $this->details());
        $this->assertFalse($catchAntiflood->antiflood($this->exception(), $this->details()));
    }

    public function testAnotherAntifloodPasses()
    {
        $catchAntiflood = new \Antiflood\CatchAntiflood();
        $catchAntiflood->antiflood($this->exception(), array('time' => new \DateTime()));
        $this->assertTrue($catchAntiflood->antiflood(array('file' => 'b.txt'), array('time' => new \DateTime())));
    }

    public function testAntifloodsAfterTimeout()
    {
        $catchAntiflood = new \Antiflood\CatchAntiflood('PT1H');
        $now = new \DateTime();
        $catchAntiflood->antiflood($this->exception(), array('time' => $now));
        $now = clone $now;
        $this->assertTrue($catchAntiflood->antiflood($this->exception(), array('time' => $now->add(new \DateInterval('P1D')))));
    }

    public function testNotAntifloodsAfterSmallTimeout()
    {
        $catchAntiflood = new \Antiflood\CatchAntiflood('PT1H');
        $now = new \DateTime();
        $catchAntiflood->antiflood($this->exception(), array('time' => $now));
        $now = clone $now;
        $this->assertFalse($catchAntiflood->antiflood($this->exception(), array('time' => $now->add(new \DateInterval('PT1M')))));
    }

    public function testStorage()
    {
        $storage = new \ArrayObject();
        $catchAntiflood = new \Antiflood\CatchAntiflood('PT1H', $storage);
        $catchAntiflood->antiflood($this->exception(), $this->details());
        $keys = array();
        foreach ($storage as $k => $v) $keys[] = $k;
        foreach ($keys as $k) unset($storage[$k]);
        $this->assertTrue($catchAntiflood->antiflood($this->exception(), $this->details()));
    }

    private function exception()
    {
        return array(
            'file' => 'a.txt',
            'line' => 11,
            'type' => 'RuntimeException',
            'message' => 'invalid arguments'
    );
}

    private function details()
    {
        return array('additional' => 'info', 'time' => new \DateTime());
    }
} 
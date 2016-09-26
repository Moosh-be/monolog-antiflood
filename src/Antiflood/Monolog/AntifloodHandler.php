<?php
/**
 * Cette classe est un handler Monolog qui compte les évènements identiques
 * et ne fait suivre au handler suivant que dans certaines conditions.
 *
 */

namespace Antiflood\Monolog;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;

class AntifloodHandler extends AbstractHandler
{
    private $handler;
    private $catchAntiflood;

    /**
     *
     * @param AbstractHandler $handler to protect
     * @param \Antiflood\CatchAntiflood $catchAntiflood
     */
    public function __construct(AbstractHandler $handler, \Antiflood\CatchAntiflood $catchAntiflood)
    {
        parent::__construct($handler->getLevel(), $handler->getAntiflood());
        $this->handler = $handler;
        $this->catchAntiflood = $catchAntiflood;
    }

    public function handle(array $record)
    {
        if ($record['level'] < $this->level || isset($record['context']['just_log']) && $record['context']['just_log']) {
            return false;
        }
        $antiflood = \Antiflood\Monolog\MonologAntiflood::monolog2antiflood($record);
        $stored = $this->catchAntiflood->getAntiflood($antiflood['record'], $antiflood['details']);
        if ($stored) {
            $record['extra']['repetitions'] = $stored['repetitions'];
            $this->handler->handle($record);
            return false === $this->bubble;
        }
        return false;
    }
}
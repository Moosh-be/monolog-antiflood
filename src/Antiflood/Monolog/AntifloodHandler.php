<?php
/**
 * Cette classe est un handler Monolog qui compte les évènements identiques
 * et ne fait suivre au handler suivant que dans certaines conditions.
 *
 * AntifloodHandler a besoin
 * - d'un handler à qui relayer les évènements (premier param du constructeur)
 * - d'une méthode qui valide le relai de l'évènement au handler 
 * - d'un stockage de compteur d'évènements (qui supporte l'increment atomique)
 * - d'une méthode qui permet de calculer la clé du compteur
 *
 */

namespace Antiflood\Monolog;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;

class AntifloodHandler extends AbstractHandler implements HandlerInterface
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
        parent::__construct($handler->getLevel(), $handler->getBubble());
        $this->handler = $handler;
        $this->catchAntiflood = $catchAntiflood;

        if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
            throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
        }
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
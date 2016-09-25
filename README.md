# monolog-antiflood
# =================

Monolog handler for limiting logging of similar records

La mécanisme est le suivant : 
On vient placer ce handler entre l'envoi des évènements et le journal cible de votre choix.
Avant d'envoyer les événements au journal cible, on calcule une clé sur base du contenu de l'événement. 
Cette clé correspond à un compteur.
Les événements ne seront réellement relayés au journal cible que pour lorsque la fonction trigger est positive.
Cette fonction se base sur la valeur du compteur ou sur le temps pour déclencher cette écriture.


## Note 

based on bobagold/monolog-bubble
With bubble, duplicate event are stored into memcache  during an fix time interval.




bobagold/monolog-bubble example 

    use Bubble\CatchBubble;
    use Bubble\MemcacheArray;
    use Bubble\Monolog\BubbleHandler;

    $log = new \Monolog\Logger(/*...*/);
    $log->pushHandler(/*...*/); // this handler will log everything

    $mailHandler = new \Monolog\Handler\NativeMailerHandler('support@example.com', 'Error report', 'noreply@example.com');
    $mailHandler->setFormatter(/*...*/);

    //$mailHandler will not pollute support mailbox with similar records more than once an hour
    $log->pushHandler(new BubbleHandler($mailHandler, new CatchBubble('PT1H', new MemcacheArray())));
    $log->pushProcessor(/*...*/);

    \Monolog\ErrorHandler::register($log);

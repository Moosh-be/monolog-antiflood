# monolog-antiflood
----

Monolog handler for limiting logging of similar records

## The mechanism 

We just put this handler between sending events and target log of your choice. 
Before sending the log events to the target, we calculate a key based on the content of the event. 
This key is a counter. 
The events will actually relayed to the target log for when the trigger function is positive. 
This function is based on the counter value or time to trigger this writing.

### Compute the key
by default the key is computed on all fields of the event.

You can set a white list or black list.

If white list is set, the black list is ignored.

If white list is set, only theses fields are used to compute the key.
Else all fields of the black list are removed before to compute the key.

The key is an hash()

### counter storage
By default the counter is stored in memcache (because it' my need :-) 
Redis can be replaced with memcached, a stream, ...


----
## Note 

based on bobagold/monolog-bubble
With bubble, duplicate event are stored into memcache  during an fix time interval.

## bobagold/monolog-bubble example 

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

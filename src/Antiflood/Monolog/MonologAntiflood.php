<?php


namespace Antiflood\Monolog;
use Antiflood\GeneralizeException;

class MonologAntiflood
{
    private static function generalizeException(\Exception $e)
    {
        $exception = self::exceptionContext($e);
        unset($exception['trace']);
        if (isset($exception['previous'])) {
            $exception['previous'] = self::generalizeException($e->getPrevious());
        }
        switch ($exception['class']) {
            case 'Doctrine\\DBAL\\DBALException':
                if (strstr($exception['message'], 'with params') && $exception['previous'])
                    $exception['message'] = $exception['previous']['message'];
                break;
        }
        if ($e instanceof GeneralizeException) {
            $exception = $e->generalize($exception);
        }
        return $exception;
    }

    public static function monolog2antiflood(array $record)
    {
        unset($record['channel']);
        $details = $record;
        $details['time'] = $record['datetime'];
        unset($record['datetime']);
        unset($record['extra']);

        if (isset($record['context']['exception'])) {
            $record['context']['exception'] = self::generalizeException($record['context']['exception']);
        }

        return array(
            'record' => $record,
            'details' => $details,
        );
    }

    public static function fingerPrint(array $record)
    {
        $antiflood = self::monolog2antiflood($record);
        return $antiflood['record'];
    }

    public static function exceptionContext(\Exception $e)
    {
        $array = array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        );
        if ($e->getPrevious()) {
            $array['previous'] = self::exceptionContext($e->getPrevious());
        }

        return $array;
    }
}
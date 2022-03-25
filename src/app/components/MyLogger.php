<?php

namespace App\Controller;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;

class MyLogger
{
    public function log($logFile, $message)
    {
        $adapter = new Stream('../app/logs/' . $logFile . '.log');
        $logger  = new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );

        $logger->error($message);
    }
}

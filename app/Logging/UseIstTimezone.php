<?php

namespace App\Logging;

use App\Support\Ist;
use DateTimeZone;
use Illuminate\Log\Logger;
use Monolog\Logger as MonologLogger;

class UseIstTimezone
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if ($monolog instanceof MonologLogger) {
            $monolog->setTimezone(new DateTimeZone(Ist::ZONE));
        }
    }
}

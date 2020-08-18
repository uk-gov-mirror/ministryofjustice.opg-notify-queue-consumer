<?php

declare(strict_types=1);

use Psr\Log\Test\TestLogger;
use VCR\VCR;

require_once __DIR__ . '/../vendor/autoload.php';

// This bootstrap is shared between unit tests which don't have any env vars
// and functional tests which do and for which we want to setup services
$functionalTestSupport = getenv('OPG_NOTIFY_API_KEY') !== false;

if ($functionalTestSupport) {
    VCR::configure()->enableLibraryHooks(['stream_wrapper', 'curl']);
    VCR::configure()->enableRequestMatchers(['method', 'url', 'host', 'query_string', 'post_fields']);
    VCR::turnOn();
    VCR::turnOff();

    $config = require_once __DIR__ . '/../src/bootstrap/config.php';
    $psrLoggerAdapter = new TestLogger();
    require_once __DIR__ . '/../src/bootstrap/services.php';
}

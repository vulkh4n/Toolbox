<?php

namespace Vulkhan\Toolbox\Trait;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;

trait LoggerTrait
{
    protected Logger $logger;

    public function initLogger(string $channel = "Default", string $filename = null, Level $level = Level::Debug) : void
    {
        $this->logger = new Logger( $channel );
        $this->logger->pushHandler( new StreamHandler( $filename ?? \WP_PLUGIN_DIR . "/Toolbox/var/log/Toolbox.log", $level ) );
    }
}
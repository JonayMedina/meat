<?php

namespace App\MessageHandler;

use App\Message\Sync;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SyncHandler implements MessageHandlerInterface
{
    public function __invoke(Sync $message)
    {
        // ... do some work - like sending an SMS message!
    }
}

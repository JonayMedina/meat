<?php

namespace App\MessageHandler;

use App\Message\PushNotificationMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PushNotificationMessageHandler implements MessageHandlerInterface
{
    public function __invoke(PushNotificationMessage $message)
    {
        // ... do some work - like sending an SMS message!
    }
}

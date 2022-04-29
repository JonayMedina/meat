<?php

namespace App\Message;

/**
 * Class PushNotificationMessage
 * @package App\Message
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class PushNotificationMessage
{
    /**
     * @var int
     */
    private $pushId;

    /**
     * PushNotificationMessage constructor.
     * @param int $pushId
     */
    public function __construct($pushId)
    {
        $this->pushId = $pushId;
    }

    /**
     * @return int
     */
    public function getPushId()
    {
        return $this->pushId;
    }
}

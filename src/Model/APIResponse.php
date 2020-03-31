<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class APIResponse
{
    const TYPE_INFO = 'info';

    const TYPE_ERROR = 'error';

    private $code;

    private $type;

    private $message;

    private $recordset = [];

    /**
     * APIResponse constructor.
     * @param $code
     * @param $type
     * @param $message
     * @param array $recordset
     */
    public function __construct($code = Response::HTTP_OK, $type = self::TYPE_INFO, $message = 'Ok', array $recordset = [])
    {
        $this->setCode($code);
        $this->setType($type);
        $this->setMessage($message);
        $this->setRecordset($recordset);
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return APIResponse
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return APIResponse
     */
    public function setType($type)
    {
        if (!in_array($type, [self::TYPE_INFO, self::TYPE_ERROR])) {
            throw new BadRequestHttpException('Invalid type for API Response.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return APIResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecordset(): array
    {
        return $this->recordset;
    }

    /**
     * @param array $recordset
     * @return APIResponse
     */
    public function setRecordset(array $recordset): APIResponse
    {
        $this->recordset = $recordset;

        return $this;
    }
}

<?php

namespace SimpleSSO\CommonBundle\Exception;

use RuntimeException;

class ApiBadRequestException extends RuntimeException
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $details = [];

    /**
     * ApiBadRequestException constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        parent::__construct('Error ' . $response['status'] . ': ' . $response['error']);
        $this->status = $response['status'];
        if (key_exists('errorDetails', $response['data'])) {
            $this->details = $response['data']['errorDetails'];
        }
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}

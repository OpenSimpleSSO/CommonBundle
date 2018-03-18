<?php

namespace SimpleSSO\CommonBundle\Model;

class AuthServerModel
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $clientId;

    /**
     * AuthServerModel constructor.
     *
     * @param string $host
     * @param string $publicKey
     * @param string $clientId
     */
    public function __construct(string $host, string $publicKey, string $clientId)
    {
        $this->host = $host;
        $this->publicKey = $publicKey;
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getAuthenticationEndPoint(): string
    {
        return $this->host . '/authenticate';
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }
}

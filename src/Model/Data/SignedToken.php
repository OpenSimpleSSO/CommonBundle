<?php

namespace SimpleSSO\CommonBundle\Model\Data;

use Symfony\Component\HttpFoundation\Request;

class SignedToken
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $signature;

    /**
     * SignedToken constructor.
     *
     * @param string $token
     * @param string $signature
     */
    public function __construct(string $token, string $signature)
    {
        $this->token = $token;
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getAsUrlParameters(): string
    {
        return 't=' . urlencode($this->token) . '&s=' . urlencode($this->signature);
    }

    /**
     * @param Request $request
     * @return SignedToken
     */
    public static function FromRequest(Request $request): self
    {
        return new self(
            $request->query->get('t'),
            $request->query->get('s')
        );
    }
}

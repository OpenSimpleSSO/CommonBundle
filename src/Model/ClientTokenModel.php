<?php

namespace SimpleSSO\CommonBundle\Model;

use SimpleSSO\CommonBundle\Model\Data\SignedToken;

class ClientTokenModel extends TokenModel
{
    /**
     * @var AuthServerModel
     */
    private $authServerModel;

    /**
     * ClientTokenModel constructor.
     *
     * @param AuthServerModel $authServerModel
     * @param OpenSslModel    $openSslModel
     */
    public function __construct(AuthServerModel $authServerModel, OpenSslModel $openSslModel)
    {
        parent::__construct($openSslModel);
        $this->authServerModel = $authServerModel;
    }

    /**
     * Emit a signed token containing the given data for the auth server.
     *
     * @param array $data
     * @return SignedToken
     */
    public function emitTokenForAuthServer(array $data): SignedToken
    {
        return $this->emitToken($data, $this->authServerModel->getPublicKey());
    }

    /**
     * Receive and validate a signed token issued by the auth server and extract its data.
     *
     * @param SignedToken $signedToken
     * @param array       $requiredAttributes
     * @return array
     */
    public function receiveTokenFromAuthServer(SignedToken $signedToken, array $requiredAttributes = []): array
    {
        return $this->receiveToken($signedToken, $this->authServerModel->getPublicKey(), $requiredAttributes);
    }

    /**
     * Generate the url to authenticate the user against the auth server using the given token.
     *
     * @param SignedToken $token
     * @return string
     */
    public function generateAuthenticationUrl(SignedToken $token): string
    {
        return $this->authServerModel->getAuthenticationEndPoint() . '?c=' . $this->authServerModel->getClientId() . '&' . $token->getAsUrlParameters();
    }
}

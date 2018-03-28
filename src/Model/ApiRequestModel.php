<?php

namespace SimpleSSO\CommonBundle\Model;

use RuntimeException;
use SimpleSSO\CommonBundle\Exception\ApiBadRequestException;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;

class ApiRequestModel
{
    /**
     * @var AuthServerModel
     */
    private $authServerModel;

    /**
     * @var ClientTokenModel
     */
    private $tokenModel;

    /**
     * ApiRequestModel constructor.
     *
     * @param AuthServerModel  $authServerModel
     * @param ClientTokenModel $tokenModel
     */
    public function __construct(AuthServerModel $authServerModel, ClientTokenModel $tokenModel)
    {
        $this->authServerModel = $authServerModel;
        $this->tokenModel = $tokenModel;
    }

    /**
     * Make a request to an API.
     *
     * @param string     $method
     * @param string     $url
     * @param mixed|null $body
     * @param array      $headers
     * @return array
     */
    public function request(string $method, string $url, $body = null, array $headers = []): array
    {
        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connection, CURLOPT_HTTPHEADER, array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($headers), $headers));
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        if ($body) {
            curl_setopt($connection, CURLOPT_POSTFIELDS, $body);
        }
        $rawResult = curl_exec($connection);
        if ($rawResult === false) {
            throw new RuntimeException(curl_error($connection));
        }
        curl_close($connection);
        $decodedResult = json_decode($rawResult, true);
        if (!is_array($decodedResult)) {
            throw new RuntimeException('Invalid JSON in the response: ' . $rawResult);
        }
        if (key_exists('ok', $decodedResult) && !$decodedResult['ok']) {
            throw new ApiBadRequestException($decodedResult);
        }

        return $decodedResult;
    }

    /**
     * Get the profile of the user identified by the given id.
     *
     * @param string $userId
     * @return array The user's profile.
     */
    public function getUserProfile(string $userId): array
    {
        $response = $this->request(
            'GET',
            $this->authServerModel->getHost() . '/api/user/' . $userId,
            null,
            $this->generateClientAuthenticationHeaders()
        );

        return $response['data'];
    }

    /**
     * Register a new user in the auth server.
     *
     * @param array $profileData
     * @return array The user's profile updated.
     */
    public function registerUser(array $profileData): array
    {
        $response = $this->request(
            'POST',
            $this->authServerModel->getHost() . '/api/user/register',
            json_encode($profileData),
            $this->generateClientAuthenticationHeaders()
        );

        return $response['data'];
    }

    /**
     * Update the user's profile.
     *
     * @param string $userId
     * @param array  $profileData
     * @return array The user's profile updated.
     */
    public function updateUserProfile(string $userId, array $profileData): array
    {
        $response = $this->request(
            'PUT',
            $this->authServerModel->getHost() . '/api/user/' . $userId,
            json_encode($profileData),
            $this->generateClientAuthenticationHeaders()
        );

        return $response['data'];
    }

    /**
     * Update the user's password.
     *
     * @param string $userId
     * @param string $password
     * @return array The user's profile updated.
     */
    public function updateUserPassword(string $userId, string $password): array
    {
        $response = $this->request(
            'PUT',
            $this->authServerModel->getHost() . '/api/user/' . $userId . '/password',
            json_encode([ 'password' => $password ]),
            $this->generateClientAuthenticationHeaders()
        );

        return $response['data'];
    }

    /**
     * Enable a user.
     *
     * @param string $userId
     * @return array The user's profile updated.
     */
    public function enableUser(string $userId): array
    {
        $response = $this->request(
            'POST',
            $this->authServerModel->getHost() . '/api/user/' . $userId . '/enable',
            null,
            $this->generateClientAuthenticationHeaders()
        );

        return $response['data'];
    }

    /**
     * Disable a user.
     *
     * @param string $userId
     * @return array The user's profile updated.
     */
    public function disableUser(string $userId): array
    {
        $response = $this->request(
            'POST',
            $this->authServerModel->getHost() . '/api/user/' . $userId . '/disable',
            null,
            $this->generateClientAuthenticationHeaders()
        );

        return $response['data'];
    }

    /**
     * @param SignedToken $token
     * @return array
     */
    public function generateClientAuthenticationHeaders(SignedToken $token = null): array
    {
        if (!$token) {
            $token = $this->tokenModel->emitAccessToken();
        }

        return [
            'SSSO-Client'                 => $this->authServerModel->getClientId(),
            'SSSO-Access-Token'           => $token->token,
            'SSSO-Access-Token-Signature' => $token->signature,
        ];
    }
}

<?php

namespace SimpleSSO\CommonBundle\Model;

use DateTime;
use Ramsey\Uuid\Uuid;
use RuntimeException;

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
            throw new RuntimeException('Invalid JSON in the response.');
        }
        if (key_exists('ok', $decodedResult) && !$decodedResult['ok']) {
            throw new RuntimeException('Error ' . $decodedResult['status'] . ': ' . $decodedResult['error']);
        }

        return $decodedResult;
    }

    /**
     * Get the profile of the user identified by the given id.
     *
     * @param string $userId
     * @return array
     */
    public function getUserProfile(string $userId): array
    {
        $accessToken = $this->tokenModel->emitAccessToken();

        return $this->request('GET', $this->authServerModel->getHost() . '/api/user/' . $userId, null, [
            'SSSO-Client'                 => $this->authServerModel->getClientId(),
            'SSSO-Access-Token'           => $accessToken->token,
            'SSSO-Access-Token-Signature' => $accessToken->signature,
        ]);
    }
}

<?php

namespace SimpleSSO\CommonBundle\Model;

use SimpleSSO\CommonBundle\Exception\InvalidTokenException;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;
use DateInterval;
use DateTime;

class TokenModel
{
    public const TOKEN_EXPIRATION_INTERVAL = 'PT1M';

    /**
     * @var OpenSslModel
     */
    private $openSslModel;

    /**
     * TokenModel constructor.
     *
     * @param OpenSslModel $openSslModel
     */
    public function __construct(OpenSslModel $openSslModel)
    {
        $this->openSslModel = $openSslModel;
    }

    /**
     * Emit a signed token containing the given data.
     *
     * @param array  $data
     * @param string $publicKey
     * @return SignedToken
     */
    public function emitToken(array $data, string $publicKey): SignedToken
    {
        $content = $this->openSslModel->encrypt(json_encode($data), $publicKey);

        return new SignedToken($content, $this->openSslModel->sign($content));
    }

    /**
     * Receive and validate a signed token and extract its data.
     *
     * @param SignedToken $signedToken
     * @param string      $publicKey
     * @param array       $requiredAttributes
     * @return array
     */
    public function receiveToken(SignedToken $signedToken, string $publicKey, array $requiredAttributes = []): array
    {
        $this->checkTokenSignature($signedToken->token, $signedToken->signature, $publicKey);
        $data = $this->decodeToken($signedToken->token);
        $this->checkAttributes($data, $requiredAttributes);
        if (key_exists('expire', $data)) {
            $this->checkTokenExpiration($data);
        } elseif (key_exists('time', $data)) {
            $data['expire'] = clone $data['time'];
            $data['expire']->add(new DateInterval(self::TOKEN_EXPIRATION_INTERVAL));
            $this->checkTokenExpiration($data);
        }

        return $data;
    }

    /**
     * Get the expiration date according to the given interval representation.
     *
     * @param string        $intervalRepresentation The interval representation as required by DateInterval constructor.
     * @param DateTime|null $from                   If not provided, current time will be used.
     * @return DateTime
     */
    public function getExpirationDate(string $intervalRepresentation, ?DateTime $from = null): DateTime
    {
        $expireDate = $from ?
            clone $from :
            new DateTime();
        $expireDate->add(new DateInterval($intervalRepresentation));

        return $expireDate;
    }

    /**
     * Check the signature of a token against the given public key.
     *
     * @param string $token
     * @param string $signature
     * @param string $publicKey
     * @throws InvalidTokenException when the signature is invalid.
     */
    private function checkTokenSignature(string $token, string $signature, string $publicKey): void
    {
        if (!$this->openSslModel->verify($token, $signature, $publicKey)) {
            throw new InvalidTokenException('The token signature is invalid.');
        }
    }

    /**
     * Decode the token.
     *
     * @param string $token
     * @return array
     * @throws InvalidTokenException
     */
    private function decodeToken(string $token): array
    {
        $data = json_decode($this->openSslModel->decrypt($token), true);
        if (!is_array($data)) {
            throw new InvalidTokenException('Token must be a valid JSON object.');
        }
        $this->decodeDatetime('time', $data);
        $this->decodeDatetime('expire', $data);

        return $data;
    }

    /**
     * @param string $attribute
     * @param array  $data
     * @throws InvalidTokenException when the date is not well formatted.
     */
    private function decodeDatetime(string $attribute, array &$data): void
    {
        if (key_exists($attribute, $data)) {
            $data[$attribute] = DateTime::createFromFormat(DATE_ISO8601, $data[$attribute]);
            if ($data[$attribute] === false) {
                throw new InvalidTokenException('The "' . $attribute . '" attribute must be a valid date formatted in ISO8601.');
            }
        }
    }

    /**
     * Check if the token's data are missing attributes.
     *
     * @param array $data
     * @param array $requirements
     * @throws InvalidTokenException when an attribute is missing.
     */
    private function checkAttributes(array $data, array $requirements): void
    {
        foreach ($requirements as $requirement) {
            if (!key_exists($requirement, $data)) {
                throw new InvalidTokenException('Token must have a "' . $requirement . '" attribute.');
            }
        }
    }

    /**
     * @param array $data
     * @throws InvalidTokenException when token is missing "time" and "expire" attribute or when the token has expired.
     */
    private function checkTokenExpiration(array $data): void
    {
        if ($data['expire'] < new DateTime()) {
            throw new InvalidTokenException('Token has expired.');
        }
    }
}

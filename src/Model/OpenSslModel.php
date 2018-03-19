<?php

namespace SimpleSSO\CommonBundle\Model;

use SimpleSSO\CommonBundle\Exception\OpenSslException;

/**
 * A wrapper model over OpenSSL.
 */
class OpenSslModel
{
    /**
     * @var string
     */
    private $privateKeyFilePath;

    /**
     * @var string
     */
    private $publicKeyFilePath;

    /**
     * OpenSslModel constructor.
     *
     * @param string $privateKeyFilePath
     * @param string $publicKeyFilePath
     */
    public function __construct(string $privateKeyFilePath, string $publicKeyFilePath)
    {
        $this->privateKeyFilePath = $privateKeyFilePath;
        $this->publicKeyFilePath = $publicKeyFilePath;
    }

    /**
     * Get the public key.
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        $publicKey = openssl_pkey_get_public('file://' . $this->publicKeyFilePath);
        if ($publicKey === false) {
            throw new OpenSslException('Could not read server\'s public key.');
        }

        $details = openssl_pkey_get_details($publicKey);
        if ($details === false) {
            throw new OpenSslException('Could not extract details from public key.');
        }

        return $details['key'];
    }

    /**
     * Sign a string.
     *
     * @param string $data
     * @return string
     */
    public function sign(string $data): string
    {
        $privateKey = openssl_pkey_get_private('file://' . $this->privateKeyFilePath);
        if ($privateKey === false) {
            throw new OpenSslException('Could not read server\'s private key.');
        }

        if (!openssl_sign($data, $signature, $privateKey)) {
            throw new OpenSslException('Could not sign the data.');
        }

        return base64_encode($signature);
    }

    /**
     * Verify the data signature against the given public key.
     *
     * @param string $data
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    public function verify(string $data, string $signature, string $publicKey): bool
    {
        switch (openssl_verify($data, base64_decode($signature), $publicKey)) {
            case 0:
                return false;

            case 1:
                return true;

            default:
                throw new OpenSslException('The token signature could not be checked.');
        }
    }

    /**
     * Encrypt the data against the given public key.
     *
     * @param string $data
     * @param string $publicKey
     * @return string
     */
    public function encrypt(string $data, string $publicKey): string
    {
        if (!openssl_public_encrypt($data, $encryptedData, $publicKey)) {
            throw new OpenSslException('The data could not be encrypted');
        }

        return base64_encode($encryptedData);
    }

    /**
     * Decrypt the data.
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $privateKey = openssl_pkey_get_private('file://' . $this->privateKeyFilePath);
        if ($privateKey === false) {
            throw new OpenSslException('Could not read server\'s private key.');
        }

        if (!openssl_private_decrypt(base64_decode($data), $decryptedData, $privateKey)) {
            throw new OpenSslException('The data could not be decrypted.');
        }

        return $decryptedData;
    }
}

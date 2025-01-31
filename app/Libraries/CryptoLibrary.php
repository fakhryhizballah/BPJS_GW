<?php

namespace App\Libraries;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use phpseclib3\File\X509;


class CryptoLibrary
{
    public static function generateUrl($agentName, $agentNik, $accessToken, $apiUrl, $environment)
    {
        try {
            // Generate RSA Key Pair
            $keyPair = self::generateRSAKeyPair();
            $publicKey = $keyPair['publicKey'];
            $privateKey = $keyPair['privateKey'];

            // Select public key based on environment


            // Set the request data
            $data = [
                'agent_name' => $agentName,
                'agent_nik' => $agentNik,
                'public_key' => $publicKey
            ];

            // Convert data to JSON and encrypt
            $jsonData = json_encode($data);
            $pubPEM = "-----BEGIN PUBLIC KEY-----
  MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxLwvebfOrPLIODIxAwFp
  4Qhksdtn7bEby5OhkQNLTdClGAbTe2tOO5Tiib9pcdruKxTodo481iGXTHR5033I
  A5X55PegFeoY95NH5Noj6UUhyTFfRuwnhtGJgv9buTeBa4pLgHakfebqzKXr0Lce
  /Ff1MnmQAdJTlvpOdVWJggsb26fD3cXyxQsbgtQYntmek2qvex/gPM9Nqa5qYrXx
  8KuGuqHIFQa5t7UUH8WcxlLVRHWOtEQ3+Y6TQr8sIpSVszfhpjh9+Cag1EgaMzk+
  HhAxMtXZgpyHffGHmPJ9eXbBO008tUzrE88fcuJ5pMF0LATO6ayXTKgZVU0WO/4e
  iQIDAQAB
  -----END PUBLIC KEY-----";
            $encryptedPayload = self::encryptMessage($jsonData, $pubPEM);

            // Send request via cURL
            $response = self::sendRequest($apiUrl, $encryptedPayload, $accessToken);

            // Decrypt response
            return self::decryptMessage($response, $privateKey);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private static function generateRSAKeyPair()
    {
        $privateKey = RSA::createKey(2048);
        $publicKey = $privateKey->getPublicKey()->toString('PKCS8');
        return ['privateKey' => $privateKey, 'publicKey' => $publicKey];
    }



    private static function encryptMessage($message, $pubPEM)
    {
        $aesKey = Random::string(32);
        $serverKey = PublicKeyLoader::load($pubPEM)->withPadding(RSA::ENCRYPTION_OAEP);
        $wrappedAesKey = $serverKey->encrypt($aesKey);
        $encryptedMessage = self::aesEncrypt($message, $aesKey);
        return self::formatMessage($wrappedAesKey . $encryptedMessage);
    }

    private static function sendRequest($url, $payload, $accessToken)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'X-Debug-Mode: 0',
                'Content-Type: text/plain',
                "Authorization: Bearer $accessToken"
            ],
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);
        return $response;
    }

    private static function decryptMessage($message, $privateKey)
    {
        $beginTag = "-----BEGIN ENCRYPTED MESSAGE-----";
        $endTag = "-----END ENCRYPTED MESSAGE-----";
        $messageContents = substr($message, strlen($beginTag) + 1, strlen($message) - strlen($endTag) - strlen($beginTag) - 2);
        $binaryDerString = base64_decode($messageContents);
        $wrappedKeyLength = 256;
        $wrappedKey = substr($binaryDerString, 0, $wrappedKeyLength);
        $encryptedMessage = substr($binaryDerString, $wrappedKeyLength);
        $key = PublicKeyLoader::load($privateKey);
        $aesKey = $key->decrypt($wrappedKey);
        return self::aesDecrypt($encryptedMessage, $aesKey);
    }

    private static function aesEncrypt($data, $symmetricKey)
    {
        $iv = random_bytes(12);
        $cipher = new AES('gcm');
        $cipher->setKeyLength(256);
        $cipher->setKey($symmetricKey);
        $cipher->setNonce($iv);
        $ciphertext = $cipher->encrypt($data);
        $tag = $cipher->getTag();
        return $iv . $ciphertext . $tag;
    }

    private static function aesDecrypt($encryptedData, $symmetricKey)
    {
        $ivLength = 12;
        $tagLength = 16;
        $iv = substr($encryptedData, 0, $ivLength);
        $tag = substr($encryptedData, -$tagLength);
        $ciphertext = substr($encryptedData, $ivLength, -$tagLength);
        $aes = new AES('gcm');
        $aes->setKey($symmetricKey);
        $aes->setNonce($iv);
        $aes->setTag($tag);
        return $aes->decrypt($ciphertext);
    }

    private static function formatMessage($data)
    {
        return "-----BEGIN ENCRYPTED MESSAGE-----\r\n" . chunk_split(base64_encode($data)) . "-----END ENCRYPTED MESSAGE-----";
    }
}

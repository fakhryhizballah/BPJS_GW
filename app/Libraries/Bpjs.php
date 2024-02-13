<?php

namespace App\Libraries;

class Bpjs
{
    public function  getSingnature()
    {
        // timestamp
        $secretKey = $_ENV['BPJS.secretKey'];
        $X_cons_id = $_ENV['BPJS.X_cons_id'];
        $user_key = $_ENV['BPJS.user_key'];
        $baseURL = $_ENV['BPJS.baseURL'];
        $vclaimURL = $_ENV['BPJS.vclaimURL'];
        $url = $_ENV['BPJS.URL'];
        $ppk = $_ENV['BPJS.kodeppk'];
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $X_cons_id . "&" . $tStamp, $secretKey, true);
        // base64 encode
        $encodedSignature = base64_encode($signature);
        // urlencode
        // $encodedSignature = urlencode($encodedSignature);
        $data = array(
            'timestamp' => $tStamp,
            'signature' => $encodedSignature,
            'secretKey' => $secretKey,
            'X_cons_id' => $X_cons_id,
            'user_key' => $user_key,
            'baseURL' => $baseURL,
            'vclaimURL' => $vclaimURL,
            'URL' => $url,
            'ppk' => $ppk
        );
        return $data;
    }
    // function decrypt
    public function stringDecrypt($key, $string)
    {

        $encrypt_method = 'AES-256-CBC';
        // hash
        $key_hash = hex2bin(hash('sha256', $key));

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

        return $output;
    }

    // function lzstring decompress
    // download libraries lzstring : https://github.com/nullpunkt/lz-string-php
    public function decompress($string)
    {

        return \LZCompressor\LZString::decompressFromEncodedURIComponent($string);
    }
}

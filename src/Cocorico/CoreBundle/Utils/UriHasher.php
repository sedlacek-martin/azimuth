<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Utils;

/**
 * Hash URIs.
 *
 * Inspired by vendor/symfony/http-kernel/UriSigner
 */
class UriHasher
{
    private $secret;

    /**
     * Constructor.
     *
     * @param string $secret A secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Hash an URI.
     *
     * @param string   $url              URL to hash
     * @param bool     $onlyPathAndQuery Extract only path and query
     * @param string[] $fieldsToRemove   Query string fields to remove ex: array(["location"]["address"], ["page"])
     *
     * @return string the hashed URI or '' if malformed url is passed
     */
    public function hash($url, $onlyPathAndQuery = true, $fieldsToRemove = [])
    {
        $uri = parse_url($url);
        if ($uri) {
            if ($onlyPathAndQuery) {
                unset($uri['scheme'], $uri['host'], $uri['port'], $uri['user'], $uri['pass']);
            }

            if (isset($uri['query'])) {
                parse_str($uri['query'], $params);
                //remove fields from query string
                $params = $this->unsetArrayByKeys($params, $fieldsToRemove);
            } else {
                $params = [];
            }

            $uri = $this->buildUrl($uri, $params);

            return $this->computeHash($uri);
        }

        return null;
    }

    /**
     * Unset array by keys expressed as strings
     *
     * @param array    $array
     * @param string[] $unwantedKeys ex: array(["location"]["address"], ["page"])
     * @return array
     */
    private function unsetArrayByKeys($array, $unwantedKeys = [])
    {
        foreach ($unwantedKeys as $unwantedKey) {
            if ($unwantedKey) {
                $arrayExp = '$array' . $unwantedKey;

                eval('unset (' . $arrayExp . ');');
            }
        }

        return $array;
    }

    /**
     * @param $url
     * @return string|null
     */
    private function computeHash($url)
    {
        if ($url) {
            return hash_hmac('sha256', $url, $this->secret);
        }

        return null;
    }

    /**
     * @param array $url
     * @param array $params
     * @return string
     */
    public function buildUrl(array $url, array $params = [])
    {
        PHP::ksort_recursive($params, SORT_STRING);

        $url['query'] = http_build_query($params, '', '&');

        $scheme = isset($url['scheme']) ? $url['scheme'] . '://' : '';
        $host = $url['host'] ?? '';
        $port = isset($url['port']) ? ':' . $url['port'] : '';
        $user = $url['user'] ?? '';
        $pass = isset($url['pass']) ? ':' . $url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $url['path'] ?? '';
        $query = isset($url['query']) && $url['query'] ? '?' . $url['query'] : '';
        $fragment = isset($url['fragment']) ? '#' . $url['fragment'] : '';

        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
    }

    public static function encryptForUrl($string, $secret)
    {
        $key = hash('sha256', $secret);
        $iv = substr(hash('sha256', strrev($secret)), 0, 16);

        return bin2hex(openssl_encrypt($string, 'AES-256-CBC', $key, 0, $iv));
    }

    /**
     * @param $string
     * @param $secret
     * @return string
     */
    public static function decryptForUrl($string, $secret)
    {
        $key = hash('sha256', $secret);
        $iv = substr(hash('sha256', strrev($secret)), 0, 16);

        return @openssl_decrypt(hex2bin($string), 'AES-256-CBC', $key, 0, $iv);
    }
}

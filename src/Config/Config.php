<?php

namespace MedyaT\Parapos\Config;

final class Config
{
    public const API_PROD = 'https://api.parapos.com';

    public const API_TEST = 'https://test-api.parapos.com';

    public bool $isTest = true;

    public string $apiUrl;

    public string $apiKey;

    public string $secretKey;

    public string $language = 'tr';

    private function setApiUrl(string $url = null): void
    {
        if ($url) {
            $this->apiUrl = rtrim($url, '/');

            return;
        }

        $this->apiUrl = $this->isTest
            ? self::API_TEST
            : self::API_PROD;
    }

    /**
     * @param  string[]  $arguments
     */
    public function __construct(array $arguments = [])
    {

        if (isset($arguments['isTest'])) {
            $this->isTest = (bool) $arguments['isTest'];
        }
        if (isset($arguments['apiKey'])) {
            $this->apiKey = $arguments['apiKey'];
        }
        if (isset($arguments['secretKey'])) {
            $this->secretKey = $arguments['secretKey'];
        }
        if (isset($arguments['language'])) {
            $this->language = $arguments['language'];
        }

        $this->setApiUrl($arguments['apiUrl'] ?? null);

    }

    public function signature(string $url, string $hash): string
    {
        return hash_hmac('sha256', $url.$hash, $this->secretKey);
    }

    public function getApiUrl(string $uri): string
    {
        return $this->apiUrl.'/'.trim($uri, '/');
    }
}

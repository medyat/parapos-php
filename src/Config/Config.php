<?php

namespace MedyaT\Parapos\Config;

final class Config
{
    public const API_PROD = 'https://api.parapos.com';

    public const API_TEST = 'https://test-api.parapos.com';

    public bool $isTest = true;

    public bool $isMarketplace = false;

    public string $apiUrl;

    public string $apiKey;

    public string $secretKey;

    public string $language = 'tr';

    public string $response_url = 'parapos/response/{hash}';

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

        if (isset($arguments['isMarketplace'])) {
            $this->isMarketplace = (bool) $arguments['isMarketplace'];
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
        if (isset($arguments['response_url'])) {
            $this->response_url = $arguments['response_url'];
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

    public function getResponseUrl(string $hash): string
    {
        $app_url = config('app.url');

        $app_url = rtrim((string) $app_url, '/');

        return $app_url.'/'.str_replace('{hash}', $hash, $this->response_url);
    }
}

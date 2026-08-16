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

    /**
     * Instance-level overrides for values the package used to read straight off
     * global config. Left null, each falls back to `config('parapos.*')`, so a
     * host that configures the package globally sees no change. Setting them
     * lets one process drive two independent profiles — e.g. a tenant context
     * and an admin context in the same worker — without mutating global config,
     * which would leak from one request into the next.
     */
    public ?string $response_url = null;

    /** @var class-string<\Illuminate\Database\Eloquent\Model>|null */
    public ?string $model = null;

    public ?string $tenant = null;

    public ?string $appUrl = null;

    /**
     * Whether `tenant` was passed to the constructor at all. `tenant` is the one
     * override whose null is meaningful — "this profile has no tenant segment" —
     * and a null property alone cannot be told apart from an absent one, so an
     * admin profile could never opt out of a globally configured tenant. The
     * other three read null as "not specified" and need no such flag.
     */
    private bool $tenantWasGiven = false;

    private function setApiUrl(?string $url = null): void
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
        if (isset($arguments['model'])) {
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
            $model = $arguments['model'];
            $this->model = $model;
        }
        if (array_key_exists('tenant', $arguments)) {
            $this->tenant = $arguments['tenant'];
            $this->tenantWasGiven = true;
        }
        if (isset($arguments['appUrl'])) {
            $this->appUrl = $arguments['appUrl'];
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

        $response_url = $this->response_url ?? config('parapos.response_url', 'parapos/response/{hash}/{tenant?}');

        $response_url = str_replace('{hash}', $hash, (string) $response_url);

        $tenant = $this->tenantWasGiven ? $this->tenant : config('parapos.tenant');

        if (! empty($tenant)) {
            $response_url = str_replace('{tenant?}', (string) $tenant, $response_url);
        } else {
            $response_url = str_replace('/{tenant?}', '', $response_url);
        }

        $app_url = $this->appUrl ?? config('app.url');

        $app_url = rtrim((string) $app_url, '/');

        return $app_url.'/'.$response_url;
    }
}

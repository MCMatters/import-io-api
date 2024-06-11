<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Http;

use McMatters\ImportIo\Utilities\Retry;
use McMatters\Ticl\Client as HttpClient;
use McMatters\Ticl\Http\Response;

use function explode;
use function json_decode;
use function simplexml_load_string;

use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_TIMEOUT;
use const null;
use const true;

class Client
{
    protected Client $httpClient;

    public function __construct(
        string $subDomain,
        string $apiKey,
        protected ?Retry $retry = null,
        array $httpClientOptions = [],
    ) {
        $this->httpClient = new HttpClient([
            'base_uri' => "https://{$subDomain}.import.io/",
            'query' => ['_apikey' => $apiKey],
        ] + $this->prepareHttpClientOptions($httpClientOptions));
    }

    public function request(string $method, string $uri, array $options = [])
    {
        if ($this->retry) {
            return $this->retry->run(function () use ($method, $uri, $options) {
                return $this->httpClient->{$method}($uri, $options);
            });
        }

        return $this->httpClient->{$method}($uri, $options);
    }

    public function get(
        string $uri,
        array $query = [],
        string $accept = 'json',
    ): array|string {
        return $this->parseResponse(
            $this->request('get', $uri, [
                'query' => $query,
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    public function post(
        string $uri,
        array|string|null $body = null,
        string $accept = 'json',
    ): array|string {
        return $this->parseResponse(
            $this->request('post', $uri, [
                'json' => $body ?? [],
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    public function put(
        string $uri,
        array|string|null $body = null,
        string $accept = 'json',
    ): array|string {
        return $this->parseResponse(
            $this->request('put', $uri, [
                'json' => $body ?? [],
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    public function patch(
        string $uri,
        array|string|null $body = null,
        string $accept = 'json',
    ): array|string {
        return $this->parseResponse(
            $this->request('patch', $uri, [
                'json' => $body ?? [],
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    public function delete(string $uri): int
    {
        return $this->request('delete', $uri)?->getStatusCode() ?? -1;
    }

    public function head(string $uri, array $options = []): Response
    {
        return $this->request('head', $uri, $options);
    }

    protected function prepareHttpClientOptions(array $httpClientOptions = []): array
    {
        $defaults = [
            'keep_alive' => true,
            'curl' => [
                CURLOPT_TIMEOUT => 120,
                CURLOPT_CONNECTTIMEOUT => 120,
            ],
        ];

        return $httpClientOptions + $defaults;
    }

    protected function getAcceptHeader(string $type = 'json'): string
    {
        $types = [
            'json' => 'application/json;charset=UTF-8',
            'jsonl' => 'application/json;charset=UTF-8',
            'xml' => 'application/xml',
            'zip' => 'application/zip',
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'plain' => 'text/plain',
        ];

        return $types[$type] ?? "application/{$type}";
    }

    protected function parseResponse(
        Response $response,
        string $accept = 'json',
    ): array|string {
        if ('json' === $accept) {
            return $response->json();
        }

        $content = $response->getBody();

        if ('jsonl' === $accept) {
            return $this->parseNdJson($content);
        }

        if ('xml' === $accept) {
            return (array) simplexml_load_string($content);
        }

        return $content;
    }

    protected function parseNdJson(string $response): array
    {
        $rows = [];

        foreach (explode("\n", $response) as $line) {
            if (empty($line)) {
                continue;
            }

            $rows[] = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        }

        return $rows;
    }
}

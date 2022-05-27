<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Http;

use InvalidArgumentException;
use McMatters\ImportIo\Utilities\Retry;
use McMatters\Ticl\Client as HttpClient;
use McMatters\Ticl\Http\Response;

use function explode;
use function json_decode;
use function in_array;
use function simplexml_load_string;
use function strtolower;

use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_TIMEOUT;
use const null;
use const true;

/**
 * Class Client
 *
 * @package McMatters\ImportIo\Http
 */
class Client
{
    /**
     * @var \McMatters\Ticl\Client
     */
    protected $httpClient;

    /**
     * @var \McMatters\ImportIo\Utilities\Retry|null
     */
    protected $retry;

    /**
     * Client constructor.
     *
     * @param string $subDomain
     * @param string $apiKey
     * @param \McMatters\ImportIo\Utilities\Retry|null $retry
     * @param array $httpClientOptions
     */
    public function __construct(
        string $subDomain,
        string $apiKey,
        Retry $retry = null,
        array $httpClientOptions = []
    ) {
        $this->httpClient = new HttpClient([
            'base_uri' => "https://{$subDomain}.import.io/",
            'query' => ['_apikey' => $apiKey],
        ] + $this->prepareHttpClientOptions($httpClientOptions));

        $this->retry = $retry;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function request(string $method, string $uri, array $options = [])
    {
        $methods = ['head', 'get', 'post', 'put', 'patch', 'delete'];

        $method = strtolower($method);

        if (!in_array($method, $methods, true)) {
            throw new InvalidArgumentException('Wrong method passed');
        }

        if ($this->retry) {
            return $this->retry->run(function () use ($method, $uri, $options) {
                return $this->httpClient->{$method}($uri, $options);
            });
        }

        return $this->httpClient->{$method}($uri, $options);
    }

    /**
     * @param string $uri
     * @param array $query
     * @param string $accept
     *
     * @return array|string
     *
     * @throws \Throwable
     */
    public function get(string $uri, array $query = [], string $accept = 'json')
    {
        return $this->parseResponse(
            $this->request('get', $uri, [
                'query' => $query,
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    /**
     * @param string $uri
     * @param array|string|null $body
     * @param string $accept
     *
     * @return array|string
     *
     * @throws \Throwable
     */
    public function post(string $uri, $body = null, string $accept = 'json')
    {
        return $this->parseResponse(
            $this->request('post', $uri, [
                'json' => $body ?? [],
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    /**
     * @param string $uri
     * @param array|string|null $body
     * @param string $accept
     *
     * @return array|string
     *
     * @throws \Throwable
     */
    public function put(string $uri, $body = null, string $accept = 'json')
    {
        return $this->parseResponse(
            $this->request('put', $uri, [
                'json' => $body ?? [],
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    /**
     * @param string $uri
     * @param mixed $body
     * @param string $accept
     *
     * @return array|string
     *
     * @throws \Throwable
     */
    public function patch(string $uri, $body = null, string $accept = 'json')
    {
        return $this->parseResponse(
            $this->request('patch', $uri, [
                'json' => $body ?? [],
                'headers' => ['Accept' => $this->getAcceptHeader($accept)],
            ]),
            $accept,
        );
    }

    /**
     * @param string $uri
     *
     * @return int
     *
     * @throws \Throwable
     */
    public function delete(string $uri): int
    {
        return $this->request('delete', $uri)->getStatusCode();
    }

    /**
     * @param string $uri
     * @param array $options
     *
     * @return \McMatters\Ticl\Http\Response
     *
     * @throws \Throwable
     */
    public function head(string $uri, array $options = []): Response
    {
        return $this->request('head', $uri, $options);
    }

    /**
     * @param array $httpClientOptions
     *
     * @return array
     */
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

    /**
     * @param string $type
     *
     * @return string
     */
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

    /**
     * @param \McMatters\Ticl\Http\Response $response
     * @param string $accept
     *
     * @return array|string
     *
     * @throws \McMatters\Ticl\Exceptions\JsonDecodingException
     */
    protected function parseResponse(
        Response $response,
        string $accept = 'json'
    ) {
        if ('json' === $accept) {
            return $response->json(true);
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

    /**
     * @param string $response
     *
     * @return array
     */
    protected function parseNdJson(string $response): array
    {
        $rows = [];

        foreach (explode("\n", $response) as $line) {
            if (empty($line)) {
                continue;
            }

            $rows[] = json_decode($line, true);
        }

        return $rows;
    }
}

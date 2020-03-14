<?php

declare(strict_types=1);

namespace McMatters\ImportIo\Http;

use InvalidArgumentException;
use McMatters\Ticl\Client as HttpClient;
use McMatters\Ticl\Http\Response;

use function explode, json_decode, in_array, simplexml_load_string, strtolower;

use const null, true;

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
     * Client constructor.
     *
     * @param string $subDomain
     * @param string $apiKey
     */
    public function __construct(string $subDomain, string $apiKey)
    {
        $this->httpClient = new HttpClient([
            'base_uri' => "https://{$subDomain}.import.io/",
            'query' => ['_apikey' => $apiKey],
            'keep_alive' => true,
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return mixed
     */
    public function request(string $method, string $uri, array $options = [])
    {
        $method = strtolower($method);

        if (!in_array($method, ['head', 'get', 'post', 'patch', 'delete'], true)) {
            throw new InvalidArgumentException('Wrong method passed');
        }

        return $this->httpClient->{$method}($uri, $options);
    }

    /**
     * @param string $uri
     * @param array $query
     * @param string $accept
     *
     * @return array|string
     */
    public function get(string $uri, array $query = [], string $accept = 'json')
    {
        return $this->parseResponse(
            $this->httpClient->get(
                $uri,
                [
                    'query' => $query,
                    'headers' => ['Accept' => $this->getAcceptHeader($accept)],
                ]
            ),
            $accept
        );
    }

    /**
     * @param string $uri
     * @param array|string|null $body
     * @param string $accept
     *
     * @return array|string
     */
    public function post(string $uri, $body = null, string $accept = 'json')
    {
        return $this->parseResponse(
            $this->httpClient->post(
                $uri,
                [
                    'json' => $body ?? [],
                    'headers' => ['Accept' => $this->getAcceptHeader($accept)],
                ]
            ),
            $accept
        );
    }

    /**
     * @param string $uri
     * @param array|string|null $body
     * @param string $accept
     *
     * @return array|string
     */
    public function put(string $uri, $body = null, string $accept = 'json')
    {
        return $this->parseResponse(
            $this->httpClient->put(
                $uri,
                [
                    'json' => $body ?? [],
                    'headers' => ['Accept' => $this->getAcceptHeader($accept)],
                ]
            ),
            $accept
        );
    }

    /**
     * @param string $uri
     * @param mixed $body
     * @param string $accept
     *
     * @return array|string
     */
    public function patch(string $uri, $body = null, string $accept = 'json')
    {
        return $this->parseResponse(
            $this->httpClient->patch(
                $uri,
                [
                    'json' => $body ?? [],
                    'headers' => ['Accept' => $this->getAcceptHeader($accept)],
                ]
            ),
            $accept
        );
    }

    /**
     * @param string $uri
     *
     * @return int
     */
    public function delete(string $uri): int
    {
        return $this->httpClient->delete($uri)->getStatusCode();
    }

    /**
     * @param string $uri
     * @param array $options
     *
     * @return \McMatters\Ticl\Http\Response
     */
    public function head(string $uri, array $options = []): Response
    {
        return $this->httpClient->head($uri, $options);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getAcceptHeader(string $type = 'json'): string
    {
        $types = [
            'json' => 'application/json',
            'jsonl' => 'application/json',
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

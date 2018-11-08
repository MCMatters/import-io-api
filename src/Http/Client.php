<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Http;

use McMatters\ImportIo\Exceptions\ImportIoException;
use McMatters\Ticl\Client as HttpClient;
use McMatters\Ticl\Http\Response;
use Throwable;
use const PHP_EOL;
use const null, true;
use function explode, json_decode, is_array, is_callable, simplexml_load_string;

/**
 * Class Client
 *
 * @package McMatters\ImportIo\Http
 */
class Client
{
    /**
     * @var HttpClient
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
        ]);
    }

    /**
     * @param string $uri
     * @param array $query
     * @param string $accept
     *
     * @return mixed
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    public function get(string $uri, array $query = [], string $accept = 'json')
    {
        try {
            return $this->parseResponse(
                $this->httpClient->get(
                    $uri,
                    [
                        'query' => $query,
                        'headers' => [
                            'Accept' => $this->getAcceptHeader($accept),
                        ],
                    ]
                ),
                $accept
            );
        } catch (Throwable $e) {
            throw new ImportIoException(
                $this->getExceptionMessage($e),
                (int) $e->getCode()
            );
        }
    }

    /**
     * @param string $uri
     * @param array|string|null $body
     * @param string $accept
     *
     * @return mixed
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    public function post(string $uri, $body = null, string $accept = 'json')
    {
        try {
            return $this->parseResponse(
                $this->httpClient->post(
                    $uri,
                    [
                        'json' => $body ?? [],
                        'headers' => [
                            'Accept' => $this->getAcceptHeader($accept),
                        ],
                    ]
                ),
                $accept
            );
        } catch (Throwable $e) {
            throw new ImportIoException(
                $this->getExceptionMessage($e),
                (int) $e->getCode()
            );
        }
    }

    /**
     * @param string $uri
     * @param array|string|null $body
     * @param string $accept
     *
     * @return mixed
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    public function put(string $uri, $body = null, string $accept = 'json')
    {
        try {
            return $this->parseResponse(
                $this->httpClient->put(
                    $uri,
                    [
                        'json' => $body ?? [],
                        'headers' => [
                            'Accept' => $this->getAcceptHeader($accept),
                        ],
                    ]
                ),
                $accept
            );
        } catch (Throwable $e) {
            throw new ImportIoException(
                $this->getExceptionMessage($e),
                (int) $e->getCode()
            );
        }
    }

    /**
     * @param string $uri
     * @param mixed $body
     * @param string $accept
     *
     * @return mixed
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    public function patch(string $uri, $body = null, string $accept = 'json')
    {
        try {
            return $this->parseResponse(
                $this->httpClient->patch(
                    $uri,
                    [
                        'json' => $body ?? [],
                        'headers' => [
                            'Accept' => $this->getAcceptHeader($accept),
                        ],
                    ]
                ),
                $accept
            );
        } catch (Throwable $e) {
            throw new ImportIoException(
                $this->getExceptionMessage($e),
                (int) $e->getCode()
            );
        }
    }

    /**
     * @param string $uri
     *
     * @return int
     * @throws \McMatters\ImportIo\Exceptions\ImportIoException
     */
    public function delete(string $uri): int
    {
        try {
            return $this->httpClient->delete($uri)->getStatusCode();
        } catch (Throwable $e) {
            throw new ImportIoException(
                $this->getExceptionMessage($e),
                (int) $e->getCode()
            );
        }
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
     * @param Response $response
     * @param string $accept
     *
     * @return mixed
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
        $parsed = [];
        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $parsed[] = json_decode($line, true);
        }

        return $parsed;
    }

    /**
     * @param Throwable $e
     *
     * @return string
     */
    protected function getExceptionMessage(Throwable $e): string
    {
        $message = '';

        if (is_callable([$e, 'asJson'])) {
            try {
                $json = $e->asJson();

                if (is_array($json)) {
                    if (isset($json['message'])) {
                        $message = "Message: {$json['message']}";
                    }

                    if (isset($json['details'])) {
                        $message .= PHP_EOL."Details: {$json['details']}";
                    }
                }
            } catch (Throwable $x) {
                $message = $e->getMessage();
            }
        }

        return $message ?: $e->getMessage();
    }
}

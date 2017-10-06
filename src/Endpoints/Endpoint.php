<?php

declare(strict_types = 1);

namespace McMatters\ImportIo\Endpoints;

use GuzzleHttp\Client;
use InvalidArgumentException;
use McMatters\ImportIo\Exceptions\ImportIoException;
use Throwable;
use const null, true;
use function array_key_exists, array_merge_recursive, explode, json_decode,
    is_array, is_callable, simplexml_load_string, trim;

/**
 * Class Endpoint
 *
 * @package McMatters\ImportIo\Endpoints
 */
abstract class Endpoint
{
    /**
     * @var string
     */
    protected $baseUrl = 'import.io';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * Endpoint constructor.
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->httpClient = new Client([
            'base_uri'       => $this->getBaseUrl(),
            'query'          => ['_apikey' => $apiKey],
            'decode_content' => 'gzip',
        ]);
    }

    /**
     * @param string $uri
     * @param array $options
     * @param string $accept
     *
     * @return array
     * @throws ImportIoException
     */
    protected function requestGet(
        string $uri,
        array $options = [],
        string $accept = 'json'
    ): array {
        try {
            $request = $this->httpClient->get(
                $uri,
                $this->getRequestOptions($options, $accept)
            );

            $content = $request->getBody()->getContents();

            return [
                'content' => $this->parseResponseContent($content, $accept),
                'headers' => $request->getHeaders(),
                'code'    => $request->getStatusCode(),
            ];
        } catch (Throwable $e) {
            throw new ImportIoException($this->getErrorMessage($e));
        }
    }

    /**
     * @param string $uri
     * @param mixed $body
     * @param array $options
     * @param string $accept
     *
     * @return array
     * @throws ImportIoException
     */
    protected function requestPost(
        string $uri,
        $body = null,
        array $options = [],
        string $accept = 'json'
    ): array {
        try {
            $request = $this->httpClient->post(
                $uri,
                $this->getPostOptions($body, $options, $accept)
            );

            $content = $request->getBody()->getContents();

            return [
                'content' => $this->parseResponseContent($content, $accept),
                'headers' => $request->getHeaders(),
                'code'    => $request->getStatusCode(),
            ];
        } catch (Throwable $e) {
            throw new ImportIoException($this->getErrorMessage($e));
        }
    }

    /**
     * @param string $uri
     * @param mixed $body
     * @param array $options
     * @param string $accept
     *
     * @return array
     * @throws ImportIoException
     */
    protected function requestPut(
        string $uri,
        $body = null,
        array $options = [],
        string $accept = 'json'
    ): array {
        try {
            $request = $this->httpClient->put(
                $uri,
                $this->getPostOptions($body, $options, $accept)
            );

            $content = $request->getBody()->getContents();

            return [
                'content' => $this->parseResponseContent($content, $accept),
                'headers' => $request->getHeaders(),
                'code'    => $request->getStatusCode(),
            ];
        } catch (Throwable $e) {
            throw new ImportIoException($this->getErrorMessage($e));
        }
    }

    /**
     * @param string $uri
     * @param array $options
     *
     * @return int
     * @throws ImportIoException
     */
    protected function requestDelete(string $uri, array $options = []): int
    {
        try {
            return $this->httpClient->delete($uri, $options)->getStatusCode();
        } catch (Throwable $e) {
            throw new ImportIoException($this->getErrorMessage($e));
        }
    }

    /**
     * @param string $content
     * @param string $accept
     *
     * @return mixed
     */
    protected function parseResponseContent(
        string $content,
        string $accept = 'json'
    ) {
        if (trim($content) === '') {
            return null;
        }

        switch ($accept) {
            case 'json':
                return json_decode($content, true);

            case 'jsonl':
                return $this->parseJsonL($content);

            case 'xml':
                return (array) simplexml_load_string($content);

            case 'csv':
            default:
                return $content;
        }
    }

    /**
     * @param string $response
     *
     * @return array
     */
    protected function parseJsonL(string $response): array
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
    protected function getErrorMessage(Throwable $e): string
    {
        $message = '';

        if (is_callable([$e, 'getResponse'])) {
            $response = $e->getResponse();

            try {
                $content = $response->getBody()->getContents();

                $json = json_decode($content, true);
                $message = $content;

                if (is_array($json) && array_key_exists('message', $json)) {
                    $message = $json['message'];
                }
            } catch (Throwable $x) {
                $message = $e->getMessage();
            }
        }

        return $message ?: $e->getMessage();
    }

    /**
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $prefix = isset($this->subDomain)
            ? "{$this->subDomain}."
            : '';

        return "https://{$prefix}{$this->baseUrl}";
    }

    /**
     * @param array $options
     * @param string $accept
     *
     * @return array
     */
    protected function getRequestOptions(
        array $options = [],
        string $accept = 'json'
    ): array {
        return array_merge_recursive(
            [
                'headers' => ['Accept' => $this->getContentType($accept)],
                'query'   => $this->httpClient->getConfig('query'),
            ],
            $options
        );
    }

    /**
     * @param mixed $body
     * @param array $options
     * @param string $accept
     *
     * @return array
     */
    protected function getPostOptions(
        $body = null,
        array $options = [],
        string $accept = 'json'
    ): array {
        return array_merge_recursive(
            [
                'json'    => $body,
                'headers' => [
                    'Content-Type' => $this->getContentType($accept),
                ],
            ],
            $this->getRequestOptions($options, $accept)
        );
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getContentType(string $type = 'json'): string
    {
        $types = [
            'json'  => 'application/json',
            'jsonl' => 'application/json',
            'xml'   => 'application/xml',
            'csv'   => 'text/csv',
            'plain' => 'text/plain',
        ];

        return $types[$type] ?? "application/{$type}";
    }

    /**
     * @param string $extractorId
     *
     * @throws InvalidArgumentException
     */
    protected function checkExtractorId(string $extractorId)
    {
        $this->checkUuid($extractorId, 'extractorId');
    }

    /**
     * @param string $crawlRunId
     *
     * @throws InvalidArgumentException
     */
    protected function checkCrawlRunId(string $crawlRunId)
    {
        $this->checkUuid($crawlRunId, 'crawlRunId');
    }

    /**
     * @param string $attachmentId
     *
     * @throws InvalidArgumentException
     */
    protected function checkAttachmentId(string $attachmentId)
    {
        $this->checkUuid($attachmentId, 'attachmentId');
    }

    /**
     * @param string $uuid
     * @param string $name
     *
     * @throws InvalidArgumentException
     */
    protected function checkUuid(string $uuid, string $name)
    {
        $check = preg_match(
            '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/',
            $uuid
        );

        if (!$check) {
            throw new InvalidArgumentException("Invalid {$name} was passed");
        }
    }
}

<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Http;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class LoggingAwareHttpClient implements HttpClientInterface, LoggerAwareInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {}

    /**
     * @param mixed[] $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            $this->logger->info('HTTP Request and Response', [
                'request' => [
                    'method' => $method,
                    'url' => $url,
                    'options' => $options,
                ],
                'response' => [
                    'statusCode' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'content' => $response->getContent(false),
                ],
            ]);

            return $response;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('HTTP Request failed', [
                'method' => $method,
                'url' => $url,
                'options' => $options,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream($responses, $timeout);
    }

    /**
     * @param mixed[] $options
     */
    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->httpClient = $this->httpClient->withOptions($options);

        return $clone;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}

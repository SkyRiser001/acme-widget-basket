<?php

declare(strict_types=1);

namespace Acme\Http;

use Acme\SalesConfig;
use Acme\UnknownProductException;
use InvalidArgumentException;
use JsonException;
use Throwable;

final readonly class Kernel
{
    public function __construct(
        private SalesConfig $config,
        private QuotePresenter $presenter = new QuotePresenter(),
    ) {
    }

    public function handle(string $method, string $uri, string $body): JsonResponse
    {
        if (strtoupper($method) === 'OPTIONS') {
            return new JsonResponse(204, null);
        }

        try {
            return $this->dispatch(strtoupper($method), $this->path($uri), $body);
        } catch (UnknownProductException $exception) {
            return JsonResponse::error(422, $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            return JsonResponse::error(400, $exception->getMessage());
        } catch (Throwable $exception) {
            error_log($exception->__toString());

            return JsonResponse::error(500, 'The basket could not be priced.');
        }
    }

    private function dispatch(string $method, string $path, string $body): JsonResponse
    {
        if ($path === '/') {
            if ($method !== 'GET') {
                return JsonResponse::error(405, 'Use GET for the service index.');
            }

            return new JsonResponse(200, [
                'service' => 'Acme Widget Co',
                'catalogue' => '/api/catalogue',
                'basket' => '/api/basket',
            ]);
        }

        if ($path === '/api/catalogue') {
            if ($method !== 'GET') {
                return JsonResponse::error(405, 'Use GET to read the catalogue.');
            }

            return new JsonResponse(200, $this->presenter->catalogue($this->config));
        }

        if ($path === '/api/basket') {
            if ($method !== 'POST') {
                return JsonResponse::error(405, 'Use POST to price a basket.');
            }

            return $this->quote($body);
        }

        return JsonResponse::error(404, 'Not found.');
    }

    private function quote(string $body): JsonResponse
    {
        try {
            /** @var mixed $payload */
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return JsonResponse::error(400, 'The basket request must be JSON.');
        }

        if (
            !is_array($payload)
            || !isset($payload['items'])
            || !is_array($payload['items'])
            || !array_is_list($payload['items'])
        ) {
            return JsonResponse::error(400, 'The basket request must include an items array of product codes.');
        }

        $basket = $this->config->basket();

        foreach ($payload['items'] as $code) {
            if (!is_string($code) || trim($code) === '') {
                return JsonResponse::error(400, 'Each item must be a product code.');
            }

            $basket->add($code);
        }

        return new JsonResponse(200, $this->presenter->quote($basket->summary()));
    }

    private function path(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }
}

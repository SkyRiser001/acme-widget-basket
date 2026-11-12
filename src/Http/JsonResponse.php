<?php

declare(strict_types=1);

namespace Acme\Http;

use JsonException;

final readonly class JsonResponse
{
    public function __construct(
        public int $status,
        public mixed $data,
    ) {
    }

    public static function error(int $status, string $message): self
    {
        return new self($status, ['error' => $message]);
    }

    /**
     * @throws JsonException
     */
    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if ($this->data === null) {
            return;
        }

        echo json_encode(
            $this->data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}

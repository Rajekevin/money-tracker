<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Reads a JSON body and gives typed access to its fields.
 * Amounts are accepted as numbers or strings and passed on as strings, never as floats.
 */
final readonly class JsonPayload
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(private array $data)
    {
    }

    public static function from(Request $request): self
    {
        try {
            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR | \JSON_BIGINT_AS_STRING);
        } catch (\JsonException) {
            throw new BadRequestHttpException('The request body must be valid JSON.');
        }

        if (!\is_array($data)) {
            throw new BadRequestHttpException('The request body must be a JSON object.');
        }

        /** @var array<string, mixed> $fields */
        $fields = $data;

        return new self($fields);
    }

    public function string(string $field): string
    {
        $value = $this->data[$field] ?? null;

        if (\is_int($value)) {
            return (string) $value;
        }

        if (!\is_string($value)) {
            throw new BadRequestHttpException(sprintf('The "%s" field is required and must be a string.', $field));
        }

        return $value;
    }
}

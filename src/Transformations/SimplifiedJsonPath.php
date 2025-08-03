<?php

declare(strict_types=1);

namespace Grano22\TestKit\Transformations;

use InvalidArgumentException;

class SimplifiedJsonPath
{
    private const TOKEN_ARRAY_KEY_OPEN = '[';
    private const TOKEN_ARRAY_KEY_CLOSE = ']';
    private const TOKEN_STRING = '"';
    private const TOKEN_WILDCARD = '*';
    private const TOKEN_SEPARATOR = '.';
    private const TOKEN_STRING_ESCAPE = '\\';
    private const FIRST_TOKEN_ROOT = '$';

    public static function fromString(string $input): self
    {
        if (!self::validate($input)) {
            throw new InvalidArgumentException("Cannot create simplified json path from invalid input - $input");
        }

        return new self($input);
    }

    private function __construct(private readonly string $path)
    {
    }

    /** @return array<non-empty-string|int> */
    public function toSegments(): array
    {
        $segments = [];
        $nextSegment = '';

        for ($i = 0; $i < strlen($this->path); $i++) {
            $char = $this->path[$i];

            if (
                ($char === self::FIRST_TOKEN_ROOT && $i === 0) ||
                $char === self::TOKEN_STRING_ESCAPE ||
                ($char === self::TOKEN_STRING && $this->path[$i - 1] !== self::TOKEN_STRING_ESCAPE)
            ) {
                continue;
            }

            if ($char === self::TOKEN_ARRAY_KEY_OPEN || $char === self::TOKEN_ARRAY_KEY_CLOSE || $char === '.') {
                if ($nextSegment) {
                    $segments[] = $nextSegment !== '*' && $char === self::TOKEN_ARRAY_KEY_CLOSE ? (int)$nextSegment : $nextSegment;
                    $nextSegment = '';
                }

                continue;
            }

            $nextSegment .= $char;

            if ($i === strlen($this->path) - 1) {
                $segments[] = $nextSegment;
            }
        }

        return $segments;
    }

    /**
     * @phpstan-pure
     * @psalm-pure
     */
    public static function validate(string $potentialJsonPath): bool
    {
        // TODO Refactor to make it more performant using data structures and patterns
        if ($potentialJsonPath === self::FIRST_TOKEN_ROOT || $potentialJsonPath === self::TOKEN_SEPARATOR) {
            return true;
        }

        $inputSize = strlen($potentialJsonPath);
        if (!$inputSize) {
            return false;
        }

        $startPointer = 0;
        if ($potentialJsonPath[0] === self::FIRST_TOKEN_ROOT) {
            $startPointer++;
        }

        if (!in_array($potentialJsonPath[$startPointer], ['[', '.'], true)) {
            return false;
        }

        $inTheParentheses = false;
        $inProperty = false;
        $inQuotes = false;
        for ($i = $startPointer; $i < $inputSize; $i++) {
            $char = $potentialJsonPath[$i];

            if ($char === self::TOKEN_STRING && !$inTheParentheses) {
                if ($inQuotes && $potentialJsonPath[$i - 1] !== self::TOKEN_STRING_ESCAPE) {
                    $inQuotes = false;
                    $inProperty = false;

                    continue;
                }

                if ($potentialJsonPath[$i - 1] === self::TOKEN_STRING) {
                    return false;
                }

                //$inProperty = false;
                $inQuotes = true;

                continue;
            }

            if ($inQuotes) {
                continue;
            }

            if ($inTheParentheses && $char === self::TOKEN_WILDCARD) {
                if (
                    $potentialJsonPath[$i - 1] !== self::TOKEN_ARRAY_KEY_OPEN ||
                    $i >= $inputSize - 1 ||
                    $potentialJsonPath[$i + 1] !== self::TOKEN_ARRAY_KEY_CLOSE
                ) {
                    return false;
                }

                continue;
            }

            if ($inProperty && $char === self::TOKEN_WILDCARD) {
                if ($potentialJsonPath[$i - 1] !== self::TOKEN_SEPARATOR) {
                    return false;
                }

                $inProperty = false;

                continue;
            }

            if ($char === self::TOKEN_ARRAY_KEY_CLOSE) {
                if (!$inTheParentheses || $potentialJsonPath[$i - 1] === self::TOKEN_ARRAY_KEY_OPEN) {
                    return false;
                }

                $inTheParentheses = false;
                continue;
            }

            if ($char === self::TOKEN_ARRAY_KEY_OPEN) {
                if ($inProperty && $potentialJsonPath[$i - 1] === self::TOKEN_SEPARATOR) {
                    return false;
                }

                if ($inTheParentheses) {
                    return false;
                }

                $inProperty = false;
                $inTheParentheses = true;

                continue;
            }

            if ($inTheParentheses && !ctype_digit($char)) {
                return false;
            }

            if (!$inTheParentheses && $char === self::TOKEN_SEPARATOR) {
                $inProperty = true;

                continue;
            }

            if (!ctype_alnum($char) || (!$inProperty && !$inTheParentheses)) {
                return false;
            }
        }

        return !($inTheParentheses || $inQuotes || $potentialJsonPath[$inputSize - 1] === self::TOKEN_SEPARATOR);
    }
}

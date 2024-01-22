<?php

namespace Blu3\DynamoDB\Traits;

use Carbon\CarbonImmutable;
use RuntimeException;

trait HasAttributes {
    protected array $attributes = [];
    protected array $original = [];
    protected array $casts = [];

    protected string $dateFormat = 'Y-m-d H:i:s';

    public function setOriginalAttribute(string $key, string $value): void {
        if (isset($this->casts[$key])){
            $value = $this->uncastAttribute($this->casts[$key], $value);
        }

        $this->original[$key] = $value;
    }

    public function setAttribute(string $key, string $value): void {
        if (isset($this->casts[$key])){
            $value = $this->uncastAttribute($this->casts[$key], $value);
        }

        $this->attributes[$key] = $value;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function isDirty(): bool {
        foreach ($this->attributes as $key => $value) {
            if (!isset($this->original[$key]) || $this->original[$key] !== $value){
                return true;
            }
        }

        return false;
    }

    public function hasAttribute(string $key): bool {
        return array_key_exists($key, $this->attributes);
    }

    public function getAttribute(string $key): mixed {
        if (!isset($this->attributes[$key])){
            throw new RuntimeException("Attribute [{$key}] not found in " . static::class);
        }

        $attribute = $this->attributes[$key];

        if (isset($this->casts[$key])){
            return $this->castAttribute($this->casts[$key], $attribute);
        }

        return $attribute;
    }

    private function castAttribute(string $key, string $value): mixed {
        return match ($key) {
            'datetime' => CarbonImmutable::parse($value),
            'array' => json_decode($value, true, flags: JSON_THROW_ON_ERROR),
            default => throw new RuntimeException("Unknown cast '{$key}'"),
        };
    }

    private function uncastAttribute(string $key, mixed $value): mixed {
        return match ($key) {
            'datetime' => CarbonImmutable::parse($value)->format($this->dateFormat),
            'array' => json_encode($value),
            default => $value
        };
    }
}
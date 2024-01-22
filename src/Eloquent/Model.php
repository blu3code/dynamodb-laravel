<?php

namespace Blu3\DynamoDB\Eloquent;

use Blu3\DynamoDB\Traits\HasAttributes;
use Illuminate\Support\Str;
use RuntimeException;

abstract class Model {
    use HasAttributes;

    public bool $exists = false;

    protected string $table;
    protected string $primaryKey = 'id';

    private bool $consistentRead = false;

    public function getConsistentRead(): bool {
        return $this->consistentRead;
    }

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    public function initialize(array $attributes): static {
        $this->exists = true;

        foreach ($attributes as $key => $value) {
            $this->setOriginalAttribute($key, $value);
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public static function create(array $attributes): static {
        $model = new static;
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public static function query(): Query {
        return new Query(static::class);
    }

    public static function withConsistentRead(bool $consistentRead = true): Query {
        return static::query()->withConsistentRead($consistentRead);
    }

    public static function find(string $key): ?static {
        return static::query()->find($key);
    }

    public static function findOrFail(string $key): static {
        return static::query()->findOrFail($key);
    }

    public static function findOr(string $key, mixed $default): mixed {
        return static::query()->findOr($key, $default);
    }

    public function __get(string $attribute): mixed {
        return $this->getAttribute($attribute);
    }

    public function __set(string $name, mixed $value): void {
        $this->setAttribute($name, $value);
    }

    public function getTable(): string {
        return $this->table ?? Str::snake(Str::pluralStudly(class_basename($this)));
    }

    public function getPrimaryKey(): string {
        return $this->primaryKey;
    }

    public function fill(array $attributes): static {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    public function update(array $attributes): static {
        return $this->fill($attributes)->save();
    }

    public function save(): static {
        if (!$this->isDirty()){
            return $this;
        }

        if (!$this->hasAttribute($this->getPrimaryKey())){
            throw new RuntimeException("Model " . static::class . " doesn't have primary key");
        }

        Repository::save($this);

        return $this;
    }
}
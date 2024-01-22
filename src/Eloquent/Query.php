<?php

namespace Blu3\DynamoDB\Eloquent;

use RuntimeException;

class Query {
    private string $className;
    private bool $consistentRead;

    public function __construct(string $className) {
        $this->className = $className;
    }

    public function withConsistentRead(bool $consistentRead = true): static {
        $this->consistentRead = $consistentRead;

        return $this;
    }

    public function find(string $key): ?Model {
        /** @var Model $model */
        $model = new $this->className;

        $response = Repository::get([
            'TableName' => $model->getTable(),
            'ConsistentRead' => $this->consistentRead ?? $model->getConsistentRead(),
            'Key' => [$model->getPrimaryKey() => ['S' => $key]]
        ]);

        if (is_null($response)){
            return null;
        }

        $attributes = [];

        foreach ($response as $key => $value) {
            if (!isset($value['S'])){
                continue;
            }

            $attributes[$key] = $value['S'];
        }

        if (empty($attributes)){
            throw new RuntimeException("Model initialization failed");
        }

        $model->initialize($attributes);

        return $model;
    }

    public function findOrFail(string $key): Model {
        $model = $this->find($key);

        if (is_null($model)){
            throw new RuntimeException("Model [{$this->className}] record not found");
        }

        return $model;
    }

    public function findOr(string $key, mixed $default): mixed {
        $model = $this->find($key);

        if (is_null($model)){
            return value($default);
        }

        return $model;
    }
}
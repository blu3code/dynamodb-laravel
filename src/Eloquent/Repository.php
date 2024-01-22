<?php

namespace Blu3\DynamoDB\Eloquent;

use RuntimeException;

class Repository {
    public static function get(array $properties): ?array {
        $response = Connection::get()->getItem([
            'TableName' => $properties['TableName'],
            'ConsistentRead' => $properties['ConsistentRead'],
            'Key' => $properties['Key']
        ]);

        if (empty($response['Item'])){
            return null;
        }

        return $response['Item'];
    }

    public static function save(Model $model): void {
        $pkName = $model->getPrimaryKey();

        $response = Connection::get()->putItem([
            'TableName' => $model->getTable(),
            'ConditionExpression' => $model->exists ? "attribute_exists({$pkName})" : "attribute_not_exists({$pkName})",
            'Item' => array_map(fn(string $value) => ['S' => $value], $model->getAttributes())
        ]);

        if ($response->get('@metadata')['statusCode'] !== 200){
            throw new RuntimeException("Unexpected technical error during model save");
        }
    }
}
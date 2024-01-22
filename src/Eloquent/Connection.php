<?php

namespace Blu3\DynamoDB\Eloquent;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\Config;

class Connection {
    public static function get(): DynamoDbClient {
        $configuration = Config::get('database.dynamodb');

        return new DynamoDbClient([
            'version' => 'latest',
            'region' => $configuration['region'],
            'credentials' => [
                'key' => $configuration['key'],
                'secret' => $configuration['secret']
            ],
            'endpoint' => $configuration['endpoint'] ?? null
        ]);
    }
}
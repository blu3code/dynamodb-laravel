# DynamoDB Laravel Adapter

AWS DynamoDB Eloquent ORM Driver

## Prerequisites

- PHP 8.3
- Laravel 10+

## Installation

**1.**  Install via composer

```sh
$ composer require blu3code/dynamodb-laravel
```

**2.** Patch DynamoDB config file (config/database.php)

```php
'dynamodb' => [
    'key' => env('DYNAMODB_KEY_ID'),
    'secret' => env('DYNAMODB_ACCESS_KEY'),
    'region' => env('DYNAMODB_REGION'),
    'endpoint' => env('DYNAMODB_ENDPOINT'),
]
```

Modify your environment:

```dotenv
DYNAMODB_KEY_ID={AWS IAM Key ID}
DYNAMODB_ACCESS_KEY={AWS IAM Access Key}
DYNAMODB_REGION={AWS Region code}
# only for dynamodb-local
DYNAMODB_ENDPOINT=http://127.0.0.1:8080
```

## Usage and examples

Model structure

```php
namespace App\Models\DynamoDB;

use Blu3\DynamoDB\Eloquent\Model;

class User extends Model {
    // Not necessary. Can be obtained from class name MyTable => my_tables
    protected string $table = 'users';
}
```

Find model instance and update attributes

```php
use App\Models\DynamoDB\User;

$user = User::findOrFail('0e00232f-c55d-4879-bb31-236f2eea82f9');
$user->update([
    'filename' => '1234.jpg' 
]);
```

Create new model

```php
use App\Models\DynamoDB\User;

$user = new User();
$user->fill([
    'id' => str()->uuid()->toString(),
    'name' => 'Alex'
]);
$user->save();
```

Alternative way to create model

```php
use App\Models\DynamoDB\User;

$user = User::create([
    'id' => str()->uuid()->toString(),
    'name' => 'Alex'
]);
```

Attribute casts

```php
namespace App\Models\DynamoDB;

use Blu3\DynamoDB\Eloquent\Model;

class User extends Model {
    protected string $dateFormat = 'Y-m-d H:i:s.v';
    protected array $casts = [
        'roles' => 'array',
        'created_at' => 'datetime',
    ];
}
```

```php
use App\Models\DynamoDB\User;
use Carbon\CarbonImmutable;

$user = new User();
$user->fill([
    'id' => str()->uuid()->toString(),
    'name' => 'Alex',
    'roles' => ['admin', 'manager'],
    'created_at' => CarbonImmutable::now()
]);
$user->save();
```

## Advanced usage

### Find model using strong consistent read

```php
use App\Models\DynamoDB\User;

$user = User::query()->withConsistentRead(true)->findOrFail($id);
```

### Always find model using strong consistent read

```php
namespace App\Models;

use Blu3\DynamoDB\Eloquent\Model;

class User extends Model {
    private bool $consistentRead = true;
}
```
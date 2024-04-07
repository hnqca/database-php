# Database PHP

Seu objetivo é facilitar e garantir a segurança na execução de operações no banco de dados (MySQL) usando PDO com prepared statements.

- Aplica a sanitização e preparação de dados para prevenir possíveis vulnerabilidades como XSS e SQL Injection.
- Segue uma abordagem semântica.
- Permite trabalhar com vários bancos de dados de forma simples.
- Com apenas algumas linhas de código, você pode realizar operações do tipo "CRUD".  Cadastrar, ler, atualizar e excluir registros facilmente.

***

## Instalação:

via [composer](https://getcomposer.org/):
```bash
composer require hnqca/database-php
```

***

## Exemplo:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Hnqca\Database\Connection;
use Hnqca\Database\Database;

/**
 * Configures the database connection data:
 */
$connection = new Connection([
    'driver'     => 'mysql',
    'host'       => 'localhost',
    'name'       => 'your_database',
    'user'       => 'root',
    'pass'       => '',
    'port'       => '3306',
    'charset'    => 'utf8'
]);

try {

    /**
     * Initializes the database connection:
     */
    $database = new Database($connection);

    /** 
     * Handles the table in the currently set database during class instantiation:
     */
    $results = $database->from('users')->select(true);

    /**
     * Displays the results found in the table:
     */
    echo '<pre>';
    print_r($results);


    /***
     * Checks if any error occurred during the process:
     */
} catch (\Exception $e) {
    die('error: ' . $e->getMessage()); // Displays the reason for the error.
}
```

***

## Conexão:

```php
use Hnqca\Database\Connection;
use Hnqca\Database\Database;

/**
 * Configures the database connection data:
 */
$connection = new Connection([
    'driver'     => 'mysql',
    'host'       => 'localhost',
    'name'       => 'your_database',
    'user'       => 'root',
    'pass'       => 'password',
    'port'       => '3306',
    'charset'    => 'utf8'
]);

/**
 * Initializes the database connection:
 */
$database = new Database($connection);
```

***

## Select:

```php
/**
 * Selecting multiple records:
 */
$users = $database->from('users')->select(true);

/**
 * Limiting the columns to be selected:
 */
$users = $database->from('users')->select(true, ['id', 'age']);

/**
 * Limiting the number of records to be selected:
 */
$users = $database->from('users')->limit(30)->select(true);

/**
 * Selecting data from a specific user:
 */
$userId = 24;
$user = $database->from("users")->where("id = {$userId}")->select();

/**
 * Selecting multiple records with one or more conditions:
 */
$name  = "John";
$users = $database->from('users')->where("first_name = {$name}, age >= 18")->select(true);

/**
* You can also use "ORDER BY"
*/
$users = $database->from("users")->where("first_name = {$name}, age >= 18")->orderBy(['age' => 'DESC'])->select(true);


/**
 * Displaying the obtained data:
 */

echo "Total users found: " . count($users);

foreach ($users as $user) {
    echo "Id:   {$user->id}"                            . '<br/>';
    echo "Name: {$user->first_name} {$user->last_name}" . '<br/>';
    echo '<hr />';
    // ...
}
```

***

## LIMIT, OFFSET and "PAGE":

#### LIMIT:

```php
$results = $database->from("products")->limit(2)->select(true);
```

***

#### LIMIT and OFFSET:

O método ``offset`` é frequentemente combinado com o método ``limit`` para permitir a paginação dos resultados.

```php
$results = $database->from("products")->limit(2)->offset(0)->select(true);

$results = $database->from("products")->limit(2)->offset(2)->select(true);
```

***

#### LIMIT and "PAGE":

``page`` é uma abordagem alternativa para implementar a paginação dos resultados de uma forma mais fácil.

Aceitando o tamanho da página **(o número de registros por página)** e o número da página desejada.

Ela calculará automaticamente o deslocamento apropriado. 

```php
$result = $database->from("products")->limit(limit: 2, page: 1)->select(true);

$result = $database->from("products")->limit(limit: 2, page: 2)->select(true);

```

***

## Insert:

```php
$userId = $database->from('users')->insert([
    'first_name' => "John",
    'last_name'  => "Doe",
    'email'      => "user@example.com",
    'age'        => 32
    // ...
]);

if (!$userId) {
    die ("Unable to register the user in the database.");
}

echo "User registered successfully! ID: {$userId}";
```

***

## Update:

```php
$database->from('users')->where("id = 123")->update([
    'email' => "new.email@example.com"
    // ...
]);
```

***

## Delete:

```php
/**
 * Delete specific records:
 */
$database->from('users')->where("id = 123")->delete();

/**
 * Delete all records from the table. (be careful):
 */
$database->from('users')->delete();
```

***

## Aggregations:

#### COUNT:

```php
/**
 * Counting the number of records:
 */
$totalUsers = $database->from("users")->count();
```

#### SUM:

```php
 /**
  * Summing up some value from the column:
  */
$totalPrice = $database->from("products")->sum("price");
```

#### AVG:

```php
/**
 * To calculate the average of values in a numeric column of a table.
 */
$averageAge = $database->from("users")->avg("age");
```

#### MIN:

```php
/**
 * Finding the lowest value in the column:
 */
$lowestAge = $database->from("users")->min("age");
```

#### MAX:

```php
/**
 * Finding the highest value in the column:
 */
$highestAge = $database->from("users")->max("age");
```

###
<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
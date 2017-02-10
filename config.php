<?php 

// put header to plain text to make the API results easier to read
header('Content-Type:text/plain');

// define the database variables
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'USERNAME');
define('DB_PASSWORD', 'PASSWORD');
define('DB_DATABASE', 'football');
define('DB_PORT', 'PORT');

// connect to the database
try {
    $db = new PDO('mysql:host='.DB_HOSTNAME.';dbname='.DB_DATABASE.';charset=utf8', DB_USERNAME, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}

// get API KEY
$query = $db->query("SELECT * FROM settings WHERE name = 'API'");
$API = $query->fetch();

// SET API key to variable API
$API = $API['value'];

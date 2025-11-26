<?php

//define('DEBUG', true); // à enlever qd tt ok
session_start();

//echo "<div style='background:yellow;padding:10px;'>SESSION DEBUG: " . (isset($_SESSION['user']) ? "✅ CONNECTÉ - " . $_SESSION['user']['firstname'] : "❌ NON CONNECTÉ") . "</div>";
//var_dump($_SESSION);
//$_SESSION['user']

// charge l'autoload de composer
require __DIR__ . '/../vendor/autoload.php';

// charge le contenu du .env dans $_ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ .'/..');
$dotenv->load();
require(__DIR__ .'/../core/Router.php');
$router = new Router();

$router->handleRequest($_GET);

?>
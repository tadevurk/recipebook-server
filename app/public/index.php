<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

$router->setNamespace('Controllers');


// routes for the recipes endpoint
$router->get('/recipes', 'RecipeController@getAll');
$router->get('/recipes/(\d+)', 'RecipeController@getOne');
$router->post('/recipes', 'RecipeController@create');
$router->put('/recipes/(\d+)', 'RecipeController@update');
$router->delete('/recipes/(\d+)', 'RecipeController@delete');
$router->post('/recipes/autocomplete', 'RecipeController@getRecipesForAutocomplete');
$router->get('/recipes/(\d+)/ingredients', 'RecipeController@getRecipeIngredients');

// routes for the ingredients endpoint
$router->post('/ingredients', 'IngredientController@getAll');

// routes for the auth endpoint
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');

// routes for the users endpoint
$router->get('/users', 'UserController@getAll');
$router->get('/users/(\d+)', 'UserController@getOne');
$router->put('/users/(\d+)', 'UserController@update');
$router->delete('/users/(\d+)', 'UserController@delete');


// Run it!
$router->run();
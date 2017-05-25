<?php

use \App\Vending;
use \App\Char;
use \App\BuyingStore;
require './vendor/autoload.php';

$config = require 'config.php';
$app = new \Slim\App($config);

$container = $app->getContainer();
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('settings')['db']);
$capsule->bootEloquent();

$app->get('/', function($request, $response) {
  return $response->withJson((object) [
    'buying' => BuyingStore::all()->map(function($store) {
      return $store->load('char', 'items');
    }),
    'selling' => Vending::all()->map(function($store) {
      return $store->load('char', 'items', 'items.attributes');
    })
  ]);
});

$app->get('/selling/{item}', function($request, $response, $args) {
  return $response->withJson(Vending::item($args['item']));
});

$app->get('/selling', function ($request, $response) {
  return $response->withJson(Vending::all()->map(function($store) {
    return $store->load('char', 'items', 'items.attributes');
  }));
});

$app->get('/buying/{item}', function($request, $response, $args) {
  return $response->withJson(BuyingStore::item($args['item']));
});

$app->get('/buying', function ($request, $response) {
  return $response->withJson(BuyingStore::all()->map(function($store) {
    return $store->load('char', 'items');
  }));
});

$app->get('/item/{item}', function($request, $response, $args) {
  return $response->withJson((object) [
    'buying' => BuyingStore::item($args['item']),
    'selling' => Vending::item($args['item'])
  ]);
});

$app->get('/merchant/{char}', function ($request, $response, $args) {
  return $response->withJson(Char::where('name', $args['char'])->first()->store());
});

$app->run();

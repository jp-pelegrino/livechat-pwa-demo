<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Messages API for tickets
    $routes->get('tickets/(:num)/messages', 'MessagesController::index/$1');
    $routes->post('tickets/(:num)/messages', 'MessagesController::create/$1');
});

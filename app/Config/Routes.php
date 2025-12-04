<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    // Rooms API
    $routes->get('rooms', 'RoomsController::index');
    $routes->get('rooms/(:num)', 'RoomsController::show/$1');
    $routes->post('rooms', 'RoomsController::create');
    $routes->get('rooms/(:num)/messages', 'RoomsController::messages/$1');
    
    // Messages API for tickets/rooms (backward compatible)
    $routes->get('tickets/(:num)/messages', 'MessagesController::index/$1');
    $routes->post('tickets/(:num)/messages', 'MessagesController::create/$1');
});

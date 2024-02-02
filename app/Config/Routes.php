<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/api/bpjs/monitoring', 'Rest::index');
$routes->get('/api/bpjs/sep', 'Rest::cekSep');

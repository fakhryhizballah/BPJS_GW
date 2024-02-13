<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/api/bpjs/monitoring', 'Rest::index');
$routes->get('/api/bpjs/monit', 'Rest::monit');
$routes->get('/api/bpjs/sep', 'Rest::cekSep');
$routes->get('/api/bpjs/peserta/nik', 'Rest::getPesertaByNik');
$routes->get('/api/bpjs/peserta/rujukan', 'Rest::getRujukan');

$routes->get('/api/bpjs/kamar', 'Rest::getKamar');
$routes->post('/api/bpjs/updatekamar', 'Rest::updateKamar');
$routes->post('/api/bpjs/deletkamar', 'Rest::delKamar');


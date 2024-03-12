<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/api/bpjs/monitoring', 'Rest::index');
$routes->get('/api/bpjs/monitoring/klaim', 'Rest::monit');
$routes->get('/api/bpjs/monitoring/kunjungan', 'Rest::kunjungan');
$routes->get('/api/bpjs/sep', 'Rest::cekSep');
$routes->get('/api/bpjs/peserta/nik', 'Rest::getPesertaByNik');
$routes->get('/api/bpjs/peserta/nokartu', 'Rest::getPesertaByNokartu');
$routes->get('/api/bpjs/peserta/rujukan', 'Rest::getRujukan');

$routes->get('/api/bpjs/kamar', 'Rest::getKamar');
$routes->post('/api/bpjs/updatekamar', 'Rest::updateKamar');
$routes->post('/api/bpjs/deletkamar', 'Rest::delKamar');


$routes->post('/api/bpjs/antrean/add', 'Rest::addAntrean');
$routes->get('/api/bpjs/antrean/pendaftaran', 'Rest::getAntrean');
$routes->get('/api/bpjs/antrean/jadwaldokter', 'Rest::getJadwalDokter');


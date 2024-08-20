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
$routes->get('/api/bpjs/peserta/jumlahsep', 'Rest::getJumlahSEP');
$routes->get('/api/bpjs/peserta/listrencanakontrol', 'Rest::ListRencanaKontrol');



$routes->get('/api/bpjs/kamar', 'Rest::getKamar');
$routes->post('/api/bpjs/updatekamar', 'Rest::updateKamar');
$routes->post('/api/bpjs/deletkamar', 'Rest::delKamar');
$routes->post('/api/bpjs/addkamar', 'Rest::addKamar');
$routes->get('/api/bpjs/refKamar', 'Rest::refKamar');


$routes->post('/api/bpjs/antrean/add', 'Rest::addAntrean');
$routes->get('/api/bpjs/antrean/pendaftaran', 'Rest::getAntrean');
$routes->post('/api/bpjs/antrean/getlisttask', 'Rest::getlisttask');
$routes->post('/api/bpjs/antrean/batal', 'Rest::antrean_batal');
$routes->post('/api/bpjs/antrean/updatewaktu', 'Rest::updatewaktu');
$routes->get('/api/bpjs/antrean/pendaftaranby', 'Rest::getAntreanby');
$routes->get('/api/bpjs/antrean/jadwaldokter', 'Rest::getJadwalDokter');
$routes->get('/api/bpjs/antrean/refdokter', 'Rest::getRefDokter');
$routes->get('/api/bpjs/antrean/refpoli', 'Rest::getRefPoli');
$routes->get('/api/bpjs/antrean/signatuer', 'Rest::signatuer');

$routes->post('/api/bpjs/icare/validate', 'Rest::icare');

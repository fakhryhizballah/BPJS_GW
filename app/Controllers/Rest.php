<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\Bpjs;
use App\Libraries\Inacbg;
use CodeIgniter\I18n\Time;


class Rest extends ResourceController
{
    protected $format    = 'json';
    
    public function __construct()
    {
        $this->Bpjs = new Bpjs();
        $this->Inacbg = new Inacbg();
        $this->client = new \GuzzleHttp\Client();
    }

    public function index()
    {
        $tanggal = $this->request->getVar('tanggal');
        $pelayanan = $this->request->getVar('pelayanan');
        $status =  $this->request->getVar('status');

        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Monitoring/Klaim/Tanggal/' . $tanggal . '/JnsPelayanan/' . $pelayanan . '/Status/' . $status, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function monit()
    {
        $pelayanan = $this->request->getVar('pelayanan');
        $status =  $this->request->getVar('status');

        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        // Get the start and end dates from the query parameters
        $from = $this->request->getGet('from');
        $until = $this->request->getGet('until');

        // Initialize an empty array to store the date list
        $dateList = [];

        // Convert the string dates to DateTime objects
        $fromDate = \DateTime::createFromFormat('Y-m-d', $from);
        $untilDate = \DateTime::createFromFormat('Y-m-d', $until);

        // Generate the date list
        while ($fromDate <= $untilDate) {
            $dateList[] = $fromDate->format('Y-m-d');
            $fromDate->modify('+1 day');
        }
        $x = [];
        foreach ($dateList as $tanggal) {
            $request =  new Request('GET', $data['vclaimURL'] . '/Monitoring/Klaim/Tanggal/' . $tanggal . '/JnsPelayanan/' . $pelayanan . '/Status/' . $status, $headers);
            $res = $this->client->sendAsync($request)->wait();
            $res = json_decode($res->getBody()->getContents());
            if ($res->metaData->code != "200") {
                continue;
            } else {
                $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
                $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
                $hasil = $this->Bpjs->decompress($hasil);
                $res->response = json_decode($hasil);
                $x = array_merge($x, $res->response->klaim);
            }
        }
        $hasil = [
            "metaData" => [
                "code" => "200",
                "message" => true
            ],
            "response" => [
            "record" => count($x),
            "data" => $x,
            ]
        ];
        return $this->respond($hasil);
    }
    public function kunjungan()
    {
        // Get the start and end dates from the query parameters
        $from = $this->request->getGet('from');
        $until = $this->request->getGet('until');

        $pelayanan = $this->request->getVar('pelayanan');

        // Initialize an empty array to store the date list
        $dateList = [];

        // Convert the string dates to DateTime objects
        $fromDate = \DateTime::createFromFormat('Y-m-d', $from);
        $untilDate = \DateTime::createFromFormat('Y-m-d', $until);

        // Generate the date list
        while ($fromDate <= $untilDate) {
            $dateList[] = $fromDate->format('Y-m-d');
            $fromDate->modify('+1 day');
        }

        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $x = [];
        foreach ($dateList as $tanggal) {
            $request =  new Request('GET', $data['vclaimURL'] . '/Monitoring/Kunjungan/Tanggal/' . $tanggal . '/JnsPelayanan/' . $pelayanan, $headers);
            $res = $this->client->sendAsync($request)->wait();
            $res = json_decode($res->getBody()->getContents());
            if ($res->metaData->code != "200") {
                continue;
            } else {
                $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
                $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
                $hasil = $this->Bpjs->decompress($hasil);
                $res->response = json_decode($hasil);
                $x = array_merge($x, $res->response->sep);
            }
        }
        $hasil = [
            "metaData" => [
                "code" => "200",
                "message" => true
            ],
            "response" => [
                "record" => count($x),
                "data" => $x,
            ]
        ];
        return $this->respond($hasil);
    }
    public function cekSep()
    {
        $data = $this->Bpjs->getSingnature();
        $noSEP = $this->request->getVar('noSEP');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/SEP/' . $noSEP, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getPesertaByNik()
    {
        $data = $this->Bpjs->getSingnature();
        $nik = $this->request->getVar('nik');
        $tglSEP = $this->request->getVar('tglSEP');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Peserta/nik/' . $nik . '/tglSEP/' . $tglSEP, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getPesertaByNokartu()
    {
        $data = $this->Bpjs->getSingnature();
        $nik = $this->request->getVar('nik');
        $tglSEP = $this->request->getVar('tglSEP');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Peserta/nokartu/' . $nik . '/tglSEP/' . $tglSEP, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getRujukan()
    {
        $data = $this->Bpjs->getSingnature();
        $noKartu = $this->request->getVar('noKartu');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Rujukan/List/Peserta/' . $noKartu, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getJumlahSEP()
    {
        $data = $this->Bpjs->getSingnature();
        $jenisRujukan = $this->request->getVar('jenisRujukan');
        $noRujukan = $this->request->getVar('noRujukan');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Rujukan/JumlahSEP/' . $jenisRujukan . '/' . $noRujukan, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function ListRencanaKontrol()
    {
        $data = $this->Bpjs->getSingnature();
        $bulan = $this->request->getVar('Bulan');
        $tahun = $this->request->getVar('Tahun');
        $nokartu = $this->request->getVar('Nokartu');
        $filter = $this->request->getVar('filter');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/RencanaKontrol/ListRencanaKontrol/Bulan/' . $bulan . '/Tahun/' . $tahun . '/Nokartu/' . $nokartu . '/filter/' . $filter, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getfinger()
    {
        $data = $this->Bpjs->getSingnature();
        $nokartu = $this->request->getVar('Nokartu');
        $Tglpelayanan = $this->request->getVar('Tglpelayanan');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/SEP/FingerPrint/Peserta/' . $nokartu . '/TglPelayanan/' . $Tglpelayanan, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getallfinger()
    {
        $data = $this->Bpjs->getSingnature();
        $Tglpelayanan = $this->request->getVar('Tglpelayanan');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/SEP/FingerPrint/List/Peserta/TglPelayanan/' . $Tglpelayanan, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getKamar()
    {
        $data = $this->Bpjs->getSingnature();
        $start = $this->request->getVar('start');
        $limit = $this->request->getVar('limit');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['URL'] . 'aplicaresws/rest/bed/read/' . $data['ppk'] . '/' . $start . '/' . $limit, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }
    public function updateKamar()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['URL'] . 'aplicaresws/rest/bed/update/' . $data['ppk'], $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }
    public function delKamar()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['URL'] . 'aplicaresws/rest/bed/delete/' . $data['ppk'], $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }
    public function addKamar()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['URL'] . 'aplicaresws/rest/bed/create/' . $data['ppk'], $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }
    public function refKamar()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('GET', $data['URL'] . 'aplicaresws/rest/ref/kelas', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }

    public function addAntrean()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['baseURL'] . '/antrean/add', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }
    public function addFarmasi()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['baseURL'] . '/antrean/farmasi/add', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        return $this->respond($res);
    }

    public function getAntrean()
    {
        $data = $this->Bpjs->getSingnature();
        $tanggal = $this->request->getVar('tanggal');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('GET', $data['baseURL'] . '/antrean/pendaftaran/tanggal/' . $tanggal, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metadata->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getlisttask()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['baseURL'] . '/antrean/getlisttask', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metadata->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function antrean_batal()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['baseURL'] . '/antrean/batal', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metadata->code != "200") {
            return $this->respond($res);
        }
        return $this->respond($res);
    }
    public function updatewaktu()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['baseURL'] . '/antrean/updatewaktu', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metadata->code != "200") {
            return $this->respond($res);
        }
        return $this->respond($res);
    }
    public function getAntreanby()
    {
        $data = $this->Bpjs->getSingnature();
        $from = $this->request->getGet('from');
        $until = $this->request->getGet('until');

        $dateList = [];

        // Convert the string dates to DateTime objects
        $fromDate = \DateTime::createFromFormat('Y-m-d', $from);
        $untilDate = \DateTime::createFromFormat('Y-m-d', $until);

        // Generate the date list
        while ($fromDate <= $untilDate) {
            $dateList[] = $fromDate->format('Y-m-d');
            $fromDate->modify('+1 day');
        }
        $x = [];
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        foreach ($dateList as $tanggal) {
            $request =  new Request('GET', $data['baseURL'] . '/antrean/pendaftaran/tanggal/' . $tanggal, $headers);
            $res = $this->client->sendAsync($request)->wait();
            $res = json_decode($res->getBody()->getContents());
            if ($res->metadata->code != "200") {
                continue;
            } else {
                $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
                $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
                $hasil = $this->Bpjs->decompress($hasil);
                $res->response = json_decode($hasil);

                $x = array_merge($x, $res->response);
            }
        }
        $hasil = [
            "metaData" => [
                "code" => "200",
                "message" => true
            ],
            "response" => [
                "record" => count($x),
                "data" => $x,
            ]
        ];
        return $this->respond($hasil);
    }
    public function getJadwalDokter()
    {
        $data = $this->Bpjs->getSingnature();
        $tanggal = $this->request->getVar('tanggal');
        $kd_poli = $this->request->getVar('kd_poli_BPJS');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('GET', $data['baseURL'] . '/jadwaldokter/kodepoli/' . $kd_poli . '/tanggal/' . $tanggal, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metadata->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getRefDokter()
    {
        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('GET', $data['baseURL'] . '/ref/dokter', $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function getRefPoli()
    {
        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('GET', $data['baseURL'] . '/ref/poli', $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());

        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }

    public function addfarmasi()
    {
        $data = $this->Bpjs->getSingnature();
        $bulan = $this->request->getVar('bulan');
        $tahun = $this->request->getVar('tahun');
        $filter = $this->request->getVar('filter');
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Sep/updtglplg/list/bulan/' . $bulan . '/tahun/' . $tahun . '/' . $filter, $headers);
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());

        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function icare()
    {
        $data = $this->Bpjs->getSingnature();
        $kelas = $this->request->getBody();
        $kelas = json_decode($kelas);
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'Content-Type' => 'application/json'
        ];
        $request =  new Request('POST', $data['URL'] . 'wsihs/api/rs/validate', $headers, json_encode($kelas));
        $res = $this->client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        if ($res->metaData->code != "200") {
            return $this->respond($res);
        }
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        $res->response = json_decode($hasil);
        return $this->respond($res);
    }
    public function decript()
    {
        $body = $this->request->getBody();
        $body = json_decode($body, true);

        $key =  $body['x-cons-id'] . '6yY911188D' . $body['x-timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $body['response']);
        $hasil = $this->Bpjs->decompress($hasil);
        //  $res->response = json_decode($hasil);
        return $this->respond($hasil);
    }
    public function signatuer()
    {
        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key'],
            'baseURL' => $data['baseURL'],
            'vclaimURL' => $data['vclaimURL'],
            'Content-Type' => 'application/json'
        ];
        return $this->respond($headers);
    }
    public function get_claim_data()
    {
        $param = $this->request->getGet();
        $request = '{
                        "metadata": {
                            "method":"' . $param['method'] . '"
                        },
                        "data": {
                            "nomor_sep":"' . $param['noSEP'] . '"
                        }
                   }';

        $msg = $this->Inacbg->Request($request);
        return $this->respond($msg);
    }
    public function get_claim_covid()
    {
        $param = $this->request->getGet();
        $request = '{
                        "metadata": {
                            "method":"' . $param['method'] . '"
                        },
                        "data": {
                            "nomor_sep":"' . $param['noSEP'] . '",
                            "nomor_pengajuan": "' . $param['nomor_pengajuan'] . '"
                        }
                   }';
        $msg = $this->Inacbg->Request($request);
        return $this->respond($msg);
    }
    public function get_claim_pdf()
    {
        $param = $this->request->getGet();
        $request = '{
                        "metadata": {
                            "method":"' . $param['method'] . '"
                        },
                        "data": {
                            "nomor_sep":"' . $param['noSEP'] . '"
                        }
                   }';
        $msg = $this->Inacbg->Request($request);
        $pdf = base64_decode($msg["data"]);
        // hasilnya adalah berupa binary string $pdf, untuk disimpan:
        file_put_contents("klaim.pdf", $pdf);
        // atau untuk ditampilkan dengan perintah:
        header("Content-type:application/pdf");
        header("Content-Disposition:attachment;filename=" . $param['noSEP'] . ".pdf");
        echo $pdf;
        // return $this->respond($msg['data']);
    }
 
}

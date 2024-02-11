<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\Bpjs;
use CodeIgniter\I18n\Time;


class Rest extends ResourceController
{
    protected $format    = 'json';
    
    public function __construct()
    {
        $this->Bpjs = new Bpjs();
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
                return $this->respond($res);
            }
            $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
            $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
            $hasil = $this->Bpjs->decompress($hasil);
            $res->response = json_decode($hasil);

            $x = array_merge($x, $res->response->klaim);
        }
        $hasil = [
            "status" => true,
            "message" => "Monitoring Data Klaim",
            "record" => count($x),
            "data" => $x,
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
}

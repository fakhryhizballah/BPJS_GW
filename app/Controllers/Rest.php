<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\Bpjs;


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

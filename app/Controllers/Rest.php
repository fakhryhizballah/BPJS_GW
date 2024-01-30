<?php

namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\Bpjs;


class Rest extends ResourceController
{
    public function __construct()
    {
        $this->Bpjs = new Bpjs();
    }

    public function index()
    {
        // get params request
        $request = \Config\Services::request();
        $tanggal = $request->getVar('tanggal');
        $pelayanan = $request->getVar('pelayanan');
        $status = $request->getVar('status');
        $client = new \GuzzleHttp\Client();
        $data = $this->Bpjs->getSingnature();
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/Monitoring/Klaim/Tanggal/' . $tanggal . '/JnsPelayanan/' . $pelayanan . '/Status/' . $status, $headers);
        $res = $client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        // echo $res->response;
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        // retunt nik;
        $hasil = json_decode($hasil);
        echo json_encode($hasil);
        //dd($hasil);
    }
    public function sep()
    {
        $client = new \GuzzleHttp\Client();
        $data = $this->Bpjs->getSingnature();
        $noSEP = '1510R0010823V006423';
        $headers = [
            'x-cons-id' => $data['X_cons_id'],
            'x-timestamp' =>  $data['timestamp'],
            'x-signature' => $data['signature'],
            'user_key' => $data['user_key']
        ];
        $request =  new Request('GET', $data['vclaimURL'] . '/SEP/' . $noSEP, $headers);
        $res = $client->sendAsync($request)->wait();
        $res = json_decode($res->getBody()->getContents());
        // echo $res->response;
        $key =  $data['X_cons_id'] . $data['secretKey'] . $data['timestamp'];
        $hasil = $this->Bpjs->stringDecrypt($key, $res->response);
        $hasil = $this->Bpjs->decompress($hasil);
        // retunt nik;
        $hasil = json_decode($hasil);
        dd($hasil);
    }
}

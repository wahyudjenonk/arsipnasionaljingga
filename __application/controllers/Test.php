<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

//Controller TEST API
class Test extends CI_Controller {
	
	function __construct(){
		parent::__construct();
	}
	
	function testapi(){
		$url = "http://localhost:81/public_codeigniter/pgasol_arsip/api/getapi"; // url = (host/IP)/namadirektoriserver/api/getapi
		$data = array(
			"type_api" => "document_file", 	// Type API
			"page" => 1,   					// Untuk Paging Data
			"per_page" => 50, 				// Untuk Paging Data
			//"no_dokumen" => "operator", 	// Parameter Search No Dokumen
			//"perihal" => "teknisi", 		// Parameter Search Perihal Dokumen
		);
		
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data));
		
		$kirim = curl_exec($curl_handle);
		curl_close($curl_handle);
		
		header('Content-Type: application/json');
		echo $kirim;
		
		/*
		$hasil = json_decode($kirim, true);
		echo "<pre>";
		print_r($hasil);
		*/
	}
	
}
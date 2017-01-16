<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

//Controller API
class Api extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('modelsx');
		$this->load->library(array('encrypt','lib'));
	}
	
	function getapi(){
		header('Content-Type: application/json');
		$type = $this->input->post('type_api');
		
		switch($type){
			case "document_file":
				$per_page = $this->input->post('per_page');
				$page = $this->input->post('page');
				$no_dokumen = $this->input->post('no_dokumen');
				$perihal = $this->input->post('perihal');
				
				$array_parameter = array(
					'per_page' => $per_page,
					'page' => $page,
					'no_dokumen' => $no_dokumen,
					'perihal' => $perihal,
				);
				
				$array = array();
				$data = $this->modelsx->getapi($type, $array_parameter);
				
				$array['total_rows'] = count($data);
				$array['data'] = array();
				foreach($data as $k => $v){
					$array['data'][$k]['no_dokumen'] = $v["no_dokumen"];
					$array['data'][$k]['perihal'] = $v["perihal"];
					if($v["pengirim"] == "Internal"){
						$array['data'][$k]['pengirim'] = $v["pengirim"]." - ".$v["pengirim_internal"];
					}else{
						$array['data'][$k]['pengirim'] = $v["pengirim"]." - ".$v["pengirim_external"];
					}
					$array['data'][$k]['file'] = $this->config->item("base_url")."__repository/".$v["cl_unit_kerja_id"]."/".$v["nama_file"];
					$array['data'][$k]['jenis_dokumen'] = $v["tipe_dokumen"];
					$array['data'][$k]['tanggal_arsip'] = $v["tanggal_arsipnya"];
					$array['data'][$k]['tanggal_upload'] = $v["tanggal_uploadnya"];
				}
				
				echo json_encode($array);
			break;
		}
	}
	
}
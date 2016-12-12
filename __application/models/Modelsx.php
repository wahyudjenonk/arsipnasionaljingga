<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Modelsx extends CI_Model{
	function __construct(){
		parent::__construct();
		//$this->auth = unserialize(base64_decode($this->session->userdata('44mpp3R4')));
	}
	
	function getdata($type="", $balikan="", $p1="", $p2="",$p3="",$p4=""){
		$where = " WHERE 1=1 ";
		if($this->input->post('kat')){
			$where .=" AND ".$this->input->post('kat')." like '%".$this->db->escape_str($this->input->post('key'))."%'";
		}
		switch($type){
			case "data_login":
				$sql = "
					SELECT A.*, B.nama_unit
					FROM tbl_user A
					LEFT JOIN cl_unit_kerja B ON B.id = A.cl_unit_kerja_id
					WHERE A.nama_user = '".$p1."'
				";
			break;
			
			case "tbl_upload_file":
				if($this->auth['cl_user_group_id'] != "1"){
					$where .= " AND A.cl_unit_kerja_id = '".$this->auth['cl_unit_kerja_id']."' ";
				}
				
				$sql = "
					SELECT A.*, 
						B.nama_unit as unit_kerja, DATE_FORMAT(A.create_date,'%d %b %y') as tanggal_upload
					FROM tbl_upload_file A
					LEFT JOIN cl_unit_kerja B ON B.id = A.cl_unit_kerja_id
					$where
				";
			break;
			
			default:
				if($balikan=='get'){$where .=" AND A.id=".$this->input->post('id');}
				$sql="SELECT A.* FROM ".$type." A ".$where;
				if($balikan=='get')return $this->db->query($sql)->row_array();
			break;
		}
		
		if($balikan == 'json'){
			return $this->lib->json_grid($sql);
		}elseif($balikan == 'row_array'){
			return $this->db->query($sql)->row_array();
		}elseif($balikan == 'result'){
			return $this->db->query($sql)->result();
		}elseif($balikan == 'result_array'){
			return $this->db->query($sql)->result_array();
		}
		
	}
	
	function get_combo($type="", $p1="", $p2=""){
		switch($type){
			case "cl_unit_kerja":
				$sql = "
					SELECT id, nama_unit as txt
					FROM cl_unit_kerja
				";
			break;
		}
		
		return $this->db->query($sql)->result_array();
	}
	
	function simpandata($table,$data,$sts_crud){ //$sts_crud --> STATUS NYEE INSERT, UPDATE, DELETE
		$this->db->trans_begin();
		if(isset($data['id'])){
			$id = $data['id'];
			unset($data['id']);
		}
		
		switch($table){
			case "tbl_upload_file":				
				$target_path = "__repository/".$this->auth['cl_unit_kerja_id']."/";
				if(!is_dir($target_path)) {
					mkdir($target_path, 0777);         
				}
				
				if($sts_crud == 'add'){
					$sqlmax = "
						SELECT MAX(id) as id_max
						FROM tbl_upload_file
					";
					$querymax = $this->db->query($sqlmax)->row_array();
					if($querymax['id_max'] != null){
						$idmax = ($querymax['id_max'] + 1);
						$idmax = date('Ym')."-".sprintf('%05d', $idmax);
					}else{
						$idmax = date('Ym')."-00001";
					}
					$data['id_dokumen'] = $idmax;
					$namefilenya = $idmax;
				}elseif($sts_crud == 'edit'){
					$getdata = $this->db->get_where('tbl_upload_file', array('id'=>$id) )->row_array();
					$namefilenya = $getdata['id_dokumen'];
				}elseif($sts_crud == 'delete'){
					if(file_exists($target_path.$data['nama_file'])){
						unlink($target_path.$data['nama_file']);
					}
				}
				
				if(isset($_FILES['filename']['name'])){
					if($_FILES['filename']['name'] != ''){	
						if($sts_crud == 'edit'){
							unlink($target_path.$getdata['nama_file']);
						}
						$filebersih = $this->lib->clean(pathinfo($_FILES['filename']['name'], PATHINFO_FILENAME));
						$file_p = $namefilenya.strtoupper($filebersih);
						$filename_p =  $this->lib->uploadnong($target_path, 'filename', $file_p); 
						$data['nama_file'] = $filename_p;
					}else{
						$data['nama_file'] = null;
					}
				}
				
				$data['cl_unit_kerja_id'] = $this->auth['cl_unit_kerja_id'];
			break;
		}
		
		switch ($sts_crud){
			case "add":
				$data['create_date'] = date('Y-m-d H:i:s');
				$data['create_by'] = $this->auth['nama_user'];
				$this->db->insert($table,$data);
			break;
			case "edit":
				$this->db->update($table, $data, array('id' => $id) );				
			break;
			case "delete":
				$this->db->delete($table, array('id' => $id));
			break;
		}
		
		if($this->db->trans_status() == false){
			$this->db->trans_rollback();
			return 'gagal';
		}else{
			 return $this->db->trans_commit();
		}
	}
	
}
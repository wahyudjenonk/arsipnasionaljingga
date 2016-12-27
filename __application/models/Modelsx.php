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
			case "total_dokumen_unit_kerja":
				$sql = "
					SELECT count(id) as jmlnya
					FROM tbl_upload_file 
					WHERE cl_unit_kerja_id = '".$p1."'
				";
			break;
			case "tbl_log":
				$sql="SELECT * FROM tbl_log ".$where." ORDER BY create_date DESC ";
			break;
			case "chart_file":
				$sql="SELECT A.nama_unit,
					CASE WHEN B.total IS NULL THEN 0 ELSE B.total END AS total
					FROM cl_unit_kerja A
					LEFT JOIN(
							SELECT B.nama_unit,COUNT(A.cl_unit_kerja_id)as total 
							FROM tbl_upload_file A
							LEFT JOIN cl_unit_kerja B ON A.cl_unit_kerja_id=B.id
							GROUP BY B.nama_unit
					)AS B ON A.nama_unit=B.nama_unit ";
			break;
			case "tbl_user":
				$sql="SELECT A.*,B.group_user,C.nama_unit 
						FROM tbl_user A 
						LEFT JOIN cl_group_user B ON A.cl_user_group_id=B.id 
						LEFT JOIN cl_unit_kerja C ON A.cl_unit_kerja_id=C.id 
						".$where;
			break;
			case "ldap_user":
				if($this->input->post('key')){
					$data=$this->lib->get_ldap_user("data_ldap",$this->input->post('key'));
				}else{
					$data=$this->lib->get_ldap_user("data_ldap");
				}
				//print_r($data);
				if($data['msg']==1){
				   $responce = new stdClass();
				   $responce->rows= $data['data'];
				   $responce->total =count($data);
				   return json_encode($responce);
				}else{ 
				   $responce = new stdClass();
				   $responce->rows = 0;
				   $responce->total = 0;
				   return json_encode($responce);
				} 
				
			break;
			case "data_login":
				$sql = "
					SELECT A.*, B.nama_unit
					FROM tbl_user A
					LEFT JOIN cl_unit_kerja B ON B.id = A.cl_unit_kerja_id
					WHERE A.nama_user = '".$p1."'
				";
			break;
			case "tbl_ldap_group":
				$sql="SELECT A.*,B.group_user as grp_user,C.nama_unit 
					  FROM tbl_ldap_group A 
					  LEFT JOIN cl_group_user B ON A.cl_group_user_id=B.id
					  LEFT JOIN cl_unit_kerja C ON A.cl_unit_kerja_id=C.id 
					  ".$where." AND A.flag <> 'D'";
			break;
			case "group_ldap":
				$sql="SELECT A.*,B.group_user 
					  FROM tbl_ldap_group A
					  LEFT JOIN cl_group_user B ON A.cl_group_user_id=B.id
					  WHERE user_ldap='".$p1."'";
			break;
			case "unit_sharing":
				$sql="SELECT * FROM cl_unit_kerja
						WHERE id NOT IN(
							SELECT cl_unit_id FROM tbl_sharing_file WHERE tbl_upload_file_id=".$p2."
						) AND id <> ".$p1;
			break;
			case "tbl_upload_file":
				if($this->input->post('request_delete')){
					$where .= " AND A.status_data = 'RD' ";
				}
				
				if($this->auth['cl_user_group_id'] != "1"){
					$where .= " AND A.cl_unit_kerja_id = '".$this->auth['cl_unit_kerja_id']."' ";
				}
				
				$sql = "
					SELECT A.*, 
						B.nama_unit as unit_kerja, DATE_FORMAT(A.create_date,'%d %b %y') as tanggal_upload,
						C.tipe_dokumen
					FROM tbl_upload_file A
					LEFT JOIN cl_unit_kerja B ON B.id = A.cl_unit_kerja_id
					LEFT JOIN cl_jenis_dokumen C ON C.id = A.cl_jenis_dokumen_id
					$where
				";
			break;
			case "tbl_sharing_file":
				$sql = "
					SELECT B.*, C.nama_unit, D.tipe_dokumen, 
						DATEDIFF(A.tgl_akhir, NOW()) as time_limit
					FROM tbl_sharing_file A
					LEFT JOIN tbl_upload_file B ON B.id = A.tbl_upload_file_id
					LEFT JOIN cl_unit_kerja C ON C.id = B.cl_unit_kerja_id
					LEFT JOIN cl_jenis_dokumen D ON D.id = B.cl_jenis_dokumen_id
					WHERE A.cl_unit_id = '".$this->auth['cl_unit_kerja_id']."' 
					AND A.tgl_akhir >= NOW()
				";
			break;
			
			//User Management Role
			case "menu":
				$sql = "
					SELECT a.tbl_menu_id, b.nama_menu, b.type_menu, b.icon_menu, b.url, b.ref_tbl
						FROM tbl_user_prev_group a
					LEFT JOIN tbl_user_menu b ON a.tbl_menu_id = b.id 
					WHERE a.cl_user_group_id=".$this->auth['cl_user_group_id']." 
					AND (b.type_menu='P' OR b.type_menu='PC') AND b.status=1
				";
				$parent = $this->db->query($sql)->result_array();
				$menu = array();
				foreach($parent as $v){
					$menu[$v['tbl_menu_id']]=array();
					$menu[$v['tbl_menu_id']]['parent']=$v['nama_menu'];
					$menu[$v['tbl_menu_id']]['icon_menu']=$v['icon_menu'];
					$menu[$v['tbl_menu_id']]['url']=$v['url'];
					$menu[$v['tbl_menu_id']]['type_menu']=$v['type_menu'];
					$menu[$v['tbl_menu_id']]['judul_kecil']=$v['ref_tbl'];
					$menu[$v['tbl_menu_id']]['child']=array();
					$sql="
						SELECT a.tbl_menu_id, b.nama_menu, b.url, b.icon_menu , b.type_menu, b.ref_tbl
							FROM tbl_user_prev_group a
						LEFT JOIN tbl_user_menu b ON a.tbl_menu_id = b.id 
						WHERE a.cl_user_group_id=".$this->auth['cl_user_group_id']." 
						AND (b.type_menu = 'C' OR b.type_menu = 'CHC') 
						AND b.status=1 AND b.parent_id=".$v['tbl_menu_id'];
					$child = $this->db->query($sql)->result_array();
					foreach($child as $x){
						$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]=array();
						$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['menu']=$x['nama_menu'];
						$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['type_menu']=$x['type_menu'];
						$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['url']=$x['url'];
						$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['icon_menu']=$x['icon_menu'];
						$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['judul_kecil']=$x['ref_tbl'];
						
						if($x['type_menu'] == 'CHC'){
							$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['sub_child'] = array();
							$sqlSubChild="
								SELECT a.tbl_menu_id, b.nama_menu, b.url, b.icon_menu , b.type_menu, b.ref_tbl
									FROM tbl_user_prev_group a
								LEFT JOIN tbl_user_menu b ON a.tbl_menu_id = b.id 
								WHERE a.cl_user_group_id=".$this->auth['cl_user_group_id']." 
								AND b.type_menu = 'CC'
								AND b.parent_id_2 = ".$x['tbl_menu_id']."
								AND b.status=1 
							";
							$SubChild = $this->db->query($sqlSubChild)->result_array();
							foreach($SubChild as $z){
								$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['sub_child'][$z['tbl_menu_id']]['sub_menu'] = $z['nama_menu'];
								$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['sub_child'][$z['tbl_menu_id']]['type_menu'] = $z['type_menu'];
								$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['sub_child'][$z['tbl_menu_id']]['url'] = $z['url'];
								$menu[$v['tbl_menu_id']]['child'][$x['tbl_menu_id']]['sub_child'][$z['tbl_menu_id']]['icon_menu'] = $z['icon_menu'];
							}
						}
						
					}
				}
				
				/*
				echo "<pre>";
				print_r($menu);exit;
				//*/
				
				$array = $menu;
			break;		
			
			case "menu_parent":
				$sql = "
					SELECT A.*
					FROM tbl_user_menu A
					WHERE (A.type_menu = 'P' OR A.type_menu = 'PC') AND A.status = '1'
				";
			break;
			case "menu_child":
				$sql = "
					SELECT A.*
					FROM tbl_user_menu A
					WHERE (A.type_menu = 'C') AND A.parent_id = '".$p1."' AND A.status = '1'
				";
			break;
			case "menu_child_2":
				$sql = "
					SELECT A.*
					FROM tbl_user_menu A
					WHERE A.type_menu = 'CC' AND A.parent_id_2 = '".$p1."' AND A.status = '1'
				";
			break;
			case "previliges_menu":
				$sql = "
					SELECT A.*
					FROM tbl_user_prev_group A
					WHERE A.tbl_menu_id = '".$p1."' AND A.cl_user_group_id = '".$p2."'
				";
			break;			
			//End Modul User Management			
			
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
		}elseif($balikan == 'variable'){
			return $array;
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
			case "cl_jenis_dokumen":
				$sql = "
					SELECT id, tipe_dokumen as txt
					FROM cl_jenis_dokumen
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
			case "mapping": $table="tbl_ldap_group";break;
			case "user_mng": $table="tbl_user"; break;
			case "group": $table="cl_group_user"; break;
			case "unit": $table="cl_unit_kerja"; break;
			case "tbl_user":
				if($sts_crud=='add'){
					$cek_user=$this->db->get_where('tbl_user',array('nama_user'=>$data['nama_user']))->row_array();
					if(isset($cek_user['nama_user'])){
						$this->db->trans_rollback();
						return 2;
					}else{
						$pwd=$data['password'];unset($data['password']);
						$data['password']=$this->encrypt->encode($pwd);
					}
				}
				//print_r($data);exit;
			break;
			case "sharing":
				//print_r($data);exit;
				$pilih=$data['pilihan'];
				unset($data['pilihan']);
				$data['create_date'] = date('Y-m-d H:i:s');
				$data['create_by'] = $this->auth['nama_user'];
				foreach($pilih as $x){
					$data['cl_unit_id']=$x;
					$this->db->insert('tbl_sharing_file',$data);
				}
				if($this->db->trans_status() == false){
					$this->db->trans_rollback();
					return 'gagal';
				}else{
					 return $this->db->trans_commit();
				}
			break;
			
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
					$data['no_dokumen'] = $idmax;
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
			case "user_role_group":
				$id_group = $id;
				$this->db->delete('tbl_user_prev_group', array('cl_user_group_id'=>$id_group) );
				if(isset($data['data'])){
					$postdata = $data['data'];
					$row=array();
					foreach($postdata as $v){
						$pecah = explode("_",$v);
						$row["buat"]=0;
						$row["baca"]=0;
						$row["ubah"]=0;
						$row["hapus"]=0;
						
						switch($pecah[0]){
							case "C":
								$row["buat"]=1;
							break;
							case "R":
								$row["baca"]=1;
							break;
							case "U":
								$row["ubah"]=1;
							break;
							case "D":
								$row["hapus"]=1;
							break;
						}
						
						$row["tbl_menu_id"] = $pecah[1];
						$row["cl_user_group_id"] = $id_group;
						
						$cek_data = $this->db->get_where('tbl_user_prev_group', array('tbl_menu_id'=>$pecah[1], 'cl_user_group_id'=>$id_group) )->row_array();
						if(!$cek_data){
							$this->db->insert('tbl_user_prev_group', $row);
						}else{
							if($row["buat"]==0)unset($row["buat"]);
							if($row["baca"]==0)unset($row["baca"]);
							if($row["ubah"]==0)unset($row["ubah"]);
							if($row["hapus"]==0)unset($row["hapus"]);
							
							$this->db->update('tbl_user_prev_group', $row, array('tbl_menu_id'=>$pecah[1], 'cl_user_group_id'=>$id_group) );
						}
					}	
				}
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
			case "req_delete":
				$this->db->update($table, array('status_data'=>'RD'), array('id' => $id) );			
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

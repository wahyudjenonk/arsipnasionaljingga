<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

//Controller1 Utk aa goyz
class Controllerx extends JINGGA_Controller {
	
	function __construct(){
		parent::__construct();
		if(!$this->auth){
			$this->nsmarty->display('backend/main-login.html');
			exit;
		}
		$this->nsmarty->assign('acak', md5(date('H:i:s')) );
		$this->temp="backend/";
		$this->load->model('modelsx');
		$this->load->library(array('encrypt','lib','encrypt'));
	}
	
	function index(){
		if($this->auth){
			$menu = $this->modelsx->getdata('menu', 'variable');			
			$this->nsmarty->assign('menu', $menu);
			$this->nsmarty->display( 'backend/main-backend.html');
		}else{
			$this->nsmarty->display( 'backend/main-login.html');
		}
	}
	
	function modul($p1="",$p2=""){
		if($this->auth){
			switch($p1){
				case "beranda":
					switch($p2){
						case "main":
							$this->nsmarty->assign('combo_bulan', $this->lib->fillcombo('bulan', 'return'));
							$this->nsmarty->assign('combo_tahun', $this->lib->fillcombo('tahun', 'return'));
						
							if($this->auth['cl_user_group_id'] == 1){
								$array_filter = array();
							}elseif($this->auth['cl_user_group_id'] == 2){
								$array_filter = array(
									'id' => $this->auth['cl_user_group_id']
								);
							}
							$data_unit_kerja = $this->db->get_where('cl_unit_kerja', $array_filter)->result_array();
							foreach($data_unit_kerja as $k => $v){
								$data_total_dokumen = $this->modelsx->getdata('total_dokumen_unit_kerja', 'row_array', $v['id']);
								$data_unit_kerja[$k]['jumlah'] = $data_total_dokumen['jmlnya'];
								unset($data_unit_kerja[$k]['keterangan']);
								unset($data_unit_kerja[$k]['create_date']);
								unset($data_unit_kerja[$k]['create_by']);
							}
							$this->nsmarty->assign('data_unit_kerja', $data_unit_kerja);
							
							/*
							echo "<pre>";
							print_r($data_unit_kerja);exit;
							//*/
						break;
						case "total_arsip":
							$groupuser = $this->input->post('grp');
							$data_total_dokumen = $this->modelsx->getdata('total_dokumen_unit_kerja', 'row_array', $groupuser);
							
							echo $data_total_dokumen['jmlnya'];
							exit;
						break;
						case "total_arsip_html":
							$data_unit_kerja = $this->db->get('cl_unit_kerja')->result_array();
							foreach($data_unit_kerja as $k => $v){
								$data_total_dokumen = $this->modelsx->getdata('total_dokumen_unit_kerja', 'row_array', $v['id']);
								$data_unit_kerja[$k]['jumlah'] = $data_total_dokumen['jmlnya'];
								unset($data_unit_kerja[$k]['keterangan']);
								unset($data_unit_kerja[$k]['create_date']);
								unset($data_unit_kerja[$k]['create_by']);
							}
							$this->nsmarty->assign('data_unit_kerja', $data_unit_kerja);
							$html = $this->nsmarty->fetch('backend/modul/beranda/total_arsip.html');
							
							echo $html;
							exit;
						break;
					}
				break;
				case "preview_file":
					$auth_sharing = $this->db->get_where("tbl_user_prev_group", array("cl_user_group_id"=>$this->auth["cl_user_group_id"], "tbl_menu_id"=>"2") )->row_array();
					if($auth_sharing["download_file"] == 1){
						$this->nsmarty->assign("tinggi_iframe", "519");
						$this->nsmarty->assign("button_download", "true");
					}else{
						$this->nsmarty->assign("tinggi_iframe", "540");
						$this->nsmarty->assign("button_download", "false");
					}
										
					$getdata = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('idx')) )->row_array();
					$nama_area = $this->db->get_where("cl_area", array("id"=>$getdata['cl_area_id']) )->row_array();
					$nama_folder = str_replace(" ", "_", strtolower($nama_area["nama_area"]) );
					$target_path = "__repository/".$getdata['cl_unit_kerja_id']."/".$nama_folder."/";
					$nama_file = $this->input->post('nama_file');					
					
					$this->nsmarty->assign("filenya", $target_path.$nama_file);
					$this->nsmarty->display("backend/modul/management_file/preview_file.html");
					exit;
				break;
				case "preview_file_sharing":
					$this->nsmarty->assign("tinggi_iframe", "540");
					$this->nsmarty->assign("button_download", "false");
					
					$getdata = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('idx')) )->row_array();
					$nama_area = $this->db->get_where("cl_area", array("id"=>$getdata['cl_area_id']) )->row_array();
					$nama_folder = str_replace(" ", "_", strtolower($nama_area["nama_area"]) );
					$target_path = "__repository/".$getdata['cl_unit_kerja_id']."/".$nama_folder."/";
					$nama_file = $this->input->post('nama_file');					
					
					$this->nsmarty->assign("filenya", $target_path.$nama_file);
					$this->nsmarty->display("backend/modul/management_file/preview_file.html");
					exit;
				break;
				case "management_file":
					$auth_sharing = $this->db->get_where("tbl_user_prev_group", array("cl_user_group_id"=>$this->auth["cl_user_group_id"], "tbl_menu_id"=>"2") )->row_array();
					if($auth_sharing){
						$this->nsmarty->assign("auth_sharing", $auth_sharing);
					}
					
					switch($p2){
						case "sharing_file":
							//$data = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('id')) )->row_array();
							$data = $this->modelsx->getdata('upload_file','row_array');
							$unit = $this->modelsx->getdata('unit_sharing','result_array',$data['cl_unit_kerja_id'],$data['id']);
							$this->nsmarty->assign("data", $data);
							$this->nsmarty->assign("unit", $unit);
							//print_r($unit);
						break;
						case "advanced_search":
							$this->nsmarty->assign("jenis_dokumen", $this->lib->fillcombo('cl_jenis_dokumen', 'return') );
							$this->nsmarty->assign("pengirim", $this->lib->fillcombo('pengirim', 'return') );
							$this->nsmarty->assign("area", $this->lib->fillcombo('cl_area', 'return' ) );
						break;
					}
				break;
				case "user_management":
					switch($p2){
						case "form_user_role":
							$id_role = $this->input->post('id');
							$array = array();
							$dataParent = $this->modelsx->getdata('menu_parent', 'result_array');
							foreach($dataParent as $k=>$v){
								$dataChild = $this->modelsx->getdata('menu_child', 'result_array', $v['id']);
								$dataPrev = $this->modelsx->getdata('previliges_menu', 'row_array', $v['id'], $id_role);
								
								$array[$k]['id'] = $v['id'];
								$array[$k]['nama_menu'] = $v['nama_menu'];
								$array[$k]['id_prev'] = (isset($dataPrev['id']) ? $dataPrev['id'] : 0) ;
								$array[$k]['buat'] = (isset($dataPrev['buat']) ? $dataPrev['buat'] : 0) ;
								$array[$k]['baca'] = (isset($dataPrev['baca']) ? $dataPrev['baca'] : 0);
								$array[$k]['ubah'] = (isset($dataPrev['ubah']) ? $dataPrev['ubah'] : 0);
								$array[$k]['hapus'] = (isset($dataPrev['hapus']) ? $dataPrev['hapus'] : 0);
								$array[$k]['sharing_file'] = (isset($dataPrev['sharing_file']) ? $dataPrev['sharing_file'] : 0);
								$array[$k]['download_file'] = (isset($dataPrev['download_file']) ? $dataPrev['download_file'] : 0);
								$array[$k]['child_menu'] = array();
								$jml = 0;
								foreach($dataChild as $y => $t){
									$dataPrevChild = $this->modelsx->getdata('previliges_menu', 'row_array', $t['id'], $id_role);
									$array[$k]['child_menu'][$y]['id_child'] = $t['id'];
									$array[$k]['child_menu'][$y]['nama_menu_child'] = $t['nama_menu'];
									$array[$k]['child_menu'][$y]['type_menu'] = $t['type_menu'];
									$array[$k]['child_menu'][$y]['id_prev'] = (isset($dataPrevChild['id']) ? $dataPrevChild['id'] : 0) ;
									$array[$k]['child_menu'][$y]['buat'] = (isset($dataPrevChild['buat']) ? $dataPrevChild['buat'] : 0) ;
									$array[$k]['child_menu'][$y]['baca'] = (isset($dataPrevChild['baca']) ? $dataPrevChild['baca'] : 0) ;
									$array[$k]['child_menu'][$y]['ubah'] = (isset($dataPrevChild['ubah']) ? $dataPrevChild['ubah'] : 0) ;
									$array[$k]['child_menu'][$y]['hapus'] = (isset($dataPrevChild['hapus']) ? $dataPrevChild['hapus'] : 0) ;
									$array[$k]['child_menu'][$y]['sharing_file'] = (isset($dataPrevChild['sharing_file']) ? $dataPrevChild['sharing_file'] : 0) ;
									$array[$k]['child_menu'][$y]['download_file'] = (isset($dataPrevChild['download_file']) ? $dataPrevChild['download_file'] : 0) ;
									$jml++;
									
									if($t['type_menu'] == 'CHC'){
										$array[$k]['child_menu'][$y]['sub_child_menu'] = array();
										$dataSubChild = $this->modelsx->getdata('menu_child_2', 'result_array', $t['id']);
										$jml_sub_child = 0;
										foreach($dataSubChild as $x => $z){
											$dataPrevSubChild = $this->modelsx->getdata('previliges_menu', 'row_array', $z['id'], $id_role);
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['id_sub_child'] = $z['id'];
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['nama_menu_sub_child'] = $z['nama_menu'];
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['id_prev'] = (isset($dataPrevSubChild['id']) ? $dataPrevSubChild['id'] : 0) ;
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['buat'] = (isset($dataPrevSubChild['buat']) ? $dataPrevSubChild['buat'] : 0) ;
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['baca'] = (isset($dataPrevSubChild['baca']) ? $dataPrevSubChild['baca'] : 0) ;
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['ubah'] = (isset($dataPrevSubChild['ubah']) ? $dataPrevSubChild['ubah'] : 0) ;
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['hapus'] = (isset($dataPrevSubChild['hapus']) ? $dataPrevSubChild['hapus'] : 0) ;
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['sharing_file'] = (isset($dataPrevSubChild['sharing_file']) ? $dataPrevSubChild['sharing_file'] : 0) ;
											$array[$k]['child_menu'][$y]['sub_child_menu'][$x]['download_file'] = (isset($dataPrevSubChild['download_file']) ? $dataPrevSubChild['download_file'] : 0) ;
											$jml_sub_child++;
										}
									}
								}
								$array[$k]['total_child'] = $jml;
							}
							
							/*
							echo "<pre>";
							print_r($array);
							exit;
							//*/
							
							$this->nsmarty->assign('role', $array);
							$this->nsmarty->assign('id_group', $id_role);
						break;
					}
				break;
			}
			
			$this->nsmarty->assign("main", $p1);
			$this->nsmarty->assign("mod", $p2);
			$temp = 'backend/modul/'.$p1.'/'.$p2.'.html';
			if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
			else{$this->nsmarty->display($temp);}	
		}
	}	
	
	function get_grid($mod){
		$temp = 'backend/modul/grid_config.html';
		$filter = $this->combo_option($mod);
		$this->nsmarty->assign('data_select', $filter);
		$this->nsmarty->assign('mod',$mod);
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
	}
	
	function get_form($mod){
		$temp='backend/modul/'.$mod."/main-form.html";
		$sts=$this->input->post('editstatus');
		$this->nsmarty->assign('sts',$sts);
		switch($mod){
			case "upload_file":
				$temp="backend/modul/management_file/main-form.html";
				if($sts=='edit'){
					$data = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('id')) )->row_array();
					$data["jangka_waktu"] = str_replace("-","/",$data["jangka_waktu_mulai"])." - ".str_replace("-","/",$data["jangka_waktu_akhir"]);
					$this->nsmarty->assign('data',$data);
					if($data['nama_file'] != null){
						$nama_area = $this->db->get_where("cl_area", array("id"=>$data['cl_area_id']) )->row_array();
						$nama_folder = str_replace(" ", "_", strtolower($nama_area["nama_area"]) );
						
						$target_path = "__repository/".$this->auth['cl_unit_kerja_id']."/".$nama_folder."/";
						$this->nsmarty->assign('filenya', $target_path.$data['nama_file']);
					}
					
					if($data['pengirim'] == 'Internal'){
						$this->nsmarty->assign("display_external", "display:none");
					}else{
						$this->nsmarty->assign("display_internal", "display:none");
					}
				}else{
					$this->nsmarty->assign("display_external", "display:none");
					$this->nsmarty->assign("display_internal", "display:none");
				}
				
				$this->nsmarty->assign("area", $this->lib->fillcombo('cl_area', 'return', ($sts == 'edit' ? $data['cl_area_id'] : "") ) );
				$this->nsmarty->assign("jenis_dokumen", $this->lib->fillcombo('cl_jenis_dokumen', 'return', ($sts == 'edit' ? $data['cl_jenis_dokumen_id'] : "") ) );
				$this->nsmarty->assign("pengirim", $this->lib->fillcombo('pengirim', 'return', ($sts == 'edit' ? $data['pengirim'] : "") ) );
				$this->nsmarty->assign("unit_kerja", $this->lib->fillcombo('cl_unit_kerja', 'return', ($sts == 'edit' ? $data['pengirim_internal_unit_kerja'] : "") ) );
			break;
			case "mapping":
				$temp='backend/modul/user_management/mapping-form.html';
				if($sts=='edit'){
					$data = $this->db->get_where('tbl_ldap_group', array('id'=>$this->input->post('id')) )->row_array();
					$this->nsmarty->assign('data',$data);
				}
				$unit=$this->modelsx->getdata('cl_unit_kerja','result_array');
				$group=$this->modelsx->getdata('cl_group_user','result_array');
				$this->nsmarty->assign('group',$group);
				$this->nsmarty->assign('unit',$unit);
			break;
			case "user_mng":
				$temp='backend/modul/user_management/user_mng-form.html';
				if($sts=='edit'){
					$data = $this->db->get_where('tbl_user', array('id'=>$this->input->post('id')) )->row_array();
					$this->nsmarty->assign('data',$data);
				}
				$unit=$this->modelsx->getdata('cl_unit_kerja','result_array');
				$group=$this->modelsx->getdata('cl_group_user','result_array');
				
				//print_r($group);exit;
				
				$this->nsmarty->assign('group',$group);
				$this->nsmarty->assign('unit',$unit);
			break;
			case "group":
				$temp='backend/modul/user_management/group-form.html';
				if($sts=='edit'){
					$data = $this->db->get_where('cl_group_user', array('id'=>$this->input->post('id')) )->row_array();
					$this->nsmarty->assign('data',$data);
				}
			break;
			case "unit":
				$temp='backend/modul/user_management/unit-form.html';
				if($sts=='edit'){
					$data = $this->db->get_where('cl_unit_kerja', array('id'=>$this->input->post('id')) )->row_array();
					$this->nsmarty->assign('data',$data);
				}
			break;
		}
		$this->nsmarty->assign('mod',$mod);
		$this->nsmarty->assign('temp',$temp);
		
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
		
	}	
	
	function get_report($mod){
		$temp="backend/modul/report/".$mod.".html";
		$this->nsmarty->assign('mod',$mod);
		switch($mod){	
			case "report_registrasi":
				$data=$this->modelsx->getdata('report_registrasi','result_array');
				$this->nsmarty->assign('data',$data);
			break;
		}
		$this->nsmarty->assign('temp',$temp);
		if(!file_exists($this->config->item('appl').APPPATH.'views/'.$temp)){$this->nsmarty->display('konstruksi.html');}
		else{$this->nsmarty->display($temp);}
	}
	
	function getdata($p1,$p2="",$p3=""){
		echo $this->modelsx->getdata($p1,'json',$p3);
	}
	
	function simpandata($p1="",$p2=""){
		//print_r($_POST);exit;
		if($this->input->post('mod'))$p1=$this->input->post('mod');
		$post = array();
        foreach($_POST as $k=>$v){
			if($this->input->post($k)!=""){
				$post[$k] = $this->input->post($k);
			}else{
				$post[$k] = null;
			}
			
		}
		if(isset($post['editstatus'])){$editstatus = $post['editstatus'];unset($post['editstatus']);}
		else $editstatus = $p2;
		
		echo $this->modelsx->simpandata($p1, $post, $editstatus);
	}
	
	function test(){
	}
	
	function combo_option($mod){
		$opt="";
		switch($mod){
			case "management_file":
				$opt .="<option value='A.room_type'>Nama Arsip</option>";
				$opt .="<option value='A.description'>No. Arsip</option>";
			break;
			case "mapping":
				$opt .="<option value='A.user_ldap'>User</option>";
				$opt .="<option value='B.group_user'>Group User</option>";
				$opt .="<option value='C.nama_unit'>Nama Unit</option>";
			break;
			case "user_mng":
				$opt .="<option value='A.nama_user'>User</option>";
				$opt .="<option value='B.group_user'>Group User</option>";
				$opt .="<option value='C.nama_unit'>Nama Unit</option>";
			break;
			case "unit":
				$opt .="<option value='A.nama_unit'>Unit Kerja</option>";
			break;
			case "group":
				$opt .="<option value='A.group_user'>Group User</option>";
			break;
			case "log":
				$opt .="<option value='aktivitas'>Aktivitas</option>";
			break;
		}
		return $opt;
	}
	
	function cetak(){
		$mod=$this->input->post('mod');
			switch($mod){
				case "cetak_bast":
					$data=$this->modelsx->getdata('get_bast');
					$tgl=$this->konversi_tgl(date('Y-m-d'));
					$file_name=$data['header']['konfirmasi_no'];
					$this->hasil_output('pdf',$mod,$data,$file_name,'BERITA ACARA SERAH TERIMA BUKU',$data['header']['konfirmasi_no'],$tgl);
				break;
			}
	}
	
	function hasil_output($p1,$mod,$data,$file_name,$judul_header,$nomor="",$param=""){
		switch($p1){
			case "pdf":
				$this->load->library('mlpdf');	
				//$data=$this->mhome->getdata('cetak_voucher');
				$pdf = $this->mlpdf->load();
				$this->nsmarty->assign('param', $param);
				$this->nsmarty->assign('judul_header', $judul_header);
				$this->nsmarty->assign('nomor', $nomor);
				$this->nsmarty->assign('data', $data);
				$this->nsmarty->assign('mod', $mod);
				
				$htmlcontent = $this->nsmarty->fetch("backend/template/temp_pdf.html");
				$htmlheader = $this->nsmarty->fetch("backend/template/header.html");
				
				//echo $htmlcontent;exit;
				
				$spdf = new mPDF('', 'A4', 0, '', 12.7, 12.7, 33, 20, 5, 2, 'P');
				$spdf->ignore_invalid_utf8 = true;
				// bukan sulap bukan sihir sim salabim jadi apa prok prok prok
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-1';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				$spdf->SetHTMLHeader($htmlheader);
				//$spdf->keep_table_proportions = true;
				$spdf->useSubstitutions=false;
				$spdf->simpleTables=true;
				
				$spdf->SetHTMLFooter('
					<div style="font-family:arial; font-size:8px; text-align:center; font-weight:bold;">
						<table width="100%" style="font-family:arial; font-size:8px;">
							<tr>
								<td width="30%" align="left">
									
								</td>
								<td width="40%" align="center">
									
								</td>
								<td width="30%" align="right">
									Hal. {PAGENO} dari {nbpg}
								</td>
							</tr>
						</table>
					</div>
				');				
				//$file_name = date('YmdHis');
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output('repositories/Dokumen_LS/LS_PDF/'.$filename.'.pdf', 'F'); // save to file because we can
				//$spdf->Output('repositories/Billing/'.$filename.'.pdf', 'F');
				$spdf->Output($file_name.'.pdf', 'I'); // view file	
			break;
		}
	}
	
	function get_chart(){
		$chart=array();
		$x=array();
		$y=array();
		$mod=$this->input->post('mod');
		
		//echo json_encode($tgl);exit;
		//print_r($tgl);exit;
		switch($mod){
			case "jml_file":
				$x['name']='File Upload';
				$x['colorByPoint']='true';
				$x['data']=array();
				$data = $this->modelsx->getdata('chart_file','result_array');
				$idx=0;
				foreach($data as $v=>$z){
					$x['data'][$idx]=array('name'=>$z['nama_unit'],'y'=>(float)$z['total']);
					//$x['data'][$idx]=array('name'=>$z['nama_unit'],'y'=>$z['total']);
					$idx++;
				}
				$chart['x']=array($x);
			break;
			case "space":
				$data=$this->lib->get_space_hardisk();
				$chart['name']="Space Dalam Hardisk";
				$chart['colorByPoint']=true;
				$chart['data']=array();
				//$chart['data'][]=array('name'=>'Total Space Hardisk','y'=>(float)$data['total_space']);
				$chart['data'][]=array('name'=>'Total Terpakai','y'=>(float)$data['space_pake']);
				$chart['data'][]=array('name'=>'Sisa Space Hardisk','y'=>(float)$data['free_space'],"sliced"=>true,"selected"=>true);
				echo json_encode(array($chart));exit; 
			break;
		}
		echo json_encode($chart);
	}
	
	function downloadfile(){
		$this->load->helper('download');
		$filenya = $this->input->post("filenya");
		force_download($filenya, NULL);
	}
	
	function tester(){
		print_r($this->auth);
	}
}

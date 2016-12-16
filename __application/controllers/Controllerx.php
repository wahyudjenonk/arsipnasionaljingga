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
			$this->nsmarty->display( 'backend/main-backend.html');
		}else{
			$this->nsmarty->display( 'backend/main-login.html');
		}
	}
	
	function modul($p1="",$p2=""){
		if($this->auth){
			switch($p1){
				case "preview_file":
					if($this->auth['cl_user_group_id'] == '1'){
						$getdata = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('idx')) )->row_array();
						$target_path = $this->host."__repository/".$getdata['cl_unit_kerja_id']."/";
					}else{
						$target_path = $this->host."__repository/".$this->auth['cl_unit_kerja_id']."/";
					}					
					
					$nama_file = $this->input->post('nama_file');
					$html = '
						<iframe src="'.$target_path.$nama_file.'" frameborder="0"  scrolling="no" width="100%" height="550"></iframe>
					';
					echo $html;
					exit;
				break;
				case "management_file":
					switch($p2){
						case "sharing_file":
							$data = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('id')) )->row_array();
							$unit = $this->modelsx->getdata('unit_sharing','result_array',$data['cl_unit_kerja_id'],$data['id']);
							$this->nsmarty->assign("data", $data);
							$this->nsmarty->assign("unit", $unit);
							//print_r($unit);
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
			case "management_file":
				if($sts=='edit'){
					$data = $this->db->get_where('tbl_upload_file', array('id'=>$this->input->post('id')) )->row_array();
					$this->nsmarty->assign('data',$data);
					if($data['nama_file'] != null){
						$target_path = "__repository/".$this->auth['cl_unit_kerja_id']."/";
						$this->nsmarty->assign('filenya', $target_path.$data['nama_file']);
					}
				}
				$this->nsmarty->assign("tipe_dokumen", $this->lib->fillcombo('tipe_dokumen', 'return', ($sts == 'edit' ? $data['tipe_dokumen'] : "") ) );
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
				$data=$this->modelsx->getdata('chart_file','result_array');
				$idx=0;
				foreach($data as $v=>$z){
					$x['data'][$idx]=array('name'=>$z['nama_unit'],'y'=>(float)$z['total']);
					$idx++;
				}
				$chart['x']=array($x);
			break;
			case "space":
				$data=$this->lib->get_space_hardisk();
				$chart['name']="Space Dalam Hardisk";
				$chart['colorByPoint']=true;
				$chart['data']=array();
				$chart['data'][]=array('name'=>'Total Space Hardisk','y'=>(float)$data['total_space']);
				$chart['data'][]=array('name'=>'Total Terpakai','y'=>(float)$data['space_pake']);
				$chart['data'][]=array('name'=>'Sisa Space Hardisk','y'=>(float)$data['free_space'],"sliced"=>true,"selected"=>true);
				echo json_encode(array($chart));exit; 
			break;
		}
		echo json_encode($chart);
	}
}

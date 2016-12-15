<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends JINGGA_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->library(array('encrypt','lib'));
	}
	
	public function index(){
		$user = $this->db->escape_str($this->input->post('user'));
		$pass = $this->db->escape_str($this->input->post('pwd'));
		$error=false;
		if($user && $pass){
			$cek_user = $this->modelsx->getdata('data_login', 'row_array', $user);
			//print_r($cek_user);exit;
			if(count($cek_user)>0){
				if(isset($cek_user['status']) && $cek_user['status']==1){
					if($pass == $this->encrypt->decode($cek_user['password'])){
						$this->session->set_userdata('44mpp3R4', base64_encode(serialize($cek_user)));
					}else{
						$error=true;
						$this->session->set_flashdata('error', 'Password Tidak Benar');
					}
				}else{
					$error=true;
					$this->session->set_flashdata('error', 'USER Sudah Tidak Aktif Lagi');
				}
			}else{
				//CEK LDAP
				$cek_ldap=$this->lib->get_ldap_user("data_ldap",$user,$pass);
				echo "<pre>";print_r($cek_ldap);exit;
				if($cek_ldap['msg']==1){
					$get_group=$this->modelsx->getdata('group_ldap', 'row_array', $user);
					//echo "<pre>"; print_r($get_group);exit;
					if(isset($get_group["cl_group_user_id"])){
						$data=array();
						$data["nama_user"]=$cek_ldap["data"][0]["samaccountname"];
						$data["nama_lengkap"]=$cek_ldap["data"][0]["samaccountname"];
						$data["email"]=$cek_ldap["data"][0]["userprincipalname"];
						$data["cl_user_group_id"]=$get_group["cl_group_user_id"];
						//$data["nama_unit"]=$cek_ldap["data"][0]["memberof"];
						$data["nama_unit"]='LDAP Unit';
						$this->session->set_userdata('44mpp3R4', base64_encode(serialize($data)));
					}else{
						$error=true;
						$this->session->set_flashdata('error', 'User LDAP Belum Terdaftar Dalam Group');
					}
					
				}elseif($cek_ldap['msg']==2){
					$error=true;
					$this->session->set_flashdata('error', 'LDAP Server Tidak Terkoneksi');
				}else{
					$error=true;
					$this->session->set_flashdata('error', 'User Tidak Terdaftar');
				}
			}
		}else{
			$error=true;
			$this->session->set_flashdata('error', 'Isi User Dan Password');
		}
		header("Location: " . $this->host ."backoffice");
	
		
	}
	
	function logout(){
		//$log = $this->db->update('tbl_user', array('last_log_date'=>date('Y-m-d')), array('nama_user'=>$this->auth['nama_user']) );
		//if($log){
			$this->session->unset_userdata('44mpp3R4', 'limit');
			$this->session->sess_destroy();
			header("Location: " . $this->host ."backoffice");
		//}
	}
	
}

<?php if (!defined('BASEPATH')) exit('No direct script allowed');

/**
 * @OA\Schema(schema="user")
 */
class M_user extends CI_Model
{
	function get_user($q)
	{
		return $this->db->get_where('v_active_user', $q);
	}

	function login($username, $pasword, $deviceId){
		//hash the password with md5
		$pasword = md5($pasword);

		//user can login with email or nip
		$this->db->where('nip', $username);
		$this->db->or_where('email', $username);
		$this->db->where('password', $pasword);
		$query = $this->db->get('v_active_user');

		//check if there is a user count 0
		if ($query->num_rows() == 0) {
			throw new Exception("user tidak ditemukan");
		}

		//if there is a user
		if ($query->num_rows() > 1) {
			throw new Exception("ada user yang sama");
		}

		//get user data from database
		$row = $query->row();

		//check if device id match or add this deviceid if null
		if ($row->device_id == null) {
			$this->db->where('id_user', $row->id_user);
			$this->db->update('sys_user', array('device_id' => $deviceId));
		} else if ($row->device_id != $deviceId) {
			throw new Exception("device tidak cocok, harap hubungi admin");
		}
		
		//return user
		return $row;
	}

	function activate($nip, $nama, $tglLahir, $tglMulaiKerja, $password, $deviceId ){
		//get user data from database
		$this->db->where('nip', $nip);
		$query = $this->db->get('v_active_user');

		//check if there is a user count 0
		if ($query->num_rows() == 0) {
			//datapkan data pegawai dari mst_pegawai
			$this->db->where('nip', $nip);
			$this->db->where('nama_pegawai', $nama);
			$this->db->where('tgl_lahir', $tglLahir);
			$this->db->where('tgl_mulai_kerja', $tglMulaiKerja);
			$query = $this->db->get('mst_pegawai');
			if ($query->num_rows() == 1) {
				$row = $query->row();
				//insert data ke sys_user
				$this->db->insert('sys_user', array(
					"id_user_level" => "0", 
					"id_pegawai" => $row->id_pegawai,
					"nip" => $row->nip, 
					"nama" => $row->nama_pegawai,
					"password" => md5($password), 
					"is_aktif" => "y",
					"device_id" => $deviceId
				));
				//get user data from database
				$this->db->where('nip', $nip);
				$this->db->where('password', md5($password));
				$query = $this->db->get('v_active_user');
				$row = $query->row();
				return $row;
			} else {
				throw new Exception("data epgawai tidak ditemukan");
			}
		}else if($query->num_rows() == 1){
			//update sys_user
			$row = $query->row();
			$this->db->where('id_user', $row->id_user);
			$this->db->update('sys_user', array('password' => md5($password)));
			//get user data from database
			$this->db->where('nip', $nip);
			$this->db->where('password', md5($password));
			$query = $this->db->get('v_active_user');
			$row = $query->row();
			return $row;
		}

		//if there is a user
		if ($query->num_rows() > 1) {
			throw new Exception("ada user yang sama");
		}

		//get user data from database
		$row = $query->row();

		//check if device id match or add this deviceid if null
		if ($row->tgl_lahir != $tglLahir) {
			throw new Exception("tanggal lahir tidak cocok");
		} else if ($row->tgl_mulai_kerja != $tglMulaiKerja) {
			throw new Exception("tanggal mulai kerja tidak cocok");
		}
		
		//return user
		return $row;
	}
}

/**
 * @OA\Schema(schema="AktivasiModel")
 */
class AktivasiModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var String
     */
    public $nip;

    /**
     * @OA\Property()
     * @var String
     */
    public $nama;

    /**
     * @OA\Property()
     * @var String
     */
    public $tglLahir;

	/**
     * @OA\Property()
     * @var String
     */
    public $tglMulaiKerja;

	/**
     * @OA\Property()
     * @var String
     */
    public $password;

}

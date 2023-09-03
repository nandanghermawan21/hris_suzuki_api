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
}

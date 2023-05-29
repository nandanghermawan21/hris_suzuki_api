<?php if (!defined('BASEPATH')) exit('No direct script allowed');

/**
 * @OA\Schema(schema="user")
 */
class M_user extends CI_Model
{
	function get_user($q)
	{
		return $this->db->get_where('sys_user', $q);
	}
}

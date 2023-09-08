<?php if (!defined('BASEPATH')) exit('No direct script allowed');

/**
 * @OA\Schema(schema="AttendanceModel")
 */
class AttendanceModel extends CI_Model
{
    // ,[id_pegawai]
    // ,[id_attendance]
    // ,[id_lokasi_fisik]
    // ,[lat]
    // ,[lon]
    // ,[created_date]
    // ,[created_by]

	/**
     * @OA\Property()
     * @var int
     */
    public $id_pegawai;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_attendance;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_lokasi_fisik;

    /**
     * @OA\Property()
     * @var double
     */
    public $lat;

    /**
     * @OA\Property()
     * @var double
     */
    public $lon;

    /**
     * @OA\Property()
     * @var string
    */
    public $address;

    /**
     * @OA\Property()
     * @var string
     */
    public $created_date;

    /**
     * @OA\Property()
     * @var string
     */
    public $created_by;


    //crete function get v_kehadiran
    function get_kehadiran($q)
    {
        return $this->db->get_where('v_kehadiran', $q);
    }

    //create function get v_kehadiran berdasarkan id_pegawai
    function get_kehadiran_by_id_pegawai($id_pegawai, $bulan, $tahun)
    {
        $this->db->where('pegawai_id', $id_pegawai);
        //add filter berdasarkan bulan saat ini
        $this->db->where("MONTH(tanggal) = ", $bulan ?? date('m'));
        //add filter berdasarkan tahun saat ini
        $this->db->where("YEAR(tanggal) = ", $tahun ?? date('Y'));
        $this->db->order_by('tanggal', 'desc');
        $query = $this->db->get('v_kehadiran');

        //return result
        return $query->result();
    }

    //create function get v_kehadiran berdasarkan id_pegawai
    function get_kehadiran_bawahan_by_date($bawahan, $tanggal)
    {
        $this->db->where_in('pegawai_id', $bawahan);
        $this->db->where('tanggal', $tanggal);
        $query = $this->db->get('v_kehadiran');

        //return result
        return $query->result();
    }

    //create function get detail chek in chek out berdasarkan id_pegawai dant tanggal
    function get_approval_kehadiran($idPegawai, $tanggal)
    {
        $this->db->where('id_pegawai', $idPegawai);
        $this->db->where("CONVERT(date, created_date) = ", $tanggal);
        $query = $this->db->get('v_approval_kehadiran');

        //return result
        return $query->result();
    }

    //reject kehadian
    function reject_kehadiran($id, $reason, $idPegawai, $timezone)
    {
        //get trx_attendance by id
        $this->db->where('id_trx', $id);
        $query = $this->db->get('trx_attendance');

        //hitung jumlah data yang diperoleh
        $count = $query->num_rows();

        //check apakah create_date sudah lebih dari 35 hari
        $row = $query->row();
        $create_date = $row->created_date;
        $date1 = new DateTime($create_date);
        $date2 = new DateTime(date('Y-m-d H:i:s'));
        $diff = $date1->diff($date2);
        $diffDays = $diff->days;

        if($diffDays > 35){
            throw new Exception("data tidak dapat diubah karena sudah lebih dari 35 hari");
        }

        //jika data kurang atau sama dengan 0 maka throw exception
        if ($count <= 0) {
            throw new Exception("data tidak ditemukan");
        }

        try {
            //update data trx_attendance by id
            $this->db->where('id_trx', $id);
            $this->db->update('trx_attendance', array('approval_status' => 0, 'approval_reason' => $reason, 'approved_by' => $idPegawai, 'approved_date' => date('Y-m-d H:i:s'), 'approved_time_zone' => $timezone));

            //kembalikan data yang diupdate dari view v_approval_kehadiran
            $this->db->where('id_attendance', $id);
            $query = $this->db->get('v_approval_kehadiran');
            return $query->row();

        } catch (\Throwable $th) {
            throw $th;
        }

    }

}

/**
 * @OA\Schema(schema="AttendanceCheckInModel")
 */
class AttendanceCheckInModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var double
     */
    public $lat;

    /**
     * @OA\Property()
     * @var double
     */
    public $lon;

    /**
     * @OA\Property()
     * @var string
    */
    public $address;

}

/**
 * @OA\Schema(schema="AttendanceRejectModel")
 */
class AttendanceRejectModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var double
     */
    public $attendanceId;

    /**
     * @OA\Property()
     * @var String
     */
    public $reason;

}

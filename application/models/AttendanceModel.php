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

    //daftar izin ayng tersedia
    function daftar_izin_tersedia($idPegawai){
        try {
            $query = $this->db->query("EXEC spDaftarIzinTersediaKayawan $idPegawai");
            return $query->result();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //daftar izin ayng tersedia
    function daftar_cuti_tersedia($idPegawai){
        try {
            $query = $this->db->query("EXEC spDaftarJatahCutiKayawan $idPegawai");
            return $query->result();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //submit izin
    function submit_izin($idPegawai, $idAtendance, $dates, $reason, $attachment, $timezone, $timezoneName){
        try {
            // Memulai transaksi
            $this->db->trans_start();

            //insert data ke table trx_hrd_leave
            $trxLeave = $this->db->insert('trx_hrd_leave', 
                array('id_pegawai' => $idPegawai, 
                      'id_attendance' => $idAtendance, 
                      'reason' => $reason, 
                      'attachment' => $attachment,
                      'tgl_pengajuan' => date('Y-m-d H:i:s'), 
                      'tgl_pengajuan_timezone' => $timezone,
                      'tgl_pengajuan_timezone_name' => $timezoneName,
                      'status' => 'Menunggu Persetujuan',
                      'created_by' => $idPegawai
                     ));
            
            $newId = $this->db->insert_id();
            
            //loop data $dates
            for ($i = 0; $i < count($dates); $i++) {
               //insert into table trx_hrd_leave_dates
                $trxLeaveDates = $this->db->insert('trx_hrd_leave_date', 
                      array('id_leave' => $newId, 
                            'tgl_leave' => $dates[$i], 
                             ));
            }

            // Menyelesaikan transaksi (commit) jika berhasil atau membatalkan (rollback) jika gagal
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                throw new Exception("Gagal menyimpan data izin");
            } else {
                $this->db->trans_complete();
            }

        } catch (\Throwable $th) {
            throw $th;
        }
    }

     //submit izin
     function submit_cuti($idPegawai, $idAllowance, $idAtendance, $dates, $reason, $attachment, $timezone, $timezoneName){
        try {
            // Memulai transaksi
            $this->db->trans_start();

            //insert data ke table trx_hrd_leave
            $trxLeave = $this->db->insert('trx_hrd_leave', 
                array('id_pegawai' => $idPegawai, 
                      'allowence_id' => $idAllowance,
                      'id_attendance' => $idAtendance, 
                      'reason' => $reason, 
                      'attachment' => $attachment,
                      'tgl_pengajuan' => date('Y-m-d H:i:s'), 
                      'tgl_pengajuan_timezone' => $timezone,
                      'tgl_pengajuan_timezone_name' => $timezoneName,
                      'status' => 'Menunggu Persetujuan',
                      'created_by' => $idPegawai
                     ));
            
            $newId = $this->db->insert_id();
            
            //loop data $dates
            for ($i = 0; $i < count($dates); $i++) {
               //insert into table trx_hrd_leave_dates
                $trxLeaveDates = $this->db->insert('trx_hrd_leave_date', 
                      array('id_leave' => $newId, 
                            'tgl_leave' => $dates[$i], 
                             ));
            }

            // Menyelesaikan transaksi (commit) jika berhasil atau membatalkan (rollback) jika gagal
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                throw new Exception("Gagal menyimpan data izin");
            } else {
                $this->db->trans_complete();
            }

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //get data leave saya
    function get_my_leave($idPegawai, $skip, $take){
        try {
            $this->db->where('id_pegawai', $idPegawai);
            $this->db->order_by('tgl_pengajuan', 'desc');
            $query = $this->db->get('v_approval_leave', $take, $skip);
            return $query->result();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //get data leave bawahan
    function get_bawahan_leave($idAtasan, $skip, $take){
        try {
            $this->db->where_in('id_atasan', $idAtasan);
            $this->db->order_by('tgl_pengajuan', 'desc');
            $query = $this->db->get('v_approval_leave', $take, $skip);
            return $query->result();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //approval leave
    function approval_leave($id, $idPegawai, $status, $reason, $timezone, $timezoneName){
        try {
            //get trx_hrd_leave by id
            $this->db->where('id_leave', $id);
            $query = $this->db->get('trx_hrd_leave');

            //hitung jumlah data yang diperoleh
            $count = $query->num_rows();

            //jika data kurang atau sama dengan 0 maka throw exception
            if ($count <= 0) {
                throw new Exception("data tidak ditemukan");
            }

            //update data trx_hrd_leave by id
            $this->db->where('id_leave', $id);
            $this->db->update('trx_hrd_leave', array('status' => $status, 'approved_by' => $idPegawai, 'approved_reason' => $reason, 'approved_date' => date('Y-m-d H:i:s'), 'approved_timezone' => $timezone, 'approved_timezone_name' => $timezoneName));

            //kembalikan data yang diupdate dari view v_approval_leave
            $this->db->where('id', $id);
            $query = $this->db->get('v_approval_leave');
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

/**
 * @OA\Schema(schema="LeaveApprovalModel")
 */
class LeaveApprovalModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var double
     */
    public $leaveId;

    /**
     * @OA\Property()
     * @var boolean
     */
    public $accept;

    /**
     * @OA\Property()
     * @var String
     */
    public $reason;

}

/**
 * @OA\Schema(schema="CategoryAttendanceModel")
 */
class CategoryAttendanceModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $id_attendance;

    /**
     * @OA\Property()
     * @var String
     */
    public $kode_attendance;

    /**
     * @OA\Property()
     * @var String
     */
    public $attendance;

    /**
     * @OA\Property()
     * @var String
     */
    public $type;

    /**
     * @OA\Property()
     * @var integer
     */
    public $count;

    /**
     * @OA\Property()
     * @var String
     */
    public $sex;

    /**
     * @OA\Property()
     * @var String
     */
    public $picker_date_mode;

}

/**
 * @OA\Schema(schema="AttendanceLeaveModel")
 */
class AttendanceLeaveModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var integer
     */
    public $idAttendance; // Perbaiki penamaan field

    /**
     * @OA\Property()
     * @var string // Gunakan "string" (huruf kecil) untuk tipe data string
     */
    public $reason;

    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(type="string")
     * )
     * @var array // Gunakan tipe data yang sesuai di dalam array
     */
    public $dates;

    /**
     * @OA\Property()
     * @var string // Gunakan "string" (huruf kecil) untuk tipe data string
     */
    public $attachment;

}

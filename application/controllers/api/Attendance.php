<?php

defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

/**
 * @OA\Info(title="HRIS Suzuki API", version="0.1")
 * servers:
 */
class Attendance extends BD_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->auth();
        $this->load->model('M_user', 'user');
        $this->load->model('PegawaiModel', 'pegawai');
        //load attendance model
        $this->load->model('AttendanceModel', 'attendance');
        //load pegawai model
        $this->load->model('PegawaiModel', 'pegawai');
    }

    /**
     * @OA\Post(path="/api/attendance/checkin",tags={"Attendance"},
     *     @OA\RequestBody(
     *     @OA\MediaType(
     *         mediaType="applications/json",
     *         @OA\Schema(ref="#/components/schemas/AttendanceCheckInModel")
     *       ),
     *     ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/user")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function checkin_post()
    {
        //get device id
        $device_id = $this->input->get_request_header('Device-Id', TRUE);
        $timezone = $this->input->get_request_header('TimeZone', TRUE);
        $timezoneName = $this->input->get_request_header('TimeZoneName', TRUE);

        //getuserInfo
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response(array('status' => 'failed', 'message' => 'User not found'), 404);
        }

        //cehcek device id match
        if ($userDetail->device_id != $device_id) {
            $this->response('device tidak cocok, harap hubungi admin', 500);
        }

        //decode json from request body
        $json = json_decode(file_get_contents('php://input'), true);

        //chekin
        try {
            //dapatkan jarak dengan loaksi fisik

            try{
                $lokasiFisik = $this->pegawai->get_distance($userDetail->id_pegawai, $json['lat'], $json['lon']);
            }catch(\Throwable $th){
                $this->response($th->getMessage(), 500);
            }


            //validasi kebijakan jarak absen
            if ($lokasiFisik->distance > $lokasiFisik->radius) {
                $this->response('anda berjarak ' .  number_format(round($lokasiFisik->distance), 0, ',', '.') . ' meter, jauh dari lokasi absen', 500);
            } else {
                $data = array(
                    'id_pegawai' => $userDetail->id_pegawai,
                    'id_lokasi_fisik' => $lokasiFisik->lokasiFisikIdActual,
                    'code_attendance' => "MCI",
                    'lat' => $json['lat'],
                    'lon' => $json['lon'],
                    'address' => $json['address'],
                    'created_date' => date('Y-m-d H:i:s'),
                    'created_time_zone' => $timezone,
                    'created_by' => $userDetail->id_user,
                    'attendance_image' => $json['image'],
                );

                $attendance = $this->db->insert('trx_attendance', $data);
                $data['distance'] = $lokasiFisik->distance;
            }

            $this->response($data, 200);
        } catch (\Throwable $th) {
            $this->response(array('status' => 'failed', 'message' => $th->getMessage()), 500);
        }

        //retun userx
    }

    /**
     * @OA\Post(path="/api/attendance/checkout",tags={"Attendance"},
     *     @OA\RequestBody(
     *     @OA\MediaType(
     *         mediaType="applications/json",
     *         @OA\Schema(ref="#/components/schemas/AttendanceCheckInModel")
     *       ),
     *     ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/user")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function checkout_post()
    {
        //get device id
        $device_id = $this->input->get_request_header('Device-Id', TRUE);
        $timezone = $this->input->get_request_header('TimeZone', TRUE);
        $timezoneName = $this->input->get_request_header('TimeZoneName', TRUE);

        //getuserInfo
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response(array('status' => 'failed', 'message' => 'User not found'), 404);
        }

        //cehcek device id match
        if ($userDetail->device_id != $device_id) {
            $this->response('device tidak cocok, harap hubungi admin', 500);
        }

        //decode json from request body
        $json = json_decode(file_get_contents('php://input'), true);

        //chekin
        try {
            try{
                $lokasiFisik = $this->pegawai->get_distance($userDetail->id_pegawai, $json['lat'], $json['lon']);
            }catch(\Throwable $th){
                $this->response($th->getMessage(), 500);
            }

            if ($lokasiFisik->distance > $lokasiFisik->radius) {
                $this->response('anda berjarak ' .  number_format(round($lokasiFisik->distance), 0, ',', '.') . ' meter, jauh dari lokasi absen', 500);
            } else {

                $data = array(
                    'id_pegawai' => $userDetail->id_pegawai,
                    'id_lokasi_fisik' => $lokasiFisik->lokasiFisikIdActual,
                    'code_attendance' => "MCO",
                    'lat' => $json['lat'],
                    'lon' => $json['lon'],
                    'address' => $json['address'],
                    'created_date' => date('Y-m-d H:i:s'),
                    'created_time_zone' => $timezone,
                    'created_by' => $userDetail->id_user,
                    'attendance_image' => $json['image'],
                );

                $attendance = $this->db->insert('trx_attendance', $data);
                $data['distance'] = $lokasiFisik->distance;
            }

            $this->response($data, 200);
        } catch (\Throwable $th) {
            $this->response(array('status' => 'failed', 'message' => $th->getMessage()), 500);
        }

        //retun userx
    }

    /**
     * @OA\GET(path="/api/attendance/kehadiransaya",tags={"Attendance"},
     *   @OA\Parameter(
     *     name="bulan",
     *     in="query",
     *     description="bulan kehadiran",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="tahun",
     *     in="query",
     *     description="tahun kehadiran",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/user")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function kehadiransaya_get(){
        //dapatkan input
        $bulan = $this->input->get('bulan');
        $tahun = $this->input->get('tahun');

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        //get kehadiran saya using attendance model
        try {
            $kehadiran = $this->attendance->get_kehadiran_by_id_pegawai($userDetail->id_pegawai, $bulan, $tahun);
            $this->response($kehadiran, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    } 

    /**
     * @OA\GET(path="/api/attendance/kehadiranpegawai",tags={"Attendance"},
     *   @OA\Parameter(
     *     name="tanggal",
     *     in="query",
     *     description="tanggal kehadiran",
     *     required=true,
     *     @OA\Schema(type="string", format="date")
     *   ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/user")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function kehadiranpegawai_get(){
        //get param
        $tanggal = $this->input->get('tanggal');

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        //get daftar bawahan
        try {
       
        $bawahan = $this->pegawai->get_bawahan($userDetail->id_pegawai);
      
        //collect id pegawai to array
        $idPegawai = array();
        foreach ($bawahan as $key => $value) {
            array_push($idPegawai, $value->id_bawahan);
        }

        //check apakah dia memiliki bawahan
        if(count($idPegawai) == 0){
            $this->response(array('Anda tidak memiliki pegawai'), 404);
        }

        //get kehadiran bawahan using attendance model
        $kehadiran = $this->attendance->get_kehadiran_bawahan_by_date($idPegawai, $tanggal);

        $this->response($kehadiran, 200); 

        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(path="/api/attendance/approvalKehadiran",tags={"Attendance"},
     *   @OA\Parameter(
     *     name="idPegawai",
     *     in="query",
     *     description="idPegawai",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="tanggal",
     *     in="query",
     *     description="tanggal",
     *     required=true,
     *     @OA\Schema(type="string", format="date")
     *   ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/user")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function approvalKehadiran_get(){
        //get param
        $idPegawai = $this->input->get('idPegawai');
        $tanggal = $this->input->get('tanggal');

        //get user from jwt token
        $user = $this->user_data;

        //get detail check in check out
        try {
            $detailCheckInCheckOut = $this->attendance->get_approval_kehadiran($idPegawai, $tanggal);
            $this->response($detailCheckInCheckOut, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\POST(path="/api/attendance/rejectKehadiran",tags={"Attendance"},
     *     @OA\RequestBody(
     *     @OA\MediaType(
     *         mediaType="applications/json",
     *         @OA\Schema(ref="#/components/schemas/AttendanceRejectModel")
     *       ),
     *     ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/user")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function rejectKehadiran_post(){
         //get device id
         $device_id = $this->input->get_request_header('Device-Id', TRUE);
         $timezone = $this->input->get_request_header('TimeZone', TRUE);
         $timezoneName = $this->input->get_request_header('TimeZoneName', TRUE);

        //get post data
        $json = json_decode(file_get_contents('php://input'), true);

        $attendanceId = $json['attendanceId'];
        $reason = $json['reason'];

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }
        
        //call reject kehadiran dari attendance model
        try {
            $result = $this->attendance->reject_kehadiran($attendanceId, $reason, $userDetail->id_pegawai, $timezone);
            $this->response($result, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }

    }

    /**
     * @OA\GET(path="/api/attendance/getIzinTersedia",tags={"Attendance"},
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/CategoryAttendanceModel")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function getIzinTersedia_get(){
        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        //get daftar izin tersedia
        try {
            $daftarIzinTersedia = $this->attendance->daftar_izin_tersedia($userDetail->id_pegawai);
            $this->response($daftarIzinTersedia, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(path="/api/attendance/getCutiTersedia",tags={"Attendance"},
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/CategoryAttendanceModel")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function getCutiTersedia_get(){
        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        //get daftar izin tersedia
        try {
            $daftarIzinTersedia = $this->attendance->daftar_cuti_tersedia($userDetail->id_pegawai);
            $this->response($daftarIzinTersedia, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\POST(path="/api/attendance/submitIzin",tags={"Attendance"},
     *     @OA\RequestBody(
     *     @OA\MediaType(
     *         mediaType="applications/json",
     *         @OA\Schema(ref="#/components/schemas/AttendanceLeaveModel")
     *       ),
     *     ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/AttendanceLeaveModel")
     *     ),
     *   ),
     * @OA\Parameter(
     *     name="Device-Id",
     *     in="header",
     *     required=true,
     *     @OA\Schema(
     *         type="string"
     *     ),
     *     description="Device ID"
     *  ),
     *   security={{"token": {}}},
     * )
     */
    public function submitIzin_post(){
        //get device id
        $device_id = $this->input->get_request_header('Device-Id', TRUE);
        $timezone = $this->input->get_request_header('TimeZone', TRUE);
        $timezoneName = $this->input->get_request_header('TimeZoneName', TRUE);

        //get post data
        $json = json_decode(file_get_contents('php://input'), true);

        $idAttendace = $json['idAttendance'];
        $reason = $json['reason'];
        $dates = $json['dates'];
        $attachment = $json['attachment'];

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        // //cehcek device id match
        // if ($userDetail->device_id != $device_id) {
        //     $this->response('device tidak cocok, harap hubungi admin', 500);
        // }

        //call submit izin dari attendance model
        try {
            $result = $this->attendance->submit_izin($userDetail->id_pegawai, $idAttendace, $dates, $reason, $attachment, $timezone, $timezoneName);
            $this->response("{success:true}", 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\POST(path="/api/attendance/submitCuti",tags={"Attendance"},
     *     @OA\RequestBody(
     *     @OA\MediaType(
     *         mediaType="applications/json",
     *         @OA\Schema(ref="#/components/schemas/AttendanceLeaveModel")
     *       ),
     *     ),
     *   @OA\Response(response=200,
     *     description="basic user info",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/AttendanceLeaveModel")
     *     ),
     *   ),
     * @OA\Parameter(
     *     name="Device-Id",
     *     in="header",
     *     required=true,
     *     @OA\Schema(
     *         type="string"
     *     ),
     *     description="Device ID"
     *  ),
     *   security={{"token": {}}},
     * )
     */
    public function submitCuti_post(){
        //get device id
        $device_id = $this->input->get_request_header('Device-Id', TRUE);
        $timezone = $this->input->get_request_header('TimeZone', TRUE);
        $timezoneName = $this->input->get_request_header('TimeZoneName', TRUE);

        //get post data
        $json = json_decode(file_get_contents('php://input'), true);

        $idAllowance = $json['idAllowance'];
        $idAttendace = $json['idAttendance'];
        $reason = $json['reason'];
        $dates = $json['dates'];
        $attachment = $json['attachment'];

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        // //cehcek device id match
        // if ($userDetail->device_id != $device_id) {
        //     $this->response('device tidak cocok, harap hubungi admin', 500);
        // }

        //call submit izin dari attendance model
        try {
            $result = $this->attendance->submit_cuti($userDetail->id_pegawai, $idAllowance, $idAttendace, $dates, $reason, $attachment, $timezone, $timezoneName);
            $this->response("{success:true}", 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(path="/api/attendance/myLeave",tags={"Attendance"},
     *   @OA\Parameter(
     *     name="skip",
     *     in="query",
     *     description="skip",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="take",
     *     in="query",
     *     description="take",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200,
     *     description="leave info",
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function myLeave_get(){
        //get param
        $skip = $this->input->get('skip');
        $take = $this->input->get('take');

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        //get my leave
        try {
            $myLeave = $this->attendance->get_my_leave($userDetail->id_pegawai, $skip, $take);
            $this->response($myLeave, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\GET(path="/api/attendance/bawahanLeave",tags={"Attendance"},
     *   @OA\Parameter(
     *     name="skip",
     *     in="query",
     *     description="skip",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="take",
     *     in="query",
     *     description="take",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200,
     *     description="leave info",
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function bawahanLeave_get(){
        //get param
        $skip = $this->input->get('skip');
        $take = $this->input->get('take');

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response("user tidak ditemukan", 500);
        }

        //get my leave
        try {
            $bawahanLeave = $this->attendance->get_bawahan_leave($userDetail->id_pegawai, $skip, $take);
            $this->response($bawahanLeave, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }

    /**
     * @OA\POST(path="/api/attendance/approvalLeave",tags={"Attendance"},
     *     @OA\RequestBody(
     *     @OA\MediaType(
     *         mediaType="applications/json",
     *         @OA\Schema(ref="#/components/schemas/LeaveApprovalModel")
     *       ),
     *     ),
     *   @OA\Response(response=200,
     *     description="leave info",
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function approvalLeave_post(){
        //get header data
        $device_id = $this->input->get_request_header('Device-Id', TRUE);
        $timezone = $this->input->get_request_header('TimeZone', TRUE);
        $timezoneName = $this->input->get_request_header('TimeZoneName', TRUE);

        //get post data
        $json = json_decode(file_get_contents('php://input'), true);

        $idLeave = $json['leaveId'];
        $status = $json['accept'] == true ? "Disetujui" : "Ditolak";
        $reason = $json['reason'];

        //get user from jwt token
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username
        
        //call approval leave dari attendance model
        try {
            $result = $this->attendance->approval_leave($idLeave, $userDetail->id_pegawai, $status, $reason, $timezone, $timezoneName);
            $this->response($result, 200);
        } catch (\Throwable $th) {
            $this->response($th->getMessage(), 500);
        }
    }
}

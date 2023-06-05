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
        //getuserInfo
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response(array('status' => 'failed', 'message' => 'User not found'), 404);
        }

        //decode json from request body
        $json = json_decode(file_get_contents('php://input'), true);

        //chekin
        try {
            $data = array(
                'id_pegawai' => $userDetail->id_pegawai,
                'id_lokasi_fisik' => 1,
                'code_attendance' => "MCI",
                'lat' => $json['lat'],
                'lon' => $json['lon'],
                'created_date' => date('Y-m-d H:i:s'),
                'created_by' => $userDetail->id_user
            );
    
            $attendance = $this->db->insert('trx_attendance', $data);

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
        //getuserInfo
        $user = $this->user_data;

        //get detail; data user
        $q = array('id_user' => $user->id); //For where query condition
        $userDetail = $this->user->get_user($q)->row(); //Model to get single data row from database base on username

        //check if user already exist
        if ($userDetail == null) {
            $this->response(array('status' => 'failed', 'message' => 'User not found'), 404);
        }

        //decode json from request body
        $json = json_decode(file_get_contents('php://input'), true);

        //chekin
        try {
            $data = array(
                'id_pegawai' => $userDetail->id_pegawai,
                'id_lokasi_fisik' => 1,
                'code_attendance' => "MCO",
                'lat' => $json['lat'],
                'lon' => $json['lon'],
                'created_date' => date('Y-m-d H:i:s'),
                'created_by' => $userDetail->id_user
            );
    
            $attendance = $this->db->insert('trx_attendance', $data);

             $this->response($data, 200);

        } catch (\Throwable $th) {
            $this->response(array('status' => 'failed', 'message' => $th->getMessage()), 500);
        }

        //retun userx
    }
}
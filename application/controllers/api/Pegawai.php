<?php

defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

/**
 * @OA\Info(title="HRIS Suzuki API", version="0.1")
 * servers:
 */
class Pegawai extends BD_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->auth();
        $this->load->model('PegawaiModel', 'pegawai');
        $this->load->model('M_user', 'user');
    }


    /**
     * @OA\Get(path="/api/pegawai/myprofile",tags={"Pegawai"},
     *   @OA\Response(response=200,
     *     description="Profile Pegawai",
     *     @OA\JsonContent(
     *       @OA\Items(ref="#/components/schemas/PegawaiModel")
     *     ),
     *   ),
     *   security={{"token": {}}},
     * )
     */
    public function myprofile_get()
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

        //get pegawai
        $pegawai = $this->pegawai->get_pegawai(array('id_pegawai' => $userDetail->id_pegawai));

        if(count($pegawai) == 0){
            $this->response(array('Pegawai not found'), 404);
        }

        //retun data pegawai
        $this->response($pegawai[0], 200);
    }
}

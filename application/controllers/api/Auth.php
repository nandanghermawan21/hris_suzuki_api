<?php

defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

/**
 * @OA\Info(title="HRIS Suzuki API", version="0.1")
 * servers:
 */
class Auth extends BD_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->model('M_user', 'user');
    }

    /**
    * @OA\Post(path="/api/auth/login",tags={"Auth"},
    * @OA\RequestBody(
    *      @OA\MediaType(
    *          mediaType="multipart/form-data",
    *          @OA\Schema(
    *              @OA\Property(
    *                  property="username",
    *                  type="string",
    *                  description="username"
    *              ),
    *              @OA\Property(
    *                  property="password",
    *                  type="string",
    *                  description="password"
    *              )
    *          )
    *      )
    *  ),
    * @OA\Parameter(
    *     name="Device-Id",
    *     in="header",
    *     required=true,
    *     @OA\Schema(
    *         type="string"
    *     ),
    *     description="Device ID"
    *  ),
    * @OA\Response(response=200,
    *   description="basic user info",
    *   @OA\JsonContent(
    *     @OA\Items(ref="#/components/schemas/user")
    *   ),
    * ),
    * )
    */
    public function login_post()
    {
        $d =  $this->input->get_request_header('Device-Id');

        $u = $this->post('username'); //Username Posted
        $p = $this->post('password'); //Pasword Posted
        
        try {
            $user = $this->user->login($u, $p, $d);

            $kunci = $this->config->item('thekey');
            $token['id'] = $user->id_user;  //From here
            $token['username'] = $u;
            $token['level'] = $user->id_user_level;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60 * 60 * 5; //To here is to generate token
            $output['token'] = JWT::encode($token, $kunci); //This is the output token

            //result the user
            $user->token = $output['token'];

            //set response
            $this->set_response($user, 200);

        } catch (\Throwable $th) {
            $this->set_response($th->getMessage(), 500);
        } 
    }

     /**
     * @OA\POST(path="/api/auth/activasi",tags={"Auth"},
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
    public function activasi_post()
    {
        $d = $this->input->get_request_header('Device-Id');

        //get post data
        $json = json_decode(file_get_contents('php://input'), true);

        $nip = $json['nip'];
        $nama = $json['nama'];
        $tglLahir = $json['tglLahir'];
        $tglMulaiKerja = $json['tglMulaiKerja'];
        $password = $json['password'];
        
        try {
            $user = $this->user->activate($nip, $nama, $tglLahir, $tglMulaiKerja, $password, $d);

            $kunci = $this->config->item('thekey');
            $token['id'] = $user->id_user;  //From here
            $token['username'] = $nip;
            $token['level'] = $user->id_user_level;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60 * 60 * 5; //To here is to generate token
            $output['token'] = JWT::encode($token, $kunci); //This is the output token

            //result the user
            $user->token = $output['token'];

            //set response
            $this->set_response($user, 200);

        } catch (\Throwable $th) {
            $this->set_response($th->getMessage(), 500);
        } 
    }
}

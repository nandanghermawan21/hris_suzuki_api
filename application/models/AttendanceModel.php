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
}

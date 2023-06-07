<?php if (!defined('BASEPATH')) exit('No direct script allowed');

/**
 * @OA\Schema(schema="PegawaiModel")
 */
class PegawaiModel extends CI_Model
{
    /**
     * @OA\Property()
     * @var int
     */
    public $id_pegawai;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_jabatan;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_divisi;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_dep;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_cabang;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_lokasi_fisik;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_lokasi_kerja;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_level_gol;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_homebase;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_lingkup_bisnis;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_status_kerja;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_agama;

    /**
     * @OA\Property()
     * @var String
     */
    public $nip;

    /**
     * @OA\Property()
     * @var String
     */
    public $nama_pegawai;

    /**
     * @OA\Property()
     * @var String
     */
    public $inisial;

    /**
     * @OA\Property()
     * @var String
     */
    public $warga_neg;

    /**
     * @OA\Property()
     * @var String
     */
    public $tempat_lahir;

    /**
     * @OA\Property()
     * @var String
     */
    public $tgl_lahir;

    /**
     * @OA\Property()
     * @var String
     */
    public $tgl_mulai_kerja;

    /**
     * @OA\Property()
     * @var String
     */
    public $tgl_mulai_kontrak;

    /**
     * @OA\Property()
     * @var String
     */
    public $tgl_akhir_kontrak;

    /**
     * @OA\Property()
     * @var String
     */
    public $tgl_pengangkatan;

    /**
     * @OA\Property()
     * @var String
     */
    public $tgl_berhenti_kerja;

    /**
     * @OA\Property()
     * @var String
     */
    public $masa_kerja;

    /**
     * @OA\Property()
     * @var String
     */
    public $group_masa_kerja;

    /**
     * @OA\Property()
     * @var int
     */
    public $umur;

    /**
     * @OA\Property()
     * @var String
     */
    public $group_umur;

    /**
     * @OA\Property()
     * @var String
     */
    public $jenis_kelamin;

    /**
     * @OA\Property()
     * @var String
     */
    public $pendidikan;

    /**
     * @OA\Property()
     * @var String
     */
    public $pendidikan_jurusan;

    /**
     * @OA\Property()
     * @var String
     */
    public $pendidikan_institusi;

    /**
     * @OA\Property()
     * @var String
     */
    public $jenjang_ojk;

    /**
     * @OA\Property()
     * @var String
     */
    public $status_perkawinan;

    /**
     * @OA\Property()
     * @var String
     */
    public $gol_darah;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_telp;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_hp;

    /**
     * @OA\Property()
     * @var String
     */
    public $email;

    /**
     * @OA\Property()
     * @var String
     */
    public $alamat;

    /**
     * @OA\Property()
     * @var String
     */
    public $rt;

    /**
     * @OA\Property()
     * @var String
     */
    public $rw;

    /**
     * @OA\Property()
     * @var String
     */
    public $kel;

    /**
     * @OA\Property()
     * @var String
     */
    public $kec;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_country;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_provinsi;

    /**
     * @OA\Property()
     * @var int
     */
    public $id_kota;

    /**
     * @OA\Property()
     * @var String
     */
    public $kodepos;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_rek_bank;

    /**
     * @OA\Property()
     * @var String
     */
    public $nama_rek_bank;

    /**
     * @OA\Property()
     * @var String
     */
    public $nama_bank;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_paspor;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_paspor;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_ktp;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_ktp;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_bpjs;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_bpjs;

    /**
     * @OA\Property()
     * @var String
     */
    public $npwp;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_npwp;

    /**
     * @OA\Property()
     * @var String
     */
    public $id_status_pajak;

    /**
     * @OA\Property()
     * @var int
     */
    public $jml_anak;

    /**
     * @OA\Property()
     * @var int
     */
    public $jml_tanggungan;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_photo;

    /**
     * @OA\Property()
     * @var String
     */
    public $nama_ibu_kandung;

    /**
     * @OA\Property()
     * @var String
     */
    public $nama_darurat;

    /**
     * @OA\Property()
     * @var String
     */
    public $no_telp_darurat;

    /**
     * @OA\Property()
     * @var String
     */
    public $ptkp;

    /**
     * @OA\Property()
     * @var String
     */
    public $status_vaksin;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_kartu_keluarga;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_cv;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_surat_lamaran;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_skck;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_surat_ket_sehat;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_surat_rekom_kel;

    /**
     * @OA\Property()
     * @var String
     */
    public $file_surat_rekom_kerja;

    /**
     * @OA\Property()
     * @var String
     */
    public $berkas_lainnya;

    /**
     * @OA\Property()
     * @var String
     */
    public $create_date;

    /**
     * @OA\Property()
     * @var String
     */
    public $update_date;

    /**
     * @OA\Property()
     * @var String
     */
    public $update_id_user;

    /**
     * @OA\Property()
     * @var String
     */
    public $id_group_location;


    //get pegawai only from tale pegawai
    public function get_pegawai($q)
    {
        $this->db->select('*');
        $this->db->from('mst_pegawai');
        $this->db->where($q);
        $query = $this->db->get();
        return $query->result();
    }
    
}
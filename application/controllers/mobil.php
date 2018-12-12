<?php

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

// Jika ada pesan "REST_Controller not found"
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class mobil extends REST_Controller {

    // Konfigurasi letak folder untuk upload image
    private $folder_upload = 'uploads/';

    function all_get(){
        $get_mobil = $this->db->query("
            SELECT
                id_mobil,
                tahun_mobil,
                merek_mobil,
                nama_mobil,
                rating_mobil,
                photo_url
            FROM mobil")->result();
       $this->response(
           array(
               "status" => "success",
               "result" => $get_mobil 
           )
       );
    }

    function all_post() {

        $action  = $this->post('action');
        $data_mobil = array(
     	               'id_mobil' => $this->post('id_mobil'),
     	               'tahun_mobil'       => $this->post('tahun_mobil'),
     	               'merek_mobil'     => $this->post('merek_mobil'),
     	               'nama_mobil'      => $this->post('nama_mobil'),
                       /*'rating_mobil'      => $this->post('rating_mobil'),*/
     	               'photo_url'   => $this->post('photo_url')
 	               );

        switch ($action) {
            case 'insert':
                $this->insertMobil($data_mobil);
                break;
            
            case 'update':
                $this->updateMobil($data_mobil);
                break;
            
            case 'delete':
                $this->deleteMobil($data_mobil);
                break;
            
            default:
                $this->response(
                    array(
                        "status"  =>"failed",
                        "message" => "action harus diisi"
                    )
                );
                break;
        }
    }

    function insertMobil($data_mobil){

 	   // Cek validasi
 	   if (empty($data_mobil['tahun_mobil']) || empty($data_mobil['merek_mobil']) || empty($data_mobil['nama_mobil'])){
 	       $this->response(
 	           array(
 	               "status" => "failed",
 	               "message" => "tahun / merek / nama mobil harus diisi "
 	           )
 	       );
 	   } else {

 	       $data_mobil['photo_url'] = $this->uploadPhoto();

 	       $do_insert = $this->db->insert('mobil', $data_mobil);
     	  
     	   if ($do_insert){
         	   $this->response(
         	       array(
         	           "status" => "success",
         	           "result" => array($data_mobil),
         	           "message" => $do_insert
         	       )
         	   );
            }
 	   }
    }

    function updateMobil($data_mobil){

 	   // Cek validasi
 	   if (empty($data_mobil['tahun_mobil']) || empty($data_mobil['merek_mobil']) || empty($data_mobil['nama_mobil']) || empty($data_mobil['id_mobil'])){
 	       $this->response(
 	           array(
 	               "status" => "failed",
 	               "message" => "id / tahun / merek / nama mobil harus diisi"
 	           )
 	       );
 	   } else {
 	       // Cek apakah ada di database
 	       $get_mobil_baseID = $this->db->query("
 	           SELECT 1
 	           FROM mobil
 	           WHERE id_mobil =  {$data_mobil['id_mobil']}")->num_rows();

 	       if($get_mobil_baseID === 0){
     	       // Jika tidak ada
     	       $this->response(
     	           array(
     	               "status"  => "failed",
     	               "message" => "ID Mobil tidak ditemukan"
     	           )
     	       );
 	       } else {
 	           // Jika ada
 	           $data_mobil['photo_url'] = $this->uploadPhoto();

         	   if ($data_mobil['photo_url']){
         	       // Jika upload foto berhasil, eksekusi update
         	       $update = $this->db->query("
         	           UPDATE mobil SET
         	               tahun_mobil = '{$data_mobil['tahun_mobil']}',
         	               merek_mobil = '{$data_mobil['merek_mobil']}',
         	               nama_mobil = '{$data_mobil['nama_mobil']}',
         	               photo_url = '{$data_mobil['photo_url']}'
         	           WHERE id_mobil = '{$data_mobil['id_mobil']}'");

         	   } else {
         	       // Jika foto kosong atau upload foto tidak berhasil, eksekusi update
                    $update = $this->db->query("
                        UPDATE mobil
                        SET
                            tahun_mobil    = '{$data_mobil['tahun_mobil']}',
                            merek_mobil  = '{$data_mobil['merek_mobil']}',
                            nama_mobil    = '{$data_mobil['nama_mobil']}'
                        WHERE id_mobil = {$data_mobil['id_mobil']}"
                    );
         	   }
         	  
         	   if ($update){
             	   $this->response(
             	       array(
             	           "status"    => "success",
             	           "result"    => array($data_mobil),
             	           "message"   => $update
             	       )
             	   );
                }
 	       }   
 	   }
    }

    function deleteMobil($data_mobil){

        if (empty($data_mobil['id_mobil'])){
 	       $this->response(
 	           array(
 	               "status" => "failed",
 	               "message" => "ID Mobil harus diisi"
 	           )
 	       );
 	   } else {
 	       // Cek apakah ada di database
 	       $get_mobil_baseID =$this->db->query("
 	           SELECT 1
 	           FROM mobil
 	           WHERE id_mobil = {$data_mobil['id_mobil']}")->num_rows();

 	       if($get_mobil_baseID > 0){
 	           
 	           $get_photo_url =$this->db->query("
 	           SELECT photo_url
 	           FROM mobil
 	           WHERE id_mobil = {$data_mobil['id_mobil']}")->result();
 	       
                if(!empty($get_photo_url)){

                    // Dapatkan nama file
                    $photo_nama_file = basename($get_photo_url[0]->photo_url);
                    // Dapatkan letak file di folder upload
                    $photo_lokasi_file = realpath(FCPATH . $this->folder_upload . $photo_nama_file);
                    
                    // Jika file ada, hapus
                    if(file_exists($photo_lokasi_file)) {
                        // Hapus file
         	           unlink($photo_lokasi_file);
         	       }

         	       $this->db->query("
         	           DELETE FROM mobil
         	           WHERE id_mobil = {$data_mobil['id_mobil']}");
         	       $this->response(
         	           array(
         	               "status" => "success",
         	               "message" => "Data ID = " .$data_mobil['id_mobil']. " berhasil dihapus"
         	           )
         	       );
         	   }
 	       
            } else {
                $this->response(
                    array(
                        "status" => "failed",
                        "message" => "ID Mobil tidak ditemukan"
                    )
                );
            }
 	   }
    }

    function uploadPhoto() {

        // Apakah user upload gambar?
        if ( isset($_FILES['photo_url']) && $_FILES['photo_url']['size'] > 0 ){

            // Foto disimpan di android-api/uploads
            $config['upload_path'] = realpath(FCPATH . $this->folder_upload);
            $config['allowed_types'] = 'jpg|png';

 	       // Load library upload & helper
 	       $this->load->library('upload', $config);
 	       $this->load->helper('url');

 	       // Apakah file berhasil diupload?
 	       if ( $this->upload->do_upload('photo_url')) {

               // Berhasil, simpan nama file-nya
               // URL image yang disimpan adalah http://localhost/android-api/uploads/namafile
        	   $img_data = $this->upload->data();
        	   $post_image = base_url(). $this->folder_upload .$img_data['file_name'];

 	       } else {

 	           // Upload gagal, beri nama image dengan errornya
 	           // Ini bodoh, tapi efektif
 	           $post_image = $this->upload->display_errors();
 	           
 	       }
 	   } else {
 	       // Tidak ada file yang di-upload, kosongkan nama image-nya
 	       $post_image = '';
 	   }

 	   return $post_image;
    }
}
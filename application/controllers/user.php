<?php

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

// Jika ada pesan "REST_Controller not found"
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class user extends REST_Controller {

    // Konfigurasi letak folder untuk upload image
    //private $folder_upload = 'uploads/';

    function all_get(){
        $get_user = $this->db->query("
            SELECT
                id_user,
                username,
                password
                /*photo_url*/
            FROM user")->result();
       $this->response(
           array(
               "status" => "success",
               "result" => $get_user 
           )
       );
    }

    function all_post() {

        $action  = $this->post('action');
        $data_user = array(
     	               'id_user' => $this->post('id_user'),
     	               'username'       => $this->post('username'),
     	               'password'     => $this->post('password')
     	               /*'photo_url'   => $this->post('photo_url')*/
 	               );

        switch ($action) {
            case 'insert':
                $this->insertUser($data_user);
                break;
            
            case 'update':
                $this->updateUser($data_user);
                break;
            
            case 'delete':
                $this->deleteUser($data_user);
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

    function insertUser($data_user){

 	   // Cek validasi
 	   if (empty($data_user['username']) || empty($data_user['password'])){
 	       $this->response(
 	           array(
 	               "status" => "failed",
 	               "message" => "username dan password harus diisi "
 	           )
 	       );
 	   } else {

 	       //$data_user['photo_url'] = $this->uploadPhoto();

 	       $do_insert = $this->db->insert('user', $data_user);
     	  
     	   if ($do_insert){
         	   $this->response(
         	       array(
         	           "status" => "success",
         	           "result" => array($data_user),
         	           "message" => $do_insert
         	       )
         	   );
            }
 	   }
    }

    function updateUser($data_user){

 	   // Cek validasi
 	   if (empty($data_user['username']) || empty($data_user['password']) || empty($data_user['id_user'])){
 	       $this->response(
 	           array(
 	               "status" => "failed",
 	               "message" => "id_user, usernname, dan password harus diisi"
 	           )
 	       );
 	   } else {
 	       // Cek apakah ada di database
 	       $get_user_baseID = $this->db->query("
 	           SELECT 1
 	           FROM user
 	           WHERE id_user =  {$data_user['id_user']}")->num_rows();

 	       if($get_user_baseID === 0){
     	       // Jika tidak ada
     	       $this->response(
     	           array(
     	               "status"  => "failed",
     	               "message" => "ID user tidak ditemukan"
     	           )
     	       );
 	       } else {
 	           // Jika ada
 	           //$data_user['photo_url'] = $this->uploadPhoto();

         	   /*if ($data_user['photo_url']){
         	       // Jika upload foto berhasil, eksekusi update
         	       $update = $this->db->query("
         	           UPDATE user SET
         	               username = '{$data_user['username']}',
         	               password = '{$data_user['password']}',
         	               nama_user = '{$data_user['nama_user']}',
         	               photo_url = '{$data_user['photo_url']}'
         	           WHERE id_user = '{$data_user['id_user']}'");

         	   } else {*/
         	       // Jika foto kosong atau upload foto tidak berhasil, eksekusi update
                    $update = $this->db->query("
                        UPDATE user
                        SET
                            username    = '{$data_user['username']}',
                            password  = '{$data_user['password']}'
                        WHERE id_user = {$data_user['id_user']}"
                    );
         	   //}
         	  
         	   if ($update){
             	   $this->response(
             	       array(
             	           "status"    => "success",
             	           "result"    => array($data_user),
             	           "message"   => $update
             	       )
             	   );
                }
 	       }   
 	   }
    }

    function deleteUser($data_user){

        if (empty($data_user['id_user'])){
 	       $this->response(
 	           array(
 	               "status" => "failed",
 	               "message" => "ID user harus diisi"
 	           )
 	       );
 	   } else {
 	       // Cek apakah ada di database
 	       /*$get_user_baseID =$this->db->query("
 	           SELECT 1
 	           FROM user
 	           WHERE id_user = {$data_user['id_user']}")->num_rows();

 	       if($get_user_baseID > 0){
 	           
 	           $get_photo_url =$this->db->query("
 	           SELECT photo_url
 	           FROM user
 	           WHERE id_user = {$data_user['id_user']}")->result();
 	       
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
         	   }*/

               $delete = $this->db->query("
                       DELETE FROM user
                       WHERE id_user = {$data_user['id_user']}");

 	        if ($delete) {
                # code...
                $this->response(
                       array(
                           "status" => "success",
                           "message" => "Data ID = " .$data_user['id_user']. " berhasil dihapus"
                       )
                   );
            } else {
                $this->response(
                    array(
                        "status" => "failed",
                        "message" => "ID user tidak ditemukan"
                    )
                );
            }
 	   }
    }

    //not used yet
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
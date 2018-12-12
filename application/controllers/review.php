<?php
use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class review extends REST_Controller {

   // show data review
   function user_get() {
       $get_transaksi = $this->db->query("SELECT rev.id_review, rev.id_user, rev.id_mobil, rev.rating, rev.deskripsi FROM user, review rev, mobil Where rev.id_mobil=user.id_user AND rev.id_mobil=mobil.id_mobil")->result();
     
       $this->response(array("status"=>"success","result" => $get_transaksi));
   }

   // insert review
   function user_post() {
       $data_review = array(
           'id_user'     => $this->post('id_user'),
           'id_mobil'   => $this->post('id_mobil'),
           'rating'    => $this->post('rating'),
           'deskripsi'       => $this->post('deskripsi')
           );
      
           $getId = $this->db->query("Select id_user, id_mobil from review where id_user='".$data_review['id_user']."' and id_mobil='".$data_review['id_mobil']."'")->result();
          
           //jika id_mobil tidak ada dalam database maka eksekusi insert
           if (empty($getId)){
                    if (empty($data_review['id_user'])){
                       $this->response(array('status'=>'fail',"message"=>"id_user kosong"));
                    }
                    else if(empty($data_review['id_mobil'])){
                       $this->response(array('status'=>'fail',"message"=>"id_mobil kosong"));
                    }else if(empty($data_review['rating'])){
                       $this->response(array('status'=>'fail',"message"=>"rating kosong"));
                    }else if(empty($data_review['deskripsi'])){
                       $this->response(array('status'=>'fail',"message"=>"deskripsi kosong"));
                    }
                    else{
                       //jika masuk pada else atau kondisi ini maka dipastikan seluruh input telah di set
                       //jika akan melakukan pembelian id_pembeli dan id_tiket harus dipastikan ada
                       $getIdUser = $this->db->query("Select id_user from user Where id_user='".$data_review['id_user']."'")->result();
                       $getIdMobil = $this->db->query("Select id_mobil from mobil Where id_mobil='".$data_review['id_mobil']."'")->result();
                       $message="";
                       if (empty($getIdUser)) $message.="id_user tidak ada/salah ";
                       if (empty($getIdMobil)) {
                           if (empty($message)) {
                               $message.="id_mobil tidak ada/salah";
                           }
                           else {
                               $message.="dan id_mobil tidak ada/salah";
                           }
                       }
                       if (empty($message)){
                           $insert= $this->db->insert('review',$data_review);
                           if ($insert){
                               $this->response(array('status'=>'success','result' => $data_review,"message"=>$insert));   
                           }
                          
                       }else{
                           $this->response(array('status'=>'fail',"message"=>$message));   
                       }
                      
                    }
           }else{
               $this->response(array('status'=>'fail',"message"=>"Review untuk mobil sudah ada"));
           }  
   }

   // update data review
   function user_put() {
       $data_review = array(
                   'id_review'    => $this->put('id_review'),
                   'id_user'      => $this->put('id_user'),
                   'id_mobil'    => $this->put('id_mobil'),
                   'rating'     => $this->put('rating'),
                   'deskripsi'        => $this->put('deskripsi')
                   );
       if  (empty($data_review['id_review'])){
           $this->response(array('status'=>'fail',"message"=>"id_review kosong"));
       }else{
           $getId = $this->db->query("Select id_review from review where id_review='".$data_review['id_review']."'")->result();
           //jika id_review harus ada dalam database
           if (empty($getId)){
             $this->response(array('status'=>'fail',"message"=>"id_review tidak ada/salah")); 
           }else{
               //jika masuk disini maka dipastikan id_pembelian ada dalam database
                if (empty($data_review['id_user'])){
                   $this->response(array('status'=>'fail',"message"=>"id_user kosong"));
                }
                else if(empty($data_review['id_mobil'])){
                   $this->response(array('status'=>'fail',"message"=>"id_mobil kosong"));
                }else if(empty($data_review['rating'])){
                   $this->response(array('status'=>'fail',"message"=>"rating"));
                }else if(empty($data_review['deskripsi'])){
                       $this->response(array('status'=>'fail',"message"=>"deskripsi kosong"));
                } 
                else{
                   //jika masuk pada else atau kondisi ini maka dipastikan seluruh input telah di set
                   //jika akan melakukan edit pembelian id_pembeli dan id_tiket harus dipastikan ada
                   $getIdUser = $this->db->query("Select id_user from user Where id_user='".$data_review['id_user']."'")->result();
                       $getIdMobil = $this->db->query("Select id_mobil from mobil Where id_mobil='".$data_review['id_mobil']."'")->result();
                   $message="";
                   if (empty($getIdUser)) $message.="id_review tidak ada/salah ";
                   if (empty($getIdMobil)) {
                   if (empty($message)) {
                           $message.="id_mobil tidak ada/salah";
                       }
                       else {
                           $message.="dan id_user tidak ada/salah";
                       }
                   }
                   if (empty($message)){
                       $this->db->where('id_review',$data_review['id_review']);
                       $update= $this->db->update('review',$data_review);
                       if ($update){
                           $this->response(array('status'=>'success','result' => $data_review,"message"=>$update));
                       }
                      
                   }else{
                       $this->response(array('status'=>'fail',"message"=>$message));   
                   }
                }
           }

       }
   }

   // delete pembelian
   function user_delete() {
       $id_review = $this->delete('id_review');
       if (empty($id_review)){
           $this->response(array('status' => 'fail', "message"=>"id_review harus diisi"));
       } else {
           $this->db->where('id_review', $id_review);
           $delete = $this->db->delete('review');  
           if ($this->db->affected_rows()) {
               $this->response(array('status' => 'success','message' =>"Berhasil delete dengan id_review = ".$id_review));
           } else {
               $this->response(array('status' => 'fail', 'message' =>"id_review tidak dalam database"));
           }
       }
   }
}  
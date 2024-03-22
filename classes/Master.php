<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function delete_img(){
		extract($_POST);
		if(is_file($path)){
			if(unlink($path)){
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete '.$path;
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown '.$path.' path';
		}
		return json_encode($resp);
	}
	function save_service(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				if(!empty($data)) $data .=",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$check = $this->conn->query("SELECT * FROM `service_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Service Name already exists.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `service_list` set {$data} ";
		}else{
			$sql = "UPDATE `service_list` set {$data} where id = '{$id}' ";
		}
			$save = $this->conn->query($sql);
		if($save){
			$sid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['sid'] = $sid;
			$resp['status'] = 'success';
			if(!empty($_FILES['image']['tmp_name'])){
				if(!is_dir(base_app."uploads/services"))
					mkdir(base_app."uploads/services");
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$fname = "uploads/services/$sid.png";
				$accept = array('image/jpeg','image/png');
				if(!in_array($_FILES['image']['type'],$accept)){
					$err = "Image file type is invalid";
				}
				if($_FILES['image']['type'] == 'image/jpeg')
					$uploadfile = imagecreatefromjpeg($_FILES['image']['tmp_name']);
				elseif($_FILES['image']['type'] == 'image/png')
					$uploadfile = imagecreatefrompng($_FILES['image']['tmp_name']);
				if(!$uploadfile){
					$err = "Image is invalid";
				}
				list($width,$height) = getimagesize($_FILES['image']['tmp_name']);
				$temp = imagescale($uploadfile,$width,$height);
				if(is_file(base_app.$fname))
				unlink(base_app.$fname);
				$upload =imagepng($temp,base_app.$fname);
				if($upload){
					$this->conn->query("UPDATE `service_list` set `image_path` = CONCAT('{$fname}', '?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$sid}'");
				}

				imagedestroy($temp);
			}
			if(empty($id))
				$resp['msg'] = "New Service successfully saved.";
			else
				$resp['msg'] = " Service successfully updated.";
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if($resp['status'] == 'success')
			$this->settings->set_flashdata('success',$resp['msg']);
			return json_encode($resp);
	}
	function delete_service(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `service_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Service successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_inquiry(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id', 'visitor'))){
				if(!empty($data)) $data .=",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `inquiry_list` set {$data} ";
		}else{
			$sql = "UPDATE `inquiry_list` set {$data} where id = '{$id}' ";
		}
			$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			if(empty($id)){
				if(!isset($visitor))
					$resp['msg'] = "New Inquiry successfully saved.";
					else
					$resp['msg'] = "Your Message has been sent successfully. We will reach out using your contact information as soon as we sees your message. Thank you!";
			}else
				$resp['msg'] = " Inquiry successfully updated.";
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if($resp['status'] == 'success')
			$this->settings->set_flashdata('success',$resp['msg']);
			return json_encode($resp);
	}
	function delete_inquiry(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `inquiry_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Inquiry successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_book(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!is_array($_POST[$k]) && !in_array($k,array('id', 'customer'))){
				if(!empty($data)) $data .=",";
				$v = htmlspecialchars($this->conn->real_escape_string($v));
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `book_list` set {$data} ";
		}else{
			$sql = "UPDATE `book_list` set {$data} where id = '{$id}' ";
		}
			$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			$bid = empty($id) ? $this->conn->insert_id : $id;
			$resp['bid'] = $bid;
			$data = "";
			foreach($services as $service_id){
				if(!empty($data)) $data .= ", ";
				$data .= " ('{$bid}', '{$service_id}') ";
			}
			if(!empty($data)){
				 $this->conn->query("INSERT INTO `book_services` (`book_id`, `service_id`) VALUES {$data}");
			}
			if(empty($id)){
				if(!isset($customer))
					$resp['msg'] = "New Booking Data has been saved successfully.";
					else
					$resp['msg'] = "Booking Data successfully submitted. We will reach as soon as we sees your service request. Thank you!";
			}else
				$resp['msg'] = " Booking Data has been updated successfully.";
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if($resp['status'] == 'success')
			$this->settings->set_flashdata('success',$resp['msg']);
			return json_encode($resp);
	}
	function delete_book(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `book_list` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Booking Data successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_page(){
		extract($_POST);
		if(!is_dir(base_app.'pages'))
		mkdir(base_app.'pages');
		if(isset($page['welcome'])){
			$content = $page['welcome'];
			$save = file_put_contents(base_app.'pages/welcome.html', $content);
		}
		if(isset($page['about'])){
			$content = $page['about'];
			$save = file_put_contents(base_app.'pages/about.html', $content);
		}
		$this->settings->set_flashdata('success', "Page Content has been updated successfully");
		return json_encode(['status' => 'success']);
	}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'delete_img':
		echo $Master->delete_img();
	break;
	case 'save_service':
		echo $Master->save_service();
	break;
	case 'delete_service':
		echo $Master->delete_service();
	break;
	case 'save_page':
		echo $Master->save_page();
	break;
	case 'save_inquiry':
		echo $Master->save_inquiry();
	break;
	case 'delete_inquiry':
		echo $Master->delete_inquiry();
	break;
	case 'save_book':
		echo $Master->save_book();
	break;
	case 'delete_book':
		echo $Master->delete_book();
	break;
	default:
		// echo $sysset->index();
		break;
}
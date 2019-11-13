<?php
	//This file works with the submitted activity form.
	/* 
	Status messages (sent through GET query as edit_status):
		1: add success
		2: edit success
		3: delete success
		4: illegal mode
		5: server-side error
		6: invalid data
		7: access violation
		8: file upload error
		9: file couldn't be saved
	For 3, there are additional messages (sent as err_code):
		0: non-existent or illegal ID
		1: invalid first name
		2: invalid middle name
		3: invalid last name
		4: invalid activity name
		5: invalid date
		6: invalid file format
	*/
	session_start();
	if (isset($_SESSION['login']) && isset($_POST['submit']) && isset($_POST['mode']) ) {
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/include/data/userdata_readfrom.php');
		$permission = $users[$_SESSION['login']]['permission'];
	
		//We must check if the user is director
		
		if ('director' !== $permission) {
			header("Location: /cabinet/my_cabinet.php?edit_status=7");
			exit;
		}
		
		//Server-side validation
		
		//Validate mode
		$mode = $_POST['mode'];
		
		$supported_modes = array(
			'add',
			'edit',
			'delete'
		);
		
		if (!in_array($mode,$supported_modes)) {
			header("Location: /cabinet/my_cabinet.php?edit_status=4");
			exit;
		}
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/activity_data_db_operations.php');
		
		//Check if ID exists
		if (('edit' === $mode) || ('delete' === $mode)) {
			$ids = get_ids_from('activities');
			if (!in_array($_POST['id'],$ids)) {
				header("Location: /cabinet/my_cabinet.php?edit_status=6&err_code=0");
				exit;
			}
		}
		
		//If we're deleting an activity, we can do it right away
		if ('delete' === $mode) {
			$file_name = get_file_name_for($_POST['id']);
			if ($file_name !== '')
				unlink($_SERVER['DOCUMENT_ROOT'].$file_name);
			if (delete_activity($_POST['id']) === 0) {
				header("Location: /cabinet/my_cabinet.php?edit_status=3");
			}
			else {
				header("Location: /cabinet/my_cabinet.php?edit_status=5");
			}
			exit;
		}
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/validate_date.php');
		
		$err_redirect = "Location: /cabinet/my_cabinet.php?edit_status=6&err_code=";

		//Validate all fields via regex
		
		$fields = array(
			'activity_name',
			'first_name',
			'middle_name',
			'last_name'
		);
		
		$field_info = array(
			'first_name' => array(
				'pattern' => "/^[а-яА-ЯёЁ\-]+$/ui",
				'errcode' => "1",
				'required' => true
			),
			'middle_name' => array(
				'pattern' => "/^[а-яА-ЯёЁ\-]+$/ui",
				'errcode' => "2",
				'required' => false
			),
			'last_name' => array(
				'pattern' => "/^[а-яА-ЯёЁ\-]+$/ui",
				'errcode' => "3",
				'required' => true
			),
			'activity_name' => array(
				'pattern' => "/^[а-яА-ЯёЁa-zA-Z -]+$/ui",
				'errcode' => "4",
				'required' => true
			)
		);

		foreach ($fields as $field) {
			$pattern = $field_info[$field]['pattern'];
			if (!preg_match($pattern,$_POST[$field]) && !(($_POST[$field] === '') && !($field_info[$field]['required']))) {
				header($err_redirect.$field_info[$field]['errcode']);
				exit;
			}
		}
		
		//Validate date
		if (validate_date($_POST['date_of_creation']) == -1) {
			header($err_redirect."5");
			exit;
		}
		
		$directory = get_file_name_for($_POST['id']);
		
		//Check if the user wants to delete the file that was already uploaded
		if (isset($_POST['delete_file_checkbox']) && ('on' === $_POST['delete_file_checkbox']) && ('edit' === $mode) && ('director' === $permission)) {
			if ('' !== $directory)
				unlink($_SERVER['DOCUMENT_ROOT'].$directory);
			$directory = '';
		}
		//Else, check if we recieved a file, and if we did, validate it
		else if (isset($_FILES['activity_plan']) && $_FILES['activity_plan']['error'] !== 4) { //4 corresponds to file not being submitted; we need to exclude that option or else this script'll redirect us with an error
			
			define("UPLOAD_DIR","/cabinet/plans");
			$activity_plan_file = $_FILES['activity_plan'];
			
			if ($activity_plan_file['error'] !== UPLOAD_ERR_OK) {
				header("Location: /cabinet/my_cabinet.php?edit_status=8");
				exit;
			}
			
			//safe file name
			$file_name = preg_replace("/[^A-ZА-Я0-9._-]/ui", "_", $activity_plan_file['name']);
			
			//check type
			$mime = 'application/pdf; charset=binary';
			$operating_system_name = php_uname('s'); //this fucntion gets OS name
			$out = array();
			
			//If you're running this on an operating system not listed here, please implement the MIME-type check functionality of your OS here
			switch ($operating_system_name) {
				case 'Windows NT':
					//Windows doesn't ship with "file"; however, there is an opensource implementation of this program available here: http://gnuwin32.sourceforge.net/packages/file.htm
					//We'll be using that instead.
					exec('"C:\Program Files (x86)\GnuWin32\bin\file" -bi "'.$activity_plan_file['tmp_name'].'"', $out);
					break;
				case 'Linux':
					exec('file -bi "'.$activity_plan_file['tmp_name'].'"', $out);
					break;
				default:
					//If OS can't be recognized, this'll be the last resort
					$out[0] = $activity_plan_file['type'];
					break;
			}
			
			if ($out[0] !== $mime) {
				header($err_redirect."6");
				exit;
			}
			
			//if we're editing the file, we have to delete the previous one
			if ('edit' === $mode) {
				$old_file_name = get_file_name_for($_POST['id']);
				if ($old_file_name !== '')
					unlink($_SERVER['DOCUMENT_ROOT'].$old_file_name);
			}
			
			//this part will prevent us from overwriting other files in the folder if the file happens to have the same name
			$i = 0;
			$parts = pathinfo($file_name);
			
			while (file_exists($_SERVER['DOCUMENT_ROOT'].UPLOAD_DIR.'/'.$file_name)) {
				++$i;
				$file_name = $parts['filename'].'-'.$i.'.'.$parts['extension'];
			}
			
			//directory does not contain document root adress because otherwise it'll be unsafe to make a link out of it
			$directory = UPLOAD_DIR.'/'.$file_name;
			
			$success = move_uploaded_file($activity_plan_file['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$directory);
			
			if (!$success) {
				header("Location: /cabinet/my_cabinet.php?edit_status=9");
				exit;
			}
			
			//$directory now contains a path to the plan of submitted activity; we can now add it to DB.
		}
		
		//End of server-side validation
		
		$name = $_POST['last_name']." ".$_POST['first_name'];
		if ($_POST['middle_name'])
			$name .= " ".$_POST['middle_name'];
		if ('add' === $mode) 
			if (add_activity($_POST['activity_name'], $_POST['date_of_creation'], $name, $directory) === 0) {
				header("Location: /cabinet/my_cabinet.php?edit_status=1");
			}
			else {
				header("Location: /cabinet/my_cabinet.php?edit_status=5");
			}
		else if ('edit' === $mode)
			if (edit_activity($_POST['id'], $_POST['activity_name'], $_POST['date_of_creation'], $name, $directory) === 0) {
				header("Location: /cabinet/my_cabinet.php?edit_status=2");
			}
			else {
				header("Location: /cabinet/my_cabinet.php?edit_status=5");
			}
	}
	else {
		header("Location: /main.html");
	}
?>
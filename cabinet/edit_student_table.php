<?php
	//This file works with the submitted student form.
	/* 
	Status messages (sent through GET query as edit_status):
		1: add success
		2: edit success
		3: delete success
		4: illegal mode
		5: server-side error
		6: invalid data
		7: access violation
	For 6, there are additional messages (sent as err_code):
		0: non-existent or illegal ID
		1: invalid first name
		2: invalid middle name
		3: invalide last name
		4: invalid date of entry
		5: invalid number of attended lessons
		6: invalid email
		7: invalid phone number
		8: invalid activity ID
	*/
	session_start();
	if (isset($_SESSION['login']) && isset($_POST['submit']) && isset($_POST['mode']) ) {
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/include/data/userdata_readfrom.php');
		$activity_id = $users[$_SESSION['login']]['activity_id'];
		$permission = $users[$_SESSION['login']]['permission'];
		
		$recieved_activity_id = $activity_id;
		
		//$recieved_activity_id is a variable that contains id we got from the form, if it is set
		if (isset($_POST['activity_id']))
			$recieved_activity_id = $_POST['activity_id'];
		
		//First, check if the user is permitted to edit student table
		//Only teachers and directors are allowed to edit student tables
		//Non-directors are not permitted to edit data of students who don't belong to their activity
		
		if ((('director' !== $permission) && ('teacher' !== $permission)) || (('teacher' === $permission) && ($recieved_activity_id !== $activity_id))) {
			header("Location: /cabinet/my_cabinet.php?edit_status=7");
			exit;
		}
		
		//Check if the mode is correct
		$mode = $_POST['mode'];
		
		$supported_modes = array(
			'add',
			'edit',
			'delete'
		);
		
		//if recieved mode isn't supported, it is illegal
		if (!in_array($mode,$supported_modes)) {
			header("Location: /cabinet/my_cabinet.php?edit_status=4");
			exit;
		}
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/activity_data_db_operations.php');
		
		//Server-side validation
		
		//Check if ID exists (only for edit and delete modes)
		if (('edit' === $mode) || ('delete' === $mode)) {
			$ids = ($permission === 'director' && isset($_SESSION['viewed_table']) && $_SESSION['viewed_table'] === 'all_students') ? get_ids_from('students') : get_student_ids_with($activity_id);
			//If it is a director and they were viewing all students at the time of editing the table, load all students, else load only students of the specified activity ID
			if (!in_array($_POST['id'],$ids)) {
				header("Location: /cabinet/my_cabinet.php?edit_status=6&err_code=0"); //doesn't exist at all, or for their specified activity
				exit;
			}
		}
		
		//If ID exists and we need to delete student with that ID, we can delete the object right away
		//If a teacher tries to delete a student of an activity they don't teach, it'll fail because said ID doesn't exist in $ids (the former action ensured that) 
		if ('delete' === $mode) {
			if (delete_student($_POST['id']) === 0) {
				header("Location: /cabinet/my_cabinet.php?edit_status=3");
			}
			else {
				header("Location: /cabinet/my_cabinet.php?edit_status=5");
			}
			exit;
		}
		
		//Check if activity ID exists in the DB
		$activity_ids = get_ids_from('activities');
		if (!in_array($recieved_activity_id,$activity_ids) || (('teacher' === $permission) && ($recieved_activity_id !== $activity_id))) {
			header("Location: /cabinet/my_cabinet.php?edit_status=6&err_code=8");
			exit;
		}
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/validate_date.php');
		
		$err_redirect = "Location: /cabinet/my_cabinet.php?edit_status=6&err_code=";

		//Validate all fields via regex
		
		$fields = array(
			'first_name',
			'middle_name',
			'last_name',
			'attended_lessons',
			'email',
			'phone_number'
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
			'attended_lessons' => array(
				'pattern' => "/^(0|[1-9]\d*)$/",
				'errcode' => "4",
				'required' => true
			),
			'email' => array(
				'pattern' => "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/",
				'errcode' => "5",
				'required' => false
			),
			'phone_number' => array(
				'pattern' => "/^\+7\d{10}$/",
				'errcode' => "6",
				'required' => false
			)
		);

		foreach ($fields as $field) {
			$pattern = $field_info[$field]['pattern'];
			if (!preg_match($pattern,$_POST[$field]) && !(($_POST[$field] === '') && !($field_info[$field]['required']))) {
				header($err_redirect.$field_info[$field]['errcode']);
				exit;
			}
		}
		
		//Validate date (can't be simply checked via regex, so a function is used (see validate_date.php))
		if (validate_date($_POST['date_of_entry']) == -1) {
			header($err_redirect."7");
			exit;
		}
		
		//End of server-side validation
		
		$name = $_POST['last_name']." ".$_POST['first_name'];
		if ($_POST['middle_name'])
			$name .= " ".$_POST['middle_name'];
		if ('add' === $mode) 
			if (add_student($name, $recieved_activity_id, $_POST['date_of_entry'], $_POST['email'], $_POST['phone_number'], $_POST['attended_lessons']) === 0) {
				header("Location: /cabinet/my_cabinet.php?edit_status=1");
			}
			else {
				header("Location: /cabinet/my_cabinet.php?edit_status=5");
			}
		else if ('edit' === $mode)
			if (edit_student($_POST['id'], $name, $recieved_activity_id, $_POST['date_of_entry'], $_POST['email'], $_POST['phone_number'], $_POST['attended_lessons']) === 0) {
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
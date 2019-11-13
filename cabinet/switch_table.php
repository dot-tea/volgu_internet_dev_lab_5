<?php
	//Switches table in my_cabinet.php
	session_start();
	include_once($_SERVER['DOCUMENT_ROOT'].'/include/data/userdata_readfrom.php');
	if (isset($_SESSION['login']) && isset($_POST['submit'])) {
		$permission = $users[$_SESSION['login']]['permission']; //We have to also validate permssion to prevent teachers from accessing restricted tables 
		if (isset($_POST['switch_to'])) {
			$error_code = '0';
			switch ($_POST['switch_to']) {
				case 'students_of_my_activity':
					if (isset($users[$_SESSION['login']]['activity_id'])) { //if the user has an assigned activity, allow the option
						$_SESSION['viewed_table'] = $_POST['switch_to'];
					}
					else {
						$error_code = '1';
					}
					break;
				case 'all_students':
					if ('director' === $permission) { //other two are only allowed for directors
						$_SESSION['viewed_table'] = $_POST['switch_to'];
					}
					else {
						$error_code = '2';
					}
					break;
				case 'activities':
					if ('director' === $permission) {
						$_SESSION['viewed_table'] = $_POST['switch_to'];
					}
					else {
						$error_code = '2';
					}
					break;
				default:
					$error_code = '3'; //user submitted an invalid option
					break;
			}
			if ('0' === $error_code)
				header("Location: /cabinet/my_cabinet.php");
			else
				header("Location: /cabinet/my_cabinet.php?switch_error=".$error_code);
		}
		else {
			header("Location: /cabinet/my_cabinet.php?switch_error=4");
			exit;
		}
	}
?>
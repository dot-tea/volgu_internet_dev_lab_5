<?php
	session_start();
	include_once($_SERVER['DOCUMENT_ROOT'].'/include/data/userdata_readfrom.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/include/scripts/generate_random_strings.php');
	if (isset($_POST['submit'])) {
		$login = $_POST['login'];
		$entered_password = $_POST['password'];
		if (isset($users[$login]) && ($entered_password === $users[$login]['password'])) {
			setcookie("login",$login,time() + 25*25*25,'/');
			
			$coded_codes = file_get_contents($_SERVER['DOCUMENT_ROOT']."/cabinet/codes.txt");
			$codes = unserialize($coded_codes);
			
			$code = generate_random_string(6);
			$codes[$login] = $code;
			
			$send_data = serialize($codes);
			file_put_contents($_SERVER['DOCUMENT_ROOT']."/cabinet/codes.txt",$send_data);
			
			setcookie("code",md5($code),time() + 25*25*25,'/');
			
			$_SESSION['login'] = $login;
			$_SESSION['viewed_table'] = ('director' === $users[$login]['permission']) ? 'activities' : 'students_of_my_activity'; //Default for director is activities table
			header("Location: /cabinet/my_cabinet.php");
		}
	else {
			if (!isset($users[$login]))
				header("Location: /cabinet/error_message.php?err=1");
			else
				header("Location: /cabinet/error_message.php?err=2");
		}
	}
	else
		header("Location: /main.html");
?>
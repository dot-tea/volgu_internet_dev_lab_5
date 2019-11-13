<?php
	session_start();
	if (isset($_SESSION['login'])) {
		//Здесь нужно загрузить код, unset-нуть один из кодов, а потом записать обратно
		session_destroy();
		unset($_COOKIE['login']);
		setcookie('login', "", -1, '/');
		unset($_COOKIE['code']);
		setcookie('code', "", -1, '/');
		$_SESSION = array();
	}
	header("Location: /main.html");
?>
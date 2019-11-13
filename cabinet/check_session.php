<?php
	session_start();
	if (isset($_SESSION['login']) || (isset($_COOKIE['login']) && isset($_COOKIE['code'])))
		header("Location: /cabinet/my_cabinet.php");
	else
		header("Location: /cabinet/login.html");
?>
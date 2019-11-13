<!DOCTYPE html>
<!-- saved from url=(0014)about:internet -->
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v3.8.5">
    <title>Личный кабинет</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" href="/carousel.css">
	<link rel="stylesheet" href="/cabinet/my_cabinet.css">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
  </head>
  <body>
	<?php
		//Load header
		include_once($_SERVER['DOCUMENT_ROOT'].'/include/templates/header_main.html');
		include_once($_SERVER['DOCUMENT_ROOT'].'/include/templates/header_logged_in.html'); //part of header which contains "Exit" button
		//if unneeded, we include "header_unlogged_in.html" instead
		
		//Start session
		session_start();
		include_once($_SERVER['DOCUMENT_ROOT'].'/include/data/userdata_readfrom.php'); //contains $users array
		
		//This page will be loaded either if current section is active or the user has required cookies
		if (isset($_SESSION['login']) || (isset($_COOKIE['login']) && isset($_COOKIE['code']))) {
			
			if (!isset($_SESSION['login'])) { //If session is inactive, we will log user in via cookies
					
				//Fetch codes
				$coded_codes = file_get_contents("codes.txt");
				$codes = unserialize($coded_codes);
				
				//Get creditentials from browser
				$login = $_COOKIE['login'];
				$code = $_COOKIE['code'];
					
				//If this user exists in the file and the codes in cookies and on server are the same, activate session
				if (isset($users[$login]) && $code === md5($codes[$login])) {
					$_SESSION['login'] = $login;
					$_SESSION['viewed_table'] = 'students_of_my_activity';
				}
				else {
					header("Location: /cabinet/login.html");
					exit;
				}
			}
				
			//fetches login, activtiy_id and permission from users array and session
			$login = $_SESSION['login'];
			$activity_id = '';
			if (isset($users[$login]['activity_id']))
				$activity_id = $users[$login]['activity_id'];
			$permission = $users[$login]['permission'];
			
			echo "Здравствуйте! Вы вошли в сессию. Ваш логин: ".$login."";
		?>
		<!-- This is a form used to switch to different viewed tables. See switch_table.php for details. -->
			<form name="table_select" action="/cabinet/switch_table.php" method="post" class="table-select">
				<div class="row">
					<div class="col-lg-8"><select name="switch_to" class="form-control">
						<?php
							if ($activity_id !== '') {
								echo '<option value="students_of_my_activity">Ученики моего кружка</option>'; //Display that option if we have activity specified
							}
							if ('director' === $permission) {
								//We'll display two more options
								$table_select_options = array(
									'all_students',
									'activities'
								);
								$table_select_options_output = array(
									'all_students' => '<option value="all_students" %selected%>Все ученики</option>',
									'activities' => '<option value="activities" %selected%>Кружки</option>'
								);
								//$_SESSION['viewed_table'] contains the name of a table we're viewing
								foreach ($table_select_options as $option) {
									if ($option === $_SESSION['viewed_table'])
										echo str_replace('%selected%','selected',$table_select_options_output[$option]);
									else
										echo str_replace('%selected%','',$table_select_options_output[$option]);
								}
							}
						?>
					</select></div>
					<div class="col"><button type="submit" class="btn btn-primary" value="switch_table" name="submit">Вперёд</button></div>
				</div>
			</form><br>
		<?php
			//switch_table.php error catcher
			if (isset($_GET['switch_error'])) {
				switch ($_GET['switch_error']) {
					case '1':
						echo '<p style="color: red;">Вы не ведёте ни одного кружка!</p>';
						break;	
					case '2':
						echo '<p style="color: red;">У вас недостаточно прав</p>';
						break;	
					case '3':
						echo '<p style="color: red;">Сервер не распознал переданную опцию</p>';
						break;	
					case '4':
						echo '<p style="color: red;">На сервер не было подано опции</p>';
						break;	
					default:
						break;
				}
			}
			
			include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/activity_data_db_operations.php');
			
			echo '</div>'; //There are many fields, and they can be long. We got to use more space for displaying table, so we close the container here to display the wide table.
			
			//We choose which table to display from the session variable set via switch_table.php
			switch ($_SESSION['viewed_table']) {
				case 'students_of_my_activity':
					if (display_students_of($activity_id) !== 0) {
						echo '<p style="color: red;">Таблица на данный момент недоступна</p>';
					}
					else {
						echo '<div class="container">'; //we can open a new container now
						include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/catch_errors_for_students_table.php'); //error catcher for students table
						$edit_interface = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cabinet/student_edit_interface.html'); //loads edit interface for students
						$edit_interface = str_replace('%DISABLED_ATTRIBUTE%','disabled',$edit_interface); //$DISABLED_ATTRIBUTE% is written in <select> tag; we use it to disable list
						$edit_interface = str_replace('%ACTIVITY_OPTIONS','<option value="'.$activity_id.'">'.get_activity_name($activity_id).'</option>', $edit_interface); //display out activity
						echo $edit_interface;
					}
					break;
				case 'all_students':
					if (display_all_students() !== 0) {
						echo '<p style="color: red;">Таблица на данный момент недоступна</p>';
					}
					else {
						echo '<div class="container">';
						include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/catch_errors_for_students_table.php');
						$edit_interface = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/cabinet/student_edit_interface.html');
						$edit_interface = str_replace('%DISABLED_ATTRIBUTE%','',$edit_interface);
						$activity_options = '';
						$activity_ids = get_ids_from('activities');
						//Display all options for the select list of activities
						foreach ($activity_ids as $id) {
							$activity_options .= '<option value="'.$id.'">'.get_activity_name($id).'</option>';
						}
						$edit_interface = str_replace('%ACTIVITY_OPTIONS',$activity_options,$edit_interface);
						echo $edit_interface;
					}
					break;
				case 'activities':
					if (display_activities() !== 0) {
						echo '<p style="color: red;">Таблица на данный момент недоступна</p>';
					}
					else {
						echo '<div class="container">';
						include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/catch_errors_for_activities_table.php'); //error catcher for activity table
						include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/activity_edit_interface.html'); //loads edit interface for activities
					}
					break;
				default:
					echo '<div class="container">'; //if we somehow didn't select a table, close the container to display other messages, possibly
					break;
			}
			
		}
		else {
			header("Location: /cabinet/access_denied.html");
			exit;
		}
		include_once($_SERVER['DOCUMENT_ROOT'].'/include/templates/footer.html');
	?>
</main>
<script src="/cabinet/validate_form.js"></script>
<script src="/cabinet/switch_to_edit_mode.js"></script>
<script src="./Carousel Template · Bootstrap_files/jquery-3.3.1.slim.min.js.Без названия" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
      <script>window.jQuery || document.write('<script src="/docs/4.3/assets/js/vendor/jquery-slim.min.js"><\/script>')</script><script src="./Carousel Template · Bootstrap_files/bootstrap.bundle.min.js.Без названия" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>

</body></html>
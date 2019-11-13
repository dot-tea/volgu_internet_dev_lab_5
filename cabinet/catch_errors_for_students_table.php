<?php
	if (isset($_GET['edit_status']))
			switch ($_GET['edit_status']) {
				case '1':
					echo '<p style="color: green;">Ученик успешно добавлен в базу!</p>';
					break;
				case '2':
					echo '<p style="color: green;">Ученик успешно изменен в базе!</p>';
					break;
				case '3': 
					echo '<p style="color: green;">Ученик успешно исключен из базы!</p>';
					break;
				case '4':
					echo '<p style="color: red;">Форма была отправлена с недопустимым действием</p>';
					break;
				case '5':
					echo '<p style="color: red;">Произошла ошибка на стороне сервера...</p>';
					break;
				case '6':
					echo '<p style="color: red;">На сервер были поданы некорректные данные</p><br>';
					break;
				case '7':
					echo '<p style="color: red;">У вас недостаточно прав на это действие!</p><br>';
					break;
				default:
					break;
			}
	if (isset($_GET['edit_status']) && $_GET['edit_status'] === '6' && isset($_GET['err_code']))
		switch ($_GET['err_code']) {
				case '0':
					echo '<p style="color: red;">Несуществующее или недопустимое id</p>';
					break;
				case '1':
					echo '<p style="color: red;">Неверное имя</p>';
					break;
				case '2':
					echo '<p style="color: red;">Неверное отчество</p>';
						break;
				case '3':
					echo '<p style="color: red;">Неверная фамилия</p>';
					break;
				case '4':
					echo '<p style="color: red;">Неверное количество посещений</p>';
					break;
				case '5':
					echo '<p style="color: red;">Неверная электронная почта</p>';
					break;
				case '6':
					echo '<p style="color: red;">Неверный номер телефона</p>';
					break;
				case '7':
					echo '<p style="color: red;">Неверная дата записи</p>';
					break;
				case '8':
					echo '<p style="color: red;">Несуществующий или недопустимый кружок</p>';
					break;
				default:
					break;
		}
?>
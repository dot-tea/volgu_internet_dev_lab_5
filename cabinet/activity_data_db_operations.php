<?php
	//This file contains functions which work with the DB.
	/* Contents:
		1) Common functions (used by both datatypes)
		2) Student functions (work with students table)
		3) Activity functions (work with activities table) */
		
	
	# *** Common functions ***
	
	//Connects to activity_data database and sets names to UTF-8 to prevent encoding errors
	function connect_to_activity_database() {
		$db = new PDO('mysql:host=localhost;dbname=activity_data','root','');
		$db->query("SET NAMES utf8");
		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		return $db;
	}
	
	//Returns array of IDs for the specified table
	//!!! DO NOT CALL THIS FUNCTION WITH A VARIABLE !!!
	//Only call this function using string literals, ex. get_ids_from('students')
	//If you need to fetch IDs using activity_id, use get_student_ids_with()!
	function get_ids_from($table) {
		$ids = array();
		
		$db = connect_to_activity_database();
		$query_result = $db->query('SELECT `id` FROM '.$table);
		if ('00000' !== $query_result->errorCode())
			return $ids; //returns empty array
		$i = 0;
		while ($recieved_id = $query_result->fetch(PDO::FETCH_NUM)) {
			$ids[$i] = $recieved_id[0];
			++$i;
		}
		return $ids;
	}
	
	//Displays column names with specified names array
	function display_column_names($names) {
		echo '<p><table class="table table-striped table-bordered"><thead><tr>';
		foreach ($names as $name) {
			echo '<th>'.$name.'</th>';
		}
		echo '<th colspan="2">Действия</th>';
		echo '</tr></thead><tbody id="table_data">'; 
	};
	
	/* These functions actually belong in the "Activity functions", but they are required to display activity names/links in students table (so it'd look more fancy), so it's described here instead */
	//Returns activity name by specified ID
	function get_activity_name($id) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('SELECT activity_name FROM activities WHERE id = :id');
		$query_result->execute(array(
			':id' => $id
		));
		$result = $query_result->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	//Returns path to activity plan file using ID
	function get_file_name_for($id) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('SELECT activity_plan FROM activities WHERE id = :id');
		$query_result->execute(array(
			':id' => $id
		));
		$result = $query_result->fetch(PDO::FETCH_NUM);
		return $result[0];
	}
	
	//Self-explanatory; $type is needed for proper definitions of JS functions used to switch modes for different table types 
	function display_data_using_query_result_and_type($db, $query_result, $type) {
		if ('00000' !== $query_result->errorCode())
			return -1;
		while ($row = $query_result->fetch(PDO::FETCH_NUM)) {
				echo '<tr id="'.$type.'_id_'.(string)$row[0].'">';
				for ($i = 0; $i < $query_result->columnCount(); $i++) {
					//There are some type-specific fields that must be displayed in a different way
					if ('student' === $type && 2 === $i) //This field (from students) corresponds to activity name; we fetch that activity name from DB to display it
						echo '<td>'.get_activity_name($row[$i]).'</td>';
					else if ('activity' === $type && 4 === $i) { //This field (from activities) corresponds to file path; we need to create a link from that path or tell that there's no file
						$file_name = get_file_name_for($row[0]);
						if ('' !== $file_name)
							echo '<td><a href="'.get_file_name_for($row[0]).'">Открыть</a></td>';
						else
							echo '<td>Нет</td>';
					}
					else //Else, just display the value we got from the table
						echo '<td>'.$row[$i].'</td>';
				};
				echo '<td><button class="btn btn-primary" value="'.(string)$row[0].'" onclick="switchTo'.ucfirst($type).'EditMode('.(string)$row[0].')">Редактировать</button></td>';
				echo '<td><button class="btn btn-primary" value="'.(string)$row[0].'" onclick="displayDelete'.ucfirst($type).'ModeFor('.(string)$row[0].')">Удалить</button></td>';
				echo '</tr>';
			}
		echo '</tbody></table><br>';
		return 0;
	}
	
	# *** Student functions ***
	
	//Returns array of studend IDs with a specified activity ID
	//Unlike get_ids_from(), you can safely call it with a variable
	function get_student_ids_with($activity_id) {
		$ids = array();
		
		$db = connect_to_activity_database();
		$query_result = $db->prepare('SELECT `id` FROM students WHERE `activity_id` = :activity_id');
		$query_result->execute(array(
			':activity_id' => $activity_id
		));
		if ('00000' !== $query_result->errorCode())
			return $ids;
		$i = 0;
		while ($recieved_id = $query_result->fetch(PDO::FETCH_NUM)) {
			$ids[$i] = $recieved_id[0];
			++$i;
		}
		return $ids;
	}
	
	//Displays all students in a table
	function display_all_students() {
		$db = connect_to_activity_database();
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/localized_column_names.php'); //This file contains localized column names for tables
		display_column_names($student_column_names); //We display them
		$query_result = $db->query('SELECT * FROM students');
		if (display_data_using_query_result_and_type($db, $query_result, 'student') !== 0)
			return -1;
		return 0;
	}
	
	//Displays students of specified activity ID in a table
	function display_students_of($activity_id) {
		$db = connect_to_activity_database();
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/localized_column_names.php');
		display_column_names($student_column_names);
		$query_result = $db->prepare('SELECT * FROM students WHERE activity_id = :activity_id');
		$query_result->execute(array(
			':activity_id' => $activity_id
		));
		if (display_data_using_query_result_and_type($db, $query_result, 'student') !== 0)
			return -1;
		return 0;
	}
	
	//Adds student using specified parameters
	function add_student($student_name, $activity_id, $date_of_entry, $email, $phone_number, $attended_lessons) {
		
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('INSERT INTO `students` (`id`, `student_name`, `activity_id`, `date_of_entry`, `email`, `phone_number`, `attended_lessons`) VALUES (NULL, :student_name, :activity_id, :date_of_entry, :email, :phone_number, :attended_lessons) ');
		$query_result->execute(array(
			':student_name' => $student_name,
			':activity_id' => $activity_id,
			':date_of_entry' => $date_of_entry,
			':email' => $email,
			':phone_number' => $phone_number,
			':attended_lessons' => $attended_lessons
		));
		if ('00000' !== $query_result->errorCode())
			return -1;
		else
			return 0;
	}
	
	//Edits student with a specified ID by replacing them with specified parameters
	function edit_student($id, $student_name, $activity_id, $date_of_entry, $email, $phone_number, $attended_lessons) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('UPDATE `students` SET `student_name` = :student_name, `activity_id` = :activity_id, `date_of_entry` = :date_of_entry, `email` = :email, `phone_number` = :phone_number, `attended_lessons` = :attended_lessons WHERE `students`.`id` = :id');
		$query_result->execute(array(
			':student_name' => $student_name,
			':activity_id' => $activity_id,
			':date_of_entry' => $date_of_entry,
			':email' => $email,
			':phone_number' => $phone_number,
			':attended_lessons' => $attended_lessons,
			':id' => $id
		));
		if ('00000' !== $query_result->errorCode())
			return -1;
		else
			return 0;
	}
	
	//Deletes student with a specified ID
	function delete_student($id) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('DELETE FROM `students` WHERE `students`.`id` = :id');
		$query_result->execute(array(
			':id' => $id
		));
		if ('00000' !== $query_result->errorCode())
			return -1;
		else
			return 0;
	}
	
	# *** Activity functions ***
	
	//Displays all activities in a table
	function display_activities() {
		$db = connect_to_activity_database();
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/cabinet/localized_column_names.php');
		display_column_names($activity_column_names);
		$query_result = $db->query('SELECT * FROM activities');
		if (display_data_using_query_result_and_type($db, $query_result, 'activity') !== 0)
			return -1;
		return 0;
	}
	
	//Adds activity using specified parameters
	function add_activity($activity_name, $date_of_creation, $teacher_name, $activity_plan) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('INSERT INTO `activities` (`id`, `activity_name`, `date_of_creation`, `teacher_name`, `activity_plan`) VALUES (NULL, :activity_name, :date_of_creation, :teacher_name, :activity_plan)');
		$query_result->execute(array(
			':activity_name' => $activity_name,
			':date_of_creation' => $date_of_creation,
			':teacher_name' => $teacher_name,
			':activity_plan' => $activity_plan
		));
		if ('00000' !== $query_result->errorCode())
			return -1;
		else
			return 0;
	}
	
	//Edits activity of specified ID by replacing them with specified parameters
	function edit_activity($id, $activity_name, $date_of_creation, $teacher_name, $activity_plan) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('UPDATE `activities` SET `activity_name` = :activity_name, `date_of_creation` = :date_of_creation, `teacher_name` = :teacher_name, `activity_plan` = :activity_plan WHERE `activities`.`id` = :id');
		$query_result->execute(array(
			':activity_name' => $activity_name,
			':date_of_creation' => $date_of_creation,
			':teacher_name' => $teacher_name,
			':activity_plan' => $activity_plan,
			':id' => $id
		));
		if ('00000' !== $query_result->errorCode())
			return -1;
		else
			return 0;
	}
	
	//Deletes activity with a specified ID
	function delete_activity($id) {
		$db = connect_to_activity_database();
		
		$query_result = $db->prepare('DELETE FROM `activities` WHERE `activities`.`id` = :id');
		$query_result->execute(array(
			':id' => $id
		));
		if ('00000' !== $query_result->errorCode())
			return -1;
		else
			return 0;
	}
?>
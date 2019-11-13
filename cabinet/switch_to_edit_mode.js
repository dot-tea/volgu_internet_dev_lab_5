//This file contains functions related to dynamic switch between different modes (add, edit, delete)

//Resets student form upon pressing Cancel button
function resetStudentForm() {
	var form = document.forms['student_form'];
	//Emptiying all fields/resetting mode to default value 'add'
	form['mode'].value = 'add';
	form['id'].value = '';
	form['first_name'].value = '';
	form['middle_name'].value = '';
	form['last_name'].value = '';
	form['date_of_entry'].value = '';
	form['attended_lessons'].value = '';
	form['phone_number'].value = '';
	form['email'].value = '';
	if (!form['activity_id'].disabled) { //If we're director, it'd be good if we could go back to the first item in the select box
		form['activity_id'].selectedIndex = 0;
	}
	document.getElementById("student_form_cancel_button").remove(); //remove cancel button afterwards
	var form_name = document.getElementById("form_name"); //change card title
	form_name.innerHTML = "Добавить студента";
}

//Same, but for activity mode
function resetActivityForm() {
	var form = document.forms['activity_form'];
	form['mode'].value = 'add';
	form['id'].value = '';
	form['first_name'].value = '';
	form['middle_name'].value = '';
	form['last_name'].value = '';
	form['activity_name'].value = '';
	form['date_of_creation'].value = '';
	document.getElementById("activity_form_cancel_button").remove();
	document.getElementById("delete_file_checkbox_main").remove();
	var form_name = document.getElementById("form_name");
	form_name.innerHTML = "Добавить кружок";
}

//Switches the form to edit mode for specified id (student)
function switchToStudentEditMode(id) {
	//Change title of the card
	var form_name = document.getElementById("form_name");
	form_name.innerHTML = "Редактировать студента с id = " + id;
	
	row = document.getElementById("student_id_" + id); //gets the row in a table of specified student
	//Copy all values from table
	var name = row.cells[1].innerHTML;
	//We should split the name
	var name_parts = name.split(" ");
	var last_name = name_parts[0];
	var first_name = name_parts[1];
	var middle_name = "";
	if (name_parts.length == 3)
		middle_name = name_parts[2];
	
	var activity = row.cells[2].innerHTML;
	var date_of_entry = row.cells[3].innerHTML;
	var email = row.cells[4].innerHTML;
	var phone_number = row.cells[5].innerHTML;
	var attended_lessons = row.cells[6].innerHTML;
	
	var form = document.forms['student_form'];
	
	//Insert all values to form and switch mode to edit
	form['mode'].value = "edit";
	form['id'].value = id;
	form['first_name'].value = first_name;
	form['middle_name'].value = middle_name;
	form['last_name'].value = last_name;
	if (!form['activity_id'].disabled) { //if we're director, also change select option to the activity of that student
		var opts = form['activity_id'].options;
		for (var opt, j = 0; opt = opts[j]; j++) {
			if (opt.innerHTML === activity) {
				form['activity_id'].selectedIndex = j;
				break;
			}
		}
			
	}
	form['date_of_entry'].value = date_of_entry;
	form['email'].value = email;
	form['phone_number'].value = phone_number;
	form['attended_lessons'].value = attended_lessons;
	
	//Add cancel button
	if (document.getElementById("student_form_cancel_button") === null) {
		var cancel_button = document.createElement("button");
		cancel_button.id = "student_form_cancel_button";
		cancel_button.className = "btn btn-primary";
		cancel_button.type = "button";
		cancel_button.onclick = function() {
			resetStudentForm();
		};
		cancel_button.innerHTML = "Отмена";
		form.appendChild(cancel_button);
	}

	form_name.scrollIntoView();
	
}

//Switches the form to edit mode for specified id (activity)
function switchToActivityEditMode(id) {
	//The basic logic is the same as the previous function, but specific for the fields from activity table
	var form_name = document.getElementById("form_name");
	form_name.innerHTML = "Редактировать кружок с id = " + id;
	
	row = document.getElementById("activity_id_" + id);
	var activity_name = row.cells[1].innerHTML;
	var date_of_creation = row.cells[2].innerHTML;
	
	var name = row.cells[3].innerHTML;
	var name_parts = name.split(" ");
	var last_name = name_parts[0];
	var first_name = name_parts[1];
	var middle_name = "";
	if (name_parts.length == 3)
		middle_name = name_parts[2];
	
	var form = document.forms['activity_form'];
	
	form['mode'].value = "edit";
	form['id'].value = id;
	form['first_name'].value = first_name;
	form['middle_name'].value = middle_name;
	form['last_name'].value = last_name;
	form['activity_name'].value = activity_name;
	form['date_of_creation'].value = date_of_creation;
	
	if (document.getElementById("activity_form_cancel_button") === null) {
		var cancel_button = document.createElement("button");
		cancel_button.id = "activity_form_cancel_button";
		cancel_button.className = "btn btn-primary";
		cancel_button.type = "button";
		cancel_button.onclick = function() {
			resetActivityForm();
		};
		cancel_button.innerHTML = "Отмена";
		form.appendChild(cancel_button);
	}
	
	//We also need to add a checkbox which allows us to delete file from server if we want so
	if (document.getElementById("delete_file_checkbox") === null) {
		var checkbox_main = document.createElement("div");
		checkbox_main.className = "col";
		checkbox_main.id = "delete_file_checkbox_main";
		var label = document.createElement("label");
		label.for = "delete_file_checkbox";
		label.innerHTML = "Удалить файл";
		checkbox_main.appendChild(label);
		var checkbox = document.createElement("input");
		checkbox.type = "checkbox";
		checkbox.id = "delete_file_checkbox";
		checkbox.name = "delete_file_checkbox";
		checkbox_main.appendChild(checkbox);
		var file_upload_manager = document.getElementById("file_upload_manager");
		file_upload_manager.appendChild(checkbox_main);
	}

	form_name.scrollIntoView();
}

//Displays a card which warns user about deleting a student
function displayDeleteStudentModeFor(id) {
	
	var delete_title = document.getElementById("delete_title");
	delete_title.innerHTML = "Удалить ученика с id = " + id;
	
	var warning_message = document.getElementById("delete_warning_message");
	var name = document.getElementById("student_id_" + id).cells[1].innerHTML;
	warning_message.innerHTML = "Вы действительно хотите удалить ученика по имени " + name + "?";
	
	//We specify the id in the delete form, so when we send the form, it'll know what id should be deleted (check for id and permission is server-side)
	var current_id = document.getElementById("delete_form_id");
	current_id.value = id;
	
	var submit_button = document.getElementById("delete_form_submit");
	submit_button.disabled = false;
	
	var delete_form = document.getElementById("db_delete");
	delete_form.style.display = "block";
	
	delete_title.scrollIntoView();
	
}

//Same, but for activity
function displayDeleteActivityModeFor(id) {
	var delete_title = document.getElementById("delete_title");
	delete_title.innerHTML = "Удалить кружок с id = " + id;
	
	var warning_message = document.getElementById("delete_warning_message");
	var name = document.getElementById("activity_id_" + id).cells[1].innerHTML;
	warning_message.innerHTML = "Вы действительно хотите удалить кружок под названием " + name + "?";
	
	var current_id = document.getElementById("delete_form_id");
	current_id.value = id;
	
	var submit_button = document.getElementById("delete_form_submit");
	submit_button.disabled = false;
	
	var delete_form = document.getElementById("db_delete");
	delete_form.style.display = "block";
	
	delete_title.scrollIntoView();
}

//Hides delete card

function removeDeletePrompt() {
	var delete_form = document.getElementById("db_delete");
	delete_form.style.display = "none";
	var submit_button = document.getElementById("delete_form_submit");
	submit_button.disabled = true;
}
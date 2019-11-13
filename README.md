# Лабораторная работа №5
Ссылка, текст ТЗ и скринкасты приведены в Гугл-папке.

# Изменения после предыдущей лабораторной
* В дальнейшем, если не указывается обратного, считается, что все упомянутые файлы находятся в **/cabinet/**
* Вместо того, чтобы хранить учеников каждого кружка в отдельных таблицах базы данных **students**, я храню их в единой таблице **students** с новым полем **activity_id**, по которому и будут выводиться ученики соответствующего кружка. Новая таблица БД называется **activity_data**.
* **students_data_db_operations.php** стала **activity_data_db_operations.php** и теперь содержит функции для работы как с students, так и с новой таблицей activities, которая содержит данные о кружках.
* Вывод ошибок со стороны сервера при передаче формы добавления студента на сервер был вынесен из **my_cabinet.php** в **catch_errors_for_activities_table.php**. Также, был создан файл **catch_errors_for_activity_table.php**, который содержит вывод ошибок, специфических для таблицы кружков.
* **add_student.php** стал **edit_student_table.php** и теперь содержит функционал для всех трех действий (добавление, редактирование, удаление). Также, была написана версия скрипта, специфичная для таблицы кружков -- **edit_activity_table.php**.
* Формы для добавления новых студентов и кружков были вынесены из **my_cabinet.php** в отдельные HTML-шаблоны: **student_edit_interface** и **activity_edit_interface**.
* **validate_student_form.js** стала **validate_form.js** и теперь содержит описание функций, валидирующих формы обеих типов данных.
* Массив $users в **/include/data/userdata_readfrom.php** теперь содержит вместо поля 'activity' поле 'activity_id'. Соответствующее изменение было сделано во всех файлах. 

# Релевантные файлы
* **plans** - папка, в которой содержатся хранимые файлы из таблицы кружков.
* **activity_data_db_operations.php** содержит все функции, связанные с работой в БД activity_data.
* **edit_activity/student_table.php** - файлы, в которых осуществляется валидация и обработка присланных по форме данных, а также вызов функций, выполняющие изменения таблиц в БД.
* **my_cabinet.php** содержит код для выбора таблицы для просмотра.
* **switch_table.php** - скрипт, в котором осуществляется изменение таблицы по имеющемуся разрешению. 
* **switch_to_edit_mode.js** содержит функции, связанные с переходом между режимами добавления, редактирования и удаления в формах обоих типов данных.
* **validate_form.js** содержит функции, валидирующие и проверяющие цельность форм, которые будут отправлены на сервер.

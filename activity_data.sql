-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Ноя 13 2019 г., 19:44
-- Версия сервера: 10.4.6-MariaDB
-- Версия PHP: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `activity_data`
--

-- --------------------------------------------------------

--
-- Структура таблицы `activities`
--

CREATE TABLE `activities` (
  `id` int(4) NOT NULL,
  `activity_name` varchar(100) NOT NULL,
  `date_of_creation` date NOT NULL,
  `teacher_name` varchar(225) NOT NULL,
  `activity_plan` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `activities`
--

INSERT INTO `activities` (`id`, `activity_name`, `date_of_creation`, `teacher_name`, `activity_plan`) VALUES
(1, 'Бумажное искусство', '2019-09-01', 'Лампов Лампа Лампович', ''),
(2, 'Рисование', '2019-08-01', 'Валентинова Валентина Валентиновна', ''),
(10, 'Тест', '2020-01-01', 'Пушкина Александра', '');

-- --------------------------------------------------------

--
-- Структура таблицы `students`
--

CREATE TABLE `students` (
  `id` int(4) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `activity_id` int(4) NOT NULL,
  `date_of_entry` date NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `attended_lessons` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `students`
--

INSERT INTO `students` (`id`, `student_name`, `activity_id`, `date_of_entry`, `email`, `phone_number`, `attended_lessons`) VALUES
(1, 'Иванова Наталья Николаевна', 1, '2019-09-03', 'nata@somewhere.ru', '+74268361612', 5),
(2, 'Александрова Ольга Анатольевна', 1, '2019-09-03', 'whatever@luchshayapochta.ru', '+73289423433', 4),
(3, 'Соколов Антон Валерьевич', 1, '2019-09-03', 'yep@yep.ru', '+73243124132', 3),
(4, 'Кузнецова Елизавета Сергеева', 1, '2019-09-01', 'well@wow.ru', '+79376643423', 8),
(5, 'Пушкина Александра Сергеевна', 1, '2019-11-02', 'welly@wow.ru', '+79999999997', 4),
(6, 'Буянов Ладно Океевич', 1, '2008-02-29', 'ladno@vk.com', '+79999999999', 1),
(7, 'Сильвесторов Василий', 1, '2019-09-01', 'pust.budet@ya.ru', '', 3),
(12, 'Новый Студент', 2, '2019-11-01', '', '', 12),
(13, 'Некто Некто Некто', 2, '2019-10-01', 'nani@omaewa.ru', '+74268361612', 3),
(24, 'Редактирую Без Проблем', 10, '2000-11-03', '', '', 3);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_students_activity_id` (`activity_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `students`
--
ALTER TABLE `students`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `FK_students_activity_id` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

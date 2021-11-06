INSERT INTO user (registration, email, name, password) VALUES
(CURRENT_TIMESTAMP, 'a@abc.ru', 'Азамат', '123456'),
(CURRENT_TIMESTAMP, 'b@abc.ru', 'Нурсултан', '123456'),
(CURRENT_TIMESTAMP, 'c@abc.ru', 'Владимир', '123456');
INSERT INTO project (user_id, name) VALUES
(1, 'Входящие'),
(2, 'Учеба'),
(3, 'Работа'),
(2, 'Домашние дела'),
(1, 'Авто');
INSERT INTO task (project_id, create_date, state, name, file_name, expiration) VALUES
(3, CURRENT_TIMESTAMP, 0, 'Собеседование в IT компании', NULL, '2021-11-05'),
(3, CURRENT_TIMESTAMP, 0, 'Выполнить тестовое задание', NULL, '2021-11-05'),
(2, CURRENT_TIMESTAMP, 0, 'Сделать задание первого раздела', NULL, '2021-11-05'),
(1, CURRENT_TIMESTAMP, 0, 'Встреча с другом', NULL, '2021-11-04'),
(4, CURRENT_TIMESTAMP, 0, 'Купить корм для кота', NULL, '2021-11-04'),
(4, CURRENT_TIMESTAMP, 0, 'Заказать пиццу', NULL, '2021-11-03');
-- получить список из всех проектов для одного пользователя
SELECT p.name, u.name FROM project AS p INNER JOIN user AS u ON p.user_id = u.id;
-- получить список из всех задач для одного проекта
SELECT * FROM task INNER JOIN project ON project_id = project.id;
-- пометить задачу как выполненную
UPDATE task SET state = 1 WHERE name = 'Встреча с другом';
-- обновить название задачи по её идентификатору
UPDATE task SET name = 'Встреча с подругой' WHERE id = 4;


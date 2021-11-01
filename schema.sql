CREATE DATABASE doingsdone DEFAULT CHARACTER SET UTF8 DEFAULT COLLATE UTF8_GENERAL_CI;
USE doingsdone;
CREATE TABLE user(
	id INT AUTO_INCREMENT PRIMARY KEY,
	registration TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	email VARCHAR(128) NOT NULL UNIQUE,
	name CHAR(255) NOT NULL,
	password CHAR(64)
);
CREATE INDEX email_idx ON user(email);
CREATE TABLE project(
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT, 
	name CHAR(255) NOT NULL,
	FOREIGN KEY (user_id) REFERENCES user(id)
);
CREATE INDEX name ON project(name);
CREATE TABLE task(
	id INT AUTO_INCREMENT PRIMARY KEY,
	project_id INT,
	create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	state TINYINT(1) NOT NULL,
	name CHAR(255) NOT NULL,
	file_name VARCHAR(256),
	expiration DATE,
	FOREIGN KEY (project_id) REFERENCES project(id)
);
CREATE INDEX expiration ON task(expiration);
create database Registration;
use Registration;
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    description TEXT
);
ALTER TABLE user
DROP COLUMN image,
DROP COLUMN description;
SHOW COLUMNS FROM user;


SELECT * FROM user;
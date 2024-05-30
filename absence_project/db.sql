-- Create the database (run this part only if the database does not exist)
CREATE DATABASE IF NOT EXISTS absencce_project;

-- Use the database
USE absencce_project;

-- Create the Teachers table
CREATE TABLE IF NOT EXISTS Teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL
);

-- Insert the default teacher account
INSERT INTO Teachers (username, password, name) VALUES ('teacher@gmail.com', 'password', 'Default Teacher');

-- Create the Classes table
CREATE TABLE IF NOT EXISTS Classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL
);

-- Create the Students table
CREATE TABLE IF NOT EXISTS Students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    class_id INT,
    FOREIGN KEY (class_id) REFERENCES Classes(id)
);

-- Create the Modules table
CREATE TABLE IF NOT EXISTS Modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(100) NOT NULL,
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES Teachers(id)
);

-- Create the Absences table
CREATE TABLE IF NOT EXISTS Absences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE NOT NULL,
    reason VARCHAR(255),
    FOREIGN KEY (student_id) REFERENCES Students(id)
);
-- Create the Students table
CREATE TABLE IF NOT EXISTS Students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    class_id INT,
    date_of_birth DATE,
    FOREIGN KEY (class_id) REFERENCES Classes(id)
);

CREATE DATABASE `CLUB-MANAGEMENT-SYSTEM`;
USE `CLUB-MANAGEMENT-SYSTEM`;

-- Object
CREATE TABLE CLUB (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100),
    logo BLOB,
    created_date DATE DEFAULT (DATE(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')))


-- User
CREATE TABLE `ADMIN` (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(10),
    `password` VARCHAR(255),
    created_date DATE DEFAULT (DATE(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')))
);

CREATE TABLE CLUBER (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(10),
    `password` VARCHAR(255),
    club_id INT NOT NULL,
    FOREIGN KEY(club_id) REFERENCES CLUB(id),
    created_date DATE DEFAULT (DATE(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')))
);

CREATE TABLE STUDENT (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(10),
    `password` VARCHAR(255),
    google_id VARCHAR(50),         -- Google unique user ID
    email VARCHAR(255) NOT NULL,   -- Email from Google or manual signup
    `name` VARCHAR(20),
    phone VARCHAR(15),
    program VARCHAR(100),
    logo BLOB,
    created_date DATE DEFAULT (DATE(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')))
);

-- Activity
CREATE TABLE CLUB_ACTIVITY (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100),
    `start` DATETIME,
    `end` DATETIME,
    merit_point INT,
    club_id INT NOT NULL,
    created_date DATE DEFAULT (DATE(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00'))),
    FOREIGN KEY(club_id) REFERENCES CLUB(id)
);

CREATE TABLE ACTIVITY_PARTICIPANT (
    student_id INT NOT NULL,
    club_activity_id INT NOT NULL,
    joined DATETIME DEFAULT (CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')),
    FOREIGN KEY (student_id) REFERENCES STUDENT(id),
    FOREIGN KEY (club_activity_id) REFERENCES CLUB_ACTIVITY(id),
    PRIMARY KEY (student_id, club_activity_id)
);

CREATE TABLE CLUB_PARTICIPANT (
    student_id INT NOT NULL,
    club_id INT NOT NULL,
    joined DATE DEFAULT (CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')),
    position VARCHAR(50),
    merit_point INT,
    FOREIGN KEY (student_id) REFERENCES STUDENT(id),
    FOREIGN KEY (club_id) REFERENCES CLUB(id),
    PRIMARY KEY (student_id, club_id)
)
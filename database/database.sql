USE css_sit_in;



-- Reservations table
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    date DATE,
    laboratory VARCHAR(50),
    time_slot VARCHAR(50),
    purpose TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(USERNAME)
);

-- Insert default admin account with:
-- Username: admin
-- Password: password
INSERT INTO users (ID_NUMBER, LASTNAME, FIRSTNAME, USERNAME, PASSWORD, user_type, EMAIL) 
VALUES (
    '999999', 
    'CSS', 
    'Admin', 
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is the hashed version of 'password'
    'admin',
    'admin@uc.edu.ph'
);

-- Announcements table
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_username VARCHAR(50),
    content TEXT,
    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_username) REFERENCES users(USERNAME)
);

-- Update sit_in_sessions table
DROP TABLE IF EXISTS sit_in_sessions;
CREATE TABLE sit_in_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id int(11),
    lab_room VARCHAR(50),
    purpose VARCHAR(100),
    start_time DATETIME,
    end_time DATETIME DEFAULT NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(ID_NUMBER)
);

-- Feedback table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    rating INT,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sit_in_sessions(id)
);

ALTER TABLE feedback
ADD COLUMN user_id INT NOT NULL AFTER session_id,
ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(ID);

------------------------------------------------------------------------CORRECTED DATABASE.SQL FILE----------------------------------------------------------------------------------------------------------------------------
ALTER TABLE users 
MODIFY COLUMN SESSION INT DEFAULT 30;


CREATE TABLE `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_NUMBER` int(11) NOT NULL UNIQUE,  
  `LASTNAME` varchar(50) NOT NULL,
  `FIRSTNAME` varchar(50) NOT NULL,
  `MIDDLENAME` varchar(50) DEFAULT NULL,
  `COURSE` varchar(100) DEFAULT NULL,
  `YEAR` int(11) DEFAULT NULL,
  `USERNAME` varchar(50) UNIQUE NOT NULL,
  `PASSWORD` varchar(100) NOT NULL,
  `EMAIL` varchar(100) UNIQUE NOT NULL,
  `ADDRESS` varchar(30) DEFAULT NULL,
  `SESSION` INT(11) DEFAULT 30 NULL,
  `IMAGE` varchar(100) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` ENUM('student', 'admin') DEFAULT 'student' NOT NULL,
  PRIMARY KEY (`ID`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE sit_in_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id int(11),
    lab_room VARCHAR(50),
    purpose VARCHAR(100),
    start_time DATETIME,
    end_time DATETIME DEFAULT NULL,
    status ENUM('active', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(ID_NUMBER) 
);

CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_username VARCHAR(50),
    content TEXT,
    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_username) REFERENCES users(USERNAME)
);


CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    rating INT,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sit_in_sessions(id)
);

ALTER TABLE feedback
ADD COLUMN user_id INT NOT NULL AFTER session_id,
ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(ID);

INSERT INTO users (ID_NUMBER, LASTNAME, FIRSTNAME, USERNAME, PASSWORD, user_type, EMAIL) 
VALUES (
    '999999', 
    'CSS', 
    'Admin', 
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is the hashed version of 'password'
    'admin',
    'admin@uc.edu.ph'
);

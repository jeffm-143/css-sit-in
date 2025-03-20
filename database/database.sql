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
    student_id VARCHAR(50),
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
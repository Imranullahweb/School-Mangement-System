-- School Management System Database Schema

-- Admin settings and license
CREATE TABLE IF NOT EXISTS settings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  license_key VARCHAR(50),
  license_type VARCHAR(10) DEFAULT 'free',
  admin_username VARCHAR(50) NOT NULL,
  admin_password VARCHAR(255) NOT NULL
);

-- Students
CREATE TABLE IF NOT EXISTS students (
  id INT PRIMARY KEY AUTO_INCREMENT,
  reg_no VARCHAR(30) NOT NULL,
  student_id VARCHAR(30) NOT NULL,
  admission_date DATE NOT NULL,
  name VARCHAR(100) NOT NULL,
  father_name VARCHAR(100) NOT NULL,
  dob DATE NOT NULL,
  address VARCHAR(255),
  class VARCHAR(30) NOT NULL,
  religion VARCHAR(30),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers
CREATE TABLE IF NOT EXISTS teachers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  teacher_id VARCHAR(30) NOT NULL,
  name VARCHAR(100) NOT NULL,
  father_name VARCHAR(100) NOT NULL,
  designation VARCHAR(50),
  qualification VARCHAR(100),
  phone VARCHAR(20),
  address VARCHAR(255),
  salary DECIMAL(10,2),
  appointment_date DATE NOT NULL,
  leaving_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student Leaving Certificates
CREATE TABLE IF NOT EXISTS student_certificates (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id VARCHAR(30) NOT NULL,
  class VARCHAR(30),
  conduct VARCHAR(100),
  remarks VARCHAR(255),
  leaving_date DATE,
  prepared_by VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teacher Experience Certificates
CREATE TABLE IF NOT EXISTS teacher_experience (
  id INT PRIMARY KEY AUTO_INCREMENT,
  teacher_id VARCHAR(30) NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student Fees
CREATE TABLE IF NOT EXISTS student_fees (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id VARCHAR(30) NOT NULL,
  class VARCHAR(30),
  month VARCHAR(20),
  year INT,
  amount DECIMAL(10,2),
  status ENUM('Paid','Unpaid') DEFAULT 'Unpaid',
  paid_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Marksheet
CREATE TABLE IF NOT EXISTS marksheets (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id VARCHAR(30) NOT NULL,
  exam_name VARCHAR(50),
  total_marks INT,
  obtained_marks INT,
  percentage DECIMAL(5,2),
  grade VARCHAR(5),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Marksheet Subjects
CREATE TABLE IF NOT EXISTS marksheet_subjects (
  id INT PRIMARY KEY AUTO_INCREMENT,
  marksheet_id INT NOT NULL,
  subject_name VARCHAR(50),
  total_marks INT,
  obtained_marks INT,
  FOREIGN KEY (marksheet_id) REFERENCES marksheets(id) ON DELETE CASCADE
);

-- Exam Schedule (for admit cards)
CREATE TABLE IF NOT EXISTS exam_schedule (
  id INT PRIMARY KEY AUTO_INCREMENT,
  exam_name VARCHAR(50),
  class VARCHAR(30),
  subject VARCHAR(50),
  room VARCHAR(20),
  exam_date DATE,
  start_time TIME,
  end_time TIME
);

-- Student Photos (for admit cards)
CREATE TABLE IF NOT EXISTS student_photos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id VARCHAR(30) NOT NULL,
  photo_path VARCHAR(255)
); 
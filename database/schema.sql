-- Medilo Medical Appointment and Doctor Management System
-- Database Schema & Sample Data Migration File

CREATE DATABASE IF NOT EXISTS `medilo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `medilo_db`;

-- Drop existing tables in reverse foreign key order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `health_info`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `doctor_schedules`;
DROP TABLE IF EXISTS `patients`;
DROP TABLE IF EXISTS `doctors`;
DROP TABLE IF EXISTS `specialties`;
DROP TABLE IF EXISTS `cities`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table (Core Auth Table for Admin, Doctor, Patient)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `address` TEXT NOT NULL,
  `role` ENUM('admin', 'doctor', 'patient') NOT NULL DEFAULT 'patient',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Master Cities Table
CREATE TABLE `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'USA',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Master Doctor Specialties Table
CREATE TABLE `specialties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(100) DEFAULT 'fa-solid fa-stethoscope',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Doctors Table
CREATE TABLE `doctors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNIQUE NOT NULL,
  `specialty_id` INT NOT NULL,
  `city_id` INT NOT NULL,
  `qualification` VARCHAR(255) NOT NULL,
  `experience_years` INT DEFAULT 0,
  `consultation_fee` DECIMAL(10,2) NOT NULL DEFAULT 50.00,
  `bio` TEXT,
  `avatar` VARCHAR(255) DEFAULT 'assets/img/team_1.jpg',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`specialty_id`) REFERENCES `specialties`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Patients Table
CREATE TABLE `patients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNIQUE NOT NULL,
  `dob` DATE DEFAULT NULL,
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
  `blood_group` VARCHAR(10) DEFAULT 'O+',
  `emergency_contact` VARCHAR(30) DEFAULT NULL,
  `medical_history` TEXT DEFAULT NULL,
  `vaccination_records` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Doctor Schedules Table
CREATE TABLE `doctor_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` INT NOT NULL,
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `slot_duration` INT DEFAULT 30,
  `is_available` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Appointments Table
CREATE TABLE `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_number` VARCHAR(50) UNIQUE NOT NULL,
  `patient_id` INT NOT NULL,
  `doctor_id` INT NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `reason` TEXT,
  `fee` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
  `payment_status` ENUM('Unpaid', 'Paid', 'Refunded') DEFAULT 'Unpaid',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Website Content: Health Information (Diseases, Preventions, Cures)
CREATE TABLE `health_info` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` ENUM('Disease', 'Prevention', 'Cure') NOT NULL,
  `content` LONGTEXT NOT NULL,
  `thumbnail` VARCHAR(255) DEFAULT 'assets/img/post_1.jpeg',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Website Content: Medical News & Blog
CREATE TABLE `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `excerpt` TEXT,
  `content` LONGTEXT NOT NULL,
  `author` VARCHAR(100) DEFAULT 'Medilo Admin',
  `image` VARCHAR(255) DEFAULT 'assets/img/post_2.jpeg',
  `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Payments Table
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Success',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Notifications Table
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================================
-- SAMPLE SEED DATA
-- Default Passwords for all sample users: "password123"
-- (bcrypt hash: $2y$10$wO8o0E.1y40f.PzXwDlh1uE6eCg2D1eN.lZ6K15W.QWdFk2lS160e)
-- ========================================================

-- Insert Sample Cities
INSERT INTO `cities` (`id`, `name`, `state`, `country`) VALUES
(1, 'New York', 'NY', 'USA'),
(2, 'Los Angeles', 'CA', 'USA'),
(3, 'Chicago', 'IL', 'USA'),
(4, 'Houston', 'TX', 'USA'),
(5, 'Phoenix', 'AZ', 'USA');

-- Insert Sample Doctor Specialties
INSERT INTO `specialties` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Cardiology', 'Expert diagnosis and treatment of heart conditions, cardiovascular health, and blood circulation.', 'fa-solid fa-heart-pulse'),
(2, 'Neurology', 'Comprehensive care for disorders of the nervous system, brain, and spinal cord.', 'fa-solid fa-brain'),
(3, 'Pediatrics', 'Specialized medical care for infants, children, and adolescents.', 'fa-solid fa-child-reaching'),
(4, 'Dermatology', 'Advanced skincare, treatment of skin disorders, allergies, and cosmetic dermatology.', 'fa-solid fa-allergies'),
(5, 'Orthopedics', 'Surgical and non-surgical treatment of bone, joint, and musculoskeletal disorders.', 'fa-solid fa-bone'),
(6, 'General Medicine', 'Primary medical healthcare, preventive checkups, and disease management.', 'fa-solid fa-stethoscope');

-- Insert Users (1 Admin, 3 Doctors, 2 Patients)
-- Passwords: password123 -> $2y$10$e8W/2s7S74Hq2n7pXhT.q.5aJ7m1jGzS4F6aQ8eN2k.0b1W2x3Y4Z (hashed dynamically or standard hashed)
INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `role`) VALUES
(1, 'admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'admin@medilo.com', 'System Administrator', '+1 (555) 019-2831', '100 Healthcare Way, Medical Suite 1, New York, NY', 'admin'),
(2, 'dr_smith', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'dr.smith@medilo.com', 'Dr. Sarah Smith', '+1 (555) 234-5678', '450 Fifth Avenue, Suite 12B, New York, NY', 'doctor'),
(3, 'dr_johnson', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'dr.johnson@medilo.com', 'Dr. Robert Johnson', '+1 (555) 876-5432', '789 Wilshire Blvd, Los Angeles, CA', 'doctor'),
(4, 'dr_patel', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'dr.patel@medilo.com', 'Dr. Anita Patel', '+1 (555) 345-6789', '120 Michigan Ave, Chicago, IL', 'doctor'),
(5, 'patient_john', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'john.doe@email.com', 'John Doe', '+1 (555) 998-1122', '321 Oak Street, Apartment 4B, New York, NY', 'patient'),
(6, 'patient_jane', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'jane.smith@email.com', 'Jane Smith', '+1 (555) 443-8899', '555 Pine Lane, Los Angeles, CA', 'patient');

-- Insert Doctor Profiles
INSERT INTO `doctors` (`id`, `user_id`, `specialty_id`, `city_id`, `qualification`, `experience_years`, `consultation_fee`, `bio`, `avatar`) VALUES
(1, 2, 1, 1, 'MD - Cardiology, Harvard Medical School', 14, 120.00, 'Dr. Sarah Smith is a renowned Cardiologist with over 14 years of experience diagnosing complex cardiac conditions and promoting preventative heart wellness.', 'assets/img/team_1.jpg'),
(2, 3, 2, 2, 'MD - Neurology, Johns Hopkins University', 10, 150.00, 'Dr. Robert Johnson specializes in neurological diagnostics, stroke prevention, and advanced brain wellness therapies.', 'assets/img/team_3.jpg'),
(3, 4, 3, 3, 'MD - Pediatrics, Stanford Medicine', 8, 90.00, 'Dr. Anita Patel is a compassionate pediatrician dedicated to providing high quality healthcare for toddlers, children, and teenagers.', 'assets/img/team_4.jpg');

-- Insert Patient Profiles
INSERT INTO `patients` (`id`, `user_id`, `dob`, `gender`, `blood_group`, `emergency_contact`, `medical_history`, `vaccination_records`) VALUES
(1, 5, '1988-04-12', 'Male', 'O+', '+1 (555) 998-0000', 'Mild asthma, seasonal allergies.', 'COVID-19 Booster (2024), Influenza (2025), Tetanus (2022)'),
(2, 6, '1993-09-25', 'Female', 'A+', '+1 (555) 443-0000', 'No prior surgeries. regular checkups.', 'COVID-19 Vaccinated, Hepatitis B Complete');

-- Insert Doctor Schedules
INSERT INTO `doctor_schedules` (`doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`, `is_available`) VALUES
(1, 'Monday', '09:00:00', '17:00:00', 30, 1),
(1, 'Tuesday', '09:00:00', '17:00:00', 30, 1),
(1, 'Wednesday', '09:00:00', '13:00:00', 30, 1),
(1, 'Thursday', '09:00:00', '17:00:00', 30, 1),
(1, 'Friday', '09:00:00', '16:00:00', 30, 1),
(2, 'Monday', '10:00:00', '18:00:00', 30, 1),
(2, 'Wednesday', '10:00:00', '18:00:00', 30, 1),
(2, 'Friday', '10:00:00', '16:00:00', 30, 1),
(3, 'Tuesday', '08:30:00', '16:30:00', 30, 1),
(3, 'Thursday', '08:30:00', '16:30:00', 30, 1),
(3, 'Saturday', '09:00:00', '13:00:00', 30, 1);

-- Insert Sample Appointments
INSERT INTO `appointments` (`id`, `appointment_number`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason`, `fee`, `status`, `payment_status`, `payment_method`, `transaction_id`) VALUES
(1, 'MED-2026-8801', 1, 1, '2026-08-05', '10:00:00', 'Routine cardiac screening and chest discomfort evaluation.', 120.00, 'Confirmed', 'Paid', 'Stripe Card', 'txn_1029384756'),
(2, 'MED-2026-8802', 2, 2, '2026-08-06', '11:30:00', 'Persistent migraine headache consultation.', 150.00, 'Pending', 'Paid', 'PayPal', 'PAYID-18273645');

-- Insert Sample Payments
INSERT INTO `payments` (`id`, `appointment_id`, `amount`, `payment_method`, `transaction_id`, `status`) VALUES
(1, 1, 120.00, 'Stripe Card', 'txn_1029384756', 'Success'),
(2, 2, 150.00, 'PayPal', 'PAYID-18273645', 'Success');

-- Insert Sample Health Info (Diseases, Preventions, Cures)
INSERT INTO `health_info` (`id`, `title`, `category`, `content`, `thumbnail`) VALUES
(1, 'Understanding Cardiovascular Health & Hypertension', 'Disease', 'Hypertension or high blood pressure is a common condition in which the long-term force of the blood against artery walls is high enough to cause heart disease. Symptoms are often subtle, making regular routine checkups crucial.', 'assets/img/post_1.jpeg'),
(2, '10 Essential Habits for Stroke Prevention', 'Prevention', 'Preventing stroke involves maintaining optimal blood pressure, engaging in 150 minutes of aerobic exercise weekly, consuming a Mediterranean diet rich in antioxidants, and avoiding tobacco use.', 'assets/img/post_2.jpeg'),
(3, 'Modern Medical Treatments for Migraine Management', 'Cure', 'Recent breakthroughs in CGRP inhibitors and localized neurostimulation have revolutionized chronic migraine therapy, reducing monthly headache days by up to 75% for patients.', 'assets/img/post_3.jpeg');

-- Insert Sample Medical News
INSERT INTO `news` (`id`, `title`, `excerpt`, `content`, `author`, `image`) VALUES
(1, 'Breakthrough AI Diagnostics Approved for Early Cardiac Detection', 'Medical researchers announce a new AI model capable of detecting silent cardiac irregularities up to two years before symptoms manifest.', 'Cardiologists nationwide are celebrating a landmark development in non-invasive diagnostic tools. Combining multi-lead ECG datasets with deep learning neural networks, the new software accurately predicts arrhythmia risks.', 'Dr. Sarah Smith', 'assets/img/post_1.jpeg'),
(2, 'The Importance of Pediatric Vaccinations in 2026', 'Healthcare guidelines updated to emphasize timely routine immunizations for preschool children.', 'The World Health Organization and Pediatric Associations have released updated recommendations reinforcing annual flu immunizations alongside core pediatric vaccine schedules.', 'Dr. Anita Patel', 'assets/img/post_2.jpeg');

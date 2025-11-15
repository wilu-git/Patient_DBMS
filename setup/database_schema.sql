-- =====================================================
-- Patient Database Management System (Patient DBMS)
-- Database Schema SQL Script
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS patient_dbms 
CHARACTER SET utf8 
COLLATE utf8_general_ci;

-- Use the database
USE patient_dbms;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('doctor', 'secretary', 'developer', 'accountant') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 2. PATIENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS patients (
    id INT(11) NOT NULL AUTO_INCREMENT,
    patient_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    medical_history TEXT,
    allergies TEXT,
    insurance_provider VARCHAR(100),
    insurance_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11),
    is_active BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 3. APPOINTMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS appointments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    appointment_id VARCHAR(20) NOT NULL UNIQUE,
    patient_id INT(11) NOT NULL,
    doctor_id INT(11) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_type ENUM('Consultation', 'Follow-up', 'Emergency', 'Surgery', 'Check-up') NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled', 'No-show') DEFAULT 'Scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11),
    PRIMARY KEY (id),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 4. SERVICES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS services (
    id INT(11) NOT NULL AUTO_INCREMENT,
    service_name VARCHAR(100) NOT NULL,
    service_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 5. BILLING TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS billing (
    id INT(11) NOT NULL AUTO_INCREMENT,
    bill_id VARCHAR(20) NOT NULL UNIQUE,
    patient_id INT(11) NOT NULL,
    appointment_id INT(11),
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    balance DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Partial', 'Paid', 'Overdue') DEFAULT 'Pending',
    billing_date DATE NOT NULL,
    due_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11),
    PRIMARY KEY (id),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 6. BILLING ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS billing_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    billing_id INT(11) NOT NULL,
    service_id INT(11) NOT NULL,
    quantity INT(11) DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (billing_id) REFERENCES billing(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 7. TRANSACTIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    transaction_id VARCHAR(20) NOT NULL UNIQUE,
    billing_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'Credit Card', 'Debit Card', 'Insurance', 'Bank Transfer', 'Check') NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reference_number VARCHAR(50),
    notes TEXT,
    created_by INT(11),
    PRIMARY KEY (id),
    FOREIGN KEY (billing_id) REFERENCES billing(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- 8. AUDIT LOG TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11),
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT(11),
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- CREATE INDEXES FOR PERFORMANCE
-- =====================================================

-- Users table indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_is_active ON users(is_active);

-- Patients table indexes
CREATE INDEX idx_patients_patient_id ON patients(patient_id);
CREATE INDEX idx_patients_is_active ON patients(is_active);
CREATE INDEX idx_patients_created_by ON patients(created_by);
CREATE INDEX idx_patients_name ON patients(last_name, first_name);

-- Appointments table indexes
CREATE INDEX idx_appointments_appointment_id ON appointments(appointment_id);
CREATE INDEX idx_appointments_patient_id ON appointments(patient_id);
CREATE INDEX idx_appointments_doctor_id ON appointments(doctor_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_appointments_date_status ON appointments(appointment_date, status);

-- Billing table indexes
CREATE INDEX idx_billing_bill_id ON billing(bill_id);
CREATE INDEX idx_billing_patient_id ON billing(patient_id);
CREATE INDEX idx_billing_payment_status ON billing(payment_status);
CREATE INDEX idx_billing_date ON billing(billing_date);

-- Transactions table indexes
CREATE INDEX idx_transactions_transaction_id ON transactions(transaction_id);
CREATE INDEX idx_transactions_billing_id ON transactions(billing_id);
CREATE INDEX idx_transactions_payment_date ON transactions(payment_date);

-- Audit log indexes
CREATE INDEX idx_audit_user_id ON audit_log(user_id);
CREATE INDEX idx_audit_action ON audit_log(action);
CREATE INDEX idx_audit_created_at ON audit_log(created_at);
CREATE INDEX idx_audit_table_record ON audit_log(table_name, record_id);

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Insert default users (password: admin123)
INSERT IGNORE INTO users (username, password, email, role, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@clinic.com', 'developer', 'System Administrator'),
('doctor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor@clinic.com', 'doctor', 'Dr. John Smith'),
('secretary1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary@clinic.com', 'secretary', 'Jane Doe'),
('accountant1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@clinic.com', 'accountant', 'Bob Johnson');

-- Insert default services
INSERT IGNORE INTO services (service_name, service_code, description, price, category) VALUES 
('General Consultation', 'CONS001', 'General medical consultation', 50.00, 'Consultation'),
('Follow-up Visit', 'FOLLOW001', 'Follow-up medical visit', 30.00, 'Consultation'),
('Blood Test', 'LAB001', 'Complete blood count test', 25.00, 'Laboratory'),
('X-Ray', 'RAD001', 'Chest X-Ray', 75.00, 'Radiology'),
('Prescription', 'RX001', 'Medication prescription', 15.00, 'Pharmacy'),
('Emergency Visit', 'EMERG001', 'Emergency medical consultation', 100.00, 'Emergency');

SELECT 'Patient DBMS Database Schema Created Successfully!' as Status;


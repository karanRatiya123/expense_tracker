CREATE DATABASE IF NOT EXISTS apexspend_db;
USE apexspend_db;

-- ============================================================
--  users  — written by auth.php (login + signup)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar_path VARCHAR(255) DEFAULT NULL,
    savings_goal DECIMAL(15,2) DEFAULT 50000.00,
    run_rate_target DECIMAL(15,2) DEFAULT 1000000.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id VARCHAR(50) PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('expense', 'income') NOT NULL,
    title VARCHAR(255) NOT NULL,
    note TEXT,
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    date DATETIME NOT NULL,
    method VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS budgets (
    id VARCHAR(50) PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    period_type VARCHAR(50) NOT NULL DEFAULT 'monthly',
    start_date DATE,
    end_date DATE,
    thresholds JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert demo user
INSERT IGNORE INTO users (id, name, email, password, created_at) VALUES 
(1, 'Alex Morgan', 'demo@apexspend.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-07-28 10:00:00');
-- The password hash is for 'password123' generated using BCRYPT. (from config.php)

-- Insert demo transactions
INSERT IGNORE INTO transactions (id, user_id, type, title, note, amount, category, date, method) VALUES 
('tx_201', 1, 'expense', 'Chai Point', 'Evening chai & samosa', 180.00, 'Food & Dining', '2026-07-28 15:45:00', 'UPI ··4182'),
('tx_202', 1, 'expense', 'BigBasket', 'Weekly groceries', 11800.50, 'Food & Dining', '2026-07-28 14:30:00', 'UPI ··4182'),
('tx_203', 1, 'income', 'Tech Corp Payroll', 'Salary, July', 450000.00, 'Income', '2026-07-25 09:00:00', 'NEFT ··0019'),
('tx_204', 1, 'expense', 'Tata Power', 'Electric bill, July', 8240.00, 'Housing & Utilities', '2026-07-22 10:14:00', 'NEFT ··0019'),
('tx_205', 1, 'expense', 'Netflix', 'Standard plan, monthly', 649.00, 'Entertainment', '2026-07-20 18:12:00', 'UPI ··4182'),
('tx_206', 1, 'expense', 'Rapido', 'Ride to Kempegowda Airport, T2', 1420.00, 'Transportation', '2026-07-18 11:30:00', 'UPI'),
('tx_207', 1, 'expense', 'Apollo Pharmacy', 'Vitamins & first-aid', 2150.00, 'Health & Medical', '2026-07-15 19:45:00', 'RuPay ··2207'),
('tx_208', 1, 'expense', 'H&M', 'Summer wardrobe', 6480.00, 'Shopping & Retail', '2026-07-12 16:22:00', 'RuPay ··2207'),
('tx_209', 1, 'expense', 'BESCOM', 'Electricity, June', 6450.00, 'Housing & Utilities', '2026-07-08 10:00:00', 'NEFT ··0019'),
('tx_210', 1, 'expense', 'Indigo', 'Flight BOM → BLR', 8950.00, 'Transportation', '2026-07-05 07:15:00', 'RuPay ··2207'),
('tx_211', 1, 'expense', 'Toit Brewery', 'Dinner with friends', 4200.00, 'Food & Dining', '2026-07-03 20:30:00', 'UPI ··4182'),
('tx_212', 1, 'expense', 'BookMyShow', 'Inox, 4 tickets', 1800.00, 'Entertainment', '2026-07-01 19:00:00', 'UPI ··4182'),
('tx_213', 1, 'expense', 'Airtel Broadband', 'Monthly plan', 999.00, 'Housing & Utilities', '2026-06-28 08:00:00', 'NEFT ··0019'),
('tx_214', 1, 'expense', 'Decathlon', 'Running shoes', 5999.00, 'Shopping & Retail', '2026-06-25 14:10:00', 'RuPay ··2207'),
('tx_215', 1, 'income', 'Freelance Project', 'UI design — milestone 2', 75000.00, 'Income', '2026-06-20 11:00:00', 'IMPS'),
('tx_216', 1, 'expense', 'Shell Petrol', 'Fuel', 3500.00, 'Transportation', '2026-06-18 09:20:00', 'RuPay ··2207'),
('tx_217', 1, 'expense', 'IKEA', 'Desk lamp & organizers', 4150.00, 'Shopping & Retail', '2026-06-15 15:40:00', 'UPI ··4182'),
('tx_218', 1, 'expense', 'Cult Fitness', 'Monthly membership', 2500.00, 'Health & Medical', '2026-06-10 07:30:00', 'NEFT ··0019'),
('tx_219', 1, 'expense', 'Spotify', 'Premium, family plan', 299.00, 'Entertainment', '2026-06-05 09:00:00', 'RuPay ··2207'),
('tx_220', 1, 'expense', 'Manipal Hospital', 'Annual health check-up', 7800.00, 'Health & Medical', '2026-06-02 11:30:00', 'RuPay ··2207');



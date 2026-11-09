-- ========================================================
-- Database Schema for GlobeTrek Adventures (Negombo)
-- Database Name: globetrek_db
-- Compatibility: MySQL (WAMP / phpMyAdmin)
-- ========================================================

CREATE DATABASE IF NOT EXISTS globetrek_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE globetrek_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    images VARCHAR(255) NOT NULL, -- Path or URL to package image
    expertise_type ENUM('Culture & Heritage', 'Nature & Adventure', 'Wellness & Spa', 'Wildlife Safari', 'Shore Excursions') NOT NULL
) ENGINE=InnoDB;

-- 3. Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    travel_date DATE NOT NULL,
    travelers INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Inquiries Table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Pending', 'Resolved') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ========================================================
-- Seed Data (Pre-configured for Testing & Demonstration)
-- ========================================================

-- Seed Default Users
-- Default Password for all seeded users: password123
-- BCRYPT hash generated for password123: $2y$10$wE9KjW5pLz1mZ29U7U4Z.O7jU/k/n9v1oGvC.K75x1c2nZ115l92e
INSERT INTO users (name, email, password_hash, role) VALUES
('John Doe', 'customer@globetrek.com', '$2y$10$wE9KjW5pLz1mZ29U7U4Z.O7jU/k/n9v1oGvC.K75x1c2nZ115l92e', 'customer'),
('Sarah Wick', 'staff@globetrek.com', '$2y$10$wE9KjW5pLz1mZ29U7U4Z.O7jU/k/n9v1oGvC.K75x1c2nZ115l92e', 'staff'),
('Robert Ford', 'admin@globetrek.com', '$2y$10$wE9KjW5pLz1mZ29U7U4Z.O7jU/k/n9v1oGvC.K75x1c2nZ115l92e', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Seed Default Tour Packages (Tailored around Negombo & Sri Lanka Travel)
INSERT INTO packages (id, title, destination, price, description, images, expertise_type) VALUES
(1, 'Negombo Dutch Fort & Historical Canal Cruise', 'Negombo, Sri Lanka', 45.00, 'Explore the rich colonial history of Negombo. Wander through the 17.7km-long Dutch Canal, visit the ruins of the 1672 Dutch Fort, and explore the gorgeous Angurukaramulla Temple with its giant Buddha statue.', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?q=80&w=800', 'Culture & Heritage'),
(2, 'Muthurajawela Wetland Boat Safari & Kayaking', 'Negombo Wetland, Sri Lanka', 35.00, 'Discover Sri Lanka''s premier coastal peat bog. Tour the Hamilton Canal and enter a biodiversity hotspot teeming with water birds, monitor lizards, butterflies, and crocodiles. Includes a guided flat-bottom boat cruise and safety kayak gear.', 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?q=80&w=800', 'Nature & Adventure'),
(3, 'Negombo Lagoon Ayurvedic & Spa Treatment', 'Negombo Beach Road, Sri Lanka', 75.00, 'Rejuvenate your body and mind with authentic Sri Lankan Ayurvedic healing. Enjoy an outdoor herbal bath overlooking the peaceful Negombo Lagoon, followed by a full-body hot oil massage and steam therapy conducted by certified wellness practitioners.', 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?q=80&w=800', 'Wellness & Spa'),
(4, 'Wilpattu National Park Jeep Safari Excursion', 'Wilpattu (Departure Negombo), Sri Lanka', 120.00, 'Embark on a thrilling safari into Sri Lanka''s largest national park. Famed for its unique natural lakes (willus), you will search for the majestic Sri Lankan Leopard, sloth bears, Asian elephants, and spotted deer. Includes hotel transfers from Negombo.', 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?q=80&w=800', 'Wildlife Safari'),
(5, 'Negombo Beach Water Sports & Catamaran Excursion', 'Negombo Beach, Sri Lanka', 85.00, 'Experience Negombo''s coastline from a traditional wooden catamaran. Join local fishermen for an exciting offshore ride, followed by an afternoon of active water sports, including jet-skiing and windsurfing. Concludes with a seafood barbecue buffet on the sand.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=800', 'Shore Excursions')
ON DUPLICATE KEY UPDATE id=id;

-- Seed Default Booking
INSERT INTO bookings (id, user_id, package_id, travel_date, travelers, total_price, status) VALUES
(1001, 1, 1, '2026-07-15', 2, 90.00, 'Approved')
ON DUPLICATE KEY UPDATE id=id;

-- Seed Default Inquiry
INSERT INTO inquiries (id, name, email, subject, message, status) VALUES
(1, 'Alice Smith', 'alice@example.com', 'Group Tour Inquiry', 'Hello GlobeTrek, we are planning a family excursion to Muthurajawela in August for 10 people. Do you offer special discounts for group packages?', 'Pending')
ON DUPLICATE KEY UPDATE id=id;

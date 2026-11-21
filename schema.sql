-- StayNest PG Finder - database schema + sample data
CREATE DATABASE IF NOT EXISTS pg_finder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pg_finder;

DROP TABLE IF EXISTS interested_users;
DROP TABLE IF EXISTS property_amenities;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,          -- bcrypt hash from password_hash()
  phone VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE properties (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  city VARCHAR(80) NOT NULL,
  area VARCHAR(120) NOT NULL,
  price INT UNSIGNED NOT NULL,             -- monthly rent in INR
  gender ENUM('male','female','unisex') NOT NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  description TEXT NOT NULL,
  image VARCHAR(255) NOT NULL,             -- cover image
  INDEX idx_city (city),
  INDEX idx_price (price),
  INDEX idx_gender (gender)
) ENGINE=InnoDB;

CREATE TABLE property_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  property_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE amenities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE property_amenities (
  property_id INT UNSIGNED NOT NULL,
  amenity_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (property_id, amenity_id),
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
  FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE interested_users (
  user_id INT UNSIGNED NOT NULL,
  property_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, property_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Sample data ----------
INSERT INTO amenities (name) VALUES
('WiFi'),('Meals included'),('Air conditioning'),('Laundry'),
('Power backup'),('CCTV security'),('Study room'),('Housekeeping');

INSERT INTO properties (name, city, area, price, gender, rating, description, image) VALUES
('Sunrise Boys PG','Delhi','Mukherjee Nagar',7500,'male',4.3,'Walking distance from coaching hubs and the Vidyarthi Nagar market. Double and triple sharing rooms with a quiet study hall open until midnight.','https://picsum.photos/seed/pg1/800/600'),
('Lotus Girls Residency','Delhi','Laxmi Nagar',9000,'female',4.6,'Gated residence with a female warden, biometric entry and home-style meals. Ten minutes from the metro.','https://picsum.photos/seed/pg2/800/600'),
('Campus Edge Stay','Delhi','Kamla Nagar',11000,'unisex',4.1,'Separate floors for men and women, shared rooftop lounge, and fast fibre internet. Close to Delhi University North Campus.','https://picsum.photos/seed/pg3/800/600'),
('Green Valley PG','Pune','Kothrud',8000,'male',4.2,'Airy rooms near MIT and Cummins College. Includes a tiffin service and a covered parking area for bikes.','https://picsum.photos/seed/pg4/800/600'),
('Orchid Women''s Hostel','Pune','Viman Nagar',10500,'female',4.7,'Premium single and double rooms with attached bathrooms, daily housekeeping and 24x7 security.','https://picsum.photos/seed/pg5/800/600'),
('Techie Nest','Pune','Hinjewadi',12000,'unisex',4.0,'Built for interns and early-career professionals, with shuttle access to the IT park and a co-working corner.','https://picsum.photos/seed/pg6/800/600'),
('Scholar''s Den','Bengaluru','Koramangala',13500,'male',4.4,'Modern rooms with ergonomic study desks, filtered water and a games lounge. Cafes and bookstores nearby.','https://picsum.photos/seed/pg7/800/600'),
('Jasmine Court','Bengaluru','HSR Layout',14000,'female',4.8,'Bright rooms, two meals a day and a landscaped courtyard. Strict visitor policy and CCTV across common areas.','https://picsum.photos/seed/pg8/800/600'),
('Metro Link Hostel','Bengaluru','Indiranagar',9500,'unisex',3.9,'Budget-friendly shared rooms two minutes from the Indiranagar metro station.','https://picsum.photos/seed/pg9/800/600'),
('Marine View PG','Mumbai','Andheri West',15000,'male',4.2,'Compact rooms with sea breeze from the terrace. Close to the suburban railway and the Versova metro.','https://picsum.photos/seed/pg10/800/600'),
('Blossom Ladies PG','Mumbai','Powai',16500,'female',4.5,'Lake-facing rooms near IIT Bombay with laundry service and a quiet reading nook.','https://picsum.photos/seed/pg11/800/600'),
('Harbour Homes','Mumbai','Dadar',13000,'unisex',4.0,'Centrally located with easy access to both railway lines. Simple, clean rooms and a shared kitchen.','https://picsum.photos/seed/pg12/800/600');

INSERT INTO property_images (property_id, image)
SELECT p.id, CONCAT('https://picsum.photos/seed/pg', p.id, 'g', n.n, '/800/600')
FROM properties p CROSS JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3) n;

INSERT INTO property_amenities (property_id, amenity_id)
SELECT p.id, a.id FROM properties p JOIN amenities a ON a.name IN ('WiFi','Power backup','CCTV security');
INSERT INTO property_amenities (property_id, amenity_id)
SELECT p.id, a.id FROM properties p JOIN amenities a ON a.name IN ('Meals included','Housekeeping') WHERE p.id % 2 = 0;
INSERT INTO property_amenities (property_id, amenity_id)
SELECT p.id, a.id FROM properties p JOIN amenities a ON a.name IN ('Air conditioning','Study room') WHERE p.id % 3 <> 0;
INSERT INTO property_amenities (property_id, amenity_id)
SELECT p.id, a.id FROM properties p JOIN amenities a ON a.name = 'Laundry' WHERE p.id % 2 = 1;

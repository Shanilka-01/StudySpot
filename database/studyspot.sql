-- ============================================================
-- StudySpot - Database schema + sample data
-- phpMyAdmin -> Import -> select this file
-- ============================================================

CREATE DATABASE IF NOT EXISTS studyspot
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studyspot;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS recently_viewed;
DROP TABLE IF EXISTS place_images;
DROP TABLE IF EXISTS places;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  avatar        VARCHAR(255) DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
CREATE TABLE places (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(150) NOT NULL,
  type          ENUM('library','cafe','coworking','university') NOT NULL,
  city          VARCHAR(100) NOT NULL,
  address       VARCHAR(255) NOT NULL,
  latitude      DECIMAL(10,7) DEFAULT NULL,
  longitude     DECIMAL(10,7) DEFAULT NULL,
  distance_km   DECIMAL(4,1) DEFAULT 0.0,
  wifi          ENUM('none','free','paid') DEFAULT 'free',
  wifi_note     VARCHAR(100) DEFAULT 'High Speed',
  noise_level   ENUM('very_quiet','quiet','moderate','lively') DEFAULT 'quiet',
  noise_note    VARCHAR(100) DEFAULT 'Perfect for deep focus',
  cost_type     ENUM('free','1-200','201-500','500+') DEFAULT 'free',
  cost_label    VARCHAR(60) DEFAULT 'Free',
  price         DECIMAL(10,2) DEFAULT 0.00,
  open_time     TIME DEFAULT '08:00:00',
  close_time    TIME DEFAULT '20:00:00',
  open_days     VARCHAR(60) DEFAULT 'Monday - Sunday',
  description   TEXT,
  cover_image   VARCHAR(255) DEFAULT NULL,
  facilities    VARCHAR(255) DEFAULT 'Power Outlets,Parking,Air Conditioning,Drinking Water,Restrooms',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE place_images (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  place_id INT NOT NULL,
  image    VARCHAR(255) NOT NULL,
  FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
CREATE TABLE reviews (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  place_id        INT NOT NULL,
  user_id         INT NOT NULL,
  rating          TINYINT NOT NULL,
  noise_level     VARCHAR(30) DEFAULT NULL,
  wifi_quality    VARCHAR(30) DEFAULT NULL,
  value_for_money VARCHAR(30) DEFAULT NULL,
  comment         TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE favorites (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  place_id   INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_fav (user_id, place_id),
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  place_id     INT NOT NULL,
  booking_date DATE NOT NULL,
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  people       INT DEFAULT 1,
  total_price  DECIMAL(10,2) DEFAULT 0.00,
  status       ENUM('pending','upcoming','completed','cancelled') DEFAULT 'pending',
  payment_ref  VARCHAR(50) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE recently_viewed (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  place_id   INT NOT NULL,
  viewed_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_view (user_id, place_id),
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Sample data
-- ============================================================
-- Password for every demo user is:  123456
INSERT INTO users (full_name, email, password_hash) VALUES
('Sahan Perera',   'sahan@example.com',   '$2y$10$zIl.N41sf10AUJEQbBtbMOC5YDxup8sA7.rAHZVHoiJZN7q6pT2KK'),
('Tharushi D.',    'tharushi@example.com','$2y$10$zIl.N41sf10AUJEQbBtbMOC5YDxup8sA7.rAHZVHoiJZN7q6pT2KK'),
('Nimal Fernando', 'nimal@example.com',   '$2y$10$zIl.N41sf10AUJEQbBtbMOC5YDxup8sA7.rAHZVHoiJZN7q6pT2KK');

INSERT INTO places
(name, type, city, address, latitude, longitude, distance_km, wifi, wifi_note, noise_level, noise_note,
 cost_type, cost_label, price, open_time, close_time, open_days, description, cover_image) VALUES
('National Library Colombo','library','Colombo','Colombo 07, Sri Lanka',6.9061,79.8612,0.8,'free','High Speed','very_quiet','Perfect for deep focus',
 'free','Free',0,'08:00:00','20:00:00','Monday - Sunday',
 'The National Library Colombo is a peaceful and spacious environment ideal for focused study and research. It offers a wide collection of books, comfortable seating and free Wi-Fi for students.','national-library.jpg'),

('Cafe Kumbuk','cafe','Colombo','Colombo 06, Sri Lanka',6.8790,79.8610,1.2,'free','Free Wi-Fi','quiet','Soft background music',
 '201-500','LKR 200 - 500',350,'07:00:00','22:00:00','Monday - Sunday',
 'A cosy garden cafe with plenty of natural light, long tables and power outlets at almost every seat. Good for short study sessions and group work.','cafe-kumbuk.jpg'),

('Hub Lanka Co-working Space','coworking','Colombo','Colombo 03, Sri Lanka',6.9210,79.8480,1.5,'free','High Speed Wi-Fi','quiet','Quiet working floor',
 '201-500','LKR 300 / day',300,'06:30:00','19:00:00','Monday - Saturday',
 'A professional co-working space with dedicated desks, meeting rooms, unlimited coffee and very fast internet. Day passes are available for students.','hub-lanka.jpg'),

('University of Colombo Library','university','Colombo','Colombo 03, Sri Lanka',6.9020,79.8600,2.1,'free','Free Wi-Fi','very_quiet','Silent reading hall',
 'free','Free',0,'07:00:00','22:30:00','Monday - Sunday',
 'The main university library with silent reading halls, reference sections and group discussion rooms. Open to visiting students with a valid ID.','uoc-library.jpg'),

('The Library Cafe','cafe','Kandy','Peradeniya Road, Kandy',7.2906,80.6337,0.8,'free','Free Wi-Fi','quiet','Calm and comfortable',
 '201-500','LKR 500',500,'08:00:00','21:00:00','Monday - Sunday',
 'Book-lined walls, wooden tables and filter coffee. One of the calmest study cafes in Kandy, popular with university students.','library-cafe.jpg'),

('Mind Space','coworking','Peradeniya','Peradeniya, Kandy',7.2560,80.5970,1.4,'paid','Paid Wi-Fi','very_quiet','Dedicated silent zone',
 '500+','LKR 600 / day',600,'08:00:00','20:00:00','Monday - Saturday',
 'A small co-working studio built for students, with silent pods, whiteboards and a study lounge.','mind-space.jpg'),

('Book Haven','library','Kandy','Dalada Veediya, Kandy',7.2930,80.6350,2.0,'free','Free Wi-Fi','quiet','Quiet most of the day',
 '1-200','LKR 150',150,'09:00:00','19:00:00','Monday - Sunday',
 'A community library and reading room with a large fiction and reference collection, plus a quiet upstairs study area.','book-haven.jpg'),

('Green Space','university','Kandy','University of Peradeniya, Kandy',7.2540,80.5950,3.2,'free','Free Wi-Fi','moderate','Open air, light chatter',
 'free','Free',0,'07:00:00','18:00:00','Monday - Sunday',
 'Open air study lawns and shaded seating inside the Peradeniya campus. Best in the morning before it gets busy.','green-space.jpg');

-- extra photos shown as thumbnails on the place page
INSERT INTO place_images (place_id, image) VALUES
(1,'national-library-2.jpg'),(1,'national-library-3.jpg'),(1,'national-library-4.jpg'),
(1,'national-library-5.jpg'),(1,'national-library-6.jpg'),
(2,'cafe-kumbuk-2.jpg'),(2,'cafe-kumbuk-3.jpg'),
(3,'hub-lanka-2.jpg'),(3,'hub-lanka-3.jpg'),
(4,'uoc-library-2.jpg'),(4,'national-library-2.jpg'),
(5,'library-cafe-2.jpg'),(5,'cafe-kumbuk-3.jpg'),
(6,'book-haven-2.jpg'),
(7,'book-haven-2.jpg'),(7,'national-library-4.jpg'),
(8,'green-space-2.jpg');

INSERT INTO reviews (place_id, user_id, rating, noise_level, wifi_quality, value_for_money, comment, created_at) VALUES
(1,2,5,'Very Quiet','Excellent','Excellent','Very quiet and comfortable. Perfect place for long study sessions!', NOW() - INTERVAL 5 DAY),
(1,1,5,'Very Quiet','Good','Excellent','Huge reading hall and plenty of power outlets.', NOW() - INTERVAL 12 DAY),
(1,3,4,'Quiet','Good','Excellent','Gets a little busy after 4pm but still great.', NOW() - INTERVAL 20 DAY),
(2,1,4,'Quiet','Good','Good','Nice coffee, good wifi. Bit pricey for a full day.', NOW() - INTERVAL 3 DAY),
(3,1,5,'Quiet','Excellent','Good','Fastest internet I have used in Colombo.', NOW() - INTERVAL 8 DAY),
(4,2,5,'Very Quiet','Good','Excellent','Free and very silent. My favourite exam week spot.', NOW() - INTERVAL 2 DAY),
(5,1,5,'Quiet','Good','Good','Lovely atmosphere in Kandy, friendly staff.', NOW() - INTERVAL 6 DAY);

INSERT INTO favorites (user_id, place_id) VALUES (1,1),(1,2),(1,3),(1,4);

INSERT INTO bookings (user_id, place_id, booking_date, start_time, end_time, people, total_price, status, payment_ref) VALUES
(1,5,'2026-09-12','10:00:00','12:00:00',1,500.00,'upcoming','SS-100231'),
(1,8,'2026-09-15','14:00:00','16:00:00',2,0.00,'upcoming','SS-100232'),
(1,2,'2026-08-20','09:00:00','11:00:00',1,350.00,'completed','SS-100198'),
(1,3,'2026-08-11','08:00:00','17:00:00',1,300.00,'completed','SS-100177'),
(1,1,'2026-07-30','13:00:00','16:00:00',3,0.00,'completed','SS-100120'),
(1,6,'2026-08-02','10:00:00','12:00:00',1,600.00,'cancelled','SS-100150');

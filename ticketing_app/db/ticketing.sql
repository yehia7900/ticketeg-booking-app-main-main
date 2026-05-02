
-- 1. Create & select the database
CREATE DATABASE IF NOT EXISTS ticketdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ticketdb;


CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name       VARCHAR(120)     NOT NULL,
    email      VARCHAR(180)     NOT NULL,
    password   VARCHAR(255)     NOT NULL,
    role       ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS events (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    title           VARCHAR(200)     NOT NULL,
    description     TEXT,
    category        VARCHAR(50)      NOT NULL DEFAULT 'Other',
    date            DATETIME         NOT NULL,
    location        VARCHAR(300)     NOT NULL,
    price           DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    total_seats     INT UNSIGNED     NOT NULL DEFAULT 100,
    available_seats INT UNSIGNED     NOT NULL DEFAULT 100,
    image_url       VARCHAR(500)              DEFAULT '',
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  TABLE: tickets
--  Foreign keys: user_id → users(id), event_id → events(id)
-- ============================================================
CREATE TABLE IF NOT EXISTS tickets (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED     NOT NULL,
    event_id    INT UNSIGNED     NOT NULL,
    quantity    INT UNSIGNED     NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    status      ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    booked_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_tickets_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_tickets_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  SEED DATA: users
--  admin@ticketeg.com  → password: admin123
--  ahmed@example.com   → password: user123
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
(
  'Admin User',
  'admin@ticketeg.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uFpf78rWi',
  'admin'
),
(
  'Ahmed Hassan',
  'ahmed@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uFpf78rWi',
  'user'
),
(
  'Sara Mahmoud',
  'sara@example.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uFpf78rWi',
  'user'
);

-- ============================================================
--  SEED DATA: events (6 Egyptian events)
-- ============================================================
INSERT INTO events
  (title, description, category, date, location, price, total_seats, available_seats, image_url)
VALUES
(
  'Amr Diab Live — Sahel Summer Concert',
  'Egypt''s most iconic pop star returns to the North Coast for an unforgettable open-air performance. Expect chart-topping hits spanning four decades, a spectacular light show, and a stunning beach backdrop. Gates open at 8 PM.',
  'Concert',
  '2026-07-15 21:00:00',
  'Hacienda Bay Amphitheatre, North Coast',
  850.00,
  3000,
  3000,
  'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80'
),
(
  'Al Ahly vs Zamalek — Cairo Derby',
  'Witness Egyptian football''s greatest rivalry live at Cairo International Stadium. The two most decorated clubs in African football history go head-to-head in the Egyptian Premier League.',
  'Sports',
  '2026-05-20 19:00:00',
  'Cairo International Stadium, Cairo',
  200.00,
  7500,
  7500,
  'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80'
),
(
  'Opera Aida — Cairo Opera House',
  'Verdi''s grand masterpiece performed at the iconic Cairo Opera House Main Hall. Presented by the Egyptian Opera Company with full orchestra, featuring award-winning soprano Rania El-Sayed in the title role.',
  'Theater',
  '2026-06-05 20:00:00',
  'Cairo Opera House — Main Hall, Cairo',
  450.00,
  1200,
  1200,
  'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=800&q=80'
),
(
  'Mashrou'' Leila — Alexandria Festival',
  'Lebanese indie rock band Mashrou'' Leila headlines the Alexandria Summer Festival alongside top Egyptian independent acts. The event takes place on the stunning Corniche waterfront.',
  'Concert',
  '2026-08-02 20:30:00',
  'Alexandria Corniche Open Stage, Alexandria',
  350.00,
  5000,
  5000,
  'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80'
),
(
  'Pyramids Marathon & Sports Fiesta',
  'Run through history! The annual Pyramids Marathon takes place alongside the iconic Giza Plateau. Distances include 5K, 10K, and full marathon. After the race, enjoy a sports fiesta with live music and food stalls.',
  'Sports',
  '2026-10-10 06:00:00',
  'Giza Plateau — Great Pyramids, Giza',
  120.00,
  2000,
  2000,
  'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=800&q=80'
);
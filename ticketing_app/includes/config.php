<?php
// ============================================================
//  TicketEG — Database Configuration
//  Host     : localhost
//  Database : ticketdb
//  User     : root
//  Password : (empty — default XAMPP setup)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'ticketdb');
define('DB_USER', 'root');
define('DB_PASS', '');

// Start the session once (safe to call from any page)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Connect to MySQL (without selecting a database yet)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die('
        <div style="font-family:sans-serif;padding:2rem;text-align:center;">
            <h2>&#9888; Database Connection Failed</h2>
            <p>Make sure <strong>XAMPP MySQL</strong> is running.</p>
            <p style="color:#999;font-size:.85rem;">Error: ' . $conn->connect_error . '</p>
        </div>
    ');
}

$conn->set_charset('utf8mb4');

// 2. Create the database if it does not exist yet
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
              CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// 3. Select the database
if (!$conn->select_db(DB_NAME)) {
    die('Could not select database: ' . DB_NAME);
}

// 4. Create tables if they do not exist

// Users table — stores account details and roles
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(120)  NOT NULL,
        email      VARCHAR(180)  NOT NULL UNIQUE,
        password   VARCHAR(255)  NOT NULL,
        role       ENUM('user','admin') NOT NULL DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");

// Events table — stores event listings
$conn->query("
    CREATE TABLE IF NOT EXISTS events (
        id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title           VARCHAR(200)  NOT NULL,
        description     TEXT,
        category        VARCHAR(50)   NOT NULL DEFAULT 'Other',
        date            DATETIME      NOT NULL,
        location        VARCHAR(300)  NOT NULL,
        price           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total_seats     INT UNSIGNED  NOT NULL DEFAULT 100,
        available_seats INT UNSIGNED  NOT NULL DEFAULT 100,
        image_url       VARCHAR(500)  DEFAULT '',
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");

// Tickets table — links users to events they have booked
$conn->query("
    CREATE TABLE IF NOT EXISTS tickets (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id     INT UNSIGNED NOT NULL,
        event_id    INT UNSIGNED NOT NULL,
        quantity    INT UNSIGNED NOT NULL DEFAULT 1,
        total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status      ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
        booked_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");

// 4b. Add photo column to users if it was not in the original schema
$col_check = $conn->query("
    SELECT COUNT(*) AS cnt
    FROM information_schema.columns
    WHERE table_schema = '" . DB_NAME . "'
      AND table_name   = 'users'
      AND column_name  = 'photo'
");
if ((int)$col_check->fetch_assoc()['cnt'] === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN photo VARCHAR(300) DEFAULT NULL");
}

// 5. Seed default accounts when the users table is empty
$user_count = (int)$conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];

if ($user_count === 0) {
    // Admin account — password: admin123
    $admin_hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $admin_name  = 'Admin User';
    $admin_email = 'admin@ticketeg.com';
    $stmt->bind_param('sss', $admin_name, $admin_email, $admin_hash);
    $stmt->execute();
    $stmt->close();

    // Regular user account — password: user123
    $user_hash  = password_hash('user123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    $user_name  = 'Ahmed Hassan';
    $user_email = 'ahmed@example.com';
    $stmt->bind_param('sss', $user_name, $user_email, $user_hash);
    $stmt->execute();
    $stmt->close();
}

// 6. Seed sample events when the events table is empty
$event_count = (int)$conn->query("SELECT COUNT(*) AS cnt FROM events")->fetch_assoc()['cnt'];

if ($event_count === 0) {
    // Each sub-array: [title, description, category, date, location, price, total_seats, available_seats, image_url]
    $sample_events = [
        [
            'Amr Diab Live — Sahel Summer Concert',
            "Egypt's most iconic pop star returns to the North Coast for an unforgettable open-air performance.",
            'Concert', '2026-07-15 21:00:00',
            'Hacienda Bay Amphitheatre, North Coast',
            850.00, 3000, 3000,
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80'
        ],
        [
            'Al Ahly vs Zamalek — Cairo Derby',
            "Witness Egyptian football's greatest rivalry live at Cairo International Stadium.",
            'Sports', '2026-05-20 19:00:00',
            'Cairo International Stadium, Cairo',
            200.00, 7500, 7500,
            'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80'
        ],
        [
            'Opera Aida — Cairo Opera House',
            "Verdi's grand masterpiece performed at the iconic Cairo Opera House with full orchestra.",
            'Theater', '2026-06-05 20:00:00',
            'Cairo Opera House — Main Hall, Cairo',
            450.00, 1200, 1200,
            'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=800&q=80'
        ],
        [
            "Mashrou' Leila — Alexandria Festival",
            'Lebanese indie rock band headlines the Alexandria Summer Festival on the stunning Corniche.',
            'Concert', '2026-08-02 20:30:00',
            'Alexandria Corniche Open Stage, Alexandria',
            350.00, 5000, 5000,
            'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80'
        ],
        [
            'Antar & Abla — National Theatre Production',
            "A breathtaking retelling of Egypt's beloved Arabic epic by the National Theatre Company.",
            'Theater', '2026-05-30 19:30:00',
            'El-Hanager Arts Centre, Cairo',
            180.00, 800, 800,
            'https://images.unsplash.com/photo-1503095396549-807759245b35?w=800&q=80'
        ],
        [
            'Pyramids Marathon & Sports Fiesta',
            'Run through history at the annual Pyramids Marathon alongside the iconic Giza Plateau.',
            'Sports', '2026-10-10 06:00:00',
            'Giza Plateau — Great Pyramids, Giza',
            120.00, 2000, 2000,
            'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=800&q=80'
        ],
    ];

    $stmt = $conn->prepare("
        INSERT INTO events
            (title, description, category, date, location, price, total_seats, available_seats, image_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($sample_events as $e) {
        $stmt->bind_param('ssssssdis', $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6], $e[7], $e[8]);
        $stmt->execute();
    }
    $stmt->close();
}

// ── Shared helper functions (available to every PHP page) ─────

// Redirect to a URL and stop the script — shorter than writing header()/exit everywhere
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Run a SELECT and return a single row as an array (or null if not found)
// Example: $user = db_row($conn, 'SELECT * FROM users WHERE id = ?', 'i', $id);
function db_row($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row; // null if nothing was found
}

// Run a SELECT and return all matching rows as an array of arrays
// Example: $events = db_rows($conn, 'SELECT * FROM events ORDER BY date ASC');
function db_rows($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows; // empty array if nothing was found
}

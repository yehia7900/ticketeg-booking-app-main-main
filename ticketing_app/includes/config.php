<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ticketdb');
define('DB_USER', 'root');
define('DB_PASS', '');

session_save_path(__DIR__ . '/../storage/sessions');
session_start();

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$conn) {
    die('Database connection failed. Please make sure MySQL is running.');
}

mysqli_set_charset($conn, 'utf8mb4');
mysqli_query($conn, 'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
mysqli_select_db($conn, DB_NAME);

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function clean($value)
{
    global $conn;
    return mysqli_real_escape_string($conn, $value);
}

function get_one($sql)
{
    $result = mysqli_query($GLOBALS['conn'], $sql);
    if (!$result) {
        die(mysqli_error($GLOBALS['conn']));
    }
    return mysqli_fetch_array($result, MYSQLI_ASSOC);
}

function get_all($sql)
{
    $rows = array();
    $result = mysqli_query($GLOBALS['conn'], $sql);
    if (!$result) {
        die(mysqli_error($GLOBALS['conn']));
    }

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $rows[] = $row;
    }

    return $rows;
}

function render($template, $data = array())
{
    extract($data, EXTR_SKIP);
    $html_template = __DIR__ . '/../views/' . $template . '.html';
    require file_exists($html_template) ? $html_template : __DIR__ . '/../views/' . $template . '.phtml';
}

function render_page($template, $data = array())
{
    extract($data, EXTR_SKIP);
    $html_template = __DIR__ . '/../views/' . $template . '.html';

    require __DIR__ . '/../views/layout/header.phtml';
    require file_exists($html_template) ? $html_template : __DIR__ . '/../views/' . $template . '.phtml';
    require __DIR__ . '/../views/layout/footer.phtml';
}

function category_icon($category)
{
    $icons = array(
        'Concert' => '&#127925;',
        'Theater' => '&#127914;',
        'Sports' => '&#9917;',
        'Other' => '&#9734;'
    );

    return isset($icons[$category]) ? $icons[$category] : '&#9734;';
}

function seed_database($conn)
{
    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(180) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user','admin') NOT NULL DEFAULT 'user',
            photo VARCHAR(300) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            category VARCHAR(50) NOT NULL DEFAULT 'Other',
            date DATETIME NOT NULL,
            location VARCHAR(300) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total_seats INT UNSIGNED NOT NULL DEFAULT 100,
            available_seats INT UNSIGNED NOT NULL DEFAULT 100,
            image_url VARCHAR(500) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS tickets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            event_id INT UNSIGNED NOT NULL,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
            booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    $column = get_one("
        SELECT COUNT(*) AS total
        FROM information_schema.columns
        WHERE table_schema = '" . DB_NAME . "' AND table_name = 'users' AND column_name = 'photo'
    ");

    if ((int)$column['total'] == 0) {
        mysqli_query($conn, 'ALTER TABLE users ADD COLUMN photo VARCHAR(300) DEFAULT NULL');
    }

    $count = get_one('SELECT COUNT(*) AS total FROM events');
    if ((int)$count['total'] > 0) {
        return;
    }

    $events = array(
        array('Amr Diab Live - Sahel Summer Concert', "Egypt's most iconic pop star returns to the North Coast for an unforgettable open-air performance.", 'Concert', '2026-07-15 21:00:00', 'Hacienda Bay Amphitheatre, North Coast', 850.00, 3000, 3000, 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80'),
        array('Al Ahly vs Zamalek - Cairo Derby', "Witness Egyptian football's greatest rivalry live at Cairo International Stadium.", 'Sports', '2026-05-20 19:00:00', 'Cairo International Stadium, Cairo', 200.00, 7500, 7500, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80'),
        array('Opera Aida - Cairo Opera House', "Verdi's grand masterpiece performed at the iconic Cairo Opera House with full orchestra.", 'Theater', '2026-06-05 20:00:00', 'Cairo Opera House - Main Hall, Cairo', 450.00, 1200, 1200, 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?w=800&q=80'),
        array("Mashrou' Leila - Alexandria Festival", 'Lebanese indie rock band headlines the Alexandria Summer Festival on the stunning Corniche.', 'Concert', '2026-08-02 20:30:00', 'Alexandria Corniche Open Stage, Alexandria', 350.00, 5000, 5000, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80'),
        array('Pyramids Marathon & Sports Fiesta', 'Run through history at the annual Pyramids Marathon alongside the iconic Giza Plateau.', 'Sports', '2026-10-10 06:00:00', 'Giza Plateau - Great Pyramids, Giza', 120.00, 2000, 2000, 'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=800&q=80')
    );

    foreach ($events as $event) {
        $sql = "INSERT INTO events
                (title, description, category, date, location, price, total_seats, available_seats, image_url)
                VALUES (
                    '" . clean($event[0]) . "',
                    '" . clean($event[1]) . "',
                    '" . clean($event[2]) . "',
                    '" . clean($event[3]) . "',
                    '" . clean($event[4]) . "',
                    " . (float)$event[5] . ",
                    " . (int)$event[6] . ",
                    " . (int)$event[7] . ",
                    '" . clean($event[8]) . "'
                )";
        mysqli_query($conn, $sql);
    }
}

seed_database($conn);

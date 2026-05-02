# 🎟️ Concert Ticket Booking System

## 📖 Overview

This project is a simple web application that allows users to:

* Create an account and log in
* View available concerts/events
* Book tickets for events
* Manage ticket reservations

The system is built using **PHP**, **MySQL**, and runs on **XAMPP**.

---

## 🗂️ Database

Database name: `ticketdb`

### Tables:

* **users** → stores user accounts
* **events** → stores concert/event details
* **tickets** → stores booking records

---

## ⚙️ Requirements

* XAMPP (Apache + MySQL)
* PHP 7 or higher
* Web browser

---

## 🚀 Installation Steps

1. **Move Project Files**

   * Extract the ZIP file
   * Place the project folder inside:

     ```
     C:\xampp\htdocs\
     ```

2. **Start Server**

   * Open XAMPP
   * Start **Apache** and **MySQL**

3. **Create Database**

   * Go to: http://localhost/phpmyadmin
   * Create a database named:

     ```
     ticketdb
     ```
   * Run the provided SQL script to create tables

4. **Configure Database Connection**

   * Open `config.php` or `db.php`
   * Set:

     ```php
     $host = "localhost";
     $user = "root";
     $pass = "";
     $db   = "ticketdb";
     ```

5. **Run the Project**

   * Open browser and go to:

     ```
     http://localhost/your-project-folder
     ```

---

## 🔐 Notes

* Passwords should be stored using hashing (e.g., `password_hash`)
* Do not use plain text passwords
* Ensure MySQL service is running before using the system

---

## 📌 Features

* User registration & login
* Event listing
* Ticket booking system
* Database-driven structure

---

## 👨‍💻 Future Improvements

* Admin dashboard
* Payment integration
* Email confirmation system
* Ticket QR codes

---

## 📃 License

This project is for educational purposes.

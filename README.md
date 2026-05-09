# 📦 Topolter

A simple, fast and modern web-based chat application built with PHP, MySQL and vanilla JavaScript.

---

## 🚀 About The Project

**Topolter** is a lightweight real-time chat system designed for private messaging between users.

It includes:

* One-to-one messaging
* File sharing support
* User conversations list
* Unread message tracking
* Browser notifications
* Responsive UI (mobile friendly)

The project focuses on simplicity, speed, and a clean chat experience without external frameworks.

---

## ⚙️ Built With

* PHP (Backend API)
* MySQL (Database)
* JavaScript (Frontend logic)
* HTML5 / CSS3 (UI)
* Native Browser APIs (Notifications, History API)

---

## ✨ Features

* 💬 Real-time-like chat (polling based)
* 📎 File & media sharing (images, videos, PDFs, etc.)
* 🔔 Desktop notifications for new messages
* 👥 Conversation-based user list
* 📱 Mobile responsive layout
* ⬇ Auto-scroll to latest messages
* 🧠 Unread message counter (badge system)
* 🔐 Session-based authentication

---

## 📁 Project Structure

```
topolter/
│
├── api/              # Backend endpoints (messages, users, auth)
├── uploads/         # Uploaded files
├── config/          # DB and config files
├── chat/            # Main UI pages
├── init.php         # Session & bootstrap
└── index.php
```

---

## 🛠 Installation

### 1. Clone the repo

```bash
git clone https://github.com/uxe2734/topolter.git
```

### 2. Import database

```sql
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,

    message TEXT NULL,

    file_path VARCHAR(255) NULL,
    file_type VARCHAR(50) NULL,

    is_read TINYINT(1) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX (sender_id),
    INDEX (receiver_id),
    INDEX (created_at)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

```
config/db.php


Set:

* DB host
* DB name
* username
* password

### 4. Run on server

Place project inside:

* XAMPP / WAMP / LAMP
* or any PHP-supported hosting

---

## 📌 Requirements

* PHP 7.4+
* MySQL 5.7+
* Browser with JavaScript enabled

---

## 🧠 How it works

* Messages are fetched using AJAX polling
* Every few seconds client requests new messages
* Notifications trigger when unread messages exist
* Files are uploaded to `/uploads` and rendered dynamically

---

## 🔮 Future Improvements

* WebSocket real-time chat (instead of polling)
* Message seen ✔✔ system
* Typing indicator
* Online/offline status
* Message encryption upgrade
* Better caching & performance optimization

---

## 👨‍💻 Author

Developed by **uxe2734**

---

## ⚠️ License

This project is open-source for learning and personal use.

---

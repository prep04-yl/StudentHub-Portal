# StudentHub Portal

A modular, full-stack student portal web application developed progressively as part of the **Web Development Frameworks (WDF)** course.

---

## 📚 About the Project

The **StudentHub Portal** is a centralized platform designed to manage and deliver student-centric services and campus resources. The application covers full lifecycle web engineering—ranging from UI/UX wireframing, semantic HTML5 structuring, and responsive modern CSS layout systems to client-side DOM scripting, PHP MySQLi database integrations, RESTful JSON APIs, file management, and Role-Based Access Control (RBAC).

---

## 🛠️ Technology Stack

- **Frontend:** Semantic HTML5, Vanilla CSS3 (Flexbox, Grid, CSS Variables, Responsive Design), Vanilla JavaScript (ES6+, DOM Manipulation, Async/Await, Fetch API)
- **Backend:** PHP 8.x (Object-Oriented & Procedural, Session Management, Prepared Statements)
- **Database:** MySQL / MariaDB (Relational schemas, foreign constraints, indexing)
- **Security:** `password_hash()` (bcrypt), `password_verify()`, session fixation defense (`session_regenerate_id()`), SQL injection prevention (parameterized MySQLi queries), input sanitization, and strict MIME/size file upload validation.
- **Server Environment:** Apache HTTP Server / XAMPP Stack

---

## 📁 Practicals Overview & Repository Structure

| Practical | Module Title | Description & Key Implementations |
| :--- | :--- | :--- |
| **Practical 1** | Requirement Analysis & Wireframing | Problem definition, feature breakdown, sitemap architecture, and UI/UX wireframes. |
| **Practical 2** | Semantic HTML5 Portal Pages | Core static pages: `home`, `about`, `events`, `faq`, `contact`, `feedback`, `registration`, and `login`. |
| **Practical 3** | Responsive CSS Layout & Styling | Centralized `style.css` using CSS custom properties, responsive cards, form styling, and layout grids. |
| **Practical 4** | Advanced CSS & Theming | CSS Grid layouts, interactive pseudo-classes, transitions, animations, and Light/Dark theme switching. |
| **Practical 5** | Responsive Framework Principles | Mobile-first media queries, flexible images, and interactive mobile hamburger menu. |
| **Practical 6** | Client-Side DOM Scripting | Dynamic DOM events, FAQs accordion toggle, testimonial carousel, and event countdown timers. |
| **Practical 7** | Form Validation & Regex | Real-time client-side input validation with regex patterns for email, mobile, password strength, and error states. |
| **Practical 8** | Backend Setup & PHP Basics | PHP runtime environment configuration, GET/POST parameter parsing, and server-side request sanitization. |
| **Practical 9** | Secure Registration & MySQLi | Duplicate email checking, bcrypt password hashing, and parameterized database insertion into `students` table. |
| **Practical 10** | Authentication & Session Management | Role-based login (`admin`/`student`), secure session lifecycle, inactivity timeout, and access-control guards. |
| **Practical 11** | Student Management CRUD | Full Create, Read, Update, and Delete operations for student records with multi-field search and department filter. |
| **Practical 12** | Event Management & Poster Upload | Admin event CRUD with multipart file upload, strict file type (`JPG`/`PNG`) and file size (≤ 2MB) validation. |
| **Practical 13** | PHP JSON API & Fetch CRUD | Headless RESTful JSON API (`api.php`) consumed asynchronously via JavaScript `fetch()` without full page reloads. |
| **Practical 14** | Admin Dashboard & RBAC | Analytics dashboard with dynamic KPI statistics, role-based conditional navigation menus, and system audit logging. |
| **Practical 15** | Comprehensive Review & Deployment | End-to-end integration, security audits, accessibility compliance, code modularization, and project packaging. |

---

## 🚀 Installation & Local Setup

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)
- Modern Web Browser (Google Chrome, Microsoft Edge, Mozilla Firefox)

### 2. Database Configuration
1. Start the **Apache** and **MySQL** modules from the XAMPP Control Panel.
2. Open **phpMyAdmin** in your browser at `http://localhost/phpmyadmin`.
3. Create a database named `studenthub_db`:
   ```sql
   CREATE DATABASE studenthub_db;
   ```
4. Import the schema script located in [Practical 11/schema.sql](file:///d:/StudentHub%20Portal%20old/Practical%2011/schema.sql) or [Practical 14/schema.sql](file:///d:/StudentHub%20Portal%20old/Practical%2014/schema.sql) to generate the required tables (`students`, `events`, `users`, `audit_logs`).

### 3. Application Setup
1. Clone or copy this repository into your web server's root directory:
   - For XAMPP: `C:/xampp/htdocs/StudentHub-Portal` (or create a directory junction/symlink).
2. Configure database credentials in `db.php` if required (default: `localhost`, username `root`, no password).

### 4. Running the Application
Access individual components in your browser:
- **Main Portal (Home):** `http://localhost/StudentHub/Practical%202/pages/home.html`
- **Registration Form:** `http://localhost/StudentHub/Practical%202/pages/registration.html`
- **Login & Authentication:** `http://localhost/StudentHub/Practical%202/pages/login.html`
- **Practical 11 (Student CRUD):** `http://localhost/StudentHub/Practical%2011/index.php`
- **Practical 12 (Event Management):** `http://localhost/StudentHub/Practical%2012/index.php`
- **Practical 13 (AJAX/Fetch CRUD):** `http://localhost/StudentHub/Practical%2013/index.php`
- **Practical 14 (Admin Dashboard):** `http://localhost/StudentHub/Practical%2014/index.php`

---

## 🔒 Security Features Implemented

- **SQL Injection Prevention:** 100% of queries handling user input use parameterized MySQLi prepared statements.
- **Password Security:** Salted and hashed passwords utilizing the industry-standard bcrypt algorithm (`PASSWORD_DEFAULT`).
- **Session Protection:** Session identifiers regenerated upon privilege escalation (`session_regenerate_id(true)`) to mitigate session fixation attacks.
- **Access Control:** Server-side route authorization ensuring unauthorized users cannot access privileged views.
- **MIME & Upload Security:** Server-side inspection of file extensions and MIME types to prevent arbitrary script execution.

---

## 👤 Author

**Prey Patel**  
Web Development Frameworks

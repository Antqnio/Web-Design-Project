# Gym Booking Web App

A simple PHP & AJAX web application that allows users to sign up for a gym, book one-hour training slots, and lets the gym owner manage members and subscriptions.

---

## Table of Contents

1. [Features](#features)  
2. [Tech Stack](#tech-stack)  
3. [Prerequisites](#prerequisites)  
4. [Installation & Setup](#installation--setup)  
5. [Configuration](#configuration)  
6. [Usage](#usage)  
7. [Project Structure](#project-structure)  
8. [Database Schema](#database-schema)  
9. [License](#license)  

---

## Features

- **User Registration & Login**  
- **Slot Booking**  
  - Pick a date & one-hour time slot  
  - Prevent double-booking via AJAX  
- **Owner/Admin Panel**  
  - View & remove members  
  - Reset user passwords  
  - Extend or expire user subscriptions  
- **Dynamic Pages** via PHP templates  
- **Responsive UI** styled with CSS and icons  

---

## Tech Stack

- **Frontend:**  
  - HTML5, CSS3  
  - JavaScript (AJAX)  
- **Backend:**  
  - PHP 7+  
  - MySQL  
- **Server:**  
  - Apache 

---

## Prerequisites

- PHP 7.2 or higher
- MySQL  
- Web server (Apache) 

---

## Installation & Setup

1. **Clone the repo**  
   ```bash
   git clone https://github.com/Antqnio/Web-Design-Project.git
   cd Web-Design-Project

---
   
## Configuration

1. **Database settings**

   - Open `php/dbutility.php` and set your database credentials as follows:
     ```php
     <?php
     define('DBHOST', 'localhost');
     define('DBNAME', 'querci_655055');
     define('DBUSER', 'your_workbench_username');
     define('DBPASS', 'your_workbench_password');
     ?>
     ```

---

## 📄 License

This project is licensed under the MIT License.



# SchoolSync - School Management System

A comprehensive web-based school management system built with PHP, MySQL, HTML, CSS, and JavaScript. SchoolSync provides administrators and teachers with tools to manage students, grades, subjects, enrollments, and academic records efficiently.

## 🎯 Project Overview

SchoolSync is a modern, user-friendly school management system designed to streamline administrative tasks in educational institutions. The system features a clean, responsive interface and robust backend functionality for managing all aspects of school operations.

## ✨ Features

### 🔐 Authentication & Security
- Secure login system with session management
- Role-based access control (Admin/Teacher)
- Default admin credentials for easy setup

### 👥 Student Management
- Add, edit, and delete student records
- Comprehensive student profiles with detailed information
- Student ID tracking and management
- Gender, contact, and academic details

### 👨‍🏫 Teacher Management
- Teacher profile management
- Assignment and role management
- Performance tracking

### 📚 Subject & Grade Management
- Subject creation and management
- Grade/Class management (Grade 1-13)
- Class divisions (A, B, C for grades 1-11)
- Specialized streams for grades 12-13 (Art, Science, Commerce)
- Subject enrollment system
- **Automated grade advancement** - Move students to next grade level annually

### 📊 Academic Records
- Grade assignment and tracking
- Test marks management
- Report card generation
- Academic progress monitoring
- Multi-term assessment tracking

### 📈 Analytics & Reporting
- Class statistics and performance metrics
- Subject-wise performance analysis
- Student progress reports
- Comprehensive data visualization

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8.2+
- **Database**: MySQL 10.4+ (MariaDB)
- **Server**: Apache (XAMPP)
- **UI Framework**: Custom CSS with Font Awesome icons
- **Architecture**: MVC-like structure with PHP backend

## 📋 Prerequisites

Before running this project, ensure you have:

- **XAMPP** (or similar local server stack)
- **PHP 8.2** or higher
- **MySQL 10.4** or higher
- **Apache Web Server**
- **Modern web browser** (Chrome, Firefox, Safari, Edge)

## 🚀 Installation

### Step 1: Clone/Download the Project
```bash
# Clone the repository or download the ZIP file
# Place it in your XAMPP htdocs folder
C:\xampp\htdocs\school_management_system\
```

### Step 2: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Ensure both services are running (green status)

### Step 3: Database Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `school_db`
3. Import the database structure:
   - Go to **Import** tab
   - Select `school_db.sql` file from the project root
   - Click **Go** to import

### Step 4: Configuration
1. Open `php/config.php`
2. Verify database connection settings:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";  // Default XAMPP password
   $dbname = "school_db";
   ```
3. Update credentials if needed

### Step 5: Access the Application
- Open your web browser
- Navigate to: `http://localhost/school_management_system/`
- Use default admin credentials:
  - **Username**: `admin`
  - **Password**: `admin`

## 📁 Project Structure

```
school_management_system/
├── css/
│   └── styles.css              # Main stylesheet
├── images/                     # Project images and assets
│   ├── logo.svg               # Application logo
│   ├── loginbg.webp           # Login background
│   └── ...
├── js/
│   └── scripts.js             # JavaScript functionality
├── php/                       # Backend PHP files
│   ├── config.php             # Database configuration
│   ├── login.php              # Authentication logic
│   ├── dashboard.php          # Admin dashboard
│   ├── student_manage.php     # Student management
│   ├── admin_teachers.php     # Teacher management
│   ├── subject_manage.php     # Subject management
│   ├── assign_grade.php       # Grade assignment
│   ├── advance_grades.php     # Automated grade advancement
│   ├── test_marks.php         # Test marks management
│   ├── report_cards.php       # Report generation
│   └── ...
├── school_db.sql              # Database structure and sample data
├── index.html                 # Main login page
└── README.md                  # This file
```

## 🔧 Configuration

### Database Configuration
The system uses MySQL with the following default settings:
- **Host**: localhost
- **Database**: school_db
- **Username**: root
- **Password**: (empty - XAMPP default)

### File Permissions
Ensure the following directories have write permissions:
- `php/` (for session management)
- Any upload directories (if implemented)

## 📖 Usage Guide

### Admin Login
1. Access the application URL
2. Use default credentials: `admin` / `admin`
3. Access full administrative features

### Managing Students
1. Navigate to **Student Management**
2. Add new students with complete details
3. Edit existing student information
4. View comprehensive student profiles

### Grade Management
1. Access **Grade Management**
2. Create new grades/classes
3. Assign students to specific grades
4. Monitor class performance
5. **Advance Grades** - Automatically move students to next grade level annually

### Academic Records
1. **Test Marks**: Record and manage student test scores
2. **Report Cards**: Generate comprehensive student reports
3. **Progress Tracking**: Monitor academic development

## 🐛 Known Issues & TODO

The project has several pending improvements documented in the `TODO` file:

- Student addition error resolution
- UI improvements for student management
- Grade assignment enhancements
- Database updates and data population
- Performance optimizations

## 🔒 Security Considerations

- **Default Credentials**: Change default admin password after first login
- **Session Management**: Implement proper session timeout
- **Input Validation**: Ensure all user inputs are properly validated
- **SQL Injection**: Use prepared statements for database queries
- **File Uploads**: Implement proper file upload security if needed

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For support and questions:
- Check the `TODO` file for known issues
- Review the code comments for implementation details
- Ensure all prerequisites are met
- Verify database connectivity

## 🔄 Updates

- **Current Version**: 1.0.0
- **Last Updated**: August 2025
- **Database Version**: school_db.sql (v1.0)

## 📊 System Requirements

- **Minimum PHP**: 8.0
- **Recommended PHP**: 8.2+
- **MySQL**: 10.4+
- **Browser Support**: Modern browsers with ES6+ support
- **Memory**: 512MB RAM minimum
- **Storage**: 100MB free space

---

**SchoolSync** - Empowering Education Through Technology

*Built with ❤️ for educational institutions*

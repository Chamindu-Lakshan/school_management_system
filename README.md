# SchoolSync - School Management System
https://github.com/Chamindu-Lakshan/school_management_system/

A comprehensive web-based school management system built with PHP, MySQL, HTML, CSS, and JavaScript. SchoolSync provides administrators and teachers with tools to manage students, grades, subjects, enrollments, and academic records efficiently.

## 🎯 Project Overview

SchoolSync is a modern, user-friendly school management system designed to streamline administrative tasks in educational institutions. The system features a clean, responsive interface and robust backend functionality for managing all aspects of school operations. The system now includes smart defaults for common operations, making it more efficient for daily use.

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
- **Smart Defaults** - Grade 6 A and English subject pre-selected for common operations

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

## 🚀 Quick Start (5 Minutes)

### For Immediate Setup:
1. **Download XAMPP**: https://www.apachefriends.org/
2. **Install XAMPP** with Apache, MySQL, and phpMyAdmin
3. **Start XAMPP** services (Apache + MySQL)
4. **Place project** in `C:\xampp\htdocs\school_management_system\`
5. **Create database** `school_db` in phpMyAdmin
6. **Import** `school_db.sql` file
7. **Access** `http://localhost/school_management_system/`
8. **Login** with `admin` / `admin`

## 🚀 Detailed Installation & Setup with XAMPP

### Prerequisites
- **XAMPP** (Download from https://www.apachefriends.org/)
- **Windows 10/11** (or macOS/Linux with XAMPP)
- **Modern web browser** (Chrome, Firefox, Safari, Edge)

### Step 1: Download and Install XAMPP
1. Download XAMPP from the official website
2. Run the installer as Administrator
3. Select Apache, MySQL, and phpMyAdmin during installation
4. Install to default location: `C:\xampp\`

### Step 2: Download the Project
1. Download or clone this repository
2. Extract the project to: `C:\xampp\htdocs\school_management_system\`
3. Ensure the folder structure looks like:
   ```
   C:\xampp\htdocs\school_management_system\
   ├── css\
   ├── images\
   ├── js\
   ├── php\
   ├── school_db.sql
   └── index.html
   ```

### Step 3: Start XAMPP Services
1. Open **XAMPP Control Panel** (as Administrator)
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both services should show **green** status
5. If ports are busy, change ports in XAMPP settings

### Step 4: Database Setup
1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click **New** in the left sidebar
4. Create database named: `school_db`
5. Select **utf8mb4_general_ci** as collation
6. Click **Create**

### Step 5: Import Database Structure
1. In phpMyAdmin, select the `school_db` database
2. Click the **Import** tab
3. Click **Choose File** and select `school_db.sql` from your project folder
4. Click **Go** to import
5. You should see "Import has been successfully finished"

### Step 6: Verify Configuration
1. Open `C:\xampp\htdocs\school_management_system\php\config.php`
2. Verify these settings (should work with default XAMPP):
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";  // Empty for XAMPP default
   $dbname = "school_db";
   ```

### Step 7: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/school_management_system/`
3. You should see the login page
4. Use these default credentials:
   - **Username**: `admin`
   - **Password**: `admin`

### Step 8: First Login & Setup
1. Login with admin credentials
2. Change the default password immediately
3. Explore the dashboard and features
4. The system will automatically show Grade 6 A and English as defaults

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
1. Access the application URL: `http://localhost/school_management_system/`
2. Use default credentials: `admin` / `admin`
3. Access full administrative features

### Managing Students
1. Navigate to **Student Management**
2. Students are automatically sorted by ID with ID as the first column
3. Add new students with complete details
4. Edit existing student information
5. View comprehensive student profiles

### Grade Management
1. Access **Grade Management**
2. Create new grades/classes
3. Assign students to specific grades
4. Monitor class performance

### Academic Records
1. **Test Marks**: 
   - Automatically loads Grade 6 A English marks by default
   - Record and manage student test scores
   - View existing marks and update them
2. **Report Cards**: 
   - Automatically shows Grade 6 students by default
   - Generate comprehensive student reports
3. **Class Statistics**: 
   - Automatically displays Grade 6 A statistics
   - View performance metrics and rankings
4. **Subject Statistics**: 
   - Automatically shows English subject statistics
   - Analyze subject performance across grades
5. **Subject Enrollment**: 
   - Automatically loads Grade 6 students
   - Enroll students in subjects easily

### Smart Defaults Feature
The system now includes smart defaults for common operations:
- **Test Marks**: Opens with Grade 6 A English marks
- **Report Cards**: Shows Grade 6 students by default
- **Class Statistics**: Displays Grade 6 A statistics
- **Subject Statistics**: Shows English subject data
- **Subject Enrollment**: Loads Grade 6 students

This makes daily operations more efficient by reducing the need to manually select common options.

## 🐛 Troubleshooting

### Common Issues and Solutions

#### 1. XAMPP Services Won't Start
- **Problem**: Apache or MySQL won't start
- **Solution**: 
  - Run XAMPP as Administrator
  - Check if ports 80 (Apache) and 3306 (MySQL) are free
  - Change ports in XAMPP settings if needed

#### 2. Database Connection Error
- **Problem**: "Connection failed" error
- **Solution**:
  - Ensure MySQL is running in XAMPP
  - Check `php/config.php` settings
  - Verify database `school_db` exists

#### 3. Session Start Error
- **Problem**: "session_start(): Ignoring session_start() because a session is already active"
- **Solution**: This has been fixed in the current version with proper session management

#### 4. Unknown Column Error
- **Problem**: "Unknown column 's.status' in 'where clause'"
- **Solution**: This has been resolved in the current version

#### 5. Page Not Found (404 Error)
- **Problem**: Pages not loading
- **Solution**:
  - Ensure project is in `C:\xampp\htdocs\school_management_system\`
  - Check Apache is running
  - Verify file permissions

### Performance Tips
- Use a modern browser for best performance
- Clear browser cache if experiencing issues
- Ensure sufficient RAM (512MB minimum)

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

- **Current Version**: 1.1.0
- **Last Updated**: September 2025
- **Database Version**: school_db.sql (v1.1)

### Recent Updates (v1.1.0)
- ✅ Added smart defaults for common operations
- ✅ Test Marks page now opens with Grade 6 A English marks
- ✅ Report Cards page shows Grade 6 students by default
- ✅ Class Statistics displays Grade 6 A statistics automatically
- ✅ Subject Statistics shows English subject data by default
- ✅ Subject Enrollment loads Grade 6 students automatically
- ✅ Students are sorted by ID with ID as the first column
- ✅ Removed password minimum length requirements
- ✅ Fixed session management issues
- ✅ Improved error handling and debugging
- ✅ Enhanced user experience with auto-loading data
- ✅ Removed advance_grades.php feature

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

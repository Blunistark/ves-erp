# School ERP System

A comprehensive School Enterprise Resource Planning (ERP) system built with PHP and MySQL, featuring three distinct portals for different user roles.

## 🏫 Project Overview

This School ERP system provides a complete management solution for educational institutions with role-based access control and modern web interfaces.

### ✨ Key Features

- **Multi-Portal Architecture**: Separate interfaces for Admin, Teachers, and Students
- **Role-Based Access Control**: Secure authentication and authorization
- **Academic Management**: Classes, sections, subjects, and timetables
- **Student Management**: Admission, records, attendance, and performance tracking
- **Teacher Management**: Assignment, resource sharing, and homework management
- **Financial Management**: Fee structure, collection, and reporting
- **Communication System**: Announcements and notifications
- **Resource Management**: Digital library and file sharing

## 🏗️ Architecture

### Portal Structure
```
├── admin/          # Administrative portal
├── teachers/       # Teacher portal
├── student/        # Student portal
└── ves-reception/  # Visitor & Admission management
```

### Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (WAMP/XAMPP)
- **Architecture**: MVC-style with modular components

## 🚀 Getting Started

### Prerequisites
- WAMP/XAMPP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd erp
   ```

2. **Database Setup**
   - Import the database schema from `db/setup.sql`
   - Update database credentials in `includes/config.php`

3. **Configuration**
   - Copy `includes/config.php.example` to `includes/config.php`
   - Update database connection settings
   - Set up file upload directories

4. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 admin/uploads/
   ```

5. **Access the System**
   - Navigate to `http://localhost/erp`
   - Run setup scripts for initial configuration

## 📁 Project Structure

```
erp/
├── admin/                  # Admin Portal
│   ├── dashboard/         # Admin dashboard modules
│   ├── uploads/           # Admin file uploads
│   └── index.php         # Admin login
├── teachers/              # Teacher Portal
│   ├── dashboard/         # Teacher dashboard modules
│   └── index.php         # Teacher login
├── student/               # Student Portal
│   ├── dashboard/         # Student dashboard modules
│   └── index.php         # Student login
├── ves-reception/         # Reception Module
├── includes/              # Shared utilities
│   ├── config.php        # Database configuration
│   └── functions.php     # Utility functions
├── assets/               # Static assets
├── backend/              # API endpoints
├── uploads/              # General uploads
└── db/                   # Database files
```

## 🔐 Default Access

### Admin Portal
- URL: `/admin/`
- Default credentials configured during setup

### Teacher Portal
- URL: `/teachers/`
- Credentials provided by admin

### Student Portal
- URL: `/student/`
- Credentials provided by admin

## 🌟 Features by Portal

### Admin Portal
- ✅ User Management (CRUD operations)
- ✅ Academic Structure (Classes, Sections, Subjects)
- ✅ Teacher Assignment & Management
- ✅ Student Management & Records
- ✅ Fee Management & Reporting
- ✅ Online Class Management
- ✅ Resource Management
- ✅ Attendance Tracking
- ✅ Report Generation

### Teacher Portal
- ✅ Marks & Assessment Management
- ✅ Homework Assignment & Tracking
- ✅ Attendance Management
- ✅ Resource Sharing
- ✅ Student Performance Analytics
- ✅ Announcements & Communication
- ✅ Class Schedule Management

### Student Portal
- ✅ Academic Results & Progress
- ✅ Attendance Viewing
- ✅ Homework Submissions
- ✅ Online Class Participation
- ✅ Fee Status & Payments
- ✅ Announcements & Notifications
- ✅ Resource Access

## 🔄 Git Workflow

This project follows a structured branching strategy:

### Branch Structure
- **`main`**: Production-ready code
- **`dev`**: Development branch for new features
- **`feature/*`**: Feature development branches
- **`hotfix/*`**: Emergency fixes for production

### Workflow
1. Create feature branches from `dev`
2. Develop and test features
3. Merge feature branches to `dev`
4. Test thoroughly in `dev`
5. Merge `dev` to `main` for releases

### Commands
```bash
# Switch to development branch
git checkout dev

# Create a new feature branch
git checkout -b feature/new-feature

# Merge feature to dev
git checkout dev
git merge feature/new-feature

# Merge dev to main (for releases)
git checkout main
git merge dev
```

## 🛠️ Development

### Coding Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Comment complex logic
- Implement proper error handling

### Security Practices
- Input validation and sanitization
- SQL injection prevention (prepared statements)
- CSRF protection
- Session management
- File upload validation

### Testing
- Test all features before committing
- Cross-browser compatibility
- Mobile responsiveness
- Database integrity

## 📝 Database Schema

The system uses a comprehensive database schema with the following key tables:
- `users` - User authentication and roles
- `classes` - Academic class structure
- `sections` - Class sections
- `subjects` - Subject management
- `students` - Student records
- `teachers` - Teacher information
- `attendance` - Attendance tracking
- `marks` - Grade management
- `fees` - Financial records

## 🔧 Configuration

### Environment Variables
Create a `.env` file (not committed to git):
```env
DB_HOST=localhost
DB_NAME=school_erp
DB_USER=root
DB_PASS=password
```

### File Uploads
- Configure upload limits in `php.ini`
- Set appropriate file permissions
- Validate file types and sizes

## 🚧 Roadmap

### Upcoming Features
- [ ] Mobile App Integration
- [ ] Advanced Analytics Dashboard
- [ ] Automated Report Generation
- [ ] Parent Portal
- [ ] Library Management System
- [ ] Transport Management
- [ ] Hostel Management

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Contributors and developers
- Educational institutions for requirements gathering
- Open source community for tools and libraries

## 📞 Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation wiki

---

**Version**: 1.0.0  
**Last Updated**: June 2025  
**Status**: Production Ready

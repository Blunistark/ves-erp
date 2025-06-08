# Contributing to School ERP System

Thank you for your interest in contributing to the School ERP System! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### 1. Fork and Clone
```bash
# Fork the repository on GitHub
# Clone your fork
git clone https://github.com/yourusername/school-erp.git
cd school-erp
```

### 2. Set Up Development Environment
```bash
# Install WAMP/XAMPP/LAMP
# Configure database
# Set up the project as described in README.md
```

### 3. Create a Feature Branch
```bash
# Switch to dev branch
git checkout dev

# Create a new feature branch
git checkout -b feature/your-feature-name
```

## 🌿 Branch Strategy

### Main Branches
- **`main`**: Production-ready code, always stable
- **`dev`**: Development branch, integration of new features

### Supporting Branches
- **`feature/*`**: New features and enhancements
- **`hotfix/*`**: Critical fixes for production
- **`bugfix/*`**: Bug fixes for development

### Branch Naming Convention
```
feature/add-parent-portal
feature/improve-authentication
bugfix/fix-attendance-calculation
hotfix/security-patch
```

## 📋 Development Workflow

### 1. Feature Development
```bash
# Start from dev branch
git checkout dev
git pull origin dev

# Create feature branch
git checkout -b feature/your-feature

# Develop your feature
# Make commits with clear messages
git add .
git commit -m "Add: new feature description"

# Push to your fork
git push origin feature/your-feature

# Create Pull Request to dev branch
```

### 2. Code Standards

#### PHP Standards
- Follow PSR-12 coding standards
- Use meaningful variable names
- Add docblocks for functions and classes
- Implement proper error handling

```php
<?php
/**
 * Calculate student attendance percentage
 * 
 * @param int $student_id Student ID
 * @param string $start_date Start date (Y-m-d)
 * @param string $end_date End date (Y-m-d)
 * @return float Attendance percentage
 */
function calculateAttendancePercentage($student_id, $start_date, $end_date) {
    // Implementation
}
```

#### HTML/CSS Standards
- Use semantic HTML5 elements
- Follow BEM methodology for CSS classes
- Ensure responsive design
- Maintain accessibility standards

```html
<!-- Good -->
<div class="student-card">
    <h3 class="student-card__name">John Doe</h3>
    <p class="student-card__info">Class: 10A</p>
</div>

<!-- CSS -->
.student-card {
    /* Block */
}

.student-card__name {
    /* Element */
}

.student-card--featured {
    /* Modifier */
}
```

#### JavaScript Standards
- Use ES6+ features where appropriate
- Add comments for complex logic
- Handle errors gracefully

```javascript
// Good
const fetchStudentData = async (studentId) => {
    try {
        const response = await fetch(`/api/students/${studentId}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching student data:', error);
        throw error;
    }
};
```

### 3. Database Changes

#### Migration Files
Create migration files for database changes:
```sql
-- migrations/add_parent_portal_table.sql
CREATE TABLE parent_portal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_email VARCHAR(255) NOT NULL,
    access_code VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);
```

#### Naming Conventions
- Tables: `snake_case` (e.g., `student_marks`, `fee_payments`)
- Columns: `snake_case` (e.g., `first_name`, `created_at`)
- Indexes: `idx_table_column` (e.g., `idx_students_class_id`)

### 4. Security Guidelines

#### Input Validation
```php
// Always validate and sanitize input
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
if (!$student_id) {
    throw new InvalidArgumentException('Invalid student ID');
}
```

#### SQL Injection Prevention
```php
// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? AND class_id = ?");
$stmt->execute([$student_id, $class_id]);
```

#### File Upload Security
```php
// Validate file types and sizes
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
if (!in_array($_FILES['file']['type'], $allowed_types)) {
    throw new Exception('Invalid file type');
}
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] Feature works as expected
- [ ] No PHP errors or warnings
- [ ] Responsive design on mobile/tablet
- [ ] Cross-browser compatibility
- [ ] Security vulnerabilities checked
- [ ] Database integrity maintained

### Test Different User Roles
- [ ] Admin functionality
- [ ] Teacher functionality  
- [ ] Student functionality
- [ ] Unauthenticated access prevention

## 📝 Commit Guidelines

### Commit Message Format
```
Type: Brief description

Detailed description if needed

- Additional point 1
- Additional point 2
```

### Commit Types
- **Add**: New features or functionality
- **Update**: Improvements to existing features
- **Fix**: Bug fixes
- **Remove**: Remove features or code
- **Refactor**: Code refactoring without feature changes
- **Style**: Code style changes (formatting, etc.)
- **Docs**: Documentation changes

### Examples
```bash
git commit -m "Add: student attendance report generation"
git commit -m "Fix: attendance calculation error for partial days"
git commit -m "Update: improve responsive design for mobile devices"
git commit -m "Refactor: optimize database queries in student module"
```

## 🔍 Code Review Process

### Pull Request Requirements
1. **Clear Description**: Explain what the PR does and why
2. **Testing**: Describe how you tested the changes
3. **Screenshots**: Include screenshots for UI changes
4. **Breaking Changes**: Document any breaking changes

### Review Checklist
- [ ] Code follows project standards
- [ ] Functionality works correctly
- [ ] Security considerations addressed
- [ ] Performance implications considered
- [ ] Documentation updated if needed

## 📚 Documentation

### Code Documentation
- Add docblocks for all functions and classes
- Update README.md for new features
- Create wiki pages for complex features

### User Documentation
- Update user manuals for new features
- Create setup guides for new components
- Document API endpoints

## 🐛 Bug Reports

### Bug Report Template
```markdown
**Bug Description**
A clear description of the bug

**Steps to Reproduce**
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

**Expected Behavior**
What you expected to happen

**Screenshots**
Add screenshots if applicable

**Environment**
- OS: [e.g. Windows 10]
- Browser: [e.g. Chrome 91]
- PHP Version: [e.g. 7.4]
```

## 💡 Feature Requests

### Feature Request Template
```markdown
**Feature Description**
A clear description of the feature

**Use Case**
Why this feature would be useful

**Proposed Solution**
How you think this could be implemented

**Alternatives**
Other ways this could be achieved
```

## 🏷️ Release Process

### Version Numbering
Follow semantic versioning (MAJOR.MINOR.PATCH):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes

### Release Checklist
- [ ] All tests pass
- [ ] Documentation updated
- [ ] Database migrations tested
- [ ] Security review completed
- [ ] Performance testing done
- [ ] Cross-browser testing completed

## 📞 Getting Help

### Resources
- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and general discussion
- **Documentation**: Check the project wiki
- **Code Examples**: Look at existing implementations

### Contact
- Create an issue for bug reports
- Use discussions for questions
- Mention maintainers for urgent issues

## 📄 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

Thank you for contributing to the School ERP System! 🎉

# Tests Directory

This directory contains test files and debugging utilities for the ERP system.

## Structure

- `admin/` - Admin module test files and debugging scripts
- `teachers/` - Teachers module test files  
- `student/` - Student module test files

## Files

### Admin Tests
- Test files for teacher assignment systems
- Class teacher assignment testing
- Teacher profile testing
- Debug utilities for class-teacher relationships

### Teachers Tests
- Content testing utilities
- Teacher dashboard functionality tests

### Student Tests
- Upload functionality testing
- Student dashboard feature tests

## Purpose

These files are used for:
- Feature testing during development
- Debugging system issues
- Validating functionality before deployment
- Development troubleshooting

## Usage

Run test files individually to check specific functionality:

```bash
php tests/admin/test-teacher-assignment-routes.php
php tests/teachers/test-content.php
php tests/student/test-upload.php
```

## Note

These files are for development and testing purposes only. They should not be accessible in production environments.

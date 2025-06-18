# SecureAuth - Secure Login System

A comprehensive PHP-based authentication system with advanced security features including two-factor authentication, secure password recovery, and admin evaluation management.

## Features

### User Authentication
- User registration and login with username, email, and phone number
- Account activation via email verification
- Two-factor authentication (2FA) with TOTP support
- Google OAuth integration for social login
- Secure password recovery with time-limited tokens (5-hour expiration)
- Security questions for additional account protection
- Account lockout after 3 failed login attempts (10-minute timeout)

### Security Implementation
- XSS protection using htmlspecialchars() and Content Security Policy (CSP)
- CSRF protection with secure token generation and 10-minute form expiration
- SQL injection prevention using prepared statements exclusively
- Rate limiting with IP-based login attempt restrictions
- Password strength enforcement with entropy requirements
- Symmetric encryption for sensitive user data (email, phone number)
- Salted and hashed passwords using secure algorithms
- Protection against botnet attacks with CAPTCHA integration

### Administrative Features
- Role-based access control with administrator privileges
- User evaluation and feedback system
- File upload functionality with security measures
- Image upload support with automatic file extension detection
- Encrypted file storage with randomized filenames
- Public/private key encryption for admin evaluation data
- Administrator role assignment capabilities
- Evaluation management (view, delete, contact users)

### Password Security
- Minimum 8 characters required
- Must contain at least one lowercase letter
- Must contain at least one uppercase letter
- Must contain at least one number
- Must contain at least one special character
- Password strength validation against common password lists
- Secure password reset functionality

## Installation

1. Clone the repository
   ```bash
   git clone https://github.com/username/Secure-Log-in-System.git
   cd Secure-Log-in-System
   ```

2. Install dependencies
   ```bash
   composer install
   ```

3. Configure the application
   - Update `includes/config.php` with your database credentials
   - Set up email configuration for account verification
   - Configure Google OAuth credentials (if using)
   - Set up CAPTCHA keys for bot protection

4. Set up the database
   - Import the provided SQL schema
   - Configure database connection parameters
   - Create necessary tables for users, evaluations, and admin data

5. Configure web server
   - Point document root to `public/` directory
   - Ensure PHP extensions are enabled (PDO, OpenSSL, GD)
   - Configure SSL certificates for secure connections

## Technical Implementation

### Encryption Methods
- User passwords are salted and hashed using secure algorithms
- Email and phone numbers are encrypted using symmetric encryption with user password as key
- Admin evaluation data uses public/private key encryption system
- File uploads are encrypted and stored with randomized names

### Session Security
- Secure session handling with proper timeouts
- CSRF tokens with 10-minute expiration on all forms
- IP-based rate limiting for failed login attempts
- Automatic session cleanup and management

### Database Security
- All database interactions use prepared statements
- No direct SQL query execution
- Parameterized queries prevent SQL injection attacks
- Encrypted storage for sensitive user information

## System Requirements

- PHP 7.4 or higher with extensions:
  - PDO (MySQL support)
  - OpenSSL for encryption
  - GD for image processing
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- Composer for dependency management
- SSL certificate for production use

## Configuration Files

- `includes/config.php` - Database and application settings
- `composer.json` - PHP dependencies and autoloading
- `public/index.html` - Main entry point
- `includes/10k-most-common.txt` - Common password list for validation

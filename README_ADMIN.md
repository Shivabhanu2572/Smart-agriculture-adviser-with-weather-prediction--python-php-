# Smart Agriculture Admin Module

## Overview
The Admin Module provides a comprehensive management interface for the Smart Agriculture Advisor system. It allows administrators to manage users, crop recommendations, irrigation tips, market prices, and system settings.

## Features

### 🔐 **Authentication & Security**
- Secure admin login with password hashing
- Session-based authentication
- Role-based access control (super_admin, admin, moderator)
- Activity logging for all admin actions
- IP address tracking

### 👥 **User Management**
- View all registered users
- Search and filter users by name, email, or location
- Edit user profiles
- Delete user accounts
- Pagination for large user lists

### 🌾 **Crop Recommendations Management**
- Add new crop recommendations for districts and months
- Edit existing recommendations
- Delete recommendations
- Search and filter by district or crop type
- Bulk management capabilities

### 💧 **Irrigation Tips Management**
- Manage crop-specific irrigation schedules
- Add/edit irrigation methods and tips
- Organize by crop types and growth stages

### 📊 **Market Prices Management**
- Update market price data
- Manage price sources and APIs
- Historical price tracking

### 📈 **Analytics & Reporting**
- User registration trends
- System usage statistics
- Activity logs and audit trails
- Performance metrics

### ⚙️ **System Settings**
- Configure API keys
- Manage system preferences
- Enable/disable features
- Maintenance mode controls

## Installation

### 1. Database Setup
Run the SQL setup file to create admin tables:

```sql
-- Execute admin_setup.sql in your MySQL database
mysql -u root -p smart_agri < admin_setup.sql
```

### 2. Default Admin Account
After running the setup, you'll have a default admin account:
- **Email**: admin@smartagriculture.com
- **Password**: admin123
- **Role**: super_admin

⚠️ **Important**: Change the default password immediately after first login!

### 3. File Structure
Ensure these files are in your project root:
```
admin_login.php          # Admin login page
admin_dashboard.php      # Main admin dashboard
admin_users.php         # User management
admin_crops.php         # Crop recommendations management
admin_logout.php        # Admin logout
admin_setup.sql         # Database setup
```

## Usage

### Accessing Admin Panel
1. Navigate to `admin_login.php`
2. Login with admin credentials
3. You'll be redirected to the admin dashboard

### Managing Users
1. Go to "Manage Users" in the sidebar
2. Use search and filters to find specific users
3. Click "Edit" to modify user details
4. Click "Delete" to remove user accounts

### Managing Crop Recommendations
1. Go to "Crop Recommendations" in the sidebar
2. Click "Add New Recommendation" to create entries
3. Use filters to find specific districts or crops
4. Edit or delete existing recommendations

### System Monitoring
- View real-time statistics on the dashboard
- Monitor user registration trends
- Check recent admin activities
- Review system performance

## Database Schema

### Admins Table
```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Admin Logs Table
```sql
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
```

### System Settings Table
```sql
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Security Features

### Password Security
- Passwords are hashed using PHP's `password_hash()` function
- Uses bcrypt algorithm with cost factor 10
- Secure password verification with `password_verify()`

### Session Security
- Session-based authentication
- Automatic session timeout
- Secure session handling
- CSRF protection through form tokens

### Access Control
- Role-based permissions
- IP address logging
- Activity audit trails
- Secure logout functionality

## Customization

### Adding New Admin Roles
1. Modify the `role` ENUM in the admins table
2. Update role checks in admin pages
3. Add role-specific permissions

### Customizing Dashboard
1. Edit `admin_dashboard.php` to add new statistics
2. Modify the sidebar navigation
3. Add new chart visualizations

### Extending Functionality
1. Create new admin pages following the existing pattern
2. Add new database tables as needed
3. Implement proper security checks

## Troubleshooting

### Common Issues

**Admin can't login:**
- Check if admin account exists in database
- Verify password hash is correct
- Ensure `is_active` is set to TRUE

**Database connection errors:**
- Verify database credentials in `db_connection.php`
- Check if admin tables exist
- Ensure proper permissions

**Session issues:**
- Check PHP session configuration
- Verify session storage permissions
- Clear browser cookies and cache

### Error Logging
All admin actions are logged in the `admin_logs` table. Check this table for:
- Login attempts
- User management actions
- System changes
- Error tracking

## Best Practices

### Security
1. **Change default password** immediately after setup
2. **Use strong passwords** for admin accounts
3. **Regular security audits** of admin activities
4. **Limit admin access** to trusted personnel only
5. **Monitor admin logs** regularly

### Maintenance
1. **Regular backups** of admin data
2. **Update system settings** as needed
3. **Monitor system performance**
4. **Clean old log entries** periodically
5. **Update admin accounts** when personnel changes

### User Management
1. **Verify user information** before approving accounts
2. **Handle user complaints** promptly
3. **Maintain user privacy** and data protection
4. **Document user management** decisions
5. **Regular user data cleanup**

## API Integration

The admin module can be extended to include API endpoints for:
- User management via REST API
- Automated data imports
- Third-party integrations
- Mobile admin applications

## Support

For technical support or feature requests:
1. Check the error logs in `admin_logs` table
2. Review database connection settings
3. Verify file permissions
4. Contact system administrator

## Version History

- **v1.0** - Initial admin module release
- Basic user and crop management
- Dashboard with statistics
- Security and logging features

---

**Note**: This admin module is designed for the Smart Agriculture Advisor system. Ensure compatibility with your specific database structure and requirements before deployment. 
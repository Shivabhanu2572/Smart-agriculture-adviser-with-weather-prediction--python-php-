-- Admin Module Database Setup for Smart Agriculture
-- Run this SQL file to create admin tables and initial admin user

-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admin_logs table for activity tracking
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Create system_settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add created_at column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Insert default admin user (password: admin123)
-- Change this password immediately after first login!
INSERT INTO admins (fullname, email, password, role) VALUES 
('System Administrator', 'admin@smartagriculture.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'Smart Agriculture Advisor', 'Website name'),
('site_description', 'Empowering farmers with smart agricultural solutions', 'Website description'),
('max_users', '1000', 'Maximum number of users allowed'),
('maintenance_mode', 'false', 'Maintenance mode status'),
('weather_api_key', '7b46741e515713880330945106c0d3d8', 'OpenWeatherMap API key'),
('market_api_key', '579b464db66ec23bdd0000011ec418109033452d47392bb62daee529', 'Market prices API key'),
('email_notifications', 'true', 'Enable email notifications'),
('auto_backup', 'true', 'Enable automatic database backup');

-- Create indexes for better performance
CREATE INDEX idx_admin_logs_admin_id ON admin_logs(admin_id);
CREATE INDEX idx_admin_logs_created_at ON admin_logs(created_at);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_users_email ON users(email);

-- Insert sample admin activities
INSERT INTO admin_logs (admin_id, action, ip_address, created_at) VALUES
(1, 'System Setup', '127.0.0.1', NOW()),
(1, 'Database Initialized', '127.0.0.1', NOW());

-- Display success message
SELECT 'Admin module setup completed successfully!' as status; 
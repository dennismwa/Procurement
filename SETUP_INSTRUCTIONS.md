# Procurement Management System - Setup Instructions

## Overview
This is a complete procurement management system similar to the World Bank's STEM procurement system, built with pure PHP, HTML, CSS, and JavaScript. No frameworks or external libraries required.

## File Structure
```
procurement-system/
├── index.php                  # Public homepage
├── tender-details.php         # Individual tender details
├── all-tenders.php           # Complete tender listings
├── style.css                 # Public frontend styles
├── config/
│   └── database.php          # Database configuration
├── admin/
│   ├── admin.php             # Admin login
│   ├── dashboard.php         # Admin dashboard
│   ├── tenders.php           # Tender management
│   ├── projects.php          # Project management
│   ├── reports.php           # Financial reports
│   └── admin.css             # Admin panel styles
├── uploads/
│   ├── .htaccess             # Security configuration
│   ├── tenders/              # Tender documents
│   ├── projects/             # Project files
│   └── reports/              # Report files
└── SETUP_INSTRUCTIONS.md     # This file
```

## Installation Steps

### 1. Database Setup
- **Host:** localhost
- **Username:** vxjtgclw_newprocurement  
- **Password:** Zf[Ww5wh]%9V_ya#
- **Database:** vxjtgclw_newprocurement

The system will automatically create all required tables when you first access any page.

### 2. File Upload
Upload all files to your web server maintaining the directory structure above.

### 3. Permissions
Set the following directory permissions:
```bash
chmod 755 uploads/
chmod 755 uploads/tenders/
chmod 755 uploads/projects/
chmod 755 uploads/reports/
```

### 4. Admin Access
- **URL:** `your-domain.com/admin/admin.php`
- **Username:** admin
- **Password:** Admin@254!

### 5. Public Access
- **URL:** `your-domain.com/index.php`

## Features

### Admin Panel Features
✅ **Dashboard**
- Real-time statistics (tenders, projects, budgets)
- Recent activity tracking
- Quick action buttons
- System status monitoring

✅ **Tender Management**
- Create, edit, delete tenders
- File upload for tender documents
- Status management (open/closed/awarded)
- Budget tracking

✅ **Project Management**
- Full project lifecycle management
- Budget vs spent amount tracking
- Project status updates
- Link projects to tenders

✅ **Financial Reports**
- Multiple report types (financial, progress, completion)
- Monthly spending analysis
- Project and tender linking
- Visual reporting dashboard

### Public Features
✅ **Homepage**
- Featured open tenders
- Search functionality
- Statistics overview
- About and contact sections

✅ **Tender Listings**
- Complete tender database
- Advanced filtering (status, budget, date)
- Pagination support
- Search capabilities

✅ **Tender Details**
- Complete tender information
- Document downloads
- Deadline tracking
- Contact information

## Database Tables

The system automatically creates these tables:

### `users`
- Admin authentication
- Role-based access control

### `tenders`
- Complete tender information
- Status and deadline tracking
- Budget management

### `projects`
- Project lifecycle management
- Budget vs actual spending
- Tender relationships

### `files`
- Document management
- Secure file storage
- Type and size tracking

### `reports`
- Financial reporting
- Progress tracking
- Multi-type report support

## Security Features

### File Security
- PHP execution blocked in uploads
- Only specific file types allowed
- Directory browsing disabled
- Proper security headers

### Database Security
- Prepared statements (SQL injection protection)
- Password hashing
- Session management
- Input validation

### Access Control
- Admin authentication required
- Session timeout
- Role-based permissions

## Customization

### Styling
- Modify `style.css` for public interface
- Modify `admin/admin.css` for admin panel
- Both use responsive design principles

### Database
- Connection settings in `config/database.php`
- All queries use prepared statements
- Easy to extend with new tables

### Features
- Modular design allows easy feature addition
- Clean separation between admin and public interfaces
- No external dependencies

## Troubleshooting

### Common Issues

**Database Connection Error**
- Verify database credentials in `config/database.php`
- Ensure database exists and is accessible
- Check MySQL/MariaDB service status

**File Upload Issues**
- Check directory permissions (755 for directories)
- Verify upload directory exists
- Check PHP upload settings in `.htaccess`

**Admin Login Problems**
- Default credentials: admin / Admin@254!
- Clear browser cache/cookies
- Check if user exists in database

**Missing Styles**
- Ensure CSS files are uploaded correctly
- Check file paths in HTML
- Verify web server can serve CSS files

### Performance Optimization

**Database**
- Add indexes on frequently queried columns
- Regular database maintenance
- Consider connection pooling for high traffic

**Files**
- Implement file caching
- Compress CSS/JavaScript files
- Use CDN for static assets

**Server**
- Enable PHP OpCache
- Configure proper cache headers
- Monitor server resources

## Support

This system is designed to be:
- ✅ Self-contained (no external dependencies)
- ✅ Easy to deploy
- ✅ Simple to maintain
- ✅ Secure by default
- ✅ Mobile responsive
- ✅ Search engine friendly

For technical support or customization requests, refer to the inline code comments or contact your development team.

## System Requirements

### Minimum Requirements
- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.2
- Apache/Nginx web server
- 100MB disk space
- SSL certificate (recommended)

### Recommended Requirements
- PHP 8.0 or higher
- MySQL 8.0 or MariaDB 10.5
- 500MB disk space
- Regular backups configured
- Error logging enabled

## Deployment Checklist

- [ ] Database created and accessible
- [ ] All files uploaded with correct permissions
- [ ] Upload directories created and writable
- [ ] Admin login tested
- [ ] Public pages loading correctly
- [ ] File uploads working
- [ ] Email functionality configured (if needed)
- [ ] SSL certificate installed
- [ ] Backup system configured
- [ ] Error logging enabled

## Updates and Maintenance

### Regular Maintenance
- Monitor disk space usage
- Regular database backups
- Review uploaded files
- Check error logs
- Update admin passwords periodically

### Security Updates
- Keep PHP version updated
- Monitor security advisories
- Regular security scans
- Review file permissions
- Update passwords as needed

This system is production-ready and follows best practices for security, performance, and maintainability.
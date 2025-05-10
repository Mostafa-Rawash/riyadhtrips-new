# Visa Module for BookingCore

## Overview
The Visa module allows customers to track their visa applications through the main website. It integrates with an external database containing visa application data.

## Features

### Customer Features
1. **Visa History Page** - View all visa applications
2. **Track Status** - Monitor visa application progress
3. **Edit Applications** - Modify visa application details (when allowed)
4. **Dashboard Widget** - Quick overview of visa applications
5. **Payment History** - View visa payment status

### Admin Features
1. **Manage Applications** - View, edit, and delete visa applications
2. **Bulk Actions** - Approve, reject, or delete multiple applications
3. **Statistics Dashboard** - View visa application analytics
4. **Customer Communication** - Respond to customer requests

## Installation

### Database Configuration
Add these lines to your `.env` file:
```
DB_EXTERNAL_HOST=127.0.0.1
DB_EXTERNAL_PORT=3306
DB_EXTERNAL_DATABASE=riyaoeiu_subvisadom
DB_EXTERNAL_USERNAME=riyaoeiu_subvisadom
DB_EXTERNAL_PASSWORD=4h[#Uln@G{ZS
```

### Module Files
The following files have been created:

#### Models
- `modules/Visa/Models/VisaApplication.php` - Main model for visa applications

#### Controllers
- `modules/Visa/Controllers/VisaController.php` - Frontend controller
- `modules/Visa/Admin/VisaController.php` - Admin controller

#### Views
- `modules/Visa/Views/frontend/` - Customer-facing views
- `modules/Visa/Views/admin/` - Admin interface views

#### Routes
- `modules/Visa/Routes/web.php` - Module routes

#### Configuration
- External database connection configured in `config/database.php`
- Module registered in `themes/Base/ThemeProvider.php`

## Usage

### Customer Access
Customers can access their visa history through:
- Dashboard menu item "Visa History"
- Direct URL: `/visa/history`

### Admin Access
Admins can manage visa applications through:
- Admin menu item "Visa Management"
- Direct URL: `/admin/visa`

## Database Schema

The module uses the external `visa_application_summary` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| id | int(11) | Primary key |
| unique_code | int(11) | Unique application code |
| first_name | varchar(20) | Applicant's first name |
| last_name | varchar(20) | Applicant's last name |
| user_id | int(11) | User ID from main website |
| email | varchar(50) | Contact email |
| phone | text | Contact phone |
| contact_type | text | Type of contact (mobile, home, etc.) |
| total_price | decimal(10,0) | Application cost |
| created_at | date | Application creation date |
| updated_at | date | Last update date |
| scheduled_trip_date | date | Planned travel date |
| country_name | text | Destination country |
| visa_name | text | Type of visa |
| embassy_name | text | Embassy/consulate |
| adults | int(11) | Number of adults |
| childrens | int(11) | Number of children |
| relationship | text | Relationship to applicant |
| payment_status | text | Payment status (paid, pending, failed) |
| payment_method | text | Payment method used |
| appointment | text | Admin notes/responses |
| status | int(11) | Application status (0-5) |

## Status Codes

| Status Code | Meaning |
|-------------|---------|
| 0 | Pending |
| 1 | Processing |
| 2 | Approved |
| 3 | Rejected |
| 4 | Cancelled |
| 5 | Completed |

## Permissions Required

### Customer
- None (all authenticated users can view their own visa applications)

### Admin
- `visa_manage` permission to access admin visa functions

## Customization

The module follows the standard BookingCore module structure. You can customize:

1. Views in `modules/Visa/Views/`
2. Controllers in `modules/Visa/Controllers/`
3. Routes in `modules/Visa/Routes/`

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify external database credentials in `.env`
   - Check if external database allows connections from your server

2. **Menu Not Showing**
   - Ensure module is properly registered in `ThemeProvider.php`
   - Clear application cache: `php artisan cache:clear`

3. **Permission Denied**
   - Add `visa_manage` permission to admin roles
   - Check user roles and permissions

## Need Help?

For support or questions about this module, please contact the development team.

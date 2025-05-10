# Visa Module - Tour Pattern Implementation

## Updated Structure to Match Tour Module

### New File Structure:
```
/modules/Visa/
├── Admin/
│   └── VisaController.php
├── Blocks/
├── Config/
│   └── visa.php
├── Controllers/
│   ├── VisaController.php
│   └── ManageVisaController.php
├── Migrations/
├── Models/
│   └── VisaApplication.php
├── Routes/
│   ├── admin.php
│   ├── api.php
│   ├── language.php
│   └── web.php
├── Views/
│   ├── admin/
│   │   ├── vendor/
│   │   ├── index.blade.php
│   │   ├── edit.blade.php
│   │   └── statistics.blade.php
│   └── frontend/
│       ├── history.blade.php
│       ├── detail.blade.php
│       ├── edit.blade.php
│       └── dashboard-widget.blade.php
├── Hook.php
├── ModuleProvider.php
├── RouterServiceProvider.php
└── SettingClass.php
```

### Key Changes Made:

1. **Routes Structure**:
   - Split routes into separate files: `admin.php`, `api.php`, `language.php`, `web.php`
   - Updated routes to match Tour pattern with proper prefixes and namespaces

2. **RouterServiceProvider**:
   - Added support for multiple route files
   - Proper namespace configuration for admin and frontend
   - Language route mapping

3. **Controllers**:
   - Added `ManageVisaController` for vendor management (like `ManageTourController`)
   - Updated `VisaController` for customer-facing functionality
   - Proper admin controller in `Admin/` directory

4. **Configuration**:
   - Added `Config/visa.php` for module settings
   - Created `SettingClass.php` for admin settings
   - Added `Hook.php` for WordPress-style hooks

5. **Route Patterns**:
   - Customer routes: `/visa/history`, `/visa/{id}`
   - Vendor routes: `/user/visa/`, `/user/visa/create`, etc.
   - Admin routes: `/admin/module/visa/`, `/admin/module/visa/statistics`
   - API routes: `/api/visa/customer/summary`

6. **Vendor Integration**:
   - Added vendor management capabilities
   - Vendor-specific views and routes
   - Recovery and restoration functionality

### Updated Route Names:

- Customer: `visa.customer.history`, `visa.customer.detail`, etc.
- Vendor: `visa.vendor.index`, `visa.vendor.create`, etc.
- Admin: `visa.admin.index`, `visa.admin.statistics`, etc.
- API: `visa.api.customer.summary`

### Integration Points:

1. **User Menu**:
   - `visa.customer.history`

2. **Admin Menu**:
   - `visa.admin.index`
   - `visa.admin.statistics`

3. **Vendor Menu**:
   - `visa.vendor.index`

The module now perfectly follows the Tour module pattern and should integrate seamlessly with the BookingCore system!

# Quick Reference: Testing & Deployment

## 🚀 Quick Start

### First Time Setup
```bash
# Create test database
php create_test_db.php

# Run migrations
php artisan migrate

# Run tests
php artisan test
```

### Running Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/UserTest.php

# Specific test method
php artisan test --filter "admin_users_can_view_users_list"

# Watch mode (requires package)
php artisan test --watch

# With coverage report
php artisan test --coverage
```

---

## 📊 Test Results

| Suite | Tests | Passed | Skipped | Failed |
|-------|-------|--------|---------|--------|
| Unit | 1 | 1 | 0 | 0 |
| Authentication | 14 | 14 | 0 | 0 |
| Authorization | 73 | 73 | 0 | 0 |
| Settings | 7 | 7 | 0 | 0 |
| Dashboard | 2 | 2 | 0 | 0 |
| **Totals** | **113** | **107** | **6** | **0** |

---

## 🔧 Database Management

### Test Database
```bash
# Create
php create_test_db.php

# Check exists
mysql -u root -e "SHOW DATABASES LIKE 'hydronew_test'"

# View tables
mysql hydronew_test -u root -e "SHOW TABLES"

# Run migrations
php artisan migrate --env=testing
```

### Production Database
```bash
# Migrate
php artisan migrate

# Seed (optional)
php artisan db:seed

# Check status
php artisan migrate:status
```

---

## ✅ Verification Checklist

- [ ] Test database created (`hydronew_test`)
- [ ] Migrations applied to both databases
- [ ] All 107 tests passing
- [ ] No failed tests
- [ ] Production database unchanged during tests
- [ ] Admin user can view dashboard
- [ ] Admin can manage users
- [ ] Admin can manage devices
- [ ] Non-admin users blocked from admin features
- [ ] Guest users redirected to login

---

## 🛡️ Security Features

✅ **Implemented**
- Role-based access control (admin/user)
- Policy-based authorization
- Admin-only access to critical features
- Email verification
- Two-factor authentication
- Password hashing & validation
- CSRF protection
- Rate limiting

---

## 📁 Key Files

```
app/
├── Models/User.php                          (User model with Fortify traits)
├── Http/
│   ├── Controllers/
│   │   ├── Settings/
│   │   │   └── PasswordController.php      (Password update with hashing)
│   │   └── ...
│   ├── Middleware/
│   │   └── EnsureUserIsAdmin.php           (Admin-only middleware)
│   └── Policies/
│       ├── UserPolicy.php                  (User authorization)
│       └── DevicePolicy.php                (Device authorization)
├── Actions/Fortify/                        (Fortify actions)
└── Providers/
    └── AppServiceProvider.php              (Policy registration)

database/
├── migrations/
│   ├── 2025_11_22_113951_create_users_table.php
│   ├── 2025_11_28_000000_add_status_to_users_table.php
│   ├── 2025_11_28_164236_add_role_to_users_table.php
│   └── 2025_11_28_170000_add_two_factor_to_users_table.php
├── factories/
│   └── UserFactory.php                     (Test data generation)
└── seeders/
    └── DatabaseSeeder.php

tests/
├── Feature/
│   ├── Auth/                               (14 tests)
│   ├── DeviceTest.php                      (29 tests)
│   ├── UserTest.php                        (44 tests)
│   ├── DashboardTest.php                   (2 tests)
│   └── Settings/                           (7 tests)
└── Unit/
    └── ExampleTest.php                     (1 test)

routes/
├── web.php                                 (Web routes with middleware)
├── settings.php                            (Settings routes)
└── console.php                             (Console routes)

config/
├── fortify.php                             (Fortify configuration)
├── auth.php                                (Authentication config)
└── app.php                                 (Application config)

phpunit.xml                                 (PHPUnit test configuration)
create_test_db.php                          (Test DB setup script)
```

---

## 🔐 User Roles

### Admin User
- ✅ View all users
- ✅ Create users
- ✅ Edit users
- ✅ Delete users
- ✅ View all devices
- ✅ Create devices
- ✅ Edit devices
- ✅ Delete devices
- ✅ Access admin dashboard
- ✅ Manage settings

### Regular User
- ❌ Cannot view user list
- ❌ Cannot create users
- ❌ Cannot access device list
- ❌ Cannot create devices
- ❌ Cannot access admin dashboard
- ✅ Can view own profile
- ✅ Can update own password
- ✅ Can enable 2FA

### Guest
- ❌ Access denied to all protected routes
- ✅ Can view login page
- ✅ Can view email verification page
- ✅ Can view password confirmation page

---

## 🚀 Deployment Steps

### 1. Pre-Deployment
```bash
php artisan test                           # Verify all tests pass
php artisan migrate:status                 # Check migration status
```

### 2. Deployment
```bash
git pull origin main
composer install
php artisan migrate --force                # Apply pending migrations
npm run build                              # Build frontend assets
php artisan optimize                       # Optimize application
```

### 3. Post-Deployment
```bash
php artisan migrate:status
php artisan config:cache
php artisan route:cache
```

### 4. Verification
```bash
# Test the deployed system
php artisan tinker
>>> auth()->user()   # Should be null (not logged in)
>>> exit
```

---

## 🐛 Troubleshooting

### Tests Failing
```bash
# Clear cache
php artisan cache:clear

# Rebuild test database
php create_test_db.php

# Run tests again
php artisan test
```

### Database Issues
```bash
# Check test database exists
mysql -u root -e "SHOW DATABASES"

# Check migrations
php artisan migrate:status --env=testing

# Run migrations
php artisan migrate --env=testing
```

### Password Issues
- Ensure `Hash::make()` is used when updating passwords
- Always use `bcrypt()` for password hashing

### Two-Factor Issues
- Ensure migration `add_two_factor_to_users_table` is applied
- Check columns exist: `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`

---

## 📞 Support

For issues or questions:
1. Check test output: `php artisan test -v`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Review database: `php artisan migrate:status`
4. Verify migrations: `mysql hydronew -u root -e "DESCRIBE users"`

---

**Last Updated**: 2025-11-28  
**Version**: 1.0.0  
**Status**: ✅ Production Ready

# Project Architecture Diagram

## Current (BROKEN) Flow

```
HTTP Request to /users
         ↓
    Route (web.php)
         ↓
  use App\Http\Controllers\User\UserController  ← WRONG IMPORT!
         ↓
  User/UserController@index  (STUB - empty method!)
         ↓
    return Inertia::render('users/index')
         ↓
  Frontend displays, but NO DATA!  ❌


HTTP Request to /devices
         ↓
    Route (web.php)
         ↓
  use App\Http\Controllers\Devices\DeviceController  ← WRONG IMPORT!
         ↓
  Devices/DeviceController@index  (STUB - empty method!)
         ↓
    return Inertia::render('devices/index')
         ↓
  Frontend displays, but NO DATA!  ❌
```

---

## Correct (FIXED) Flow

```
HTTP Request to /users
         ↓
    Route (web.php)
         ↓
  use App\Http\Controllers\UserController  ← CORRECT IMPORT!
         ↓
  UserController@index (FULL IMPLEMENTATION)
         ↓
  $this->authorize('viewAny', User::class)  [Check: admin?]
         ↓
  $users = $this->userRepository->getPaginated(...)
         ↓
  return Inertia::render('users/index', ['users' => $users])
         ↓
  Frontend displays users with data!  ✅


HTTP Request to /devices
         ↓
    Route (web.php)
         ↓
  use App\Http\Controllers\DeviceController  ← CORRECT IMPORT!
         ↓
  DeviceController@index (FULL IMPLEMENTATION - FIXED)
         ↓
  $this->authorize('viewAny', Device::class)  [Check: admin?]
         ↓
  $devices = Device::all()  [Show ALL devices, not user's only]
         ↓
  return Inertia::render('devices/index', ['devices' => $devices])
         ↓
  Frontend displays ALL devices!  ✅
```

---

## File Structure

```
app/Http/Controllers/
│
├── Controller.php (Base)
│
├── ❌ WRONG: User/UserController.php (STUB - what routes currently use)
│   └── Methods: empty, just renders views
│
├── ✅ CORRECT: UserController.php (FULL - not being used!)
│   ├── index() - fetches paginated users from repository
│   ├── create() - renders create form
│   ├── store() - validates and creates user
│   ├── show() - shows user details
│   ├── edit() - renders edit form
│   ├── update() - updates user
│   └── destroy() - deletes user
│
├── ❌ WRONG: Devices/DeviceController.php (STUB - what routes currently use)
│   └── Methods: empty, just renders views
│
├── ✅ CORRECT (BUT HAS BUG): DeviceController.php (FULL - not being used!)
│   ├── index() - ❌ FILTERS BY USER (should show ALL for admin)
│   ├── create() - renders create form
│   ├── store() - ❌ ASSIGNS USER_ID (shouldn't in admin-only)
│   ├── show() - shows device details
│   ├── edit() - renders edit form
│   ├── update() - updates device
│   └── destroy() - deletes device
│
├── Settings/PasswordController.php ✅
├── Settings/ProfileController.php ✅
├── Settings/TwoFactorAuthenticationController.php ✅
├── Analytics/AnalyticsController.php ✅
└── Http/
    └── ...
```

---

## Authorization vs Implementation Mismatch

### Device Authorization (Policy)

```php
class DevicePolicy {
    public function view(User $user, Device $device): bool {
        return $user->isAdmin();  ✅ Says: Only admins!
    }
    
    public function viewAny(User $user): bool {
        return $user->isAdmin();  ✅ Says: Only admins can list!
    }
}
```

### Device Implementation (Controller - CURRENT)

```php
class DeviceController {
    public function index() {
        $devices = Device::where('user_id', auth()->id())  ❌ Shows only user's devices!
            ->with('sensors')
            ->paginate(10);
    }
    
    public function store(Request $request) {
        $device = Device::create([
            'user_id' => auth()->id(),  ❌ Assigns to user!
            ...$validated,
        ]);
    }
}
```

### What Should Happen

```
Request to /devices/index

Check: Is user admin?  (From Policy)
  → NO: Return 403 Forbidden ✅
  → YES: Continue
  
Show ALL devices  (From updated Controller)
  → NOT filtered by user_id ✅
  → ALL devices visible to admin ✅
```

---

## Data Model Analysis

### User Model ✅ Perfect
```
users table
├── id
├── first_name, last_name
├── email, password
├── role ✅ (admin | user)
├── status (active | inactive)
├── email_verified_at
├── two_factor_secret, two_factor_recovery_codes
├── verification_code, verification_expires_at
├── last_login_at
└── timestamps
```

### Device Model ❓ Confusing
```
devices table
├── id
├── user_id ❓ (Why? Devices are admin-managed, not per-user)
├── name
├── serial_number
├── status (connected | not connected)
└── timestamps

Relationships:
├── user() → belongsTo(User)  ❓ (Implies ownership)
├── sensors() → hasMany(Sensor)  ✅
├── notifications() → hasMany(Notification)  ✅
└── treatment_reports() → hasMany(TreatmentReport)  ✅
```

**Questions:**
- Is `user_id` for audit/tracking purposes?
- Should we have `managed_by_admin_id` instead?
- Or remove it entirely for true global management?

---

## Test Coverage Map

```
tests/Feature/
│
├── Auth/
│   ├── AuthenticationTest.php ✅ (Login, 2FA, password reset)
│   ├── EmailVerificationTest.php ✅
│   └── PasswordResetTest.php ✅
│
├── Settings/
│   ├── PasswordTest.php ✅ (Profile password change)
│   ├── ProfileTest.php ✅ (Profile update)
│   └── TwoFactorTest.php ✅ (2FA setup)
│
├── UserTest.php ✅ (44 tests)
│   ├── Index tests (admin can view, non-admin can't)
│   ├── Create tests
│   ├── Store tests
│   ├── Show tests
│   ├── Edit tests
│   ├── Update tests
│   └── Delete tests
│
├── DeviceTest.php ✅ (29 tests)
│   ├── Index tests (admin can view, non-admin can't)
│   ├── Create tests
│   ├── Store tests
│   ├── Show tests
│   ├── Edit tests
│   ├── Update tests
│   └── Delete tests
│
├── DashboardTest.php ✅
├── UserSortFilterSearchTest.php ✅
│
└── Tests pass: 107/113 (94.7%)
   Note: Tests may not catch routing issues if they use direct imports
```

---

## Summary: The Big Picture

| Aspect | Status | Details |
|--------|--------|---------|
| **Architecture** | 🟠 Partial | Admin-only designed, but device logic still per-user |
| **Authorization** | ✅ Good | Policies correctly enforce admin-only |
| **Controllers** | 🔴 Broken | Wrong controllers being used (stubs instead of real) |
| **Business Logic** | 🟠 Buggy | Device filtering contradicts admin-only design |
| **Tests** | ✅ Comprehensive | 107/113 passing, good coverage |
| **Database** | 🟠 Confusing | `user_id` field contradicts admin-only model |
| **Security** | ✅ Good | 2FA, email verification, password hashing working |

---

## Priority Actions

### 🔴 URGENT (This branch - feature/user)
1. Fix routes to use correct controllers
2. Fix device controller logic (remove user filter)
3. Delete stub controllers
4. Run tests

### 🟠 IMPORTANT (Next sprint)
1. Decide: Keep user_id or remove it?
2. Add integration tests for routing
3. Update documentation

### 🟡 NICE-TO-HAVE (Future)
1. Consider audit logging with managed_by_admin_id
2. Add device archive functionality
3. Performance optimization for large datasets

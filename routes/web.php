<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Carbon\Carbon;

// Controllers
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\BiometricDeviceController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\PayReportController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\SalaryMasterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ITSupportController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\SecuritySettingController;
use App\Http\Controllers\SecurityDashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\Auth\FaceLoginController;
use App\Http\Controllers\OccupancyController;
use App\Http\Controllers\WebAuthnController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');


/*
|--------------------------------------------------------------------------
| QUICK ATTENDANCE (LEGACY / DEVICE SUPPORT)
|--------------------------------------------------------------------------
*/
// Legacy device-triggered attendance — require auth to prevent anonymous manipulation
Route::middleware('auth')->group(function () {
    Route::get('attended/{user_id}', [AttendanceController::class, 'attended'])->name('attended');
    Route::get('attended-before/{user_id}', [AttendanceController::class, 'attendedBefore'])->name('attendedBefore');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Auth::routes([
    'register' => false,
    'reset' => false,
]);
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout.get');

Route::post('/face-login', [FaceLoginController::class, 'loginWithFace'])->name('face.login');

/*
|--------------------------------------------------------------------------
| WEBAUTHN / ZERO-STORAGE BIOMETRICS (FIDO2 / WINDOWS HELLO)
|--------------------------------------------------------------------------
*/
Route::post('/webauthn/login/challenge', [WebAuthnController::class, 'loginChallenge'])->name('webauthn.login.challenge');
Route::post('/webauthn/login/verify', [WebAuthnController::class, 'loginVerify'])->name('webauthn.login.verify');
Route::middleware('auth')->group(function () {
    Route::post('/webauthn/register/challenge', [WebAuthnController::class, 'registerChallenge'])->name('webauthn.register.challenge');
    Route::post('/webauthn/register/verify', [WebAuthnController::class, 'registerVerify'])->name('webauthn.register.verify');
});

/*
|--------------------------------------------------------------------------
| FACE MODULE (ADMIN ONLY — requires auth)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin']], function () {
    // Capture face is an admin-only operation — biometric data must be protected
    Route::post('/employees/{employee}/capture-face', [EmployeeController::class, 'captureFace'])
        ->name('employees.capture.face');

    Route::get('/employees/{employee}/capture', function (Employee $employee) {
        return view('admin.face_capture', [
            'employee' => $employee
        ]);
    })->name('employees.capture.view');
});

/*
|--------------------------------------------------------------------------
| DAILY BIOMETRIC KIOSK (ODD/EVEN CHECK-IN TOGGLE)
|--------------------------------------------------------------------------
*/
// Kiosk view is intentionally public (terminal screen)
Route::get('/kiosk', function () {
    return view('admin.kiosk'); 
})->name('kiosk.view');

// Write endpoints are rate-limited to prevent automated abuse
Route::post('/scan-face', [FaceController::class, 'scanFace'])
    ->middleware('throttle:30,1')
    ->name('scan.face');
Route::post('/fallback-checkin', [FaceController::class, 'fallbackCheckIn'])
    ->middleware('throttle:10,1')
    ->name('fallback.checkin');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin']], function () {

    // DASHBOARD
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    // LIVE OFFICE OCCUPANCY
    Route::get('/occupancy', [OccupancyController::class, 'index'])->name('occupancy.index');
    Route::get('/api/occupancy/status', [OccupancyController::class, 'index'])->name('api.occupancy.status');

    // EMPLOYEES
    Route::resource('/employees', EmployeeController::class);
    Route::get('/employees/create-face', [EmployeeController::class, 'create'])->name('employees.face.create');
    Route::delete('/employees/{employee}/photo', [EmployeeController::class, 'deletePhoto'])->name('employees.photo.delete');

    // USER MANAGEMENT
    Route::resource('/users', UserController::class);
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle');

    // ROLE & PERMISSION MANAGEMENT
    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::put('/roles/{id}', [RolePermissionController::class, 'update'])->name('roles.update');

    // NOTE: /audit-logs is intentionally NOT defined here to avoid a duplicate route name.
    // The effective definition lives in the ['admin', 'it-support'] group below, which
    // correctly restricts it to those two roles. Admin access is still enforced because
    // 'admin' is listed in that group's roles array.

    // SECURITY SETTINGS (ADMIN ONLY)
    Route::get('/security/settings', [SecuritySettingController::class, 'index'])->name('security.settings');
    Route::post('/security/settings/update', [SecuritySettingController::class, 'update'])->name('security.settings.update');

    // ATTENDANCE & TIME EXCEPTIONS (ADMIN ONLY)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::get('/latetime', [AttendanceController::class, 'indexLatetime'])->name('latetime');
    Route::post('/attendance/approve-late/{id}', [AttendanceController::class, 'approveLate'])->name('attendance.approve-late');
    Route::post('/attendance/reject-late/{id}', [AttendanceController::class, 'rejectLate'])->name('attendance.reject-late');
    
    // --- MISSED PUNCH / DEFENSE RESOLUTION ROUTES ---
    Route::post('/api/attendance/resolve/{id}', [AttendanceController::class, 'resolveMissedScan'])->name('api.attendance.resolve');

    // Overtime Approve & Reject Routes (Admin Only)
    Route::post('/overtime/approve/{id}', [LeaveController::class, 'approveOvertime'])->name('overtime.approve');
    Route::post('/overtime/reject/{id}', [LeaveController::class, 'rejectOvertime'])->name('overtime.reject');
    Route::get('/overtime-approvals', [OvertimeController::class, 'index'])->name('overtime.index');
    Route::post('/overtime-approvals/approve/{id}', [OvertimeController::class, 'approve'])->name('overtime.approve.alt');
    Route::post('/overtime-approvals/reject/{id}', [OvertimeController::class, 'reject'])->name('overtime.reject.alt');

    // SCHEDULE (ADMIN ONLY)
    Route::resource('/schedule', ScheduleController::class);

    // MANUAL CHECK
    Route::get('/check', [CheckController::class, 'index'])->name('check');
    Route::get('/sheet-report', [CheckController::class, 'sheetReport'])->name('sheet-report');
    Route::post('/check-store', [CheckController::class, 'CheckStore'])->name('check_store');

    // ADVANCED REPORTS HUB (ADMIN ONLY)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // CHART + EXPORT
    Route::get('/attendance/chart-data', [AttendanceController::class, 'chartData'])->name('attendance.chart');
    Route::get('/attendance/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
    Route::get('/attendance/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');

    // --- SALARY MASTER (STRICT ADMIN ONLY) ---
    Route::get('/settings/salary-master', [SalaryMasterController::class, 'index'])->name('salary.master');
    Route::post('/settings/salary-master/update/{id}', [SalaryMasterController::class, 'update'])->name('salary.master.update');

    // --- REAL SYSTEM SETTINGS (STRICT ADMIN ONLY) ---
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');

    // --- ANNUAL HOLIDAY CALENDAR (STRICT ADMIN ONLY) ---
    Route::get('/settings/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/settings/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::put('/settings/holidays/{id}', [HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/settings/holidays/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    Route::get('/api/holidays/events', [HolidayController::class, 'events'])->name('api.holidays.events');
});

/*
|--------------------------------------------------------------------------
| IT SUPPORT & INFRASTRUCTURE ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'it-support']], function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
    Route::resource('/finger_device', BiometricDeviceController::class);
    Route::post('/finger_device/{id}/block', [BiometricDeviceController::class, 'block'])->name('finger_device.block');
    Route::post('/finger_device/{id}/activate', [BiometricDeviceController::class, 'activate'])->name('finger_device.activate');
    Route::delete('/finger_device/destroy', [BiometricDeviceController::class, 'massDestroy'])->name('finger_device.massDestroy');
    Route::get('/finger_device/{fingerDevice}/employees/add', [BiometricDeviceController::class, 'addEmployee'])->name('finger_device.add.employee');
    Route::get('/finger_device/{fingerDevice}/get/attendance', [BiometricDeviceController::class, 'getAttendance'])->name('finger_device.get.attendance');
    Route::get('/sync-attendance', [BiometricDeviceController::class, 'sync'])->name('sync.attendance');
    Route::resource('/cameras', CameraController::class);
    Route::post('/cameras/{id}/toggle-status', [CameraController::class, 'toggleStatus'])->name('cameras.toggle');
    Route::get('/api/cameras/health', [CameraController::class, 'apiHealthStatus'])->name('cameras.api.health');
    Route::get('/finger_device/clear/attendance', function () {
        $midnight = Carbon::createFromTime(23, 50, 00);
        $diff = now()->diffInMinutes($midnight);
        dispatch(new \App\Jobs\ClearAttendanceJob())->delay(now()->addMinutes($diff));
        return back();
    })->name('finger_device.clear.attendance');
});

/*
|--------------------------------------------------------------------------
| SECURITY DASHBOARD (ADMIN, IT-SUPPORT & SECURITY)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'it-support', 'security']], function () {
    Route::get('/security/dashboard', [SecurityDashboardController::class, 'index'])->name('security.dashboard');
});

/*
|--------------------------------------------------------------------------
| EMPLOYEE REQUESTS & PAYSLIP (ADMIN & EMPLOYEE)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'employee']], function () {
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave');
    Route::get('/overtime', [LeaveController::class, 'indexOvertime'])->name('overtime');
    Route::get('/pay-report', [PayReportController::class, 'index'])->name('pay.report');
    Route::get('/pay-report/fetch/{id}', [PayReportController::class, 'fetchPayData'])->name('pay-report.fetch');
});

/*
|--------------------------------------------------------------------------
| IT SUPPORT ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['it-support']], function () {
    Route::get('/it-support/dashboard', [ITSupportController::class, 'index'])->name('it-support.dashboard');
});

/*
|--------------------------------------------------------------------------
| RECEPTIONIST ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['receptionist']], function () {
    Route::get('/receptionist/dashboard', [ReceptionistController::class, 'index'])->name('receptionist.dashboard');
});


/*
|--------------------------------------------------------------------------
| ADMIN & RECEPTIONIST & SECURITY ROUTES (SHARED)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'receptionist', 'security']], function () {
    // --- VISITOR MANAGEMENT (STABLE VERSION) ---
    Route::get('/visitor-checkin', function () {
        return view('admin.visitor_checkin');
    })->name('visitor.checkin');
    Route::post('/visitor/store', [VisitorController::class, 'store'])->name('visitor.store');
    Route::get('/visitor-logs', [VisitorController::class, 'index'])->name('admin.visitor_index'); 
    Route::post('/visitor/checkout/{id}', [VisitorController::class, 'checkout'])->name('visitor.checkout');
    Route::get('/visitor-download', [VisitorController::class, 'downloadReport'])->name('visitor.export'); 
    Route::post('/visitor/prune-expired', [VisitorController::class, 'pruneExpired'])->name('visitor.prune');
    Route::delete('/visitor/delete/{id}', [VisitorController::class, 'destroy'])->name('visitor.destroy');
});

/*
|--------------------------------------------------------------------------
| EMPLOYEE / PERSONAL DASHBOARD ROUTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'employee']], function () {
    Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
});
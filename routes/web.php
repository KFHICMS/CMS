<?php

use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChildController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventAttendanceController;
use App\Http\Controllers\Admin\FieldSubmissionController;
use App\Http\Controllers\QuestionnaireResponseController;
use App\Http\Controllers\Admin\EventEnumeratorController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Admin\BackupController;

use App\Http\Controllers\ChildOfficer\DashboardController;
use App\Http\Controllers\ChildOfficer\ChildController as ChildOfficerChildController;
use App\Http\Controllers\ChildOfficer\GuardianController as ChildOfficerGuardianController;

use App\Http\Controllers\ChildOfficer\QRCodeController as ChildOfficerQRCodeController;
use App\Http\Controllers\ChildOfficer\ChildInformationController as ChildOfficerChildInformationController;
use App\Http\Controllers\FieldOfficer\DashboardController as FieldOfficerDashboardController;
use App\Http\Controllers\FieldOfficer\ActivityController as FieldOfficerActivityController;
use App\Http\Controllers\FieldOfficer\ReportController as FieldOfficerReportController;
use App\Http\Controllers\FieldOfficer\FollowUpController as FieldOfficerFollowUpController;
use App\Http\Controllers\Admin\FollowUpController as AdminFollowUpController;

use App\Http\Controllers\Enumerator\DashboardController as EnumeratorDashboardController;
use App\Http\Controllers\Enumerator\EventController as EnumeratorEventController;
use App\Http\Controllers\Enumerator\AttendanceController as EnumeratorAttendanceController;
use App\Http\Controllers\Enumerator\QuestionnaireController as EnumeratorQuestionnaireController;
use App\Http\Controllers\Enumerator\SubmissionController as EnumeratorSubmissionController;
use App\Http\Controllers\Admin\EnumeratorInvitationController;

use App\Http\Controllers\PublicInvitationController;
use App\Http\Middleware\PublicInvitationHeaders;
use App\Http\Controllers\Admin\QuestionnaireSubmissionController;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    return view('home.index');
})->name('home');


// Login page
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');


// Process login
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');


// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Protected Admin Area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::resource('/admin/programs', ProgramController::class)
        ->except('show')
        ->middleware('role:admin');
    
    Route::get('/admin/reports', [AdminReportController::class, 'index'])
        ->name('admin.reports.index');
    Route::get('/admin/reports/export', [AdminReportController::class, 'export'])
        ->middleware('throttle:10,1')
        ->name('admin.reports.export');

    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])
        ->name('admin.audit_logs.index');
    Route::get('/admin/audit-logs/export', [AuditLogController::class, 'export'])
        ->middleware('throttle:10,1')
        ->name('admin.audit_logs.export');

    Route::get('/admin/field-submissions', [FieldSubmissionController::class, 'index'])
        ->name('field_submissions.index');
    Route::get('/admin/field-submissions/{submission}', [FieldSubmissionController::class, 'show'])
        ->name('field_submissions.show');
    Route::patch('/admin/field-submissions/{submission}/correct', [FieldSubmissionController::class, 'correct'])
        ->name('field_submissions.correct');
    //followups
    Route::get('/admin/follow-ups', [AdminFollowUpController::class, 'index'])
        ->name('admin_follow_ups.index');
    Route::get('/admin/follow-ups/create', [AdminFollowUpController::class, 'create'])
        ->name('admin_follow_ups.create');
    Route::post('/admin/follow-ups', [AdminFollowUpController::class, 'store'])
        ->name('admin_follow_ups.store');
    Route::get('/admin/follow-ups/{followUp}/edit', [AdminFollowUpController::class, 'edit'])
        ->name('admin_follow_ups.edit');
    Route::put('/admin/follow-ups/{followUp}', [AdminFollowUpController::class, 'update'])
        ->name('admin_follow_ups.update');

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin User Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/users', UserController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin Child Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/children', ChildController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin Guardian Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/guardians', GuardianController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin Event Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/events', EventController::class);
    
    Route::get('/admin/events/{event}/enumerators', [EventEnumeratorController::class, 'edit'])
        ->name('events.enumerators.edit');
    Route::put('/admin/events/{event}/enumerators', [EventEnumeratorController::class, 'update'])
        ->name('events.enumerators.update');


    /*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/
    /*
    |--------------------------------------------------------------------------
    | Admin QR Code
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/admin/children/{child}/qr/generate',
        [QRCodeController::class, 'generate']
    )->name('children.qr.generate');

    Route::get(
        '/admin/children/{child}/qr',
        [QRCodeController::class, 'show']
    )->name('children.qr.show');


    /*
    |--------------------------------------------------------------------------
    | Admin Event Attendance
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/events/{event}/attendance',
        [EventAttendanceController::class, 'index']
    )->name('events.attendance.index');

    Route::post(
        '/admin/events/{event}/attendance/scan',
        [EventAttendanceController::class, 'scan']
    )->name('events.attendance.scan');


    /*
    |--------------------------------------------------------------------------
    | Admin Questionnaires
    |--------------------------------------------------------------------------
    */

    Route::resource(
        '/admin/events/{event}/questionnaires',
        \App\Http\Controllers\Admin\QuestionnaireController::class
    )->names('events.questionnaires');


    /*
    |--------------------------------------------------------------------------
    | Admin Questionnaire Questions
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/create',
        [\App\Http\Controllers\Admin\QuestionController::class, 'create']
    )->name('events.questionnaires.questions.create');

    Route::post(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions',
        [\App\Http\Controllers\Admin\QuestionController::class, 'store']
    )->name('events.questionnaires.questions.store');

    Route::get(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/{question}/edit',
        [\App\Http\Controllers\Admin\QuestionController::class, 'edit']
    )->name('events.questionnaires.questions.edit');

    Route::put(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/{question}',
        [\App\Http\Controllers\Admin\QuestionController::class, 'update']
    )->name('events.questionnaires.questions.update');

    Route::delete(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/{question}',
        [\App\Http\Controllers\Admin\QuestionController::class, 'destroy']
    )->name('events.questionnaires.questions.destroy');

    Route::post(
        '/admin/events/{event}/questionnaires/{questionnaire}/invitations',
        [EnumeratorInvitationController::class, 'store']
    )->name('events.questionnaires.invitations.store');

    Route::post(
        '/admin/events/{event}/questionnaires/{questionnaire}/invitations/{invitation}/revoke',
        [EnumeratorInvitationController::class, 'revoke']
    )->name('events.questionnaires.invitations.revoke');

    Route::post(
        '/admin/events/{event}/questionnaires/{questionnaire}/invitations/{invitation}/replace',
        [EnumeratorInvitationController::class, 'replace']
    )->name('events.questionnaires.invitations.replace');

    Route::get('/admin/questionnaire-submissions', [QuestionnaireSubmissionController::class, 'index'])
        ->name('admin_submissions.index');
    Route::get('/admin/questionnaire-submissions/{submission}', [QuestionnaireSubmissionController::class, 'show'])
        ->name('admin_submissions.show');
    Route::patch('/admin/questionnaire-submissions/{submission}/review', [QuestionnaireSubmissionController::class, 'review'])
        ->name('admin_submissions.review');

    Route::get('/admin/questionnaire-submissions/{submission}/edit', [QuestionnaireSubmissionController::class, 'edit'])
        ->name('admin_submissions.edit');
    Route::put('/admin/questionnaire-submissions/{submission}', [QuestionnaireSubmissionController::class, 'update'])
        ->name('admin_submissions.update');

    Route::get('/admin/questionnaire-submissions/{submission}/photo', [QuestionnaireSubmissionController::class, 'photo'])
        ->name('admin_submissions.photo');

    Route::get('/admin/backups', [BackupController::class, 'index'])->name('admin.backups.index');
    Route::post('/admin/backups', [BackupController::class, 'store'])->middleware('throttle:5,1')->name('admin.backups.store');
    Route::get('/admin/backups/{backup}/download', [BackupController::class, 'download'])->middleware('throttle:10,1')->name('admin.backups.download');
    Route::get('/admin/backups/{backup}/restore', [BackupController::class, 'restoreForm'])->name('admin.backups.restore.form');
    Route::post('/admin/backups/{backup}/restore', [BackupController::class, 'restore'])->middleware('throttle:3,1')->name('admin.backups.restore');
});

/*
|--------------------------------------------------------------------------
| Public Enumerator Invitation Links (no login; secret link only)
|--------------------------------------------------------------------------
*/
Route::prefix('join')
    ->middleware([PublicInvitationHeaders::class])
    ->name('public.invitations.')
    ->group(function () {
        Route::get('/{token}', [PublicInvitationController::class, 'show'])
            ->where('token', '[A-Za-z0-9_-]{43}')
            ->middleware('throttle:public-invitation-view')
            ->name('show');

        Route::post('/{token}', [PublicInvitationController::class, 'store'])
            ->where('token', '[A-Za-z0-9_-]{43}')
            ->middleware('throttle:public-invitation-submit')
            ->name('store');

        Route::post('/{token}/identify', [PublicInvitationController::class, 'identify'])
            ->middleware('throttle:public-invitation-identify')
            ->name('identify');

        Route::get('/{token}/thanks', [PublicInvitationController::class, 'thanks'])
            ->where('token', '[A-Za-z0-9_-]{43}')
            ->middleware('throttle:public-invitation-view')
            ->name('thanks');
    });


/*
|--------------------------------------------------------------------------
| Public QR Scan
|--------------------------------------------------------------------------
*/

Route::get(
    '/qr/scan/{token}',
    [QRCodeController::class, 'scan']
)->name('qr.scan');


/*
|--------------------------------------------------------------------------
| Questionnaire Response
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,child_officer,field_officer,enumerator'])->group(function () {
    Route::get(
        '/questionnaires/{questionnaire}/children/{child}/respond',
        [QuestionnaireResponseController::class, 'create']
    )->name('questionnaires.responses.create');

    Route::post(
        '/questionnaires/{questionnaire}/children/{child}/respond',
        [QuestionnaireResponseController::class, 'store']
    )->name('questionnaires.responses.store');

    Route::get(
        '/questionnaires/{questionnaire}/children/{child}/success',
        [QuestionnaireResponseController::class, 'success']
    )->name('questionnaires.responses.success');
});


/*
|--------------------------------------------------------------------------
| Child Officer Area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:child_officer,admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Child Officer Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/child-officer/dashboard',
        [DashboardController::class, 'index']
    )->name('child_officer.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Child Officer Children
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/child-officer/children',
        [ChildOfficerChildController::class, 'index']
    )->name('child_officer.children.index');

    Route::get(
        '/child-officer/children/create',
        [ChildOfficerChildController::class, 'create']
    )->name('child_officer.children.create');

    Route::post(
        '/child-officer/children',
        [ChildOfficerChildController::class, 'store']
    )->name('child_officer.children.store');

    Route::get(
        '/child-officer/children/{child}/edit',
        [ChildOfficerChildController::class, 'edit']
    )->name('child_officer.children.edit');

    Route::put(
        '/child-officer/children/{child}',
        [ChildOfficerChildController::class, 'update']
    )->name('child_officer.children.update');

    Route::get(
        '/child-officer/children/{child}',
        [ChildOfficerChildController::class, 'show']
    )->name('child_officer.children.show');


    /*
    |--------------------------------------------------------------------------
    | Child Officer Guardians
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/child-officer/guardians/create',
        [ChildOfficerGuardianController::class, 'create']
    )->name('child_officer.guardians.create');
    
    Route::get(
        '/child-officer/guardians/search-children',
        [ChildOfficerGuardianController::class, 'searchChildren']
    )->name('child_officer.guardians.search_children');
    
    Route::post(
        '/child-officer/guardians',
        [ChildOfficerGuardianController::class, 'store']
    )->name('child_officer.guardians.store');

    Route::get(
        '/child-officer/qr/scan',
        [ChildOfficerQRCodeController::class, 'scan']
    )->middleware('role:child_officer')->name('child_officer.qr.scan');

    Route::post(
        '/child-officer/qr/lookup',
        [ChildOfficerQRCodeController::class, 'lookup']
    )->middleware('role:child_officer')->name('child_officer.qr.lookup');

    Route::delete(
        '/child-officer/children/{child}',
        [ChildOfficerChildController::class, 'destroy']
    )->name('child_officer.children.destroy');

    Route::get(
        '/child-officer/guardians/{guardian}/edit',
        [ChildOfficerGuardianController::class, 'edit']
    )->name('child_officer.guardians.edit');
    
    Route::put(
        '/child-officer/guardians/{guardian}',
        [ChildOfficerGuardianController::class, 'update']
    )->name('child_officer.guardians.update');
    
    Route::delete(
        '/child-officer/guardians/{guardian}',
        [ChildOfficerGuardianController::class, 'destroy']
    )->name('child_officer.guardians.destroy');

    Route::get(
        '/child-officer/children/{child}/information/create',
        [ChildOfficerChildInformationController::class, 'create']
    )->name('child_officer.child_information.create');
    
    Route::post(
        '/child-officer/children/{child}/information',
        [ChildOfficerChildInformationController::class, 'store']
    )->name('child_officer.child_information.store');




});

Route::middleware(['auth', 'role:enumerator'])
    ->prefix('enumerator')
    ->name('enumerator.')
    ->group(function () {
        Route::get('/dashboard', [EnumeratorDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/events/{event}', [EnumeratorEventController::class, 'show'])
            ->name('events.show');
    Route::get('/events/{event}/attendance', [EnumeratorAttendanceController::class, 'index'])
            ->name('events.attendance.index');
    Route::post('/events/{event}/attendance/identify', [EnumeratorAttendanceController::class, 'identify'])
            ->name('events.attendance.identify');
    Route::post('/events/{event}/attendance', [EnumeratorAttendanceController::class, 'store'])
            ->name('events.attendance.store');
    Route::get('/events/{event}/questionnaires/{questionnaire}', [EnumeratorQuestionnaireController::class, 'show'])
            ->name('events.questionnaires.show');
    Route::post('/events/{event}/questionnaires/{questionnaire}/identify', [EnumeratorQuestionnaireController::class, 'identify'])
            ->middleware('throttle:60,1')
            ->name('events.questionnaires.identify');
    Route::get('/submissions', [EnumeratorSubmissionController::class, 'index'])
            ->name('submissions.index');
    Route::get('/submissions/questionnaires/{submission}', [EnumeratorSubmissionController::class, 'show'])
            ->name('submissions.show');       
});

Route::middleware(['auth', 'role:field_officer'])->prefix('field-officer')->name('field_officer.')->group(function () {
    Route::get('/dashboard', [FieldOfficerDashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/programs', [FieldOfficerDashboardController::class, 'programs'])
        ->name('programs.index');
    Route::get('/activities', [FieldOfficerDashboardController::class, 'activities'])
        ->name('activities.index');
    Route::get('/follow-ups', [FieldOfficerDashboardController::class, 'followUps'])
        ->name('follow_ups.index');
    Route::get('/programs/{program}', [FieldOfficerDashboardController::class, 'program'])
        ->name('programs.show');
    Route::get('/activities/{event}', [FieldOfficerActivityController::class, 'show'])
        ->name('activities.show');
    Route::post('/activities/{event}/identify', [FieldOfficerActivityController::class, 'identify'])
        ->middleware('throttle:60,1')
        ->name('activities.identify');
    Route::post('/activities/{event}/save', [FieldOfficerActivityController::class, 'save'])
        ->name('activities.save');
    Route::post('/submissions/{submission}/submit', [FieldOfficerActivityController::class, 'submit'])
        ->name('submissions.submit');
    Route::get('/submissions/{submission}/review', [FieldOfficerActivityController::class, 'review'])
        ->name('submissions.review');
    Route::get('/reports', [FieldOfficerReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/export', [FieldOfficerReportController::class, 'export'])
        ->middleware('throttle:10,1')
        ->name('reports.export');
    Route::get('/follow-ups/{followUp}', [FieldOfficerFollowUpController::class, 'show'])
        ->name('follow_ups.show');
    Route::patch('/follow-ups/{followUp}', [FieldOfficerFollowUpController::class, 'update'])
        ->name('follow_ups.update');
});

Route::middleware(['auth', 'role:admin,child_officer,field_officer,enumerator'])
    ->prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::get('/{conversation}', [MessageController::class, 'show'])->whereNumber('conversation')->name('show');
        Route::get('/notifications', [MessageController::class, 'notifications'])->middleware('throttle:60,1')->name('notifications');
        Route::post('/{conversation}/reply', [MessageController::class, 'reply'])->middleware('throttle:30,1')->whereNumber('conversation')->name('reply');
        Route::delete('/{conversation}', [MessageController::class, 'destroy'])->whereNumber('conversation')->name('destroy');
        Route::patch('/{conversation}/restore', [MessageController::class, 'restore'])->whereNumber('conversation')->name('restore');
});
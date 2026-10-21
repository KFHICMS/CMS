<?php

use App\Http\Controllers\Admin\GuardianController;
<<<<<<< HEAD
=======
use App\Http\Controllers\Admin\ProgramController;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChildController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventAttendanceController;
<<<<<<< HEAD
use App\Http\Controllers\Admin\FollowUpController;
use App\Http\Controllers\QuestionnaireResponseController;
=======
use App\Http\Controllers\Admin\FieldSubmissionController;
use App\Http\Controllers\QuestionnaireResponseController;
use App\Http\Controllers\Admin\EventEnumeratorController;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

use App\Http\Controllers\ChildOfficer\DashboardController;
use App\Http\Controllers\ChildOfficer\ChildController as ChildOfficerChildController;
use App\Http\Controllers\ChildOfficer\GuardianController as ChildOfficerGuardianController;

use App\Http\Controllers\ChildOfficer\QRCodeController as ChildOfficerQRCodeController;
use App\Http\Controllers\ChildOfficer\ChildInformationController as ChildOfficerChildInformationController;
<<<<<<< HEAD
=======
use App\Http\Controllers\FieldOfficer\DashboardController as FieldOfficerDashboardController;
use App\Http\Controllers\FieldOfficer\ActivityController as FieldOfficerActivityController;
use App\Http\Controllers\FieldOfficer\ReportController as FieldOfficerReportController;
use App\Http\Controllers\FieldOfficer\FollowUpController as FieldOfficerFollowUpController;
use App\Http\Controllers\Admin\FollowUpController as AdminFollowUpController;

use App\Http\Controllers\Enumerator\DashboardController as EnumeratorDashboardController;
use App\Http\Controllers\Enumerator\EventController as EnumeratorEventController;
use App\Http\Controllers\Enumerator\AttendanceController as EnumeratorAttendanceController;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
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

<<<<<<< HEAD
Route::middleware('auth')->group(function () {
=======
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::resource('/admin/programs', ProgramController::class)
        ->except('show')
        ->middleware('role:admin');
    
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
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */

<<<<<<< HEAD
    Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('admin.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Reports & Analytics
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])
        ->name('admin.reports.index');

    Route::get('/admin/reports/export/children', [\App\Http\Controllers\Admin\ReportController::class, 'exportChildrenCsv'])
        ->name('admin.reports.export.children');

    Route::get('/admin/reports/export/attendance', [\App\Http\Controllers\Admin\ReportController::class, 'exportAttendanceCsv'])
        ->name('admin.reports.export.attendance');

    Route::get('/admin/reports/export/follow-ups', [\App\Http\Controllers\Admin\ReportController::class, 'exportFollowUpsCsv'])
        ->name('admin.reports.export.followups');
=======
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73


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
<<<<<<< HEAD

    Route::get('/admin/follow-ups', [FollowUpController::class, 'index'])
        ->name('admin.follow-ups.index');

    Route::get('/admin/follow-ups/targets', [FollowUpController::class, 'targets'])
        ->name('admin.follow-ups.targets');

    Route::post('/admin/follow-ups', [FollowUpController::class, 'store'])
        ->name('admin.follow-ups.store');

    Route::patch('/admin/follow-ups/{followUp}', [FollowUpController::class, 'update'])
        ->name('admin.follow-ups.update');

=======
    
    Route::get('/admin/events/{event}/enumerators', [EventEnumeratorController::class, 'edit'])
        ->name('events.enumerators.edit');
    Route::put('/admin/events/{event}/enumerators', [EventEnumeratorController::class, 'update'])
        ->name('events.enumerators.update');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

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

<<<<<<< HEAD
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
=======
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
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73


/*
|--------------------------------------------------------------------------
| Child Officer Area
|--------------------------------------------------------------------------
*/

<<<<<<< HEAD
Route::middleware('auth')->group(function () {
=======
Route::middleware(['auth', 'role:child_officer,admin'])->group(function () {
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

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
<<<<<<< HEAD
    )->name('child_officer.qr.scan');
=======
    )->middleware('role:child_officer')->name('child_officer.qr.scan');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

    Route::post(
        '/child-officer/qr/lookup',
        [ChildOfficerQRCodeController::class, 'lookup']
<<<<<<< HEAD
    )->name('child_officer.qr.lookup');
=======
    )->middleware('role:child_officer')->name('child_officer.qr.lookup');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

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




<<<<<<< HEAD
=======
});

Route::middleware(['auth', 'role:enumerator'])
    ->prefix('enumerator')
    ->name('enumerator.')
    ->group(function () {
        Route::get('/dashboard', [EnumeratorDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/events/{event}', [EnumeratorEventController::class, 'show'])
            ->name('events.show');
        Route::post('/events/{event}/questionnaires/{questionnaire}/respond', [EnumeratorEventController::class, 'startQuestionnaire'])
                ->name('events.questionnaires.start');
    Route::get('/events/{event}/attendance', [EnumeratorAttendanceController::class, 'index'])
            ->name('events.attendance.index');
    Route::post('/events/{event}/attendance/identify', [EnumeratorAttendanceController::class, 'identify'])
            ->name('events.attendance.identify');
    Route::post('/events/{event}/attendance', [EnumeratorAttendanceController::class, 'store'])
            ->name('events.attendance.store');
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
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
});
<?php

/*
|--------------------------------------------------------------------------
| KFHI navigation
|--------------------------------------------------------------------------
| One entry per role. "key" is matched against the page's $active value.
| Items whose route does not exist are skipped automatically.
| Icons are names from resources/views/layouts/partials/icons.blade.php.
*/

return [
    // Turn on once the application has a notification feature.
    'notifications' => false,

    'roles' => [
        'admin' => [
            ['key' => 'dashboard', 'group' => 'Overview', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
            ['key' => 'children', 'group' => 'Records', 'label' => 'Children', 'route' => 'children.index', 'icon' => 'heart'],
            ['key' => 'guardians', 'group' => 'Records', 'label' => 'Guardians', 'route' => 'guardians.index', 'icon' => 'users'],
            ['key' => 'programs', 'group' => 'Programs & Events', 'label' => 'Programs', 'route' => 'programs.index', 'icon' => 'folder'],
            ['key' => 'events', 'group' => 'Programs & Events', 'label' => 'Events', 'route' => 'events.index', 'icon' => 'calendar'],
            ['key' => 'submissions', 'group' => 'Review', 'label' => 'Questionnaire Submissions', 'route' => 'admin_submissions.index', 'icon' => 'check'],
            ['key' => 'field_submissions', 'group' => 'Review', 'label' => 'Field Submissions', 'route' => 'field_submissions.index', 'icon' => 'clipboard'],
            ['key' => 'follow_ups', 'group' => 'Review', 'label' => 'Follow-ups', 'route' => 'admin_follow_ups.index', 'icon' => 'file'],
            ['key' => 'reports', 'group' => 'Review', 'label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'chart'],
            ['key' => 'users', 'group' => 'System', 'label' => 'Users', 'route' => 'users.index', 'icon' => 'user'],
            ['key' => 'audit_logs', 'group' => 'System', 'label' => 'Audit Logs', 'route' => 'admin.audit_logs.index', 'icon' => 'clipboard'],
            ['key' => 'messages', 'group' => 'System', 'label' => 'Messages', 'route' => 'messages.index', 'icon' => 'message'],
            ['key' => 'backups', 'group' => 'System', 'label' => 'Backup & Recovery', 'route' => 'admin.backups.index', 'icon' => 'folder'],
        ],

        'child_officer' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'child_officer.dashboard', 'icon' => 'dashboard'],
            ['key' => 'children', 'label' => 'Children', 'route' => 'child_officer.children.index', 'icon' => 'heart'],
            ['key' => 'guardians', 'label' => 'Guardians', 'route' => 'child_officer.guardians.create', 'icon' => 'users'],
            ['key' => 'scan', 'label' => 'Scan QR', 'route' => 'child_officer.qr.scan', 'icon' => 'scan'],
            ['key' => 'messages', 'group' => 'System', 'label' => 'Messages', 'route' => 'messages.index', 'icon' => 'message'],
        ],

        'field_officer' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'field_officer.dashboard', 'icon' => 'dashboard'],
            ['key' => 'programs', 'label' => 'My Programs', 'route' => 'field_officer.programs.index', 'icon' => 'folder'],
            ['key' => 'activities', 'label' => "Today's Activities", 'route' => 'field_officer.activities.index', 'icon' => 'calendar'],
            ['key' => 'follow-ups', 'label' => 'Follow-ups', 'route' => 'field_officer.follow_ups.index', 'icon' => 'file'],
            ['key' => 'reports', 'label' => 'Reports', 'route' => 'field_officer.reports.index', 'icon' => 'chart'],
            ['key' => 'messages', 'group' => 'System', 'label' => 'Messages', 'route' => 'messages.index', 'icon' => 'message'],
        ], 

        'enumerator' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'enumerator.dashboard', 'icon' => 'dashboard'],
            ['key' => 'submissions', 'label' => 'My Submissions', 'route' => 'enumerator.submissions.index', 'icon' => 'check'],
            ['key' => 'messages', 'group' => 'System', 'label' => 'Messages', 'route' => 'messages.index', 'icon' => 'message'],
        ],
    ],
];
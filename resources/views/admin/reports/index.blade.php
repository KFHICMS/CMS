<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | KFHI-CMS</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #eef7f0;
            color: #294432;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #f4faf5;
            padding: 25px 18px;
            box-shadow: 5px 0 20px rgba(0, 70, 30, 0.08);
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 10;
        }

        .brand {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid #dce9df;
        }

        .brand img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .brand h2 {
            color: #236b3b;
            font-size: 19px;
        }

        .brand p {
            color: #7b897f;
            font-size: 11px;
            margin-top: 4px;
        }

        .nav {
            margin-top: 25px;
        }

        .nav-title {
            font-size: 11px;
            color: #8a978d;
            text-transform: uppercase;
            margin: 0 10px 10px;
            letter-spacing: 1px;
        }

        .nav a {
            display: block;
            text-decoration: none;
            color: #53665a;
            padding: 12px 14px;
            margin-bottom: 6px;
            border-radius: 10px;
            font-size: 14px;
            transition: 0.2s ease;
        }

        .nav a:hover, .nav a.active {
            background: #e0f0e3;
            color: #28733a;
            font-weight: bold;
        }

        .logout-form {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 25px;
        }

        .logout-button {
            width: 100%;
            border: none;
            background: #e8efe9;
            color: #385d43;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }

        .logout-button:hover {
            background: #dce7dd;
        }

        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 {
            color: #215e34;
            font-size: 26px;
        }

        .topbar p {
            color: #798b7e;
            font-size: 13px;
            margin-top: 4px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #dce9df;
            padding-bottom: 5px;
        }

        .tab-btn {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px 8px 0 0;
            font-size: 14px;
            font-weight: 600;
            color: #5c7463;
            background: #e1efe4;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background: #25783f;
            color: #ffffff;
        }

        /* Metric cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #f4faf5;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 4px 4px 14px rgba(0, 70, 30, 0.06);
            border-left: 4px solid #2e8b49;
        }

        .stat-label {
            color: #7c8e81;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stat-val {
            color: #1f5d34;
            font-size: 26px;
            font-weight: bold;
            margin: 8px 0;
        }

        .stat-sub {
            color: #8b9d90;
            font-size: 12px;
        }

        /* Report Cards & Panels */
        .card {
            background: #f4faf5;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 5px 5px 16px rgba(0, 70, 30, 0.07);
            margin-bottom: 25px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .card-header h2 {
            font-size: 18px;
            color: #215e34;
        }

        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #267a41;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-export:hover {
            background: #1e6334;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background: #e3f0e6;
            color: #295436;
            text-align: left;
            padding: 12px;
            font-weight: 600;
            border-bottom: 2px solid #cfe0d3;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e1eee4;
            color: #3b5042;
        }

        tr:hover td {
            background: #ebf5ee;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background: #d3f3db; color: #1e7033; }
        .badge-warning { background: #fef0cd; color: #8a6500; }
        .badge-danger { background: #fcdbd9; color: #9a2820; }
        .badge-info { background: #dff0fe; color: #1c5b88; }

        /* Filter Form */
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 20px;
            background: #e8f4eb;
            padding: 16px;
            border-radius: 12px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #4a6752;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #b8d4be;
            border-radius: 6px;
            background: #ffffff;
            font-size: 13px;
            color: #25442e;
        }

        .btn-filter {
            background: #25783f;
            color: #ffffff;
            padding: 9px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-reset {
            background: #cbdcd0;
            color: #385541;
            padding: 9px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .grid-2col {
                grid-template-columns: 1fr;
            }
            .sidebar {
                width: 200px;
            }
            .main {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar Navigation -->
    @include('admin.partials.sidebar')

    <!-- Main Body Area -->
    <main class="main">
        <div class="topbar">
            <div>
                <h1>Report Management & Analytics</h1>
                <p>Detailed performance summaries, demographic analytics, event outcomes, and case follow-ups.</p>
            </div>
            <div style="text-align: right;">
                <strong style="color: #205c33; font-size: 15px;">{{ auth()->user()->name }}</strong><br>
                <span style="color: #849488; font-size: 12px;">Administrator</span>
            </div>
        </div>

        <!-- Section Navigation Tabs -->
        <div class="tabs">
            <a href="{{ route('admin.reports.index', ['tab' => 'demographics']) }}" class="tab-btn {{ $activeTab === 'demographics' ? 'active' : '' }}">
                 Demographics & Enrolment
            </a>
            <a href="{{ route('admin.reports.index', ['tab' => 'attendance']) }}" class="tab-btn {{ $activeTab === 'attendance' ? 'active' : '' }}">
                 Event Attendance
            </a>
            <a href="{{ route('admin.reports.index', ['tab' => 'questionnaires']) }}" class="tab-btn {{ $activeTab === 'questionnaires' ? 'active' : '' }}">
                 Impact & Questionnaires
            </a>
            <a href="{{ route('admin.reports.index', ['tab' => 'followups']) }}" class="tab-btn {{ $activeTab === 'followups' ? 'active' : '' }}">
                 Case Follow-ups
            </a>
        </div>

        <!-- TAB 1: DEMOGRAPHICS -->
        @if($activeTab === 'demographics')
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Registered Children</div>
                    <div class="stat-val">{{ number_format($totalChildren) }}</div>
                    <div class="stat-sub">Active: {{ $activeChildren }} | Inactive: {{ $inactiveChildren }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Guardians Registered</div>
                    <div class="stat-val">{{ number_format($totalGuardians) }}</div>
                    <div class="stat-sub">{{ $guardiansWithPhone }} with phone contact</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Gender Ratio</div>
                    <div class="stat-val">
                        M: {{ $genderDistribution['male'] ?? ($genderDistribution['Male'] ?? 0) }} /
                        F: {{ $genderDistribution['female'] ?? ($genderDistribution['Female'] ?? 0) }}
                    </div>
                    <div class="stat-sub">Recorded across all divisions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Coverage Districts</div>
                    <div class="stat-val">{{ count($districtDistribution) }}</div>
                    <div class="stat-sub">Districts actively served</div>
                </div>
            </div>

            <div class="grid-2col">
                <div class="card">
                    <div class="card-header">
                        <h2>Top Districts Distribution</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>District</th>
                                <th>Children Enrolled</th>
                                <th>% Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($districtDistribution as $dist => $count)
                                <tr>
                                    <td><strong>{{ $dist }}</strong></td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $totalChildren > 0 ? round(($count / $totalChildren) * 100, 1) : 0 }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align: center; color: #888;">No district data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>GN Division Breakdown</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>GN Division</th>
                                <th>Children Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gnDivisionDistribution as $division => $count)
                                <tr>
                                    <td><strong>{{ $division }}</strong></td>
                                    <td>{{ $count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="text-align: center; color: #888;">No GN division records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Filterable Children Demographics List & Export -->
            <div class="card">
                <div class="card-header">
                    <h2>Enrolled Children Report Registry</h2>
                    <a href="{{ route('admin.reports.export.children', request()->query()) }}" class="btn-export">
                         Export CSV
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.reports.index') }}" class="filter-form">
                    <input type="hidden" name="tab" value="demographics">

                    <div class="form-group">
                        <label>District</label>
                        <select name="district" class="form-control">
                            <option value="">All Districts</option>
                            @foreach($availableDistricts as $d)
                                <option value="{{ $d }}" {{ request('district') == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">All Genders</option>
                            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>GN Division</label>
                        <input type="text" name="gn_division" value="{{ request('gn_division') }}" placeholder="Search GN..." class="form-control">
                    </div>

                    <button type="submit" class="btn-filter">Apply Filter</button>
                    <a href="{{ route('admin.reports.index', ['tab' => 'demographics']) }}" class="btn-reset">Reset</a>
                </form>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Child Code</th>
                                <th>Full Name</th>
                                <th>Gender</th>
                                <th>District / GN Division</th>
                                <th>School & Grade</th>
                                <th>Primary Guardian</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredChildren as $child)
                                <tr>
                                    <td><strong>{{ $child->child_code }}</strong></td>
                                    <td>{{ $child->first_name }} {{ $child->last_name }}</td>
                                    <td>{{ ucfirst($child->gender ?? '-') }}</td>
                                    <td>{{ $child->district ?? '-' }} / {{ $child->gn_division ?? '-' }}</td>
                                    <td>{{ $child->school_name ?? '-' }} ({{ $child->grade ?? 'N/A' }})</td>
                                    <td>
                                        @php
                                            $pg = $child->guardians->firstWhere('is_primary', true) ?? $child->guardians->first();
                                        @endphp
                                        {{ $pg ? $pg->full_name : 'No guardian recorded' }}
                                        @if($pg && $pg->phone)<br><small style="color: #666;">📞 {{ $pg->phone }}</small>@endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $child->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($child->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" style="text-align: center; color: #888;">No children found matching the filter criteria.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 15px;">
                    {{ $filteredChildren->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

        <!-- TAB 2: ATTENDANCE -->
        @if($activeTab === 'attendance')
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Events Held</div>
                    <div class="stat-val">{{ number_format($totalEvents) }}</div>
                    <div class="stat-sub">Programs & field activities</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Attendance Marks</div>
                    <div class="stat-val">{{ number_format($totalAttendanceMarks) }}</div>
                    <div class="stat-sub">Total check-in records</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">QR vs Manual Check-in</div>
                    <div class="stat-val">
                        QR: {{ $attendanceByMethod['qr'] ?? ($attendanceByMethod['QR'] ?? 0) }} /
                        Man: {{ $attendanceByMethod['manual'] ?? 0 }}
                    </div>
                    <div class="stat-sub">Digital ID scan adoption</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Events Participation Performance</h2>
                    <a href="{{ route('admin.reports.export.attendance') }}" class="btn-export">
                         Export All Attendance CSV
                    </a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Event Code</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Attendance Count</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eventsSummary as $evt)
                                <tr>
                                    <td><strong>{{ $evt->event_code }}</strong></td>
                                    <td>{{ $evt->title }}</td>
                                    <td>{{ $evt->event_date ? $evt->event_date->format('Y-m-d') : 'N/A' }}</td>
                                    <td>{{ $evt->location ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 13px;">
                                             {{ $evt->attendances_count }} Attendees
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $evt->status === 'completed' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($evt->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('events.attendance.index', $evt->id) }}" style="color: #236b3b; font-weight: bold; text-decoration: none;">
                                            View Register &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" style="text-align: center; color: #888;">No events recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 3: QUESTIONNAIRES & IMPACT -->
        @if($activeTab === 'questionnaires')
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Active Questionnaires</div>
                    <div class="stat-val">{{ number_format($totalQuestionnaires) }}</div>
                    <div class="stat-sub">Surveys & feedback forms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Completed Responses</div>
                    <div class="stat-val">{{ number_format($totalResponses) }}</div>
                    <div class="stat-sub">Submissions from children & parents</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Questionnaire Participation & Assessments</h2>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Questionnaire Title</th>
                                <th>Linked Event</th>
                                <th>Responses Received</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($questionnairesWithResponseCount as $q)
                                <tr>
                                    <td><strong>{{ $q->title }}</strong></td>
                                    <td>{{ $q->event ? $q->event->title : 'General / Standalone' }}</td>
                                    <td>
                                        <span class="badge badge-success" style="font-size: 13px;">
                                            {{ $q->responses_count }} Responses
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $q->status === 'published' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($q->status ?? 'Draft') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align: center; color: #888;">No questionnaires created yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 4: CASE FOLLOW-UPS -->
        @if($activeTab === 'followups')
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Follow-ups</div>
                    <div class="stat-val">{{ number_format($totalFollowUps) }}</div>
                    <div class="stat-sub">Cases identified for intervention</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending / In Progress</div>
                    <div class="stat-val">{{ ($followUpsByStatus['pending'] ?? 0) + ($followUpsByStatus['in_progress'] ?? 0) }}</div>
                    <div class="stat-sub">Active open cases</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Resolved / Completed</div>
                    <div class="stat-val">{{ $followUpsByStatus['completed'] ?? 0 }}</div>
                    <div class="stat-sub">Closed cases</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">High Priority Cases</div>
                    <div class="stat-val">{{ $followUpsByPriority['high'] ?? 0 }}</div>
                    <div class="stat-sub">Urgent attention needed</div>
                </div>
            </div>

            <div class="grid-2col">
                <div class="card">
                    <div class="card-header">
                        <h2>Officer Caseload Breakdown</h2>
                        <a href="{{ route('admin.reports.export.followups') }}" class="btn-export">
                             Export Cases CSV
                        </a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Officer Name</th>
                                <th>Assigned</th>
                                <th>Pending</th>
                                <th>Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($officerCaseload as $officer)
                                <tr>
                                    <td><strong>{{ $officer->name }}</strong></td>
                                    <td>{{ $officer->follow_ups_count }}</td>
                                    <td><span class="badge badge-warning">{{ $officer->pending_follow_ups_count }}</span></td>
                                    <td><span class="badge badge-success">{{ $officer->completed_follow_ups_count }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align: center; color: #888;">No officer assignments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Cases by Category</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total Cases</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($followUpsByCategory as $cat => $cnt)
                                <tr>
                                    <td><strong>{{ ucfirst($cat) }}</strong></td>
                                    <td>{{ $cnt }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="text-align: center; color: #888;">No categories recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </main>

</div>

</body>
</html>

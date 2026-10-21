<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Child Officer Dashboard - KFHI CMS</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f8f5;
            color: #1f2937;
          vb}

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 25px 18px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 35px;
            padding-left: 10px;
        }

        .nav-title {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            margin: 25px 10px 10px;
            letter-spacing: 1px;
        }

        .nav-link {
            display: block;
            text-decoration: none;
            color: #4b5563;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        /* Main */
        .main-content {
            flex: 1;
            padding: 25px 35px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 25px;
            border-bottom: 1px solid #e5e7eb;
        }

        .header h1 {
            font-size: 22px;
        }

        .officer-name {
            color: #2e7d32;
            font-weight: 600;
        }

        .welcome {
            margin-top: 35px;
        }

        .welcome h2 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #6b7280;
        }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: #ffffff;
            padding: 22px;
            border-radius: 15px;
            border: 1px solid #e5e7eb;
        }

        .stat-title {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 700;
            color: #2e7d32;
        }

        /* Quick Actions */
        .section {
            margin-top: 35px;
        }

        .section h3 {
            margin-bottom: 15px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .action-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            padding: 25px;
            text-decoration: none;
            color: #1f2937;
        }

        .action-card:hover {
            border-color: #81c784;
        }

        .action-card strong {
            display: block;
            margin-bottom: 8px;
            color: #2e7d32;
        }

        .action-card span {
            font-size: 13px;
            color: #6b7280;
        }

        /* Recent Children */
        .table-container {
            background: #ffffff;
            border-radius: 15px;
            border: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        th {
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
        }

        .view-btn {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .dashboard-container {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="logo">
            KFHI-CMS
        </div>

        <a href="{{ route('child_officer.dashboard') }}"
   class="nav-link active">
    Dashboard
</a>
        <div class="nav-title">
            CHILD MANAGEMENT
        </div>

        <a href="{{ route('child_officer.children.index') }}"
   class="nav-link">
    Children
</a>
<a href="{{ route('child_officer.children.create') }}"
           class="nav-link active">
            Register Child
        </a>
<a href="{{ route('child_officer.guardians.create') }}"
   class="nav-link">
    Guardians
</a>
        <div class="nav-title">
            IDENTIFICATION
        </div>

        <a href="{{ route('child_officer.qr.scan') }}" class="nav-link">
    Scan QR
</a>
        <a href="#" class="nav-link">
            Digital IDs
        </a>

        <div class="nav-title">
            ACTIVITIES
        </div>

        <a href="#" class="nav-link">
            Follow-ups
        </a>

        <div class="nav-title">
            REPORTS
        </div>

        <a href="#" class="nav-link">
            Child Reports
        </a>

        <div class="nav-title">
            ACCOUNT
        </div>

        <a href="#" class="nav-link">
            Profile
        </a>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit"
                    class="nav-link"
                    style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
                Logout
            </button>
        </form>

    </aside>


    <!-- Main Content -->
    <main class="main-content">

        <!-- Header -->
        <header class="header">

            <h1>Child Officer Dashboard</h1>

            <div>
                Child Officer
                <span class="officer-name">
                    {{ auth()->user()->name }}
                </span>
            </div>

        </header>


        <!-- Welcome -->
        <section class="welcome">

            <h2>
                Good Morning, Child Officer
            </h2>

            <p>
                Manage and monitor registered children.
            </p>

        </section>


        <!-- Statistics -->
        <section class="stats">

            <div class="stat-card">
                <div class="stat-title">
                    Total Children
                </div>

                <div class="stat-value">
                    {{ $totalChildren }}
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-title">
                    Active Children
                </div>

                <div class="stat-value">
                    {{ $activeChildren }}
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-title">
                    Inactive Children
                </div>

                <div class="stat-value">
                    {{ $inactiveChildren }}
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-title">
                    Follow-ups
                </div>

                <div class="stat-value">
                    {{ $followUps }}
                </div>
            </div>

        </section>


        <!-- Quick Actions -->
        <section class="section">

            <h3>
                Quick Actions
            </h3>

            <div class="quick-actions">

            <a href="{{ route('child_officer.children.create') }}"
            class="action-card">

                    <strong>
                        Register Child
                    </strong>

                    <span>
                        Register a new child in the system.
                    </span>

                </a>


                <a href="{{ route('child_officer.qr.scan') }}" class="action-card">

                    <strong>
                        Scan QR
                    </strong>

                    <span>
                        Scan a child's secure QR code.
                    </span>

                </a>


                <a href="{{ route('child_officer.children.index') }}"
                class="action-card">

                    <strong>
                        Search Child
                    </strong>

                    <span>
                        Search and manage registered children.
                    </span>

                </a>

            </div>

        </section>


        <!-- Recent Children -->
        <section class="section">

            <h3>
                Recent Children
            </h3>

            <div class="table-container">

                <table>

                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>District</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($recentChildren as $child)

                            <tr>

                                <td>
                                    {{ $child->child_code }}
                                </td>

                                <td>
                                    {{ $child->first_name }}
                                    {{ $child->middle_name }}
                                    {{ $child->last_name }}
                                </td>

                                <td>
                                    {{ $child->district ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ ucfirst($child->status) }}
                                </td>

                                <td>
                                <a href="{{ route('child_officer.children.show', $child) }}"
   class="view-btn">
    View
</a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" style="text-align:center;">
                                    No children registered yet.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>
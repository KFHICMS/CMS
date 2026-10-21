<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Guardian - KFHI-CMS</title>

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
        }

        .page-container {
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

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #2e7d32;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Form */

        .form-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 30px;
            margin-top: 20px;
            max-width: 850px;
        }

        .form-title {
            font-size: 20px;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .form-description {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .child-box {
            background: #f1f8f3;
            border: 1px solid #c8e6c9;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .child-box-title {
            color: #2e7d32;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .child-box-info {
            color: #4b5563;
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
            font-family: Arial, sans-serif;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.08);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 5px;
        }

        .checkbox-group input {
            width: auto;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
        }

        .error-message {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-list {
            margin-left: 18px;
            margin-top: 8px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2e7d32;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #256b29;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #4b5563;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        /* Responsive */

        @media (max-width: 700px) {
            .page-container {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

<div class="page-container">

    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="logo">
            KFHI-CMS
        </div>

        <a href="{{ route('child_officer.dashboard') }}"
           class="nav-link">
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
           class="nav-link">
            Register Child
        </a>

        <a href="{{ route('child_officer.guardians.create') }}"
           class="nav-link active">
            Guardians
        </a>

        <div class="nav-title">
            IDENTIFICATION
        </div>

        <a href="{{ route('child_officer.qr.scan') }}"
           class="nav-link">
            Scan QR
        </a>

        <a href="#"
           class="nav-link">
            Digital IDs
        </a>

        <div class="nav-title">
            ACTIVITIES
        </div>

        <a href="#"
           class="nav-link">
            Follow-ups
        </a>

        <div class="nav-title">
            REPORTS
        </div>

        <a href="#"
           class="nav-link">
            Child Reports
        </a>

        <div class="nav-title">
            ACCOUNT
        </div>

        <a href="#"
           class="nav-link">
            Profile
        </a>

        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button
                type="submit"
                class="nav-link"
                style="border:none;background:none;width:100%;text-align:left;cursor:pointer;"
            >
                Logout
            </button>

        </form>

    </aside>


    <!-- Main -->

    <main class="main-content">

        <header class="header">

            <h1>
                Edit Guardian
            </h1>

            <div>
                Child Officer
                <span class="officer-name">
                    {{ auth()->user()->name }}
                </span>
            </div>

        </header>


        <a
            href="{{ route('child_officer.children.show', $guardian->child_id) }}"
            class="back-link"
        >
            ← Back to Child Details
        </a>


        <div class="form-card">

            <div class="form-title">
                Edit Guardian Information
            </div>

            <div class="form-description">
                Update the guardian information for this child.
            </div>


            <!-- Validation Errors -->

            @if($errors->any())

                <div class="error-message">

                    <strong>
                        Please correct the following errors:
                    </strong>

                    <ul class="error-list">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- Child Information -->

            <div class="child-box">

                <div class="child-box-title">
                    Child
                </div>

                <div class="child-box-info">

                    <strong>
                        {{ $guardian->child->child_code }}
                    </strong>

                    -
                    {{ $guardian->child->first_name }}

                    @if($guardian->child->middle_name)
                        {{ $guardian->child->middle_name }}
                    @endif

                    {{ $guardian->child->last_name }}

                    @if($guardian->child->district)
                        | {{ $guardian->child->district }}
                    @endif

                </div>

            </div>


            <!-- Form -->

            <form
                action="{{ route('child_officer.guardians.update', $guardian) }}"
                method="POST"
            >

                @csrf

                @method('PUT')


                <div class="form-grid">

                    <!-- Full Name -->

                    <div class="form-group">

                        <label for="full_name">
                            Guardian Full Name *
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="{{ old('full_name', $guardian->full_name) }}"
                            required
                        >

                    </div>


                    <!-- Relationship -->

                    <div class="form-group">

                        <label for="relationship">
                            Relationship *
                        </label>

                        <input
                            type="text"
                            id="relationship"
                            name="relationship"
                            value="{{ old('relationship', $guardian->relationship) }}"
                            required
                        >

                    </div>


                    <!-- Phone -->

                    <div class="form-group">

                        <label for="phone">
                            Phone *
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $guardian->phone) }}"
                            required
                        >

                    </div>


                    <!-- Alternate Phone -->

                    <div class="form-group">

                        <label for="alternate_phone">
                            Alternate Phone
                        </label>

                        <input
                            type="text"
                            id="alternate_phone"
                            name="alternate_phone"
                            value="{{ old('alternate_phone', $guardian->alternate_phone) }}"
                        >

                    </div>


                    <!-- Occupation -->

                    <div class="form-group">

                        <label for="occupation">
                            Occupation
                        </label>

                        <input
                            type="text"
                            id="occupation"
                            name="occupation"
                            value="{{ old('occupation', $guardian->occupation) }}"
                        >

                    </div>


                    <!-- Guardian Type -->

                    <div class="form-group">

                        <label for="guardian_type">
                            Guardian Type
                        </label>

                        <input
                            type="text"
                            id="guardian_type"
                            name="guardian_type"
                            value="{{ old('guardian_type', $guardian->guardian_type) }}"
                            placeholder="Example: Parent, Relative, Foster Parent"
                        >

                    </div>


                    <!-- Address -->

                    <div class="form-group full">

                        <label for="address">
                            Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                        >{{ old('address', $guardian->address) }}</textarea>

                    </div>


                    <!-- Primary Guardian -->

                    <div class="form-group full">

                        <div class="checkbox-group">

                            <input
                                type="checkbox"
                                id="is_primary"
                                name="is_primary"
                                value="1"
                                {{ old('is_primary', $guardian->is_primary) ? 'checked' : '' }}
                            >

                            <label for="is_primary">
                                Set as Primary Guardian
                            </label>

                        </div>

                    </div>

                </div>


                <!-- Actions -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Guardian
                    </button>

                    <a
                        href="{{ route('child_officer.children.show', $guardian->child_id) }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>
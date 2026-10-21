<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Child - KFHI-CMS</title>

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

        .logout-btn {
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
        }

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
            margin: 25px 0 20px;
            color: #2e7d32;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 30px;
        }

        .form-intro {
            margin-bottom: 25px;
        }

        .form-intro h2 {
            font-size: 20px;
            margin-bottom: 7px;
        }

        .form-intro p {
            color: #6b7280;
            font-size: 14px;
        }

        .section-title {
            color: #2e7d32;
            font-size: 16px;
            font-weight: 700;
            padding-bottom: 10px;
            margin: 25px 0 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 20px;
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
            margin-bottom: 7px;
            color: #374151;
        }

        label span {
            color: #d32f2f;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 11px 13px;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.10);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .current-photo {
            margin-bottom: 10px;
        }

        .current-photo img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e8f5e9;
        }

        .photo-placeholder {
            width: 90px;
            height: 90px;
            border-radius: 12px;
            background: #e8f5e9;
            color: #2e7d32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
        }

        .file-input {
            padding: 9px;
        }

        .help-text {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 5px;
        }

        .error-box {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #b71c1c;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-box ul {
            padding-left: 20px;
            margin-top: 7px;
        }

        .button-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-cancel {
            background: #f3f4f6;
            color: #4b5563;
        }

        .btn-save {
            background: #2e7d32;
            color: #ffffff;
        }

        .btn-save:hover {
            background: #256b29;
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 210px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }
        }

        @media (max-width: 650px) {
            .page-container {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .button-row {
                flex-direction: column;
            }

            .btn {
                text-align: center;
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
           class="nav-link active">
            Children
        </a>

        <a href="{{ route('child_officer.children.create') }}"
           class="nav-link">
            Register Child
        </a>

        <a href="#"
           class="nav-link">
            Guardians
        </a>

        <div class="nav-title">
            IDENTIFICATION
        </div>

        <a href="#" class="nav-link">
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

            <button type="submit" class="nav-link logout-btn">
                Logout
            </button>

        </form>

    </aside>


    <!-- Main -->

    <main class="main-content">

        <header class="header">

            <h1>
                Edit Child
            </h1>

            <div>
                Child Officer
                <span class="officer-name">
                    {{ auth()->user()->name }}
                </span>
            </div>

        </header>


        <!-- Back Button -->

        <a href="{{ route('child_officer.children.show', $child) }}"
           class="back-link">

            ← Back to Child Details

        </a>


        <div class="form-card">

            <div class="form-intro">

                <h2>
                    Update Child Information
                </h2>

                <p>
                    Update the child's information carefully.
                    Required fields are marked with <strong>*</strong>.
                </p>

            </div>


            @if($errors->any())

                <div class="error-box">

                    <strong>
                        Please correct the following errors:
                    </strong>

                    <ul>

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <form
                action="{{ route('child_officer.children.update', $child) }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf
                @method('PUT')


                <!-- Basic Information -->

                <div class="section-title">
                    Basic Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Child Code <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="child_code"
                            value="{{ old('child_code', $child->child_code) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Gender <span>*</span>
                        </label>

                        <select name="gender" required>

                            <option value="">
                                Select Gender
                            </option>

                            <option value="Male"
                                {{ old('gender', $child->gender) === 'Male' ? 'selected' : '' }}>
                                Male
                            </option>

                            <option value="Female"
                                {{ old('gender', $child->gender) === 'Female' ? 'selected' : '' }}>
                                Female
                            </option>

                            <option value="Other"
                                {{ old('gender', $child->gender) === 'Other' ? 'selected' : '' }}>
                                Other
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            First Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $child->first_name) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Middle Name
                        </label>

                        <input
                            type="text"
                            name="middle_name"
                            value="{{ old('middle_name', $child->middle_name) }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Last Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $child->last_name) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Date of Birth <span>*</span>
                        </label>

                        <input
                            type="date"
                            name="date_of_birth"
                            value="{{ old(
                                'date_of_birth',
                                $child->date_of_birth
                                    ? $child->date_of_birth->format('Y-m-d')
                                    : ''
                            ) }}"
                            required
                        >

                    </div>

                </div>


                <!-- Education -->

                <div class="section-title">
                    Education Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            School Name
                        </label>

                        <input
                            type="text"
                            name="school_name"
                            value="{{ old('school_name', $child->school_name) }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Grade
                        </label>

                        <input
                            type="text"
                            name="grade"
                            value="{{ old('grade', $child->grade) }}"
                        >

                    </div>

                </div>


                <!-- Location -->

                <div class="section-title">
                    Location Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            District
                        </label>

                        <input
                            type="text"
                            name="district"
                            value="{{ old('district', $child->district) }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            GN Division
                        </label>

                        <input
                            type="text"
                            name="gn_division"
                            value="{{ old('gn_division', $child->gn_division) }}"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                        >{{ old('address', $child->address) }}</textarea>

                    </div>

                </div>


                <!-- Additional Information -->

                <div class="section-title">
                    Additional Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Current Profile Photo
                        </label>

                        <div class="current-photo">

                            @if($child->profile_photo)

                                <img
                                    src="{{ asset('storage/' . $child->profile_photo) }}"
                                    alt="Current Child Photo"
                                >

                            @else

                                <div class="photo-placeholder">

                                    {{ strtoupper(
                                        substr($child->first_name, 0, 1)
                                    ) }}

                                </div>

                            @endif

                        </div>


                        <label>
                            Replace Profile Photo
                        </label>

                        <input
                            type="file"
                            name="profile_photo"
                            class="file-input"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <div class="help-text">
                            JPG, JPEG, PNG or WEBP. Maximum 2MB.
                        </div>

                    </div>


                    <div class="form-group">

                        <label>
                            Status <span>*</span>
                        </label>

                        <select name="status" required>

                            <option value="active"
                                {{ old('status', $child->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive"
                                {{ old('status', $child->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>

                    </div>


                    <div class="form-group full">

                        <label>
                            Notes
                        </label>

                        <textarea
                            name="notes"
                        >{{ old('notes', $child->notes) }}</textarea>

                    </div>

                </div>


                <!-- Buttons -->

<<<<<<< HEAD
                @include('admin.children._custom_fields')

=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
                <div class="button-row">

                    <a
                        href="{{ route('child_officer.children.show', $child) }}"
                        class="btn btn-cancel"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>
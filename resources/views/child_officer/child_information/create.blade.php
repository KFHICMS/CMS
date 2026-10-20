<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Information | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f8f4;
            color: #26352a;
            min-height: 100vh;
        }

        .header {
            background: #ffffff;
            border-bottom: 1px solid #dce8df;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand-text h1 {
            font-size: 20px;
            color: #2e7d32;
            margin-bottom: 3px;
        }

        .brand-text p {
            font-size: 12px;
            color: #718076;
        }

        .user-info {
            text-align: right;
        }

        .user-info strong {
            display: block;
            font-size: 14px;
            color: #2e7d32;
        }

        .user-info span {
            font-size: 12px;
            color: #718076;
        }

        .container {
            width: min(900px, calc(100% - 40px));
            margin: 35px auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            color: #2e7d32;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .page-title {
            margin-bottom: 22px;
        }

        .page-title h2 {
            font-size: 28px;
            color: #26352a;
            margin-bottom: 6px;
        }

        .page-title p {
            color: #718076;
            font-size: 14px;
        }

        .child-card {
            background: #ffffff;
            border: 1px solid #dce8df;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .child-details h3 {
            color: #2e7d32;
            font-size: 18px;
            margin-bottom: 7px;
        }

        .child-details p {
            font-size: 13px;
            color: #68756c;
            margin-bottom: 4px;
        }

        .child-code {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #dce8df;
            border-radius: 14px;
            padding: 28px;
        }

        .form-card h3 {
            font-size: 18px;
            color: #26352a;
            margin-bottom: 22px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #36463a;
            margin-bottom: 8px;
        }

        .required {
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            border: 1px solid #cfdcd2;
            border-radius: 9px;
            padding: 12px 14px;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
            color: #26352a;
            outline: none;
            transition: 0.2s ease;
        }

        .form-control:focus {
            border-color: #66a56b;
            box-shadow: 0 0 0 3px rgba(102, 165, 107, 0.12);
        }

        textarea.form-control {
            min-height: 140px;
            resize: vertical;
        }

        select.form-control {
            cursor: pointer;
        }

        .help-text {
            margin-top: 6px;
            font-size: 12px;
            color: #7b887e;
        }

        .error-box {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            border-radius: 9px;
            padding: 14px 16px;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .error-box ul {
            margin-left: 18px;
            margin-top: 6px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #e5ece7;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }

        .btn-secondary {
            background: #eef3ef;
            color: #536158;
        }

        .btn-secondary:hover {
            background: #e2eae4;
        }

        .btn-primary {
            background: #2e7d32;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #256b29;
        }

        @media (max-width: 650px) {
            .header {
                padding: 15px 18px;
            }

            .user-info {
                display: none;
            }

            .container {
                width: min(100% - 24px, 900px);
                margin: 25px auto;
            }

            .page-title h2 {
                font-size: 24px;
            }

            .child-card {
                align-items: flex-start;
                flex-direction: column;
            }

            .form-card {
                padding: 20px;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="header">

        <div class="brand">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="KFHI Logo"
            >

            <div class="brand-text">
                <h1>KFHI-CMS</h1>
                <p>Child Digital Identification & Information Management System</p>
            </div>
        </div>

        <div class="user-info">
            <strong>{{ auth()->user()->name ?? 'Child Officer' }}</strong>
            <span>Child Officer</span>
        </div>

    </header>


    <!-- Main Content -->
    <main class="container">

        <!-- Back -->
        <a
            href="{{ route('child_officer.children.show', $child) }}"
            class="back-link"
        >
            ← Back to Child Details
        </a>


        <!-- Page Title -->
        <div class="page-title">
            <h2>Add New Information</h2>

            <p>
                Add a new information record for this child.
            </p>
        </div>


        <!-- Child Information -->
        <div class="child-card">

            <div class="child-details">

                <h3>
                    {{ trim(
                        $child->first_name . ' ' .
                        ($child->middle_name ?? '') . ' ' .
                        $child->last_name
                    ) }}
                </h3>

                <p>
                    <strong>District:</strong>
                    {{ $child->district ?: 'Not provided' }}
                </p>

                <p>
                    <strong>GN Division:</strong>
                    {{ $child->gn_division ?: 'Not provided' }}
                </p>

            </div>

            <div class="child-code">
                {{ $child->child_code }}
            </div>

        </div>


        <!-- Validation Errors -->
        @if ($errors->any())

            <div class="error-box">

                <strong>Please correct the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        <!-- Form -->
        <div class="form-card">

            <h3>Information Details</h3>

            <form
                action="{{ route('child_officer.child_information.store', $child) }}"
                method="POST"
            >

                @csrf


                <!-- Information Type -->
                <div class="form-group">

                    <label for="information_type">
                        Information Type
                        <span class="required">*</span>
                    </label>

                    <select
                        name="information_type"
                        id="information_type"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select information type
                        </option>

                        <option
                            value="Education"
                            {{ old('information_type') === 'Education' ? 'selected' : '' }}
                        >
                            Education
                        </option>

                        <option
                            value="Family"
                            {{ old('information_type') === 'Family' ? 'selected' : '' }}
                        >
                            Family
                        </option>

                        <option
                            value="Program"
                            {{ old('information_type') === 'Program' ? 'selected' : '' }}
                        >
                            Program
                        </option>

                        <option
                            value="Important Note"
                            {{ old('information_type') === 'Important Note' ? 'selected' : '' }}
                        >
                            Important Note
                        </option>

                        <option
                            value="Other"
                            {{ old('information_type') === 'Other' ? 'selected' : '' }}
                        >
                            Other
                        </option>

                    </select>

                </div>


                <!-- Title -->
                <div class="form-group">

                    <label for="title">
                        Title
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control"
                        value="{{ old('title') }}"
                        placeholder="Enter a short title"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- Description -->
                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        placeholder="Enter the additional information..."
                    >{{ old('description') }}</textarea>

                    <div class="help-text">
                        Add relevant information that should be stored separately
                        from the child's main profile.
                    </div>

                </div>


                <!-- Information Date -->
                <div class="form-group">

                    <label for="information_date">
                        Information Date
                    </label>

                    <input
                        type="date"
                        name="information_date"
                        id="information_date"
                        class="form-control"
                        value="{{ old('information_date', date('Y-m-d')) }}"
                    >

                </div>


                <!-- Actions -->
                <div class="actions">

                    <a
                        href="{{ route('child_officer.children.show', $child) }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Information
                    </button>

                </div>

            </form>

        </div>

    </main>

</body>
</html>
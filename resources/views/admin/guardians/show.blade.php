<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Guardian Details - KFHI CMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f8f4;
            color: #294536;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h1 {
            margin: 0;
            color: #245c3a;
            font-size: 28px;
        }

        .topbar p {
            margin: 7px 0 0;
            color: #718078;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 11px 17px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-back {
            background: #eef5f0;
            color: #31533f;
        }

        .btn-edit {
            background: #2e7d4f;
            color: #ffffff;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(35, 80, 50, 0.07);
            margin-bottom: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 25px;
            border-bottom: 1px solid #edf1ee;
        }

        .profile-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e2f1e7;
            color: #2e7d4f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .profile-header h2 {
            margin: 0 0 7px;
            color: #294536;
            font-size: 24px;
        }

        .profile-header p {
            margin: 0;
            color: #718078;
        }

        .badge {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 11px;
            border-radius: 20px;
            background: #e4f4e8;
            color: #2e7d4f;
            font-size: 12px;
            font-weight: 600;
        }

        .section {
            margin-top: 25px;
        }

        .section h3 {
            margin: 0 0 15px;
            color: #31533f;
            font-size: 17px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .detail-item {
            padding: 15px;
            background: #f7faf8;
            border-radius: 12px;
        }

        .detail-label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            color: #7a8981;
            font-weight: 600;
            text-transform: uppercase;
        }

        .detail-value {
            color: #294536;
            font-weight: 600;
        }

        .child-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 18px;
            background: #eef7f1;
            border: 1px solid #dcece1;
            border-radius: 14px;
        }

        .child-info h4 {
            margin: 0 0 5px;
            color: #294536;
            font-size: 17px;
        }

        .child-info p {
            margin: 0;
            color: #718078;
            font-size: 14px;
        }

        .btn-child {
            background: #ffffff;
            color: #2e7d4f;
            border: 1px solid #cfe1d4;
        }

        .empty-child {
            padding: 18px;
            background: #fff5f5;
            border-radius: 12px;
            color: #b33a3a;
        }

        @media (max-width: 700px) {

            .container {
                padding: 18px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .profile-header {
                align-items: flex-start;
            }

            .child-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-child {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="topbar">

        <div>
            <h1>Guardian Details</h1>
            <p>View complete guardian information.</p>
        </div>

        <div class="actions">

            <a
                href="{{ route('guardians.index') }}"
                class="btn btn-back"
            >
                ← Back
            </a>

            <a
                href="{{ route('guardians.edit', $guardian) }}"
                class="btn btn-edit"
            >
                Edit Guardian
            </a>

        </div>

    </div>


    {{-- Guardian Profile --}}
    <div class="profile-card">

        <div class="profile-header">

            <div class="profile-icon">
                {{ strtoupper(substr($guardian->full_name, 0, 1)) }}
            </div>

            <div>

                <h2>
                    {{ $guardian->full_name }}
                </h2>

                <p>
                    {{ $guardian->relationship }}
                </p>

                @if($guardian->is_primary)
                    <span class="badge">
                        Primary Guardian
                    </span>
                @else
                    <span class="badge">
                        Guardian
                    </span>
                @endif

            </div>

        </div>


        {{-- Guardian Information --}}
        <div class="section">

            <h3>Guardian Information</h3>

            <div class="details-grid">

                <div class="detail-item">
                    <span class="detail-label">
                        Full Name
                    </span>

                    <div class="detail-value">
                        {{ $guardian->full_name }}
                    </div>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Relationship
                    </span>

                    <div class="detail-value">
                        {{ $guardian->relationship }}
                    </div>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Phone Number
                    </span>

                    <div class="detail-value">
                        {{ $guardian->phone }}
                    </div>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Alternate Phone
                    </span>

                    <div class="detail-value">
                        {{ $guardian->alternate_phone ?? '—' }}
                    </div>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Occupation
                    </span>

                    <div class="detail-value">
                        {{ $guardian->occupation ?? '—' }}
                    </div>
                </div>


                <div class="detail-item">
                    <span class="detail-label">
                        Guardian Type
                    </span>

                    <div class="detail-value">
                        {{ $guardian->is_primary ? 'Primary Guardian' : 'Guardian' }}
                    </div>
                </div>

            </div>

        </div>


        {{-- Address --}}
        <div class="section">

            <h3>Address</h3>

            <div class="detail-item">
                <div class="detail-value">
                    {{ $guardian->address ?? 'No address provided.' }}
                </div>
            </div>

        </div>


        {{-- Connected Child --}}
        <div class="section">

            <h3>Connected Child</h3>

            @if($guardian->child)

                <div class="child-card">

                    <div class="child-info">

                        <h4>
                            {{ $guardian->child->first_name }}
                            {{ $guardian->child->middle_name }}
                            {{ $guardian->child->last_name }}
                        </h4>

                        <p>
                            Child Code:
                            <strong>
                                {{ $guardian->child->child_code }}
                            </strong>
                        </p>

                    </div>

                    <a
                        href="{{ route('children.show', $guardian->child) }}"
                        class="btn btn-child"
                    >
                        View Child
                    </a>

                </div>

            @else

                <div class="empty-child">
                    The child connected to this guardian is no longer available.
                </div>

            @endif

        </div>

    </div>

</div>

</body>
</html>
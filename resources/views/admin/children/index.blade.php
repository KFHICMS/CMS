<<<<<<< HEAD
@extends('layouts.admin')

@section('title', 'Child Management')
@section('page_title', 'Child Management')
@section('page_description', 'Manage and monitor all registered beneficiaries and children.')

@section('styles')
<style>
    .card {
        background: #f4faf5;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 5px 5px 16px rgba(0, 70, 30, 0.07);
        border: 1px solid #e1eee4;
    }

    .top-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }

    .table-wrapper {
        overflow-x: auto;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e0ede4;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    th {
        background: #e3f0e6;
        color: #245c3a;
        text-align: left;
        padding: 14px 12px;
        font-weight: 700;
        border-bottom: 2px solid #cfe0d3;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #e8f1eb;
        color: #3b5042;
        vertical-align: middle;
    }

    tr:hover td {
        background: #f7fbf8;
    }

    .child-code {
        font-weight: 700;
        color: #1f5d34;
        font-family: monospace;
        font-size: 13px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-active { background: #d3f3db; color: #1e7033; }
    .status-inactive { background: #fcdbd9; color: #9a2820; }

    .actions {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .action-btn {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .view-btn { background: #eaf4ed; color: #2f7045; }
    .view-btn:hover { background: #dceee1; }

    .edit-btn { background: #edf4fe; color: #2a6496; }
    .edit-btn:hover { background: #d9e8fb; }

    .del-btn { background: #fbeaea; color: #b33a3a; }
    .del-btn:hover { background: #f7dada; }

    .empty {
        text-align: center;
        padding: 50px 20px;
        color: #7b887f;
    }
</style>
@endsection

@section('content')
<div class="top-actions">
    <a href="{{ route('children.create') }}" class="btn btn-primary">
        + Add New Child
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        @if($children->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Child Code</th>
                        <th>Full Name</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>School</th>
                        <th>Grade</th>
                        <th>District / GN</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($children as $child)
                        <tr>
                            <td>
                                @if($child->profile_photo)
                                    <img
                                        src="{{ asset('storage/' . $child->profile_photo) }}"
                                        alt="{{ $child->first_name }}"
                                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #dcece1;"
                                    >
                                @else
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e4f2e8; color: #2e7d4f; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                                        {{ strtoupper(substr($child->first_name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="child-code">{{ $child->child_code }}</span>
                            </td>
                            <td>
                                <strong>{{ $child->first_name }} {{ $child->middle_name }} {{ $child->last_name }}</strong>
                            </td>
                            <td>{{ $child->date_of_birth?->format('d M Y') ?? '-' }}</td>
                            <td>{{ ucfirst($child->gender ?? '-') }}</td>
                            <td>{{ $child->school_name ?? '-' }}</td>
                            <td>{{ $child->grade ?? '-' }}</td>
                            <td>{{ $child->district ?? '-' }} <br><small style="color:#777;">{{ $child->gn_division ?? '' }}</small></td>
                            <td>
                                <span class="status-badge {{ $child->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($child->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('children.show', $child) }}" class="action-btn view-btn">View</a>
                                    <a href="{{ route('children.edit', $child) }}" class="action-btn edit-btn">Edit</a>
                                    <a href="{{ route('children.qr.show', $child) }}" class="action-btn" style="background:#f4edf8; color:#783f98;">ID Card</a>
                                    <form method="POST" action="{{ route('children.destroy', $child) }}" onsubmit="return confirm('Delete this child record?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn del-btn">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">
                <h3>No Children Registered</h3>
                <p>No child records have been added to the system yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
=======
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Child Management - KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f8f4;
            color: #26352c;
        }

        .page {
            padding: 30px;
            max-width: 1400px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #245c3a;
        }

        .header p {
            margin: 6px 0 0;
            color: #718078;
            font-size: 14px;
        }

        .add-btn {
            background: #2e7d4f;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .add-btn:hover {
            background: #24663f;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 5px 20px rgba(39, 83, 57, 0.08);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th {
            text-align: left;
            padding: 14px;
            background: #edf7f0;
            color: #376149;
            font-size: 13px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e7eee9;
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .child-code {
            font-weight: 700;
            color: #2e7d4f;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #e7f6eb;
            color: #267044;
        }

        .status-inactive {
            background: #fbeaea;
            color: #b33a3a;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 7px 11px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .view-btn {
            background: #e8f2ec;
            color: #2e7049;
        }

        .edit-btn {
            background: #eef3f7;
            color: #456274;
        }

        .empty {
            text-align: center;
            padding: 50px 20px;
            color: #7b8981;
        }

        .empty h3 {
            margin-bottom: 8px;
            color: #526158;
        }

        @media (max-width: 700px) {
            .page {
                padding: 18px;
            }

            .header {
                align-items: flex-start;
                flex-direction: column;
            }

            .add-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <div class="header">
        <div>
            <h1>Child Management</h1>
            <p>Manage registered children and their information.</p>
        </div>

        <a href="{{ route('children.create') }}" class="add-btn">
            + Add New Child
        </a>
    </div>
    <form method="GET" action="{{ route('children.index') }}" style="
    background:#ffffff;
    padding:20px;
    border-radius:16px;
    margin-bottom:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
">
    <div style="
        display:grid;
        grid-template-columns:1fr 180px auto auto;
        gap:12px;
        align-items:end;
    ">

        <div>
            <label style="
                display:block;
                margin-bottom:7px;
                font-weight:600;
                color:#31533f;
            ">
                Search Child
            </label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Child code, name or district..."
                style="
                    width:100%;
                    padding:12px 14px;
                    border:1px solid #dce8df;
                    border-radius:10px;
                    outline:none;
                "
            >
        </div>

        <div>
            <label style="
                display:block;
                margin-bottom:7px;
                font-weight:600;
                color:#31533f;
            ">
                Status
            </label>

            <select
                name="status"
                style="
                    width:100%;
                    padding:12px 14px;
                    border:1px solid #dce8df;
                    border-radius:10px;
                    outline:none;
                    background:#fff;
                "
            >
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                    Active
                </option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
        </div>

        <button
            type="submit"
            style="
                padding:12px 20px;
                border:none;
                border-radius:10px;
                background:#2e7d4f;
                color:white;
                font-weight:600;
                cursor:pointer;
            "
        >
            Search
        </button>

        <a
            href="{{ route('children.index') }}"
            style="
                padding:12px 20px;
                border-radius:10px;
                background:#eef5f0;
                color:#31533f;
                text-decoration:none;
                font-weight:600;
                text-align:center;
            "
        >
            Clear
        </a>

    </div>
</form>
    <div class="card">

        @if(session('success'))
            <div style="
                background:#e8f6ec;
                color:#267044;
                padding:13px 16px;
                border-radius:10px;
                margin-bottom:20px;
                font-size:14px;
                font-weight:600;
            ">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="
                background:#fbeaea;
                color:#b33a3a;
                padding:13px 16px;
                border-radius:10px;
                margin-bottom:20px;
                font-size:14px;
                font-weight:600;
            ">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-wrapper">

            @if($children->count() > 0)

                <table>

                    <thead>
                        <tr>
                        <th>Photo</th>
<th>Child Code</th>
<th>Child Name</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>School</th>
                            <th>Grade</th>
                            <th>District</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($children as $child)

                            <tr>

                            <td>
    @if($child->profile_photo)
        <img
            src="{{ asset('storage/' . $child->profile_photo) }}"
            alt="{{ $child->first_name }}"
            style="
                width:45px;
                height:45px;
                object-fit:cover;
                border-radius:50%;
                border:2px solid #dcece1;
            "
        >
    @else
        <div style="
            width:45px;
            height:45px;
            border-radius:50%;
            background:#e4f2e8;
            color:#2e7d4f;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:bold;
        ">
            {{ strtoupper(substr($child->first_name, 0, 1)) }}
        </div>
    @endif
</td>

<td>
    <span class="child-code">
        {{ $child->child_code }}
    </span>
</td>

                                <td>
                                    {{ $child->first_name }}
                                    {{ $child->middle_name }}
                                    {{ $child->last_name }}
                                </td>

                                <td>
                                    {{ $child->date_of_birth?->format('d M Y') ?? '-' }}
                                </td>

                                <td>
                                    {{ ucfirst($child->gender ?? '-') }}
                                </td>

                                <td>
                                    {{ $child->school_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $child->grade ?? '-' }}
                                </td>

                                <td>
                                    {{ $child->district ?? '-' }}
                                </td>

                                <td>

                                    @if($child->status === 'active')

                                        <span class="status status-active">
                                            Active
                                        </span>

                                    @else

                                        <span class="status status-inactive">
                                            {{ ucfirst($child->status) }}
                                        </span>

                                    @endif

                                </td>

                                <td>

                                <div class="actions">

<a
    href="{{ route('children.show', $child) }}"
    class="action-btn view-btn"
>
    View
</a>

<a
    href="{{ route('children.edit', $child) }}"
    class="action-btn edit-btn"
>
    Edit
</a>

<form
    method="POST"
    action="{{ route('children.destroy', $child) }}"
    onsubmit="return confirm('Are you sure you want to delete this child record?');"
    style="display:inline;"
>
    @csrf
    @method('DELETE')

    <button
        type="submit"
        class="action-btn"
        style="
            border:none;
            background:#fbeaea;
            color:#b33a3a;
            cursor:pointer;
        "
    >
        Delete
    </button>
</form>

</div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @else

                <div class="empty">

                    <h3>No Children Registered</h3>

                    <p>
                        No child records have been added to the system yet.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>

</body>
</html>
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

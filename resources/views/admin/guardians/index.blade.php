<<<<<<< HEAD
@extends('layouts.admin')

@section('title', 'Guardian Management')
@section('page_title', 'Guardian Management')
@section('page_description', 'Manage parent and guardian information connected to registered children.')

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

    .filter-card {
        background: #e8f4ec;
        border: 1px solid #d2e4d7;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
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

    .primary-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        background: #d3f3db;
        color: #1e7033;
    }

    .secondary-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: #edf4fe;
        color: #2a6496;
    }

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
    <a href="{{ route('guardians.create') }}" class="btn btn-primary">
        + Add New Guardian
    </a>
</div>

<div class="card">
    <form method="GET" action="{{ route('guardians.index') }}" class="filter-card">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search guardian, child code, or phone..."
            style="flex: 1; min-width: 250px; padding: 10px 14px; border: 1px solid #c9decb; border-radius: 8px; font-size: 13px;"
        >

        <select name="relationship" style="padding: 10px 14px; border: 1px solid #c9decb; border-radius: 8px; font-size: 13px; background: #fff;">
            <option value="">All Relationships</option>
            <option value="Father" {{ request('relationship') === 'Father' ? 'selected' : '' }}>Father</option>
            <option value="Mother" {{ request('relationship') === 'Mother' ? 'selected' : '' }}>Mother</option>
            <option value="Guardian" {{ request('relationship') === 'Guardian' ? 'selected' : '' }}>Guardian</option>
            <option value="Grandparent" {{ request('relationship') === 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
            <option value="Other" {{ request('relationship') === 'Other' ? 'selected' : '' }}>Other</option>
        </select>

        <button type="submit" class="btn btn-primary">Search</button>
        <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="table-wrapper">
        @if($guardians->count())
=======
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Management - KFHI CMS</title>

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
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            color: #245c3a;
        }

        .page-header p {
            margin: 7px 0 0;
            color: #718078;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-primary {
            background: #2e7d4f;
            color: #ffffff;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #e4f4e8;
            color: #28613c;
        }

        .table-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(35, 80, 50, 0.07);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th {
            text-align: left;
            padding: 14px 12px;
            background: #eef6f0;
            color: #31533f;
            font-size: 14px;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #edf1ee;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .guardian-name {
            font-weight: 600;
            color: #294536;
        }

        .child-name {
            color: #527062;
        }

        .primary-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #e4f4e8;
            color: #2e7d4f;
            font-size: 12px;
            font-weight: 600;
        }

        .secondary-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #f1f3f2;
            color: #718078;
            font-size: 12px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 7px 11px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .view-btn {
            background: #edf5f0;
            color: #2e7d4f;
        }

        .edit-btn {
            background: #eef2f8;
            color: #42658c;
        }

        .delete-btn {
            border: none;
            background: #fbeaea;
            color: #b33a3a;
            cursor: pointer;
        }

        .empty {
            text-align: center;
            padding: 50px 20px;
            color: #718078;
        }

        .empty h3 {
            margin-bottom: 8px;
            color: #456352;
        }

        @media (max-width: 700px) {
            .container {
                padding: 18px;
            }

            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="page-header">
        <div>
            <h1>Guardian Management</h1>
            <p>Manage guardian information connected to registered children.</p>
        </div>

        <a href="{{ route('guardians.create') }}" class="btn btn-primary">
            + Add New Guardian
        </a>
    </div>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif
    <form
    method="GET"
    action="{{ route('guardians.index') }}"
    style="
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-bottom:20px;
        padding:16px;
        background:#f7fbf8;
        border:1px solid #dce8df;
        border-radius:12px;
    "
>
    <input
        type="text"
        name="search"
        value="{{ request('search') }}"
        placeholder="Search guardian, child ID, child name or phone..."
        style="
            flex:1;
            min-width:280px;
            padding:12px 14px;
            border:1px solid #d6e3da;
            border-radius:10px;
            outline:none;
        "
    >
    <select
    name="relationship"
    style="
        padding:12px 14px;
        border:1px solid #d6e3da;
        border-radius:10px;
        outline:none;
        min-width:180px;
        background:#ffffff;
        color:#294536;
    "
>
    <option value="">All Relationships</option>

    <option value="Father" {{ request('relationship') === 'Father' ? 'selected' : '' }}>
        Father
    </option>

    <option value="Mother" {{ request('relationship') === 'Mother' ? 'selected' : '' }}>
        Mother
    </option>

    <option value="Guardian" {{ request('relationship') === 'Guardian' ? 'selected' : '' }}>
        Guardian
    </option>

    <option value="Grandparent" {{ request('relationship') === 'Grandparent' ? 'selected' : '' }}>
        Grandparent
    </option>

    <option value="Other" {{ request('relationship') === 'Other' ? 'selected' : '' }}>
        Other
    </option>
</select>
    <button
        type="submit"
        style="
            padding:12px 22px;
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
        href="{{ route('guardians.index') }}"
        style="
            padding:12px 20px;
            border-radius:10px;
            background:#e8f2eb;
            color:#2e7d4f;
            text-decoration:none;
            font-weight:600;
        "
    >
        Clear
    </a>
</form>
    <div class="table-card">

        @if($guardians->count())

>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guardian</th>
<<<<<<< HEAD
                        <th>Associated Child</th>
=======
                        <th>Child</th>
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
                        <th>Relationship</th>
                        <th>Phone</th>
                        <th>Occupation</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
<<<<<<< HEAD
                <tbody>
                    @foreach($guardians as $guardian)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $guardian->full_name }}</strong></td>
                            <td>
                                @if($guardian->child)
                                    <div><strong>{{ $guardian->child->first_name }} {{ $guardian->child->last_name }}</strong></div>
                                    <small style="color: #6d8474; font-family: monospace;">{{ $guardian->child->child_code }}</small>
                                @else
                                    <span style="color: #b33a3a;">Child unavailable</span>
                                @endif
                            </td>
                            <td>{{ $guardian->relationship }}</td>
                            <td>{{ $guardian->phone ?? '-' }}</td>
                            <td>{{ $guardian->occupation ?? '-' }}</td>
                            <td>
                                @if($guardian->is_primary)
                                    <span class="primary-badge">Primary</span>
                                @else
                                    <span class="secondary-badge">Guardian</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('guardians.show', $guardian) }}" class="action-btn view-btn">View</a>
                                    <a href="{{ route('guardians.edit', $guardian) }}" class="action-btn edit-btn">Edit</a>
                                    <form method="POST" action="{{ route('guardians.destroy', $guardian) }}" onsubmit="return confirm('Delete this guardian?');" style="display:inline;">
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
                <h3>No Guardians Found</h3>
                <p>No guardian records have been registered or matched your search.</p>
            </div>
        @endif
    </div>
</div>
@endsection
=======

                <tbody>

                    @foreach($guardians as $guardian)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <div class="guardian-name">
                                    {{ $guardian->full_name }}
                                </div>
                            </td>

                            <td>
                                @if($guardian->child)
                                    <div class="child-name">
                                        {{ $guardian->child->first_name }}
                                        {{ $guardian->child->last_name }}
                                    </div>

                                    <small style="color:#8a9890;">
                                        {{ $guardian->child->child_code }}
                                    </small>
                                @else
                                    <span style="color:#b33a3a;">
                                        Child unavailable
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $guardian->relationship }}
                            </td>

                            <td>
                                {{ $guardian->phone }}
                            </td>

                            <td>
                                {{ $guardian->occupation ?? '—' }}
                            </td>

                            <td>
                                @if($guardian->is_primary)
                                    <span class="primary-badge">
                                        Primary
                                    </span>
                                @else
                                    <span class="secondary-badge">
                                        Guardian
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="actions">

                                    <a
                                        href="{{ route('guardians.show', $guardian) }}"
                                        class="action-btn view-btn"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route('guardians.edit', $guardian) }}"
                                        class="action-btn edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('guardians.destroy', $guardian) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this guardian?');"
                                        style="display:inline;"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete-btn"
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
                <h3>No Guardians Found</h3>
                <p>No guardian records have been registered yet.</p>

                <a
                    href="{{ route('guardians.create') }}"
                    class="btn btn-primary"
                    style="margin-top:15px;"
                >
                    Add First Guardian
                </a>
            </div>

        @endif

    </div>

</div>

</body>
</html>
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

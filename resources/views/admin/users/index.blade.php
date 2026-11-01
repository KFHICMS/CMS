@extends('layouts.app', [
    'active' => 'users',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users'],
    ],
])

@section('title', 'User Management')
@section('heading', 'User Management')
@section('subheading', 'Manage KFHI CMS administrators and field members.')

@section('actions')
    <a class="k-btn k-btn-primary" href="{{ route('users.create') }}">Add New User</a>
@endsection

@section('content')
<section class="k-card k-table-wrap" aria-label="Users">
    <table class="k-table">
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        <div class="child">
                            @if ($user->profile_photo)
                                <img src="{{ asset('storage/'.$user->profile_photo) }}" alt="">
                            @else
                                <span class="avatar" aria-hidden="true">{{ mb_substr($user->name ?? 'U', 0, 1) }}</span>
                            @endif
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?: '—' }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $user->role)) }}</td>
                    <td>
                        <span class="k-badge {{ $user->status === 'active' ? '' : 'k-badge-muted' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="k-page-actions">
                            <a class="k-btn k-btn-secondary" href="{{ route('users.edit', $user) }}">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button class="k-btn k-btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="k-empty">There are currently no users in the system.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection

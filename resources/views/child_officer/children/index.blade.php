@extends('layouts.app', ['active' => 'children'])

@section('title', 'Children')
@section('heading', 'Children')
@section('subheading', 'Search, filter and view registered children.')

@section('content')
    @if(session('success'))
        <div class="k-alert k-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="k-card" style="margin-bottom: 16px;">
        <form method="GET" action="{{ route('child_officer.children.index') }}" class="k-form-row">
            <div class="k-field">
                <label for="search">Search</label>
                <input id="search" name="search" class="k-input" type="text" value="{{ request('search') }}" placeholder="Search by ID, name or district...">
            </div>

            <div class="k-field">
                <label for="status">Status</label>
                <select id="status" name="status" class="k-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="k-field">
                <label for="district">District</label>
                <select id="district" name="district" class="k-select">
                    <option value="">All Districts</option>
                    @foreach($districts as $district)
                        <option value="{{ $district }}" {{ request('district') === $district ? 'selected' : '' }}>{{ $district }}</option>
                    @endforeach
                </select>
            </div>

            <div class="k-field" style="align-self: end;">
                <button type="submit" class="k-btn k-btn-primary">Search</button>
            </div>

            <div class="k-field" style="align-self: end;">
                <a href="{{ route('child_officer.children.index') }}" class="k-btn k-btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="k-card">
        <div class="k-card-head" style="margin-bottom: 0;">
            <h2>Children</h2>
            <span>{{ $children->total() }} children found</span>
        </div>

        <div class="k-table-wrap" style="margin-top: 12px;">
            @if($children->count())
                <table class="k-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Child Code</th>
                            <th>Name</th>
                            <th>District</th>
                            <th>GN Division</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($children as $child)
                            <tr>
                                <td>
                                    @if($child->profile_photo)
                                        <img src="{{ asset('storage/' . $child->profile_photo) }}" alt="Child Photo" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #dfeee2;">
                                    @else
                                        <div style="width:42px;height:42px;border-radius:50%;background:#e8f5e9;color:#2e7d32;display:flex;align-items:center;justify-content:center;font-weight:700;">{{ strtoupper(substr($child->first_name, 0, 1)) }}</div>
                                    @endif
                                </td>
                                <td><strong style="color: var(--k-primary-dark);">{{ $child->child_code }}</strong></td>
                                <td>{{ $child->first_name }} {{ $child->middle_name }} {{ $child->last_name }}</td>
                                <td>{{ $child->district ?? 'N/A' }}</td>
                                <td>{{ $child->gn_division ?? 'N/A' }}</td>
                                <td>
                                    <span class="k-badge {{ $child->status === 'active' ? 'k-badge' : 'k-badge-muted' }}">{{ ucfirst($child->status) }}</span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                        <a href="{{ route('child_officer.children.show', $child) }}" style="font-weight:600;">View</a>
                                        <form action="{{ route('child_officer.children.destroy', $child) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this child information?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="k-btn k-btn-danger" style="min-height: 32px; padding: 7px 12px; font-size: 13px;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="k-empty">No children found. Try changing your search or filter.</div>
            @endif
        </div>

        @if($children->hasPages())
            <div style="padding-top: 18px;">{{ $children->links() }}</div>
        @endif
    </section>
@endsection

</body>
</html>
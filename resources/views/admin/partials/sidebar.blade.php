<!-- Common Unified Admin Sidebar for KFHI-CMS -->
<aside class="sidebar">
    <div class="brand">
        <img src="{{ asset('images/logo.png') }}" alt="KFHI Logo">
        <h2>KFHI-CMS</h2>
        <p>Child Management System</p>
    </div>

    <nav class="nav">
        <div class="nav-title">Main Menu</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            User Management
        </a>
        <a href="{{ route('children.index') }}" class="{{ request()->routeIs('children.*') ? 'active' : '' }}">
            Child Management
        </a>
        <a href="{{ route('guardians.index') }}" class="{{ request()->routeIs('guardians.*') ? 'active' : '' }}">
            Guardians
        </a>
        <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">
            Events
        </a>
        <a href="{{ route('admin.follow-ups.index') }}" class="{{ request()->routeIs('admin.follow-ups.*') ? 'active' : '' }}">
            Follow-ups
        </a>

        <div class="nav-title" style="margin-top: 25px;">System</div>
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            Reports & Analytics
        </a>
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="logout-form">
        @csrf
        <button type="submit" class="logout-button">Logout</button>
    </form>
</aside>

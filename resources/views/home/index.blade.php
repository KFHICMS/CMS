<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>KFHI-CMS | Child Management System</title>
    <meta name="description" content="The KFHI Child Management System helps authorized staff securely manage child information, programs, activities and services.">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Landing-page styles only. Dashboards use kfhi-ui.css and never load this file. --}}
    <link rel="stylesheet" href="{{ asset('css/kfhi-landing.css') }}">
    <script>document.documentElement.classList.add('kl-js');</script>
</head>
<body class="kl-body">
<a class="kl-skip" href="#main">Skip to main content</a>

@include('layouts.partials.icons')

<!-- Navigation -->
<header class="kl-header">
    <div class="kl-wrap kl-header-inner">
        <a class="kl-brand" href="#home" aria-label="KFHI Child Management System, home">
            <span class="kl-logo-tile"><img src="{{ asset('images/logo.png') }}" alt="KFHI logo" width="1000" height="1000"></span>
        </a>

        <nav class="kl-nav" id="kl-nav" aria-label="Main navigation">
            <ul>
                <li><a class="kl-nav-link is-active" href="#home" data-spy="home" aria-current="location">Home</a></li>
                <li><a class="kl-nav-link" href="#about" data-spy="about">About Us</a></li>
                <li><a class="kl-nav-link" href="#services" data-spy="services">Services</a></li>
                <li><a class="kl-nav-link" href="#team" data-spy="team">Team</a></li>
            </ul>
        </nav>

        <div class="kl-header-actions">
            <a class="kl-btn kl-login" href="{{ route('login') }}">Login</a>
            <button class="kl-toggle" id="kl-toggle" type="button" aria-expanded="false" aria-controls="kl-nav" aria-label="Open menu">
                <span class="kl-toggle-bars" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</header>

<main id="main" tabindex="-1">

    <!-- Hero -->
    <section class="kl-hero" id="home" aria-labelledby="kl-hero-title">
        <div class="kl-wrap kl-hero-grid">
            <div>
                <p class="kl-eyebrow">Child Management System</p>
                <h1 id="kl-hero-title">Empowering Children, <span>Building Brighter Futures</span></h1>
                <p class="kl-lead">
                    The KFHI Child Management System helps authorized staff securely manage
                    child information, programs, activities and services in one place.
                </p>
                <div class="kl-cta">
                    <a class="kl-btn kl-btn-primary" href="{{ route('login') }}">Get Started</a>
                    <a class="kl-btn kl-btn-soft" href="#about">Learn More</a>
                </div>
            </div>

            <div class="kl-visual" aria-hidden="true">
                <span class="kl-visual-logo"><img src="{{ asset('images/logo.png') }}" alt="" width="1000" height="1000"></span>
                <div class="kl-chips">
                    <div class="kl-chip"><span class="kl-chip-icon"><svg class="kl-icon"><use href="#i-heart"></use></svg></span>Child records</div>
                    <div class="kl-chip"><span class="kl-chip-icon"><svg class="kl-icon"><use href="#i-users"></use></svg></span>Guardian information</div>
                    <div class="kl-chip"><span class="kl-chip-icon"><svg class="kl-icon"><use href="#i-calendar"></use></svg></span>Programs &amp; events</div>
                    <div class="kl-chip"><span class="kl-chip-icon"><svg class="kl-icon"><use href="#i-scan"></use></svg></span>QR identification</div>
                </div>
                <p class="kl-visual-note">Role-based access for authorized staff</p>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="kl-section" id="features" aria-labelledby="kl-features-title">
        <div class="kl-wrap">
            <div class="kl-section-head">
                <h2 id="kl-features-title">Everything your team needs, in one place</h2>
                <p>Tools built for the day-to-day work of KFHI staff.</p>
            </div>

            <div class="kl-grid kl-grid-4">
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-heart"></use></svg></div>
                    <h3>Child Information Management</h3>
                    <p>Securely maintain and access authorized child records.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-scan"></use></svg></div>
                    <h3>QR-Based Identification</h3>
                    <p>Support child identification through QR codes.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-calendar"></use></svg></div>
                    <h3>Program and Event Management</h3>
                    <p>Organize programs, activities and attendance records.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-chart"></use></svg></div>
                    <h3>Reporting and Monitoring</h3>
                    <p>Help authorized staff monitor services and review relevant information.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- About Us -->
    <section class="kl-section kl-section-alt" id="about" aria-labelledby="kl-about-title">
        <div class="kl-wrap kl-about">
            <div class="kl-about-copy">
                <h2 id="kl-about-title">About Us</h2>
                <p>
                    The KFHI Child Management System is the internal platform KFHI staff use to keep
                    child-focused work organized, consistent and secure.
                </p>
                <p>
                    It brings child and guardian records, programs, events, attendance, questionnaires
                    and follow-ups together, so authorized staff can coordinate programs and services
                    without scattered spreadsheets or paper files.
                </p>
                <p>
                    Access is limited to signed-in staff, and each person sees only the tools that
                    belong to their role.
                </p>
            </div>

            <ul class="kl-points">
                <li class="kl-point">
                    <span class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-user"></use></svg></span>
                    <div>
                        <h3>Role-based access</h3>
                        <p>Administrators, child officers, field officers and enumerators each have their own workspace.</p>
                    </div>
                </li>
                <li class="kl-point">
                    <span class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-clipboard"></use></svg></span>
                    <div>
                        <h3>Accountability</h3>
                        <p>Important actions are recorded in an audit log for administrators to review.</p>
                    </div>
                </li>
                <li class="kl-point">
                    <span class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-folder"></use></svg></span>
                    <div>
                        <h3>Backup and recovery</h3>
                        <p>Administrators can create database backups and restore them when needed.</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    <!-- Services -->
    <section class="kl-section" id="services" aria-labelledby="kl-services-title">
        <div class="kl-wrap">
            <div class="kl-section-head">
                <h2 id="kl-services-title">Services</h2>
                <p>What the system supports today.</p>
            </div>

            <div class="kl-grid kl-grid-3">
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-heart"></use></svg></div>
                    <h3>Child record management</h3>
                    <p>Register children, keep their details up to date and review each child's profile.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-users"></use></svg></div>
                    <h3>Guardian information</h3>
                    <p>Record guardians and link them to the children in their care.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-calendar"></use></svg></div>
                    <h3>Program and event coordination</h3>
                    <p>Plan programs and events, and assign field officers and enumerators to them.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-scan"></use></svg></div>
                    <h3>QR identification and attendance</h3>
                    <p>Generate child QR codes and scan them to identify children and record attendance.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-check"></use></svg></div>
                    <h3>Questionnaires and submissions</h3>
                    <p>Build questionnaires for events, collect responses and review submissions.</p>
                </article>
                <article class="kl-card">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-chart"></use></svg></div>
                    <h3>Reports and monitoring</h3>
                    <p>Review reports, follow-ups and activity records to keep services on track.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section class="kl-section kl-section-alt" id="team" aria-labelledby="kl-team-title">
        <div class="kl-wrap">
            <div class="kl-section-head">
                <h2 id="kl-team-title">Team</h2>
                <p>The people who use the system work in four roles, each with its own tools.</p>
            </div>

            <div class="kl-grid kl-grid-4">
                <article class="kl-card kl-role">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-dashboard"></use></svg></div>
                    <span class="kl-role-tag">Administration</span>
                    <h3>Administrators</h3>
                    <p>Manage users, programs, events, questionnaires, reports, audit logs and backups.</p>
                </article>
                <article class="kl-card kl-role">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-heart"></use></svg></div>
                    <span class="kl-role-tag">Child Officers</span>
                    <h3>Child records</h3>
                    <p>Register and maintain child and guardian records, and work with QR identification.</p>
                </article>
                <article class="kl-card kl-role">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-folder"></use></svg></div>
                    <span class="kl-role-tag">Field Officers</span>
                    <h3>Programs in the field</h3>
                    <p>Carry out assigned programs and activities, and handle follow-ups.</p>
                </article>
                <article class="kl-card kl-role">
                    <div class="kl-card-icon"><svg class="kl-icon" aria-hidden="true"><use href="#i-clipboard"></use></svg></div>
                    <span class="kl-role-tag">Enumerators</span>
                    <h3>Data collection</h3>
                    <p>Record attendance and questionnaire responses for the events they are assigned to.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<!-- Footer -->
<footer class="kl-footer">
    <div class="kl-wrap kl-footer-inner">
        <a class="kl-brand" href="#home" aria-label="KFHI Child Management System, back to top">
            <span class="kl-logo-tile"><img src="{{ asset('images/logo.png') }}" alt="KFHI logo" width="1000" height="1000" loading="lazy"></span>
        </a>

        <nav class="kl-footer-nav" aria-label="Footer navigation">
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
            </ul>
        </nav>
    </div>
    <p class="kl-copy">&copy; {{ date('Y') }} KFHI Child Management System. All rights reserved.</p>
</footer>

<script>
(() => {
    const toggle = document.getElementById('kl-toggle');
    const nav = document.getElementById('kl-nav');
    const links = Array.from(document.querySelectorAll('[data-spy]'));

    function setMenu(open) {
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }

    toggle.addEventListener('click', () => setMenu(!nav.classList.contains('is-open')));
    nav.addEventListener('click', (event) => { if (event.target.closest('a')) setMenu(false); });
    document.addEventListener('click', (event) => {
        if (nav.classList.contains('is-open') && !event.target.closest('.kl-header')) setMenu(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) { setMenu(false); toggle.focus(); }
    });
    window.matchMedia('(min-width: 901px)').addEventListener('change', () => setMenu(false));

    // Highlight the nav item for the section currently in view. The features block belongs to "Home".
    function setActive(name) {
        links.forEach((link) => {
            const on = link.dataset.spy === name;
            link.classList.toggle('is-active', on);
            if (on) { link.setAttribute('aria-current', 'location'); } else { link.removeAttribute('aria-current'); }
        });
    }

    if ('IntersectionObserver' in window) {
        const map = { home: 'home', features: 'home', about: 'about', services: 'services', team: 'team' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => { if (entry.isIntersecting) setActive(map[entry.target.id]); });
        }, { rootMargin: '-40% 0px -55% 0px' });
        Object.keys(map).forEach((id) => { const el = document.getElementById(id); if (el) observer.observe(el); });
    }
})();
</script>
</body>
</html>
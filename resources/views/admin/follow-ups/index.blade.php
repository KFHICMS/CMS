<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follow-up Management | KFHI-CMS</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #eef7f0;
            color: #294432;
        }

        .layout { min-height: 100vh; }

        .sidebar {
            width: 250px;
            background: #f4faf5;
            padding: 25px 18px;
            box-shadow: 5px 0 20px rgba(0, 70, 30, 0.08);
            position: fixed;
            inset: 0 auto 0 0;
        }

        .brand {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid #dce9df;
        }

        .brand img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .brand h2 { color: #236b3b; font-size: 19px; }
        .brand p { color: #7b897f; font-size: 11px; margin-top: 4px; }
        .nav { margin-top: 25px; }

        .nav-title {
            font-size: 11px;
            color: #8a978d;
            text-transform: uppercase;
            margin: 0 10px 10px;
            letter-spacing: 1px;
        }

        .nav a {
            display: block;
            text-decoration: none;
            color: #53665a;
            padding: 12px 14px;
            margin-bottom: 6px;
            border-radius: 10px;
            font-size: 14px;
        }

        .nav a:hover, .nav a.active {
            background: #e0f0e3;
            color: #28733a;
        }

        .logout-form {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 20px;
        }

        .logout-button {
            width: 100%;
            border: none;
            padding: 11px;
            border-radius: 10px;
            background: #fbeaea;
            color: #b44949;
            cursor: pointer;
            font-size: 14px;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        h1 { color: #236b3b; font-size: 28px; }
        .subtitle { color: #75837a; font-size: 13px; margin-top: 6px; }

        .user-info {
            background: #f4faf5;
            padding: 10px 16px;
            border-radius: 12px;
            box-shadow: 4px 4px 10px rgba(0, 70, 30, 0.07);
            white-space: nowrap;
        }

        .user-info strong { color: #2c693b; font-size: 14px; }
        .user-info span { display: block; color: #839087; font-size: 11px; margin-top: 3px; }

        .notice, .error-notice {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .notice { background: #e3f4e8; border: 1px solid #b8dfc5; color: #27633e; }
        .error-notice { background: #fff0ef; border: 1px solid #f0c9c5; color: #a33d35; }
        .error-notice ul { margin-left: 20px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card, .panel {
            background: #fff;
            border: 1px solid #dce9df;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(35, 70, 50, 0.05);
        }

        .stat-card { padding: 19px; }
        .stat-label { color: #77867b; font-size: 13px; }
        .stat-number { color: #246d38; font-size: 28px; font-weight: 700; margin-top: 8px; }
        .stat-card.overdue .stat-number { color: #b44949; }

        .panel { padding: 20px; }
        .panel-heading { margin-bottom: 17px; }
        .panel-heading h2 { color: #2c6339; font-size: 18px; }
        .panel-heading p { color: #7b897f; font-size: 12px; margin-top: 6px; }

        .create-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
        }

        .field { display: grid; gap: 6px; }
        .field label { color: #52675a; font-size: 12px; font-weight: 600; }
        .field small { color: #7b897f; font-size: 11px; }
        .field-wide { grid-column: 1 / -1; }
        .create-form textarea { min-height: 80px; resize: vertical; }
        .target-message { color: #a33d35; font-size: 12px; }

        .filters {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) 170px 190px auto auto;
            gap: 10px;
            margin-bottom: 17px;
        }

        input, select, textarea {
            width: 100%;
            border: 1px solid #d5e2d8;
            border-radius: 8px;
            background: #fff;
            color: #294432;
            padding: 9px 10px;
            font-size: 13px;
        }

        input:focus, select:focus, textarea:focus {
            outline: 2px solid #9dccaa;
            border-color: #5d9e70;
        }

        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            background: #2e7d4f;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
        }

        .button:hover { background: #256b42; }
        .button-muted { background: #edf3ef; color: #52675a; }
        .button-muted:hover { background: #e0ebe3; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        th {
            background: #f5faf6;
            color: #718078;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: 10px;
            text-align: left;
            padding: 12px 10px;
        }

        td { padding: 14px 10px; border-top: 1px solid #e7eee9; vertical-align: top; }
        .child-name { color: #2b5037; font-size: 13px; font-weight: 700; }
        .muted { color: #7b897f; font-size: 11px; margin-top: 5px; }
        .module-tag {
            display: inline-block;
            color: #287047;
            background: #e8f4ec;
            padding: 5px 8px;
            border-radius: 7px;
            font-size: 11px;
            white-space: nowrap;
        }

        .description { max-width: 220px; color: #52675a; font-size: 12px; line-height: 1.5; }
        .row-controls { display: grid; gap: 7px; min-width: 175px; }
        .row-controls textarea { min-height: 58px; resize: vertical; }
        .row-controls .button { justify-self: start; padding: 8px 11px; }
        .status {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 20px;
            background: #edf3ef;
            color: #53665a;
            font-size: 11px;
            white-space: nowrap;
        }

        .status-pending { background: #fff5dc; color: #89651b; }
        .status-in_progress { background: #e9f1ff; color: #3f65a2; }
        .status-completed { background: #e3f4e8; color: #27633e; }
        .status-overdue { background: #fff0ef; color: #a33d35; }

        .empty-state { text-align: center; padding: 42px 15px; color: #718078; }
        .pagination { margin-top: 18px; }

        @media (max-width: 1050px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filters { grid-template-columns: minmax(180px, 1fr) 180px 180px auto auto; }
        }

        @media (max-width: 750px) {
            .sidebar { width: 200px; }
            .main { margin-left: 200px; padding: 22px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .filters, .create-form { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 600px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 18px; }
            .stats { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-card { padding: 14px; }
            h1 { font-size: 23px; }
            .filters, .create-form { grid-template-columns: 1fr; }
            .field-wide { grid-column: auto; }
        }
    </style>
</head>
<body>
<div class="layout">
    @include('admin.partials.sidebar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Follow-up Management</h1>
                <p class="subtitle">Create, assign, and monitor follow-ups across operational modules.</p>
            </div>
            <div class="user-info">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>

        @if(session('success'))
            <div class="notice" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-notice" role="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="stats" aria-label="Follow-up summary">
            <article class="stat-card">
                <div class="stat-label">Open follow-ups</div>
                <div class="stat-number">{{ $stats['open'] }}</div>
            </article>
            <article class="stat-card">
                <div class="stat-label">Unassigned</div>
                <div class="stat-number">{{ $stats['unassigned'] }}</div>
            </article>
            <article class="stat-card">
                <div class="stat-label">In progress</div>
                <div class="stat-number">{{ $stats['inProgress'] }}</div>
            </article>
            <article class="stat-card overdue">
                <div class="stat-label">Overdue</div>
                <div class="stat-number">{{ $stats['overdue'] }}</div>
            </article>
        </section>

        <section class="panel" style="margin-bottom: 22px;">
            <div class="panel-heading">
                <h2>Create a follow-up</h2>
                <p>Choose a record from any supported module and assign an officer if known.</p>
            </div>
            <form method="POST" action="{{ route('admin.follow-ups.store') }}" class="create-form">
                @csrf
                <div class="field">
                    <label for="target-module">Module</label>
                    <select id="target-module" name="module" required>
                        <option value="">Choose a module</option>
                        @foreach($modules as $key => $label)
                            <option value="{{ $key }}" @selected(old('module') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="target-search">Find record</label>
                    <input
                        id="target-search"
                        type="search"
                        placeholder="Select a module first"
                        autocomplete="off"
                        disabled
                    >
                    <small>Search by name, code, event, or title.</small>
                </div>
                <div class="field field-wide">
                    <label for="target-record">Related record</label>
                    <select id="target-record" name="target_id" required disabled>
                        <option value="">Select a module to load records</option>
                    </select>
                    <div id="target-message" class="target-message" role="status" aria-live="polite"></div>
                </div>
                <div class="field">
                    <label for="follow-up-category">Category</label>
                    <input
                        id="follow-up-category"
                        type="text"
                        name="category"
                        maxlength="100"
                        value="{{ old('category') }}"
                        placeholder="e.g. Welfare check"
                    >
                </div>
                <div class="field">
                    <label for="follow-up-priority">Priority</label>
                    <select id="follow-up-priority" name="priority" required>
                        <option value="low" @selected(old('priority') === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority') === 'high')>High</option>
                    </select>
                </div>
                <div class="field">
                    <label for="follow-up-officer">Assign officer</label>
                    <select id="follow-up-officer" name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach($officers as $officer)
                            <option value="{{ $officer->id }}" @selected(old('assigned_to') == $officer->id)>
                            {{ $officer->name }} — {{ ucwords(str_replace('_', ' ', $officer->role)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="next-follow-up-date">Due date</label>
                    <input
                        id="next-follow-up-date"
                        type="date"
                        name="next_follow_up_date"
                        value="{{ old('next_follow_up_date') }}"
                    >
                </div>
                <div class="field field-wide">
                    <label for="follow-up-description">Follow-up details</label>
                    <textarea
                        id="follow-up-description"
                        name="description"
                        maxlength="5000"
                        required
                        placeholder="Describe what needs to be followed up"
                    >{{ old('description') }}</textarea>
                </div>
                <div class="field field-wide">
                    <button type="submit" class="button">Create follow-up</button>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <h2>Follow-up queue</h2>
                <p>Pending and completed follow-ups from every supported module.</p>
            </div>

            <form method="GET" action="{{ route('admin.follow-ups.index') }}" class="filters">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search child, officer, or details"
                    aria-label="Search follow-ups"
                >
                <select name="status" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    <option value="unassigned" @selected(request('status') === 'unassigned')>Unassigned</option>
                </select>
                <select name="module" aria-label="Filter by module">
                    <option value="">All modules</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" @selected(request('module') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="button">Apply filters</button>
                <a href="{{ route('admin.follow-ups.index') }}" class="button button-muted">Clear</a>
            </form>

            @if($followUps->isEmpty())
                <div class="empty-state">
                    No follow-ups match these filters.
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Module</th>
                            <th>Related record</th>
                            <th>Follow-up</th>
                            <th>Due date</th>
                            <th>Status</th>
                            <th>Assignment &amp; progress</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($followUps as $followUp)
                            @php
                                $isOverdue = $followUp->status !== 'completed'
                                    && $followUp->next_follow_up_date
                                    && $followUp->next_follow_up_date->lt(today());
                                $statusClass = $isOverdue ? 'overdue' : $followUp->status;
                            @endphp
                            <tr>
                                <td><span class="module-tag">{{ $followUp->targetModuleLabel() }}</span></td>
                                <td><div class="child-name">{{ $followUp->targetDisplayName() }}</div></td>
                                <td>
                                    <div class="description">
                                        <strong>{{ $followUp->category ?: 'General follow-up' }}</strong>
                                        <div class="muted">{{ $followUp->description ?: 'No description provided.' }}</div>
                                    </div>
                                </td>
                                <td>
                                    {{ $followUp->next_follow_up_date?->format('M d, Y') ?? 'Not scheduled' }}
                                    @if($isOverdue)
                                        <div class="muted" style="color: #a33d35;">Overdue</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="status status-{{ $statusClass }}">
                                        {{ $isOverdue ? 'Overdue' : ucfirst(str_replace('_', ' ', $followUp->status)) }}
                                    </span>
                                    <div class="muted">Priority: {{ ucfirst($followUp->priority) }}</div>
                                </td>
                                <td>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.follow-ups.update', $followUp) }}"
                                        class="row-controls"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="filter_status" value="{{ request('status') }}">
                                        <input type="hidden" name="filter_module" value="{{ request('module') }}">
                                        <input type="hidden" name="filter_search" value="{{ request('search') }}">
                                        <select name="assigned_to" aria-label="Assign officer">
                                            <option value="">Unassigned</option>
                                            @foreach($officers as $officer)
                                                <option
                                                    value="{{ $officer->id }}"
                                                    @selected((string) $followUp->assigned_to === (string) $officer->id)
                                                >
                                                    {{ $officer->name }} — {{ ucwords(str_replace('_', ' ', $officer->role)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select name="status" aria-label="Update status">
                                            <option value="pending" @selected($followUp->status === 'pending')>Pending</option>
                                            <option value="in_progress" @selected($followUp->status === 'in_progress')>In progress</option>
                                            <option value="completed" @selected($followUp->status === 'completed')>Completed</option>
                                        </select>
                                        <input
                                            type="date"
                                            name="next_follow_up_date"
                                            value="{{ $followUp->next_follow_up_date?->format('Y-m-d') }}"
                                            aria-label="Next follow-up date"
                                        >
                                        <textarea
                                            name="action_taken"
                                            maxlength="5000"
                                            placeholder="Record progress or action taken"
                                            aria-label="Progress notes"
                                        >{{ $followUp->action_taken }}</textarea>
                                        <button type="submit" class="button">Save changes</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination">{{ $followUps->links() }}</div>
            @endif
        </section>
    </main>
</div>
<script>
    const moduleSelect = document.getElementById('target-module');
    const targetSearch = document.getElementById('target-search');
    const targetSelect = document.getElementById('target-record');
    const targetMessage = document.getElementById('target-message');
    const targetsUrl = @json(route('admin.follow-ups.targets'));
    let searchTimer;
    let activeRequest;

    async function loadTargets() {
        const module = moduleSelect.value;
        const search = targetSearch.value.trim();
        targetSelect.replaceChildren(new Option(
            module ? 'Loading records...' : 'Select a module to load records',
            ''
        ));
        targetSelect.disabled = true;
        targetMessage.textContent = '';
        targetSearch.disabled = !module;

        if (!module) {
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }
        activeRequest = new AbortController();

        const params = new URLSearchParams({ module, search });

        try {
            const response = await fetch(`${targetsUrl}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: activeRequest.signal
            });
            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const result = await response.json();
            targetSelect.replaceChildren(new Option('Choose a related record', ''));

            for (const record of result.data) {
                targetSelect.add(new Option(record.label, record.id));
            }

            targetSelect.disabled = result.data.length === 0;
            if (result.data.length === 0) {
                targetMessage.textContent = search
                    ? 'No records match this search.'
                    : 'No records are available in this module.';
            } else if (result.data.length === 30) {
                targetMessage.textContent = 'Showing the first 30 matches. Refine your search to find another record.';
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            targetSelect.replaceChildren(new Option('Records could not be loaded', ''));
            targetMessage.textContent = 'Could not load records. Please change the search or try again.';
            console.error('Unable to load follow-up targets.', error);
        }
    }

    moduleSelect.addEventListener('change', loadTargets);
    targetSearch.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadTargets, 250);
    });

    if (moduleSelect.value) {
        loadTargets();
    }
</script>
</body>
</html>

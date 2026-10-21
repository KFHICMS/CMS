<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->title }} | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f3f8f4; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 980px; margin: 0 auto; }
        a { color: #28733a; }
        .card { margin-top: 18px; padding: 22px; background: #fff; border: 1px solid #dfe9e1; border-radius: 12px; }
        h1 { margin: 8px 0; }
        h2 { font-size: 19px; }
        .muted { color: #65756a; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { margin: 14px 0; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; color: inherit; font: inherit; }
        textarea { min-height: 110px; resize: vertical; }
        button, .button { display: inline-block; padding: 10px 14px; border: 0; border-radius: 8px; color: #fff; background: #28733a; text-decoration: none; cursor: pointer; font: inherit; }
        button.secondary, .button.secondary { color: #294432; background: #e7efe8; }
        button.danger { background: #a94343; }
        .message { margin: 14px 0; padding: 12px; border-radius: 8px; background: #edf5ee; }
        .error { color: #a94343; }
        .child { display: flex; align-items: center; gap: 14px; }
        .child img, .avatar { width: 58px; height: 58px; border-radius: 50%; object-fit: cover; background: #e1efe3; }
        .avatar { display: grid; place-items: center; font-size: 20px; font-weight: 700; color: #28733a; }
        .benefit-row { padding: 14px 0; border-top: 1px solid #e5ece6; }
        #qr-reader { width: min(100%, 440px); margin: 12px 0; border-radius: 10px; overflow: hidden; background: #101810; }
        #qr-reader[hidden] { display: none; }
        dt { color: #718075; } dd { margin: 0; }
        @media (max-width: 600px) { body { padding: 14px; } .grid { grid-template-columns: 1fr; } dl { grid-template-columns: 1fr; gap: 4px; } dd { margin-bottom: 8px; } }
    </style>
</head>
<body>
<main>
        @include('field_officer.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => $event->program->name, 'url' => route('field_officer.programs.show', $event->program)],
        ['label' => $event->title, 'url' => null],
        ['label' => $child ? 'Child '.$child->child_code : '', 'url' => null, 'id' => 'crumb-child'],
    ]])
    <section class="card">
        <p class="muted">{{ $event->program->name }} · {{ $event->event_code }}</p>
        <h1>{{ $event->title }}</h1>
        <p>{{ $event->description ?: 'No activity instructions have been provided.' }}</p>
        @if ($event->program->officers->isNotEmpty())
            <p class="muted">Assigned Field Officers: {{ $event->program->officers->pluck('name')->join(', ') }}</p>
        @endif
        <dl>
            <dt>Date</dt><dd>{{ $event->event_date->format('l, F j, Y') }}</dd>
            <dt>Time</dt><dd>{{ $event->start_time ?: 'Not set' }}{{ $event->end_time ? ' – '.$event->end_time : '' }}</dd>
            <dt>Location</dt><dd>{{ $event->location ?: 'Not specified' }}</dd>
            <dt>Activity status</dt><dd>{{ ucfirst($event->status) }}</dd>
            <dt>Attendance</dt><dd>{{ $event->enable_attendance ? 'Enabled' : 'Disabled by an administrator' }}</dd>
        </dl>
    </section>

    @if (session('success'))
        <p class="message" role="status">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div class="message error" role="alert">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if ($submission?->status === 'submitted')
        <section class="card">
            <h2>This record has been submitted</h2>
            <p>Submitted {{ $submission->submitted_at?->format('M j, Y g:i A') }}.</p>
            <a class="button" href="{{ route('field_officer.submissions.review', $submission) }}">Review submission</a>
        </section>
    @elseif (
        in_array($event->status, ['completed', 'cancelled'], true) ||
        in_array($event->program->status, ['completed', 'cancelled'], true)
    )
        <section class="card">
            <h2>Activity closed</h2>
            <p>This activity or its program is closed and is no longer accepting field records.</p>
        </section>
    @else
        <a class="button" href="#child-workspace">Start or continue activity</a>
        <section class="card" id="child-workspace">
            <h2>Identify child</h2>
            <p class="muted">Scan the secure KFHI QR code or search using the Child Code. Only an active child can be selected.</p>
            <button type="button" id="start-camera">Scan Child QR Code</button>
            <button type="button" id="stop-camera" class="secondary" hidden>Stop camera</button>
            <div id="qr-reader" hidden></div>
            <p id="scan-message" class="muted" role="status"></p>
            <form id="manual-lookup" class="grid">
                <div class="field">
                    <label for="child-code">Manual Child Code search</label>
                    <input id="child-code" name="child_code" maxlength="30" autocomplete="off" required>
                </div>
                <div class="field" style="align-self:end">
                    <button type="submit">Find Child</button>
                </div>
            </form>
        </section>

        <section class="card" id="identified-child" @if (!$child) hidden @endif>
            <h2>Identified child</h2>
            <div class="child">
                <div id="child-avatar" class="avatar">
                    @if ($child?->profile_photo)
                        <img src="{{ asset('storage/'.$child->profile_photo) }}" alt="Photo of child {{ $child->child_code }}">
                    @else
                        {{ $child ? mb_substr($child->first_name, 0, 1) : '' }}
                    @endif
                </div>
                <div>
                    <strong id="child-name">
                        {{ $child ? trim(implode(' ', array_filter([$child->first_name, $child->middle_name, $child->last_name]))) : '' }}
                    </strong>
                    <div class="muted">Child Code: <span id="child-code-display">{{ $child?->child_code }}</span></div>
                </div>
            </div>
            <p class="message" id="existing-attendance-message" @if (!$existingAttendance) hidden @endif>
                @if ($existingAttendance)
                    Attendance already recorded as <strong>{{ ucfirst($existingAttendance->status) }}</strong>. The existing record will not be duplicated.
                @endif
            </p>
        </section>

        <section class="card" id="field-record" @if (!$child) hidden @endif>
            <h2>Field activity record</h2>
            @if (!$event->enable_attendance)
                <p class="message">Attendance is disabled for this activity. Only an administrator can enable it. Benefits and observations can still be recorded.</p>
            @endif
            <form method="POST" action="{{ route('field_officer.activities.save', $event) }}" id="record-form">
                @csrf
                <input type="hidden" name="child_id" id="record-child-id" value="{{ old('child_id', $child?->id) }}">
                <div class="field">
                    <label>Activity</label>
                    <input value="{{ $event->title }}" readonly>
                </div>

                @if ($event->enable_attendance)
                    <div class="field">
                        <label for="attendance-status">Attendance</label>
                        @if ($existingAttendance)
                            <input type="hidden" name="attendance_status" value="{{ $existingAttendance->status }}">
                            <select id="attendance-status" disabled>
                                <option>{{ ucfirst($existingAttendance->status) }}</option>
                            </select>
                        @else
                            <select id="attendance-status" name="attendance_status">
                                <option value="">Choose Present or Absent</option>
                                <option value="present" @selected(old('attendance_status', $submission?->attendance_status) === 'present')>Present</option>
                                <option value="absent" @selected(old('attendance_status', $submission?->attendance_status) === 'absent')>Absent</option>
                            </select>
                        @endif
                    </div>
                @endif

                <h3>Benefits / items received</h3>
                <div id="benefit-rows">
                    @php
                        $benefitRows = old(
                            'benefits',
                            $submission?->benefits?->map(fn ($benefit) => [
                                'item_name' => $benefit->item_name,
                                'category' => $benefit->category,
                                'quantity' => $benefit->quantity,
                                'received_at' => $benefit->received_at?->format('Y-m-d'),
                                'notes' => $benefit->notes,
                            ])->all() ?? []
                        );
                    @endphp
                    @forelse ($benefitRows as $index => $benefit)
                        <div class="benefit-row">
                            <div class="grid">
                                <div class="field"><label>Item name</label><input name="benefits[{{ $index }}][item_name]" value="{{ $benefit['item_name'] ?? '' }}" maxlength="255"></div>
                                <div class="field"><label>Category</label><input name="benefits[{{ $index }}][category]" value="{{ $benefit['category'] ?? '' }}" maxlength="100"></div>
                                <div class="field"><label>Quantity</label><input type="number" min="1" max="9999" name="benefits[{{ $index }}][quantity]" value="{{ $benefit['quantity'] ?? 1 }}"></div>
                                <div class="field"><label>Date received</label><input type="date" name="benefits[{{ $index }}][received_at]" value="{{ $benefit['received_at'] ?? today()->format('Y-m-d') }}"></div>
                            </div>
                            <div class="field"><label>Item notes</label><textarea name="benefits[{{ $index }}][notes]" rows="2">{{ $benefit['notes'] ?? '' }}</textarea></div>
                            <button type="button" class="secondary remove-benefit">Remove item</button>
                        </div>
                    @empty
                        <div class="benefit-row">
                            <div class="grid">
                                <div class="field"><label>Item name</label><input name="benefits[0][item_name]" maxlength="255"></div>
                                <div class="field"><label>Category</label><input name="benefits[0][category]" maxlength="100"></div>
                                <div class="field"><label>Quantity</label><input type="number" min="1" max="9999" name="benefits[0][quantity]" value="1"></div>
                                <div class="field"><label>Date received</label><input type="date" name="benefits[0][received_at]" value="{{ today()->format('Y-m-d') }}"></div>
                            </div>
                            <div class="field"><label>Item notes</label><textarea name="benefits[0][notes]" rows="2"></textarea></div>
                            <button type="button" class="secondary remove-benefit">Remove item</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="secondary" id="add-benefit">Add another item</button>

                @php($observation = $submission?->observations?->first())
                <h3>Observation / field note</h3>
                <div class="field">
                    <label for="observation-category">Category (optional)</label>
                    <input id="observation-category" name="observation_category" maxlength="100" value="{{ old('observation_category', $observation?->category) }}">
                </div>
                <div class="field">
                    <label for="observation-note">Field note</label>
                    <textarea id="observation-note" name="observation_note" maxlength="10000" rows="5">{{ old('observation_note', $observation?->note) }}</textarea>
                </div>

                @if ($submission)
                    <button type="submit">Save draft changes</button>
                @else
                    <button type="submit">Save draft</button>
                @endif
            </form>

            @if ($submission?->status === 'draft')
                <form method="POST" action="{{ route('field_officer.submissions.submit', $submission) }}" style="margin-top:14px" onsubmit="return confirm('Submit this saved activity record? You will not be able to edit it afterward.')">
                    @csrf
                    <button type="submit" class="danger">Submit saved record</button>
                </form>
            @else
                <p class="muted">Save a draft first. You can then review and submit the saved record.</p>
            @endif
        </section>
    @endif
</main>
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
    const identifyUrl = @json(route('field_officer.activities.identify', $event));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const message = document.getElementById('scan-message');
    const reader = document.getElementById('qr-reader');
    const startButton = document.getElementById('start-camera');
    const stopButton = document.getElementById('stop-camera');
    const childPanel = document.getElementById('identified-child');
    const childIdField = document.getElementById('record-child-id');
    let scanner = null;
    let activeChildId = childIdField?.value || '';

    function resetChildRecord() {
        const form = document.getElementById('record-form');
        if (!form) return;
        form.querySelectorAll('input[name="attendance_status"][type="hidden"]').forEach((input) => input.remove());
        const attendanceSelect = document.getElementById('attendance-status');
        if (attendanceSelect) {
            attendanceSelect.disabled = false;
            attendanceSelect.name = 'attendance_status';
            attendanceSelect.value = '';
        }
        const attendanceMessage = document.getElementById('existing-attendance-message');
        if (attendanceMessage) {
            attendanceMessage.hidden = true;
            attendanceMessage.textContent = '';
        }
        const rows = document.getElementById('benefit-rows');
        if (rows) {
            rows.innerHTML = `<div class="benefit-row">
                <div class="grid">
                    <div class="field"><label>Item name</label><input name="benefits[0][item_name]" maxlength="255"></div>
                    <div class="field"><label>Category</label><input name="benefits[0][category]" maxlength="100"></div>
                    <div class="field"><label>Quantity</label><input type="number" min="1" max="9999" name="benefits[0][quantity]" value="1"></div>
                    <div class="field"><label>Date received</label><input type="date" name="benefits[0][received_at]" value="{{ today()->format('Y-m-d') }}"></div>
                </div>
                <div class="field"><label>Item notes</label><textarea name="benefits[0][notes]" rows="2"></textarea></div>
                <button type="button" class="secondary remove-benefit">Remove item</button>
            </div>`;
        }
        document.getElementById('observation-category').value = '';
        document.getElementById('observation-note').value = '';
    }

    async function identifyChild(payload) {
        message.textContent = 'Checking child access…';
        try {
            const response = await fetch(identifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Child identification failed.');
            }

            if (activeChildId && Number(activeChildId) !== Number(result.child.id)) {
                resetChildRecord();
            }
            activeChildId = String(result.child.id);
            document.getElementById('child-name').textContent = result.child.name;
            document.getElementById('child-code-display').textContent = result.child.child_code;
            childIdField.value = result.child.id;
            childPanel.hidden = false;
            document.getElementById('field-record').hidden = false;
            const avatar = document.getElementById('child-avatar');
            avatar.textContent = '';
            if (result.child.photo_url) {
                const photo = document.createElement('img');
                photo.src = result.child.photo_url;
                photo.alt = 'Photo of child ' + result.child.child_code;
                photo.addEventListener('error', () => {
                    avatar.textContent = result.child.name.slice(0, 1);
                });
                avatar.appendChild(photo);
            } else {
                avatar.textContent = result.child.name.slice(0, 1);
            }
            if (result.attendance && document.getElementById('attendance-status')) {
                const status = result.attendance.status;
                const select = document.getElementById('attendance-status');
                let hidden = document.querySelector('input[name="attendance_status"][type="hidden"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'attendance_status';
                    select.after(hidden);
                }
                hidden.value = status;
                select.removeAttribute('name');
                select.innerHTML = `<option>${status[0].toUpperCase()}${status.slice(1)}</option>`;
                select.disabled = true;
                const attendanceMessage = document.getElementById('existing-attendance-message');
                attendanceMessage.textContent = `Attendance already recorded as ${status}. The existing record will not be duplicated.`;
                attendanceMessage.hidden = false;
            }
            message.textContent = 'Child identified. The activity record is ready.';
        } catch (error) {
            message.textContent = error.message || 'Unable to identify the child. Check your connection and try again.';
        }
    }

        async function stopCamera() {
        if (scanner) {
            try { await scanner.stop(); } catch (e) { /* scanner was not running */ }
            try { scanner.clear(); } catch (e) { /* nothing to clear */ }
            scanner = null;
        }
        reader.hidden = true;
        startButton.hidden = false;
        stopButton.hidden = true;
    }

    startButton?.addEventListener('click', async () => {
        if (typeof Html5Qrcode === 'undefined') {
            message.textContent = 'The QR scanner could not be loaded. Use the manual Child Code search.';
            return;
        }
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            message.textContent = 'Camera access needs a secure (HTTPS) connection. Use the manual Child Code search.';
            return;
        }

        message.textContent = 'Starting camera…';
        reader.hidden = false;
        startButton.hidden = true;
        stopButton.hidden = false;

        let handled = false;
        scanner = new Html5Qrcode('qr-reader', {
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
            verbose: false
        });

        try {
            await scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: (width, height) => {
                        const size = Math.floor(Math.min(width, height) * 0.7);
                        return { width: size, height: size };
                    }
                },
                async (decodedText) => {
                    if (handled) return;
                    handled = true;
                    await stopCamera();
                    await identifyChild({ token: decodedText });
                },
                () => { /* no QR in this frame; keep scanning */ }
            );
            message.textContent = 'Point the camera at the child\'s QR code.';
        } catch (error) {
            await stopCamera();
            const detail = String(error?.name || error);
            message.textContent = /NotAllowed|Permission/i.test(detail)
                ? 'Camera permission was denied. Allow camera access or use the manual Child Code search.'
                : 'Camera unavailable. Use the manual Child Code search instead.';
        }
    });

    stopButton?.addEventListener('click', stopCamera);
    window.addEventListener('pagehide', stopCamera);
    document.getElementById('manual-lookup')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const code = document.getElementById('child-code').value.trim();
        if (code) identifyChild({ child_code: code });
    });

    document.getElementById('add-benefit')?.addEventListener('click', () => {
        const rows = document.getElementById('benefit-rows');
        const index = rows.querySelectorAll('.benefit-row').length;
        if (index >= 20) {
            message.textContent = 'A maximum of 20 benefit items can be recorded per activity.';
            return;
        }
        const row = document.createElement('div');
        row.className = 'benefit-row';
        row.innerHTML = `
            <div class="grid">
                <div class="field"><label>Item name</label><input name="benefits[${index}][item_name]" maxlength="255"></div>
                <div class="field"><label>Category</label><input name="benefits[${index}][category]" maxlength="100"></div>
                <div class="field"><label>Quantity</label><input type="number" min="1" max="9999" name="benefits[${index}][quantity]" value="1"></div>
                <div class="field"><label>Date received</label><input type="date" name="benefits[${index}][received_at]" value="{{ today()->format('Y-m-d') }}"></div>
            </div>
            <div class="field"><label>Item notes</label><textarea name="benefits[${index}][notes]" rows="2"></textarea></div>
            <button type="button" class="secondary remove-benefit">Remove item</button>`;
        rows.append(row);
    });

    document.getElementById('benefit-rows')?.addEventListener('click', (event) => {
        if (event.target.matches('.remove-benefit')) {
            event.target.closest('.benefit-row').remove();
        }
    });
</script>

<script>
(() => {
    const code = document.getElementById('child-code-display');
    const crumb = document.getElementById('crumb-child');
    if (!code || !crumb) return;

    const sync = () => {
        const value = code.textContent.trim();
        crumb.textContent = value ? 'Child ' + value : '';
        crumb.closest('li').hidden = !value;
    };

    new MutationObserver(sync).observe(code, { childList: true, characterData: true, subtree: true });
    sync();
})();
</script>
</body>
</html>

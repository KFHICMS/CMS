@extends('enumerator.layouts.app', ['active' => 'dashboard'])

@section('title', 'Attendance')
@section('heading', 'Attendance')
@section('subheading', $event->title.' · '.$event->event_date->format('M j, Y'))

@section('content')
    <p style="margin-top:18px">
        <a href="{{ route('enumerator.dashboard') }}">Dashboard</a> ›
        <a href="{{ route('enumerator.events.show', $event) }}">{{ $event->title }}</a> › Attendance
    </p>

    @if ($unavailable)
        <p class="notice" role="alert">{{ $unavailable[1] }}</p>
    @else
        <section class="section">
            <div class="grid">
                <article class="card">
                    <h3>Scan child QR code</h3>
                    <p class="muted">Allow camera access when asked. On a phone, the page must be opened over HTTPS.</p>
                    <button type="button" id="start-scan" class="btn">Scan Child QR Code</button>
                    <button type="button" id="stop-scan" class="btn secondary" hidden>Stop camera</button>
                    <div id="qr-reader" hidden style="margin-top:12px;max-width:440px"></div>
                    <hr>
                    <label for="child-code">Or enter the Child Code</label>
                    <input type="text" id="child-code" maxlength="30" autocomplete="off">
                    <p><button type="button" id="lookup" class="btn secondary">Find child</button></p>
                    <p id="scan-message" role="status" aria-live="polite" class="muted"></p>
                </article>

                <article class="card" id="child-panel" hidden>
                    <h3>Identified child</h3>
                    <p><strong id="child-code-out"></strong></p>
                    <p id="child-name-out"></p>
                    <p id="existing-notice" class="notice" hidden></p>
                    <form id="attendance-form">
                        <fieldset style="border:0;padding:0;margin:0 0 12px">
                            <legend class="muted">Attendance status</legend>
                            <label><input type="radio" name="status" value="present" checked> Present</label>
                            <label style="margin-left:16px"><input type="radio" name="status" value="absent"> Absent</label>
                        </fieldset>
                        <button type="submit" id="save" class="btn">Save attendance</button>
                    </form>
                    <p><button type="button" id="another" class="btn secondary">Scan another child</button></p>
                </article>
            </div>
        </section>
    @endif

    <section class="section">
        <h2>My recent attendance records</h2>
        <div class="card">
            <p id="no-records" class="muted" @if ($recent->isNotEmpty()) hidden @endif>You have not recorded attendance for this event yet.</p>
            <table id="records" @if ($recent->isEmpty()) hidden @endif>
                <thead><tr><th>Child code</th><th>Status</th><th>Time</th></tr></thead>
                <tbody>
                @foreach ($recent as $record)
                    <tr>
                        <td>{{ $record->child?->child_code }}</td>
                        <td>{{ ucfirst($record->status) }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($record->attendance_time)->format('g:i A') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection

@if (! $unavailable)
@push('scripts')
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const identifyUrl = @json(route('enumerator.events.attendance.identify', $event));
    const storeUrl = @json(route('enumerator.events.attendance.store', $event));

    const message = document.getElementById('scan-message');
    const startButton = document.getElementById('start-scan');
    const stopButton = document.getElementById('stop-scan');
    const reader = document.getElementById('qr-reader');
    const lookupButton = document.getElementById('lookup');
    const codeInput = document.getElementById('child-code');
    const panel = document.getElementById('child-panel');
    const form = document.getElementById('attendance-form');
    const saveButton = document.getElementById('save');
    const notice = document.getElementById('existing-notice');
    let scanner = null;

    async function post(url, payload) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(payload)
            });
            let data = {};
            try { data = await response.json(); } catch (e) { /* empty body */ }
            return { ok: response.ok, data };
        } catch (e) {
            return { ok: false, data: { message: 'Your request could not be sent. Please check your connection and try again.' } };
        }
    }

    function say(text, isError = false) {
        message.textContent = text;
        message.style.color = isError ? '#a12626' : '';
    }

    function errorText(data, fallback) {
        if (data && data.message && !data.errors) return data.message;
        const first = data && data.errors ? Object.values(data.errors)[0] : null;
        return (first && first[0]) || (data && data.message) || fallback;
    }

    function resetPanel() {
        panel.hidden = true;
        notice.hidden = true;
        form.hidden = false;
        document.getElementById('child-code-out').textContent = '';
        document.getElementById('child-name-out').textContent = '';
        codeInput.value = '';
    }

    function addRecord(record) {
        const body = document.querySelector('#records tbody');
        const row = document.createElement('tr');
        [record.child_code, record.status.charAt(0).toUpperCase() + record.status.slice(1), record.time].forEach((value) => {
            const cell = document.createElement('td');
            cell.textContent = value;
            row.appendChild(cell);
        });
        body.prepend(row);
        document.getElementById('records').hidden = false;
        document.getElementById('no-records').hidden = true;
    }

    async function identify(payload) {
        say('Checking…');
        const { ok, data } = await post(identifyUrl, payload);
        if (!ok) {
            resetPanel();
            say(errorText(data, 'Could not identify the child. Please try again.'), true);
            return;
        }
        document.getElementById('child-code-out').textContent = data.child.child_code;
        document.getElementById('child-name-out').textContent = data.child.name;
        panel.hidden = false;
        if (data.attendance) {
            notice.textContent = 'Attendance has already been recorded for this child in this event (' + data.attendance.status + ').';
            notice.hidden = false;
            form.hidden = true;
        } else {
            notice.hidden = true;
            form.hidden = false;
        }
        say('Child identified.');
    }

    async function stopCamera() {
        if (scanner) {
            try { await scanner.stop(); } catch (e) { /* not running */ }
            try { scanner.clear(); } catch (e) { /* nothing to clear */ }
            scanner = null;
        }
        reader.hidden = true;
        startButton.hidden = false;
        stopButton.hidden = true;
    }

    startButton.addEventListener('click', async () => {
        if (typeof Html5Qrcode === 'undefined') {
            say('The QR scanner could not be loaded. Use the manual Child Code search.', true);
            return;
        }
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            say('Camera access needs a secure (HTTPS) connection. Use the manual Child Code search.', true);
            return;
        }

        say('Starting camera…');
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
                    await identify({ token: decodedText });
                },
                () => { /* no QR in this frame */ }
            );
            say("Point the camera at the child's QR code.");
        } catch (error) {
            await stopCamera();
            const detail = String(error?.name || error);
            say(/NotAllowed|Permission/i.test(detail)
                ? 'Camera permission was denied. Allow camera access or use the manual Child Code search.'
                : 'Camera unavailable. Use the manual Child Code search instead.', true);
        }
    });

    stopButton.addEventListener('click', stopCamera);
    window.addEventListener('pagehide', stopCamera);

    lookupButton.addEventListener('click', () => {
        const code = codeInput.value.trim();
        if (!code) { say('Enter a Child Code first.', true); return; }
        identify({ child_code: code });
    });

    document.getElementById('another').addEventListener('click', () => {
        resetPanel();
        say('');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveButton.disabled = true;
        const status = form.elements['status'].value;
        const { ok, data } = await post(storeUrl, { status });
        if (ok) {
            addRecord(data.record);
            resetPanel();
            say(data.message + ' (' + data.record.child_code + ')');
        } else {
            if (data && data.message && /already been recorded/.test(data.message)) { resetPanel(); }
            say(errorText(data, 'Your submission could not be saved. Please check your connection and try again.'), true);
        }
        saveButton.disabled = false;
    });
})();
</script>
@endpush
@endif
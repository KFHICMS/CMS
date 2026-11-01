@extends('enumerator.layouts.app', ['active' => 'dashboard'])

@section('title', $questionnaire->title)
@section('heading', $questionnaire->title)
@section('subheading', $event->title.' · '.$event->event_date->format('M j, Y'))

@section('content')
    <p style="margin-top:18px">
        <a href="{{ route('enumerator.dashboard') }}">Dashboard</a> ›
        <a href="{{ route('enumerator.events.show', $event) }}">{{ $event->title }}</a> › {{ $questionnaire->title }}
    </p>

    @if ($questionnaire->description)
        <section class="card"><h3>Instructions</h3><p>{{ $questionnaire->description }}</p></section>
    @endif

    @if ($unavailable)
        <p class="notice" role="alert">{{ $unavailable[1] }}</p>
    @else
        <section class="section">
            <div class="grid">
                <article class="card">
                    <h3>Identify the child</h3>
                    <p class="muted">Scan the child's QR code, or enter the Child Code. On a phone, the page must be opened over HTTPS.</p>
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
                    <a id="continue" class="btn" href="#">Continue to questionnaire</a>
                </article>
            </div>
        </section>
    @endif

    <section class="section">
        <h2>My responses to this questionnaire</h2>
        <div class="card">
            @if ($mine->isEmpty())
                <p class="muted">You have not submitted any responses to this questionnaire yet.</p>
            @else
                <table>
                    <thead><tr><th>Child code</th><th>Submitted</th></tr></thead>
                    <tbody>
                    @foreach ($mine as $response)
                        <tr>
                            <td>{{ $response->child?->child_code }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($response->submitted_at)->format('M j, Y g:i A') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection

@if (! $unavailable)
@push('scripts')
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const identifyUrl = @json(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]));

    const message = document.getElementById('scan-message');
    const startButton = document.getElementById('start-scan');
    const stopButton = document.getElementById('stop-scan');
    const reader = document.getElementById('qr-reader');
    const codeInput = document.getElementById('child-code');
    const panel = document.getElementById('child-panel');
    let scanner = null;

    function say(text, isError = false) {
        message.textContent = text;
        message.style.color = isError ? '#a12626' : '';
    }

    async function identify(payload) {
        say('Checking…');
        panel.hidden = true;
        let response;
        let data = {};
        try {
            response = await fetch(identifyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload)
            });
            try { data = await response.json(); } catch (e) { /* empty body */ }
        } catch (e) {
            say('Your request could not be sent. Please check your connection and try again.', true);
            return;
        }

        if (!response.ok) {
            const first = data.errors ? Object.values(data.errors)[0] : null;
            say((first && first[0]) || data.message || 'Could not identify the child. Please try again.', true);
            return;
        }

        document.getElementById('child-code-out').textContent = data.child.child_code;
        document.getElementById('child-name-out').textContent = data.child.name;
        document.getElementById('continue').href = data.form_url;
        panel.hidden = false;
        say('Child identified. Check the details, then continue.');
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
                { fps: 10, qrbox: (w, h) => { const s = Math.floor(Math.min(w, h) * 0.7); return { width: s, height: s }; } },
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
            say(/NotAllowed|Permission/i.test(String(error?.name || error))
                ? 'Camera permission was denied. Allow camera access or use the manual Child Code search.'
                : 'Camera unavailable. Use the manual Child Code search instead.', true);
        }
    });

    stopButton.addEventListener('click', stopCamera);
    window.addEventListener('pagehide', stopCamera);

    document.getElementById('lookup').addEventListener('click', () => {
        const code = codeInput.value.trim();
        if (!code) { say('Enter a Child Code first.', true); return; }
        identify({ child_code: code });
    });
})();
</script>
@endpush
@endif
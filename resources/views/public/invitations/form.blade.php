<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>{{ $questionnaire->title }} | KFHI</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f8f4; color: #243b2c; font-family: Arial, sans-serif; line-height: 1.45; }
        main { max-width: 640px; margin: 0 auto; padding: 18px 16px 60px; }
        .brand { font-weight: 700; color: #236b3b; margin: 0; }
        h1 { margin: 6px 0; font-size: 24px; }
        .muted { color: #65756a; margin: 4px 0; }
        form, .box { margin-top: 18px; padding: 18px; background: #fff; border: 1px solid #dfe9e1; border-radius: 12px; }
        .q { margin: 0 0 20px; }
        .q-label { display: block; margin-bottom: 8px; font-weight: 700; }
        .req { color: #a12626; margin-left: 4px; }
        .sr { position: absolute; left: -9999px; }
        input[type=text], input[type=number], input[type=date], input[type=time], select, textarea {
            width: 100%; padding: 12px; font: inherit; border: 1px solid #bdcbbf; border-radius: 8px; background: #fff;
        }
        .choice { display: flex; align-items: center; gap: 10px; padding: 10px 12px; margin: 6px 0; border: 1px solid #dfe9e1; border-radius: 8px; }
        .choice input { width: 20px; height: 20px; flex: none; }
        .rating { display: flex; gap: 8px; flex-wrap: wrap; }
        .rating .choice { flex: 1 1 56px; justify-content: center; }
        .err { margin-top: 6px; color: #a12626; font-size: 14px; }
        .has-error input, .has-error select, .has-error textarea { border-color: #a12626; }
        .error-box { margin-top: 18px; padding: 14px; border-radius: 10px; background: #fdecec; border: 1px solid #efb9b9; color: #8a2020; }
        button { padding: 14px; border: 0; border-radius: 10px; background: #28733a; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        button.full { width: 100%; }
        button.secondary { background: #e8f4ea; color: #236b3b; }
        button.link { background: none; color: #236b3b; text-decoration: underline; padding: 8px 0; font-weight: 400; }
        button[disabled] { opacity: .6; cursor: wait; }
        .child-box { padding: 14px; border: 1px solid #c9dfce; background: #f6fbf7; border-radius: 10px; }
        #qr-reader { margin-top: 12px; max-width: 440px; }
        #scan-message { margin: 8px 0 0; font-size: 14px; }
    </style>
</head>
<body>
<main>
    <p class="brand">KFHI</p>
    <h1>{{ $questionnaire->title }}</h1>
    <p class="muted">{{ $event->title }} · {{ $event->event_date->format('M j, Y') }}</p>
    @if ($questionnaire->description)
        <p>{{ $questionnaire->description }}</p>
    @endif

    @if ($errors->any())
        <div class="error-box" role="alert">
            <strong>Please check your answers.</strong> Some answers are missing or not valid.
        </div>
    @endif

        <form method="POST" action="{{ route('public.invitations.store', $token) }}" id="invite-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
        <input type="hidden" name="child_mode" id="child-mode" value="{{ old('child_mode') }}">
        <input type="hidden" name="child_proof" id="child-proof" value="{{ old('child_proof') }}">
        <input type="hidden" id="child-label" value="{{ old('child_label') }}">

        <div class="q @if ($errors->has('child_mode') || $errors->has('child_proof')) has-error @endif">
            <p class="q-label">Which child is this about? <span class="req" aria-hidden="true">*</span></p>

            <div class="child-box" id="child-chosen" hidden>
                <div id="child-chosen-text"></div>
                <button type="button" class="link" id="change-child">Change</button>
            </div>

            <div id="child-picker">
                <button type="button" class="secondary full" id="start-scan">Scan child's QR code</button>
                <button type="button" class="secondary full" id="stop-scan" hidden style="margin-top:8px">Stop camera</button>
                <div id="qr-reader" hidden></div>
                <p id="scan-message" role="status" aria-live="polite" class="muted"></p>
                <button type="button" class="link" id="new-child">This child has no QR card yet (new child)</button>
            </div>

            @error('child_mode') <div class="err" role="alert">Choose a child by scanning their QR code, or choose new child.</div> @enderror
            @error('child_proof') <div class="err" role="alert">{{ $message }}</div> @enderror
        </div>

        <div class="q @error('respondent_name') has-error @enderror">
            <label class="q-label" for="respondent_name">Your name <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="respondent_name" name="respondent_name" maxlength="100" autocomplete="name"
                   value="{{ old('respondent_name') }}" required>
            @error('respondent_name') <div class="err" role="alert">{{ $message }}</div> @enderror
        </div>

        @include('public.invitations.partials.questions', ['questions' => $questions])

        <div class="q @error('photo') has-error @enderror">
            <label class="q-label" for="photo">Child's photograph <span class="req" aria-hidden="true">*</span></label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
            <img id="photo-preview" alt="Preview of the selected photograph" hidden
                 style="margin-top:10px;max-width:160px;border-radius:8px">
            <p id="photo-note" class="muted" style="font-size:14px"></p>
            @if ($errors->any() && ! $errors->has('photo'))
                <p class="muted" style="font-size:14px">Please choose the photograph again. Phones do not keep it after the page reloads.</p>
            @endif
            @error('photo') <div class="err" role="alert">{{ $message }}</div> @enderror
        </div>

        @error('submission_key') <div class="err" role="alert">Please reload the page and try again.</div> @enderror

        <button type="submit" class="full" id="submit-btn">Submit</button>
    </form>
</main>

<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
(() => {
    const identifyUrl = @json(route('public.invitations.identify', $token));
    const form = document.getElementById('invite-form');
    const submitButton = document.getElementById('submit-btn');
    const modeInput = document.getElementById('child-mode');
    const proofInput = document.getElementById('child-proof');
    const labelInput = document.getElementById('child-label');
    const picker = document.getElementById('child-picker');
    const chosen = document.getElementById('child-chosen');
    const chosenText = document.getElementById('child-chosen-text');
    const startButton = document.getElementById('start-scan');
    const stopButton = document.getElementById('stop-scan');
    const reader = document.getElementById('qr-reader');
    const message = document.getElementById('scan-message');
    let scanner = null;

    function say(text, isError = false) {
        message.textContent = text;
        message.style.color = isError ? '#a12626' : '';
    }

    function showChosen(label) {
        chosenText.textContent = label;
        labelInput.value = label;
        chosen.hidden = false;
        picker.hidden = true;
    }

    function resetChoice() {
        modeInput.value = '';
        proofInput.value = '';
        labelInput.value = '';
        chosen.hidden = true;
        picker.hidden = false;
        say('');
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

    async function identify(qr) {
        say('Checking…');
        let response;
        let data = {};
        try {
            response = await fetch(identifyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ qr })
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

        modeInput.value = 'existing';
        proofInput.value = data.proof;
        showChosen('Child identified: ' + data.child.child_code);
        say('');
    }

    startButton.addEventListener('click', async () => {
        if (typeof Html5Qrcode === 'undefined') {
            say('The QR scanner could not be loaded. Please reload the page.', true);
            return;
        }
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            say('Camera access needs a secure (HTTPS) connection.', true);
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
                    await identify(decodedText);
                },
                () => { /* no QR in this frame */ }
            );
            say("Point the camera at the child's QR code.");
        } catch (error) {
            await stopCamera();
            say(/NotAllowed|Permission/i.test(String(error?.name || error))
                ? 'Camera permission was denied. Allow camera access and try again.'
                : 'Camera unavailable on this device.', true);
        }
    });

    stopButton.addEventListener('click', stopCamera);
    window.addEventListener('pagehide', stopCamera);

    document.getElementById('new-child').addEventListener('click', async () => {
        await stopCamera();
        modeInput.value = 'new';
        proofInput.value = '';
        showChosen('New child (no QR card)');
    });

    document.getElementById('change-child').addEventListener('click', resetChoice);

    // Restore the choice after a validation error.
    if (modeInput.value === 'new') {
        showChosen('New child (no QR card)');
    } else if (modeInput.value === 'existing' && proofInput.value) {
        showChosen(labelInput.value || 'Child identified');
    }

        // Photo: shrink on the phone before upload (faster on mobile data, and removes location data).
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    const photoNote = document.getElementById('photo-note');
    const MAX_SIDE = 1600;

    function sizeText(bytes) {
        return bytes > 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    photoInput.addEventListener('change', async () => {
        const file = photoInput.files[0];
        photoPreview.hidden = true;
        photoNote.textContent = '';
        if (!file) return;

        try {
            const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
            const scale = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(bitmap.width * scale);
            canvas.height = Math.round(bitmap.height * scale);
            canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
            if (blob) {
                const transfer = new DataTransfer();
                transfer.items.add(new File([blob], 'photo.jpg', { type: 'image/jpeg' }));
                photoInput.files = transfer.files;
            }
        } catch (e) {
            // Keep the original file; the server checks and cleans it as well.
        }

        const chosen = photoInput.files[0];
        photoPreview.src = URL.createObjectURL(chosen);
        photoPreview.hidden = false;
        photoNote.textContent = 'Photo ready (' + sizeText(chosen.size) + ').';
    });

    form.addEventListener('submit', (event) => {
        if (!modeInput.value) {
            event.preventDefault();
            say("Scan the child's QR code, or choose new child, before submitting.", true);
            picker.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting…';
        // If the network drops, allow another try. A retry carries the same submission key,
        // so it can never create a duplicate record.
        setTimeout(() => { submitButton.disabled = false; submitButton.textContent = 'Submit'; }, 20000);
    });
})();
</script>
</body>
</html>
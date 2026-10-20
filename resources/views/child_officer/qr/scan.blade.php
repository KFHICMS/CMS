<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scan QR | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f8f3;
            color: #1f2937;
            min-height: 100vh;
        }

        .header {
            background: #ffffff;
            border-bottom: 1px solid #dfe9e2;
            padding: 18px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-size: 20px;
            font-weight: 700;
            color: #2e7d32;
        }

        .role {
            font-size: 14px;
            color: #64748b;
        }

        .container {
            width: min(1000px, 94%);
            margin: 35px auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            color: #2e7d32;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .page-title {
            margin-bottom: 8px;
            font-size: 28px;
            color: #1f2937;
        }

        .page-description {
            color: #64748b;
            margin-bottom: 25px;
        }

        .scanner-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.08);
            border: 1px solid #e2ebe5;
        }

        .scanner-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .scanner-header h2 {
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .scanner-header p {
            color: #64748b;
            font-size: 14px;
        }

        #reader {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 16px;
        }

        .scanner-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2e7d32;
            color: white;
        }

        .btn-primary:hover {
            background: #256b29;
        }

        .btn-secondary {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .btn-secondary:hover {
            background: #d7ecd9;
        }

        .status {
            max-width: 520px;
            margin: 20px auto 0;
            padding: 14px;
            border-radius: 10px;
            background: #f1f8f3;
            color: #52635a;
            text-align: center;
            font-size: 14px;
        }

        .result-card {
            display: none;
            max-width: 520px;
            margin: 25px auto 0;
            padding: 22px;
            border-radius: 14px;
            background: #f7fbf8;
            border: 1px solid #cde3d1;
        }

        .result-card h3 {
            color: #2e7d32;
            margin-bottom: 15px;
        }

        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 9px 0;
            border-bottom: 1px solid #e5eee7;
        }

        .result-row:last-of-type {
            border-bottom: none;
        }

        .result-label {
            color: #64748b;
            font-size: 13px;
        }

        .result-value {
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        /* Result action buttons */
        .result-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e2ebe4;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 16px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s ease;
            flex: 1;
            min-width: 140px;
            text-align: center;
        }

        .action-btn.primary {
            background: #2e7d32;
            color: #ffffff;
        }

        .action-btn.primary:hover {
            background: #256b29;
        }

        .action-btn.secondary {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .action-btn.secondary:hover {
            background: #d7ecd9;
        }

        .action-btn.outline {
            background: #ffffff;
            color: #2e7d32;
            border: 1px solid #a9c9ad;
        }

        .action-btn.outline:hover {
            background: #f1f8f2;
        }

        @media (max-width: 650px) {

            .header {
                padding: 15px 18px;
            }

            .container {
                margin: 25px auto;
            }

            .scanner-card {
                padding: 20px;
            }

            .page-title {
                font-size: 23px;
            }

            .result-actions {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                flex: none;
            }
        }
    </style>
</head>

<body>

<header class="header">

    <div class="brand">
        KFHI-CMS
    </div>

    <div class="role">
        Child Officer: {{ auth()->user()->name }}
    </div>

</header>


<main class="container">

    <a
        href="{{ route('child_officer.dashboard') }}"
        class="back-btn"
    >
        ← Back to Dashboard
    </a>


    <h1 class="page-title">
        Scan Child QR Code
    </h1>

    <p class="page-description">
        Scan a child's secure QR code to identify the registered child.
    </p>


    <div class="scanner-card">

        <div class="scanner-header">

            <h2>
                QR Scanner
            </h2>

            <p>
                Allow camera access and position the child's QR code
                inside the scanning area.
            </p>

        </div>


        <!-- QR Reader -->
        <div id="reader"></div>


        <!-- Scanner Buttons -->
        <div class="scanner-actions">

            <button
                type="button"
                class="btn btn-primary"
                id="startScanner"
            >
                Start Scanner
            </button>

            <button
                type="button"
                class="btn btn-secondary"
                id="stopScanner"
            >
                Stop Scanner
            </button>

        </div>


        <!-- Scanner Status -->
        <div
            class="status"
            id="scannerStatus"
        >
            Scanner is ready.
        </div>


        <!-- Child Result -->
        <div
            class="result-card"
            id="resultCard"
        >

            <h3>
                Child Identified
            </h3>


            <div class="result-row">

                <span class="result-label">
                    Child Code
                </span>

                <span
                    class="result-value"
                    id="childCode"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Child Name
                </span>

                <span
                    class="result-value"
                    id="childName"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Gender
                </span>

                <span
                    class="result-value"
                    id="childGender"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Date of Birth
                </span>

                <span
                    class="result-value"
                    id="childDob"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    School
                </span>

                <span
                    class="result-value"
                    id="childSchool"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Grade
                </span>

                <span
                    class="result-value"
                    id="childGrade"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    GN Division
                </span>

                <span
                    class="result-value"
                    id="childGn"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    District
                </span>

                <span
                    class="result-value"
                    id="childDistrict"
                >
                    -
                </span>

            </div>


            <div class="result-row">

                <span class="result-label">
                    Status
                </span>

                <span
                    class="result-value"
                    id="childStatus"
                >
                    -
                </span>

            </div>


            <!-- Child Officer Actions -->
            <div class="result-actions">

                <a
                    href="#"
                    id="editChildBtn"
                    class="action-btn primary"
                >
                    Edit Child Information
                </a>


                <a
                    href="#"
                    id="addInfoBtn"
                    class="action-btn secondary"
                >
                    Add New Information
                </a>


                <a
                    href="#"
                    id="viewChildBtn"
                    class="action-btn outline"
                >
                    View Full Child Profile
                </a>

            </div>

        </div>

    </div>

</main>


<script
    src="https://unpkg.com/html5-qrcode"
    type="text/javascript"
></script>


<script>

    let scanner = null;
    let scannerRunning = false;
    let processingQR = false;

    const statusBox =
        document.getElementById('scannerStatus');

    const resultCard =
        document.getElementById('resultCard');


    /*
    |--------------------------------------------------------------------------
    | Update Scanner Status
    |--------------------------------------------------------------------------
    */

    function updateStatus(message) {

        statusBox.textContent = message;

    }


    /*
    |--------------------------------------------------------------------------
    | Lookup QR Code
    |--------------------------------------------------------------------------
    */

    async function lookupQRCode(decodedText) {

        if (processingQR) {
            return;
        }

        processingQR = true;

        updateStatus(
            "QR code detected. Verifying..."
        );


        try {

            const response = await fetch(
                "{{ route('child_officer.qr.lookup') }}",
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN":
                            "{{ csrf_token() }}"
                    },

                    body: JSON.stringify({
                        token: decodedText
                    })
                }
            );


            const data = await response.json();


            /*
            |--------------------------------------------------------------------------
            | Invalid QR
            |--------------------------------------------------------------------------
            */

            if (!response.ok || !data.success) {

                resultCard.style.display = "none";

                updateStatus(
                    data.message ||
                    "Invalid or inactive QR code."
                );

                processingQR = false;

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Child Data
            |--------------------------------------------------------------------------
            */

            const child = data.child;


            /*
            |--------------------------------------------------------------------------
            | Build Child Officer URLs
            |--------------------------------------------------------------------------
            */

            const editChildUrl =
                "{{ route(
                    'child_officer.children.edit',
                    ['child' => '__CHILD_ID__']
                ) }}";

            const addInfoUrl =
                "{{ route(
                    'child_officer.child_information.create',
                    ['child' => '__CHILD_ID__']
                ) }}";

            const viewChildUrl =
                "{{ route(
                    'child_officer.children.show',
                    ['child' => '__CHILD_ID__']
                ) }}";


            /*
            |--------------------------------------------------------------------------
            | Set Action Button URLs
            |--------------------------------------------------------------------------
            */

            document.getElementById('editChildBtn').href =
                editChildUrl.replace(
                    '__CHILD_ID__',
                    child.id
                );


            document.getElementById('addInfoBtn').href =
                addInfoUrl.replace(
                    '__CHILD_ID__',
                    child.id
                );


            document.getElementById('viewChildBtn').href =
                viewChildUrl.replace(
                    '__CHILD_ID__',
                    child.id
                );


            /*
            |--------------------------------------------------------------------------
            | Display Child Information
            |--------------------------------------------------------------------------
            */

            document.getElementById('childCode').textContent =
                child.child_code || '-';


            document.getElementById('childName').textContent =
                child.full_name || '-';


            document.getElementById('childGender').textContent =
                child.gender || '-';


            document.getElementById('childDob').textContent =
                child.date_of_birth || '-';


            document.getElementById('childSchool').textContent =
                child.school_name || '-';


            document.getElementById('childGrade').textContent =
                child.grade || '-';


            document.getElementById('childGn').textContent =
                child.gn_division || '-';


            document.getElementById('childDistrict').textContent =
                child.district || '-';


            document.getElementById('childStatus').textContent =
                child.status || '-';


            /*
            |--------------------------------------------------------------------------
            | Show Result
            |--------------------------------------------------------------------------
            */

            resultCard.style.display = "block";


            updateStatus(
                "Child identified successfully."
            );


            /*
            |--------------------------------------------------------------------------
            | Stop Scanner
            |--------------------------------------------------------------------------
            */

            await stopScanner();


        } catch (error) {

            console.error(error);

            updateStatus(
                "Unable to verify the QR code. Please try again."
            );

        } finally {

            processingQR = false;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Start Scanner
    |--------------------------------------------------------------------------
    */

    function startScanner() {

        if (scannerRunning) {
            return;
        }


        resultCard.style.display = "none";


        scanner =
            new Html5Qrcode("reader");


        scanner.start(
            {
                facingMode: "environment"
            },
            {
                fps: 10,

                qrbox: {
                    width: 250,
                    height: 250
                }
            },

            function(decodedText) {

                lookupQRCode(decodedText);

            },

            function(errorMessage) {

                // Normal scanning errors are ignored.

            }

        )
        .then(function() {

            scannerRunning = true;

            updateStatus(
                "Scanner started. Point the camera at the QR code."
            );

        })
        .catch(function(error) {

            console.error(error);

            scannerRunning = false;

            updateStatus(
                "Unable to access the camera. Please allow camera permission."
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Stop Scanner
    |--------------------------------------------------------------------------
    */

    async function stopScanner() {

        if (!scanner || !scannerRunning) {
            return;
        }


        try {

            await scanner.stop();

            scanner.clear();

            scannerRunning = false;

            updateStatus(
                "Scanner stopped."
            );

        } catch (error) {

            console.error(error);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Button Events
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('startScanner')
        .addEventListener(
            'click',
            startScanner
        );


    document
        .getElementById('stopScanner')
        .addEventListener(
            'click',
            stopScanner
        );

</script>

</body>
</html>
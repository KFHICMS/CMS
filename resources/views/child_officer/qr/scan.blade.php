@extends('layouts.app', ['active' => 'scan'])

@section('title', 'Scan QR')
@section('heading', 'Scan QR')
@section('subheading', 'Scan a QR code to find a child record.')

@section('content')


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
@endsection

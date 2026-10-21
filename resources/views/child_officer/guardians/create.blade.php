<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Add Guardian | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f8f5;
            color: #26352b;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 35px 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #245b2a;
        }

        .header p {
            margin: 0;
            color: #68756c;
        }

        .form-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(40, 80, 45, 0.08);
            border: 1px solid #e3ebe5;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #34453a;
        }

        .required {
            color: #d32f2f;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #d5dfd7;
            border-radius: 10px;
            padding: 12px 13px;
            font-size: 14px;
            background: #fbfdfb;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #66a86d;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.10);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Child Search */

        .child-search-wrapper {
            position: relative;
        }

        .search-loading {
            position: absolute;
            right: 13px;
            top: 39px;
            color: #777;
            font-size: 12px;
            display: none;
        }

        .child-results {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            z-index: 1000;
            background: #ffffff;
            border: 1px solid #d5dfd7;
            border-radius: 10px;
            margin-top: 5px;
            max-height: 260px;
            overflow-y: auto;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10);
            display: none;
        }

        .child-result {
            padding: 12px 14px;
            cursor: pointer;
            border-bottom: 1px solid #edf1ed;
        }

        .child-result:last-child {
            border-bottom: none;
        }

        .child-result:hover {
            background: #e8f5e9;
        }

        .child-result-name {
            font-weight: 700;
            color: #245b2a;
            margin-bottom: 4px;
        }

        .child-result-info {
            font-size: 12px;
            color: #6b756d;
        }

        .no-results {
            padding: 14px;
            color: #777;
            font-size: 13px;
        }

        .selected-child {
            margin-top: 10px;
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 10px;
            padding: 13px;
            display: none;
        }

        .selected-child strong {
            color: #25652b;
        }

        .change-child {
            float: right;
            color: #2e7d32;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }

        .checkbox-row input {
            width: auto;
        }

        .checkbox-row label {
            margin: 0;
            font-weight: 500;
        }

        .error-box {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .error-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #edf1ed;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-cancel {
            background: #edf1ed;
            color: #435247;
        }

        .btn-save {
            background: #2e7d32;
            color: #ffffff;
        }

        .btn-save:hover {
            background: #25652a;
        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .form-card {
                padding: 20px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                text-align: center;
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <a
        href="{{ route('child_officer.dashboard') }}"
        class="back-link"
    >
        ← Back to Dashboard
    </a>

    <div class="header">

        <h1>
            Add Guardian
        </h1>

        <p>
            Search and select a child, then enter the guardian information.
        </p>

    </div>

    @if ($errors->any())

        <div class="error-box">

            <ul>

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <div class="form-card">

        <form
            action="{{ route('child_officer.guardians.store') }}"
            method="POST"
            id="guardianForm"
        >

            @csrf

            <div class="form-grid">

                <!-- Child Selection -->

                <div class="form-group full child-search-wrapper">

                    <label>
                        Search & Select Child <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="childSearch"
                        placeholder="Search by Child Code or Child Name..."
                        autocomplete="off"
                    >

                    <span
                        class="search-loading"
                        id="searchLoading"
                    >
                        Searching...
                    </span>

                    <input
                        type="hidden"
                        name="child_id"
                        id="childId"
                        value="{{ old('child_id') }}"
                    >

                    <div
                        class="child-results"
                        id="childResults"
                    ></div>

                    <div
                        class="selected-child"
                        id="selectedChild"
                    >
                        <span class="change-child" id="changeChild">
                            Change
                        </span>

                        <strong>Selected Child:</strong>

                        <div id="selectedChildDetails"></div>
                    </div>

                </div>


                <!-- Guardian Full Name -->

                <div class="form-group">

                    <label>
                        Guardian Full Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        required
                    >

                </div>


                <!-- Relationship -->

                <div class="form-group">

                    <label>
                        Relationship
                        <span class="required">*</span>
                    </label>

                    <select
                        name="relationship"
                        required
                    >

                        <option value="">
                            Select Relationship
                        </option>

                        <option value="Father"
                            {{ old('relationship') == 'Father' ? 'selected' : '' }}>
                            Father
                        </option>

                        <option value="Mother"
                            {{ old('relationship') == 'Mother' ? 'selected' : '' }}>
                            Mother
                        </option>

                        <option value="Guardian"
                            {{ old('relationship') == 'Guardian' ? 'selected' : '' }}>
                            Guardian
                        </option>

                        <option value="Grandfather"
                            {{ old('relationship') == 'Grandfather' ? 'selected' : '' }}>
                            Grandfather
                        </option>

                        <option value="Grandmother"
                            {{ old('relationship') == 'Grandmother' ? 'selected' : '' }}>
                            Grandmother
                        </option>

                        <option value="Uncle"
                            {{ old('relationship') == 'Uncle' ? 'selected' : '' }}>
                            Uncle
                        </option>

                        <option value="Aunt"
                            {{ old('relationship') == 'Aunt' ? 'selected' : '' }}>
                            Aunt
                        </option>

                        <option value="Other"
                            {{ old('relationship') == 'Other' ? 'selected' : '' }}>
                            Other
                        </option>

                    </select>

                </div>


                <!-- Phone -->

                <div class="form-group">

                    <label>
                        Phone Number
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                    >

                </div>


                <!-- Alternate Phone -->

                <div class="form-group">

                    <label>
                        Alternate Phone
                    </label>

                    <input
                        type="text"
                        name="alternate_phone"
                        value="{{ old('alternate_phone') }}"
                    >

                </div>


                <!-- Occupation -->

                <div class="form-group">

                    <label>
                        Occupation
                    </label>

                    <input
                        type="text"
                        name="occupation"
                        value="{{ old('occupation') }}"
                    >

                </div>


                <!-- Guardian Type -->

                <div class="form-group">

                    <label>
                        Guardian Type
                    </label>

                    <select name="guardian_type">

                        <option value="">
                            Select Guardian Type
                        </option>

                        <option value="Parent"
                            {{ old('guardian_type') == 'Parent' ? 'selected' : '' }}>
                            Parent
                        </option>

                        <option value="Legal Guardian"
                            {{ old('guardian_type') == 'Legal Guardian' ? 'selected' : '' }}>
                            Legal Guardian
                        </option>

                        <option value="Relative"
                            {{ old('guardian_type') == 'Relative' ? 'selected' : '' }}>
                            Relative
                        </option>

                        <option value="Other"
                            {{ old('guardian_type') == 'Other' ? 'selected' : '' }}>
                            Other
                        </option>

                    </select>

                </div>


                <!-- Primary -->

                <div class="form-group">

                    <label>
                        Set as Primary Guardian
                    </label>

                    <div class="checkbox-row">

                        <input
                            type="checkbox"
                            id="is_primary"
                            name="is_primary"
                            value="1"
                            {{ old('is_primary') ? 'checked' : '' }}
                        >

                        <label for="is_primary">
                            Mark as primary guardian
                        </label>

                    </div>

                </div>


                <!-- Address -->

                <div class="form-group full">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                        placeholder="Enter guardian address"
                    >{{ old('address') }}</textarea>

                </div>

            </div>


            <div class="actions">

                <a
                    href="{{ route('child_officer.dashboard') }}"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-save"
                >
                    Save Guardian
                </button>

            </div>

        </form>

    </div>

</div>


<script>

    const childSearch = document.getElementById('childSearch');
    const childResults = document.getElementById('childResults');
    const childId = document.getElementById('childId');
    const selectedChild = document.getElementById('selectedChild');
    const selectedChildDetails = document.getElementById('selectedChildDetails');
    const changeChild = document.getElementById('changeChild');
    const searchLoading = document.getElementById('searchLoading');

    let searchTimer = null;


    childSearch.addEventListener('input', function () {

        const search = this.value.trim();

        childId.value = '';
        selectedChild.style.display = 'none';

        clearTimeout(searchTimer);

        if (search.length < 2) {

            childResults.innerHTML = '';
            childResults.style.display = 'none';

            return;
        }

        searchTimer = setTimeout(function () {

            searchLoading.style.display = 'block';

            fetch(
                "{{ route('child_officer.guardians.search_children') }}?search="
                + encodeURIComponent(search),
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            )
            .then(response => {

                if (!response.ok) {
                    throw new Error('Search request failed');
                }

                return response.json();

            })
            .then(children => {

                childResults.innerHTML = '';

                if (children.length === 0) {

                    childResults.innerHTML =
                        '<div class="no-results">No children found.</div>';

                    childResults.style.display = 'block';

                    return;
                }


                children.forEach(child => {

                    const result = document.createElement('div');

                    result.className = 'child-result';

                    const fullName = [
                        child.first_name,
                        child.middle_name,
                        child.last_name
                    ]
                    .filter(Boolean)
                    .join(' ');

                    result.innerHTML = `
                        <div class="child-result-name">
                            ${fullName}
                        </div>

                        <div class="child-result-info">
                            Child Code: ${child.child_code}
                            ${child.district ? ' | District: ' + child.district : ''}
                            ${child.gn_division ? ' | GN: ' + child.gn_division : ''}
                        </div>
                    `;


                    result.addEventListener('click', function () {

                        childId.value = child.id;

                        childSearch.value =
                            fullName + ' (' + child.child_code + ')';

                        selectedChildDetails.innerHTML = `
                            ${fullName}
                            <br>
                            <small>
                                Child Code: ${child.child_code}
                            </small>
                        `;

                        selectedChild.style.display = 'block';

                        childResults.innerHTML = '';

                        childResults.style.display = 'none';

                    });


                    childResults.appendChild(result);

                });

                childResults.style.display = 'block';

            })
            .catch(error => {

                console.error(error);

                childResults.innerHTML =
                    '<div class="no-results">Unable to search children.</div>';

                childResults.style.display = 'block';

            })
            .finally(() => {

                searchLoading.style.display = 'none';

            });

        }, 300);

    });


    changeChild.addEventListener('click', function () {

        childId.value = '';

        childSearch.value = '';

        selectedChild.style.display = 'none';

        childSearch.focus();

    });


    document.getElementById('guardianForm')
        .addEventListener('submit', function (event) {

            if (!childId.value) {

                event.preventDefault();

                alert('Please search and select a child first.');

                childSearch.focus();

            }

        });


    document.addEventListener('click', function (event) {

        if (!event.target.closest('.child-search-wrapper')) {

            childResults.style.display = 'none';

        }

    });

</script>

</body>
</html>
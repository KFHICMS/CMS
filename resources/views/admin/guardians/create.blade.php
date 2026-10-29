@extends('layouts.app', [
    'active' => 'guardians',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Guardians', 'url' => route('guardians.index')],
        ['label' => 'Add Guardian'],
    ],
])

@section('title', 'Add Guardian')
@section('heading', 'Add New Guardian')
@section('subheading', 'Register a guardian and connect them to a child.')

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('guardians.index') }}">Back to Guardians</a>
@endsection

@push('head')
    <style>
        * {
            box-sizing: border-box;
        }

        .container {
            max-width: none;
            margin: 0;
            padding: 0;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #245c3a;
            font-size: 28px;
        }

        .header p {
            margin-top: 7px;
            color: #718078;
        }

        .form-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(35, 80, 50, 0.07);
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

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #31533f;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dce8df;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #5b9d72;
            box-shadow: 0 0 0 3px rgba(91, 157, 114, 0.10);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .required {
            color: #b33a3a;
        }

        .error {
            margin-top: 6px;
            color: #b33a3a;
            font-size: 13px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 5px;
        }

        .checkbox-group input {
            width: auto;
        }

        .checkbox-group label {
            margin: 0;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #edf1ee;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2e7d4f;
            color: #ffffff;
        }

        .btn-secondary {
            background: #eef5f0;
            color: #31533f;
        }

        @media (max-width: 700px) {
            .container {
                padding: 18px;
            }

            .form-card {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .actions .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="form-card">

        <form
            method="POST"
            action="{{ route('guardians.store') }}"
        >
            @csrf

            <div class="form-grid">

            {{-- Child --}}
<div class="form-group full-width" style="position:relative;">

    <label>
        Search & Select Child <span class="required">*</span>
    </label>

    {{-- Search box --}}
    <input
        type="text"
        id="childSearch"
        placeholder="Search by child ID or name..."
        autocomplete="off"
    >

    {{-- Hidden field that stores the selected child ID --}}
    <input
        type="hidden"
        name="child_id"
        id="childId"
        value="{{ old('child_id') }}"
    >

    {{-- Suggestions --}}
    <div
        id="childSuggestions"
        style="
            display:none;
            position:absolute;
            left:0;
            right:0;
            top:78px;
            background:#ffffff;
            border:1px solid #dce8df;
            border-radius:10px;
            box-shadow:0 8px 20px rgba(35,80,50,0.12);
            max-height:250px;
            overflow-y:auto;
            z-index:1000;
        "
    ></div>

    {{-- Selected child --}}
    <div
        id="selectedChild"
        style="
            display:none;
            margin-top:10px;
            padding:12px 14px;
            background:#e8f5ec;
            border:1px solid #cde4d4;
            border-radius:10px;
            color:#2e7d4f;
            font-weight:600;
        "
    ></div>

    @error('child_id')
        <div class="error">{{ $message }}</div>
    @enderror

</div>

<script>
    const children = @json($children);

    const childSearch = document.getElementById('childSearch');
    const childId = document.getElementById('childId');
    const childSuggestions = document.getElementById('childSuggestions');
    const selectedChild = document.getElementById('selectedChild');

    childSearch.addEventListener('input', function () {

        const search = this.value.toLowerCase().trim();

        childSuggestions.innerHTML = '';

        if (search.length === 0) {
            childSuggestions.style.display = 'none';
            return;
        }

        const results = children.filter(child => {

            const childCode = (child.child_code || '').toLowerCase();
            const firstName = (child.first_name || '').toLowerCase();
            const middleName = (child.middle_name || '').toLowerCase();
            const lastName = (child.last_name || '').toLowerCase();

            return (
                childCode.includes(search) ||
                firstName.includes(search) ||
                middleName.includes(search) ||
                lastName.includes(search)
            );
        });

        if (results.length === 0) {

            childSuggestions.innerHTML = `
                <div style="
                    padding:14px;
                    color:#718078;
                    text-align:center;
                ">
                    No child found
                </div>
            `;

            childSuggestions.style.display = 'block';
            return;
        }

        results.forEach(child => {

const item = document.createElement('div');

item.style.cssText = `
    padding:12px 14px;
    cursor:pointer;
    border-bottom:1px solid #edf1ee;
    transition:background 0.2s;
    display:flex;
    align-items:center;
    gap:12px;
`;

const fullName = [
    child.first_name,
    child.middle_name,
    child.last_name
]
.filter(Boolean)
.join(' ');

let photoHtml = '';

if (child.profile_photo) {

    photoHtml = `
        <img
            src="/storage/${child.profile_photo}"
            alt="${fullName}"
            style="
                width:42px;
                height:42px;
                object-fit:cover;
                border-radius:50%;
                border:2px solid #dcece1;
            "
        >
    `;

} else {

    photoHtml = `
        <div style="
            width:42px;
            height:42px;
            border-radius:50%;
            background:#e4f2e8;
            color:#2e7d4f;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:bold;
            flex-shrink:0;
        ">
            ${child.first_name ? child.first_name.charAt(0).toUpperCase() : '?'}
        </div>
    `;
}

item.innerHTML = `
    ${photoHtml}

    <div>
        <div style="
            font-weight:600;
            color:#294536;
        ">
            ${child.child_code}
        </div>

        <div style="
            margin-top:3px;
            font-size:13px;
            color:#718078;
        ">
            ${fullName}
        </div>
    </div>
`;

item.addEventListener('mouseenter', function () {
    this.style.background = '#f1f8f3';
});

item.addEventListener('mouseleave', function () {
    this.style.background = '#ffffff';
});

item.addEventListener('click', function () {

    childId.value = child.id;

    childSearch.value =
        `${child.child_code} - ${fullName}`;

    selectedChild.innerHTML = `
        ✓ Selected Child:
        ${child.child_code} - ${fullName}
    `;

    selectedChild.style.display = 'block';

    childSuggestions.style.display = 'none';
});

childSuggestions.appendChild(item);
});

    // Close suggestions when clicking outside
    document.addEventListener('click', function (event) {

        if (
            !childSearch.contains(event.target) &&
            !childSuggestions.contains(event.target)
        ) {
            childSuggestions.style.display = 'none';
        }
    });
</script>

                {{-- Full Name --}}
                <div class="form-group">
                    <label>
                        Guardian Full Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        placeholder="Enter guardian full name"
                    >

                    @error('full_name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Relationship --}}
                <div class="form-group">
                    <label>
                        Relationship <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="relationship"
                        value="{{ old('relationship') }}"
                        placeholder="e.g. Father, Mother, Guardian"
                    >

                    @error('relationship')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Phone --}}
                <div class="form-group">
                    <label>
                        Phone Number <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number"
                    >

                    @error('phone')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Alternate Phone --}}
                <div class="form-group">
                    <label>Alternate Phone</label>

                    <input
                        type="text"
                        name="alternate_phone"
                        value="{{ old('alternate_phone') }}"
                        placeholder="Optional"
                    >

                    @error('alternate_phone')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Occupation --}}
                <div class="form-group">
                    <label>Occupation</label>

                    <input
                        type="text"
                        name="occupation"
                        value="{{ old('occupation') }}"
                        placeholder="Enter occupation"
                    >

                    @error('occupation')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Primary Guardian --}}
                <div class="form-group">
                    <label>Guardian Type</label>

                    <div class="checkbox-group">
                        <input
                            type="checkbox"
                            name="is_primary"
                            value="1"
                            id="is_primary"
                            {{ old('is_primary') ? 'checked' : '' }}
                        >

                        <label for="is_primary">
                            Set as Primary Guardian
                        </label>
                    </div>

                    @error('is_primary')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Address --}}
                <div class="form-group full-width">
                    <label>Address</label>

                    <textarea
                        name="address"
                        placeholder="Enter guardian address"
                    >{{ old('address') }}</textarea>

                    @error('address')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="actions">

                <a
                    href="{{ route('guardians.index') }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Guardian
                </button>

            </div>

        </form>

    </div>

</div>
@endsection
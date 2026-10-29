@extends('layouts.app', ['active' => 'guardians'])

@section('title', 'Add Guardian')
@section('heading', 'Add Guardian')
@section('subheading', 'Link a guardian to an existing child record.')

@section('content')

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
@endsection

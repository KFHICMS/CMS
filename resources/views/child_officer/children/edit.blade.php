@extends('layouts.app', ['active' => 'children'])

@section('title', 'Edit Child')
@section('heading', 'Edit Child')
@section('subheading', 'Update the selected child profile.')

@section('content')

<div class="form-card">

            <div class="form-intro">

                <h2>
                    Update Child Information
                </h2>

                <p>
                    Update the child's information carefully.
                    Required fields are marked with <strong>*</strong>.
                </p>

            </div>


            @if($errors->any())

                <div class="error-box">

                    <strong>
                        Please correct the following errors:
                    </strong>

                    <ul>

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <form
                action="{{ route('child_officer.children.update', $child) }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf
                @method('PUT')


                <!-- Basic Information -->

                <div class="section-title">
                    Basic Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Child Code <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="child_code"
                            value="{{ old('child_code', $child->child_code) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Gender <span>*</span>
                        </label>

                        <select name="gender" required>

                            <option value="">
                                Select Gender
                            </option>

                            <option value="Male"
                                {{ old('gender', $child->gender) === 'Male' ? 'selected' : '' }}>
                                Male
                            </option>

                            <option value="Female"
                                {{ old('gender', $child->gender) === 'Female' ? 'selected' : '' }}>
                                Female
                            </option>

                            <option value="Other"
                                {{ old('gender', $child->gender) === 'Other' ? 'selected' : '' }}>
                                Other
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            First Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $child->first_name) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Middle Name
                        </label>

                        <input
                            type="text"
                            name="middle_name"
                            value="{{ old('middle_name', $child->middle_name) }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Last Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $child->last_name) }}"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Date of Birth <span>*</span>
                        </label>

                        <input
                            type="date"
                            name="date_of_birth"
                            value="{{ old(
                                'date_of_birth',
                                $child->date_of_birth
                                    ? $child->date_of_birth->format('Y-m-d')
                                    : ''
                            ) }}"
                            required
                        >

                    </div>

                </div>


                <!-- Education -->

                <div class="section-title">
                    Education Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            School Name
                        </label>

                        <input
                            type="text"
                            name="school_name"
                            value="{{ old('school_name', $child->school_name) }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Grade
                        </label>

                        <input
                            type="text"
                            name="grade"
                            value="{{ old('grade', $child->grade) }}"
                        >

                    </div>

                </div>


                <!-- Location -->

                <div class="section-title">
                    Location Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            District
                        </label>

                        <input
                            type="text"
                            name="district"
                            value="{{ old('district', $child->district) }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            GN Division
                        </label>

                        <input
                            type="text"
                            name="gn_division"
                            value="{{ old('gn_division', $child->gn_division) }}"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                        >{{ old('address', $child->address) }}</textarea>

                    </div>

                </div>


                <!-- Additional Information -->

                <div class="section-title">
                    Additional Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Current Profile Photo
                        </label>

                        <div class="current-photo">

                            @if($child->profile_photo)

                                <img
                                    src="{{ asset('storage/' . $child->profile_photo) }}"
                                    alt="Current Child Photo"
                                >

                            @else

                                <div class="photo-placeholder">

                                    {{ strtoupper(
                                        substr($child->first_name, 0, 1)
                                    ) }}

                                </div>

                            @endif

                        </div>


                        <label>
                            Replace Profile Photo
                        </label>

                        <input
                            type="file"
                            name="profile_photo"
                            class="file-input"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <div class="help-text">
                            JPG, JPEG, PNG or WEBP. Maximum 2MB.
                        </div>

                    </div>


                    <div class="form-group">

                        <label>
                            Status <span>*</span>
                        </label>

                        <select name="status" required>

                            <option value="active"
                                {{ old('status', $child->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive"
                                {{ old('status', $child->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>

                    </div>


                    <div class="form-group full">

                        <label>
                            Notes
                        </label>

                        <textarea
                            name="notes"
                        >{{ old('notes', $child->notes) }}</textarea>

                    </div>

                </div>


                <!-- Buttons -->

                <div class="button-row">

                    <a
                        href="{{ route('child_officer.children.show', $child) }}"
                        class="btn btn-cancel"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </div>
@endsection

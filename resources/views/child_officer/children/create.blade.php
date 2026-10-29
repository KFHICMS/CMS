@extends('layouts.app', ['active' => 'children'])

@section('title', 'Register Child')
@section('heading', 'Register Child')
@section('subheading', 'Add a new child to the KFHI system.')

@section('content')

<div class="form-card">

            <div class="form-intro">

                <h2>
                    Child Registration
                </h2>

                <p>
                    Enter the child's information carefully. Required fields
                    are marked with <strong>*</strong>.
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
    action="{{ route('child_officer.children.store') }}"
    method="POST"
    enctype="multipart/form-data"
>

                @csrf


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
                            value="{{ old('child_code') }}"
                            placeholder="Example: LKA005123"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Gender <span>*</span>
                        </label>

                        <select name="gender">

                            <option value="">
                                Select Gender
                            </option>

                            <option value="Male"
                                {{ old('gender') === 'Male' ? 'selected' : '' }}>
                                Male
                            </option>

                            <option value="Female"
                                {{ old('gender') === 'Female' ? 'selected' : '' }}>
                                Female
                            </option>

                            <option value="Other"
                                {{ old('gender') === 'Other' ? 'selected' : '' }}>
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
                            value="{{ old('first_name') }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Middle Name
                        </label>

                        <input
                            type="text"
                            name="middle_name"
                            value="{{ old('middle_name') }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Last Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name') }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Date of Birth <span>*</span>
                        </label>

                        <input
                            type="date"
                            name="date_of_birth"
                            value="{{ old('date_of_birth') }}"
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
                            value="{{ old('school_name') }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Grade
                        </label>

                        <input
                            type="text"
                            name="grade"
                            value="{{ old('grade') }}"
                            placeholder="Example: Grade 8"
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
                            value="{{ old('district') }}"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            GN Division
                        </label>

                        <input
                            type="text"
                            name="gn_division"
                            value="{{ old('gn_division') }}"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                            placeholder="Enter the child's residential address"
                        >{{ old('address') }}</textarea>

                    </div>

                </div>


                <!-- Additional Information -->

                <div class="section-title">
                    Additional Information
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Profile Photo
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

                        <select name="status">

                            <option value="active"
                                {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive"
                                {{ old('status') === 'inactive' ? 'selected' : '' }}>
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
                            placeholder="Additional notes about the child"
                        >{{ old('notes') }}</textarea>

                    </div>

                </div>


                <!-- Buttons -->

                <div class="button-row">

                    <a
                        href="{{ route('child_officer.children.index') }}"
                        class="btn btn-cancel"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Register Child
                    </button>

                </div>

            </form>

        </div>
@endsection

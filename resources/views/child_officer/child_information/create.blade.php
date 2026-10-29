@extends('layouts.app', ['active' => 'children'])

@section('title', 'Add Information')
@section('heading', 'Add New Information')
@section('subheading', 'Add a new information record for this child.')

@section('content')


        <!-- Back -->
        <a
            href="{{ route('child_officer.children.show', $child) }}"
            class="back-link"
        >
            ← Back to Child Details
        </a>


        <!-- Page Title -->
        <div class="page-title">
            <h2>Add New Information</h2>

            <p>
                Add a new information record for this child.
            </p>
        </div>


        <!-- Child Information -->
        <div class="child-card">

            <div class="child-details">

                <h3>
                    {{ trim(
                        $child->first_name . ' ' .
                        ($child->middle_name ?? '') . ' ' .
                        $child->last_name
                    ) }}
                </h3>

                <p>
                    <strong>District:</strong>
                    {{ $child->district ?: 'Not provided' }}
                </p>

                <p>
                    <strong>GN Division:</strong>
                    {{ $child->gn_division ?: 'Not provided' }}
                </p>

            </div>

            <div class="child-code">
                {{ $child->child_code }}
            </div>

        </div>


        <!-- Validation Errors -->
        @if ($errors->any())

            <div class="error-box">

                <strong>Please correct the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        <!-- Form -->
        <div class="form-card">

            <h3>Information Details</h3>

            <form
                action="{{ route('child_officer.child_information.store', $child) }}"
                method="POST"
            >

                @csrf


                <!-- Information Type -->
                <div class="form-group">

                    <label for="information_type">
                        Information Type
                        <span class="required">*</span>
                    </label>

                    <select
                        name="information_type"
                        id="information_type"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select information type
                        </option>

                        <option
                            value="Education"
                            {{ old('information_type') === 'Education' ? 'selected' : '' }}
                        >
                            Education
                        </option>

                        <option
                            value="Family"
                            {{ old('information_type') === 'Family' ? 'selected' : '' }}
                        >
                            Family
                        </option>

                        <option
                            value="Program"
                            {{ old('information_type') === 'Program' ? 'selected' : '' }}
                        >
                            Program
                        </option>

                        <option
                            value="Important Note"
                            {{ old('information_type') === 'Important Note' ? 'selected' : '' }}
                        >
                            Important Note
                        </option>

                        <option
                            value="Other"
                            {{ old('information_type') === 'Other' ? 'selected' : '' }}
                        >
                            Other
                        </option>

                    </select>

                </div>


                <!-- Title -->
                <div class="form-group">

                    <label for="title">
                        Title
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control"
                        value="{{ old('title') }}"
                        placeholder="Enter a short title"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- Description -->
                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        class="form-control"
                        placeholder="Enter the additional information..."
                    >{{ old('description') }}</textarea>

                    <div class="help-text">
                        Add relevant information that should be stored separately
                        from the child's main profile.
                    </div>

                </div>


                <!-- Information Date -->
                <div class="form-group">

                    <label for="information_date">
                        Information Date
                    </label>

                    <input
                        type="date"
                        name="information_date"
                        id="information_date"
                        class="form-control"
                        value="{{ old('information_date', date('Y-m-d')) }}"
                    >

                </div>


                <!-- Actions -->
                <div class="actions">

                    <a
                        href="{{ route('child_officer.children.show', $child) }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Information
                    </button>

                </div>

            </form>

        </div>
@endsection

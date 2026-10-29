@extends('layouts.app', ['active' => 'guardians'])

@section('title', 'Edit Guardian')
@section('heading', 'Edit Guardian')
@section('subheading', 'Update guardian information and relationship details.')

@section('content')

<div class="form-card">

            <div class="form-title">
                Edit Guardian Information
            </div>

            <div class="form-description">
                Update the guardian information for this child.
            </div>


            <!-- Validation Errors -->

            @if($errors->any())

                <div class="error-message">

                    <strong>
                        Please correct the following errors:
                    </strong>

                    <ul class="error-list">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- Child Information -->

            <div class="child-box">

                <div class="child-box-title">
                    Child
                </div>

                <div class="child-box-info">

                    <strong>
                        {{ $guardian->child->child_code }}
                    </strong>

                    -
                    {{ $guardian->child->first_name }}

                    @if($guardian->child->middle_name)
                        {{ $guardian->child->middle_name }}
                    @endif

                    {{ $guardian->child->last_name }}

                    @if($guardian->child->district)
                        | {{ $guardian->child->district }}
                    @endif

                </div>

            </div>


            <!-- Form -->

            <form
                action="{{ route('child_officer.guardians.update', $guardian) }}"
                method="POST"
            >

                @csrf

                @method('PUT')


                <div class="form-grid">

                    <!-- Full Name -->

                    <div class="form-group">

                        <label for="full_name">
                            Guardian Full Name *
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="{{ old('full_name', $guardian->full_name) }}"
                            required
                        >

                    </div>


                    <!-- Relationship -->

                    <div class="form-group">

                        <label for="relationship">
                            Relationship *
                        </label>

                        <input
                            type="text"
                            id="relationship"
                            name="relationship"
                            value="{{ old('relationship', $guardian->relationship) }}"
                            required
                        >

                    </div>


                    <!-- Phone -->

                    <div class="form-group">

                        <label for="phone">
                            Phone *
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $guardian->phone) }}"
                            required
                        >

                    </div>


                    <!-- Alternate Phone -->

                    <div class="form-group">

                        <label for="alternate_phone">
                            Alternate Phone
                        </label>

                        <input
                            type="text"
                            id="alternate_phone"
                            name="alternate_phone"
                            value="{{ old('alternate_phone', $guardian->alternate_phone) }}"
                        >

                    </div>


                    <!-- Occupation -->

                    <div class="form-group">

                        <label for="occupation">
                            Occupation
                        </label>

                        <input
                            type="text"
                            id="occupation"
                            name="occupation"
                            value="{{ old('occupation', $guardian->occupation) }}"
                        >

                    </div>


                    <!-- Guardian Type -->

                    <div class="form-group">

                        <label for="guardian_type">
                            Guardian Type
                        </label>

                        <input
                            type="text"
                            id="guardian_type"
                            name="guardian_type"
                            value="{{ old('guardian_type', $guardian->guardian_type) }}"
                            placeholder="Example: Parent, Relative, Foster Parent"
                        >

                    </div>


                    <!-- Address -->

                    <div class="form-group full">

                        <label for="address">
                            Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                        >{{ old('address', $guardian->address) }}</textarea>

                    </div>


                    <!-- Primary Guardian -->

                    <div class="form-group full">

                        <div class="checkbox-group">

                            <input
                                type="checkbox"
                                id="is_primary"
                                name="is_primary"
                                value="1"
                                {{ old('is_primary', $guardian->is_primary) ? 'checked' : '' }}
                            >

                            <label for="is_primary">
                                Set as Primary Guardian
                            </label>

                        </div>

                    </div>

                </div>


                <!-- Actions -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Guardian
                    </button>

                    <a
                        href="{{ route('child_officer.children.show', $guardian->child_id) }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>
@endsection

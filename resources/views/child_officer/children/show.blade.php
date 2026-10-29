@extends('layouts.app', ['active' => 'children'])

@section('title', 'Child Details')
@section('heading', 'Child Details')
@section('subheading', 'View complete details for this child.')

@section('content')

<section class="profile-card">

            @if($child->profile_photo)

                <img
                    src="{{ asset('storage/' . $child->profile_photo) }}"
                    alt="Child Photo"
                    class="profile-photo"
                >

            @else

                <div class="photo-placeholder">
                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                </div>

            @endif


            <div class="profile-info">

                <h2>

                    {{ $child->first_name }}

                    @if($child->middle_name)
                        {{ $child->middle_name }}
                    @endif

                    {{ $child->last_name }}

                </h2>

                <div class="child-code">
                    {{ $child->child_code }}
                </div>


                <span class="status
                    {{ $child->status === 'active'
                        ? 'status-active'
                        : 'status-inactive' }}">

                    {{ ucfirst($child->status) }}

                </span>
                <div style="margin-top: 15px;">

<a
    href="{{ route('child_officer.children.edit', $child) }}"
    class="action-btn"
>
    Edit Child
</a>

</div>
            </div>

        </section>


        <!-- Information -->

        <div class="section-grid">

            <!-- Personal Information -->

            <div class="info-card">

                <div class="card-title">
                    Personal Information
                </div>

                <div class="info-list">

                    <div class="info-row">

                        <span class="info-label">
                            Date of Birth
                        </span>

                        <span class="info-value">
                            {{ $child->date_of_birth
                                ? $child->date_of_birth->format('d M Y')
                                : 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Gender
                        </span>

                        <span class="info-value">
                            {{ $child->gender ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            School
                        </span>

                        <span class="info-value">
                            {{ $child->school_name ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Grade
                        </span>

                        <span class="info-value">
                            {{ $child->grade ?? 'N/A' }}
                        </span>

                    </div>

                </div>

            </div>


            <!-- Location -->

            <div class="info-card">

                <div class="card-title">
                    Location Information
                </div>

                <div class="info-list">

                    <div class="info-row">

                        <span class="info-label">
                            District
                        </span>

                        <span class="info-value">
                            {{ $child->district ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            GN Division
                        </span>

                        <span class="info-value">
                            {{ $child->gn_division ?? 'N/A' }}
                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Address
                        </span>

                        <span class="info-value">
                            {{ $child->address ?? 'N/A' }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- Additional Information --}}
<div class="section-card">
    <div class="section-header">
        <div>
            <h2>Additional Information</h2>
            <p>Additional records and notes related to this child.</p>
        </div>

        <a href="{{ route('child_officer.child_information.create', $child) }}"
           class="action-btn">
            + Add Information
        </a>
    </div>

    @if($child->additionalInformation->count())
        <div class="information-list">
            @foreach($child->additionalInformation as $info)
                <div class="information-card">

                    <div class="information-top">
                        <div>
                            <span class="info-type">
                                {{ $info->information_type }}
                            </span>

                            <h3>{{ $info->title }}</h3>
                        </div>

                        @if($info->information_date)
                            <span class="info-date">
                                {{ $info->information_date->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    @if($info->description)
                        <p class="info-description">
                            {{ $info->description }}
                        </p>
                    @endif

                    @if($info->addedBy)
                        <div class="info-footer">
                            Added by:
                            <strong>{{ $info->addedBy->name }}</strong>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @else
        <div class="empty-information">
            <div class="empty-icon">📝</div>
            <h3>No Additional Information</h3>
            <p>
                No additional information has been recorded for this child yet.
            </p>

            <a href="{{ route('child_officer.child_information.create', $child) }}"
               class="action-btn">
                + Add First Information
            </a>
        </div>
    @endif
</div>
<!-- Guardians -->

<div class="info-card" style="margin-top:25px;">

<div class="guardian-header">

    <div class="card-title">
        Guardians
    </div>

    <a
        href="{{ route('child_officer.guardians.create') }}"
        class="action-btn"
    >
        + Add Guardian
    </a>

</div>

    @forelse($child->guardians as $guardian)
    <div class="guardian">

<div class="guardian-name">

    {{ $guardian->full_name }}

    @if($guardian->is_primary)
        <span class="primary-badge">
            Primary
        </span>
    @endif

</div>


<div class="guardian-info">

    Relationship:
    {{ $guardian->relationship }}

    <br>

    Phone:
    {{ $guardian->phone }}

    @if($guardian->alternate_phone)

        <br>

        Alternate Phone:
        {{ $guardian->alternate_phone }}

    @endif

    @if($guardian->occupation)

        <br>

        Occupation:
        {{ $guardian->occupation }}

    @endif

    @if($guardian->guardian_type)

        <br>

        Guardian Type:
        {{ $guardian->guardian_type }}

    @endif

    @if($guardian->address)

        <br>

        Address:
        {{ $guardian->address }}

    @endif

</div>


<!-- Guardian Actions -->

<div style="margin-top: 12px;">

    <a
        href="{{ route('child_officer.guardians.edit', $guardian) }}"
        class="guardian-edit-btn"
    >
        Edit
    </a>


    <form
        action="{{ route('child_officer.guardians.destroy', $guardian) }}"
        method="POST"
        style="display: inline;"
        onsubmit="return confirm('Are you sure you want to delete this guardian information?');"
    >

        @csrf

        @method('DELETE')

        <button
            type="submit"
            class="guardian-delete-btn"
        >
            Delete
        </button>

    </form>

</div>

</div>

    @empty

        <div class="guardian">

            <span class="not-available">
                No guardian information available.
            </span>

        </div>

    @endforelse

</div>

        <!-- QR / Digital ID -->

        <div class="action-grid">

            <div class="action-card">

                <h3>
                    Secure QR Code
                </h3>

                <p>
                    View the secure QR identification associated with this child.
                </p>

                @if($child->qrCode)

                    <a
                        href="{{ route('children.qr.show', $child) }}"
                        class="action-btn"
                    >
                        View QR
                    </a>

                @else

                    <span class="not-available">
                        QR code not generated.
                    </span>

                @endif

            </div>


            <div class="action-card">

                <h3>
                    Digital ID
                </h3>

                <p>
                    View the digital identification information for this child.
                </p>

                @if($child->digitalId)

                    <span class="not-available">
                        Digital ID available.
                    </span>

                @else

                    <span class="not-available">
                        Digital ID not available.
                    </span>

                @endif

            </div>

        </div>


        <!-- Follow-ups -->

        <div class="followup-card">

            <div class="card-title">
                Follow-ups
            </div>

            @forelse($child->followUps as $followUp)

                <div class="followup-item">

                    <div class="followup-title">

                        {{ $followUp->title
                            ?? $followUp->subject
                            ?? 'Follow-up' }}

                    </div>

                    <div class="followup-description">

                        {{ $followUp->description
                            ?? $followUp->notes
                            ?? 'No additional information.' }}

                    </div>

                </div>

            @empty

                <div class="followup-item">

                    <span class="not-available">
                        No follow-ups recorded for this child.
                    </span>

                </div>

            @endforelse

        </div>
@endsection

@extends('layouts.app', [
    'active' => 'submissions',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Questionnaire Submissions', 'url' => route('admin_submissions.index')],
        ['label' => 'Submission', 'url' => route('admin_submissions.show', $submission)],
        ['label' => 'Edit answers'],
    ],
])

@section('title', 'Edit answers')
@section('heading', 'Edit answers')
@section('subheading', ($submission->questionnaire?->title).' · '.($submission->questionnaire?->event?->title).' · '.($submission->child?->child_code ?? 'New child'))

@section('content')
    <p class="muted">Every change is saved in the edit history with your name, the date and your reason. Approving the submission locks the answers.</p>

    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            <strong>Please check the answers.</strong> Some answers are missing or not valid.
        </div>
    @endif

    <form method="POST" action="{{ route('admin_submissions.update', $submission) }}" class="k-card" style="max-width: 760px">
        @csrf
        @method('PUT')

        @include('public.invitations.partials.questions', ['questions' => $questions])

        <div class="q @error('edit_reason') has-error @enderror">
            <label class="q-label" for="edit_reason">Reason for the change <span class="req" aria-hidden="true">*</span></label>
            <textarea id="edit_reason" name="edit_reason" rows="3" maxlength="1000" required>{{ old('edit_reason') }}</textarea>
            @error('edit_reason') <div class="err" role="alert">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="k-btn k-btn-primary">Save changes</button>
        <a class="k-btn k-btn-secondary" href="{{ route('admin_submissions.show', $submission) }}">Cancel</a>
    </form>
@endsection
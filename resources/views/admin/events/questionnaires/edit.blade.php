

@extends('layouts.app', [
    'active' => 'events',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Events', 'url' => route('events.index')],
        ['label' => $event->title, 'url' => route('events.show', $event)],
        ['label' => $questionnaire->title, 'url' => route('events.questionnaires.show', [$event, $questionnaire])],
        ['label' => 'Edit Questionnaire'],
    ],
])

@section('title', 'Edit Questionnaire')
@section('heading', 'Edit Questionnaire')
@section('subheading', $questionnaire->title)

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}">Back to Questionnaire</a>
@endsection

@push('head')
<style>
    .questionnaire-page {
        max-width: none;
        margin: 0;
        padding: 0;
    }

    .questionnaire-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 32px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .page-title {
        margin-bottom: 8px;
        color: #245c3a;
        font-size: 28px;
        font-weight: 700;
    }

    .page-subtitle {
        color: #777;
        margin-bottom: 28px;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #315c43;
    }

    .form-control {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d5e5da;
        border-radius: 10px;
        font-size: 15px;
        box-sizing: border-box;
        outline: none;
    }

    .form-control:focus {
        border-color: #4caf70;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.12);
    }

    textarea.form-control {
        min-height: 130px;
        resize: vertical;
    }

    .button-row {
        display: flex;
        gap: 12px;
        margin-top: 28px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        border-radius: 10px;
        padding: 11px 18px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #2e7d4f;
        color: white;
    }

    .btn-primary:hover {
        background: #25683f;
    }

    .btn-secondary {
        background: #edf5ef;
        color: #2e6b45;
    }

    .btn-secondary:hover {
        background: #dfeee3;
    }

    .error-box {
        background: #fff0f0;
        color: #a33a3a;
        border-radius: 10px;
        padding: 12px 15px;
        margin-bottom: 20px;
    }

    @media (max-width: 600px) {
        .questionnaire-page {
            margin: 20px auto;
            padding: 12px;
        }

        .questionnaire-card {
            padding: 22px;
        }

        .page-title {
            font-size: 23px;
        }
    }
</style>
@endpush

@section('content')
<div class="questionnaire-page">

    <div class="questionnaire-card">

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

        <form
            method="POST"
            action="{{ route('events.questionnaires.update', [$event, $questionnaire]) }}"
        >
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div class="form-group">
                <label for="title">
                    Questionnaire Title
                </label>

                <input
                    type="text"
                    name="title"
                    id="title"
                    class="form-control"
                    value="{{ old('title', $questionnaire->title) }}"
                    required
                >
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="description">
                    Description
                </label>

                <textarea
                    name="description"
                    id="description"
                    class="form-control"
                >{{ old('description', $questionnaire->description) }}</textarea>
            </div>

            {{-- Status --}}
            <div class="form-group">
                <label for="status">
                    Status
                </label>

                <select
                    name="status"
                    id="status"
                    class="form-control"
                    required
                >
                    <option
                        value="draft"
                        @selected(old('status', $questionnaire->status) === 'draft')
                    >
                        Draft
                    </option>

                    <option
                        value="published"
                        @selected(old('status', $questionnaire->status) === 'published')
                    >
                        Published
                    </option>

                    <option
                        value="closed"
                        @selected(old('status', $questionnaire->status) === 'closed')
                    >
                        Closed
                    </option>
                </select>
            </div>

            <div class="button-row">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

                <a
                    href="{{ route('events.questionnaires.show', [$event, $questionnaire]) }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>
@endsection
@php
    $pendingCustomFields = old('new_custom_fields', []);
    $nextCustomFieldIndex = count($pendingCustomFields)
        ? max(array_map('intval', array_keys($pendingCustomFields))) + 1
        : 0;
@endphp

<style>
    .custom-fields-section {
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid #e5eee8;
    }

    .custom-fields-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 8px;
    }

    .custom-fields-heading h3 {
        color: #2e7049;
        font-size: 17px;
    }

    .custom-fields-help {
        margin: 0 0 16px;
        color: #718078;
        font-size: 12px;
        line-height: 1.5;
    }

    .custom-field-list {
        display: grid;
        gap: 12px;
    }

    .custom-field-row {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(140px, 1fr) auto;
        align-items: end;
        gap: 12px;
        padding: 13px;
        border: 1px solid #dce9df;
        border-radius: 10px;
        background: #fbfdfc;
    }

    .custom-field-row label {
        display: block;
        margin-bottom: 6px;
        color: #46564d;
        font-size: 12px;
        font-weight: 600;
    }

    .custom-field-row input,
    .custom-field-row select {
        width: 100%;
        min-width: 0;
        padding: 10px;
        border: 1px solid #d7e4db;
        border-radius: 8px;
        background: #fff;
        font-size: 13px;
    }

    .custom-field-remove {
        border: 0;
        border-radius: 8px;
        padding: 10px 12px;
        background: #fff0ef;
        color: #a33d35;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .custom-field-remove:hover { background: #fbe1df; }
    .custom-field-add { margin-top: 12px; }

    .custom-fields-error {
        margin-bottom: 12px;
        color: #b33a3a;
        font-size: 12px;
    }

    .archived-custom-fields {
        margin-top: 17px;
        color: #526158;
        font-size: 12px;
    }

    .archived-custom-fields summary { cursor: pointer; font-weight: 600; }
    .archived-custom-field-row {
        display: grid;
        grid-template-columns: auto minmax(140px, 1fr) minmax(140px, 1fr);
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }

    .archived-custom-fields input { width: auto; margin-right: 6px; }
    .archived-custom-fields input[type="text"],
    .archived-custom-fields input[type="number"],
    .archived-custom-fields input[type="date"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #d7e4db;
        border-radius: 8px;
    }

    @media (max-width: 650px) {
        .custom-fields-heading { flex-direction: column; }
        .custom-field-row { grid-template-columns: 1fr; }
        .archived-custom-field-row { grid-template-columns: auto 1fr; }
        .archived-custom-field-row input[type="text"],
        .archived-custom-field-row input[type="number"],
        .archived-custom-field-row input[type="date"] { grid-column: 2; }
    }
</style>

<section class="custom-fields-section" aria-labelledby="custom-fields-title">
    <div class="custom-fields-heading">
        <h3 id="custom-fields-title">Extra child fields</h3>
        <button type="button" class="custom-field-remove" id="add-custom-field">
            Add extra field
        </button>
    </div>
    <p class="custom-fields-help">
        Add reusable fields such as shoe size, shoulder size, or waist size.
        New fields become available on every child record. Removing an existing
        field hides it from all child forms but keeps saved values in the database.
    </p>

    @foreach($errors->getMessages() as $key => $messages)
        @if(str_starts_with($key, 'custom_fields') || str_starts_with($key, 'new_custom_fields'))
            @foreach($messages as $message)
                <div class="custom-fields-error">{{ $message }}</div>
            @endforeach
        @endif
    @endforeach

    <div class="custom-field-list" id="existing-custom-fields">
        @forelse($customFields as $field)
            <div class="custom-field-row" data-custom-field-row>
                <div>
                    <label for="custom-field-{{ $field->id }}">{{ $field->label }}</label>
                    <input
                        id="custom-field-{{ $field->id }}"
                        type="{{ $field->type }}"
                        name="custom_fields[{{ $field->id }}]"
                        value="{{ old('custom_fields.' . $field->id, $customFieldValues[$field->id] ?? '') }}"
                        @if($field->type === 'number') step="any" @endif
                    >
                </div>
                <div>
                    <label>Field type</label>
                    <input type="text" value="{{ ucfirst($field->type) }}" disabled>
                </div>
                <div>
                    <input
                        type="hidden"
                        name="remove_custom_fields[]"
                        value="{{ $field->id }}"
                        disabled
                        data-remove-custom-field
                    >
                    <button type="button" class="custom-field-remove" data-remove-existing-field>
                        Remove field
                    </button>
                </div>
            </div>
        @empty
            <p class="custom-fields-help">No extra fields have been added yet.</p>
        @endforelse
    </div>

    <div class="custom-field-list" id="new-custom-fields">
        @foreach($pendingCustomFields as $index => $newField)
            <div class="custom-field-row" data-new-custom-field>
                <div>
                    <label for="new-custom-field-label-{{ $index }}">Field name</label>
                    <input
                        id="new-custom-field-label-{{ $index }}"
                        type="text"
                        name="new_custom_fields[{{ $index }}][label]"
                        value="{{ $newField['label'] ?? '' }}"
                        maxlength="100"
                        required
                    >
                </div>
                <div>
                    <label for="new-custom-field-value-{{ $index }}">Value for this child</label>
                    <input
                        id="new-custom-field-value-{{ $index }}"
                        type="{{ in_array($newField['type'] ?? 'text', ['text', 'number', 'date'], true) ? ($newField['type'] ?? 'text') : 'text' }}"
                        name="new_custom_fields[{{ $index }}][value]"
                        value="{{ $newField['value'] ?? '' }}"
                        @if(($newField['type'] ?? 'text') === 'number') step="any" @endif
                    >
                </div>
                <div>
                    <label for="new-custom-field-type-{{ $index }}">Type</label>
                    <select
                        id="new-custom-field-type-{{ $index }}"
                        name="new_custom_fields[{{ $index }}][type]"
                        data-custom-field-type
                    >
                        <option value="text" @selected(($newField['type'] ?? 'text') === 'text')>Text</option>
                        <option value="number" @selected(($newField['type'] ?? '') === 'number')>Number</option>
                        <option value="date" @selected(($newField['type'] ?? '') === 'date')>Date</option>
                    </select>
                </div>
                <button type="button" class="custom-field-remove" data-remove-new-field>Remove</button>
            </div>
        @endforeach
    </div>

    @if($archivedCustomFields->isNotEmpty())
        <details class="archived-custom-fields">
            <summary>Restore a previously removed field</summary>
            @foreach($archivedCustomFields as $field)
                <div class="archived-custom-field-row">
                    <input
                        type="checkbox"
                        name="restore_custom_fields[]"
                        value="{{ $field->id }}"
                        @checked(in_array((string) $field->id, old('restore_custom_fields', []), true))
                    >
                    <span>{{ $field->label }} ({{ $field->type }})</span>
                    <input
                        type="{{ $field->type }}"
                        name="restore_custom_field_values[{{ $field->id }}]"
                        value="{{ old('restore_custom_field_values.' . $field->id) }}"
                        aria-label="{{ $field->label }} value for this child"
                        @if($field->type === 'number') step="any" @endif
                    >
                </div>
            @endforeach
        </details>
    @endif
</section>

<script>
    (() => {
        const addButton = document.getElementById('add-custom-field');
        const newFields = document.getElementById('new-custom-fields');
        let fieldIndex = {{ $nextCustomFieldIndex }};

        addButton.addEventListener('click', () => {
            const index = fieldIndex++;
            const row = document.createElement('div');
            row.className = 'custom-field-row';
            row.dataset.newCustomField = '';

            const labelGroup = document.createElement('div');
            const labelTitle = document.createElement('label');
            labelTitle.htmlFor = `new-custom-field-label-${index}`;
            labelTitle.textContent = 'Field name';
            const labelInput = document.createElement('input');
            labelInput.id = `new-custom-field-label-${index}`;
            labelInput.type = 'text';
            labelInput.name = `new_custom_fields[${index}][label]`;
            labelInput.maxLength = 100;
            labelInput.required = true;
            labelGroup.append(labelTitle, labelInput);

            const valueGroup = document.createElement('div');
            const valueTitle = document.createElement('label');
            valueTitle.htmlFor = `new-custom-field-value-${index}`;
            valueTitle.textContent = 'Value for this child';
            const valueInput = document.createElement('input');
            valueInput.id = `new-custom-field-value-${index}`;
            valueInput.type = 'text';
            valueInput.name = `new_custom_fields[${index}][value]`;
            valueGroup.append(valueTitle, valueInput);

            const typeGroup = document.createElement('div');
            const typeTitle = document.createElement('label');
            typeTitle.htmlFor = `new-custom-field-type-${index}`;
            typeTitle.textContent = 'Type';
            const typeSelect = document.createElement('select');
            typeSelect.id = `new-custom-field-type-${index}`;
            typeSelect.name = `new_custom_fields[${index}][type]`;
            typeSelect.dataset.customFieldType = '';

            for (const [type, title] of [['text', 'Text'], ['number', 'Number'], ['date', 'Date']]) {
                typeSelect.add(new Option(title, type));
            }

            typeGroup.append(typeTitle, typeSelect);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'custom-field-remove';
            removeButton.dataset.removeNewField = '';
            removeButton.textContent = 'Remove';

            row.append(labelGroup, valueGroup, typeGroup, removeButton);
            newFields.append(row);
            labelInput.focus();
        });

        document.addEventListener('click', (event) => {
            const removeNew = event.target.closest('[data-remove-new-field]');
            if (removeNew) {
                removeNew.closest('[data-new-custom-field]').remove();
                return;
            }

            const removeExisting = event.target.closest('[data-remove-existing-field]');
            if (removeExisting) {
                const row = removeExisting.closest('[data-custom-field-row]');
                const removeInput = row.querySelector('[data-remove-custom-field]');
                removeInput.disabled = false;
                row.hidden = true;
            }
        });

        document.addEventListener('change', (event) => {
            const typeSelect = event.target.closest('[data-custom-field-type]');
            if (!typeSelect) {
                return;
            }

            const valueInput = typeSelect.closest('[data-new-custom-field]')
                .querySelector('input[name$="[value]"]');
            valueInput.type = typeSelect.value;
            if (typeSelect.value === 'number') {
                valueInput.step = 'any';
            } else {
                valueInput.removeAttribute('step');
            }
        });
    })();
</script>

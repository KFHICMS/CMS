<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChildCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChildCustomFieldService
{
    private const TYPES = ['text', 'number', 'date'];

    public function activeFields()
    {
        return ChildCustomField::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->get();
    }

    public function archivedFields()
    {
        return ChildCustomField::query()
            ->where('is_active', false)
            ->orderBy('label')
            ->get();
    }

    public function valuesFor(Child $child): array
    {
        return $child->customFieldValues()
            ->whereHas('customField', fn ($query) => $query->where('is_active', true))
            ->pluck('value', 'custom_field_id')
            ->all();
    }

    public function sync(Child $child, Request $request): void
    {
        $validated = Validator::make($request->all(), [
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*' => ['nullable', 'string', 'max:1000'],
            'new_custom_fields' => ['nullable', 'array', 'max:20'],
            'new_custom_fields.*.label' => ['required', 'string', 'max:100'],
            'new_custom_fields.*.type' => ['required', Rule::in(self::TYPES)],
            'new_custom_fields.*.value' => ['nullable', 'string', 'max:1000'],
            'remove_custom_fields' => ['nullable', 'array'],
            'remove_custom_fields.*' => [
                'integer',
                Rule::exists('child_custom_fields', 'id')->where('is_active', true),
            ],
            'restore_custom_fields' => ['nullable', 'array'],
            'restore_custom_fields.*' => [
                'integer',
                Rule::exists('child_custom_fields', 'id')->where('is_active', 0),
            ],
            'restore_custom_field_values' => ['nullable', 'array'],
            'restore_custom_field_values.*' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $removedIds = array_map('intval', $validated['remove_custom_fields'] ?? []);
        $restoredIds = array_map('intval', $validated['restore_custom_fields'] ?? []);

        if (array_intersect($removedIds, $restoredIds)) {
            throw ValidationException::withMessages([
                'custom_fields' => 'A custom field cannot be removed and restored in the same submission.',
            ]);
        }

        $fields = ChildCustomField::query()
            ->whereIn('id', array_keys($validated['custom_fields'] ?? []))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
        $restoredFields = ChildCustomField::query()
            ->whereIn('id', $restoredIds)
            ->where('is_active', false)
            ->get()
            ->keyBy('id');

        foreach (($validated['custom_fields'] ?? []) as $fieldId => $value) {
            if (in_array((int) $fieldId, $removedIds, true)) {
                continue;
            }

            $field = $fields->get((int) $fieldId);
            if (!$field) {
                throw ValidationException::withMessages([
                    'custom_fields' => 'One or more submitted custom fields are no longer available.',
                ]);
            }
            $this->validateValue($field->type, $value, "custom_fields.{$fieldId}");
        }

        $newFields = $validated['new_custom_fields'] ?? [];
        $existingLabels = ChildCustomField::query()
            ->pluck('label')
            ->map(fn ($label) => Str::lower(trim($label)))
            ->all();

        foreach ($newFields as $index => $newField) {
            $normalizedLabel = Str::lower(trim($newField['label']));
            if (in_array($normalizedLabel, $existingLabels, true)) {
                throw ValidationException::withMessages([
                    "new_custom_fields.{$index}.label" => 'A field with this name already exists. Restore the existing field or choose a different name.',
                ]);
            }

            $this->validateValue(
                $newField['type'],
                $newField['value'] ?? null,
                "new_custom_fields.{$index}.value"
            );
            $existingLabels[] = $normalizedLabel;
        }

        foreach ($validated['restore_custom_field_values'] ?? [] as $fieldId => $value) {
            if (!in_array((int) $fieldId, $restoredIds, true)) {
                continue;
            }

            $field = $restoredFields->get((int) $fieldId);
            if (!$field) {
                throw ValidationException::withMessages([
                    'restore_custom_fields' => 'Select a removed field before entering its value.',
                ]);
            }
            $this->validateValue(
                $field->type,
                $value,
                "restore_custom_field_values.{$fieldId}"
            );
        }

        ChildCustomField::query()
            ->whereIn('id', $removedIds)
            ->update(['is_active' => false]);

        ChildCustomField::query()
            ->whereIn('id', $restoredIds)
            ->update(['is_active' => true]);

        foreach ($validated['custom_fields'] ?? [] as $fieldId => $value) {
            $child->customFieldValues()->updateOrCreate(
                ['custom_field_id' => $fieldId],
                ['value' => $value === '' ? null : $value]
            );
        }

        foreach ($validated['restore_custom_field_values'] ?? [] as $fieldId => $value) {
            if (in_array((int) $fieldId, $restoredIds, true)) {
                $child->customFieldValues()->updateOrCreate(
                    ['custom_field_id' => $fieldId],
                    ['value' => $value === '' ? null : $value]
                );
            }
        }

        foreach ($newFields as $newField) {
            $field = ChildCustomField::create([
                'label' => trim($newField['label']),
                'type' => $newField['type'],
                'is_active' => true,
            ]);

            $child->customFieldValues()->create([
                'custom_field_id' => $field->id,
                'value' => ($newField['value'] ?? '') === ''
                    ? null
                    : $newField['value'],
            ]);
        }
    }

    private function validateValue(string $type, ?string $value, string $key): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $rule = match ($type) {
            'number' => 'numeric',
            'date' => 'date',
            default => 'string',
        };

        $validator = Validator::make(
            ['value' => $value],
            ['value' => [$rule]]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                $key => $validator->errors()->first('value'),
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ChildCustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildCustomFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_fields_can_be_added_updated_removed_and_restored(): void
    {
        $this->actingAs(User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]));

        $child = Child::create([
            'child_code' => 'CH-CUSTOM-1',
            'first_name' => 'Alex',
            'last_name' => 'Child',
            'date_of_birth' => '2015-04-12',
            'gender' => 'other',
            'status' => 'active',
        ]);

        $this->get(route('children.edit', $child))
            ->assertOk()
            ->assertSee('Add extra field');

        $this->put(route('children.update', $child), $this->childData([
            'new_custom_fields' => [
                [
                    'label' => 'Shoe size',
                    'type' => 'text',
                    'value' => '3',
                ],
            ],
        ]))->assertRedirect(route('children.show', $child));

        $field = ChildCustomField::where('label', 'Shoe size')->firstOrFail();
        $this->assertDatabaseHas('child_custom_field_values', [
            'child_id' => $child->id,
            'custom_field_id' => $field->id,
            'value' => '3',
        ]);

        $this->get(route('children.show', $child))
            ->assertOk()
            ->assertSee('Shoe size')
            ->assertSee('3');

        $this->put(route('children.update', $child), $this->childData([
            'custom_fields' => [$field->id => '4'],
        ]))->assertRedirect(route('children.show', $child));

        $this->assertDatabaseHas('child_custom_field_values', [
            'child_id' => $child->id,
            'custom_field_id' => $field->id,
            'value' => '4',
        ]);

        $this->put(route('children.update', $child), $this->childData([
            'custom_fields' => [$field->id => '4'],
            'remove_custom_fields' => [$field->id],
        ]))->assertRedirect(route('children.show', $child));

        $this->assertDatabaseHas('child_custom_fields', [
            'id' => $field->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('child_custom_field_values', [
            'child_id' => $child->id,
            'custom_field_id' => $field->id,
            'value' => '4',
        ]);

        $restoreResponse = $this->put(route('children.update', $child), $this->childData([
            'restore_custom_fields' => [$field->id],
            'restore_custom_field_values' => [$field->id => '5'],
        ]));
        $restoreResponse
            ->assertRedirect(route('children.show', $child))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('child_custom_fields', [
            'id' => $field->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('child_custom_field_values', [
            'child_id' => $child->id,
            'custom_field_id' => $field->id,
            'value' => '5',
        ]);

        $this->get(route('child_officer.children.create'))
            ->assertOk()
            ->assertSee('Add extra field')
            ->assertSee('Shoe size');

        $this->get(route('child_officer.children.edit', $child))
            ->assertOk()
            ->assertSee('Shoe size');
    }

    public function test_custom_field_value_must_match_its_declared_type(): void
    {
        $this->actingAs(User::create([
            'name' => 'Admin User',
            'email' => 'admin-types@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]));

        $child = Child::create([
            'child_code' => 'CH-CUSTOM-2',
            'first_name' => 'Jamie',
            'last_name' => 'Child',
            'date_of_birth' => '2014-09-23',
            'gender' => 'other',
            'status' => 'active',
        ]);
        $field = ChildCustomField::create([
            'label' => 'Waist size',
            'type' => 'number',
            'is_active' => true,
        ]);

        $this->from(route('children.edit', $child))
            ->put(route('children.update', $child), $this->childData([
                'custom_fields' => [$field->id => 'not-a-number'],
            ]))
            ->assertRedirect(route('children.edit', $child))
            ->assertSessionHasErrors("custom_fields.{$field->id}");
    }

    public function test_child_officer_can_create_child_with_custom_field_values(): void
    {
        $this->actingAs(User::create([
            'name' => 'Child Officer',
            'email' => 'child-officer@example.test',
            'password' => 'password',
            'role' => 'child_officer',
            'status' => 'active',
        ]));

        $this->post(route('child_officer.children.store'), [
            'child_code' => 'CH-OFFICER-1',
            'first_name' => 'Taylor',
            'middle_name' => '',
            'last_name' => 'Officer',
            'date_of_birth' => '2015-03-01',
            'gender' => 'Other',
            'school_name' => '',
            'grade' => '',
            'district' => '',
            'gn_division' => '',
            'address' => '',
            'notes' => '',
            'status' => 'active',
            'new_custom_fields' => [
                [
                    'label' => 'Shoulder size',
                    'type' => 'number',
                    'value' => '32.5',
                ],
            ],
        ])->assertRedirect(route('child_officer.children.index'));

        $child = Child::where('child_code', 'CH-OFFICER-1')->firstOrFail();
        $field = ChildCustomField::where('label', 'Shoulder size')->firstOrFail();

        $this->assertDatabaseHas('child_custom_field_values', [
            'child_id' => $child->id,
            'custom_field_id' => $field->id,
            'value' => '32.5',
        ]);
    }

    private function childData(array $extra = []): array
    {
        return array_merge([
            'child_code' => 'CH-CUSTOM-1',
            'first_name' => 'Alex',
            'middle_name' => '',
            'last_name' => 'Child',
            'date_of_birth' => '2015-04-12',
            'gender' => 'other',
            'school_name' => '',
            'grade' => '',
            'district' => '',
            'gn_division' => '',
            'address' => '',
            'notes' => '',
            'status' => 'active',
        ], $extra);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Definitions of the fields on the child form.
        Schema::create('child_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();          // machine name, never changes
            $table->string('label', 120);
            $table->string('type', 20);                   // text|textarea|number|date|select|checkbox
            $table->json('options')->nullable();          // dropdown choices
            $table->boolean('is_required')->default(false);
            $table->boolean('is_system')->default(false); // built-in field stored in the children table
            $table->boolean('is_protected')->default(false); // cannot be edited or removed at all
            $table->boolean('is_active')->default(true);  // false = archived (hidden, data kept)
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // One value per child per custom field. Definitions are never hard-deleted (restrictOnDelete).
        Schema::create('child_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('child_form_field_id')->constrained('child_form_fields')->restrictOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['child_id', 'child_form_field_id']);
        });

        // Register the existing built-in fields (read-only registration, no existing data is touched).
        $system = [
            ['child_code', 'Child code', 'text', true, true],
            ['first_name', 'First name', 'text', true, false],
            ['middle_name', 'Middle name', 'text', false, false],
            ['last_name', 'Last name', 'text', true, false],
            ['date_of_birth', 'Date of birth', 'date', true, false],
            ['gender', 'Gender', 'select', true, false],
            ['school_name', 'School name', 'text', false, false],
            ['grade', 'Grade', 'text', false, false],
            ['district', 'District', 'text', false, false],
            ['gn_division', 'GN division', 'text', false, false],
            ['address', 'Address', 'textarea', false, false],
            ['notes', 'Notes', 'textarea', false, false],
            ['profile_photo', 'Profile photo', 'text', false, false],
        ];

        foreach ($system as $i => [$key, $label, $type, $required, $protected]) {
            DB::table('child_form_fields')->insertOrIgnore([
                'key' => $key, 'label' => $label, 'type' => $type,
                'is_required' => $required, 'is_system' => true, 'is_protected' => $protected,
                'is_active' => true, 'sort_order' => ($i + 1) * 10,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('child_field_values');
        Schema::dropIfExists('child_form_fields');
    }
};
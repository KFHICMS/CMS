<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_pagination_uses_the_compact_kfhi_markup(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-'.uniqid(),
            'email' => 'admin-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        foreach (range(1, 30) as $i) {
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'test_action',
                'module' => 'test_module',
                'description' => "Entry $i",
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.audit_logs.index'))
            ->assertOk()
            ->assertSee('class="k-pagination"', false)
            ->assertSee('rel="next"', false)
            ->assertSee('Showing 1 to 25 of 30 results')
            ->assertDontSee('w-5 h-5', false);

        $this->get(route('admin.audit_logs.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('rel="prev"', false)
            ->assertSee('Showing 26 to 30 of 30 results');
    }
}
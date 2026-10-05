<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/** Users, roles and permissions: case-insensitive e-mail, last sign-in, every permission exists. */
class UserAccountSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_stored_in_lower_case_and_unique_regardless_of_case(): void
    {
        $user = User::factory()->create(['email' => '  Budi.Santoso@Contoh.ID ']);
        $this->assertSame('budi.santoso@contoh.id', $user->email);

        $this->expectException(QueryException::class);
        DB::table('users')->insert(['name' => 'Kembar', 'email' => 'BUDI.SANTOSO@contoh.id', 'password' => 'x', 'user_type' => 'umum', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_login_works_with_any_capitalisation_and_records_the_time(): void
    {
        $user = User::factory()->create(['email' => 'rina@contoh.id']);

        $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', ['email' => 'Rina@Contoh.ID', 'password' => 'password', 'captcha' => 'ABCDE']);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_every_labelled_permission_exists_after_sync(): void
    {
        RoleAccess::sync();

        foreach (array_keys(RoleAccess::PERMISSION_LABELS) as $permission) {
            $this->assertTrue(Permission::where('name', $permission)->where('guard_name', 'web')->exists(), $permission);
        }
        $this->assertTrue(\Spatie\Permission\Models\Role::findByName('back_office')->hasPermissionTo('backoffice.access'));
    }

    public function test_unknown_user_types_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $user->id)->update(['user_type' => 'tamu']);
    }

    public function test_registering_an_existing_address_in_other_capitals_is_a_validation_error(): void
    {
        User::factory()->create(['email' => 'siti@contoh.id']);

        $this->post('/register', ['name' => 'Siti', 'email' => 'SITI@contoh.id', 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123'])
            ->assertSessionHasErrors('email');
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Resources\AppSettingResource\Pages as AppSettingPages;
use App\Filament\Resources\PengumumanResource\Pages as PengumumanPages;
use App\Filament\Resources\RoleResource\Pages as RolePages;
use App\Filament\Resources\ServiceCategoryResource\Pages as CategoryPages;
use App\Filament\Resources\UserResource\Pages as UserPages;
use App\Filament\Resources\VisitorResource\Pages as VisitorPages;
use App\Models\AppSetting;
use App\Models\Pengumuman;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The Filament panel (/cp) is the single admin UI for master data. These
 * tests drive the real forms so a resource that drifts from the schema
 * fails here instead of in production.
 */
class FilamentMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $role): User
    {
        \App\Support\RoleAccess::sync();
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(Role::findOrCreate($role));
        $this->actingAs($user);

        return $user;
    }

    public function test_only_admin_manages_master_data(): void
    {
        // Every staff role works in the panel, but master data stays with administrators.
        foreach (['kepala_sekolah', 'kepala_tu', 'back_office', 'front_desk', 'supervisor'] as $role) {
            $this->actingAsRole($role);
            $this->get('/cp')->assertOk();
            $this->get('/cp/users')->assertForbidden();
            $this->get('/cp/users/create')->assertForbidden();
            $this->get('/cp/roles/create')->assertForbidden();
            $this->get('/cp/services/create')->assertForbidden();
            $this->get('/cp/app-settings')->assertForbidden();
            auth()->logout();
        }

        $this->actingAsRole('admin');
        $this->get('/cp')->assertOk();
        $this->get('/cp/users/create')->assertOk();
    }

    public function test_admin_manages_users_and_roles_through_forms(): void
    {
        $this->actingAsRole('admin');
        Role::findOrCreate('front_desk');

        Livewire::test(UserPages\CreateUser::class)
            ->fillForm([
                'name' => 'Petugas Baru',
                'email' => 'petugas.baru@example.test',
                'user_type' => 'pegawai',
                'password' => 'RahasiaKuat123!',
                'is_active' => true,
                'roles' => [Role::findByName('front_desk')->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::where('email', 'petugas.baru@example.test')->firstOrFail();
        $this->assertTrue($created->hasRole('front_desk'));
        $this->assertTrue(\Hash::check('RahasiaKuat123!', $created->password));
        $originalHash = $created->password;

        // Editing without typing a password must keep the existing one.
        Livewire::test(UserPages\EditUser::class, ['record' => $created->getRouteKey()])
            ->fillForm(['name' => 'Petugas Diubah'])
            ->call('save')
            ->assertHasNoFormErrors();

        $created->refresh();
        $this->assertSame('Petugas Diubah', $created->name);
        $this->assertSame($originalHash, $created->password);

        Livewire::test(RolePages\CreateRole::class)
            ->fillForm(['name' => 'peninjau', 'guard_name' => 'web'])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertTrue(Role::where('name', 'peninjau')->exists());
    }

    public function test_admin_manages_service_categories(): void
    {
        $this->actingAsRole('admin');

        Livewire::test(CategoryPages\CreateServiceCategory::class)
            ->fillForm(['name' => 'Kategori Uji', 'order' => 3, 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = ServiceCategory::where('name', 'Kategori Uji')->firstOrFail();

        Livewire::test(CategoryPages\EditServiceCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Kategori Diubah'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Kategori Diubah', $category->refresh()->name);
    }

    public function test_counter_registers_guests_and_admin_corrects_them(): void
    {
        $host = \App\Models\User::factory()->create(['user_type' => 'guru', 'name' => 'Bu Guru']);

        $this->actingAsRole('admin');

        Livewire::test(VisitorPages\CreateVisitor::class)
            ->fillForm([
                'name' => 'Tamu Uji',
                'institution' => 'Dinas Pendidikan',
                'purpose' => 'Koordinasi',
                'person_to_meet_id' => $host->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $visitor = Visitor::where('name', 'Tamu Uji')->firstOrFail();
        $this->assertSame('active', $visitor->status);
        $this->assertSame('Bu Guru', $visitor->person_to_meet);
        $this->assertNotNull($visitor->check_in_time);

        Livewire::test(VisitorPages\EditVisitor::class, ['record' => $visitor->getRouteKey()])
            ->fillForm(['purpose' => 'Rapat'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Rapat', $visitor->refresh()->purpose);

        Livewire::test(VisitorPages\ListVisitors::class)
            ->callTableAction('checkOut', $visitor);

        $this->assertNotNull($visitor->refresh()->check_out_time);
        $this->assertSame('checked_out', $visitor->status);
    }

    public function test_admin_manages_announcements(): void
    {
        $this->actingAsRole('admin');

        Livewire::test(PengumumanPages\CreatePengumuman::class)
            ->fillForm([
                'title' => 'Libur Semester',
                'content' => '<p>Madrasah libur.</p>',
                'publish_date' => now()->toDateString(),
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = Pengumuman::where('title', 'Libur Semester')->firstOrFail();

        Livewire::test(PengumumanPages\EditPengumuman::class, ['record' => $item->getRouteKey()])
            ->fillForm(['title' => 'Libur Semester Genap'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Libur Semester Genap', $item->refresh()->title);
    }

    public function test_admin_manages_application_settings(): void
    {
        $this->actingAsRole('admin');

        Livewire::test(AppSettingPages\CreateAppSetting::class)
            ->fillForm([
                'key' => 'app_tagline',
                'display_name' => 'Tagline',
                'type' => 'text',
                'value' => 'Melayani dengan hati',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $setting = AppSetting::where('key', 'app_tagline')->firstOrFail();

        Livewire::test(AppSettingPages\EditAppSetting::class, ['record' => $setting->getRouteKey()])
            ->fillForm(['value' => 'Cepat dan transparan'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Cepat dan transparan', $setting->refresh()->value);
    }

    public function test_every_resource_list_renders_with_data(): void
    {
        $this->actingAsRole('admin');
        Visitor::factory()->count(2)->create();
        ServiceCategory::create(['name' => 'Kategori A', 'order' => 1, 'is_active' => true]);
        Pengumuman::create(['title' => 'Info', 'content' => 'Isi', 'publish_date' => now(), 'is_active' => true, 'user_id' => auth()->id()]);
        AppSetting::create(['key' => 'k', 'value' => 'v', 'type' => 'text', 'category' => 'general']);

        $lists = [
            \App\Filament\Resources\UserResource\Pages\ListUsers::class,
            \App\Filament\Resources\RoleResource\Pages\ListRoles::class,
            \App\Filament\Resources\ServiceCategoryResource\Pages\ListServiceCategories::class,
            \App\Filament\Resources\VisitorResource\Pages\ListVisitors::class,
            \App\Filament\Resources\PengumumanResource\Pages\ListPengumumen::class,
            \App\Filament\Resources\AppSettingResource\Pages\ListAppSettings::class,
            \App\Filament\Resources\ServiceResource\Pages\ListServices::class,
            \App\Filament\Resources\ComplaintResource\Pages\ListComplaints::class,
            \App\Filament\Resources\FaqResource\Pages\ListFaqs::class,
            \App\Filament\Resources\SurveyQuestionResource\Pages\ListSurveyQuestions::class,
            \App\Filament\Resources\RegistrationCodeResource\Pages\ListRegistrationCodes::class,
            \App\Filament\Resources\WorkflowResource\Pages\ListWorkflows::class,
        ];

        foreach ($lists as $page) {
            Livewire::test($page)->assertSuccessful();
        }
    }
}

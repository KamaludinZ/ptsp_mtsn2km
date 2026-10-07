<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Identitas pelapor anonim & dirahasiakan tidak bocor ke tampilan dan rekap. */
class ComplaintReporterLabelTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    private function report(array $attributes): Complaint
    {
        return Complaint::create($attributes + ['title' => 'Uji', 'description' => 'Uraian', 'status' => 'submitted', 'reporter_name' => 'Siti Pelapor']);
    }

    public function test_labels_for_each_kind_of_report(): void
    {
        $handler = $this->user('supervisor');
        $officer = $this->user('front_desk');

        $anonymousWbs = $this->report(['complaint_type' => 'whistleblowing', 'anonymous' => true]);
        $namedWbs = $this->report(['complaint_type' => 'whistleblowing', 'anonymous' => false]);
        $confidential = $this->report(['complaint_type' => 'complaint', 'is_confidential' => true]);
        $ordinary = $this->report(['complaint_type' => 'complaint']);

        $this->assertSame('Anonim', $anonymousWbs->reporterLabel($handler));
        $this->assertSame('Siti Pelapor', $namedWbs->reporterLabel($handler));
        $this->assertSame('Identitas dirahasiakan', $namedWbs->reporterLabel($officer));
        $this->assertSame('Siti Pelapor', $confidential->reporterLabel($handler));
        $this->assertSame('Identitas dirahasiakan', $confidential->reporterLabel($officer));
        $this->assertSame('Identitas dirahasiakan', $confidential->reporterLabel(null));
        $this->assertSame('Siti Pelapor', $ordinary->reporterLabel($officer));
    }

    public function test_handler_api_keeps_anonymous_reporters_anonymous(): void
    {
        $this->report(['complaint_type' => 'whistleblowing', 'anonymous' => true, 'title' => 'Laporan anonim']);
        Sanctum::actingAs($this->user('supervisor'));

        $rows = collect($this->getJson('/api/pengaduan')->assertOk()->json('data'));

        $this->assertSame('Anonim', $rows->firstWhere('judul', 'Laporan anonim')['pelapor']);
        $this->assertNotContains('Siti Pelapor', $rows->where('judul', 'Laporan anonim')->pluck('pelapor')->all());
    }
}

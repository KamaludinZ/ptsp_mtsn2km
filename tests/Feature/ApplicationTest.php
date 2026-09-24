<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Complaint;
use App\Models\Survey;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login(): void
    {
        Http::fake();
        Role::findOrCreate('umum');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp_number' => '081234567890',
            'user_type' => 'umum',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_user_can_view_service_catalog(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)
            ->get('/onlineportal/service-catalog');

        $response->assertStatus(200);
    }

    public function test_user_can_apply_for_service(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)
            ->post("/onlineportal/apply-service/{$service->id}", [
                'service_details' => 'Test service application',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_user_can_submit_complaint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/supervision/complaint/submit', [
                'complaint_type' => 'complaint',
                'title' => 'Test Complaint',
                'description' => 'This is a test complaint',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('complaints', [
            'user_id' => $user->id,
            'title' => 'Test Complaint',
        ]);
    }

    public function test_user_can_submit_survey(): void
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create();
        $question = $survey->questions()->create([
            'question_text' => 'How was your experience?',
            'question_type' => 'rating',
            'order' => 1,
        ]);

        $response = $this->actingAs($user)
            ->post("/supervision/survey/{$survey->id}/submit", [
                'answers' => [
                    $question->id => '5',
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);
    }

    public function test_front_desk_can_register_visitor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tu');

        $response = $this->actingAs($user)
            ->post('/frontdesk/visitor/check-in', [
                'name' => 'John Doe',
                'institution' => 'Test Institution',
                'purpose' => 'Meeting with staff',
                'person_to_meet' => 'Admin Staff',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('visitors', [
            'name' => 'John Doe',
            'institution' => 'Test Institution',
        ]);
    }

    public function test_back_office_user_can_process_ticket(): void
    {
        $user = User::factory()->create();
        $user->assignRole('tu');
        
        $applicant = User::factory()->create();
        $service = Service::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $applicant->id,
            'service_id' => $service->id,
            'created_by' => $applicant->id,
        ]);

        $response = $this->actingAs($user)
            ->put("/backoffice/ticket/{$ticket->id}", [
                'status' => 'in_process',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_process',
        ]);
    }
}
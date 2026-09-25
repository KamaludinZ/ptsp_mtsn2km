<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Complaint;
use App\Models\Survey;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_creation(): void
    {
        $user = User::factory()->make();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
    }

    public function test_service_model_creation(): void
    {
        $service = Service::factory()->make();
        
        $this->assertInstanceOf(Service::class, $service);
        $this->assertIsString($service->name);
        $this->assertIsString($service->code);
        $this->assertIsArray($service->user_types_allowed);
    }

    public function test_ticket_model_creation(): void
    {
        $ticket = Ticket::factory()->make();
        
        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertIsString($ticket->ticket_number);
        $this->assertContains($ticket->status, [
            'submitted', 'verified', 'in_process', 
            'approved', 'rejected', 'completed', 'cancelled'
        ]);
    }

    public function test_complaint_model_creation(): void
    {
        $complaint = Complaint::factory()->make();
        
        $this->assertInstanceOf(Complaint::class, $complaint);
        $this->assertIsString($complaint->title);
        $this->assertContains($complaint->complaint_type, [
            'complaint', 'suggestion', 'whistleblowing'
        ]);
    }

    public function test_survey_model_creation(): void
    {
        $survey = Survey::factory()->make();
        
        $this->assertInstanceOf(Survey::class, $survey);
        $this->assertIsString($survey->name);
        $this->assertContains($survey->type, ['skm', 'spak', 'other']);
    }

    public function test_user_type_constants(): void
    {
        $user = User::factory()->make();
        
        $this->assertEquals('guru', User::USER_TYPE_GURU);
        $this->assertEquals('pegawai', User::USER_TYPE_PEGAWAI);
        $this->assertEquals('siswa', User::USER_TYPE_SISWA);
        $this->assertEquals('walimurid', User::USER_TYPE_WALIMURID);
        $this->assertEquals('alumni', User::USER_TYPE_ALUMNI);
        $this->assertEquals('instansi', User::USER_TYPE_INSTANSI);
        $this->assertEquals('umum', User::USER_TYPE_UMUM);
    }

    public function test_ticket_status_constants(): void
    {
        $ticket = Ticket::factory()->make();
        
        $this->assertEquals('submitted', Ticket::STATUS_SUBMITTED);
        $this->assertEquals('completed', Ticket::STATUS_COMPLETED);
        $this->assertEquals('cancelled', Ticket::STATUS_CANCELLED);
    }

    public function test_complaint_type_constants(): void
    {
        $complaint = Complaint::factory()->make();
        
        $this->assertEquals('complaint', Complaint::TYPE_COMPLAINT);
        $this->assertEquals('suggestion', Complaint::TYPE_SUGGESTION);
        $this->assertEquals('whistleblowing', Complaint::TYPE_WHISTLEBLOWING);
    }

    public function test_visitor_public_name_and_phone_are_masked(): void
    {
        $visitor = new \App\Models\Visitor(['name' => 'Budi Santoso', 'phone' => '0812-3456-7890']);

        $this->assertSame('Budi Santoso', $visitor->publicName());

        $visitor->is_obscured = true;
        $this->assertSame('B**i S*****o', $visitor->publicName());
        $this->assertSame('*********890', $visitor->maskedPhone());
    }

    public function test_faq_answer_is_sanitized_for_public_display(): void
    {
        $faq = new \App\Models\Faq();
        $faq->answer = '<p onclick="x()">Buka <strong>07.00</strong></p><script>alert(1)</script><a href="javascript:alert(1)">klik</a>';

        $html = $faq->safeAnswer();

        $this->assertStringContainsString('<strong>07.00</strong>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }
}

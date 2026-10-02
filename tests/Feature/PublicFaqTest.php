<?php

namespace Tests\Feature;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The public FAQ page and the FAQ excerpt on the about page. */
class PublicFaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_lists_active_questions_with_search(): void
    {
        Faq::forceCreate(['question' => 'Berapa lama legalisir ijazah?', 'answer' => 'Satu hari kerja.', 'is_active' => true]);
        Faq::forceCreate(['question' => 'Pertanyaan tersembunyi', 'answer' => 'x', 'is_active' => false]);

        $this->get(route('public.faq'))
            ->assertOk()
            ->assertSee('Pertanyaan Umum (FAQ)')
            ->assertSee('Berapa lama legalisir ijazah?')
            ->assertDontSee('Pertanyaan tersembunyi')
            ->assertSee('data-faq-search', false);
    }

    public function test_about_page_shows_five_questions_and_links_to_all(): void
    {
        foreach (range(1, 6) as $i) {
            Faq::forceCreate(['question' => "Pertanyaan nomor {$i}", 'answer' => 'Jawaban.', 'is_active' => true]);
        }

        $this->get(route('public.about'))
            ->assertOk()
            ->assertSee(route('public.faq'), false)
            ->assertDontSee('data-faq-search', false);

        $this->assertSame(5, substr_count($this->get(route('public.about'))->getContent(), 'data-faq-text='));
    }
}

<?php

namespace Tests\Feature;

use App\Support\StatusBadge;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/** Label status berwarna: shared colors and the <x-status-badge> component. */
class StatusBadgeTest extends TestCase
{
    public function test_each_status_type_has_a_label_and_color(): void
    {
        $this->assertSame(['label' => 'Selesai', 'color' => 'success', 'icon' => 'check-circle'], StatusBadge::for('completed'));
        $this->assertSame('danger', StatusBadge::for('rejected')['color']);
        $this->assertSame('warning', StatusBadge::for('in_process')['color']);
        $this->assertSame(['Ditelaah', 'info'], array_values(array_slice(StatusBadge::for('in_review', 'complaint'), 0, 2)));
        $this->assertSame(['Ditolak', 'danger'], array_values(array_slice(StatusBadge::for('rejected', 'approval'), 0, 2)));
        $this->assertSame(['Gagal', 'danger'], array_values(array_slice(StatusBadge::for('failed', 'update'), 0, 2)));
        $this->assertSame('gray', StatusBadge::for('tidak-dikenal')['color']);
        $this->assertSame('–', StatusBadge::for(null)['label']);
    }

    public function test_component_renders_colored_label_with_styles_once(): void
    {
        $html = Blade::render('<x-status-badge status="completed" /><x-status-badge status="resolved" type="complaint" size="sm" id="b" />');

        $this->assertStringContainsString('status-pill--success', $html);
        $this->assertStringContainsString('status-pill--sm', $html);
        $this->assertStringContainsString('>Selesai<', $html);
        $this->assertStringContainsString('data-status="resolved"', $html);
        $this->assertStringContainsString('id="b"', $html);
        $this->assertSame(1, substr_count($html, '<style>'));
    }
}

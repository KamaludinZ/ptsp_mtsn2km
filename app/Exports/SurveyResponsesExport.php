<?php

namespace App\Exports;

use App\Models\SurveyResponse;
use App\Models\SurveyQuestion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyResponsesExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $editionId;

    public function __construct($startDate = null, $endDate = null, $editionId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->editionId = $editionId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Summary
        $sheets[] = new SurveySummarySheet($this->startDate, $this->endDate, $this->editionId);

        // Sheet 2: Identity Responses
        $sheets[] = new IdentityResponsesSheet($this->startDate, $this->endDate, $this->editionId);

        // Sheet 3: SKM Responses
        $sheets[] = new SKMResponsesSheet($this->startDate, $this->endDate, $this->editionId);

        // Sheet 4: SPAK Responses
        $sheets[] = new SPAKResponsesSheet($this->startDate, $this->endDate, $this->editionId);

        return $sheets;
    }
}

// Summary Sheet
class SurveySummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $editionId;

    public function __construct($startDate, $endDate, $editionId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->editionId = $editionId;
    }

    public function collection()
    {
        $query = SurveyResponse::query();

        if ($this->editionId) {
            $query->where('survey_edition_id', $this->editionId);
        }
        if ($this->startDate) {
            $query->whereDate('completed_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('completed_at', '<=', $this->endDate);
        }

        $responses = $query->with('answers.question')->get();

        // Calculate IKM and IPAK
        $totalSkmScore = 0;
        $totalSkmCount = 0;
        $totalSpakScore = 0;
        $maxSpakScore = 0;

        foreach ($responses as $response) {
            foreach ($response->answers as $answer) {
                if ($answer->question->type === 'skm') {
                    $totalSkmScore += $this->getAnswerValue($answer->selected_option);
                    $totalSkmCount++;
                } elseif ($answer->question->type === 'spak') {
                    $totalSpakScore += $this->getAnswerValue($answer->selected_option);
                    $maxSpakScore += 4;
                }
            }
        }

        $ikm = $totalSkmCount > 0 ? ($totalSkmScore / $totalSkmCount) * 25 : 0;
        $ipak = $maxSpakScore > 0 ? ($totalSpakScore / $maxSpakScore) * 100 : 0;

        $data = collect([
            ['Metrik', 'Nilai'],
            ['Total Responden', $responses->count()],
            ['', ''],
            ['INDEKS KEPUASAN MASYARAKAT (IKM)', ''],
            ['Skor IKM', number_format($ikm, 2)],
            ['Kategori IKM', $this->getIKMCategory($ikm)],
            ['', ''],
            ['INDEKS PERSEPSI ANTI KORUPSI (IPAK)', ''],
            ['Skor IPAK', number_format($ipak, 2)],
            ['Kategori IPAK', $this->getIPAKCategory($ipak)],
            ['', ''],
            ['Periode Data', ''],
            ['Tanggal Mulai', $this->startDate ?? 'Semua'],
            ['Tanggal Akhir', $this->endDate ?? 'Semua'],
            ['Tanggal Export', now()->format('d F Y H:i:s')],
        ]);

        return $data;
    }

    public function headings(): array
    {
        return ['RINGKASAN HASIL SURVEY', ''];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => '10B981']]],
            8 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FBBF24']]],
            12 => ['font' => ['bold' => true]],
        ];
    }

    private function getAnswerValue($option)
    {
        $valueMap = [
            'Tidak Sesuai' => 1, 'Kurang Sesuai' => 2, 'Sesuai' => 3, 'Sangat Sesuai' => 4,
            'Tidak Mudah' => 1, 'Kurang Mudah' => 2, 'Mudah' => 3, 'Sangat Mudah' => 4,
            'Tidak Cepat' => 1, 'Kurang Cepat' => 2, 'Cepat' => 3, 'Sangat Cepat' => 4,
            'Tidak Bagus' => 1, 'Kurang Bagus' => 2, 'Bagus' => 3, 'Sangat Bagus' => 4,
            'Tidak Mampu' => 1, 'Kurang Mampu' => 2, 'Mampu' => 3, 'Sangat Mampu' => 4,
            'Tidak Sopan' => 1, 'Kurang Sopan' => 2, 'Sopan' => 3, 'Sangat Sopan' => 4,
            'Tidak Jelas' => 1, 'Kurang Jelas' => 2, 'Jelas' => 3, 'Sangat Jelas' => 4,
            'Selalu Tidak Sesuai' => 1, 'Terkadang Sesuai' => 2, 'Sesuai' => 3, 'Selalu Sesuai' => 4,
            'Sangat sering' => 1, 'Sering' => 2, 'Jarang' => 3, 'Tidak Pernah' => 4,
            'Tidak Transparan' => 1, 'Kurang Transparan' => 2, 'Transparan' => 3, 'Sangat Transparan' => 4,
        ];

        return $valueMap[$option] ?? 0;
    }

    private function getIKMCategory($score)
    {
        if ($score >= 88.31 && $score <= 100) return 'A - Sangat Baik';
        if ($score >= 76.61 && $score <= 88.30) return 'B - Baik';
        if ($score >= 65.00 && $score <= 76.60) return 'C - Kurang Baik';
        if ($score >= 25.00 && $score <= 64.99) return 'D - Tidak Baik';
        return 'N/A';
    }

    private function getIPAKCategory($score)
    {
        if ($score >= 80) return 'Sangat Baik';
        if ($score >= 60 && $score < 80) return 'Baik';
        if ($score >= 40 && $score < 60) return 'Cukup';
        if ($score < 40) return 'Perlu Perbaikan';
        return 'N/A';
    }
}

// Identity Responses Sheet
class IdentityResponsesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $editionId;

    public function __construct($startDate, $endDate, $editionId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->editionId = $editionId;
    }

    public function collection()
    {
        $query = SurveyResponse::query();

        if ($this->editionId) {
            $query->where('survey_edition_id', $this->editionId);
        }
        if ($this->startDate) {
            $query->whereDate('completed_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('completed_at', '<=', $this->endDate);
        }

        return $query->with(['answers.question' => function ($query) {
            $query->where('type', 'identity')->orderBy('order');
        }])->get();
    }

    public function headings(): array
    {
        $identityQuestions = SurveyQuestion::where('type', 'identity')
            ->orderBy('order')
            ->pluck('question')
            ->toArray();

        return array_merge(['No', 'Tanggal Mengisi', 'IP Address'], $identityQuestions);
    }

    public function map($response): array
    {
        $row = [
            $response->id,
            $response->completed_at->format('d/m/Y H:i'),
            $response->ip_address ?? '-',
        ];

        $identityAnswers = $response->answers->filter(function ($answer) {
            return $answer->question && $answer->question->type === 'identity';
        })->sortBy('question.order');

        foreach ($identityAnswers as $answer) {
            $row[] = $answer->selected_option ?? $answer->answer_text ?? '-';
        }

        return $row;
    }

    public function title(): string
    {
        return 'Data Identitas';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3B82F6']]],
        ];
    }
}

// SKM Responses Sheet
class SKMResponsesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $editionId;

    public function __construct($startDate, $endDate, $editionId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->editionId = $editionId;
    }

    public function collection()
    {
        $query = SurveyResponse::query();

        if ($this->editionId) {
            $query->where('survey_edition_id', $this->editionId);
        }
        if ($this->startDate) {
            $query->whereDate('completed_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('completed_at', '<=', $this->endDate);
        }

        return $query->with(['answers.question' => function ($query) {
            $query->where('type', 'skm')->orderBy('order');
        }])->get();
    }

    public function headings(): array
    {
        $skmQuestions = SurveyQuestion::where('type', 'skm')
            ->orderBy('order')
            ->pluck('question')
            ->toArray();

        return array_merge(['No', 'Tanggal'], $skmQuestions);
    }

    public function map($response): array
    {
        $row = [
            $response->id,
            $response->completed_at->format('d/m/Y H:i'),
        ];

        $skmAnswers = $response->answers->filter(function ($answer) {
            return $answer->question && $answer->question->type === 'skm';
        })->sortBy('question.order');

        foreach ($skmAnswers as $answer) {
            $row[] = $answer->selected_option ?? '-';
        }

        return $row;
    }

    public function title(): string
    {
        return 'Data SKM';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '10B981']]],
        ];
    }
}

// SPAK Responses Sheet
class SPAKResponsesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $editionId;

    public function __construct($startDate, $endDate, $editionId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->editionId = $editionId;
    }

    public function collection()
    {
        $query = SurveyResponse::query();

        if ($this->editionId) {
            $query->where('survey_edition_id', $this->editionId);
        }
        if ($this->startDate) {
            $query->whereDate('completed_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('completed_at', '<=', $this->endDate);
        }

        return $query->with(['answers.question' => function ($query) {
            $query->where('type', 'spak')->orderBy('order');
        }])->get();
    }

    public function headings(): array
    {
        $spakQuestions = SurveyQuestion::where('type', 'spak')
            ->orderBy('order')
            ->pluck('question')
            ->toArray();

        return array_merge(['No', 'Tanggal'], $spakQuestions);
    }

    public function map($response): array
    {
        $row = [
            $response->id,
            $response->completed_at->format('d/m/Y H:i'),
        ];

        $spakAnswers = $response->answers->filter(function ($answer) {
            return $answer->question && $answer->question->type === 'spak';
        })->sortBy('question.order');

        foreach ($spakAnswers as $answer) {
            $row[] = $answer->selected_option ?? '-';
        }

        return $row;
    }

    public function title(): string
    {
        return 'Data SPAK';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FBBF24']]],
        ];
    }
}

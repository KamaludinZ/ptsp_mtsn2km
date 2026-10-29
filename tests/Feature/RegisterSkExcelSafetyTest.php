<?php

namespace Tests\Feature;

use App\Exports\SkRegisterRecapExport;
use App\Exports\SuratKeputusanExport;
use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\SkRegisterRecap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

/** Downloaded SK workbooks keep typed-in text as text: nothing in them runs as a formula. */
class RegisterSkExcelSafetyTest extends TestCase
{
    use RefreshDatabase;

    private const FORMULA = '=HYPERLINK("http://contoh.invalid","klik")';

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    private function workbook(object $export): Spreadsheet
    {
        $base = tempnam(sys_get_temp_dir(), 'sk');
        $path = $base . '.xlsx';
        file_put_contents($path, Excel::raw($export, ExcelFormat::XLSX));
        // Maatwebsite leaves the export's value binder in place; read the file back as written.
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder);
        $book = IOFactory::load($path);
        unlink($path);
        unlink($base);

        return $book;
    }

    public function test_the_register_workbook_writes_formulas_as_text(): void
    {
        DB::table('surat_keputusan')->where('nomor_urut', 1)->update(['perihal' => self::FORMULA, 'nama_peminta' => '=1+1', 'keterangan' => '@SUM(1+1)']);

        $sheet = $this->workbook(new SuratKeputusanExport(SuratKeputusan::query()->orderBy('nomor_urut')))->getActiveSheet();

        foreach (['D2' => self::FORMULA, 'F2' => '=1+1', 'G2' => '@SUM(1+1)'] as $cell => $text) {
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell($cell)->getDataType(), $cell);
            $this->assertSame($text, $sheet->getCell($cell)->getValue(), $cell);
        }
        // Numbers are still numbers.
        $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('A2')->getDataType());
        $this->assertSame(1, $sheet->getCell('A2')->getValue());
    }

    public function test_the_recap_workbook_writes_formulas_as_text(): void
    {
        PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => '=2+2 Jenis', 'is_active' => true, 'sort' => 9]);

        $sheet = $this->workbook(new SkRegisterRecapExport(SkRegisterRecap::build(now()->year)))->getSheetByName('Per Jenis SK');
        $row = collect(range(2, $sheet->getHighestRow()))->first(fn (int $r) => $sheet->getCell("A{$r}")->getValue() === '=2+2 Jenis');

        $this->assertNotNull($row);
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell("A{$row}")->getDataType());
        $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell("C{$row}")->getDataType());
    }
}

<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Contoh layanan berisi rich text (demo data, never seeded in production):
 * the detail fields as TinyMCE stores them on the live site (paragraphs,
 * lists, a table, links, inline colour, headings) and one older JSON list,
 * open to every visitor, so the cards and details of /services can be
 * checked against real-looking content.
 */
class ServiceRichTextSeeder extends Seeder
{
    public const SERVICES = ['SKP-021', 'PSP-022', 'LDK-023'];

    public function run(): void
    {
        $adminId = User::role('admin')->orderBy('id')->value('id');
        $category = ServiceCategory::where('name', 'Layanan Umum')->value('id');

        foreach ($this->services() as $data) {
            $service = Service::updateOrCreate(['code' => $data['code']], [
                ...$data,
                'fee' => 0,
                'user_types_allowed' => null, // open to every visitor
                'is_active' => true,
                'created_by' => $adminId,
            ]);
            if ($category) {
                $service->categories()->syncWithoutDetaching([$category]);
            }
        }
    }

    private function services(): array
    {
        return [
            [
                'code' => 'SKP-021',
                'name' => 'Surat Keterangan Pindah (Mutasi) Siswa',
                'mode' => 'hybrid',
                'processing_time' => '3 hari kerja',
                'description' => '<p>Penerbitan surat keterangan pindah bagi siswa yang <strong>pindah ke madrasah atau sekolah lain</strong>, lengkap dengan rapor dan dokumen pendukung.</p>',
                'requirements' => '<ol><li>Surat permohonan pindah dari orang tua/wali <em>bermeterai Rp10.000</em>.</li><li>Fotokopi Kartu Keluarga dan akta kelahiran.</li><li>Surat keterangan <strong>diterima</strong> dari madrasah/sekolah tujuan.</li><li>Rapor asli siswa.</li></ol>'
                    . '<p><span style="color: #e03e2d;">Berkas yang belum lengkap tidak dapat diproses.</span></p>',
                'mechanism' => '<p>Alur layanan:</p>'
                    . '<table style="border-collapse: collapse; width: 100%;" border="1"><thead><tr><th>Tahap</th><th>Petugas</th><th>Waktu</th></tr></thead>'
                    . '<tbody><tr><td>Verifikasi berkas</td><td>Loket PTSP</td><td>1 hari</td></tr><tr><td>Persetujuan Kepala Madrasah</td><td>Kepala Madrasah</td><td>1 hari</td></tr>'
                    . '<tr><td>Penerbitan surat pindah</td><td>Tata Usaha</td><td>1 hari</td></tr></tbody></table>',
                'product' => '<ul><li>Surat keterangan pindah</li><li>Rapor asli dan transkrip nilai</li></ul>',
                'complaint_handling' => '<p>Sampaikan pengaduan melalui <a href="/pengaduan">halaman pengaduan</a> atau WhatsApp PTSP <strong>0851 8337 5008</strong>.</p>',
            ],
            [
                'code' => 'PSP-022',
                'name' => 'Peminjaman Sarana dan Prasarana Madrasah',
                'mode' => 'offline',
                'processing_time' => '2 hari kerja',
                'description' => '<p>Peminjaman aula, lapangan, dan ruang kelas untuk kegiatan instansi atau masyarakat di luar jam pelajaran.</p><p>&nbsp;</p>',
                'requirements' => '<h3>Instansi</h3><ul><li>Surat permohonan resmi berkop instansi.</li><li>Rencana kegiatan:<ul><li>tanggal dan jam pemakaian;</li><li>perkiraan jumlah peserta.</li></ul></li></ul>'
                    . '<h3>Perorangan</h3><ul><li>Fotokopi KTP penanggung jawab.</li></ul>',
                'mechanism' => '<ol><li>Ajukan permohonan di loket PTSP paling lambat <strong>3 hari</strong> sebelum kegiatan.</li><li>Petugas sarpras memeriksa jadwal pemakaian.</li><li>Permohonan disetujui Waka Sarpras.</li></ol>'
                    . '<blockquote><p>Sarana dikembalikan dalam keadaan bersih dan utuh.</p></blockquote>',
                'product' => '<p>Surat izin peminjaman sarana<br>beserta jadwal pemakaian.</p>',
                'complaint_handling' => '<p>Hubungi Waka Sarpras melalui loket PTSP pada jam layanan.</p>',
            ],
            [
                'code' => 'LDK-023',
                'name' => 'Legalisir Dokumen Kelulusan',
                'mode' => 'online',
                'processing_time' => '1-2 hari kerja',
                'description' => 'Legalisir ijazah, SKHUN, dan surat keterangan lulus untuk alumni.',
                // Older value: a JSON list, as some services were first saved.
                'requirements' => json_encode(['Fotokopi ijazah yang akan dilegalisir', 'Ijazah asli untuk dicocokkan', 'Kartu identitas alumni']),
                'mechanism' => '1. Ajukan permohonan online 2. Bawa dokumen asli ke loket 3. Ambil hasil legalisir',
                'product' => 'Dokumen kelulusan yang telah dilegalisir.',
                'complaint_handling' => null,
            ],
        ];
    }
}

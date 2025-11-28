<x-mail::message>
# Terima Kasih atas Partisipasi Anda

Yth. {{ $respondentName }},

Terima kasih telah meluangkan waktu untuk mengisi Survei Kepuasan Masyarakat (SKM) kami. Partisipasi Anda sangat berarti bagi kami dalam upaya meningkatkan kualitas pelayanan PTSP MTsN 2 Kota Malang.

## Detail Survei Anda

**Waktu Pengisian:** {{ $surveyResponse->completed_at->format('d F Y, H:i') }} WIB
@if($surveyResponse->ticket_code)
**Nomor Tiket Layanan:** {{ $surveyResponse->ticket_code }}
@endif

Masukan dan penilaian Anda akan kami gunakan sebagai bahan evaluasi dan perbaikan berkelanjutan untuk memberikan pelayanan yang lebih baik di masa mendatang.

<x-mail::button :url="url('/')">
Kunjungi Website Kami
</x-mail::button>

Kami berkomitmen untuk terus meningkatkan kualitas layanan kami. Jika Anda memiliki pertanyaan atau memerlukan bantuan lebih lanjut, jangan ragu untuk menghubungi kami.

Salam hormat,<br>
**{{ config('app.name') }}**<br>
PTSP MTsN 2 Kota Malang
</x-mail::message>

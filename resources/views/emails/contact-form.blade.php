@component('mail::message')
# Pesan Baru dari Formulir Kontak

**Nama:** {{ $data['name'] }}
**Email:** {{ $data['email'] }}
**Subjek:** {{ $data['subject'] }}

**Pesan:**
{{ $data['message'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent

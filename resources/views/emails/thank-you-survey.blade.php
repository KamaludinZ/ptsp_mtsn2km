<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Terima Kasih</title>
</head>
<body>
    <h1>Terima Kasih telah Mengisi Survei</h1>
    
    <p>Yang Terhormat,</p>
    
    <p>Terima kasih atas partisipasi Anda dalam mengisi survei kepuasan masyarakat. Pendapat Anda sangat berharga bagi kami dalam meningkatkan kualitas pelayanan kami.</p>
    
    @if($ticket)
        <p><strong>Nomor Tiket:</strong> {{ $ticket->ticket_number }}</p>
        <p><strong>Jenis Layanan:</strong> {{ $ticket->service->name ?? 'N/A' }}</p>
    @endif
    
    <p>Data Anda telah kami terima dan akan kami proses secara rahasia.</p>
    
    <p>Salam,<br>
    {{ config('app.name') }}</p>
</body>
</html>
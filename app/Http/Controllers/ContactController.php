<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            Mail::to(config('mail.from.address'))->send(new ContactFormMail($data));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()
                ->with('error', 'Maaf, pesan belum dapat dikirim. Silakan hubungi kami melalui WhatsApp.');
        }

        return back()->with('success', 'Pesan Anda telah berhasil dikirim!');
    }
}

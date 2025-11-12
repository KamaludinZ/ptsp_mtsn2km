<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        Mail::to(config('mail.from.address'))->send(new ContactFormMail($request->all()));

        return redirect()->back()->with('success', 'Pesan Anda telah berhasil dikirim!');
    }
}

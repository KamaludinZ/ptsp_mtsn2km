<?php

namespace App\Filament\Forms;

use App\Support\Persuratan;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;

/**
 * Combobox fields: the routine choices from Master Persuratan as a dropdown,
 * while still accepting a value typed by hand.
 */
class PersuratanFields
{
    public static function tujuan(): TextInput
    {
        return TextInput::make('tujuan_surat')->label('Tujuan surat')
            ->datalist(fn () => Persuratan::options('tujuan_naskah'))
            ->placeholder('Pilih atau ketik tujuan')
            ->maxLength(255);
    }

    public static function jenis(): TextInput
    {
        return TextInput::make('jenis_surat')->label('Jenis surat')
            ->datalist(Persuratan::JENIS_SURAT)
            ->placeholder('Pilih atau ketik jenis')
            ->maxLength(100);
    }

    public static function klasifikasi(): TextInput
    {
        return TextInput::make('klasifikasi')->label('Klasifikasi')
            ->datalist(fn () => Persuratan::options('klasifikasi'))
            ->placeholder('mis. PP.00')
            ->helperText('Ikut tersusun dalam nomor surat.')
            ->maxLength(50);
    }

    /** Stored as one line per recipient in the text column. */
    public static function tembusan(): TagsInput
    {
        return TagsInput::make('tembusan')->label('Tembusan')
            ->suggestions(fn () => Persuratan::options('tembusan'))
            ->placeholder('Pilih atau ketik, lalu Enter')
            ->separator("\n")
            ->splitKeys(['Tab']);
    }

    public static function instruksi(): TextInput
    {
        return TextInput::make('instruction')->label('Instruksi disposisi')
            ->datalist(fn () => Persuratan::options('instruksi_disposisi'))
            ->placeholder('Pilih atau ketik instruksi')
            ->maxLength(255);
    }
}

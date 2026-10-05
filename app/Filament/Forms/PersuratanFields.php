<?php

namespace App\Filament\Forms;

use App\Support\Persuratan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

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
            ->datalist(fn () => Persuratan::options('jenis_surat'))
            ->placeholder('Pilih atau ketik jenis')
            ->maxLength(100);
    }

    /**
     * Searchable by code or name and shown as "PP.00 — Pendidikan"; a code
     * outside the master list can still be typed and used as is.
     */
    public static function klasifikasi(): Select
    {
        return Select::make('klasifikasi')->label('Klasifikasi')
            ->searchable()
            ->options(fn () => Persuratan::klasifikasiOptions())
            ->getSearchResultsUsing(function (string $search) {
                $search = Str::squish($search);
                $options = collect(Persuratan::klasifikasiOptions())
                    ->filter(fn (string $label) => Str::contains($label, $search, ignoreCase: true))
                    ->all();
                $typed = mb_strtoupper($search);

                // Offer the typed text as a new code only when nothing in the list matches.
                return $options || mb_strlen($typed) > 50 || ! preg_match('/^[A-Z0-9]+([.\/-][A-Z0-9]+)*$/', $typed)
                    ? $options
                    : [$typed => "Pakai kode \"{$typed}\""];
            })
            ->getOptionLabelUsing(fn (?string $value) => Persuratan::klasifikasiOptions()[$value] ?? $value)
            ->placeholder('Cari kode atau nama, mis. PP.00')
            ->searchPrompt('Ketik kode atau nama klasifikasi')
            ->noSearchResultsMessage('Ketik kode untuk memakai klasifikasi sendiri.')
            ->helperText('Ikut tersusun dalam nomor surat.')
            ->rules(['nullable', 'string', 'max:50']);
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

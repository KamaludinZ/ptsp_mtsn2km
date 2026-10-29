<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Text typed into the app stays text in a downloaded workbook: a perihal or
 * name starting with "=" is never written as a formula (spreadsheet formula
 * injection), so opening the report runs nothing. Numbers stay numbers.
 */
class TextValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && $value !== '') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}

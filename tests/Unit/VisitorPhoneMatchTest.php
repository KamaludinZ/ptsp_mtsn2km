<?php

namespace Tests\Unit;

use App\Models\Visitor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Visitor::phoneMatches: the checkout's proof that the guest is who registered. */
class VisitorPhoneMatchTest extends TestCase
{
    public static function sameNumber(): array
    {
        return [
            'as registered' => ['081234500001', '081234500001'],
            'spaces and dashes' => ['081234500001', '0812 3450-0001'],
            'dots and brackets' => ['081234500001', '(0812).3450.0001'],
            'with 62' => ['081234500001', '6281234500001'],
            'with +62' => ['081234500001', '+62 812-3450-0001'],
            'with 0062' => ['081234500001', '0062 812 3450 0001'],
            'registered with +62' => ['+62 812 3450 0001', '081234500001'],
            'registered without the 0' => ['81234500001', '081234500001'],
        ];
    }

    #[DataProvider('sameNumber')]
    public function test_the_same_number_written_another_way_matches(string $registered, string $typed): void
    {
        $this->assertTrue((new Visitor(['phone' => $registered]))->phoneMatches($typed));
    }

    public static function otherNumber(): array
    {
        return [
            'another number' => ['081234500001', '081234500002'],
            'only the shown last digits' => ['081234500001', '001'],
            'a longer number' => ['081234500001', '0812345000011'],
            'empty' => ['081234500001', ''],
            'nothing' => ['081234500001', null],
            'no phone registered' => [null, ''],
            'blank registered' => ['  ', '0'],
        ];
    }

    #[DataProvider('otherNumber')]
    public function test_another_number_never_matches(?string $registered, ?string $typed): void
    {
        $this->assertFalse((new Visitor(['phone' => $registered]))->phoneMatches($typed));
    }
}

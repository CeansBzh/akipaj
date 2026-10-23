<?php

namespace Tests\Unit;

use App\Enums\AlertLevelEnum;
use App\Enums\UserLevelEnum;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_user_levels_are_ordered(): void
    {
        // Stored as integers and compared with >= in EnsureHasLevel and User::isMemberOrAbove()
        $this->assertSame([0, 1, 10, 99], array_map(fn (UserLevelEnum $level) => $level->value, UserLevelEnum::cases()));
    }

    public function test_user_levels_have_a_label_and_a_description(): void
    {
        $this->assertSame('Visiteur', UserLevelEnum::GUEST->label());
        $this->assertSame('Membre', UserLevelEnum::MEMBER->label());
        $this->assertSame('Contributeur', UserLevelEnum::CONTRIBUTOR->label());
        $this->assertSame('Administrateur', UserLevelEnum::ADMINISTRATOR->label());

        foreach (UserLevelEnum::cases() as $level) {
            $this->assertNotEmpty($level->description());
        }
    }

    public function test_alert_levels_have_a_tailwind_color(): void
    {
        $this->assertSame(
            ['INFO' => 'blue', 'SUCCESS' => 'green', 'WARNING' => 'yellow', 'ERROR' => 'red'],
            array_combine(
                array_map(fn (AlertLevelEnum $level) => $level->name, AlertLevelEnum::cases()),
                array_map(fn (AlertLevelEnum $level) => $level->color(), AlertLevelEnum::cases()),
            ),
        );
    }
}

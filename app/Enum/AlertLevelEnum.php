<?php

namespace App\Enum;

enum AlertLevelEnum
{
    case INFO;
    case SUCCESS;
    case WARNING;
    case ERROR;

    public function color(): string
    {
        return match ($this) {
            AlertLevelEnum::INFO => 'blue',
            AlertLevelEnum::SUCCESS => 'green',
            AlertLevelEnum::WARNING => 'yellow',
            AlertLevelEnum::ERROR => 'red',
        };
    }
}

<?php

namespace App\Enums;

enum FeedbackType: string
{
    case Bug = 'bug';
    case Feedback = 'feedback';

    public function label(): string
    {
        return match ($this) {
            self::Bug => 'Laporan Bug',
            self::Feedback => 'Masukan',
        };
    }
}

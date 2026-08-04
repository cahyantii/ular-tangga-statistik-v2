<?php

namespace App\Enums;

enum ScoreEventType: string
{
    case CorrectAnswer = 'correct_answer';
    case WrongAnswer = 'wrong_answer';
    case Win = 'win';
}

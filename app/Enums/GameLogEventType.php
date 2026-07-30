<?php

namespace App\Enums;

enum GameLogEventType: string
{
    case DiceRolled = 'dice_rolled';
    case PawnMoved = 'pawn_moved';
    case MovementBlocked = 'movement_blocked';
    case ConnectorLanded = 'connector_landed';
    case ConnectorApplied = 'connector_applied';
    case QuestionPresented = 'question_presented';
    case AnswerSubmitted = 'answer_submitted';
    case ScoreUpdated = 'score_updated';
    case Paused = 'paused';
    case Resumed = 'resumed';
    case Forfeited = 'forfeited';
    case Finished = 'finished';
    case ItemGacha = 'item_gacha';
}

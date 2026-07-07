<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Services\Progress\LearningProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __construct(private readonly LearningProgressService $learningProgress)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('player.progress', [
            'overall' => $this->learningProgress->overallAggregate($user),
            'perKategori' => $this->learningProgress->perKategori($user),
        ]);
    }
}

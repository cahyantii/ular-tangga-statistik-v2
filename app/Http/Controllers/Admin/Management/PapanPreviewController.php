<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\PapanPermainan;
use Illuminate\View\View;

class PapanPreviewController extends Controller
{
    public function show(PapanPermainan $papanPermainan): View
    {
        $papanPermainan->load(['petak', 'papanKonektor']);

        return view('admin.management.papan-permainan.preview', [
            'papan' => $papanPermainan,
        ]);
    }
}

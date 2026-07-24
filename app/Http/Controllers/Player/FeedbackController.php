<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\StoreFeedbackRequest;
use App\Models\Feedback;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;

class FeedbackController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function store(StoreFeedbackRequest $request): RedirectResponse
    {
        $feedback = Feedback::create([
            'user_id' => $request->user()->id,
            'type' => $request->string('type')->toString(),
            'subject' => $request->string('subject')->toString(),
            'message' => $request->string('message')->toString(),
        ]);

        $this->notifications->sendToAdmins($this->notifications->payloadFeedbackReceived($feedback));

        return redirect()->back()->with('status', 'Terima kasih! Masukan Anda sudah kami terima.');
    }
}

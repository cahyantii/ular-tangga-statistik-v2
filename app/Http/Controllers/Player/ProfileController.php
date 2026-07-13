<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\AvatarUpdateRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Profile\AvatarUploadService;
use App\Services\Profile\ProfileSummaryService;
use App\Services\Progress\TipsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileSummaryService $profileSummary,
        private readonly AvatarUploadService $avatarUpload,
        private readonly TipsService $tips,
    ) {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('player.profile.edit', [
            'user' => $user,
            'summary' => $this->profileSummary->summary($user),
            'securityTip' => $this->tips->securityTip(),
            'presetAvatars' => User::PRESET_AVATARS,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's avatar, either from a preset key or an uploaded
     * image (resized & center-cropped to a square by AvatarUploadService).
     */
    public function updateAvatar(AvatarUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $path = $this->avatarUpload->store($request->file('avatar'), $user);
            $user->update(['avatar' => $path]);
        } else {
            $this->avatarUpload->forgetUploaded($user);
            $user->update(['avatar' => $request->string('preset')->toString()]);
        }

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }
}

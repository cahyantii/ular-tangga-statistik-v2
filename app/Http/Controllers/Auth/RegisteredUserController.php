<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuthProvider;
use App\Events\Auth\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Role, avatar, dan level/XP/poin TIDAK diisi di sini secara sengaja:
     * role & avatar sudah punya default kolom (lihat migrasi users), sedangkan
     * level/XP/poin bukan kolom tersimpan — dihitung otomatis oleh LevelService
     * dari aktivitas pemain (lihat Progress/Profil/Leaderboard), sehingga
     * pengguna baru otomatis berada di Level 1 / 0 XP / 0 Poin tanpa perlu
     * nilai default ditulis manual di sini.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        event(new Registered($user));

        UserRegistered::dispatch($user, AuthProvider::Email, now());

        $this->notifications->sendToAdmins($this->notifications->payloadUserRegistered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

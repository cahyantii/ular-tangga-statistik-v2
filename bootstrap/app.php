<?php

use App\Exceptions\ActiveGameSessionExistsException;
use App\Exceptions\GameAlreadyFinishedException;
use App\Exceptions\NotYourTurnException;
use App\Exceptions\QuestionExpiredException;
use App\Exceptions\RoomFullException;
use App\Http\Middleware\EnsureAdminIsVerified;
use App\Http\Middleware\EnsureNoActiveGameSession;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'admin.verified' => EnsureAdminIsVerified::class,
            'no-active-session' => EnsureNoActiveGameSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (GameAlreadyFinishedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (NotYourTurnException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
        });

        $exceptions->render(function (QuestionExpiredException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }
        });

        $exceptions->render(function (RoomFullException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (ActiveGameSessionExistsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        });
    })->create();

<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if ($response->status() === 419) {
            if ($request->inertia()) {
                if ($e instanceof AuthenticationException) {
                    return response('', 409)->header('X-Inertia-Location', $e->redirectTo());
                } else {
                    if ($request->routeIs('admin.*')) {
                        return response('', 409)->header('X-Inertia-Location', route('admin.login'));
                    } else {
                        return route('login');
                    }
                }
            } else {
                return back()->with([
                    'message_expired' => 'The page expired, please try again.',
                ]);
            }
        }

        if ($response->status() === 302) {
            if ($e instanceof AuthenticationException) {
                if (
                    $request->inertia()
                    && $e->redirectTo() === route('login')
                ) {
                    return Inertia::location($e->redirectTo());
                }
            }
        }

        return $response;
    }
}

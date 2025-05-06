<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
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

    public function report(Throwable $exception)
    {
        $isValid = true;

        $excepts = [
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Illuminate\Queue\MaxAttemptsExceededException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Validation\ValidationException::class,
        ];

        foreach ($excepts as $except) {
            if ($exception instanceof $except) {
                $isValid = false;

                break;
            }
        }

        if ($isValid) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $inputs = [
                'url' => url()->full(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage() . '; ' . $userAgent,
                'trace' => $exception->getTrace(),
            ];

            if (
                !$this->isHttpException($exception)
                || $exception->getStatusCode() > 499
            ) {
                try {
                    $errorLog = new ErrorLog();

                    $errorLog->syncErrorLog($inputs);
                } catch (\Throwable $th) {
                    if (!app()->environment('local') || !app()->runningInConsole()) {
                        throw $th;
                    }
                }
            }
        }
    }

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if (
            $response->status() === Response::HTTP_TOO_MANY_REQUESTS
            && $request->inertia()
            && $request->routeIs('verification.send')
        ) {
            return back()->with('message_expired', __('error 429'));
        }

        if ($response->status() === 419) {
            if ($request->inertia()) {
                if ($e instanceof AuthenticationException) {
                    return response('', Response::HTTP_CONFLICT)->header('X-Inertia-Location', $e->redirectTo());
                } else {
                    if ($e instanceof TokenMismatchException) {
                        return $response;
                    }

                    if ($request->routeIs('admin.*')) {
                        return response('', Response::HTTP_CONFLICT)->header('X-Inertia-Location', route('admin.login'));
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

        if ($response->status() === Response::HTTP_FOUND) {
            if ($e instanceof AuthenticationException) {
                if (
                    $request->inertia()
                    && $e->redirectTo() === route('login')
                ) {
                    return Inertia::location($e->redirectTo());
                }
            }
        }

        // Provided custom error page
        if (in_array($response->status(), config('constants.theme_error_page'))) {
            if (
                $response->status() == Response::HTTP_INTERNAL_SERVER_ERROR
                && config('app.debug')
            ) {
                return $response;
            }

            return $this->responseErrorPage($response, $e);
        }

        return $response;
    }

    private function responseErrorPage($response, Throwable $e = null)
    {
        $message = 'error ' . $response->status();

        if (
            $response->status() == 403
            && $e->getMessage() != ''
        ) {
            $message = $e->getMessage();
        }

        $data = [
            'statusCode' => $response->status(),
            'message' => __($message),
        ];

        if (view()->exists('errors.' . $response->status())) {
            return response()
                ->view(
                    'errors.' . $response->status(),
                    $data,
                    $response->status()
                );
        }

        return response()
                ->view(
                    'errors.error',
                    $data,
                    $response->status()
                );
    }
}

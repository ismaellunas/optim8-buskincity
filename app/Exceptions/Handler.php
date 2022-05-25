<?php

namespace App\Exceptions;
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

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

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

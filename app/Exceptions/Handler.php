<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log the exception with context
            Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
            ]);

            // Increment error counter
            $errorKey = 'errors_today_' . now()->format('Y-m-d');
            $count = Cache::get($errorKey, 0);
            Cache::put($errorKey, $count + 1, now()->endOfDay());
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Handle specific exception types
        if ($this->shouldReturnJson($request, $e)) {
            return $this->prepareJsonResponse($request, $e);
        }

        // Custom error pages for HTTP exceptions
        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();

            // Map status codes to custom views
            $errorViews = [
                404 => 'errors.404',
                403 => 'errors.403',
                500 => 'errors.500',
                503 => 'errors.503',
                419 => 'errors.419', // CSRF token mismatch
                429 => 'errors.429', // Too many requests
            ];

            if (array_key_exists($statusCode, $errorViews) && view()->exists($errorViews[$statusCode])) {
                return response()->view($errorViews[$statusCode], [
                    'exception' => $e,
                    'statusCode' => $statusCode,
                ], $statusCode);
            }
        }

        // Handle model not found exception
        if ($e instanceof ModelNotFoundException) {
            return response()->view('errors.404', [
                'exception' => $e,
                'statusCode' => 404,
            ], 404);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'You must be logged in to access this resource.',
            ], 401);
        }

        return redirect()->guest(route('login'))
            ->with('error', 'Please log in to access this page.');
    }

    /**
     * Determine if the request should receive a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return bool
     */
    protected function shouldReturnJson($request, Throwable $e)
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        $response = [
            'message' => $e->getMessage() ?: 'An error occurred',
            'status' => $statusCode,
        ];

        // Add debug information in development mode
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    return array_only($trace, ['file', 'line']);
                })->all(),
            ];
        }

        return response()->json($response, $statusCode);
    }
}

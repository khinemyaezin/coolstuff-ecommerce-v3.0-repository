<?php

namespace App\Exceptions;

use App\Models\ViewResult;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    protected function unauthenticated($request, $exception)
    {
        error_log('unauthenticated -> '.get_class($exception));
        return $this->shouldReturnJson($request, $exception)
            ? response()->json([
                'message' => $exception->getMessage(),
                'status' => 401,
                'success' => false
            ], 401)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    public function render($request, Throwable $exception)
    {
        $viewResponse = new ViewResult();
        $viewResponse->error($exception);
        error_log('render -> '.get_class($exception));
        return response()->json($viewResponse->nullCheckResp(),$viewResponse->getHttpStatus());
    }
}

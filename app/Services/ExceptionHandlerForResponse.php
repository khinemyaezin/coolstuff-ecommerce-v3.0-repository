<?php

namespace App\Services;

use App\Models\ViewResult;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ExceptionHandlerForResponse
{
    protected function handleException(Throwable $exception, ViewResult $viewResponse)
    {
        if ($exception instanceof QueryException) {
            switch ($exception->getCode()) {
                case 23503:
                    $viewResponse->message =  "The action can't be completed because another process is using.";
                    $viewResponse->status = $exception->getCode();
                    break;

                case 23505:
                    $viewResponse->message =  "Data already exists.";
                    $viewResponse->status = $exception->getCode();
                    break;

                /**
                 * Internal Error. Logging
                 */
                default:
                    $viewResponse->status = Response::HTTP_INTERNAL_SERVER_ERROR;

                    /** have to replace logging */
                    $viewResponse->message =  $exception->getMessage();
                    $viewResponse->httpStatus = 500;
                    break;
            }
        } else if ($exception instanceof AuthenticationException) {
            /**
             * BAD REQEUSTS
             */
            $viewResponse->status = 401;
            $viewResponse->message = $exception->getMessage();
            $viewResponse->httpStatus = 401;
        } else if ($exception instanceof ValidationException) {
            /**
             * BAD REQEUSTS
             */
            $viewResponse->status = 422;
            $viewResponse->message = $exception->getMessage();
            $viewResponse->errors = $exception->errors();
            $viewResponse->httpStatus = 422;
        } else if ($exception instanceof ModelNotFoundException) {

            $viewResponse->status = 4001;
            $viewResponse->message = "Request ID " . join(' ', $exception->getIds() ?? []) . " doesn't exist";
        } else if ($exception instanceof RelationNotFoundException) {

            $viewResponse->status = 4002;
            $viewResponse->message = "Relation not found.";
        } else {
            $viewResponse->status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $viewResponse->message = $exception->getMessage();
        }

        return $viewResponse;
    }
}
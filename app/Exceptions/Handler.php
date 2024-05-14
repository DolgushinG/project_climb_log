<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Telegram\Bot\Laravel\Facades\Telegram;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if(env('APP_ENV') == "prod"){
                $message = 'Message - '.$e->getMessage().PHP_EOL.'File - '.$e->getFile().PHP_EOL.'Line - '.$e->getLine();
                $this->sendTelegramMessage($message);
            }
        });
    }

    private function sendTelegramMessage($message)
    {
        Telegram::bot('mybot')->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message,
        ]);
    }

    protected function prepareException(Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            $e = new HttpException(419, 'Ваш сессия на вход устарела. Пожалуйста обновите страницу для продолжения работы системы.', $e);
        }
        return parent::prepareException($e);
    }
}

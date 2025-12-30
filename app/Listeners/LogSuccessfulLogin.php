<?php

namespace App\Listeners;

use App\Models\AccessLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    public function __construct(protected Request $request)
    {
    }

    public function handle(Login $event): void
    {
        AccessLog::create([
            'user_id'    => $event->user->id,
            'email'      => $event->user->email,
            'action'     => AccessLog::ACTION_LOGIN,
            'status'     => AccessLog::STATUS_SUCCESS,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}

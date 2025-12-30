<?php

namespace App\Listeners;

use App\Models\AccessLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;

class LogFailedLogin
{
    public function __construct(protected Request $request)
    {
    }

    public function handle(Failed $event): void
    {
        AccessLog::create([
            'user_id'    => optional($event->user)->id,
            'email'      => $event->credentials['email'] ?? null,
            'action'     => AccessLog::ACTION_LOGIN,
            'status'     => AccessLog::STATUS_FAILED,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}

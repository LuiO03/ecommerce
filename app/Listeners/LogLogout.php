<?php

namespace App\Listeners;

use App\Models\AccessLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogLogout
{
    public function __construct(protected Request $request)
    {
    }

    public function handle(Logout $event): void
    {
        AccessLog::create([
            'user_id'    => $event->user->id,
            'email'      => $event->user->email,
            'action'     => AccessLog::ACTION_LOGOUT,
            'status'     => AccessLog::STATUS_SUCCESS,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}

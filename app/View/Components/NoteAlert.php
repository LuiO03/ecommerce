<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NoteAlert extends Component
{
    public string $type;
    public string $icon;
    public ?string $message;
    public bool $dismissible;
    public ?int $autoDismiss;
    public ?string $persistKey;

    public function __construct(
        string $type = 'info',
        ?string $message = null,
        bool $dismissible = false,
        ?int $autoDismiss = null,
        ?string $persistKey = null,
        ?string $icon = null,
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->dismissible = $dismissible;
        $this->autoDismiss = $autoDismiss;
        $this->persistKey = $persistKey;
        $this->icon = $icon ?? $this->getDefaultIcon($type);
    }

    private function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'info' => 'ri-lightbulb-fill',
            'warning' => 'ri-alert-fill',
            'danger' => 'ri-error-warning-fill',
            'success' => 'ri-checkbox-circle-fill',
            default => 'ri-information-fill',
        };
    }

    public function render()
    {
        return view('partials.components.note-alert');
    }
}

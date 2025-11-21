<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public string $type;
    public string $title;
    public ?array $items;
    public bool $dismissible;
    public string $icon;

    /**
     * Create a new component instance.
     *
     * @param string $type Tipo de alerta: 'info', 'warning', 'danger', 'success'
     * @param string $title Título del banner
     * @param array|null $items Lista de items a mostrar (opcional)
     * @param bool $dismissible Si el banner puede cerrarse
     * @param string|null $icon Ícono personalizado (opcional)
     */
    public function __construct(
        string $type = 'info',
        string $title = '',
        ?array $items = null,
        bool $dismissible = false,
        ?string $icon = null
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->items = $items;
        $this->dismissible = $dismissible;
        $this->icon = $icon ?? $this->getDefaultIcon($type);
    }

    /**
     * Obtiene el ícono por defecto según el tipo de alerta
     */
    private function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'info' => 'ri-lightbulb-line',
            'warning' => 'ri-information-line',
            'danger' => 'ri-error-warning-line',
            'success' => 'ri-checkbox-circle-line',
            default => 'ri-information-line',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('partials.components.alert');
    }
}

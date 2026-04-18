<div
    class="note-alert note-alert-{{ $type }} {{ $dismissible ? 'note-alert-dismissible' : '' }}"
    data-alert
    @if(!is_null($autoDismiss) && $autoDismiss > 0) data-auto-dismiss="{{ $autoDismiss }}" @endif
    @if(!empty($persistKey)) data-persist-key="{{ $persistKey }}" @endif
>
    <i class="{{ $icon }}"></i>
    <span>
        {{ trim($slot) !== '' ? $slot : $message }}
    </span>

    @if($dismissible)
        <button type="button" class="note-alert-close" data-alert-close aria-label="Cerrar">
            <i class="ri-close-line"></i>
        </button>
    @endif
</div>

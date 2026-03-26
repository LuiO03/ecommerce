<x-mail::message>
    # ¡Bienvenido/a, {{ $user->name }}!

    Gracias por registrarte en **{{ config('app.name') }}**. Solo falta un paso para completar la creación de tu cuenta.

    ## Confirma tu correo electrónico

    Para mantener tu cuenta segura y poder recuperar el acceso en el futuro, necesitamos que confirmes que este correo
    te pertenece.

    @isset($verificationUrl)
        <x-mail::button :url="$verificationUrl" color="accent">
            Verificar mi cuenta
        </x-mail::button>

        Si el botón no funciona, copia y pega este enlace en tu navegador:
        {{ $verificationUrl }}
    @endisset

    ---
    **Datos de tu cuenta**

    - Correo: {{ $user->email }}
    @isset($user->address)
        - Dirección: {{ $user->address }}
    @endisset

    Si tú no creaste esta cuenta, puedes ignorar este mensaje y no se completará la activación.

    Gracias, {{ config('app.name') }}
</x-mail::message>

<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.

     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Autenticación personalizada: bloquear usuarios con status inactivo
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->input(Fortify::username()))->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return null; // deja que Fortify maneje el mensaje de credenciales inválidas
            }

            if (! $user->status) {
                $message = $user->hasRole('Cliente')
                    ? 'Tu cuenta de cliente está inactiva. Revisa tu correo o contacta con soporte.'
                    : 'Tu cuenta está inactiva. Contacta con un administrador del sistema para reactivarla.';

                throw ValidationException::withMessages([
                    Fortify::username() => $message,
                ]);
            }

            return $user;
        });

        // Vista para solicitar enlace de recuperación de contraseña
        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        // Vista para restablecer la contraseña (formulario con nuevo password)
        Fortify::resetPasswordView(function (Request $request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        // Usar admin-login como la única vista de login
        Fortify::loginView(function () {
            return view('auth.admin-login');
        });

        // Redirección después de login según rol
        Fortify::redirects('login', function ($request) {
            $user = $request->user();

            if (! $user) {
                return route('welcome.index');
            }

            // Solo el rol "Cliente" se queda en la parte pública
            if ($user->hasRole('Cliente')) {
                return route('welcome.index');
            }

            // Cualquier otro rol (actual o futuro) va al panel admin
            return '/admin';
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}

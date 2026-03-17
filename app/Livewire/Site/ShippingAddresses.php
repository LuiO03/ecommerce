<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Addresses;
use Illuminate\Support\Facades\Auth;

class ShippingAddresses extends Component
{

    public $addresses = [];
    public $newAddress = false;

    public $type = 'home';
    public $address_line = '';
    public $district = '';
    public $reference = '';
    public $receiver_type = 'owner';
    public $receiver_name = '';
    public $receiver_last_name = '';
    public $receiver_phone = '';
    public $is_default = false;

    public function mount()
    {
        $this->loadAddresses();

        $user = Auth::user();

        if ($user) {
            $this->receiver_name = $user->name ?? $this->receiver_name;
            $this->receiver_last_name = $user->last_name ?? $this->receiver_last_name;
            $this->receiver_phone = $user->phone ?? $this->receiver_phone;
        }
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:home,office',
            'address_line' => 'required|string|min:5|max:255',
            'district' => 'required|string|max:120',
            'reference' => 'required|string|max:255',
            'receiver_type' => 'required|in:owner,other',
            'receiver_name' => 'required|string|min:3|max:255',
            'receiver_last_name' => 'required|string|min:2|max:255',
            'receiver_phone' => 'required|string|min:6|max:20',
            'is_default' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function saveAddress(): void
    {
        $this->validate();

        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        if ($this->is_default) {
            Addresses::where('user_id', $userId)->update(['is_default' => false]);
        }

        Addresses::create([
            'user_id' => $userId,
            'type' => $this->type,
            'address_line' => $this->address_line,
            'district' => $this->district,
            'reference' => $this->reference,
            'receiver_type' => $this->receiver_type === 'owner' ? 1 : 2,
            'receiver_name' => $this->receiver_name,
            'receiver_last_name' => $this->receiver_last_name,
            'receiver_phone' => $this->receiver_phone,
            'is_default' => (bool) $this->is_default,
        ]);

        $this->resetForm();
        $this->loadAddresses();
        $this->newAddress = false;
    }

    public function cancelNewAddress(): void
    {
        $this->resetForm();
        $this->newAddress = false;
    }

    public function setDefault(int $id): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $address = Addresses::where('user_id', $userId)->where('id', $id)->firstOrFail();

        Addresses::where('user_id', $userId)->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        $this->loadAddresses();
    }

    public function deleteAddress(int $id): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $address = Addresses::where('user_id', $userId)->where('id', $id)->first();

        if (! $address) {
            return;
        }

        $address->delete();

        $this->loadAddresses();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'type',
            'address_line',
            'district',
            'reference',
            'receiver_type',
            'receiver_name',
            'receiver_last_name',
            'receiver_phone',
            'is_default',
        ]);

        $this->type = 'home';
        $this->receiver_type = 'owner';
        $this->is_default = false;

        $user = Auth::user();

        if ($user) {
            $this->receiver_name = $user->name ?? '';
            $this->receiver_last_name = $user->last_name ?? '';
            $this->receiver_phone = $user->phone ?? '';
        }
    }

    protected function loadAddresses(): void
    {
        $userId = Auth::id();
        if (! $userId) {
            $this->addresses = collect();
            return;
        }

        $addresses = Addresses::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('id', 'desc')
            ->get();

        // Si no hay direcciones guardadas pero el usuario tiene una dirección en su perfil,
        // mostrarla como tarjeta inicial (solo lectura) para que al menos vea una dirección de envío.
        if ($addresses->isEmpty()) {
            $user = Auth::user();

            if ($user && $user->address) {
                $virtual = new Addresses([
                    'id' => null,
                    'user_id' => $userId,
                    'type' => 'home',
                    'address_line' => $user->address,
                    'district' => '',
                    'reference' => '',
                    'receiver_type' => 1,
                    'receiver_name' => $user->name,
                    'receiver_last_name' => $user->last_name,
                    'receiver_phone' => $user->phone,
                    'is_default' => true,
                ]);

                $this->addresses = collect([$virtual]);
                return;
            }
        }

        $this->addresses = $addresses;
    }

    public function updatedReceiverType($value): void
    {
        if ($value === 'owner') {
            $user = Auth::user();

            if ($user) {
                // Siempre sobreescribir con los datos actuales del titular
                $this->receiver_name = (string) ($user->name ?? '');
                $this->receiver_last_name = (string) ($user->last_name ?? '');
                $this->receiver_phone = (string) ($user->phone ?? '');
            }
        }

        if ($value === 'other') {
            $this->receiver_name = '';
            $this->receiver_last_name = '';
            $this->receiver_phone = '';
        }
    }

    public function render()
    {
        return view('livewire.site.shipping-addresses');
    }
}

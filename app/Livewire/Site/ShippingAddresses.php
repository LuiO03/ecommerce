<?php

namespace App\Livewire\Site;

use Livewire\Component;
use App\Models\Addresses;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ShippingAddresses extends Component
{

    public $addresses = [];
    public $newAddress = false;

    public ?int $editingAddressId = null;

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
            'receiver_phone' => 'nullable|string|min:6|max:20',
            'is_default' => 'boolean',
        ];
    }

    public function chooseReceiverType(string $type): void
    {
        $this->receiver_type = $type === 'owner' ? 'owner' : 'other';
        $this->syncReceiverFields();
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

        // Capitalizar nombre, apellido y solo la primera palabra de referencia, distrito y dirección
        $receiver_name = ucwords(mb_strtolower($this->receiver_name));
        $receiver_last_name = ucwords(mb_strtolower($this->receiver_last_name));
        $reference = $this->reference ? ucfirst(mb_strtolower($this->reference)) : null;
        $district = $this->district ? ucfirst(mb_strtolower($this->district)) : null;
        $address_line = $this->address_line ? ucfirst(mb_strtolower($this->address_line)) : null;

        // Crear o actualizar según si hay dirección en edición
        if ($this->editingAddressId) {
            $address = Addresses::where('user_id', $userId)
                ->where('id', $this->editingAddressId)
                ->first();

            if (! $address) {
                return;
            }

            $address->update([
                'type' => $this->type,
                'address_line' => $address_line,
                'district' => $district,
                'reference' => $reference,
                'receiver_type' => $this->receiver_type === 'owner' ? 1 : 2,
                'receiver_name' => $receiver_name,
                'receiver_last_name' => $receiver_last_name,
                'receiver_phone' => $this->receiver_phone,
                'is_default' => (bool) $this->is_default,
            ]);
        } else {
            Addresses::create([
                'user_id' => $userId,
                'type' => $this->type,
                'address_line' => $address_line,
                'district' => $district,
                'reference' => $reference,
                'receiver_type' => $this->receiver_type === 'owner' ? 1 : 2,
                'receiver_name' => $receiver_name,
                'receiver_last_name' => $receiver_last_name,
                'receiver_phone' => $this->receiver_phone,
                'is_default' => (bool) $this->is_default,
            ]);
        }

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

    #[On('delete-address-confirmed')]
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
            'editingAddressId',
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

    public function editAddress(int $id): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        $address = Addresses::where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (! $address) {
            return;
        }

        $this->editingAddressId = $address->id;
        $this->newAddress = true;

        $this->type = $address->type;
        $this->address_line = $address->address_line;
        $this->district = $address->district;
        $this->reference = $address->reference;
        $this->is_default = (bool) $address->is_default;

        $this->receiver_type = (int) $address->receiver_type === 1 ? 'owner' : 'other';

        if ($this->receiver_type === 'owner') {
            // Sincronizar con datos actuales del titular
            $this->syncReceiverFields();
        } else {
            $this->receiver_name = $address->receiver_name ?? '';
            $this->receiver_last_name = $address->receiver_last_name ?? '';
            $this->receiver_phone = $address->receiver_phone ?? '';
        }

        $this->resetValidation();
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
        $this->addresses = $addresses;
    }

    protected function syncReceiverFields(): void
    {
        if ($this->receiver_type === 'owner') {
            $user = Auth::user();

            if ($user) {
                // Siempre sobreescribir con los datos actuales del titular
                $this->receiver_name = (string) ($user->name ?? '');
                $this->receiver_last_name = (string) ($user->last_name ?? '');
                $this->receiver_phone = (string) ($user->phone ?? '');
            }
        } else {
            // "Otra persona": limpiar campos para que el usuario pueda escribir
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

<?php

namespace App\Livewire\Site;

use App\Models\Category;
use Livewire\Component;

class CategoryList extends Component
{
    public string $sectionTitle = 'Categorias populares';
    public string $sectionSubtitle = 'Explora nuestras categorias mas populares y encuentra lo que buscas';
    public ?int $familyId = null;
    public ?int $limit = null;
    public string $customClass = '';

    public function render()
    {
        $query = Category::query()
            ->where('status', true)
            ->whereNull('parent_id')
            ->orderBy('name');

        if ($this->familyId) {
            $query->where('family_id', $this->familyId);
        }

        if ($this->limit) {
            $query->limit($this->limit);
        }

        return view('livewire.site.category-list', [
            'categories' => $query->get(),
        ]);
    }
}

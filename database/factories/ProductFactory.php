<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\Option;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->bothify('SKU-#####'),
            'name' => $this->faker->words(3, true),
            'slug' => Str::slug(Str::limit($this->faker->words(3, true), 50, '')),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'discount' => $this->faker->optional()->randomFloat(2, 1, 100),
            'status' => $this->faker->boolean(80), // 80% de probabilidad de ser true
            'category_id' => $this->faker->numberBetween(1, 10), // Asumiendo que hay al menos 10 categorías
        ];
    }

    /**
     * Configuración adicional para crear variantes y opciones asociadas.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            // Con cierta probabilidad dejar productos sin variantes (productos simples)
            if (random_int(1, 100) <= 30) {
                return;
            }

            $options = Option::with('features')->get();
            if ($options->isEmpty()) {
                return;
            }

            $candidates = $options->filter(fn (Option $opt) => $opt->features->isNotEmpty());
            if ($candidates->isEmpty()) {
                return;
            }

            $maxOptions = min(3, $candidates->count());
            $take = random_int(1, $maxOptions);
            $selectedOptions = $candidates->shuffle()->take($take);

            // Construir conjuntos de features por opción (limitando cantidad para evitar explosión de combinaciones)
            $featureSets = [];
            foreach ($selectedOptions as $option) {
                $features = $option->features->shuffle()->take(min(3, $option->features->count()));
                if ($features->isEmpty()) {
                    continue;
                }
                $featureSets[$option->id] = $features->values();
            }

            if (empty($featureSets)) {
                return;
            }

            // Producto cartesiano de features para generar combinaciones
            $combos = [[]];
            foreach ($featureSets as $optionId => $features) {
                $next = [];
                foreach ($combos as $combo) {
                    foreach ($features as $feature) {
                        $next[] = array_merge($combo, [$feature]);
                    }
                }
                $combos = $next;
            }

            // Limitar el número máximo de variantes por producto para no generar demasiados registros
            $maxVariants = 15;
            $combos = array_slice($combos, 0, $maxVariants);

            if (empty($combos)) {
                return;
            }

            $optionIds = [];

            foreach ($combos as $index => $features) {
                /** @var Feature[] $features */
                $featureIds = collect($features)->pluck('id')->unique()->values();
                $optionIds = array_merge($optionIds, collect($features)->pluck('option_id')->all());

                $basePrice = (float) $product->price;
                $delta = $this->faker->randomFloat(2, -5, 20);
                $variantPrice = max(1, $basePrice + $delta);

                $variant = new Variant([
                    'sku' => $this->buildVariantSku($product->sku, $features, $index + 1),
                    'price' => $variantPrice,
                    'stock' => random_int(0, 50),
                    'status' => $this->faker->boolean(85),
                ]);

                $variant->product_id = $product->id;
                $variant->save();

                if ($featureIds->isNotEmpty()) {
                    $variant->features()->sync($featureIds->all());
                }
            }

            // Sincronizar opciones asociadas al producto (similar a syncProductOptionsFromVariants)
            $optionIds = collect($optionIds)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($optionIds)) {
                $syncPayload = [];
                foreach ($optionIds as $optionId) {
                    $syncPayload[$optionId] = ['value' => null];
                }

                $product->options()->sync($syncPayload);
            }
        });
    }

    /**
     * Genera un SKU de variante basado en el SKU base y los valores de las opciones.
     */
    protected function buildVariantSku(string $baseSku, array $features, int $index): string
    {
        $base = trim($baseSku) !== '' ? $baseSku : 'VAR';

        if (empty($features)) {
            return $base . '-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT);
        }

        $segments = collect($features)
            ->map(function (Feature $feature) {
                $raw = $feature->description ?: $feature->value;
                $slug = Str::slug($raw ?? '', '-');
                $slug = strtoupper($slug);

                return $slug !== '' ? $slug : null;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($segments)) {
            return $base . '-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT);
        }

        return $base . '-' . implode('-', $segments);
    }
}

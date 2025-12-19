<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class OptionController extends Controller
{
    public function index()
    {
        $options = Option::with(['features' => fn ($query) => $query->orderBy('id')])
            ->orderByDesc('id')
            ->get();

        return view('admin.options.index', [
            'options' => $options,
            'colorSlug' => Option::COLOR_SLUG,
        ]);
    }

    public function create()
    {
        return view('admin.options.create');
    }

    public function store(Request $request)
    {
        $payload = $this->validateOptionRequest($request);

        $fields = $payload['fields'];
        $features = $payload['features'];

        $option = null;

        DB::transaction(function () use (&$option, $fields, $features) {
            $option = Option::create([
                'name' => $fields['name'],
                'slug' => $fields['slug'],
                'description' => $fields['description'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $option->features()->createMany(array_map(fn ($feature) => [
                'value' => $feature['value'],
                'description' => $feature['description'],
            ], $features));
        });

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Opción creada',
            'message' => "La opción <strong>{$option->name}</strong> se registró correctamente.",
        ]);

        Session::flash('highlightOption', $option->slug);

        return redirect()->route('admin.options.index');
    }

    public function edit(Option $option)
    {
        $option->load('features');

        return view('admin.options.edit', [
            'option' => $option,
        ]);
    }

    public function update(Request $request, Option $option)
    {
        $payload = $this->validateOptionRequest($request, $option);

        $fields = $payload['fields'];
        $features = $payload['features'];

        DB::transaction(function () use ($option, $fields, $features) {
            $option->update([
                'name' => $fields['name'],
                'slug' => $fields['slug'],
                'description' => $fields['description'],
                'updated_by' => Auth::id(),
            ]);

            $existingIds = $option->features()->pluck('id')->all();
            $processedIds = [];

            foreach ($features as $featureData) {
                $featureId = $featureData['id'];
                $data = [
                    'value' => $featureData['value'],
                    'description' => $featureData['description'],
                ];

                if ($featureId && in_array($featureId, $existingIds, true)) {
                    $option->features()->where('id', $featureId)->update($data);
                    $processedIds[] = $featureId;
                } else {
                    $newFeature = $option->features()->create($data);
                    $processedIds[] = $newFeature->id;
                }
            }

            $toDelete = array_diff($existingIds, $processedIds);

            if (!empty($toDelete)) {
                $option->features()->whereIn('id', $toDelete)->delete();
            }
        });

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Opción actualizada',
            'message' => "Se guardaron los cambios de <strong>{$option->name}</strong>.",
        ]);

        Session::flash('highlightOption', $option->slug);

        return redirect()->route('admin.options.index');
    }

    public function destroy(Option $option)
    {
        if ($option->products()->exists()) {
            Session::flash('info', [
                'type' => 'warning',
                'header' => 'Acción no permitida',
                'title' => 'Opción en uso',
                'message' => 'La opción no se puede eliminar porque está asociada a productos.',
            ]);

            return redirect()->route('admin.options.index');
        }

        DB::transaction(function () use ($option) {
            $option->deleted_by = Auth::id();
            $option->save();
            $option->features()->delete();
            $option->delete();
        });

        Session::flash('info', [
            'type' => 'danger',
            'header' => 'Registro eliminado',
            'title' => 'Opción eliminada',
            'message' => 'La opción se eliminó correctamente.',
        ]);

        return redirect()->route('admin.options.index');
    }

    public function storeFeature(Request $request, Option $option)
    {
        $payload = $this->validateFeaturePayload($request, $option);

        $feature = $option->features()->create($payload);

        $option->forceFill(['updated_by' => Auth::id()])->save();
        $option->refresh();

        return response()->json([
            'message' => 'Valor agregado correctamente.',
            'feature' => [
                'id' => $feature->id,
                'value' => $feature->value,
                'description' => $feature->description,
                'is_color' => $option->isColor(),
                'delete_url' => route('admin.options.features.destroy', [$option, $feature]),
            ],
            'meta' => [
                'count' => $option->features()->count(),
                'updated_human' => optional($option->updated_at)->diffForHumans() ?? 'sin fecha',
            ],
        ], 201);
    }

    public function destroyFeature(Option $option, $featureId)
    {
        $feature = $option->features()->find($featureId);

        if (!$feature) {
            return response()->json([
                'message' => 'El valor ya no se encuentra disponible.',
                'meta' => [
                    'count' => $option->features()->count(),
                    'updated_human' => optional($option->updated_at)->diffForHumans() ?? 'sin fecha',
                ],
            ]);
        }

        $feature->delete();

        $option->forceFill(['updated_by' => Auth::id()])->save();
        $option->refresh();

        return response()->json([
            'message' => 'Valor eliminado correctamente.',
            'meta' => [
                'count' => $option->features()->count(),
                'updated_human' => optional($option->updated_at)->diffForHumans() ?? 'sin fecha',
            ],
        ]);
    }

    private function validateOptionRequest(Request $request, ?Option $option = null): array
    {
        $nameRule = Rule::unique('options', 'name');
        if ($option) {
            $nameRule->ignore($option->id);
        }

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120', $nameRule],
            'description' => ['nullable', 'string', 'max:600'],
            'features' => ['required', 'array', 'min:1'],
            'features.*.value' => ['required', 'string', 'max:120', 'distinct:strict'],
            'features.*.description' => ['nullable', 'string', 'max:255'],
        ];

        if ($option) {
            $rules['features.*.id'] = [
                'nullable',
                'integer',
                Rule::exists('features', 'id')->where(fn ($query) => $query->where('option_id', $option->id)),
            ];
        }

        $attributes = [
            'name' => 'nombre',
            'description' => 'descripción',
            'features' => 'valores',
            'features.*.value' => 'valor',
            'features.*.description' => 'descripción del valor',
        ];

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        $validator->after(function ($validator) use ($request, $option) {
            $requestedSlug = Str::slug($request->input('name'));
            $isColor = ($option?->isColor() ?? false) || $requestedSlug === Option::COLOR_SLUG;

            if (!$isColor) {
                return;
            }

            $duplicateColor = Option::where('slug', Option::COLOR_SLUG)
                ->when($option, fn ($query) => $query->where('id', '!=', $option->id))
                ->exists();

            if ($duplicateColor) {
                $validator->errors()->add('name', 'Ya existe una opción de color registrada.');
            }

            foreach ($request->input('features', []) as $index => $feature) {
                $value = $feature['value'] ?? '';
                if ($value === '') {
                    continue;
                }

                if (!preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $value)) {
                    $validator->errors()->add("features.$index.value", 'Debe ser un color hexadecimal válido (#RRGGBB).');
                }
            }
        });

        $validated = $validator->validate();

        $name = ucwords(mb_strtolower($validated['name']));
        $description = $validated['description'] ?? null;
        $description = $description !== null ? trim($description) : null;
        $description = $description === '' ? null : $description;

        $requestedSlug = Str::slug($name);
        $isColor = ($option?->isColor() ?? false) || $requestedSlug === Option::COLOR_SLUG;
        $slug = $isColor ? Option::COLOR_SLUG : Option::generateUniqueSlug($name, $option?->id);

        if ($option && $option->isColor()) {
            $slug = Option::COLOR_SLUG;
            $isColor = true;
        }

        return [
            'fields' => [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
            ],
            'features' => $this->formatFeatures($validated['features'], $isColor),
            'is_color' => $isColor,
        ];
    }

    private function validateFeaturePayload(Request $request, Option $option): array
    {
        $validator = Validator::make($request->all(), [
            'value' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [], [
            'value' => 'valor',
            'description' => 'descripción',
        ]);

        $data = $validator->validate();

        try {
            $normalizedValue = $this->normalizeFeatureValue($data['value'], $option->isColor());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['value' => $exception->getMessage()]);
        }

        $description = $this->normalizeFeatureDescription($data['description'] ?? null);

        if ($option->features()->where('value', $normalizedValue)->exists()) {
            throw ValidationException::withMessages([
                'value' => 'Este valor ya existe en la opción.',
            ]);
        }

        return [
            'value' => $normalizedValue,
            'description' => $description,
        ];
    }

    private function normalizeFeatureValue(string $value, bool $isColor): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Debe ingresar un valor.');
        }

        if ($isColor) {
            $formatted = strtoupper(ltrim($trimmed, '#'));

            if (preg_match('/^([0-9A-F]{3}|[0-9A-F]{6})$/', $formatted) !== 1) {
                throw new InvalidArgumentException('Debe ser un color hexadecimal válido (#RRGGBB).');
            }

            if (strlen($formatted) === 3) {
                $formatted = $formatted[0] . $formatted[0]
                    . $formatted[1] . $formatted[1]
                    . $formatted[2] . $formatted[2];
            }

            return '#' . $formatted;
        }

        return ucwords(mb_strtolower($trimmed));
    }

    private function normalizeFeatureDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $clean = trim($description);

        return $clean === '' ? null : $clean;
    }

    private function formatFeatures(array $features, bool $isColor): array
    {
        return collect($features)
            ->map(function ($feature) use ($isColor) {
                $id = isset($feature['id']) ? (int) $feature['id'] : null;
                $value = trim((string) ($feature['value'] ?? ''));

                if ($isColor) {
                    $value = $this->normalizeColor($value);
                } else {
                    $value = ucwords(mb_strtolower($value));
                }

                $description = $feature['description'] ?? null;
                $description = $description !== null ? trim($description) : null;
                $description = $description === '' ? null : $description;

                return [
                    'id' => $id,
                    'value' => $value,
                    'description' => $description,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeColor(string $value): string
    {
        $formatted = strtoupper(ltrim($value, '#'));

        if (preg_match('/^([0-9A-F]{3}|[0-9A-F]{6})$/', $formatted) !== 1) {
            return '#000000';
        }

        if (strlen($formatted) === 3) {
            $formatted = $formatted[0] . $formatted[0] . $formatted[1] . $formatted[1] . $formatted[2] . $formatted[2];
        }

        return '#' . $formatted;
    }
}

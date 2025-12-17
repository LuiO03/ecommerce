<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OptionController extends Controller
{
    public function index()
    {
        $options = Option::with(['features' => fn ($query) => $query->orderBy('id')])
            ->orderByDesc('id')
            ->get();

        return view('admin.options.index', [
            'options' => $options,
            'typeLabels' => Option::typeLabels(),
        ]);
    }

    public function create()
    {
        return view('admin.options.create', [
            'typeLabels' => Option::typeLabels(),
        ]);
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
                'slug' => Option::generateUniqueSlug($fields['name']),
                'type' => $fields['type'],
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
            'typeLabels' => Option::typeLabels(),
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
                'slug' => Option::generateUniqueSlug($fields['name'], $option->id),
                'type' => $fields['type'],
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

    private function validateOptionRequest(Request $request, ?Option $option = null): array
    {
        $typeLabels = Option::typeLabels();
        $typeValues = array_keys($typeLabels);

        $nameRule = Rule::unique('options', 'name');
        if ($option) {
            $nameRule->ignore($option->id);
        }

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120', $nameRule],
            'type' => ['required', 'integer', Rule::in($typeValues)],
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
            'type' => 'tipo',
            'description' => 'descripción',
            'features' => 'valores',
            'features.*.value' => 'valor',
            'features.*.description' => 'descripción del valor',
        ];

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        $validator->after(function ($validator) use ($request) {
            $type = (int) $request->input('type');

            if ($type === Option::TYPE_COLOR) {
                foreach ($request->input('features', []) as $index => $feature) {
                    $value = $feature['value'] ?? '';
                    if ($value === '') {
                        continue;
                    }

                    if (!preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $value)) {
                        $validator->errors()->add("features.$index.value", 'Debe ser un color hexadecimal válido (#RRGGBB).');
                    }
                }
            }
        });

        $validated = $validator->validate();

        $name = ucwords(mb_strtolower($validated['name']));
        $description = $validated['description'] ?? null;
        $description = $description !== null ? trim($description) : null;
        $description = $description === '' ? null : $description;

        return [
            'fields' => [
                'name' => $name,
                'type' => (int) $validated['type'],
                'description' => $description,
            ],
            'features' => $this->formatFeatures($validated['features'], (int) $validated['type'], $option),
        ];
    }

    private function formatFeatures(array $features, int $type, ?Option $option = null): array
    {
        return collect($features)
            ->map(function ($feature) use ($type, $option) {
                $id = isset($feature['id']) ? (int) $feature['id'] : null;
                $value = trim((string) ($feature['value'] ?? ''));

                if ($type === Option::TYPE_COLOR) {
                    $value = $this->normalizeColor($value);
                } elseif ($type === Option::TYPE_SIZE) {
                    $value = mb_strtoupper($value);
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

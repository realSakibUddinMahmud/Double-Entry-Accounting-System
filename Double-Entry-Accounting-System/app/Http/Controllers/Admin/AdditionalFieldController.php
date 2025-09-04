<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomField;

class AdditionalFieldController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $fields = CustomField::all();
        return view('admin.additional-field.index', compact('fields'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string|max:255',
            'name'       => 'required|string|max:255',
            'label'      => 'required|string|max:255',
            'type'       => 'required|string|max:50',
            'options'    => 'nullable|string|max:1000',
        ]);

        $modelMap = [
            'product'  => 'App\Models\Product',
            'sales'    => 'App\Models\Sales',
            'purchase' => 'App\Models\Purchase',
            'customer' => 'App\Models\Customer',
            'supplier' => 'App\Models\Supplier',
        ];

        $modelTypeInput = $request->input('model_type');
        $modelType = $modelMap[$modelTypeInput] ?? $modelTypeInput;

        // Store options as comma separated string if present and type is select
        $options = null;
        if ($request->input('type') === 'select' && $request->filled('options')) {
            // Remove extra spaces and empty values, then implode
            $optionsArray = array_filter(array_map('trim', explode(',', $request->input('options'))));
            $options = implode(',', $optionsArray);
        }

        CustomField::create([
            'model_type' => $modelType,
            'name'       => $request->input('name'),
            'label'      => $request->input('label'),
            'type'       => $request->input('type'),
            'options'    => $options,
        ]);

        return redirect()->route('additional-fields.index')->with('success', 'Field created successfully.');
    }

    public function update(Request $request, CustomField $additional_field)
    {
        $request->validate([
            'model_type' => 'required|string|max:255',
            'name'       => 'required|string|max:255',
            'label'      => 'required|string|max:255',
            'type'       => 'required|string|max:50',
            'options'    => 'nullable|string|max:1000',
        ]);

        $modelMap = [
            'product'  => 'App\Models\Product',
            'sales'    => 'App\Models\Sales',
            'purchase' => 'App\Models\Purchase',
            'customer' => 'App\Models\Customer',
            'supplier' => 'App\Models\Supplier',
        ];

        $modelTypeInput = $request->input('model_type');
        $modelType = $modelMap[$modelTypeInput] ?? $modelTypeInput;

        // Store options as comma separated string if present and type is select
        $options = null;
        if ($request->input('type') === 'select' && $request->filled('options')) {
            $optionsArray = array_filter(array_map('trim', explode(',', $request->input('options'))));
            $options = implode(',', $optionsArray);
        }

        $additional_field->update([
            'model_type' => $modelType,
            'name'       => $request->input('name'),
            'label'      => $request->input('label'),
            'type'       => $request->input('type'),
            'options'    => $options,
        ]);

        return redirect()->route('additional-fields.index')->with('success', 'Field updated successfully.');
    }

    public function destroy(CustomField $additional_field)
    {
        // Check if any CustomFieldValue exists for this field
        $hasValues = $additional_field->customFieldValues()->exists();

        if ($hasValues) {
            return redirect()
                ->route('additional-fields.index')
                ->withErrors(['error' => 'Cannot delete: This field has values assigned to records.']);
        }

        $additional_field->delete();
        return redirect()->route('additional-fields.index')->with('success', 'Field deleted successfully.');
    }
}
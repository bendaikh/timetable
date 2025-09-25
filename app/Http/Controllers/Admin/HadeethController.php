<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hadeeth;
use Illuminate\Http\Request;

class HadeethController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hadeeths = Hadeeth::orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('admin.hadeeths.index', compact('hadeeths'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.hadeeths.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'arabic_text' => 'required|string',
            'english_translation' => 'required|string',
            'reference' => 'required|string|max:255',
            'is_active' => 'boolean',
            'display_date' => 'nullable|date',
            'display_order' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        Hadeeth::create($data);

        return redirect()->route('admin.hadeeths.index')
            ->with('success', 'Hadeeth created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hadeeth $hadeeth)
    {
        return view('admin.hadeeths.show', compact('hadeeth'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hadeeth $hadeeth)
    {
        return view('admin.hadeeths.edit', compact('hadeeth'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hadeeth $hadeeth)
    {
        $request->validate([
            'arabic_text' => 'required|string',
            'english_translation' => 'required|string',
            'reference' => 'required|string|max:255',
            'is_active' => 'boolean',
            'display_date' => 'nullable|date',
            'display_order' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $hadeeth->update($data);

        return redirect()->route('admin.hadeeths.index')
            ->with('success', 'Hadeeth updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hadeeth $hadeeth)
    {
        $hadeeth->delete();

        return redirect()->route('admin.hadeeths.index')
            ->with('success', 'Hadeeth deleted successfully.');
    }
}

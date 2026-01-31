<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlidingText;
use Illuminate\Http\Request;

class SlidingTextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $slidingTexts = SlidingText::orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('admin.sliding-texts.index', compact('slidingTexts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sliding-texts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:500',
            'is_active' => 'boolean',
            'animation_speed' => 'required|integer|min:5|max:60',
            'font_size' => 'required|integer|min:10|max:160',
            'font_weight' => 'required|string|in:400,500,600,700,800,900',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        SlidingText::create($data);

        return redirect()->route('admin.sliding-texts.index')
            ->with('success', 'Sliding text created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SlidingText $slidingText)
    {
        return view('admin.sliding-texts.show', compact('slidingText'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SlidingText $slidingText)
    {
        return view('admin.sliding-texts.edit', compact('slidingText'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SlidingText $slidingText)
    {
        $request->validate([
            'text' => 'required|string|max:500',
            'is_active' => 'boolean',
            'animation_speed' => 'required|integer|min:5|max:60',
            'font_size' => 'required|integer|min:10|max:160',
            'font_weight' => 'required|string|in:400,500,600,700,800,900',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $slidingText->update($data);

        return redirect()->route('admin.sliding-texts.index')
            ->with('success', 'Sliding text updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SlidingText $slidingText)
    {
        $slidingText->delete();

        return redirect()->route('admin.sliding-texts.index')
            ->with('success', 'Sliding text deleted successfully.');
    }
}

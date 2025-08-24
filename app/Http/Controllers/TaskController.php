<?php

namespace App\Http\Controllers;
use App\Models\Task;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index( Request $request)
    {
         $tasks = Task::where('user_id', $request->user()->id)
         ->latest()
         ->paginate(10);

         return Inertia::render('Tasks', [
            'tasks' => $tasks,
         ]);
    }

    public function search(Request $request)
    {
        if (
            ($request->category === null || $request->category === '' || $request->category === 'All')
            && ($request->search === null || trim($request->search) === '')
        ) {
            return $this->index($request);
        }

        $query = Task::where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        $tasks = $query->latest()->paginate(10);

        return Inertia::render('Tasks', [
            'tasks' => $tasks,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function categories(Request $request)
    {
        $categories = Task::where('user_id', $request->user()->id)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json($categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string',
            'start' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'is_completed' => false,
            'category' => $validated['category'] ?? null,
            'start' => $validated['start'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task); // optional if you add policies
        return $task;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string',
            'is_completed' => 'boolean',
            'start' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //$this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}

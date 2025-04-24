<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the events.
     */
    public function index(Request $request): JsonResponse
    {
        $events = Event::query();
        
        // If user is not admin, only show published events
        if (!$request->user()->is_admin) {
            $events->where('status', Event::STATUS_PUBLISHED);
        }
        
        return response()->json($events->latest()->paginate());
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:now',
            'location' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'required|date|before:date',
            'image' => 'nullable|string|max:2048',
            'organizer_info' => 'nullable|array',
            'status' => ['required', Rule::in(['published', 'draft', 'cancelled'])],
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    /**
     * Display the specified event.
     */
    public function show(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        if (!Auth::user()->is_admin && $event->status !== Event::STATUS_PUBLISHED) {
            abort(404);
        }

        return response()->json($event);
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date|after:now',
            'location' => 'sometimes|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'registration_deadline' => 'sometimes|date|before:date',
            'image' => 'nullable|string|max:2048',
            'organizer_info' => 'nullable|array',
            'status' => ['sometimes', Rule::in(['published', 'draft', 'cancelled'])],
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Remove the specified event.
     */
    public function destroy(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $this->authorize('delete', $event);

        $event->delete();

        return response()->json(null, 204);
    }

    /**
     * Display a list of event attendees.
     */
    public function attendees(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $this->authorize('viewAttendees', $event);

        return response()->json($event->attendees()->paginate());
    }

    /**
     * Register current user for an event.
     */
    public function register(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        if ($event->status !== Event::STATUS_PUBLISHED) {
            abort(404);
        }

        if ($event->registration_deadline < now()) {
            return response()->json(['message' => 'Registration deadline has passed'], 422);
        }

        if ($event->capacity && $event->attendees()->count() >= $event->capacity) {
            return response()->json(['message' => 'Event is at full capacity'], 422);
        }

        $user = Auth::user();
        
        if ($event->attendees()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already registered for this event'], 422);
        }

        $event->attendees()->attach($user->id);

        return response()->json(['message' => 'Successfully registered for event']);
    }

    /**
     * Cancel registration for an event.
     */
    public function cancelRegistration(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();
        
        if (!$event->attendees()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not registered for this event'], 422);
        }

        $event->attendees()->detach($user->id);

        return response()->json(['message' => 'Successfully cancelled registration']);
    }

    /**
     * Get events the current user is registered for.
     */
    public function myEvents(): JsonResponse
    {
        $events = Auth::user()->events()->latest()->paginate();
        return response()->json($events);
    }
}

<div class="doctor-schedule">
    <h3>Doctor's Weekly Schedule</h3>
{{--
    @if($schedule->isEmpty()) <!-- Check if the collection is empty -->
        <p>No available schedule for this doctor.</p>
    @else --}}
        <div class="schedule-container">
            @foreach($schedule as $entry)
                <div class="schedule-card">
                    <p><strong>Day:</strong> {{ $entry->day }}</p>
                    <p><strong>Time:</strong> {{ $entry->start_time }} - {{ $entry->end_time }}</p>
                </div>
            @endforeach
        </div>
    {{-- @endif --}}
</div>

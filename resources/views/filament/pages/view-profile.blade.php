<x-filament::page>
    <div class="space-y-8">
        <h2 class="text-2xl font-bold text-gray-800">Profile Details</h2>
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="pb-4 border-b">
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['name'] }}</dd>
                </div>
                <div class="pb-4 border-b">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['email'] }}</dd>
                </div>
                <div class="pb-4 border-b">
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['address'] }}</dd>
                </div>
                <div class="pb-4 border-b">
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['phone'] }}</dd>
                </div>

                @if (Auth::user()->role === 'patient')
                    <div class="pb-4 border-b">
                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['gender'] ?? 'N/A' }}</dd>
                    </div>
                    <div class="pb-4 border-b">
                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['dob'] ?? 'N/A' }}</dd>
                    </div>
                @elseif (Auth::user()->role === 'doctor')
                    <div class="pb-4 border-b">
                        <dt class="text-sm font-medium text-gray-500">Specialization</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['specialization_name'] ?? 'N/A' }}</dd>
                    </div>
                    <div class="pb-4 border-b">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['status'] ?? 'N/A' }}</dd>
                    </div>
                    <div class="pb-4 border-b">
                        <dt class="text-sm font-medium text-gray-500">Bio</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $userData['bio'] ?? 'N/A' }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</x-filament::page>

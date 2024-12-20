<x-filament-panels::page>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">

    <form wire:submit.prevent="update">
        <!-- User Form Fields -->
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input
                    type="text"
                    id="name"
                    wire:model="data.name"
                    required
                    autofocus
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input
                    type="email"
                    id="email"
                    wire:model="data.email"
                    required
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                />
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                <input
                    type="text"
                    id="address"
                    wire:model="data.address"
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                />
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                <input
                    type="text"
                    id="phone"
                    wire:model="data.phone"
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                />
            </div>
        </div>

        @php
            $user = Auth::user();
        @endphp

        <!-- Conditional Fields for Patient or Doctor -->
        @if ($user->role === 'patient')
            <div class="mt-6 space-y-4">
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                    <select
                        id="gender"
                        wire:model="extraData.gender"
                        required
                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div>
                    <label for="dob" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                    <input
                        type="date"
                        id="dob"
                        wire:model="extraData.dob"
                        required
                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                    />
                </div>
            </div>
        @elseif ($user->role === 'doctor')
            <div class="mt-6 space-y-4">
                <div>
                    <label for="specialization" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                    <select
                        id="specialization"
                        wire:model="extraData.specialization_id"
                        required
                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                    >
                        @foreach ($specializations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                    <textarea
                        id="bio"
                        wire:model="extraData.bio"
                        required
                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                    ></textarea>
                </div>
            </div>
        @endif

        <!-- Form Actions (Submit Button) -->
        <div class="mt-6">
            <button
                type="submit"
                class="px-4 py-2 text-white bg-yellow-500 rounded-lg shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
            >
                Update Profile
            </button>
        </div>
    </form>

    <!-- Doctor Status Edit Form -->
    @if (Auth::user()->role === 'doctor')
        <div class="mt-6 text-2xl">
            Update Doctor Status
        </div>
        <form wire:submit.prevent="updateDoctorStatus" class="mt-8">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select
                    id="status"
                    wire:model="extraData.status"
                    required
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                >
                    <option value="available">Available</option>
                    <option value="not_available">Not Available</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="mt-6">
                <button
                    type="submit"
                    class="px-4 py-2 text-white bg-green-500 rounded-lg shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    Update Status
                </button>
            </div>
        </form>
    @endif
</x-filament-panels::page>

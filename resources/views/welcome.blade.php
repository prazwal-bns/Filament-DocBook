<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Doctor Appointment Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-blue-500 to-indigo-700">
        <h1 class="text-white text-4xl font-bold mb-4">Welcome to the Doctor Appointment Booking System</h1>
        <p class="text-white text-lg mb-8">Your health, our priority. Book your appointments effortlessly.</p>

        <div class="flex space-x-4">
            <a href="{{ route('filament.admin.auth.login') }}" 
                class="px-6 py-3 bg-white text-blue-600 font-medium rounded-lg shadow-md hover:bg-blue-50">
                Login
            </a>
        </div>

        <div class="mt-10 text-center">
            <h2 class="text-white text-2xl font-semibold">Why Choose Us?</h2>
            <ul class="mt-4 space-y-2 text-white text-base">
                <li>✔ Experienced and verified doctors</li>
                <li>✔ Easy-to-use appointment booking</li>
                <li>✔ Timely reminders for your appointments</li>
                <li>✔ Accessible anytime, anywhere</li>
            </ul>
        </div>

        <footer class="absolute bottom-4 text-white text-sm">
            &copy; 2024 Doctor Appointment Booking System. All rights reserved.
        </footer>
    </div>
</body>
</html>
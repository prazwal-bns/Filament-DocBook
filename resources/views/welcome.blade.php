<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointment System</title>
    <link rel="shortcut icon" href="https://www.svgrepo.com/show/385150/doctor-drug-health-healthcare-hospital-medical.svg" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="font-sans bg-gray-50">
    <nav class="fixed top-0 left-0 right-0 z-50 shadow-lg bg-gradient-to-r from-indigo-600 to-indigo-800">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="text-3xl font-semibold text-white hover:text-yellow-400">DocBook</a>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Home Link -->
                    {{-- <a href="#" id="home" class="font-medium text-yellow-400 hover:text-yellow-400">Home</a>
                    <a href="#about" id="about-link" class="font-medium text-white hover:text-yellow-400">About Us</a>
                    <a href="#contact" id="contact-link" class="font-medium text-white hover:text-yellow-400">Contact</a>
                    <a href="#doctors" id="doctors-link" class="font-medium text-white hover:text-yellow-400">Doctors</a> --}}

                    <a href="#home" id="home-link" class="font-medium text-white hover:text-yellow-400">Home</a>
                    <a href="#about" id="about-link" class="font-medium text-white hover:text-yellow-400">About Us</a>
                    <a href="#contact" id="contact-link" class="font-medium text-white hover:text-yellow-400">Contact</a>
                    <a href="#doctors" id="doctors-link" class="font-medium text-white hover:text-yellow-400">Doctors</a>


                    @if (Auth::check())
                        <!-- If user is logged in, show Dashboard link -->
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="font-medium text-white hover:text-yellow-400 {{ request()->routeIs('filament.admin.pages.dashboard') ? 'text-yellow-400' : '' }}">Dashboard</a>
                    @else
                        <!-- If user is not logged in, show Login and Register -->
                        <a href="{{ route('filament.admin.auth.login') }}" class="font-medium text-white hover:text-yellow-400 {{ request()->routeIs('filament.admin.auth.login') ? 'text-yellow-400' : '' }}">Login</a>
                        <a href="{{ route('filament.admin.auth.register') }}" class="px-4 py-2 font-medium text-white transition duration-200 bg-indigo-500 rounded-md hover:bg-blue-600 {{ request()->routeIs('filament.admin.auth.register') ? 'text-yellow-400' : '' }}">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>




    <!-- Hero Section -->
    <header class="relative mt-6 bg-blue-50">
        <div class="px-4 py-16 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8 lg:items-center">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">
                        Your Health, Our Priority
                    </h1>
                    <p class="mt-4 text-lg text-gray-700">
                        Book appointments with trusted doctors and get medical care from the best professionals.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('filament.admin.auth.register') }}" class="px-6 py-3 text-white bg-blue-600 rounded-md shadow hover:bg-blue-700">
                            Register Here
                        </a>
                    </div>
                </div>
                <div class="relative mt-10 lg:mt-0 lg:col-span-6">
                    <img class="rounded-lg shadow-lg" src="https://plus.unsplash.com/premium_photo-1658506671316-0b293df7c72b?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8ZG9jdG9yfGVufDB8fDB8fHww" alt="Doctor">
                </div>
            </div>
        </div>
    </header>

    <!-- Why Choose Us Section -->
    <section class="py-12 bg-gray-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                    Why Choose Us?
                </h2>
                <p class="max-w-2xl mt-4 text-lg text-gray-600 lg:mx-auto">
                    We provide seamless and reliable healthcare services tailored to meet your needs.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-8 mt-10 sm:grid-cols-2 lg:grid-cols-3">
                <div class="p-6 text-center bg-white rounded-lg shadow-md">
                    <i class="text-3xl text-blue-600 fas fa-user-md"></i>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">Trusted Doctors</h3>
                    <p class="mt-2 text-gray-600">
                        Verified professionals to ensure quality care.
                    </p>
                </div>
                <div class="p-6 text-center bg-white rounded-lg shadow-md">
                    <i class="text-3xl text-blue-600 fas fa-clock"></i>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">24/7 Support</h3>
                    <p class="mt-2 text-gray-600">
                        Always here to assist with your medical needs.
                    </p>
                </div>
                <div class="p-6 text-center bg-white rounded-lg shadow-md">
                    <i class="text-3xl text-blue-600 fas fa-calendar-alt"></i>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">Easy Scheduling</h3>
                    <p class="mt-2 text-gray-600">
                        Convenient and flexible appointment booking.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="py-12">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                    About Us
                </h2>
                <p class="mt-4 text-lg text-gray-600">
                    DocBook is committed to providing the best healthcare services with a patient-first approach. We have a team of experienced doctors who are dedicated to giving you the highest standard of care. Our services are available 24/7, and we make it easy for you to book appointments with our trusted professionals.
                </p>
            </div>
        </div>
    </section>

    @php
        $doctors = App\Models\Doctor::all();
    @endphp

    <!-- Doctors Section -->
    <section id="doctors" class="py-12 bg-gray-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                    Meet Our Doctors
                </h2>
                <p class="mt-4 text-lg text-gray-600">
                    Our team of qualified and experienced doctors are here to provide you with exceptional care.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-8 mt-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($doctors as $doctor)
                    <div class="p-6 text-center bg-white rounded-lg shadow-md">
                        <img class="w-32 h-32 mx-auto rounded-full" src="https://static.vecteezy.com/system/resources/previews/015/412/022/non_2x/doctor-round-avatar-medicine-flat-avatar-with-male-doctor-medical-clinic-team-round-icon-medical-collection-illustration-vector.jpg" alt="Doctor Image">
                        <h3 class="mt-4 text-2xl font-semibold text-gray-900">Name: {{ $doctor->user->name }}</h3>
                        <h2 class="mt-4 text-xl font-semibold text-gray-900">Specialization: {{ $doctor->specialization->name }}</h2>
                        <p class="mt-2 font-semibold text-gray-600">Contact: +977 {{ $doctor->user->phone }}  </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Contact Section -->
    <section id="contact" class="py-12 bg-gradient-to-r from-indigo-50 to-indigo-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                    Contact Us
                </h2>
                <p class="mt-4 text-lg text-gray-600">
                    Have any questions? We're here to help. Reach out to us today!
                </p>
            </div>

            <div class="max-w-2xl mx-auto mt-8">
                <form action="" class="p-8 space-y-6 shadow-lg bg-blue-20 rounded-xl">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" id="name" name="name" required class="block w-full px-4 py-2 mt-2 text-gray-900 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Your Email</label>
                        <input type="email" id="email" name="email" required class="block w-full px-4 py-2 mt-2 text-gray-900 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Message Field -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Your Message</label>
                        <textarea id="message" name="message" rows="4" required class="block w-full px-4 py-2 mt-2 text-gray-900 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="w-full px-6 py-3 text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>

            <div class="max-w-xl mx-auto mt-8 text-center">
                <p class="text-lg text-gray-600">
                    Alternatively, email us at <a href="mailto:info@DocBook.com" class="text-blue-600">info@docbook.com</a> or call us at <strong>+1 (555) 123-4567</strong>.
                </p>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="py-6 text-white bg-gradient-to-r from-indigo-600 to-indigo-800">
        <div class="mx-auto text-center max-w-7xl">
            <p>&copy; 2024 DocBook. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Smooth scrolling for links
            const navLinks = document.querySelectorAll("nav a[href^='#']");
            
            navLinks.forEach(link => {
                link.addEventListener("click", (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute("href").substring(1);
                    const targetSection = document.getElementById(targetId);
                    
                    if (targetSection) {
                        window.scrollTo({
                            top: targetSection.offsetTop - 80, // Adjust for navbar height
                            behavior: "smooth"
                        });
                    }
                });
            });
    
            // Highlight active link on scroll
            const sections = document.querySelectorAll("section[id]");
            const options = { threshold: 0.7 }; // Trigger when 70% of the section is visible
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const navLink = document.querySelector(`nav a[href="#${entry.target.id}"]`);
                    if (entry.isIntersecting) {
                        navLinks.forEach(link => link.classList.remove("text-yellow-400"));
                        navLink?.classList.add("text-yellow-400");
                    }
                });
            }, options);
    
            sections.forEach(section => observer.observe(section));
        });
    </script>
    
</body>
</html>
{{-- <div class="flex space-x-4">
    <a href="{{ route('filament.admin.auth.login') }}"
        class="px-6 py-3 font-medium text-blue-600 bg-white rounded-lg shadow-md hover:bg-blue-50">
        Login
    </a>
</div> --}}



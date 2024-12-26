<x-filament::page>
    <style>
         .appointment-info {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .appointment-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .appointment-info p {
            font-size: 1rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .appointment-info .bold {
            font-weight: 600;
            color: #333;
        }

        .payment-btn {
            background-color: #4CAF50;
            color: white;
            font-size: 1.2rem;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .payment-btn:hover {
            background-color: #45a049;
        }

        .appointment-info .doctor {
            color: #3b82f6;
            font-weight: 600;
        }

        .appointment-info .time {
            font-style: italic;
            color: #9ca3af;
        }

        #card-errors {
            color: #fa755a; /* Red color for errors */
            font-size: 14px; /* Smaller font size */
            margin-top: 10px;
        }

    </style>

    <div class="grid flex-1 auto-cols-fr gap-y-10" >
            <div class="p-6 bg-white rounded-lg shadow-md">
                <div class="appointment-info">
                    <h3>Appointment Details</h3>
                    <p><span class="bold">Patient Name:</span> {{ $appointmentDetails->patient->user->name }}</p>
                    <p><span class="bold">Appointment:</span> Appointment with <span class="doctor">{{ $appointmentDetails->doctor->user->name }}</span></p>
                    <p><span class="bold">Date:</span> {{ $appointmentDetails->appointment_date }}</p>
                    <p><span class="bold">Appointment Time:</span> From <span class="time">{{ $appointmentDetails->start_time }}</span> to <span class="time">{{ $appointmentDetails->end_time }}</span></p>
                    <p><span class="bold">Amount:</span>Rs. {{ $appointmentDetails->payment->amount }}</p>
                </div>

                <form action="{{ route('stripe.post') }}" method="POST" id="payment-form">
                    @csrf

                    <!-- Hidden input for appointment ID -->
                    <input type="hidden" name="appointment_id" value="{{ Crypt::encryptString($appointmentId) }}">

                    <!-- Card Details -->
                    <div class="mb-6">
                        <label for="card-element" class="block mb-3 text-xl font-medium text-gray-700">Card Details</label>
                        <div id="card-element" class="p-4 border border-gray-300 rounded-lg bg-gray-50">
                            <!-- A Stripe Element will be inserted here -->
                        </div>
                        <div id="card-errors" role="alert" class="mt-2 text-sm"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button type="submit"
                            class="w-full max-w-md px-6 py-3 font-semibold text-white rounded-lg shadow-md bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-400 focus:ring-opacity-50">
                            Submit Payment
                        </button>
                    </div>
                </form>
            </div>
    </div>


    {{-- Include the Stripe JavaScript --}}
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        var stripe = Stripe('pk_test_51QSzAgGD49l9BuIFDH9CL99P2OIjFRf0x6a5z6SzzXsVLSStYh29N6KXbGH7HZpoaP3Tq74saUgu3ll9x0IW9zIv00g9gQYprW');
        var elements = stripe.elements();

        var style = {
            base: {
                color: "#32325d",
                lineHeight: "24px",
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: "antialiased",
                fontSize: "18px",
                "::placeholder": {
                    color: "#aab7c4"
                }
            },
            invalid: {
                color: "#fa755a",
                iconColor: "#fa755a"
            }
        };

        var card = elements.create("card", { style: style });
        card.mount("#card-element");

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    var token = result.token.id;
                    var hiddenTokenInput = document.createElement('input');
                    hiddenTokenInput.setAttribute('type', 'hidden');
                    hiddenTokenInput.setAttribute('name', 'stripeToken');
                    hiddenTokenInput.setAttribute('value', token);
                    form.appendChild(hiddenTokenInput);
                    form.submit();
                }
            });
        });
    </script>
</x-filament::page>

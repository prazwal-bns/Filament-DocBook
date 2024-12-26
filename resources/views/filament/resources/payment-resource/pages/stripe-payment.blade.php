<x-filament::page>
    <style>
        #card-errors {
            color: #fa755a; /* Red color for errors */
            font-size: 14px; /* Smaller font size */
            margin-top: 10px;
        }

    </style>

    <div class="grid flex-1 auto-cols-fr gap-y-10" >
            <div class="p-6 bg-white rounded-lg shadow-md">
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

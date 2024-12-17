import './bootstrap';
import lottie from 'lottie-web';

// Load a Lottie animation
lottie.loadAnimation({
    container: document.getElementById('lottie-container'), // DOM element to hold the animation
    renderer: 'svg', // Render type
    loop: true, // Loop animation
    autoplay: true, // Start animation automatically
    path: 'path/to/your/lottie-animation.json' // Path to your Lottie JSON file
});

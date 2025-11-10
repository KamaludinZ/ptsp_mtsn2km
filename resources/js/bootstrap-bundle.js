/**
 * Bootstrap Bundle
 * Import Bootstrap JavaScript, AOS, dan dependencies lainnya
 * NOTE: Bootstrap CSS sudah di-import via bootstrap-custom.css
 */

// Import Bootstrap Icons CSS
import 'bootstrap-icons/font/bootstrap-icons.css';

// Import Font Awesome CSS
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import AOS (Animate On Scroll) CSS
import 'aos/dist/aos.css';

// Import AOS JavaScript
import AOS from 'aos';

// Import Bootstrap JavaScript (includes Popper.js)
import * as bootstrap from 'bootstrap';

// Make Bootstrap available globally
window.bootstrap = bootstrap;

// Initialize AOS
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });
});

// Make AOS available globally
window.AOS = AOS;

// Export untuk digunakan di module lain jika diperlukan
export default bootstrap;

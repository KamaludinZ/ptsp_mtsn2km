import './bootstrap';

// Import Font Awesome (local - no CDN)
import '@fortawesome/fontawesome-free/scss/fontawesome.scss';
import '@fortawesome/fontawesome-free/scss/solid.scss';
import '@fortawesome/fontawesome-free/scss/brands.scss';

// Import AOS Animation Library (local - no CDN)
import AOS from 'aos';
import 'aos/dist/aos.css';

// Import SweetAlert2
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// Make SweetAlert2 globally available
window.Swal = Swal;

// Toast configuration for SweetAlert2
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS
    AOS.init({
        duration: 600,
        once: true,
        offset: 100
    });
    
    // Dark mode toggle
    const html = document.documentElement;
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');

    // Check for saved theme preference or respect system preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        html.classList.add('dark');
        if (themeIcon) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    } else {
        html.classList.remove('dark');
        if (themeIcon) {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }

    // Theme toggle function
    function toggleTheme() {
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.theme = 'light';
            if (themeIcon) {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-sun');
            }
        } else {
            html.classList.add('dark');
            localStorage.theme = 'dark';
            if (themeIcon) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        }
    }

    // Add event listener to theme toggle button
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // WhatsApp dropdown functionality
    const whatsappToggle = document.getElementById('whatsapp-toggle');
    const whatsappDropdown = document.getElementById('whatsapp-dropdown-menu');
    
    if (whatsappToggle && whatsappDropdown) {
        // Toggle dropdown on button click
        whatsappToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            whatsappDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!whatsappToggle.contains(e.target) && !whatsappDropdown.contains(e.target)) {
                whatsappDropdown.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        whatsappDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
});
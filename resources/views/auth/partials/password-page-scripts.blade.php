{{-- Shared by Lupa Password and Atur Ulang Password. --}}
@push('scripts')
<script>
// Update theme icon based on current theme
function updateThemeIcon() {
    const theme = document.documentElement.getAttribute('data-theme');
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Initialize theme icon on page load
document.addEventListener('DOMContentLoaded', updateThemeIcon);

// Override toggleTheme to update icon
const originalToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
    originalToggleTheme();
    updateThemeIcon();
};
</script>
@endpush

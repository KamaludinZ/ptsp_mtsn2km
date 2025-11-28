
// Accessibility Panel Toggle
function toggleAccessibilitySidebar() {
    const panel = document.getElementById('accessibilityPanel');
    const btn = document.getElementById('accessibility-btn');

    if (panel.classList.contains('open')) {
        console.log('closing panel from toggle function');
        panel.classList.remove('open');
        btn.innerHTML = '<img src="/images/Accessibility.png" alt="Accessibility" class="w-full h-full object-contain">';
        btn.classList.add('pulse');
    } else {
        panel.classList.add('open');
        btn.innerHTML = '<i class="fas fa-times" style="line-height: 1;"></i>';
        btn.classList.remove('pulse');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const accessibilityBtn = document.getElementById('accessibility-btn');

    if (accessibilityBtn) {
        accessibilityBtn.addEventListener('click', function() {
            setTimeout(toggleAccessibilitySidebar, 0);
        });
    }

    // Close panel when clicking outside
    document.addEventListener('click', function(event) {
        const accessibilityPanel = document.getElementById('accessibilityPanel');
        if (!event.target.closest('#accessibility-btn') && !accessibilityPanel.contains(event.target) && accessibilityPanel.classList.contains('open')) {
            toggleAccessibilitySidebar();
        }
    });

    // Load saved accessibility preferences
    loadAccessibilityPreferences();
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide back to top button
window.addEventListener('scroll', function() {
    const backToTopBtn = document.getElementById('back-to-top-btn');
    if (window.pageYOffset > 300) {
        backToTopBtn.style.opacity = '1';
        backToTopBtn.style.visibility = 'visible';
    } else {
        backToTopBtn.style.opacity = '0';
        backToTopBtn.style.visibility = 'hidden';
    }
});

// Font Size Functions
function setFontSize(size) {
    const body = document.body;
    body.className = body.className.replace(/font-\w+/g, '');
    body.classList.add('font-' + size);

    // Update active button
    document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('font' + size.charAt(0).toUpperCase() + size.slice(1).replace('-', '') + 'Btn').classList.add('active');

    localStorage.setItem('fontSize', size);
}

// Toggle Functions
function toggleHighContrast() {
    document.body.classList.toggle('high-contrast');
    toggleSwitch('highContrastControl');
    localStorage.setItem('highContrast', document.body.classList.contains('high-contrast'));
}

function toggleUnderline() {
    document.body.classList.toggle('underline-links');
    toggleSwitch('underlineControl');
    localStorage.setItem('underlineLinks', document.body.classList.contains('underline-links'));
}

function toggleLineHeight() {
    document.body.classList.toggle('increased-line-height');
    toggleSwitch('lineHeightControl');
    localStorage.setItem('lineHeight', document.body.classList.contains('increased-line-height'));
}

function toggleLetterSpacing() {
    document.body.classList.toggle('increased-letter-spacing');
    toggleSwitch('letterSpacingControl');
    localStorage.setItem('letterSpacing', document.body.classList.contains('increased-letter-spacing'));
}

function toggleLargeCursor() {
    document.body.classList.toggle('large-cursor');
    toggleSwitch('cursorControl');
    localStorage.setItem('largeCursor', document.body.classList.contains('large-cursor'));
}

function toggleAnimations() {
    document.body.classList.toggle('no-animations');
    toggleSwitch('animationControl');
    localStorage.setItem('noAnimations', document.body.classList.contains('no-animations'));
}

function toggleKeyboardNavigation() {
    document.body.classList.toggle('keyboard-navigation');
    toggleSwitch('keyboardControl');
    localStorage.setItem('keyboardNavigation', document.body.classList.contains('keyboard-navigation'));
}

function toggleSwitch(id) {
    const switchEl = document.getElementById(id);
    switchEl.classList.toggle('active');
    const isActive = switchEl.classList.contains('active');
    switchEl.setAttribute('aria-checked', isActive);
}

function resetAllAccessibility() {
    // Remove all accessibility classes
    document.body.className = 'font-normal';

    // Reset all switches
    document.querySelectorAll('.accessibility-switch').forEach(sw => {
        sw.classList.remove('active');
        sw.setAttribute('aria-checked', 'false');
    });

    // Reset font size buttons
    document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('fontNormalBtn').classList.add('active');

    // Clear localStorage
    localStorage.removeItem('fontSize');
    localStorage.removeItem('highContrast');
    localStorage.removeItem('underlineLinks');
    localStorage.removeItem('lineHeight');
    localStorage.removeItem('letterSpacing');
    localStorage.removeItem('largeCursor');
    localStorage.removeItem('noAnimations');
    localStorage.removeItem('keyboardNavigation');
}

function loadAccessibilityPreferences() {
    // Load font size
    const fontSize = localStorage.getItem('fontSize');
    if (fontSize) {
        setFontSize(fontSize);
    }

    // Load toggles
    if (localStorage.getItem('highContrast') === 'true') {
        document.body.classList.add('high-contrast');
        document.getElementById('highContrastControl').classList.add('active');
    }
    if (localStorage.getItem('underlineLinks') === 'true') {
        document.body.classList.add('underline-links');
        document.getElementById('underlineControl').classList.add('active');
    }
    if (localStorage.getItem('lineHeight') === 'true') {
        document.body.classList.add('increased-line-height');
        document.getElementById('lineHeightControl').classList.add('active');
    }
    if (localStorage.getItem('letterSpacing') === 'true') {
        document.body.classList.add('increased-letter-spacing');
        document.getElementById('letterSpacingControl').classList.add('active');
    }
    if (localStorage.getItem('largeCursor') === 'true') {
        document.body.classList.add('large-cursor');
        document.getElementById('cursorControl').classList.add('active');
    }
    if (localStorage.getItem('noAnimations') === 'true') {
        document.body.classList.add('no-animations');
        document.getElementById('animationControl').classList.add('active');
    }
    if (localStorage.getItem('keyboardNavigation') === 'true') {
        document.body.classList.add('keyboard-navigation');
        document.getElementById('keyboardControl').classList.add('active');
    }
}

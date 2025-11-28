
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS with enhanced settings
    AOS.init({
        duration: 1200,
        once: true,
        offset: 120,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
        delay: 0,
        anchorPlacement: 'center-bottom'
    });

    // Custom AOS animations
    AOS.init({
        duration: 1000,
        easing: 'ease-out-quart',
        once: true,
        offset: 100,
        delay: 100
    });

    // Add smooth reveal for hero content
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        heroContent.classList.add('page-loading');
        setTimeout(() => {
            heroContent.classList.remove('page-loading');
            heroContent.classList.add('page-loaded');
        }, 100);
    }

    // Initialize theme and accessibility
    initializeTheme();
    initializeAccessibility();
    initializeInteractions();
    initializeAnimations();
    initializeKeyboardNavigation();
    initializeEnhancedAnimations();
    initializeFooterStats();
});

// Theme Management
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const themeIcon = document.getElementById('theme-icon');

    setTheme(savedTheme);

    // Listen for system theme changes
    if (window.matchMedia) {
        const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
        darkModeQuery.addListener((e) => {
            if (!localStorage.getItem('theme')) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
    localStorage.setItem('theme', newTheme);

    // Announce theme change for screen readers
    announceToScreenReader(`Tema diubah ke ${newTheme === 'dark' ? 'gelap' : 'terang'}`);
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Enhanced Accessibility Management - WCAG 2.1 AA Compliant
function initializeAccessibility() {
    // Load all saved accessibility settings
    const savedFontSize = localStorage.getItem('fontSize') || 'normal';
    const highContrast = localStorage.getItem('highContrast') === 'true';
    const underlineLinks = localStorage.getItem('underlineLinks') === 'true';
    const lineHeight = localStorage.getItem('lineHeight') === 'true';
    const letterSpacing = localStorage.getItem('letterSpacing') === 'true';
    const largeCursor = localStorage.getItem('largeCursor') === 'true';
    const animationsDisabled = localStorage.getItem('animationsDisabled') === 'true';
    const grayscaleMode = localStorage.getItem('grayscaleMode') === 'true';
    const blueLightFilter = localStorage.getItem('blueLightFilter') === 'true';
    const invertedColors = localStorage.getItem('invertedColors') === 'true';
    const enhancedKeyboardNav = localStorage.getItem('enhancedKeyboardNav') === 'true';
    const screenReaderMode = localStorage.getItem('screenReaderMode') === 'true';

    // Apply text settings
    document.body.classList.remove('font-small', 'font-normal', 'font-large', 'font-extra-large');
    document.body.classList.add(`font-${savedFontSize}`);

    // Apply visual settings
    if (highContrast) {
        document.body.classList.add('high-contrast');
        updateToggleSwitch('highContrastControl', true);
    }

    if (underlineLinks) {
        document.body.classList.add('underline-links');
        updateToggleSwitch('underlineControl', true);
    }

    if (lineHeight) {
        document.body.classList.add('enhanced-line-height');
        updateToggleSwitch('lineHeightControl', true);
    }

    if (letterSpacing) {
        document.body.classList.add('enhanced-letter-spacing');
        updateToggleSwitch('letterSpacingControl', true);
    }

    if (largeCursor) {
        document.body.classList.add('large-cursor');
        updateToggleSwitch('cursorControl', true);
    }

    if (animationsDisabled) {
        // Disable animations
        const style = document.createElement('style');
        style.id = 'no-animations';
        style.textContent = `
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        `;
        document.head.appendChild(style);
        updateToggleSwitch('animationControl', true);
    }

    // Apply color filters
    if (grayscaleMode) {
        document.body.classList.add('grayscale-mode');
    }

    if (blueLightFilter) {
        document.body.classList.add('blue-light-filter');
    }

    if (invertedColors) {
        document.body.classList.add('inverted-colors');
    }

    if (enhancedKeyboardNav) {
        document.body.classList.add('enhanced-keyboard-nav');
        // Add keyboard navigation styles
        const style = document.createElement('style');
        style.id = 'keyboard-nav-style';
        style.textContent = `
            .enhanced-keyboard-nav *:focus {
                outline: 3px solid #0066cc !important;
                outline-offset: 2px !important;
                background: rgba(0, 102, 204, 0.1) !important;
            }
        `;
        document.head.appendChild(style);
    }

    if (screenReaderMode) {
        document.body.classList.add('screen-reader-mode');
        addSemanticAnnouncements();
    }

    // Update UI states
    updateAccessibilityButtonStates();
}

function updateToggleSwitch(controlId, isActive) {
    const control = document.getElementById(controlId);
    if (control) {
        control.classList.toggle('active', isActive);
        control.setAttribute('aria-checked', isActive);
    }
}

function updateAccessibilityButtonStates() {
    const savedFontSize = localStorage.getItem('fontSize') || 'normal';
    const highContrast = localStorage.getItem('highContrast') === 'true';
    const underlineLinks = localStorage.getItem('underlineLinks') === 'true';
    const lineHeight = localStorage.getItem('lineHeight') === 'true';
    const letterSpacing = localStorage.getItem('letterSpacing') === 'true';
    const largeCursor = localStorage.getItem('largeCursor') === 'true';
    const animationsDisabled = localStorage.getItem('animationsDisabled') === 'true';
    const grayscaleMode = localStorage.getItem('grayscaleMode') === 'true';
    const blueLightFilter = localStorage.getItem('blueLightFilter') === 'true';
    const invertedColors = localStorage.getItem('invertedColors') === 'true';
    const enhancedKeyboardNav = localStorage.getItem('enhancedKeyboardNav') === 'true';
    const screenReaderMode = localStorage.getItem('screenReaderMode') === 'true';

    // Update toggle switch states
    updateToggleSwitch('lineHeightControl', lineHeight);
    updateToggleSwitch('letterSpacingControl', letterSpacing);
    updateToggleSwitch('cursorControl', largeCursor);
    updateToggleSwitch('animationControl', animationsDisabled);
    updateToggleSwitch('underlineControl', underlineLinks);
    updateToggleSwitch('highContrastControl', highContrast);
    updateToggleSwitch('grayscaleControl', grayscaleMode);
    updateToggleSwitch('blueLightControl', blueLightFilter);
    updateToggleSwitch('invertControl', invertedColors);
    updateToggleSwitch('keyboardControl', enhancedKeyboardNav);
    updateToggleSwitch('screenReaderControl', screenReaderMode);

    // Update font size buttons
    document.querySelectorAll('[onclick*="setFontSize"]').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`[onclick="setFontSize('${savedFontSize}')"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Update accessibility button state
    const accessibilityBtn = document.getElementById('accessibility-btn');
    if (accessibilityBtn) {
        const panel = document.getElementById('accessibilityPanel');
        if (panel && panel.classList.contains('open')) {
            accessibilityBtn.innerHTML = '<i class="fas fa-times" style="line-height: 1;"></i>';
            accessibilityBtn.classList.remove('pulse');
        } else {
            accessibilityBtn.innerHTML = '<span style="line-height: 1;">♿</span>';
            accessibilityBtn.classList.add('pulse');
        }
    }
}

function setFontSize(size) {
    // Remove all font size classes
    document.body.classList.remove('font-small', 'font-normal', 'font-large', 'font-extra-large');

    // Add new font size class
    document.body.classList.add(`font-${size}`);
    localStorage.setItem('fontSize', size);

    // Update button states
    document.querySelectorAll('.font-size-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Find and activate the clicked button
    const clickedBtn = document.querySelector(`[onclick="setFontSize('${size}')"]`);
    if (clickedBtn) {
        clickedBtn.classList.add('active');
    }

    announceToScreenReader(`Ukuran teks diubah ke ${getFontSizeDescription(size)}`);
}

function getFontSizeDescription(size) {
    const descriptions = {
        'small': 'kecil',
        'normal': 'normal',
        'large': 'besar',
        'extra-large': 'sangat besar'
    };
    return descriptions[size] || 'normal';
}

function toggleHighContrast() {
    const isHighContrast = document.body.classList.toggle('high-contrast');
    localStorage.setItem('highContrast', isHighContrast);
    updateToggleSwitch('highContrastControl', isHighContrast);
    announceToScreenReader(`Mode kontras tinggi ${isHighContrast ? 'diaktifkan' : 'dinonaktifkan'}`);
}

function toggleUnderline() {
    const isUnderlined = document.body.classList.toggle('underline-links');
    localStorage.setItem('underlineLinks', isUnderlined);
    updateToggleSwitch('underlineControl', isUnderlined);
    announceToScreenReader(`Garis bawah link ${isUnderlined ? 'diaktifkan' : 'dinonaktifkan'}`);
}

// Screen Reader Announcements
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', 'polite');
    announcement.className = 'sr-only';
    announcement.textContent = message;

    document.body.appendChild(announcement);

    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// Enhanced Animation Functions
function initializeEnhancedAnimations() {
    // Add parallax effect to background elements
    const bgElements = document.querySelectorAll('.hero-bg-element');
    bgElements.forEach(element => {
        element.classList.add('hero-bg-element');
    });

    // Enhanced mouse tracking for subtle effects
    document.addEventListener('mousemove', (e) => {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;

        // Subtle parallax for hero elements
        const heroContent = document.querySelector('.hero-content');
        if (heroContent) {
            const offsetX = (mouseX - 0.5) * 10;
            const offsetY = (mouseY - 0.5) * 10;
            heroContent.style.transform = `translateX(${offsetX}px) translateY(${offsetY}px)`;
        }

        // Parallax for background bubbles
        bgElements.forEach((element, index) => {
            const speed = (index + 1) * 0.5;
            const offsetX = (mouseX - 0.5) * speed * 20;
            const offsetY = (mouseY - 0.5) * speed * 20;
            element.style.transform = `translateX(${offsetX}px) translateY(${offsetY}px)`;
        });
    });

    // Add entrance animation to sections
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                entry.target.classList.add('section-revealed');
            }
        });
    }, observerOptions);

    // Observe all sections for entrance animations
    document.querySelectorAll('section').forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'all 1s cubic-bezier(0.4, 0, 0.2, 1)';
        sectionObserver.observe(section);
    });

    // Enhanced hover effects with magnetic cursor
    const buttons = document.querySelectorAll('.hero-btn-primary, .hero-btn-secondary');
    buttons.forEach(button => {
        button.addEventListener('mousemove', (e) => {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            button.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px) scale(1.02)`;
        });

        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Smooth reveal for info items
    const infoItems = document.querySelectorAll('.info-item');
    infoItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        setTimeout(() => {
            item.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 1000 + (index * 100));
    });

    // Add typing effect for subtitle (optional enhancement)
    const subtitle = document.querySelector('.hero-subtitle');
    if (subtitle) {
        const text = subtitle.textContent;
        subtitle.textContent = '';
        subtitle.style.opacity = '1';

        let charIndex = 0;
        const typeInterval = setInterval(() => {
            if (charIndex < text.length) {
                subtitle.textContent += text[charIndex];
                charIndex++;
            } else {
                clearInterval(typeInterval);
            }
        }, 50);
    }

    // Initialize scroll-triggered animations
    initializeScrollAnimations();
}

function initializeScrollAnimations() {
    // Smooth scroll indicator animation
    const scrollIndicator = document.querySelector('[style*="animation: bounce"]');
    if (scrollIndicator) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = scrolled / maxScroll;

            if (scrollPercent > 0.1) {
                scrollIndicator.style.opacity = 1 - scrollPercent;
            }
        });
    }

    // Remove parallax effect for hero section to prevent overlapping
    // Parallax effect was causing the hero section to overlap with other sections
}

// Footer Statistics Functions
function initializeFooterStats() {
    // Simulate real-time statistics
    updateVisitorCount();
    updateActiveUsers();

    // Update statistics every 30 seconds
    setInterval(() => {
        updateActiveUsers();
    }, 30000);

    // Update visitor count every 5 minutes
    setInterval(() => {
        updateVisitorCount();
    }, 300000);
}

function updateVisitorCount() {
    const visitorElement = document.getElementById('visitorCount');
    if (visitorElement) {
        // Get current count or start with base number
        let currentCount = parseInt(visitorElement.textContent.replace(',', ''));
        if (isNaN(currentCount)) currentCount = 15234;

        // Simulate gradual increase
        const increment = Math.floor(Math.random() * 5) + 1;
        currentCount += increment;

        // Animate the counter
        animateCounter(visitorElement, currentCount - increment, currentCount, 1000);
    }
}

function updateActiveUsers() {
    const activeUsersElement = document.getElementById('activeUsers');
    if (activeUsersElement) {
        // Simulate realistic active user count
        const baseCount = 200;
        const variation = Math.floor(Math.random() * 100) - 50;
        const newCount = Math.max(baseCount + variation, 50);

        // Add animation class
        activeUsersElement.classList.add('animated');

        // Update the count
        animateCounter(activeUsersElement, parseInt(activeUsersElement.textContent), newCount, 800);

        // Remove animation class after animation completes
        setTimeout(() => {
            activeUsersElement.classList.remove('animated');
        }, 1000);
    }
}

function animateCounter(element, start, end, duration) {
    const startTime = performance.now();
    const startValue = start;
    const endValue = end;
    const difference = endValue - startValue;

    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentValue = Math.floor(startValue + (difference * easeOutQuart));

        // Format number with commas
        element.textContent = currentValue.toLocaleString('id-ID');

        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }

    requestAnimationFrame(updateCounter);
}

// Enhanced Accessibility Functions - WCAG 2.1 AA Compliant

// Keyboard Navigation Support
document.addEventListener('keydown', function(e) {
    // ESC key to close panel
    if (e.key === 'Escape') {
        const panel = document.getElementById('accessibilityPanel');
        if (panel && panel.classList.contains('open')) {
            toggleAccessibilitySidebar();
        }
    }

    // Alt + A to open accessibility panel
    if (e.altKey && e.key === 'a') {
        e.preventDefault();
        toggleAccessibilitySidebar();
    }
});

// Add keyboard support for toggle switches
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.accessibility-switch');
    toggles.forEach(toggle => {
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Add keyboard support for accessibility buttons
    const accessibilityBtns = document.querySelectorAll('.accessibility-btn');
    accessibilityBtns.forEach(btn => {
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});

// Additional Accessibility Toggle Functions
function toggleLineHeight() {
    const isLineHeight = document.body.classList.toggle('enhanced-line-height');
    localStorage.setItem('lineHeight', isLineHeight);
    updateToggleSwitch('lineHeightControl', isLineHeight);
    announceToScreenReader(`Jarak baris ${isLineHeight ? 'ditingkatkan' : 'normal'}`);
}

function toggleLetterSpacing() {
    const isLetterSpacing = document.body.classList.toggle('enhanced-letter-spacing');
    localStorage.setItem('letterSpacing', isLetterSpacing);
    updateToggleSwitch('letterSpacingControl', isLetterSpacing);
    announceToScreenReader(`Jarak huruf ${isLetterSpacing ? 'ditingkatkan' : 'normal'}`);
}

function toggleLargeCursor() {
    const isLargeCursor = document.body.classList.toggle('large-cursor');
    localStorage.setItem('largeCursor', isLargeCursor);
    updateToggleSwitch('cursorControl', isLargeCursor);
    announceToScreenReader(`Kursor besar ${isLargeCursor ? 'diaktifkan' : 'dinonaktifkan'}`);
}

function toggleAnimations() {
    const isAnimationsDisabled = !document.body.classList.contains('no-animations');
    document.body.classList.toggle('no-animations');
    localStorage.setItem('animationsDisabled', isAnimationsDisabled);
    updateToggleSwitch('animationControl', isAnimationsDisabled);
    announceToScreenReader(`Animasi ${isAnimationsDisabled ? 'dinonaktifkan' : 'diaktifkan'}`);
}

function toggleKeyboardNavigation() {
    const isEnhancedKeyboard = document.body.classList.toggle('enhanced-keyboard-nav');
    localStorage.setItem('enhancedKeyboardNav', isEnhancedKeyboard);
    updateToggleSwitch('keyboardControl', isEnhancedKeyboard);
    announceToScreenReader(`Navigasi keyboard ditingkatkan ${isEnhancedKeyboard ? 'diaktifkan' : 'dinonaktifkan'}`);
}

function toggleScreenReaderMode() {
    const isScreenReader = document.body.classList.toggle('screen-reader-mode');
    localStorage.setItem('screenReaderMode', isScreenReader);
    updateToggleSwitch('screenReaderControl', isScreenReader);
    announceToScreenReader(`Mode screen reader ${isScreenReader ? 'diaktifkan' : 'dinonaktifkan'}`);
}

function resetAllAccessibility() {
    // Remove all accessibility classes
    document.body.className = '';

    // Reset font size to normal
    document.body.classList.add('font-normal');

    // Clear all localStorage items
    const accessibilityKeys = [
        'fontSize', 'highContrast', 'underlineLinks', 'lineHeight',
        'letterSpacing', 'largeCursor', 'animationsDisabled',
        'grayscaleMode', 'blueLightFilter', 'invertedColors',
        'enhancedKeyboardNav', 'screenReaderMode'
    ];

    accessibilityKeys.forEach(key => {
        localStorage.removeItem(key);
    });

    // Remove dynamic styles
    const dynamicStyles = ['no-animations', 'keyboard-nav-style'];
    dynamicStyles.forEach(id => {
        const style = document.getElementById(id);
        if (style) style.remove();
    });

    // Remove reading guide
    const readingGuide = document.getElementById('reading-guide');
    if (readingGuide) readingGuide.remove();

    // Update all button states
    updateAccessibilityButtonStates();

    // Announce reset
    announceToScreenReader('Semua pengaturan aksesibilitas telah direset ke default');

    // Add visual feedback
    const resetBtn = document.querySelector('.btn-danger');
    if (resetBtn) {
        resetBtn.style.background = '#16a34a';
        resetBtn.innerHTML = '<i class="fas fa-check me-2"></i> Berhasil Direset!';
        setTimeout(() => {
            resetBtn.style.background = '';
            resetBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i> Reset Semua Pengaturan';
        }, 2000);
    }
}

// Enhanced Font Size Control
function setFontSize(size) {
    // Remove all font size classes
    document.body.classList.remove('font-small', 'font-normal', 'font-large', 'font-extra-large');

    // Add new font size class
    document.body.classList.add(`font-${size}`);
    localStorage.setItem('fontSize', size);

    // Update button states
    document.querySelectorAll('.font-size-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Find and activate the clicked button
    const clickedBtn = document.querySelector(`[onclick="setFontSize('${size}')"]`);
    if (clickedBtn) {
        clickedBtn.classList.add('active');
    }

    announceToScreenReader(`Ukuran teks diubah ke ${getFontSizeDescription(size)}`);
}

function getFontSizeDescription(size) {
    const descriptions = {
        'small': 'kecil',
        'normal': 'normal',
        'large': 'besar',
        'extra-large': 'sangat besar'
    };
    return descriptions[size] || 'normal';
}

// Line Height Control
function toggleLineHeight() {
    const control = document.getElementById('lineHeightControl');
    const isActive = !control.classList.contains('active');

    document.body.classList.toggle('enhanced-line-height', isActive);
    control.classList.toggle('active', isActive);
    control.setAttribute('aria-checked', isActive);
    localStorage.setItem('lineHeight', isActive);

    announceToScreenReader(`Jarak baris ${isActive ? 'ditingkatkan' : 'normal'}`);
}

// Letter Spacing Control
function toggleLetterSpacing() {
    const control = document.getElementById('letterSpacingControl');
    const isActive = !control.classList.contains('active');

    document.body.classList.toggle('enhanced-letter-spacing', isActive);
    control.classList.toggle('active', isActive);
    control.setAttribute('aria-checked', isActive);
    localStorage.setItem('letterSpacing', isActive);

    announceToScreenReader(`Jarak huruf ${isActive ? 'ditingkatkan' : 'normal'}`);
}

// Large Cursor Control
function toggleLargeCursor() {
    const control = document.getElementById('cursorControl');
    const isActive = !control.classList.contains('active');

    document.body.classList.toggle('large-cursor', isActive);
    control.classList.toggle('active', isActive);
    control.setAttribute('aria-checked', isActive);
    localStorage.setItem('largeCursor', isActive);

    announceToScreenReader(`Kursor besar ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
}

// Animation Control
function toggleAnimations() {
    const control = document.getElementById('animationControl');
    const isActive = !control.classList.contains('active');

    if (isActive) {
        // Disable animations
        const style = document.createElement('style');
        style.id = 'no-animations';
        style.textContent = `
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        `;
        document.head.appendChild(style);
    } else {
        // Enable animations
        const style = document.getElementById('no-animations');
        if (style) style.remove();
    }

    control.classList.toggle('active', isActive);
    control.setAttribute('aria-checked', isActive);
    localStorage.setItem('animationsDisabled', isActive);

    announceToScreenReader(`Animasi ${isActive ? 'dihentikan' : 'diaktifkan'}`);
}

// Grayscale Mode
function setGrayscale() {
    const isActive = document.body.classList.toggle('grayscale-mode');
    localStorage.setItem('grayscaleMode', isActive);
    updateToggleSwitch('grayscaleControl', isActive);
    announceToScreenReader(`Mode grayscale ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
}

// Blue Light Filter
function setBlueLightFilter() {
    const isActive = document.body.classList.toggle('blue-light-filter');
    localStorage.setItem('blueLightFilter', isActive);
    updateToggleSwitch('blueLightControl', isActive);
    announceToScreenReader(`Filter cahaya biru ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
}

// Inverted Colors
function setInvertedColors() {
    const isActive = document.body.classList.toggle('inverted-colors');
    localStorage.setItem('invertedColors', isActive);
    updateToggleSwitch('invertControl', isActive);
    announceToScreenReader(`Warna terbalik ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
}

// Reading Guide
function toggleReadingGuide() {
    const existingGuide = document.getElementById('reading-guide');

    if (existingGuide) {
        existingGuide.remove();
        announceToScreenReader('Panduan baca dinonaktifkan');
    } else {
        const guide = document.createElement('div');
        guide.id = 'reading-guide';
        guide.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #ff6b6b;
            z-index: 9999;
            pointer-events: none;
            box-shadow: 0 0 10px rgba(255, 107, 107, 0.8);
        `;
        document.body.appendChild(guide);

        // Follow mouse cursor
        document.addEventListener('mousemove', (e) => {
            if (document.getElementById('reading-guide')) {
                guide.style.top = e.clientY + 'px';
            }
        });

        announceToScreenReader('Panduan baca diaktifkan. Gerakkan mouse untuk memandu mata Anda');
    }
}

// Focus to Main Content
function focusToMainContent() {
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        mainContent.focus();
        mainContent.scrollIntoView({ behavior: 'smooth' });
        announceToScreenReader('Fokus dipindahkan ke konten utama');
    }
}

// Keyboard Navigation Enhancement
function toggleKeyboardNavigation() {
    const isActive = !document.body.classList.contains('enhanced-keyboard-nav');
    document.body.classList.toggle('enhanced-keyboard-nav', isActive);

    if (isActive) {
        // Add focus indicators
        const style = document.createElement('style');
        style.id = 'keyboard-nav-style';
        style.textContent = `
            .enhanced-keyboard-nav *:focus {
                outline: 3px solid #0066cc !important;
                outline-offset: 2px !important;
                background: rgba(0, 102, 204, 0.1) !important;
            }
        `;
        document.head.appendChild(style);
    } else {
        const style = document.getElementById('keyboard-nav-style');
        if (style) style.remove();
    }

    localStorage.setItem('enhancedKeyboardNav', isActive);
    updateToggleSwitch('keyboardControl', isActive);
    announceToScreenReader(`Navigasi keyboard ${isActive ? 'ditingkatkan' : 'normal'}`);
}

// Screen Reader Mode
function toggleScreenReaderMode() {
    const isActive = !document.body.classList.contains('screen-reader-mode');
    document.body.classList.toggle('screen-reader-mode', isActive);
    localStorage.setItem('screenReaderMode', isActive);

    if (isActive) {
        // Add semantic announcements for screen readers
        addSemanticAnnouncements();
    } else {
        removeSemanticAnnouncements();
    }
    updateToggleSwitch('screenReaderControl', isActive);
    announceToScreenReader(`Mode screen reader ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
}

// Page Summary for Screen Readers
function speakPageSummary() {
    const summary = `
        Halaman PTSP MTsN 2 KOTA MALANG.
        Ini adalah halaman utama layanan terpadu satu pintu.
        Bagian utama termasuk: slider hero, statistik kinerja, akses cepat, dan informasi layanan.
        Gunakan tombol Tab untuk navigasi atau panel aksesibilitas untuk pengaturan tambahan.
    `;

    announceToScreenReader(summary);
}

// Reset All Settings
function resetAllAccessibilitySettings() {
    // Clear localStorage
    const accessibilityKeys = [
        'fontSize', 'highContrast', 'underlineLinks', 'lineHeight',
        'letterSpacing', 'largeCursor', 'animationsDisabled',
        'grayscaleMode', 'blueLightFilter', 'invertedColors',
        'enhancedKeyboardNav', 'screenReaderMode'
    ];

    accessibilityKeys.forEach(key => {
        localStorage.removeItem(key);
    });

    // Remove all classes
    document.body.className = 'font-normal';

    // Remove dynamic styles
    const dynamicStyles = ['no-animations', 'keyboard-nav-style'];
    dynamicStyles.forEach(id => {
        const style = document.getElementById(id);
        if (style) style.remove();
    });

    // Remove reading guide
    const readingGuide = document.getElementById('reading-guide');
    if (readingGuide) readingGuide.remove();

    // Reset all toggle switches
    document.querySelectorAll('.accessibility-switch').forEach(toggle => {
        toggle.classList.remove('active');
        toggle.setAttribute('aria-checked', 'false');
    });

    // Reset font size buttons
    document.querySelectorAll('.font-size-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('[onclick="setFontSize('normal')"]').classList.add('active');

    announceToScreenReader('Semua pengaturan aksesibilitas telah direset ke pengaturan default');
}

// Helper Functions
function addSemanticAnnouncements() {
    // Add region labels for better screen reader navigation
    const main = document.querySelector('main');
    if (main) main.setAttribute('role', 'main');

    const header = document.querySelector('header');
    if (header) header.setAttribute('role', 'banner');

    const footer = document.querySelector('footer');
    if (footer) footer.setAttribute('role', 'contentinfo');
}

function removeSemanticAnnouncements() {
    // Remove additional role attributes if needed
}

// Initialize Interactions
function initializeInteractions() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Close mobile menu if open
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                        navbarCollapse.classList.remove('show');
                    }

                    // Focus on target for accessibility
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                }
            }
        });
    });

    // Add hover effects for cards
    document.querySelectorAll('.feature-card, .service-card, .stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });

        // Add keyboard interaction
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Add keyboard support for toggle switches
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Add keyboard support for accessibility sidebar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('accessibilitySidebar');
            if (sidebar && sidebar.classList.contains('open')) {
                toggleAccessibilitySidebar();
            }
        }
    });
}

// Initialize Animations
function initializeAnimations() {
    // Animate progress bars when they come into view
    const progressObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBars = entry.target.querySelectorAll('.stat-progress-bar');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 200);
                });

                progressObserver.unobserve(entry.target);
            }
        });
    });

    document.querySelectorAll('.stat-card').forEach(card => {
        progressObserver.observe(card);
    });
}

// Keyboard Navigation
function initializeKeyboardNavigation() {
    // Focus management
    document.addEventListener('keydown', function(e) {
        // ESC key to close mobile menu
        if (e.key === 'Escape') {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                navbarCollapse.classList.remove('show');
                document.querySelector('.navbar-toggler').focus();
            }
        }
    });

    // Skip link functionality
    const skipLink = document.querySelector('.skip-link');
    if (skipLink) {
        skipLink.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector('#main-content');
            if (target) {
                target.setAttribute('tabindex', '-1');
                target.focus();
                target.scrollIntoView();
            }
        });
    }
}

// Performance monitoring
if (window.performance && window.performance.timing && window.performance.timing.loadEventEnd > 0) {
    window.addEventListener('load', function() {
        const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
        if (loadTime > 0) {
            console.log(`Page load time: ${loadTime}ms`);

            // Log performance metrics for monitoring
            if (window.gtag) {
                gtag('event', 'page_load_time', {
                    value: loadTime,
                    custom_parameter: 'ptsp_page'
                });
            }
        }
    });
}

      // Hero Slider Functionality
let currentSlide = 0;
let slides = [];
let slideInterval;

function initHeroSlider() {
    slides = document.querySelectorAll('.slide');
    if (slides.length <= 1) return;

    // Set initial active slide
    updateSlide(0);

    // Auto-play slider
    slideInterval = setInterval(nextSlide, 5000);

    // Pause on hover
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', () => clearInterval(slideInterval));
        heroSection.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 5000);
        });
    }
}

function updateSlide(index) {
    // Remove active class from all slides
    slides.forEach((slide, i) => {
        slide.classList.remove('active');
        const dot = document.querySelector(`[data-slide="${i}"]`);
        if (dot) dot.classList.remove('active');
    });

    // Add active class to current slide
    if (slides[index]) {
        slides[index].classList.add('active');
        const dot = document.querySelector(`[data-slide="${index}"]`);
        if (dot) dot.classList.add('active');

        // Apply custom colors from data attributes
        const textColor = slides[index].getAttribute('data-text-color');
        const overlayColor = slides[index].getAttribute('data-overlay-color');

        if (textColor) {
            slides[index].querySelector('.hero-title').style.color = textColor;
            slides[index].querySelector('.hero-subtitle').style.color = textColor;
            slides[index].querySelector('.hero-description').style.color = textColor;
        }

        if (overlayColor) {
            slides[index].querySelector('.overlay').style.background = overlayColor;
        }
    }

    currentSlide = index;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlide(currentSlide);
}

function goToSlide(index) {
    if (index >= 0 && index < slides.length) {
        updateSlide(index);
        // Reset auto-play timer
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }
}

// Initialize slider when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initHeroSlider, 100);
});

// Service Worker registration for PWA capabilities
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(error) {
                console.log('ServiceWorker registration failed');
            });
    });
}

// Multi-language Support
const translations = {
    'ina': {
        'home': 'Beranda',
        'visitor-book': 'Buku Tamu',
        'services': 'Layanan',
        'about': 'Tentang',
        'contact': 'Kontak',
        'login': 'Login',
        'hero-title': 'Selamat Datang di PTSP MTsN 2 Kota Malang',
        'hero-subtitle': 'Pelayanan Terpadu Satu Pintu untuk Kemudahan dan Efisiensi Layanan Masyarakat',
        'services-title': 'Layanan Kami',
        'services-subtitle': 'Berbagai layanan yang tersedia untuk memenuhi kebutuhan masyarakat',
        'quick-access': 'Akses Cepat',
        'contact-title': 'Kontak Kami',
        'stats-visitors': 'Total Tamu Hari Ini',
        'stats-active': 'Sedang Aktif',
        'stats-date': 'Tanggal',
        'hours': 'Senin - Jumat: 07:30 - 15:00',
        'saturday': 'Sabtu: 07:30 - 12:00',
        'closed': 'Minggu & Libur: Tutup'
    },
    'eng': {
        'home': 'Home',
        'visitor-book': 'Visitor Book',
        'services': 'Services',
        'about': 'About',
        'contact': 'Contact',
        'login': 'Login',
        'hero-title': 'Welcome to PTSP MTsN 2 Kota Malang',
        'hero-subtitle': 'Integrated One-Stop Service for Convenience and Efficiency of Public Services',
        'services-title': 'Our Services',
        'services-subtitle': 'Various services available to meet community needs',
        'quick-access': 'Quick Access',
        'contact-title': 'Contact Us',
        'stats-visitors': 'Total Visitors Today',
        'stats-active': 'Currently Active',
        'stats-date': 'Date',
        'hours': 'Monday - Friday: 07:30 - 15:00',
        'saturday': 'Saturday: 07:30 - 12:00',
        'closed': 'Sunday & Holidays: Closed'
    },
    'arb': {
        'home': 'الرئيسية',
        'visitor-book': 'سجل الزوار',
        'services': 'الخدمات',
        'about': 'حول',
        'contact': 'اتصل',
        'login': 'تسجيل الدخول',
        'hero-title': 'مرحبا بكم في PTSP MTsN 2 كوتا مالانج',
        'hero-subtitle': 'خدمة النافذة الواحدة المتكاملة لراحة وكفاءة الخدمات العامة',
        'services-title': 'خدماتنا',
        'services-subtitle': 'مختلف الخدمات المتاحة لتلبية احتياجات المجتمع',
        'quick-access': 'وصول سريع',
        'contact-title': 'اتصل بنا',
        'stats-visitors': 'إجمالي الزوار اليوم',
        'stats-active': 'نشط حاليا',
        'stats-date': 'التاريخ',
        'hours': 'الإثنين - الجمعة: 07:30 - 15:00',
        'saturday': 'السبت: 07:30 - 12:00',
        'closed': 'الأحد والعطلات: مغلق'
    }
};

// Initialize language on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentLang = localStorage.getItem('language') || 'ina';
    updateLanguage(currentLang);
});

// Change language function
function changeLanguage(lang) {
    localStorage.setItem('language', lang);
    updateLanguage(lang);
}

function updateLanguage(lang) {
    // Update current language display
    const currentLangSpan = document.getElementById('currentLang');
    if (currentLangSpan) {
        currentLangSpan.textContent = lang.toUpperCase();
    }

    // Update RTL for Arabic
    const html = document.documentElement;
    if (lang === 'arb') {
        html.setAttribute('dir', 'rtl');
        html.setAttribute('lang', 'ar');
        document.body.classList.add('rtl');
    } else {
        html.setAttribute('dir', 'ltr');
        html.setAttribute('lang', lang === 'eng' ? 'en' : 'id');
        document.body.classList.remove('rtl');
    }

    // Update all elements with data-lang-key
    const elements = document.querySelectorAll('[data-lang-key]');
    elements.forEach(element => {
        const key = element.getAttribute('data-lang-key');
        const translation = translations[lang][key];
        if (translation) {
            if (element.tagName === 'INPUT' && element.type === 'submit') {
                element.value = translation;
            } else {
                element.textContent = translation;
            }
        }
    });
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
});

// Floating Action Buttons Functionality
function initFloatingButtons() {
    const backToTopBtn = document.getElementById('back-to-top-btn');
    
    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
}

// Initialize floating buttons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initFloatingButtons();
});

// Back to top function
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/* ===== INITIALIZATION ===== */

document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    initializeSlider();
    initializeNotification();
    initializeFormListeners();
    showNotification('success', 'Welcome to StudentHub Portal!');
});

function initializeNotification() {
    const closeBtn = document.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeNotification);
    }
}

/* Form Submit Handlers for Dynamic Notifications */
function initializeFormListeners() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('success', 'Login successful! Redirecting to dashboard...');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1500);
        });
    }

    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('success', 'Registration submitted successfully! Please check your email for confirmation.');
        });
    }

    // Contact Form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const nameInput = document.getElementById('contactName');
            const categorySelect = document.getElementById('contactCategory');
            const userName = nameInput && nameInput.value ? nameInput.value.trim() : 'Student';
            const category = categorySelect && categorySelect.value ? categorySelect.value : 'General Enquiry';
            
            showNotification('success', `Thank you, ${userName}! Your message regarding "${category}" has been received. Our team will contact you shortly.`);
            contactForm.reset();
        });
    }

    // Feedback Form
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('success', 'Feedback received! Thank you for helping us improve StudentHub.');
            feedbackForm.reset();
        });
    }

    // Profile Form
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('success', 'Profile information updated successfully.');
        });
    }
}

/* ===== THEME SWITCHER ===== */

function toggleTheme() {
    const body = document.body;
    const themeSwitcher = document.getElementById('themeSwitcher');
    
    if (body.classList.contains('dark-theme')) {
        body.classList.remove('dark-theme');
        if (themeSwitcher) themeSwitcher.innerHTML = '🌙';
        localStorage.setItem('theme', 'light');
        showNotification('info', 'Switched to Light Mode');
    } else {
        body.classList.add('dark-theme');
        if (themeSwitcher) themeSwitcher.innerHTML = '☀️';
        localStorage.setItem('theme', 'dark');
        showNotification('info', 'Switched to Dark Mode');
    }
}

function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const body = document.body;
    const themeSwitcher = document.getElementById('themeSwitcher');
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-theme');
        if (themeSwitcher) themeSwitcher.innerHTML = '☀️';
    } else {
        body.classList.remove('dark-theme');
        if (themeSwitcher) themeSwitcher.innerHTML = '🌙';
    }
}

/* ===== HAMBURGER MENU ===== */

function toggleHamburger() {
    const hamburger = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger) hamburger.classList.toggle('active');
    if (navMenu) navMenu.classList.toggle('active');
}

function closeHamburger() {
    const hamburger = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger) hamburger.classList.remove('active');
    if (navMenu) navMenu.classList.remove('active');
}

/* Close hamburger when clicking outside header */
document.addEventListener('click', function(event) {
    const hamburger = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');
    const header = document.querySelector('header');
    
    if (header && !header.contains(event.target) && hamburger && navMenu) {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }
});

/* ===== NOTIFICATION BANNER ===== */

let notificationTimeout;

function showNotification(type, message) {
    const banner = document.getElementById('notificationBanner');
    if (!banner) return;

    const text = document.getElementById('notificationText');
    if (notificationTimeout) {
        clearTimeout(notificationTimeout);
    }

    banner.style.display = 'flex';
    banner.classList.remove('notification-success', 'notification-error', 'notification-info', 'notification-warning', 'hide');
    banner.classList.add(`notification-${type}`, 'show');
    if (text) text.textContent = message;

    notificationTimeout = setTimeout(() => {
        closeNotification();
    }, 5000);
}

function closeNotification() {
    const banner = document.getElementById('notificationBanner');
    if (!banner) return;

    if (notificationTimeout) {
        clearTimeout(notificationTimeout);
    }

    banner.classList.remove('show');
    banner.classList.add('hide');
    setTimeout(() => {
        banner.style.display = 'none';
        banner.classList.remove('hide', 'notification-success', 'notification-error', 'notification-info', 'notification-warning');
    }, 300);
}

/* ===== IMAGE/CONTENT SLIDER ===== */

let currentSlideIndex = 0;
let autoSlideInterval;

function initializeSlider() {
    const slides = document.querySelectorAll('.slide');
    if (!slides || slides.length === 0) return;
    
    currentSlideIndex = 0;
    updateSlider();
    startAutoSlide();

    // Pause on hover
    const sliderContainer = document.querySelector('.slider-container');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
        sliderContainer.addEventListener('mouseleave', () => startAutoSlide());
    }
}

function changeSlide(n) {
    clearInterval(autoSlideInterval);
    currentSlideIndex += n;
    updateSlider();
    startAutoSlide();
}

function currentSlide(n) {
    clearInterval(autoSlideInterval);
    currentSlideIndex = n;
    updateSlider();
    startAutoSlide();
}

function updateSlider() {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (!slides || slides.length === 0) return;

    // Wrap around
    if (currentSlideIndex >= slides.length) {
        currentSlideIndex = 0;
    }
    if (currentSlideIndex < 0) {
        currentSlideIndex = slides.length - 1;
    }
    
    // Hide all slides
    slides.forEach((slide, idx) => {
        slide.classList.remove('active');
        slide.style.display = 'none';
    });
    
    // Remove active class from all indicators
    indicators.forEach(indicator => {
        indicator.classList.remove('active');
    });
    
    // Show current slide and update indicator
    if (slides[currentSlideIndex]) {
        slides[currentSlideIndex].style.display = 'flex';
        slides[currentSlideIndex].classList.add('active');
    }
    if (indicators[currentSlideIndex]) {
        indicators[currentSlideIndex].classList.add('active');
    }
}

function startAutoSlide() {
    const slides = document.querySelectorAll('.slide');
    if (!slides || slides.length === 0) return;
    
    clearInterval(autoSlideInterval);
    autoSlideInterval = setInterval(() => {
        currentSlideIndex++;
        updateSlider();
    }, 5000);
}

/* ===== COLLAPSIBLE FAQ ===== */

function toggleFAQ(element) {
    const item = element.closest('.faq-item');
    if (!item) return;

    const answer = item.querySelector('.faq-answer');
    const question = item.querySelector('.faq-question');
    const icon = item.querySelector('.faq-icon');
    
    const isOpen = question.classList.contains('active');
    
    // Close all other FAQ items
    document.querySelectorAll('.faq-question').forEach(q => {
        q.classList.remove('active');
        const ic = q.querySelector('.faq-icon');
        if (ic) ic.textContent = '+';
    });
    document.querySelectorAll('.faq-answer').forEach(a => {
        a.classList.remove('show');
    });
    
    // Toggle clicked item
    if (!isOpen) {
        question.classList.add('active');
        answer.classList.add('show');
        if (icon) icon.textContent = '−';
    }
}

/* ===== MODAL POPUP ===== */

function openModal(title, message) {
    const modal = document.getElementById('modal');
    const overlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    
    if (modal && overlay) {
        if (modalTitle) modalTitle.textContent = title;
        if (modalMessage) modalMessage.textContent = message;
        modal.classList.remove('hidden');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.getElementById('modal');
    const overlay = document.getElementById('modalOverlay');
    
    if (modal && overlay) {
        modal.classList.add('hidden');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function confirmModal() {
    showNotification('success', 'Action confirmed successfully!');
    closeModal();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});




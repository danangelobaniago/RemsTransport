
let heroIndex = 0;
let heroTimer;

function heroGoTo(n) {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    if (!slides.length) return;

    slides[heroIndex].classList.remove('active');
    dots[heroIndex].classList.remove('active');
    heroIndex = (n + slides.length) % slides.length;
    slides[heroIndex].classList.add('active');
    dots[heroIndex].classList.add('active');
}

function heroCarousel(dir) {
    clearInterval(heroTimer);
    heroGoTo(heroIndex + dir);
    heroTimer = setInterval(() => heroGoTo(heroIndex + 1), 5000);
}

document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.carousel-slide');
    if (slides.length) {
        heroTimer = setInterval(() => heroGoTo(heroIndex + 1), 5000);
    }
});




    const slider = document.getElementById('feedbackSlider');

    if (slider) {
        let autoScrollInterval;
        let resumeTimeout;

        // Index-based navigation instead of scrollBy(pixel math) — scrollBy
        // drifted out of alignment over time (each step's offsetWidth+gap
        // guess didn't quite match the real spacing), eventually leaving two
        // cards half-cut-off on screen instead of one fully in view. Always
        // scrolling a real card element into view can't drift: it's measured
        // fresh from the DOM every time.
        function getCards() {
            return Array.from(slider.querySelectorAll('.feedback-item'));
        }

        function currentIndex(cards) {
            if (!cards.length) return 0;
            const viewportCenter = slider.scrollLeft + slider.offsetWidth / 2;
            let closest = 0;
            let closestDist = Infinity;
            cards.forEach((card, i) => {
                const cardCenter = card.offsetLeft + card.offsetWidth / 2;
                const dist = Math.abs(cardCenter - viewportCenter);
                if (dist < closestDist) {
                    closestDist = dist;
                    closest = i;
                }
            });
            return closest;
        }

        function scrollToIndex(i) {
            const cards = getCards();
            if (!cards.length) return;
            const clamped = Math.max(0, Math.min(i, cards.length - 1));
            cards[clamped].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }

        function startAutoScroll() {
            clearInterval(autoScrollInterval); // Never stack multiple intervals
            autoScrollInterval = setInterval(() => {
                const cards = getCards();
                if (!cards.length) return;
                const next = (currentIndex(cards) + 1) % cards.length; // loop back to the first card
                scrollToIndex(next);
            }, 5000); // 5 seconds
        }

        function pauseAutoScroll() {
            clearInterval(autoScrollInterval);
            clearTimeout(resumeTimeout);
        }

        // Pause while the visitor is actually interacting, then resume shortly after.
        // mouseenter/mouseleave alone don't reliably fire on touch devices, so we
        // also pause on touchstart and resume a moment after touchend.
        function pauseThenResume() {
            pauseAutoScroll();
            resumeTimeout = setTimeout(startAutoScroll, 4000);
        }

        function manualScroll(direction) {
            pauseAutoScroll();
            scrollToIndex(currentIndex(getCards()) + direction);
            resumeTimeout = setTimeout(startAutoScroll, 4000);
        }
        window.manualScroll = manualScroll;

        slider.addEventListener('mouseenter', pauseAutoScroll);
        slider.addEventListener('mouseleave', startAutoScroll);
        slider.addEventListener('touchstart', pauseAutoScroll, { passive: true });
        slider.addEventListener('touchend', pauseThenResume, { passive: true });

        startAutoScroll();
    }
// FAQ Accordion
function toggleFaq(btn) {
    const item = btn.parentElement;
    const body = btn.nextElementSibling;
    const allItems = document.querySelectorAll('.faq-item');

    // Close other items
    allItems.forEach(i => {
        if (i !== item) {
            i.classList.remove('active');
            const otherBody = i.querySelector('.faq-body');
            if (otherBody) otherBody.style.maxHeight = null;
        }
    });

    // Toggle current item
    item.classList.toggle('active');
    body.style.maxHeight = item.classList.contains('active') ? body.scrollHeight + "px" : null;
}

// Notifications
function toggleNotif(event) {
    event.stopPropagation();
    const dropdown = document.getElementById("notifDropdown");
    if (dropdown) dropdown.classList.toggle("show");
}

// User Menu
function toggleMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById("dropdownMenu");
    if (menu) menu.classList.toggle("show");
}

// Mobile Nav Menu
function toggleMobileNav(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById("mobileNavMenu");
    if (!menu) return;
    menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}

function closeMobileNav() {
    const menu = document.getElementById("mobileNavMenu");
    if (menu) menu.style.display = 'none';
}



document.addEventListener("DOMContentLoaded", function () {

    // Navbar Scroll Shadow
    const navbar = document.querySelector(".navbar");
    window.addEventListener("scroll", () => {
        if (navbar) {
            window.scrollY > 50 ? navbar.classList.add("scrolled") : navbar.classList.remove("scrolled");
        }
    });

    // Laravel Success Alerts
    if (window.successMessage && window.successMessage.trim() !== "") {
        Swal.fire({
            icon: "success",
            title: "Success",
            text: window.successMessage,
            confirmButtonColor: "#3b82f6"
        });
    }

    // Laravel Error Alerts
    if (window.errorMessage && window.errorMessage.trim() !== "") {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: window.errorMessage,
            confirmButtonColor: "#dc3545"
        });
    }

    // Close dropdowns and mobile menu when clicking anywhere else
    document.addEventListener("click", () => {
        const notif = document.getElementById("notifDropdown");
        const menu = document.getElementById("dropdownMenu");
        const mobileNav = document.getElementById("mobileNavMenu");
        if (notif) notif.classList.remove("show");
        if (menu) menu.classList.remove("show");
        if (mobileNav) mobileNav.style.display = 'none';
    });

    // Prevent dropdowns from closing when clicking inside them
    document.querySelectorAll('.notif-dropdown, .dropdown').forEach(dd => {
        dd.addEventListener('click', (e) => e.stopPropagation());
    });

});

document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('nav a');
    const sections = document.querySelectorAll('section');

    // 1. Highlight on Click
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // 2. Highlight on Scroll (Smart Highlight)
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            // Adjust the '- 100' depending on your navbar height
            if (pageYOffset >= (sectionTop - 100)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('active');
            }
        });
    });
});

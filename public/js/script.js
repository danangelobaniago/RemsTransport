
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
    let autoScrollInterval;

    function startAutoScroll() {
        autoScrollInterval = setInterval(() => {
            const card = document.querySelector('.feedback-item');
            if (!card) return;

            const scrollAmount = card.offsetWidth + 24;

            // If at the end, loop back
            if (slider.scrollLeft + slider.offsetWidth >= slider.scrollWidth - 10) {
                slider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }, 5000); // 5 seconds
    }

    function manualScroll(direction) {
        clearInterval(autoScrollInterval); // Stop auto when user clicks
        const card = document.querySelector('.feedback-item');
        const scrollAmount = card.offsetWidth + 24;

        slider.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });

        startAutoScroll(); // Restart auto scroll
    }

    // Stop scrolling on hover
    slider.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
    slider.addEventListener('mouseleave', startAutoScroll);

    // Initial Start
    window.onload = startAutoScroll;
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

// main.js - Custom JS for LaFlora

document.addEventListener('DOMContentLoaded', function() {
  // Example: Smooth scroll for nav links
  document.querySelectorAll('a.nav-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
      if (this.hash) {
        e.preventDefault();
        document.querySelector(this.hash).scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
});

// Image Slider for About Section
(function() {
  const slider = document.getElementById('about-image-slider');
  if (!slider) return;
  const slides = slider.querySelectorAll('.image-slider-slide');
  const dots = slider.querySelectorAll('.image-slider-dot');
  let current = 0;
  let interval = null;

  function showSlide(idx) {
    slides.forEach((slide, i) => {
      slide.classList.toggle('active', i === idx);
    });
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === idx);
    });
    current = idx;
  }

  function nextSlide() {
    showSlide((current + 1) % slides.length);
  }

  dots.forEach(dot => {
    dot.addEventListener('click', function() {
      showSlide(Number(this.dataset.slide));
      resetInterval();
    });
  });

  function resetInterval() {
    if (interval) clearInterval(interval);
    interval = setInterval(nextSlide, 3500);
  }

  resetInterval();
})();

// Animate About page images on scroll into view
function animateAboutImages() {
  const imgs = document.querySelectorAll('.about-img-animate');
  imgs.forEach(img => {
    const rect = img.getBoundingClientRect();
    if (rect.top < window.innerHeight - 40) {
      img.classList.add('about-img-animate');
    }
  });
}
if (document.querySelector('.about-img-animate')) {
  window.addEventListener('scroll', animateAboutImages);
  window.addEventListener('DOMContentLoaded', animateAboutImages);
}

// About page single image hover effect
(function() {
  const wrapper = document.querySelector('.about-img-single-wrapper');
  if (!wrapper) return;
  wrapper.addEventListener('mouseenter', function() {
    wrapper.classList.add('hovered');
  });
  wrapper.addEventListener('mouseleave', function() {
    wrapper.classList.remove('hovered');
  });
})();

// Contact form validation and success message
(function() {
  const form = document.getElementById('contactForm');
  const success = document.getElementById('contactSuccess');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;
    form.querySelectorAll('.contact-input').forEach(input => {
      if (!input.value.trim()) {
        input.classList.add('is-invalid');
        valid = false;
      } else {
        input.classList.remove('is-invalid');
      }
    });
    if (valid) {
      form.reset();
      if (success) {
        success.classList.remove('d-none');
        setTimeout(() => success.classList.add('d-none'), 3500);
      }
    }
  });
})();

// --- SHOP PAGE LOGIC ---
// All JS product rendering, search, and filter logic removed as requested.

// Wishlist page: Remove item, move to cart, and clear all (UI only, demo)
document.addEventListener('DOMContentLoaded', function() {
  const wishlistGrid = document.getElementById('wishlistGrid');
  const clearBtn = document.getElementById('clearWishlist');
  if (wishlistGrid) {
    wishlistGrid.addEventListener('click', function(e) {
      if (e.target.closest('.delete-wishlist')) {
        const card = e.target.closest('.col-md-6, .col-lg-4, .col-xl-3');
        if (card) card.remove();
      }
      if (e.target.closest('.move-to-cart')) {
        const btn = e.target.closest('.move-to-cart');
        btn.innerHTML = '<i class="fa fa-check me-1"></i> Moved!';
        btn.disabled = true;
        setTimeout(() => {
          const card = btn.closest('.col-md-6, .col-lg-4, .col-xl-3');
          if (card) card.remove();
        }, 900);
      }
    });
  }
  if (clearBtn && wishlistGrid) {
    clearBtn.addEventListener('click', function() {
      wishlistGrid.innerHTML = '<div class="col-12 text-center text-muted py-5">No items in your wishlist.</div>';
    });
  }
});

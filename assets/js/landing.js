// ===========================
// landing.js
// ===========================

// Hover cards (Visión y Misión)
document.querySelectorAll('.hover-card').forEach(card => {
    const img = card.querySelector('img');
    const content = card.querySelector('.card-content');

    card.addEventListener('mouseenter', () => {
        img.style.opacity = '0';
        content.style.opacity = '1';
    });

    card.addEventListener('mouseleave', () => {
        img.style.opacity = '1';
        content.style.opacity = '0';
    });
});

// Hover cards de productos
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.transform = 'scale(1.05)';
        card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.3)';
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = 'scale(1)';
        card.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    });
});

// Animación del slogan al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    const slogan = document.querySelector('.slogan-container');
    if (slogan) {
        slogan.style.opacity = 0;
        slogan.style.transition = 'opacity 1s ease, transform 1s ease';
        slogan.style.transform = 'translateY(20px)';
        setTimeout(() => {
            slogan.style.opacity = 1;
            slogan.style.transform = 'translateY(0)';
        }, 500);
    }
});

// Efecto smooth scroll para enlaces internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

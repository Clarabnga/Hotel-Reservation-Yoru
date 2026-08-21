import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.documentElement.classList.add('motion-ready');

const elements = document.querySelectorAll('main > section, main > header, .room-card, .feature-card, .panel, .account-panel, .business-services article, .site-footer');
elements.forEach((element, index) => {
    element.classList.add('reveal');
    element.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 70}ms`);
});

if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
    elements.forEach(element => element.classList.add('is-visible'));
} else {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }
    }), { threshold: 0.08, rootMargin: '0px 0px -30px' });
    elements.forEach(element => observer.observe(element));
}

requestAnimationFrame(() => document.body.classList.add('page-ready'));

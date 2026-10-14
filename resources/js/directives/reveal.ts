import type { Directive } from 'vue';

interface RevealElement extends HTMLElement {
    __revealObserver?: IntersectionObserver;
}

let revealIndex = 0;

/**
 * Marketing scroll-reveal (source `.rv` → `.rv.in`, `Tallypal Site.html`): fades an element in
 * the first time it enters the viewport, staggered `(index mod 3) × 70ms`. Renders visible
 * immediately outside the browser (SSR) or without `IntersectionObserver` support; CSS in
 * `app.css` (`.reveal`) additionally forces it visible under `prefers-reduced-motion`.
 */
export const vReveal: Directive<RevealElement> = {
    mounted(el) {
        el.classList.add('reveal');

        if (typeof window === 'undefined' || typeof IntersectionObserver === 'undefined') {
            el.classList.add('reveal-in');
            return;
        }

        const delay = (revealIndex++ % 3) * 70;

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) {
                    return;
                }

                window.setTimeout(() => el.classList.add('reveal-in'), delay);
                observer.disconnect();
            },
            { threshold: 0.12 },
        );

        observer.observe(el);
        el.__revealObserver = observer;
    },
    unmounted(el) {
        el.__revealObserver?.disconnect();
    },
};

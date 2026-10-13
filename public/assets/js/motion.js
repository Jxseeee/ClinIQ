(function () {
    'use strict';

    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    }

    function splitWords(element) {
        var words = element.textContent.trim().split(/\s+/);
        element.textContent = '';

        return words.map(function (word, index) {
            var span = document.createElement('span');
            span.textContent = word + (index === words.length - 1 ? '' : ' ');
            element.appendChild(span);
            return span;
        });
    }

    onReady(function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !window.gsap) {
            return;
        }

        var gsap = window.gsap;
        var ScrollTrigger = window.ScrollTrigger;

        if (ScrollTrigger) {
            gsap.registerPlugin(ScrollTrigger);
        }

        if (document.querySelector('.portal-login-shell')) {
            var hasScrollableLogin = document.documentElement.scrollHeight > window.innerHeight + 10;

            gsap.from('.portal-brand-nav', {
                y: -14,
                opacity: 0,
                duration: 0.7,
                ease: 'power2.out'
            });

            gsap.from('.portal-campus-name, .portal-hero-title', {
                y: 32,
                opacity: 0,
                duration: 0.9,
                stagger: 0.1,
                ease: 'power3.out'
            });

            gsap.fromTo('.portal-seal-stage', {
                scale: 0.82,
                opacity: 0.25
            }, {
                scale: 1,
                opacity: 1,
                duration: 1.25,
                ease: 'power3.out'
            });

            gsap.from('.portal-actions-inner > *', {
                x: 24,
                opacity: 0,
                duration: 0.75,
                stagger: 0.07,
                ease: 'power2.out'
            });

            var copy = document.querySelector('.portal-brand-copy');
            if (copy) {
                var words = splitWords(copy);
                gsap.set(words, { opacity: 0.18 });

                var wordReveal = {
                    opacity: 1,
                    stagger: 0.05,
                    ease: 'none'
                };

                if (ScrollTrigger && hasScrollableLogin) {
                    wordReveal.scrollTrigger = {
                        trigger: copy,
                        start: 'top 88%',
                        end: 'bottom 46%',
                        scrub: 0.5
                    };
                } else {
                    wordReveal.duration = 0.8;
                }

                gsap.to(words, wordReveal);
            }

            if (ScrollTrigger && hasScrollableLogin) {
                gsap.to('.portal-seal-stage', {
                    scale: 1.05,
                    opacity: 0.2,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: '.portal-brand-panel',
                        start: 'top top',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            }
        }

        if (document.querySelector('.student-dashboard-main')) {
            gsap.from('.student-dashboard-topbar', {
                y: -12,
                opacity: 0,
                duration: 0.55,
                ease: 'power2.out'
            });

            gsap.from('.student-dashboard-content > *', {
                y: 18,
                opacity: 0,
                duration: 0.65,
                stagger: 0.055,
                ease: 'power2.out'
            });

            if (ScrollTrigger) {
                ScrollTrigger.batch('.form-section:not(.clinic-wizard-step), .clinic-paper-form, .consent-paper-form, .clinic-visit-panel', {
                    start: 'top 92%',
                    once: true,
                    onEnter: function (elements) {
                        gsap.from(elements, {
                            scale: 0.98,
                            opacity: 0.45,
                            y: 18,
                            duration: 0.65,
                            stagger: 0.08,
                            ease: 'power2.out'
                        });
                    }
                });
            }
        }
    });
}());
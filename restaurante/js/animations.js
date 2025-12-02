// Advanced Animations and Effects
class RestaurantAnimations {
    constructor() {
        this.initScrollAnimations();
        this.initHoverEffects();
        this.initParallaxEffects();
        this.initCounterAnimations();
    }

    initScrollAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Stagger children animations
                    const staggerChildren = entry.target.querySelectorAll('.stagger-animate > *');
                    staggerChildren.forEach((child, index) => {
                        child.style.animationDelay = `${index * 0.1}s`;
                    });
                }
            });
        }, observerOptions);

        // Observe all elements with animation classes
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    initHoverEffects() {
        // 3D tilt effect for cards
        document.querySelectorAll('.mesa-card, .orden-card, .producto-card').forEach(card => {
            card.addEventListener('mousemove', this.handleCardTilt);
            card.addEventListener('mouseleave', this.resetCardTilt);
        });

        // Magnetic button effect
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mousemove', this.handleMagneticEffect);
            btn.addEventListener('mouseleave', this.resetMagneticEffect);
        });
    }

    handleCardTilt(e) {
        const card = e.currentTarget;
        const cardRect = card.getBoundingClientRect();
        const centerX = cardRect.left + cardRect.width / 2;
        const centerY = cardRect.top + cardRect.height / 2;
        
        const mouseX = e.clientX - centerX;
        const mouseY = e.clientY - centerY;
        
        const rotateX = (mouseY / cardRect.height) * -10;
        const rotateY = (mouseX / cardRect.width) * 10;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        
        // Add shine effect
        const shine = card.querySelector('.card-shine') || this.createShineElement(card);
        const shineX = (mouseX / cardRect.width) * 100;
        const shineY = (mouseY / cardRect.height) * 100;
        shine.style.background = `radial-gradient(circle at ${shineX}% ${shineY}%, rgba(229,57,53,0.25) 0%, transparent 80%)`;
    }

    createShineElement(card) {
        const shine = document.createElement('div');
        shine.className = 'card-shine';
        shine.style.position = 'absolute';
        shine.style.top = '0';
        shine.style.left = '0';
        shine.style.width = '100%';
        shine.style.height = '100%';
        shine.style.pointerEvents = 'none';
        shine.style.borderRadius = 'inherit';
        shine.style.zIndex = '1';
        card.style.position = 'relative';
        card.appendChild(shine);
        return shine;
    }

    resetCardTilt(e) {
        const card = e.currentTarget;
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
        
        const shine = card.querySelector('.card-shine');
        if (shine) {
            shine.style.background = 'transparent';
        }
    }

    handleMagneticEffect(e) {
        const btn = e.currentTarget;
        const btnRect = btn.getBoundingClientRect();
        const centerX = btnRect.left + btnRect.width / 2;
        const centerY = btnRect.top + btnRect.height / 2;
        
        const mouseX = e.clientX - centerX;
        const mouseY = e.clientY - centerY;
        
        const distance = Math.sqrt(mouseX * mouseX + mouseY * mouseY);
        const maxDistance = 100;
        
        if (distance < maxDistance) {
            const moveX = (mouseX / maxDistance) * 10;
            const moveY = (mouseY / maxDistance) * 10;
            
            btn.style.transform = `translate3d(${moveX}px, ${moveY}px, 0) scale(1.05)`;
        }
    }

    resetMagneticEffect(e) {
        const btn = e.currentTarget;
        btn.style.transform = 'translate3d(0, 0, 0) scale(1)';
    }

    initParallaxEffects() {
        // Simple parallax for background elements
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax');
            
            parallaxElements.forEach(el => {
                const speed = el.dataset.speed || 0.5;
                el.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    }

    initCounterAnimations() {
        // Animate statistics counters
        const observerOptions = {
            threshold: 0.5
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateValue(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-value').forEach(counter => {
            counterObserver.observe(counter);
        });
    }

    animateValue(element) {
        const target = parseInt(element.textContent);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }

    // Particle effect for special interactions
    createParticles(x, y, count = 10) {
        for (let i = 0; i < count; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: fixed;
                width: 6px;
                height: 6px;
                background: var(--primary-gradient);
                border-radius: 50%;
                pointer-events: none;
                z-index: 1000;
                left: ${x}px;
                top: ${y}px;
            `;

            document.body.appendChild(particle);

            const angle = Math.random() * Math.PI * 2;
            const velocity = 2 + Math.random() * 2;
            const vx = Math.cos(angle) * velocity;
            const vy = Math.sin(angle) * velocity;

            let opacity = 1;
            const animate = () => {
                opacity -= 0.02;
                particle.style.opacity = opacity;
                particle.style.transform = `translate(${vx * (1 - opacity) * 50}px, ${vy * (1 - opacity) * 50}px)`;

                if (opacity > 0) {
                    requestAnimationFrame(animate);
                } else {
                    particle.remove();
                }
            };

            animate();
        }
    }
}

// Initialize animations when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.restaurantAnimations = new RestaurantAnimations();
    
    // Add particle effects to buttons
    document.querySelectorAll('.btn-primary, .btn-success').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = rect.left + rect.width / 2;
            const y = rect.top + rect.height / 2;
            window.restaurantAnimations.createParticles(x, y, 8);
        });
    });
});

// Additional CSS for advanced animations
const advancedStyles = `
    .animate-on-scroll {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    .animate-on-scroll.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
    
    .card-shine {
        transition: background 0.3s ease;
    }
    
    .particle {
        transition: transform 0.5s ease-out, opacity 0.5s ease-out;
    }
    
    .magnetic-hover {
        transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    .parallax {
        transition: transform 0.1s ease-out;
    }
`;

// Inject advanced styles
const styleSheet = document.createElement('style');
styleSheet.textContent = advancedStyles;
document.head.appendChild(styleSheet);
document.addEventListener('DOMContentLoaded', () => {

  const navbar = document.getElementById('navbar');

  if (navbar && !navbar.classList.contains('scrolled')) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 80) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }

  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('active');
    });

    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
      });
    });

    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
      }
    });
  }

  const revealElements = document.querySelectorAll('.reveal');
  
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
  });

  revealElements.forEach(el => revealObserver.observe(el));

  const counters = document.querySelectorAll('[data-count]');
  
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute('data-count'));
        animateCounter(el, target);
        counterObserver.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(counter => counterObserver.observe(counter));

  function animateCounter(el, target) {
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = Math.floor(current) + '+';
    }, 16);
  }

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;
      
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        const offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo({
          top: offsetTop,
          behavior: 'smooth'
        });
      }
    });
  });

  const contactForm = document.getElementById('contactForm');
  
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const formData = new FormData(contactForm);
      const data = Object.fromEntries(formData);
      
      if (!data.nombre || !data.email || !data.servicio || !data.mensaje) {
        showNotification('Por favor complete todos los campos obligatorios.', 'error');
        return;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(data.email)) {
        showNotification('Por favor ingrese un email válido.', 'error');
        return;
      }

      const btn = contactForm.querySelector('button[type="submit"]');
      const originalText = btn.textContent;
      btn.textContent = 'Enviando...';
      btn.disabled = true;

      setTimeout(() => {
        showNotification('¡Mensaje enviado con éxito! Nos pondremos en contacto a la brevedad.', 'success');
        contactForm.reset();
        btn.textContent = originalText;
        btn.disabled = false;
      }, 1500);
    });
  }

  function showNotification(message, type) {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
          ${type === 'success' 
            ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>'
            : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'
          }
        </svg>
        <span>${message}</span>
        <button class="notification-close">&times;</button>
      </div>
    `;

    Object.assign(notification.style, {
      position: 'fixed',
      top: '100px',
      right: '20px',
      zIndex: '9999',
      maxWidth: '420px',
      padding: '16px 20px',
      borderRadius: '8px',
      color: '#fff',
      fontSize: '0.95rem',
      boxShadow: '0 8px 30px rgba(0,0,0,0.2)',
      transform: 'translateX(120%)',
      transition: 'transform 0.4s ease',
      background: type === 'success' ? '#2E7D32' : '#C62828'
    });

    const content = notification.querySelector('.notification-content');
    Object.assign(content.style, {
      display: 'flex',
      alignItems: 'center',
      gap: '12px'
    });

    const closeBtn = notification.querySelector('.notification-close');
    Object.assign(closeBtn.style, {
      background: 'none',
      border: 'none',
      color: '#fff',
      fontSize: '1.3rem',
      cursor: 'pointer',
      marginLeft: 'auto',
      padding: '0 4px'
    });

    document.body.appendChild(notification);

    requestAnimationFrame(() => {
      notification.style.transform = 'translateX(0)';
    });

    closeBtn.addEventListener('click', () => {
      notification.style.transform = 'translateX(120%)';
      setTimeout(() => notification.remove(), 400);
    });

    setTimeout(() => {
      if (notification.parentNode) {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => notification.remove(), 400);
      }
    }, 5000);
  }

});

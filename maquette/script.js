/* ============================================
   CERCLE DE CONFIANCE - Shared JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
  // Initialize all modules
  initHamburgerMenu();
  initLoginForm();
  initMemberDropdown();
  initContactForm();
  initCopyButton();
  initRadioOptions();
});

/* ============================================
   Hamburger Menu Toggle
   ============================================ */
function initHamburgerMenu() {
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');
  
  if (!hamburger || !mobileMenu) return;
  
  hamburger.addEventListener('click', function() {
    this.classList.toggle('active');
    mobileMenu.classList.toggle('active');
  });
  
  // Close menu when clicking on a link
  const mobileLinks = mobileMenu.querySelectorAll('a');
  mobileLinks.forEach(link => {
    link.addEventListener('click', function() {
      hamburger.classList.remove('active');
      mobileMenu.classList.remove('active');
    });
  });
  
  // Close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
      hamburger.classList.remove('active');
      mobileMenu.classList.remove('active');
    }
  });
}

/* ============================================
   Login Form Handler
   ============================================ */
function initLoginForm() {
  const loginForm = document.getElementById('loginForm');
  const memberLoginForm = document.getElementById('memberLoginForm');
  
  // Secret code login
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const codeInput = document.getElementById('secretCode');
      
      if (codeInput && codeInput.value.trim() !== '') {
        // Simulate redirect to home page
        window.location.href = 'accueil.html';
      } else {
        showError(codeInput, 'Veuillez entrer le code d\'accès');
      }
    });
  }
  
  // Member login
  if (memberLoginForm) {
    memberLoginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      
      let isValid = true;
      
      // Clear previous errors
      clearErrors(memberLoginForm);
      
      // Validate email
      if (emailInput && !isValidEmail(emailInput.value)) {
        showError(emailInput, 'Veuillez entrer une adresse email valide');
        isValid = false;
      }
      
      // Validate password
      if (passwordInput && passwordInput.value.length < 1) {
        showError(passwordInput, 'Veuillez entrer votre mot de passe');
        isValid = false;
      }
      
      if (isValid) {
        // Simulate successful login (redirect to home in real scenario)
        alert('Connexion réussie ! (Simulation - redirection vers la page d\'accueil)');
        window.location.href = 'accueil.html';
      }
    });
  }
}

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function showError(input, message) {
  input.classList.add('error');
  const errorDiv = document.createElement('div');
  errorDiv.className = 'form-error';
  errorDiv.textContent = message;
  input.parentNode.appendChild(errorDiv);
  
  // Remove error on input change
  input.addEventListener('input', function() {
    input.classList.remove('error');
    const errorEl = input.parentNode.querySelector('.form-error');
    if (errorEl) errorEl.remove();
  });
}

function clearErrors(form) {
  form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
  form.querySelectorAll('.form-error').forEach(el => el.remove());
}

/* ============================================
   Member Dropdown Toggle
   ============================================ */
function initMemberDropdown() {
  const memberRadio = document.getElementById('memberOption');
  const allCircleRadio = document.getElementById('allCircleOption');
  const memberDropdown = document.querySelector('.member-dropdown');
  
  if (!memberRadio || !allCircleRadio || !memberDropdown) return;
  
  function toggleDropdown() {
    if (memberRadio.checked) {
      memberDropdown.classList.add('active');
    } else {
      memberDropdown.classList.remove('active');
    }
  }
  
  memberRadio.addEventListener('change', toggleDropdown);
  allCircleRadio.addEventListener('change', toggleDropdown);
}

/* ============================================
   Radio Options Styling
   ============================================ */
function initRadioOptions() {
  const radioOptions = document.querySelectorAll('.radio-option');
  
  radioOptions.forEach(option => {
    option.addEventListener('click', function() {
      const group = this.closest('.radio-group');
      if (!group) return;
      
      // Remove selected class from all options in group
      group.querySelectorAll('.radio-option').forEach(opt => {
        opt.classList.remove('selected');
      });
      
      // Add selected class to clicked option
      this.classList.add('selected');
      
      // Check the input if exists
      const input = this.querySelector('input[type="radio"]');
      if (input) {
        input.checked = true;
      }
    });
  });
}

/* ============================================
   Contact Form Handler
   ============================================ */
function initContactForm() {
  const contactForm = document.getElementById('contactForm');
  const toggleButtons = document.querySelectorAll('.toggle-switch button');
  const identityFields = document.querySelector('.identity-fields');
  const anonymousInfo = document.querySelector('.anonymous-info');
  
  // Toggle between identified and anonymous
  if (toggleButtons.length > 0) {
    toggleButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        toggleButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const isAnonymous = this.dataset.mode === 'anonymous';
        
        if (identityFields) {
          identityFields.style.display = isAnonymous ? 'none' : 'block';
          // Disable inputs when anonymous
          identityFields.querySelectorAll('input').forEach(input => {
            input.disabled = isAnonymous;
          });
        }
        
        if (anonymousInfo) {
          anonymousInfo.classList.toggle('active', isAnonymous);
        }
      });
    });
  }
  
  // Form submission
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const isAnonymous = document.querySelector('.toggle-switch button.active')?.dataset.mode === 'anonymous';
      
      // Basic validation
      let isValid = true;
      
      if (!isAnonymous) {
        const nom = document.getElementById('nom');
        const email = document.getElementById('courriel');
        
        if (nom && nom.value.trim() === '') {
          showError(nom, 'Veuillez entrer votre nom');
          isValid = false;
        }
        
        if (email && !isValidEmail(email.value)) {
          showError(email, 'Veuillez entrer une adresse email valide');
          isValid = false;
        }
      }
      
      const sujet = document.getElementById('sujet');
      const message = document.getElementById('message');
      
      if (sujet && sujet.value.trim() === '') {
        showError(sujet, 'Veuillez entrer un sujet');
        isValid = false;
      }
      
      if (message && message.value.trim() === '') {
        showError(message, 'Veuillez entrer votre message');
        isValid = false;
      }
      
      if (isValid) {
        // Generate case number
        const caseNumber = generateCaseNumber();
        
        // Show success modal
        showSuccessModal(caseNumber, isAnonymous);
      }
    });
  }
}

/* ============================================
   Case Number Generator
   ============================================ */
function generateCaseNumber() {
  const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const digits = '0123456789';
  
  // Format: 1 letter + 1 digit + 3 letters + 2 digits
  let result = '';
  
  result += letters.charAt(Math.floor(Math.random() * letters.length));
  result += digits.charAt(Math.floor(Math.random() * digits.length));
  result += letters.charAt(Math.floor(Math.random() * letters.length));
  result += letters.charAt(Math.floor(Math.random() * letters.length));
  result += letters.charAt(Math.floor(Math.random() * letters.length));
  result += digits.charAt(Math.floor(Math.random() * digits.length));
  result += digits.charAt(Math.floor(Math.random() * digits.length));
  
  return result;
}

/* ============================================
   Success Modal
   ============================================ */
function showSuccessModal(caseNumber, isAnonymous) {
  const modal = document.getElementById('successModal');
  const caseValue = document.getElementById('caseNumberValue');
  const modalMessage = document.getElementById('modalMessage');
  
  if (modal) {
    // Update case number
    if (caseValue) {
      caseValue.textContent = caseNumber;
      caseValue.dataset.caseNumber = caseNumber;
    }
    
    // Update message based on anonymous mode
    if (modalMessage && isAnonymous) {
      modalMessage.innerHTML = 'Conservez précieusement ce numéro pour suivre l\'avancement de votre dossier sans avoir à vous reconnecter.';
    } else if (modalMessage) {
      modalMessage.innerHTML = 'Nous vous répondrons dans les plus brefs délais à l\'adresse email que vous avez fournie.';
    }
    
    // Show modal
    modal.classList.add('active');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
  }
}

function closeModal() {
  const modal = document.getElementById('successModal');
  
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Reset form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
      contactForm.reset();
      
      // Reset toggle to identified
      const toggleButtons = document.querySelectorAll('.toggle-switch button');
      toggleButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.mode === 'identify') {
          btn.classList.add('active');
        }
      });
      
      // Show identity fields
      const identityFields = document.querySelector('.identity-fields');
      if (identityFields) {
        identityFields.style.display = 'block';
      }
      
      // Hide anonymous info
      const anonymousInfo = document.querySelector('.anonymous-info');
      if (anonymousInfo) {
        anonymousInfo.classList.remove('active');
      }
    }
  }
}

/* ============================================
   Copy Button Handler
   ============================================ */
function initCopyButton() {
  const copyBtn = document.querySelector('.copy-btn');
  
  if (copyBtn) {
    copyBtn.addEventListener('click', function() {
      const caseNumber = document.getElementById('caseNumberValue');
      
      if (caseNumber && caseNumber.dataset.caseNumber) {
        navigator.clipboard.writeText(caseNumber.dataset.caseNumber).then(() => {
          // Update button text temporarily
          const originalText = this.innerHTML;
          this.innerHTML = '<span>✓ Copié !</span>';
          this.classList.add('copied');
          
          setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.remove('copied');
          }, 2000);
        }).catch(() => {
          // Fallback for older browsers
          const textArea = document.createElement('textarea');
          textArea.value = caseNumber.dataset.caseNumber;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
          
          const originalText = this.innerHTML;
          this.innerHTML = '<span>✓ Copié !</span>';
          
          setTimeout(() => {
            this.innerHTML = originalText;
          }, 2000);
        });
      }
    });
  }
}

/* ============================================
   Close Modal on Overlay Click
   ============================================ */
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    closeModal();
  }
  
  // Close modal on Escape key
  if (e.key === 'Escape') {
    closeModal();
  }
});

/* ============================================
   Smooth Scroll for Anchor Links
   ============================================ */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const targetId = this.getAttribute('href');
    
    if (targetId === '#') return;
    
    const target = document.querySelector(targetId);
    
    if (target) {
      e.preventDefault();
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

/* ============================================
   Scroll-triggered Animations (Intersection Observer)
   ============================================ */
function initScrollAnimations() {
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  document.querySelectorAll('.member-card, .feature-card').forEach(el => {
    observer.observe(el);
  });
}

// Initialize scroll animations after a small delay to ensure DOM is ready
setTimeout(initScrollAnimations, 100);

/* ============================================
   Utility Functions
   ============================================ */

// Debounce function for performance
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Check if element is in viewport
function isInViewport(element) {
  const rect = element.getBoundingClientRect();
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}
function toggleFAQ(element) {
  // Toggle active class on the clicked question
  element.classList.toggle('active');
  
  // Close all other FAQs
  document.querySelectorAll('.faq-question').forEach(question => {
    if (question !== element) {
      question.classList.remove('active');
    }
  });
}

// Form submission handling
document.addEventListener('DOMContentLoaded', function() {
  // Get the contact form if it exists
  const contactForm = document.querySelector('.contact-form');
  
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form values
      const name = document.getElementById('name').value;
      const email = document.getElementById('email').value;
      const subject = document.getElementById('subject').value;
      const message = document.getElementById('message').value;
      
      // Simple form validation
      if (!name || !email || !subject || !message) {
        alert('Please fill in all fields.');
        return;
      }
      
      // Here you would typically send the data to a server
      // For this example, we'll just show a success message
      alert(`Thank you ${name}! Your message has been sent. We'll get back to you soon.`);
      
      // Clear form fields
      contactForm.reset();
    });
  }

  // Set current year in footer
  const yearElement = document.getElementById('current-year');
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }
  
  // Fade-in animation for elements
  const fadeElements = document.querySelectorAll('.fade-in');
  
  const fadeInObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        fadeInObserver.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1
  });
  
  fadeElements.forEach(element => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(10px)';
    element.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
    fadeInObserver.observe(element);
  });
});
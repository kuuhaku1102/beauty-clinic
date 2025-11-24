/**
 * Featured Clinics Accordion
 */
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.bd-accordion-toggle');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.bd-accordion-icon');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle aria-expanded
            this.setAttribute('aria-expanded', !isExpanded);
            
            // Toggle content visibility
            if (isExpanded) {
                content.style.maxHeight = null;
                content.classList.remove('bd-accordion-open');
                icon.textContent = '▼';
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.classList.add('bd-accordion-open');
                icon.textContent = '▲';
            }
        });
    });
});

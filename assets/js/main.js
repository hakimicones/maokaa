// assets/js/main.js
// VEP Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeFormValidation();
    initializeNav();
    initializeAlphabetFilter();
    initializeCategoryFilter();
});

/**
 * Initialize scroll animations
 */
function initializeAnimations() {
    // Intersection Observer for fade-in animations
    const options = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, options);
    
    // Observe all sections and cards
    document.querySelectorAll('section, .card, .product-card').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Initialize navbar active state
 */
function initializeNav() {
    const currentUrl = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        if (link.href.includes(currentUrl) || (currentUrl === '/' && link.href.endsWith('/'))) {
            link.classList.add('active');
        }
    });
}

/**
 * Alphabet filter for products
 */
function initializeAlphabetFilter() {
    const letters = document.querySelectorAll('[data-letter]');
    letters.forEach(letter => {
        letter.addEventListener('click', function() {
            const selectedLetter = this.getAttribute('data-letter');
            filterByLetter(selectedLetter);
        });
    });
}

function filterByLetter(letter) {
    const products = document.querySelectorAll('[data-product-letter]');
    products.forEach(product => {
        const productLetter = product.getAttribute('data-product-letter');
        if (productLetter === letter || letter === '#') {
            product.style.display = 'block';
            setTimeout(() => product.classList.add('fade-in'), 10);
        } else {
            product.style.display = 'none';
        }
    });
}

/**
 * Category filter for products
 */
function initializeCategoryFilter() {
    const categories = document.querySelectorAll('[data-category]');
    categories.forEach(category => {
        category.addEventListener('click', function() {
            const selectedCategory = this.getAttribute('data-category');
            filterByCategory(selectedCategory);
        });
    });
}

function filterByCategory(category) {
    const products = document.querySelectorAll('[data-product-category]');
    products.forEach(product => {
        const productCategory = product.getAttribute('data-product-category');
        if (productCategory === category || category === 'all') {
            product.style.display = 'block';
            setTimeout(() => product.classList.add('fade-in'), 10);
        } else {
            product.style.display = 'none';
        }
    });
}

/**
 * Search products dynamically
 */
function searchProducts(query) {
    const products = document.querySelectorAll('[data-product-name]');
    const lowerQuery = query.toLowerCase();
    
    products.forEach(product => {
        const name = product.getAttribute('data-product-name').toLowerCase();
        const description = product.getAttribute('data-product-description').toLowerCase();
        
        if (name.includes(lowerQuery) || description.includes(lowerQuery)) {
            product.style.display = 'block';
            product.classList.add('fade-in');
        } else {
            product.style.display = 'none';
        }
    });
}

/**
 * Download file with tracking
 */
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename || url.split('/').pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastHTML = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.querySelector('.toast-container') || document.body;
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = toastContainer.querySelector('.toast:last-child');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

/**
 * Smooth scroll to element
 */
function smoothScroll(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copié dans le presse-papiers!', 'success');
    }).catch(() => {
        showToast('Erreur lors de la copie', 'error');
    });
}

// Export functions for use in global scope
window.VEP = {
    searchProducts,
    downloadFile,
    showToast,
    smoothScroll,
    copyToClipboard,
    filterByLetter,
    filterByCategory
};



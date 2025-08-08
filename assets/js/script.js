// /assets/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }

    // Auto generate slug from title in post form
    const postTitleInput = document.querySelector('form.post-form #judul');
    const postSlugInput = document.querySelector('form.post-form #slug');
    let userModifiedSlug = false;

    if (postSlugInput) {
        // Track if user has manually changed the slug
        postSlugInput.addEventListener('change', () => {
            if (postSlugInput.value !== '') {
                userModifiedSlug = true;
            }
        });
    }

    if (postTitleInput && postSlugInput) {
        postTitleInput.addEventListener('keyup', () => {
            // Only auto-generate if user hasn't touched the slug field
            if (!userModifiedSlug) {
                postSlugInput.value = createSlug(postTitleInput.value);
            }
        });
    }

    function createSlug(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }
});

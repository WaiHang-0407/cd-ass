(function(){
    // Centralized password visibility toggle.
    // Works on any element with class `toggle` inside a `.password-wrapper`.
    // The script is safe to include on all pages.
    const toggles = document.querySelectorAll('.password-wrapper .toggle');
    if (!toggles || toggles.length === 0) return;

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const wrapper = this.closest('.password-wrapper');
            if (!wrapper) return;
            // Prefer the input inside wrapper (first input element)
            const input = wrapper.querySelector('input');
            if (!input) return;

            const currentType = input.getAttribute('type');
            if (currentType === 'password') {
                input.setAttribute('type', 'text');
                this.textContent = '🙈';
            } else {
                input.setAttribute('type', 'password');
                this.textContent = '👁';
            }
        });
    });
})();

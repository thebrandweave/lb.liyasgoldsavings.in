<!-- Optimized loader.php -->
<div class="loader-overlay" id="globalLoader">
    <div class="simple-spinner"></div>
    <div class="loading-text">Processing...</div>
</div>

<style>
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.9);
    z-index: 999999;
    transition: opacity 0.15s ease, visibility 0.15s ease;
}
.loader-overlay.hide {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}
.simple-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #000000;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}
.loading-text {
    color: #000000;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 500;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
    (function() {
        const loader = document.getElementById('globalLoader');
        if (!loader) return;

        function hideLoader() {
            loader.classList.add('hide');
        }

        // Hide as soon as DOM is ready or if already ready
        if (document.readyState === 'interactive' || document.readyState === 'complete') {
            hideLoader();
        } else {
            document.addEventListener('DOMContentLoaded', hideLoader);
        }

        // Fallbacks: window load and pageshow (back/forward cache)
        window.addEventListener('load', hideLoader);
        window.addEventListener('pageshow', hideLoader);

        // Safety fallback: never keep user waiting more than 250ms
        setTimeout(hideLoader, 250);

        // Show loader before navigating to another page (ignore new-tab, anchors, downloads, scripts)
        document.querySelectorAll('a[href]:not([target])').forEach(link => {
            link.addEventListener('click', function (e) {
                if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('tel:') && !href.startsWith('mailto:') && !this.hasAttribute('download')) {
                    loader.classList.remove('hide');
                    setTimeout(hideLoader, 3000);
                }
            });
        });

    // Optimized form submission with better feedback
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Show loader immediately
            document.getElementById('globalLoader').classList.remove('hide');
            
            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
            
            // Add form validation feedback
            const requiredFields = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#c98200';
                    isValid = false;
                } else {
                    field.style.borderColor = '#000000';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                document.getElementById('globalLoader').classList.add('hide');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Register';
                }
                return false;
            }
        });
    });

    // Optional: also trigger loader for manual navigation
    window.addEventListener('beforeunload', () => {
        document.getElementById('globalLoader').classList.remove('hide');
    });
    })();
</script>

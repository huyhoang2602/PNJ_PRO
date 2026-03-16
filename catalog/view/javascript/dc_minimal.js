/* DC Minimal Theme Custom Scripts */

$(document).ready(function() {
    // 1. Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // 2. Add to cart animation (optional - if you want a flying cart effect)
    $(document).on('click', '.btn-cart', function(e) {
        e.preventDefault();
        // Custom animation logic going to cart icon can go here
    });

    // 3. Handle mobile menu toggles if header.twig has specific dc- classes
    $('.dc-mobile-menu-toggle').on('click', function() {
        $('.dc-mobile-menu').toggleClass('active');
    });

    // 4. Any other global observers for infinite scroll or lazy loading
    if ('IntersectionObserver' in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove("lazy");
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        document.querySelectorAll("img.lazy").forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }
});

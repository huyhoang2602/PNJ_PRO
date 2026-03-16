/* DC Minimal Theme Custom Scripts */

$(document).ready(function() {
    // 1. Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // 2. Prevent card click when clicking action buttons
    $(document).on('click', '.dc-btn-action', function(e) {
        e.stopPropagation();
    });

    // 3. Define global wishlist/compare objects
    window.wishlist = {
        add: function(product_id) {
            $.ajax({
                url: 'index.php?route=account/wishlist.add',
                type: 'post',
                data: 'product_id=' + product_id,
                dataType: 'json',
                success: function(json) {
                    $('.alert-dismissible').remove();
                    if (json['success']) {
                        showModalMessage('success', json['success']);
                    }
                }
            });
        }
    };

    window.compare = {
        add: function(product_id) {
            $.ajax({
                url: 'index.php?route=product/compare.add',
                type: 'post',
                data: 'product_id=' + product_id,
                dataType: 'json',
                success: function(json) {
                    $('.alert-dismissible').remove();
                    if (json['success']) {
                        showModalMessage('success', json['success']);
                    }
                }
            });
        }
    };

    // 4. Handle mobile menu toggles if header.twig has specific dc- classes
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

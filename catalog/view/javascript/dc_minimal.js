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

/**
 * Redefine global showModalMessage to use top-right toasts instead of centered modals
 */
window.showModalMessage = function(type, message) {
    const icon = type === 'success' ? '<i class="fa-solid fa-circle-check"></i>' : 
                 type === 'danger' ? '<i class="fa-solid fa-circle-xmark"></i>' : 
                 '<i class="fa-solid fa-info-circle"></i>';
    
    const alertClass = type === 'success' ? 'bg-success' : 
                       type === 'danger' ? 'bg-danger' : 
                       'bg-info';
    
    const html = `
        <div class="alert alert-dismissible show ${alertClass} text-white border-0 mb-2 shadow-lg" role="alert" style="border-radius: 12px; min-width: 320px; max-width: 450px; animation: slideInRight 0.3s ease-out; display: flex; align-items: center; padding: 1rem 1.5rem;">
            <span class="fs-3 me-3">${icon}</span>
            <div class="fw-600 line-clamp-2 pr-4">${message}</div>
            <button type="button" class="btn-close btn-close-white position-absolute end-0 top-50 translate-middle-y me-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    if (!$('#alert').length) {
        $('body').append('<div id="alert" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
    }
    
    $('#alert').prepend(html);
    
    // Auto-remove after 5 seconds
    const $newToast = $('#alert .toast:first');
    setTimeout(() => {
        $newToast.fadeOut(500, function() { $(this).remove(); });
    }, 5000);
};

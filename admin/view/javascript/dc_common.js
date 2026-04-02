(function ($) {
    $.fn.dc_autocomplete = function (option) {
        return this.each(function () {
            var element = this;
            var $dropdown = $('#' + $(element).attr('data-oc-target'));

            this.timer = null;
            this.items = [];
            this.page = 1;
            this.is_loading = false;
            this.stop_loading = false;

            $.extend(this, option);

            // Focus in
            $(element).on('focusin', function () {
                // If the dropdown is already showing and has items, don't restart?
                // Actually, standard behavior is to refresh.
                element.page = 1;
                element.stop_loading = false;
                element.request();
            });

            // Focus out
            $(element).on('focusout', function (e) {
                if (!e.relatedTarget || !$(e.relatedTarget).hasClass('dropdown-item')) {
                    this.timer = setTimeout(function (object) {
                        object.removeClass('show');
                    }, 200, $dropdown);
                }
            });

            // Input
            $(element).on('input', function (e) {
                element.page = 1;
                element.stop_loading = false;
                element.request();
            });

            // Click
            $dropdown.on('click', 'a', function (e) {
                e.preventDefault();
                var value = $(this).attr('href');
                if (element.items[value] !== undefined) {
                    element.select(element.items[value]);
                    $dropdown.removeClass('show');
                }
            });

            // Scroll for infinite loading
            $dropdown.on('scroll', function () {
                if (element.is_loading || element.stop_loading) return;
                
                var scrollTop = $(this).scrollTop();
                var scrollHeight = $(this)[0].scrollHeight;
                var height = $(this).height();

                // If scrolled to 90% of the bottom
                if (scrollTop + height >= scrollHeight * 0.9) {
                    element.page++;
                    element.request(true);
                }
            });

            // Request
            this.request = function (append) {
                clearTimeout(this.timer);
                
                if (!append) {
                    $('#autocomplete-loading').remove();
                    $dropdown.find('li').remove();
                    this.items = [];
                    $dropdown.prepend('<li id="autocomplete-loading"><span class="dropdown-item text-center disabled"><i class="fa-solid fa-circle-notch fa-spin"></i></span></li>');
                    $dropdown.addClass('show');
                } else {
                    $dropdown.append('<li id="autocomplete-loading-more"><span class="dropdown-item text-center disabled"><i class="fa-solid fa-circle-notch fa-spin"></i></span></li>');
                }

                this.is_loading = true;

                this.timer = setTimeout(function (object) {
                    var filter_name = $(object).val();
                    object.source(filter_name, object.page, $.proxy(object.response, object, append));
                }, 150, this);
            }

            // Response
            this.response = function (append, json) {
                $('#autocomplete-loading, #autocomplete-loading-more').remove();
                this.is_loading = false;

                if (!json || !json.length) {
                    if (!append) $dropdown.removeClass('show');
                    this.stop_loading = true;
                    return;
                }
                
                // If we got fewer than 10 results, we can stop loading more
                if (json.length < 10) {
                    this.stop_loading = true;
                }

                var html = '';
                for (var i = 0; i < json.length; i++) {
                    this.items[json[i]['product_id']] = {
                        label: json[i]['name'],
                        value: json[i]['product_id']
                    };
                    html += '<li><a href="' + json[i]['product_id'] + '" class="dropdown-item">' + json[i]['name'] + '</a></li>';
                }

                if (append) {
                    $dropdown.append(html);
                } else {
                    $dropdown.html(html);
                    $dropdown.scrollTop(0);
                }
            }
        });
    }
})(jQuery);

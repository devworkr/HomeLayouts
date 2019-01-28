/*
 * 
 * IS home Layout Admin js
 * 
 * @since 1.0.0
 * 
 */

(function ($) {
    var custom_uploader, timer, delaytime;
    var IsLayout = {
        settings: {
            loader: '<div class="loader loadersmall"></div>',
            spinnerInline: '<i class="fa fa-spinner fa-spin wpforms-loading-inline"></i>'
        },
        initilaize: function () {
            delaytime = 500;
            $(document).ready(function () {
                IsLayout.onInitMethods();
                IsLayout.HomeLayoutSort();
                IsLayout.categoryLayoutSort();
                IsLayout.postSortable();
            });
            $(document).click(function (e) {
                if (e.target.parentNode.id == '__search_wrap' || e.target.parentNode.id == '__post_search_result') {

                } else {
                    $("#__post_search_result").slideUp();
                }
            });
        },
        onInitMethods: function () {
            $('#is_layout_category').on('change', function () {
                IsLayout.getCategoryPosts($(this).val());
            });
            $('#is_layout_parent_category').on('change', function () {
                IsLayout.getChildCategories($(this).val());
            });
            
            $(document).on('click', '.layout_single_post', function () {
                IsLayout.selectPost($(this));
            });
            $(".layout_icon_button").click(function (e) {
                IsLayout.layout_image_uploader(e);
            });
            $(".layout_remove").click(function (e) {
                IsLayout.layout_remove_image(e);
            });
            $('#layout_post_search').on('keyup', function (e) {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    return IsLayout.search_layout_posts(e);
                }, delaytime);
            });
            $(document).on('click', '.post_search_result .__post', function () {
                var selector = $(this);
                IsLayout.select_from_search(selector);
            });
        },
        select_from_search: function (selector) {
            var post_id = selector.data('id');
            if ($('#is_layout_admin_posts #post_' + post_id).length) {
                $('#is_layout_admin_posts #post_' + post_id).trigger('click');
            } else {
                this.makeCall(ajaxurl, {post_id: selector.data('id'), action: 'select_from_search'}, function (response) {
                    if (response.status == 'success') {
                        $(response.content).insertAfter($('#is_layout_admin_posts .selected').last());
                    } else {
                        alert(response.message);
                    }
                });
            }
        },
        search_layout_posts: function (e) {
            var field = $('#layout_post_search');
            var category = $('#is_layout_category').val();
            var selected = [];
            $('.post_selector:checked').each(function () {
                selected.push(this.value);
            });
            if (!category) {
                alert('please select category first');
                return false;
            }
            field.addClass('input_loading');
            this.makeCall(ajaxurl, {value: field.val(), action: 'search_layout_posts', category: category, selected: selected}, function (response) {
                if (response.status == 'success') {
                    $('.post_search_result').html(response.content).slideDown();
                } else {
                    $('.post_search_result').html(response.message).slideDown();
                }
                field.removeClass('input_loading');
            });
        },
        selectPost: function (elem) {
            if (elem.hasClass('selected') && elem.find('.post_selector').prop('checked')) {
                elem.removeClass('selected');
                elem.find('.post_selector').prop('checked', false);
            } else {
                elem.addClass('selected');
                elem.find('.post_selector').prop('checked', true);
            }
        },
        getChildCategories : function(category_id) {
            this.makeCall(ajaxurl, {category: category_id, action: 'get_parent_category'}, function (response) {
                if (response.status == 'success') {
                    $('#is_layout_category').html(response.content);
                } else {
                    alert(response.message);
                }
            });
        },
        getCategoryPosts: function (category_id) {
            $('#IS_Layout_posts').html(this.settings.loader);
            this.makeCall(ajaxurl, {category: category_id, action: 'get_category_posts'}, function (response) {
                if (response.status == 'success') {
                    $('#IS_Layout_posts').html(response.content);
                    IsLayout.postSortable();
                } else {
                    alert(response.message);
                }
            });
        },
        HomeLayoutSort: function () {
            $('.post-type-home_layouts #the-list').sortable({
                items: 'tr',
                axis: 'y',
                delay: 100,
                opacity: 0.75,
                'update': function (e, ui) {
                    $.post(ajaxurl, {
                        action: 'update_layout_order',
                        order: $('#the-list').sortable('serialize'),
                    });
                }
            });
        },
        categoryLayoutSort: function () {
            $('.post-type-categories_layouts #the-list').sortable({
                items: 'tr.filter_by_category',
                axis: 'y',
                delay: 100,
                opacity: 0.75,
                'update': function (e, ui) {
                    $.post(ajaxurl, {
                        action: 'update_layout_order',
                        order: $('#the-list').sortable('serialize'),
                    });
                }
            });
        },
        postSortable: function () {
            $('#is_layout_admin_posts').sortable({
                items: '.layout_single_post',
                axis: 'x',
                delay: 100,
                opacity: 0.75,
                'update': function (e, ui) {
                    /*$.post( ajaxurl, {
                     action: 'update_layout_posts_order',
                     order: $('#the-list').sortable('serialize'),
                     });*/
                }
            });
        },
        layout_image_uploader: function (e) {
            e.preventDefault();
            size = e.target.id;

            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }

            title = 'Choose Image';
            var term_name = $('input[name="name"]').attr('value');
            if (term_name != 'undefined' && term_name != '')
                title = title + ' for ' + term_name;

            title = title + ' (' + size + ')';


            console.log(term_name);
            //Extend the wp.media object
            custom_uploader = wp.media({
                title: title,
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });

            //When a file is selected, grab the URL and set it as the text field's value
            custom_uploader.on('select', function () {
                attachment = custom_uploader.state().get('selection').first().toJSON();


                var attach_id = attachment.id;
                var data = {
                    action: 'layout_new_icon',
                    img_url: attachment.url,
                    attach_id: attach_id,
                    size: 40
                };

                IsLayout.makeCall(ajaxurl, data, function (response) {
                    $('#layout_icon_' + size).val(attach_id);
                    $('#layout_remove').css('display', '');
                    $('.layout_remove').css('display', '');
                    $('#layout_preview_' + size).html('<img src=' + response.newimg[0] + '>');

                });


            });

            //Open the uploader dialog
            custom_uploader.open();
        },
        layout_remove_image: function (e) {
            size = e.target.id;
            $('#layout_icon_img').val(-1);
            $('#layout_remove').css('display', 'none');
            $('.layout_remove').css('display', 'none');
            $('#layout_preview_img').html('');
        },
        makeCall: function (url, data, callback) {
            $.ajax({
                url: url, // server url
                type: 'POST', //POST or GET 
                data: data, // data to send in ajax format or querystring format
                datatype: 'json',
                async: true,
                crossDomain: true,
                beforeSend: function (xhr) {
                },
                success: function (data) {
                    callback(data); // return data in callback
                },

                complete: function () {
                    $('.loader').remove();
                },

                error: function (xhr, status, error) {
                    $('.loader').remove();
                    //alert(error);
                    //console.log(JSON.parse(xhr.responseText)); // error occur 
                }

            });
        }
    };
    IsLayout.initilaize();
})(jQuery);
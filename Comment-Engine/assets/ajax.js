jQuery(function ($) {

    // 1. Toast Notification (5 Seconds)
    const toast = (msg, type = 'success') => {
        let emoji = type === 'pending' ? '⏳' : '';
        const t = $('<div class="wp-toast ' + type + '">' + emoji + ' ' + msg + '</div>').appendTo('body');
        setTimeout(() => t.addClass('show'), 50);
        setTimeout(() => t.remove(), 5000);
    };

    // 2. Submit Comment
    $(document).on('click', '.comment-form input[type=submit]', function (e) {
        e.preventDefault();
        
        const form = $(this).closest('form'); // Support multiple forms logic
        const btn = $(this);
        
        btn.prop('disabled', true).css('opacity', '0.7');

        $.post(WPComment.ajax, {
            action: 'wp_comment_submit',
            nonce: WPComment.nonce,
            post: WPComment.post,
            author: form.find('[name=author]').val(),
            email: form.find('[name=email]').val(), // Can be undefined
            comment: form.find('[name=comment]').val(),
            parent: form.find('[name=comment_parent]').val()
        }, res => {
            btn.prop('disabled', false).css('opacity', '1');

            if (res.success) {
                // Show message
                toast(res.data.msg, res.data.status);
                
                form[0].reset();
                resetReplyForm(); // Move form back

                // If approved immediately, append it
                if (res.data.status === 'approved') {
                    if (res.data.parent > 0) {
                        // Append as reply (nested)
                        let parentLi = $('#comment-' + res.data.parent).closest('li');
                        if (parentLi.find('.children').length === 0) {
                            parentLi.append('<ul class="children"></ul>');
                        }
                        parentLi.find('.children').first().append(res.data.html);
                    } else {
                        // New Thread
                        $('.comment-list').append(res.data.html);
                    }
                }
            } else {
                toast(res.data || 'خطا در برقراری ارتباط', 'error');
            }
        });
    });

    // 3. Reply System (Move Form)
    const originalFormParent = $('.comment-respond').parent(); // Where form lives initially

    $(document).on('click', '.comment-reply-link', function (e) {
        e.preventDefault();
        const commentId = $(this).data('commentid');
        const targetComment = $('#comment-' + commentId).closest('article, .comment-body'); // Adjust based on theme structure

        // Move the form container (.comment-respond) after the comment body
        $('.comment-respond').insertAfter(targetComment).hide().slideDown();
        
        // Set Parent ID
        $('[name=comment_parent]').val(commentId);
        
        // Show Cancel Button
        $('.wp-ce-cancel-reply').show();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('.comment-respond').offset().top - 200
        }, 500);
    });

    // 4. Cancel Reply
    $('.wp-ce-cancel-reply').on('click', resetReplyForm);

    function resetReplyForm() {
        $('.comment-respond').slideUp(function(){
            $(this).appendTo(originalFormParent).slideDown();
        });
        $('[name=comment_parent]').val(0);
        $('.wp-ce-cancel-reply').hide();
    }

    // 5. Like / Dislike
    $(document).on('click', '.wp-btn-like, .wp-btn-dislike', function () {
        const el = $(this);
        const id = el.data('id');
        const type = el.data('type');

        // Check Local Cookie
        if (getCookie('wp_ce_voted_' + id)) {
            toast('شما قبلاً به این دیدگاه رای داده اید !', 'error');
            return;
        }

        $.post(WPComment.ajax, {
            action: 'wp_comment_like',
            nonce: WPComment.nonce,
            id: id,
            type: type
        }, res => {
            if (res.success) {
                el.find('small').text(res.data);
                setCookie('wp_ce_voted_' + id, '1', 365); // Save cookie for 1 year
                el.addClass('active');
            } else {
                toast(res.data, 'error');
            }
        });
    });

    // Helpers
    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
});

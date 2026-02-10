<?php
/*
Plugin Name:        WP Comment Engine
Plugin URI:         https://github.com/im-JvD/Comment-Engine
Description:        افزونه پیشرفته ارسال دیدگاه (AJAX) با قابلیت شخصی‌سازی کامل استایل و تنظیمات مدیریتی.
Version:            0.0.9
Author:             محمد جواد کریمی
Author URI:         https://mohamadjavadkarimi.ir/
*/

if (!defined('ABSPATH')) exit;

/* ==========================================================================
   1. SETTINGS & MENU (پنل تنظیمات)
   ========================================================================== */

add_action('admin_menu', function () {
    add_submenu_page(
        'edit-comments.php',
        'تنظیمات انجین دیدگاه',
        'تنظیمات انجین',
        'manage_options',
        'wp-comment-engine-settings',
        'wp_ce_render_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('wp_ce_options_group', 'wp_ce_settings');
});

function wp_ce_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>⚙️ تنظیمات انجین دیدگاه</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_ce_options_group');
            $options = get_option('wp_ce_settings');
            
            // Default Values
            $defaults = [
                'btn_bg_1' => '#5b5bff', 'btn_bg_2' => '#6a8dff', 'btn_text' => '#ffffff', 'btn_radius' => '14', 'btn_shadow' => 'rgba(91,91,255,0.45)',
                'input_bg' => '#f9fafb', 'input_text' => '#333', 'input_radius' => '14', 'input_ph' => '#999',
                'card_bg' => '#ffffff', 'card_radius' => '16', 'author_color' => '#000', 'author_size' => '14',
                'text_color' => '#444', 'text_size' => '14', 'avatar_size' => '50', 'avatar_radius' => '50',
                'date_color' => '#777', 'date_size' => '12', 'icon_color' => '#5b5bff', 'icon_size' => '16',
                'approval_mode' => 'auto', // auto (wp settings) | manual | instant
                'auth_mode' => 'all', // all | logged_in
                'email_field' => 'off', // on | off (if off, fake email used)
            ];
            $opt = wp_parse_args($options, $defaults);
            ?>
            
            <hr>
            <h3>۶.۱ تنظیمات دکمه ارسال</h3>
            <table class="form-table">
                <tr><th>رنگ پس‌زمینه (گرادینت)</th><td><input type="color" name="wp_ce_settings[btn_bg_1]" value="<?php echo $opt['btn_bg_1']; ?>"> تا <input type="color" name="wp_ce_settings[btn_bg_2]" value="<?php echo $opt['btn_bg_2']; ?>"></td></tr>
                <tr><th>رنگ متن</th><td><input type="color" name="wp_ce_settings[btn_text]" value="<?php echo $opt['btn_text']; ?>"></td></tr>
                <tr><th>شعاع حاشیه (px)</th><td><input type="number" name="wp_ce_settings[btn_radius]" value="<?php echo $opt['btn_radius']; ?>"></td></tr>
            </table>

            <hr>
            <h3>۶.۲ تنظیمات فیلدها</h3>
            <table class="form-table">
                <tr><th>رنگ پس‌زمینه</th><td><input type="color" name="wp_ce_settings[input_bg]" value="<?php echo $opt['input_bg']; ?>"></td></tr>
                <tr><th>رنگ متن تایپی</th><td><input type="color" name="wp_ce_settings[input_text]" value="<?php echo $opt['input_text']; ?>"></td></tr>
                <tr><th>رنگ متن نگهدارنده (Placeholder)</th><td><input type="color" name="wp_ce_settings[input_ph]" value="<?php echo $opt['input_ph']; ?>"></td></tr>
                <tr><th>شعاع حاشیه (px)</th><td><input type="number" name="wp_ce_settings[input_radius]" value="<?php echo $opt['input_radius']; ?>"></td></tr>
            </table>

            <hr>
            <h3>۶.۳ استایل کارت دیدگاه</h3>
            <table class="form-table">
                <tr><th>رنگ پس‌زمینه کارت</th><td><input type="color" name="wp_ce_settings[card_bg]" value="<?php echo $opt['card_bg']; ?>"></td></tr>
                <tr><th>شعاع حاشیه کارت (px)</th><td><input type="number" name="wp_ce_settings[card_radius]" value="<?php echo $opt['card_radius']; ?>"></td></tr>
                <tr><th>رنگ نام نویسنده</th><td><input type="color" name="wp_ce_settings[author_color]" value="<?php echo $opt['author_color']; ?>"> | سایز: <input type="number" name="wp_ce_settings[author_size]" value="<?php echo $opt['author_size']; ?>">px</td></tr>
                <tr><th>رنگ متن دیدگاه</th><td><input type="color" name="wp_ce_settings[text_color]" value="<?php echo $opt['text_color']; ?>"> | سایز: <input type="number" name="wp_ce_settings[text_size]" value="<?php echo $opt['text_size']; ?>">px</td></tr>
                <tr><th>سایز آواتار (px)</th><td><input type="number" name="wp_ce_settings[avatar_size]" value="<?php echo $opt['avatar_size']; ?>"> | شعاع (۵۰٪ گرد): <input type="number" name="wp_ce_settings[avatar_radius]" value="<?php echo $opt['avatar_radius']; ?>">%</td></tr>
                <tr><th>رنگ و سایز آیکون لایک/دیس‌لایک</th><td><input type="color" name="wp_ce_settings[icon_color]" value="<?php echo $opt['icon_color']; ?>"> | <input type="number" name="wp_ce_settings[icon_size]" value="<?php echo $opt['icon_size']; ?>">px</td></tr>
            </table>

            <hr>
            <h3>۶.۴ تنظیمات منطقی</h3>
            <table class="form-table">
                <tr>
                    <th>نحوه تایید دیدگاه</th>
                    <td>
                        <select name="wp_ce_settings[approval_mode]">
                            <option value="auto" <?php selected($opt['approval_mode'], 'auto'); ?>>طبق تنظیمات وردپرس (پیش‌فرض)</option>
                            <option value="instant" <?php selected($opt['approval_mode'], 'instant'); ?>>انتشار فوری (بدون بررسی)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>دسترسی ارسال</th>
                    <td>
                        <select name="wp_ce_settings[auth_mode]">
                            <option value="all" <?php selected($opt['auth_mode'], 'all'); ?>>همه (مهمان و عضو)</option>
                            <option value="logged_in" <?php selected($opt['auth_mode'], 'logged_in'); ?>>فقط اعضا</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>فیلد ایمیل</th>
                    <td>
                        <select name="wp_ce_settings[email_field]">
                            <option value="off" <?php selected($opt['email_field'], 'off'); ?>>غیرفعال (مخفی)</option>
                            <option value="on" <?php selected($opt['email_field'], 'on'); ?>>فعال (اجباری)</option>
                        </select>
                        <p class="description">اگر روی "فعال" باشد، کاربر باید ایمیل وارد کند. اگر "غیرفعال"، ایمیل فیک استفاده می‌شود.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


/* ==========================================================================
   2. DYNAMIC CSS (تزریق استایل‌های تنظیمات)
   ========================================================================== */
add_action('wp_head', function() {
    $opt = get_option('wp_ce_settings');
    if(!$opt) return;
    ?>
    <style>
        /* Button */
        .comment-form input[type=submit] {
            background: linear-gradient(135deg, <?php echo $opt['btn_bg_1'] ?? '#5b5bff'; ?>, <?php echo $opt['btn_bg_2'] ?? '#6a8dff'; ?>) !important;
            color: <?php echo $opt['btn_text'] ?? '#fff'; ?> !important;
            border-radius: <?php echo $opt['btn_radius'] ?? '14'; ?>px !important;
        }
        /* Inputs */
        .comment-form input:not([type=submit]), .comment-form textarea {
            background: <?php echo $opt['input_bg'] ?? '#f9fafb'; ?> !important;
            color: <?php echo $opt['input_text'] ?? '#333'; ?> !important;
            border-radius: <?php echo $opt['input_radius'] ?? '14'; ?>px !important;
        }
        .comment-form input::placeholder, .comment-form textarea::placeholder {
            color: <?php echo $opt['input_ph'] ?? '#999'; ?> !important;
        }
        /* Card */
        .comment {
            background: <?php echo $opt['card_bg'] ?? '#fff'; ?> !important;
            border-radius: <?php echo $opt['card_radius'] ?? '16'; ?>px !important;
        }
        .comment-author .fn {
            color: <?php echo $opt['author_color'] ?? '#000'; ?> !important;
            font-size: <?php echo $opt['author_size'] ?? '14'; ?>px !important;
        }
        .comment-content p {
            color: <?php echo $opt['text_color'] ?? '#444'; ?> !important;
            font-size: <?php echo $opt['text_size'] ?? '14'; ?>px !important;
        }
        .comment-author img {
            width: <?php echo $opt['avatar_size'] ?? '50'; ?>px !important;
            height: <?php echo $opt['avatar_size'] ?? '50'; ?>px !important;
            border-radius: <?php echo $opt['avatar_radius'] ?? '50'; ?>% !important;
        }
        .comment-metadata a {
            color: <?php echo $opt['date_color'] ?? '#777'; ?> !important;
            font-size: <?php echo $opt['date_size'] ?? '12'; ?>px !important;
        }
        .wp-comment-actions svg {
            fill: <?php echo $opt['icon_color'] ?? '#5b5bff'; ?>;
            width: <?php echo $opt['icon_size'] ?? '16'; ?>px;
            height: <?php echo $opt['icon_size'] ?? '16'; ?>px;
        }
    </style>
    <?php
});


/* ==========================================================================
   3. CORE LOGIC & FILTERS
   ========================================================================== */

// غیرفعال‌سازی بررسی اجباری ایمیل در سطح هسته (اگر تنظیمات افزونه روی مخفی باشد)
add_filter('option_require_name_email', function($val){
    $opt = get_option('wp_ce_settings');
    if(isset($opt['email_field']) && $opt['email_field'] === 'off') return false;
    return $val;
});

// دستکاری فرم
add_filter('comment_form_defaults', function ($d) {
    $opt = get_option('wp_ce_settings');
    $user = wp_get_current_user();
    
    // Check Auth
    if(isset($opt['auth_mode']) && $opt['auth_mode'] === 'logged_in' && !is_user_logged_in()){
        $d['must_log_in'] = '<p class="must-log-in">برای ارسال دیدگاه باید وارد شوید.</p>';
        // Hide form fields
        return $d;
    }

    $d['action'] = 'javascript:void(0);';
    $d['label_submit'] = 'ثبت و ارسال دیدگاه';
    $d['title_reply'] = 'ارسال دیدگاه';

    $email_field_html = '';
    if(isset($opt['email_field']) && $opt['email_field'] === 'on') {
        $email_field_html = '<p class="comment-form-email"><input type="email" name="email" required placeholder="ایمیل شما (الزامی) *"></p>';
    }

    $d['fields'] = [
        'author' => '<div class="wp-ce-fields-row">
            <p class="comment-form-author"><input type="text" name="author" required placeholder="نام شما *"></p>
            '.$email_field_html.'
        </div>'
    ];

    $d['comment_field'] = '
    <p class="comment-form-comment">
        <textarea name="comment" required placeholder="متن دیدگاه ..."></textarea>
    </p>
    <div class="wp-ce-form-footer">
        <input type="hidden" name="comment_parent" value="0">
        <button type="button" class="wp-ce-cancel-reply" style="display:none;">لغو پاسخ ❌</button>
    </div>';

    return $d;
}, 99);

// اضافه کردن دکمه‌های لایک و دیس‌لایک به محتوای دیدگاه
add_filter('comment_text', function($text, $comment){
    if(is_admin()) return $text;

    $likes = (int) get_comment_meta($comment->comment_ID, 'likes', true);
    $dislikes = (int) get_comment_meta($comment->comment_ID, 'dislikes', true);

    // SVG Icons
    $icon_like = '<svg viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 2.14-1.17l4.35-10.16c.12-.28.19-.58.19-.89v-2z"/></svg>';
    $icon_dislike = '<svg viewBox="0 0 24 24"><path d="M15 3H6c-.83 0-1.54.5-2.14 1.17L.5 14.33c-.12.28-.19.58-.19.89v2c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2zm4 0v12h4V3h-4z"/></svg>';

    $btns = '
    <div class="wp-comment-actions">
        <span class="wp-btn-like" data-id="'.$comment->comment_ID.'" data-type="like">'.$icon_like.' <small>'.$likes.'</small></span>
        <span class="wp-btn-dislike" data-id="'.$comment->comment_ID.'" data-type="dislike">'.$icon_dislike.' <small>'.$dislikes.'</small></span>
    </div>';

    return $text . $btns;
}, 10, 2);


/* ==========================================================================
   4. ENQUEUE & SCRIPTS
   ========================================================================== */
add_action('wp_enqueue_scripts', function () {
    if (!is_singular() || !comments_open()) return;

    wp_enqueue_style('wp-comment-engine', plugin_dir_url(__FILE__) . 'assets/style.css', [], '3.0.0');
    wp_enqueue_script('wp-comment-engine', plugin_dir_url(__FILE__) . 'assets/ajax.js', ['jquery'], '3.0.0', true);

    wp_localize_script('wp-comment-engine', 'WPComment', [
        'ajax'   => admin_url('admin-ajax.php'),
        'post'   => get_the_ID(),
        'nonce'  => wp_create_nonce('wp_comment_nonce')
    ]);
});


/* ==========================================================================
   5. AJAX HANDLERS
   ========================================================================== */

// ثبت دیدگاه
add_action('wp_ajax_wp_comment_submit', 'wp_comment_submit');
add_action('wp_ajax_nopriv_wp_comment_submit', 'wp_comment_submit');

function wp_comment_submit() {
    check_ajax_referer('wp_comment_nonce', 'nonce');
    $opt = get_option('wp_ce_settings');

    // Email Handling
    $email = 'hidden@email.com';
    if(isset($opt['email_field']) && $opt['email_field'] === 'on' && !empty($_POST['email'])){
        $email = sanitize_email($_POST['email']);
    }

    // Approval Logic
    $approved = 1; // Default Approved
    $msg = '✅ دیدگاه شما با موفقیت ارسال شد.';
    $status_code = 'approved';

    // 1. Check Setting: Instant?
    if(isset($opt['approval_mode']) && $opt['approval_mode'] === 'instant') {
        $approved = 1;
    } 
    // 2. Check WordPress Core Setting
    else {
        if(get_option('comment_moderation') == 1) {
            $approved = 0;
            $msg = '⏳ دیدگاه ارسال شد و در انتظار تایید مدیریت است.';
            $status_code = 'pending';
        }
    }

    $data = [
        'comment_post_ID'      => intval($_POST['post']),
        'comment_author'       => sanitize_text_field($_POST['author']),
        'comment_author_email' => $email,
        'comment_content'      => sanitize_textarea_field($_POST['comment']),
        'comment_parent'       => intval($_POST['parent']),
        'comment_approved'     => $approved
    ];

    $id = wp_insert_comment($data);

    if ($id) {
        ob_start();
        // فقط اگر تایید شده باشد رندر می‌کنیم
        if($approved){
            wp_list_comments(['per_page'=>1, 'avatar_size'=> ($opt['avatar_size'] ?? 50)], [$id]);
        }
        
        wp_send_json_success([
            'html' => ob_get_clean(),
            'msg'  => $msg,
            'status' => $status_code,
            'parent' => $data['comment_parent']
        ]);
    }

    wp_send_json_error('❌ خطا در ارسال دیدگاه');
}

// لایک و دیس‌لایک
add_action('wp_ajax_wp_comment_like', 'wp_comment_like');
add_action('wp_ajax_nopriv_wp_comment_like', 'wp_comment_like');

function wp_comment_like(){
    $id   = intval($_POST['id']);
    $type = sanitize_text_field($_POST['type']);
    $ip   = $_SERVER['REMOTE_ADDR'];

    // Check Duplicate by IP (Meta)
    $voters = get_comment_meta($id, '_voters_ip', true);
    if(!is_array($voters)) $voters = [];

    if(in_array($ip, $voters)){
        wp_send_json_error('شما قبلا رای داده‌اید!');
    }

    // Update Meta
    $key  = $type === 'like' ? 'likes' : 'dislikes';
    $val  = (int) get_comment_meta($id, $key, true);
    update_comment_meta($id, $key, $val + 1);
    
    // Add IP to voters
    $voters[] = $ip;
    update_comment_meta($id, '_voters_ip', $voters);

    wp_send_json_success($val + 1);
}

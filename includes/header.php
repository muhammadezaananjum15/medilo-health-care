<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Global Header Include Template
 * 
 * Layout strictly matches the original Medilo Envato HTML template structure & theme:
 * - Top Bar: Contact info left, social icons right (in cs_blue_bg)
 * - Main Header: 3-column layout:
 *     1) cs_main_header_left   -> Brand Logo
 *     2) cs_main_header_center -> Navigation Menu (cs_nav)
 *     3) cs_main_header_right  -> Search Toggle + Login/Register/Dashboard Buttons
 */

require_once __DIR__ . '/../config/db.php';

$current_user = get_current_user_data();
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Medilo Health Services">
    <!-- Favicon -->
    <link rel="icon" href="<?php echo APP_URL; ?>/assets/img/favicon.png">
    <!-- Site Title -->
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . APP_NAME : APP_NAME; ?></title>
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/animate.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/odometer.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/slick.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    
    <!-- Custom Enhancements, Mobile Header & Hamburger Menu Styling -->
    <style>
        /* Flash message container styling */
        .flash-container {
            position: relative;
            z-index: 1000;
        }

        /* -----------------------------------------------------
           HEADER POINTER-EVENTS & CLICKABILITY FIX
        ----------------------------------------------------- */
        .cs_main_header_left,
        .cs_main_header_right {
            position: relative;
            z-index: 30;
            display: flex;
            align-items: center;
        }

        .cs_main_header_center {
            pointer-events: none;
        }

        .cs_main_header_center .cs_nav {
            pointer-events: auto;
        }

        .cs_main_header_right a.cs_btn,
        .cs_main_header_right .cs_search_toggle {
            pointer-events: auto !important;
            position: relative;
            z-index: 35;
            cursor: pointer !important;
        }

        .cs_main_header_right .cs_btn.cs_style_1 {
            font-size: 14px;
            padding: 9px 20px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 30px;
        }

        /* Top header link styling */
        .cs_top_header_left a {
            color: #ffffff;
            transition: color 0.3s ease;
        }
        .cs_top_header_left a:hover {
            color: var(--accent-color);
        }

        /* -----------------------------------------------------
           STICKY HEADER STYLING & COLLAPSE TRANSITION
        ----------------------------------------------------- */
        .cs_site_header.cs_sticky_header {
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000 !important;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .cs_site_header .cs_top_header {
            transition: max-height 0.35s ease, opacity 0.3s ease, padding 0.35s ease;
            max-height: 100px;
            overflow: hidden;
        }

        .cs_site_header.cs_gescout_sticky .cs_top_header {
            max-height: 0 !important;
            opacity: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .cs_site_header.cs_gescout_sticky {
            box-shadow: 0 10px 30px rgba(15, 34, 73, 0.12) !important;
            background-color: #ffffff !important;
        }

        /* -----------------------------------------------------
           HEADER SEARCH FORM REFINEMENTS (DESKTOP & MOBILE)
        ----------------------------------------------------- */
        .cs_header_search_form {
            position: absolute;
            top: calc(100% + 15px);
            right: 0;
            width: 310px;
            background-color: #ffffff;
            padding: 14px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(15, 34, 73, 0.18);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px) scale(0.95);
            z-index: 1050;
            border: 1px solid rgba(0, 77, 153, 0.1);
        }

        .cs_header_search_form.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .cs_header_search_form_in {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .cs_header_search_form .cs_header_search_field {
            font-size: 14px !important;
            font-weight: 500;
            height: 46px;
            padding: 5px 50px 5px 18px;
            border-radius: 30px !important;
            border: 1px solid #dce4ec;
            color: var(--heading-color);
            outline: none;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .cs_header_search_form .cs_header_search_field::placeholder {
            font-size: 13px !important;
            color: #888888;
        }

        .cs_header_search_form .cs_header_search_field:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 12px rgba(46, 166, 247, 0.25);
        }

        .cs_header_search_form .cs_header_submit_btn {
            position: absolute !important;
            right: 3px !important;
            top: 3px !important;
            bottom: 3px !important;
            height: 40px !important;
            width: 40px !important;
            border: none !important;
            padding: 0 !important;
            border-radius: 50% !important;
            background-color: var(--accent-color) !important;
            color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            transition: all 0.25s ease !important;
            cursor: pointer !important;
        }

        .cs_header_search_form .cs_header_submit_btn:hover {
            background-color: var(--blue-color) !important;
            transform: scale(1.06);
        }

        /* -----------------------------------------------------
           FOOTER NEWSLETTER INPUT & FORM STYLING
        ----------------------------------------------------- */
        .cs_newsletter_input_wrap {
            position: relative;
            width: 100%;
            margin-bottom: 10px;
        }

        .cs_newsletter_input {
            width: 100%;
            height: 48px;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 30px;
            padding: 0 20px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .cs_newsletter_input::placeholder {
            color: rgba(255, 255, 255, 0.65);
        }

        .cs_newsletter_input:focus {
            background-color: rgba(255, 255, 255, 0.22);
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(46, 166, 247, 0.4);
        }

        .cs_newsletter_form .cs_btn {
            border-radius: 30px;
            height: 46px;
            font-weight: 600;
        }

        /* -----------------------------------------------------
           SITE-WIDE CARD & BUTTON HOVER/BOX-SHADOW ENHANCEMENTS
        ----------------------------------------------------- */
        .card, .cs_card, .cs_iconbox {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-radius: 14px !important;
        }
        .card:hover, .cs_card:hover {
            transform: translateY(-6px) !important;
            box-shadow: 0 16px 36px rgba(15, 34, 73, 0.12) !important;
        }

        .cs_btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 14px rgba(0, 77, 153, 0.15);
        }
        .cs_btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 77, 153, 0.25);
        }

        /* Doctor card image container & object-fit contain */
        .cs_doctor_card_img {
            height: 250px;
            width: 100%;
            object-fit: contain !important;
            background-color: #f8fafd;
            padding: 12px;
            border-bottom: 1px solid #eef2f7;
            transition: transform 0.3s ease;
        }

        .card:hover .cs_doctor_card_img {
            transform: scale(1.02);
        }

        /* Active list items for sidebars & portals */
        .list-group-item.active,
        .list-group-item.cs_blue_bg {
            background-color: var(--blue-color) !important;
            border-color: var(--blue-color) !important;
            color: #ffffff !important;
        }

        .cs_hero_slider_thumb_item {
            min-height: 520px;
        }

        /* Hero Slider Dots */
        .cs_hero_dots {
            position: absolute;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
        }
        .cs_hero_dots .slick-dots {
            display: flex !important;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .cs_hero_dots .slick-dots li button {
            font-size: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.7);
            background: transparent;
            cursor: pointer;
            padding: 0;
            transition: all 0.3s ease;
        }
        .cs_hero_dots .slick-dots li.slick-active button {
            background: #ffffff;
            border-color: #ffffff;
            transform: scale(1.3);
        }

        /* Hero Slider Arrows */
        .cs_hero_prev_arrow,
        .cs_hero_next_arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 20;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(6px);
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .cs_hero_prev_arrow { left: 24px; }
        .cs_hero_next_arrow { right: 24px; }
        .cs_hero_prev_arrow:hover,
        .cs_hero_next_arrow:hover {
            background: rgba(255,255,255,0.35);
            border-color: #ffffff;
            transform: translateY(-50%) scale(1.1);
        }
        @media (max-width: 767px) {
            .cs_hero_prev_arrow,
            .cs_hero_next_arrow {
                width: 36px;
                height: 36px;
                font-size: 13px;
            }
            .cs_hero_prev_arrow { left: 10px; }
            .cs_hero_next_arrow { right: 10px; }
        }

        /* -----------------------------------------------------
           MOBILE HAMBURGER DRAWER MENU STYLING (THEME NAVY BG + CENTER ALIGNED + ACCENT HOVER)
        ----------------------------------------------------- */
        @media (max-width: 1199px) {
            .cs_site_header.cs_style_1 .cs_main_header_in {
                height: 75px;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-wrap: nowrap !important;
            }
            .cs_main_header_center {
                position: static;
                transform: none;
                width: auto;
                max-width: none;
            }

            /* Mobile Slide-Out Nav Drawer - Deep Navy Theme Alignment */
            .cs_nav .cs_nav_list {
                background: linear-gradient(180deg, #0f2249 0%, #0b1a36 100%) !important;
                text-align: center !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                padding-top: 90px !important;
                padding-bottom: 60px !important;
                overflow-y: auto;
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4) !important;
                border-bottom: 3px solid var(--accent-color);
            }

            .cs_nav .cs_nav_list > li {
                width: 100% !important;
                margin-right: 0 !important;
                margin-bottom: 12px !important;
                text-align: center !important;
            }

            .cs_nav .cs_nav_list > li > a {
                font-size: 20px !important;
                font-weight: 600 !important;
                color: #ffffff !important;
                padding: 10px 24px !important;
                display: inline-block !important;
                position: relative !important;
                transition: color 0.3s ease, transform 0.2s ease !important;
            }

            /* Animated Bottom Line / Underline on Hover & Active */
            .cs_nav .cs_nav_list > li > a::after {
                content: "" !important;
                position: absolute !important;
                bottom: 2px !important;
                left: 50% !important;
                transform: translateX(-50%) scaleX(0) !important;
                width: 60% !important;
                height: 3px !important;
                background-color: var(--accent-color) !important;
                border-radius: 2px !important;
                transition: transform 0.3s ease !important;
            }

            .cs_nav .cs_nav_list > li > a:hover::after,
            .cs_nav .cs_nav_list > li.active > a::after {
                transform: translateX(-50%) scaleX(1) !important;
            }

            .cs_nav .cs_nav_list > li > a:hover {
                color: var(--accent-color) !important;
                transform: translateY(-2px);
            }

            .cs_main_header_right {
                gap: 10px !important;
                flex-wrap: nowrap !important;
            }
            .cs_main_header_right .cs_btn.cs_style_1 {
                padding: 7px 14px;
                font-size: 13px;
            }
        }

        @media (max-width: 767px) {
            .cs_site_header.cs_style_1 .cs_main_header_in {
                height: 68px;
                padding: 0 5px;
            }
            .cs_main_header_right {
                gap: 6px !important;
            }
            .cs_main_header_right .cs_btn.cs_style_1 {
                padding: 6px 11px;
                font-size: 12px;
                border-radius: 20px;
            }
            .cs_site_branding img {
                max-height: 38px;
            }
            /* MOBILE SEARCH POPUP ALIGN CENTER */
            .cs_header_search_form {
                position: fixed !important;
                top: 75px !important;
                left: 50% !important;
                right: auto !important;
                width: 90vw !important;
                max-width: 360px !important;
                transform: translateX(-50%) translateY(12px) scale(0.95) !important;
                box-shadow: 0 20px 45px rgba(11, 26, 54, 0.3) !important;
            }
            .cs_header_search_form.active {
                transform: translateX(-50%) translateY(0) scale(1) !important;
            }
            .cs_footer_widget {
                padding: 25px 0 !important;
            }
            .cs_footer_logo {
                padding-bottom: 15px;
                margin-bottom: 15px;
            }
            .cs_footer_widget_title {
                font-size: 18px;
                margin-bottom: 15px;
            }
            .cs_footer_bottom_in {
                text-align: center;
                justify-content: center !important;
            }
            .cs_hero_title {
                font-size: 30px !important;
            }
        }

        /* -----------------------------------------------------
           HAMBURGER MENU TOGGLE VISIBILITY FIX
           Force visible color in both inactive (bars) and active (X) states
        ----------------------------------------------------- */
        .cs_menu_toggle {
            top: 50% !important;
            right: 15px !important;
            margin-top: -13px !important;
            z-index: 200 !important;
            display: none;
        }

        /* Inactive state: 3 horizontal bars — force dark navy color */
        .cs_menu_toggle span,
        .cs_menu_toggle span::before,
        .cs_menu_toggle span::after {
            background-color: var(--blue-color) !important;
        }

        /* Active state (X icon when menu open) — use accent color */
        .cs_toggle_active.cs_menu_toggle span {
            background-color: transparent !important;
        }
        .cs_toggle_active.cs_menu_toggle span::before,
        .cs_toggle_active.cs_menu_toggle span::after {
            background-color: var(--accent-color) !important;
        }

        /* Ensure menu toggle shows on mobile/tablet breakpoints */
        @media (max-width: 1199px) {
            .cs_menu_toggle {
                display: inline-block !important;
            }
        }

        /* -----------------------------------------------------
           HERO TITLE MOBILE FONT SIZE BOOST
        ----------------------------------------------------- */
        @media (max-width: 767px) {
            .cs_hero.cs_style_1 .cs_hero_title {
                font-size: 34px !important;
                line-height: 1.2 !important;
                margin-bottom: 10px !important;
            }
            .cs_hero.cs_style_1 .cs_hero_title span::before {
                height: 4px !important;
                bottom: 3px !important;
            }
            .cs_hero.cs_style_1 .cs_hero_subtitle {
                font-size: 15px !important;
            }
        }

        @media (max-width: 480px) {
            .cs_hero.cs_style_1 .cs_hero_title {
                font-size: 28px !important;
            }
        }

        /* -----------------------------------------------------
           STATS COUNTER SECTION MOBILE SCALING
        ----------------------------------------------------- */
        .cs_counter_item h2 {
            font-size: clamp(2rem, 7vw, 3.5rem);
            line-height: 1.1;
        }
        .cs_counter_item p {
            font-size: clamp(0.78rem, 2.8vw, 1rem);
            margin-top: 4px;
        }

        @media (max-width: 575px) {
            .cs_counter_area .row {
                row-gap: 1.5rem !important;
            }
            .cs_counter_item {
                padding: 16px 8px;
                border-radius: 10px;
                background-color: rgba(255,255,255,0.07);
            }
            .cs_counter_item h2 {
                font-size: 2.2rem !important;
            }
            .cs_counter_item p {
                font-size: 0.78rem !important;
            }
        }

        @media (max-width: 480px) {
            .cs_main_header_right .cs_btn.cs_style_1 {
                padding: 5px 9px;
                font-size: 11px;
            }
            .cs_main_header_right .cs_btn.cs_style_1 i {
                font-size: 11px;
            }
            .cs_btn_hide_mobile {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="cs_preloader">
        <div class="cs_preloader_in">
            <div class="cs_wave_first">
                <svg enable-background="new 0 0 300.08 300.08" viewBox="0 0 300.08 300.08" xmlns="http://www.w3.org/2000/svg"><path d="m293.26 184.14h-82.877l-12.692-76.138c-.546-3.287-3.396-5.701-6.718-5.701-.034 0-.061 0-.089 0-3.369.027-6.199 2.523-6.677 5.845l-12.507 87.602-14.874-148.69c-.355-3.43-3.205-6.056-6.643-6.138-.048 0-.096 0-.143 0-3.39 0-6.274 2.489-6.752 5.852l-19.621 137.368h-9.405l-12.221-42.782c-.866-3.028-3.812-5.149-6.8-4.944-3.13.109-5.777 2.332-6.431 5.395l-8.941 42.332h-73.049c-3.771 0-6.82 3.049-6.82 6.82 0 3.778 3.049 6.82 6.82 6.82h78.566c3.219 0 6.002-2.251 6.67-5.408l4.406-20.856 6.09 21.313c.839 2.939 3.526 4.951 6.568 4.951h20.46c3.396 0 6.274-2.489 6.752-5.845l12.508-87.596 14.874 148.683c.355 3.437 3.205 6.056 6.643 6.138h.143c3.39 0 6.274-2.489 6.752-5.845l14.227-99.599 6.397 38.362c.546 3.287 3.396 5.702 6.725 5.702h88.66c3.771 0 6.82-3.049 6.82-6.82-.001-3.772-3.05-6.821-6.821-6.821z"></path></svg>
            </div>
            <div class="cs_wave_second">
                <svg enable-background="new 0 0 300.08 300.08" viewBox="0 0 300.08 300.08" xmlns="http://www.w3.org/2000/svg"><path d="m293.26 184.14h-82.877l-12.692-76.138c-.546-3.287-3.396-5.701-6.718-5.701-.034 0-.061 0-.089 0-3.369.027-6.199 2.523-6.677 5.845l-12.507 87.602-14.874-148.69c-.355-3.43-3.205-6.056-6.643-6.138-.048 0-.096 0-.143 0-3.39 0-6.274 2.489-6.752 5.852l-19.621 137.368h-9.405l-12.221-42.782c-.866-3.028-3.812-5.149-6.8-4.944-3.13.109-5.777 2.332-6.431 5.395l-8.941 42.332h-73.049c-3.771 0-6.82 3.049-6.82 6.82 0 3.778 3.049 6.82 6.82 6.82h78.566c3.219 0 6.002-2.251 6.67-5.408l4.406-20.856 6.09 21.313c.839 2.939 3.526 4.951 6.568 4.951h20.46c3.396 0 6.274-2.489 6.752-5.845l12.508-87.596 14.874 148.683c.355 3.437 3.205 6.056 6.643 6.138h.143c3.39 0 6.274-2.489 6.752-5.845l14.227-99.599 6.397 38.362c.546 3.287 3.396 5.702 6.725 5.702h88.66c3.771 0 6.82-3.049 6.82-6.82-.001-3.772-3.05-6.821-6.821-6.821z"></path></svg>
            </div>
        </div>
    </div>

    <!-- =====================================================
         SITE HEADER
         Top Bar + 3-Column Main Header Row
    ====================================================== -->
    <header class="cs_site_header cs_style_1 cs_primary_color cs_sticky_header cs_white_bg">

        <!-- Top Header Bar -->
        <div class="cs_top_header cs_blue_bg cs_white_color">
            <div class="container">
                <div class="cs_top_header_in">
                    <div class="cs_top_header_left">
                        <ul class="cs_header_contact_list cs_mp_0">
                            <li>
                                <i class="fa-solid fa-envelope"></i>
                                <a href="mailto:support@medilo.com">support@medilo.com</a>
                            </li>
                            <li>
                                <i class="fa-solid fa-location-dot"></i> 100 Healthcare Way, Medical District, NY
                            </li>
                            <li>
                                <i class="fa-solid fa-phone"></i> +1 (800) 555-MEDILO
                            </li>
                        </ul>
                    </div>
                    <div class="cs_top_header_right">
                        <div class="cs_social_btns cs_style_1">
                            <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="https://www.pinterest.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Pinterest"><i class="fa-brands fa-pinterest-p"></i></a>
                            <a href="https://www.twitter.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Twitter"><i class="fa-brands fa-twitter"></i></a>
                            <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header Row -->
        <div class="cs_main_header">
            <div class="container">
                <div class="cs_main_header_in">

                    <!-- Column 1: Brand Logo (Left) -->
                    <div class="cs_main_header_left">
                        <a class="cs_site_branding" href="<?php echo APP_URL; ?>/index.php">
                            <img src="<?php echo APP_URL; ?>/assets/img/logo.svg" alt="Medilo Logo">
                        </a>
                    </div>

                    <!-- Column 2: Navigation Menu (Center) -->
                    <div class="cs_main_header_center">
                        <div class="cs_nav cs_primary_color">
                            <ul class="cs_nav_list">
                                <li><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/doctors.php">Doctors</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/services.php">Services</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/health_info.php">Health Info</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/news.php">News</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/contact.php">Contact</a></li>
                                
                                <!-- Mobile Drawer Auth Links -->
                                <?php if ($current_user): ?>
                                    <?php if ($current_user['role'] === 'admin'): ?>
                                        <li class="d-lg-none"><a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="text-info fw-bold">Admin Panel</a></li>
                                    <?php elseif ($current_user['role'] === 'doctor'): ?>
                                        <li class="d-lg-none"><a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php" class="text-info fw-bold">Doctor Portal</a></li>
                                    <?php else: ?>
                                        <li class="d-lg-none"><a href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php" class="text-info fw-bold">Patient Dashboard</a></li>
                                    <?php endif; ?>
                                    <li class="d-lg-none"><a href="<?php echo APP_URL; ?>/auth/logout.php" class="text-danger">Logout</a></li>
                                <?php else: ?>
                                    <li class="d-lg-none"><a href="<?php echo APP_URL; ?>/auth/login.php">Login</a></li>
                                    <li class="d-lg-none"><a href="<?php echo APP_URL; ?>/auth/register.php">Register</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Search Toggle & Role-Based Auth Buttons (Right) -->
                    <div class="cs_main_header_right">

                        <!-- Search Toggle -->
                        <div class="cs_search_wrap">
                            <div class="cs_search_toggle cs_center">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <form action="<?php echo APP_URL; ?>/frontend/doctors.php" method="GET" class="cs_header_search_form">
                                <div class="cs_header_search_form_in">
                                    <input type="text" name="search" placeholder="Search doctor or specialty…" class="cs_header_search_field">
                                    <button type="submit" class="cs_header_submit_btn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Role-Based Action Buttons -->
                        <?php if ($current_user): ?>
                            <?php if ($current_user['role'] === 'admin'): ?>
                                <a class="cs_btn cs_style_1 cs_color_1" href="<?php echo APP_URL; ?>/admin/dashboard.php">
                                    <span>Admin Panel</span> <i class="fa-solid fa-gauge"></i>
                                </a>
                            <?php elseif ($current_user['role'] === 'doctor'): ?>
                                <a class="cs_btn cs_style_1 cs_color_1" href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php">
                                    <span>My Portal</span> <i class="fa-solid fa-user-doctor"></i>
                                </a>
                            <?php else: ?>
                                <a class="cs_btn cs_style_1 cs_color_1" href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php">
                                    <span>Dashboard</span> <i class="fa-solid fa-user"></i>
                                </a>
                            <?php endif; ?>
                            <a class="cs_btn cs_style_1 cs_color_2 cs_btn_hide_mobile" href="<?php echo APP_URL; ?>/auth/logout.php" title="Logout">
                                <span>Logout</span> <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        <?php else: ?>
                            <a class="cs_btn cs_style_1 cs_color_2" href="<?php echo APP_URL; ?>/auth/login.php">
                                <span>Login</span> <i class="fa-solid fa-right-to-bracket"></i>
                            </a>
                            <a class="cs_btn cs_style_1 cs_color_1 cs_btn_hide_mobile" href="<?php echo APP_URL; ?>/auth/register.php">
                                <span>Register</span> <i class="fa-solid fa-user-plus"></i>
                            </a>
                        <?php endif; ?>

                    </div><!-- end .cs_main_header_right -->
                </div><!-- end .cs_main_header_in -->
            </div><!-- end .container -->
        </div><!-- end .cs_main_header -->
    </header>
    <div class="cs_site_header_spacing_150"></div>

    <!-- Flash Notifications Container -->
    <div class="flash-container">
        <div class="container mt-2">
            <?php display_flash_message(); ?>
        </div>
    </div>

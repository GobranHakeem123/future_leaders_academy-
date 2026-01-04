<?php
// index.php
require_once 'config.php';
require_once 'functions.php';

// معالجة الفلاتر
$filter = $_GET['filter'] ?? 'all';
$category = $_GET['category'] ?? 'all';

// جلب البيانات
$works = Helper::getAllWorks($filter, $category);
$categories = Helper::getAllCategories();

// دالة لتصحيح مسار الملفات
function getCorrectedMediaUrl($url) {
    // إذا كان المسار يحتوي على localhost، قم بتصحيحه
    if (strpos($url, 'http://localhost/future_leaders_academy/') !== false) {
        $url = str_replace('http://localhost/future_leaders_academy/', '', $url);
    }
    // التأكد من أن المسار يبدأ بـ uploads/
    if (strpos($url, 'uploads/') === 0) {
        return $url;
    }
    // إذا كان المسار نسبيًا، أضف uploads/ في البداية
    if (strpos($url, 'uploads/') !== false) {
        // استخراج المسار النسبي فقط
        $parts = explode('uploads/', $url);
        if (isset($parts[1])) {
            return 'uploads/' . $parts[1];
        }
    }
    return $url;
}

// رأس الصفحة
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="معرض الأعمال الإبداعية - FUTURE LEADERS ACADEMY">
    <title>معرض الأعمال - FUTURE LEADERS ACADEMY</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* ===== ENHANCED COLOR SCHEME ===== */
        :root {
            /* Primary Blue Colors - بارزة وجذابة */
            --primary-blue: #2563EB;
            --primary-blue-light: #3B82F6;
            --primary-blue-dark: #1D4ED8;
            --primary-cyan: #06B6D4;
            --primary-cyan-light: #22D3EE;
            --primary-purple: #8B5CF6;
            
            /* Vibrant Accent Colors */
            --accent-teal: #0D9488;
            --accent-pink: #EC4899;
            --accent-orange: #F59E0B;
            --accent-emerald: #10B981;
            --accent-rose: #F43F5E;
            
            /* Country Colors */
            --saudi-green: #059669;
            --saudi-green-light: #10B981;
            --uae-red: #DC2626;
            --uae-red-light: #EF4444;
            
            /* File Type Colors - أكثر حيوية */
            --pdf-red: #EF4444;
            --presentation-pink: #EC4899;
            --document-blue: #2563EB;
            --spreadsheet-emerald: #10B981;
            --archive-amber: #F59E0B;
            
            /* UI Colors - Modern Dark Theme (Default) */
            --bg-primary: #0F172A;
            --bg-secondary: #1E293B;
            --bg-card: #1E293B;
            --text-primary: #F1F5F9;
            --text-secondary: #CBD5E1;
            --text-muted: #94A3B8;
            --border-color: #334155;
            
            /* Gradients - بارزة ومتدرجة */
            --gradient-primary: linear-gradient(135deg, #2563EB 0%, #06B6D4 50%, #8B5CF6 100%);
            --gradient-blue: linear-gradient(135deg, #2563EB 0%, #3B82F6 100%);
            --gradient-cyan: linear-gradient(135deg, #06B6D4 0%, #22D3EE 100%);
            --gradient-purple: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
            --gradient-saudi: linear-gradient(135deg, #059669 0%, #10B981 100%);
            --gradient-uae: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
            --gradient-pdf: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
            --gradient-presentation: linear-gradient(135deg, #EC4899 0%, #F472B6 100%);
            --gradient-document: linear-gradient(135deg, #2563EB 0%, #60A5FA 100%);
            --gradient-spreadsheet: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            
            /* Glow Effects */
            --glow-blue: 0 0 20px rgba(37, 99, 235, 0.5);
            --glow-cyan: 0 0 20px rgba(6, 182, 212, 0.5);
            --glow-purple: 0 0 20px rgba(139, 92, 246, 0.5);
            
            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.4);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.5);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.6);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.7);
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            
            /* Z-index Layers */
            --z-dropdown: 1000;
            --z-sticky: 1020;
            --z-fixed: 1030;
            --z-modal: 1050;
            --z-tooltip: 1060;
        }

        /* ===== Light Mode Colors ===== */
        .light-mode {
            --bg-primary: #F8FAFC;
            --bg-secondary: #FFFFFF;
            --bg-card: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border-color: #E2E8F0;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.16);
        }

        /* ===== Reset & Base Styles ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }

        body {
            font-family: 'Noto Kufi Arabic', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.7;
            transition: background-color var(--transition-normal), color var(--transition-normal);
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }

        /* ===== Enhanced Hero Animation ===== */
        .hero-section {
            animation: gradientBackground 15s ease infinite, pulseGlow 4s ease-in-out infinite;
            background: linear-gradient(-45deg, #0F172A, #1E293B, #1E40AF, #0F766E);
            background-size: 400% 400%;
            position: relative;
            transition: background var(--transition-normal);
        }

        .light-mode .hero-section {
            background: linear-gradient(-45deg, #F8FAFC, #E2E8F0, #3B82F6, #0D9488);
            background-size: 400% 400%;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(37, 99, 235, 0.3) 0%, transparent 50%);
            pointer-events: none;
            animation: pulseGlow 4s ease-in-out infinite;
            transition: background var(--transition-normal);
        }

        .light-mode .hero-section::before {
            background: radial-gradient(circle at 30% 50%, rgba(37, 99, 235, 0.15) 0%, transparent 50%);
        }

        @keyframes gradientBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.8; }
        }

        /* ===== Brand Logo - Enhanced ===== */
        .brand-logo .logo-arabic {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.8rem;
            font-weight: 900;
            animation: gradientText 3s ease infinite;
            background-size: 200% auto;
        }

        @keyframes gradientText {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* ===== Work Cards - Enhanced ===== */
        .work-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-slow);
            animation: fadeInUp 0.6s ease both;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
            background: linear-gradient(var(--bg-card), var(--bg-card)) padding-box,
                        linear-gradient(135deg, var(--primary-blue), var(--primary-cyan)) border-box;
        }

        .work-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--glow-blue), var(--shadow-lg);
            animation: cardFloat 3s ease-in-out infinite;
        }

        @keyframes cardFloat {
            0%, 100% { transform: translateY(-10px) rotateX(0); }
            50% { transform: translateY(-15px) rotateX(2deg); }
        }

        /* ===== Buttons - Enhanced ===== */
        .nav-link, .category-btn, .work-btn, .contact-btn {
            position: relative;
            overflow: hidden;
            transition: all var(--transition-normal);
            border: 2px solid transparent;
        }

        .nav-link:hover,
        .nav-link:focus,
        .category-btn:hover,
        .category-btn:focus,
        .work-btn:hover,
        .work-btn:focus {
            background: var(--gradient-blue);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: var(--glow-blue);
        }

        /* Primary CTA Buttons */
        .contact-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            animation: pulseButton 2s infinite;
        }

        @keyframes pulseButton {
            0%, 100% { transform: translateY(0); box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4); }
            50% { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6); }
        }

        /* ===== Badges - Enhanced ===== */
        .work-badge {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-cyan));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: var(--glow-blue);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .work-badge.saudi {
            background: var(--gradient-saudi);
            box-shadow: 0 0 15px rgba(5, 150, 105, 0.5);
        }

        .work-badge.uae {
            background: var(--gradient-uae);
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.5);
        }

        /* ===== File Type Indicators ===== */
        .media-type {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-cyan));
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: var(--glow-blue);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ===== Navigation - Enhanced ===== */
        .nav-link.active {
            background: var(--gradient-blue);
            color: white;
            box-shadow: var(--glow-blue);
        }

        .nav-link.active::after {
            background: var(--gradient-cyan);
            box-shadow: 0 0 10px var(--primary-cyan);
        }

        /* ===== Theme Toggle Button ===== */
        .theme-toggle-btn {
            background: var(--gradient-primary);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-normal);
            box-shadow: var(--glow-blue);
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }

        .theme-toggle-btn:hover {
            transform: rotate(30deg) scale(1.1);
            box-shadow: 0 0 25px rgba(37, 99, 235, 0.8);
        }

        .light-mode .theme-toggle-btn {
            background: linear-gradient(135deg, #F59E0B, #F97316);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
        }

        .theme-toggle-btn .fa-sun {
            display: none;
        }

        .theme-toggle-btn .fa-moon {
            display: block;
        }

        .light-mode .theme-toggle-btn .fa-sun {
            display: block;
        }

        .light-mode .theme-toggle-btn .fa-moon {
            display: none;
        }

        /* ===== Mobile Theme Toggle ===== */
        .mobile-theme-toggle {
            width: 45px;
            height: 45px;
            border-radius: var(--radius-full);
            background: var(--gradient-primary);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all var(--transition-normal);
            box-shadow: var(--glow-blue);
            margin-left: auto;
        }

        .light-mode .mobile-theme-toggle {
            background: linear-gradient(135deg, #F59E0B, #F97316);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
        }

        .mobile-theme-toggle:hover {
            transform: rotate(30deg) scale(1.1);
        }

        /* ===== Categories - Enhanced ===== */
        .category-btn.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--glow-blue);
            animation: pulseGlowSmall 2s infinite;
        }

        @keyframes pulseGlowSmall {
            0%, 100% { box-shadow: var(--glow-blue); }
            50% { box-shadow: 0 0 25px rgba(37, 99, 235, 0.8); }
        }

        /* ===== File Previews - Enhanced ===== */
        .pdf-preview {
            background: linear-gradient(135deg, var(--pdf-red), #F87171);
        }

        .ppt-preview {
            background: linear-gradient(135deg, var(--presentation-pink), #F472B6);
        }

        .doc-preview {
            background: linear-gradient(135deg, var(--document-blue), #60A5FA);
        }

        .excel-preview {
            background: linear-gradient(135deg, var(--spreadsheet-emerald), #34D399);
        }

        .archive-preview {
            background: linear-gradient(135deg, var(--archive-amber), #FBBF24);
        }

        /* ===== WhatsApp Dropdown Styles ===== */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: var(--z-fixed);
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .whatsapp-float-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            color: white;
            border-radius: 50%;
            font-size: 1.8rem;
            text-decoration: none;
            transition: all var(--transition-normal);
            background: linear-gradient(135deg, #25D366, #128C7E);
            box-shadow: 0 0 20px rgba(37, 211, 102, 0.6);
            animation: pulseWhatsApp 2s infinite;
            border: none;
            cursor: pointer;
            position: relative;
            z-index: 1002;
        }
        
        .whatsapp-float-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 30px rgba(37, 211, 102, 0.8);
        }
        
        .whatsapp-dropdown {
            position: absolute;
            bottom: 70px;
            left: 0;
            width: 320px;
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 2px solid var(--border-color);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all var(--transition-normal);
            z-index: 1001;
        }
        
        .whatsapp-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .whatsapp-dropdown-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }
        
        .whatsapp-dropdown-header h4 {
            margin: 0 0 0.5rem 0;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        
        .whatsapp-dropdown-header p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .whatsapp-dropdown-body {
            padding: 1rem;
        }
        
        .whatsapp-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            text-decoration: none;
            transition: all var(--transition-normal);
            margin-bottom: 0.75rem;
            border: 2px solid transparent;
        }
        
        .whatsapp-option:last-child {
            margin-bottom: 0;
        }
        
        .whatsapp-option:hover {
            transform: translateX(-5px);
            border-color: var(--primary-blue);
        }
        
        .whatsapp-option.saudi:hover {
            border-color: var(--saudi-green);
            background: rgba(5, 150, 105, 0.05);
        }
        
        .whatsapp-option.uae:hover {
            border-color: var(--uae-red);
            background: rgba(220, 38, 38, 0.05);
        }
        
        .whatsapp-option-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-left: 1rem;
            flex-shrink: 0;
        }
        
        .whatsapp-option.saudi .whatsapp-option-icon {
            background: var(--gradient-saudi);
            color: white;
        }
        
        .whatsapp-option.uae .whatsapp-option-icon {
            background: var(--gradient-uae);
            color: white;
        }
        
        .whatsapp-option-info {
            flex: 1;
        }
        
        .whatsapp-option-title {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .whatsapp-option.saudi .whatsapp-option-title {
            color: var(--saudi-green);
        }
        
        .whatsapp-option.uae .whatsapp-option-title {
            color: var(--uae-red);
        }
        
        .whatsapp-option-number {
            color: var(--text-secondary);
            font-size: 0.85rem;
            direction: ltr;
            text-align: right;
        }
        
        .whatsapp-option-arrow {
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: transform var(--transition-normal);
        }
        
        .whatsapp-option:hover .whatsapp-option-arrow {
            transform: translateX(-5px);
            color: var(--primary-blue);
        }
        
        .whatsapp-dropdown-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
            background: var(--bg-primary);
            border-radius: 0 0 var(--radius-xl) var(--radius-xl);
        }
        
        .whatsapp-dropdown-footer p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        /* WhatsApp Close Button */
        .whatsapp-close {
            position: absolute;
            top: 10px;
            left: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1rem;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal);
        }
        
        .whatsapp-close:hover {
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        @keyframes pulseWhatsApp {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 0 20px rgba(37, 211, 102, 0.6); 
            }
            50% { 
                transform: scale(1.1); 
                box-shadow: 0 0 30px rgba(37, 211, 102, 0.8); 
            }
        }

        /* ===== Enhanced Features Section ===== */
        .feature-card {
            background: linear-gradient(145deg, var(--bg-card), var(--bg-secondary));
            border: 2px solid transparent;
            background: linear-gradient(var(--bg-card), var(--bg-card)) padding-box,
                        linear-gradient(135deg, var(--primary-blue), transparent) border-box;
        }

        .feature-card:hover {
            background: linear-gradient(145deg, var(--bg-secondary), var(--bg-card));
            border-color: var(--primary-blue);
            box-shadow: var(--glow-blue);
        }

        .feature-icon {
            background: var(--gradient-primary);
            box-shadow: var(--glow-blue);
        }

        /* ===== Services - Enhanced ===== */
        .country-card {
            background: linear-gradient(145deg, var(--bg-card), var(--bg-secondary));
            border: 2px solid transparent;
            background: linear-gradient(var(--bg-card), var(--bg-card)) padding-box,
                        linear-gradient(135deg, var(--primary-blue), transparent) border-box;
        }

        .country-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--glow-blue), var(--shadow-lg);
        }

        .country-card.saudi::before {
            background: var(--gradient-saudi);
            height: 6px;
        }

        .country-card.uae::before {
            background: var(--gradient-uae);
            height: 6px;
        }

        .country-icon {
            background: var(--gradient-primary);
            box-shadow: var(--glow-blue);
        }

        /* ===== Scroll Indicator ===== */
        .scroll-indicator {
            background: var(--gradient-primary);
            box-shadow: var(--glow-blue);
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* ===== Loading Animations ===== */
        .loading-spinner {
            border: 3px solid rgba(37, 99, 235, 0.3);
            border-top-color: var(--primary-blue);
            animation: spin 1s linear infinite;
        }

        /* ===== Particle Effects ===== */
        .particle {
            position: absolute;
            background: var(--primary-cyan);
            border-radius: 50%;
            pointer-events: none;
            animation: floatParticle linear infinite;
        }

        @keyframes floatParticle {
            to {
                transform: translateY(-100vh) rotate(360deg);
            }
        }

        /* ===== Mobile Menu Button ===== */
        .mobile-menu-toggle {
            display: none;
            background: var(--gradient-primary);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: var(--radius-lg);
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 9999999;
            box-shadow: var(--glow-blue);
            transition: all var(--transition-normal);
        }

        .mobile-menu-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 0 25px rgba(37, 99, 235, 0.8);
        }

        /* ===== Mobile Sidebar Navigation ===== */
        .mobile-sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100vh;
            background: var(--bg-secondary);
            z-index: 999999;
            transition: right var(--transition-normal);
            box-shadow: var(--shadow-xl);
            overflow-y: auto;
            padding: 2rem 1.5rem;
        }

        .mobile-sidebar.active {
            right: 0;
        }

        .mobile-sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-normal);
            backdrop-filter: blur(5px);
        }

        .mobile-sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .mobile-nav-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            padding: 1rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: all var(--transition-normal);
            background: var(--bg-primary);
        }

        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            background: var(--gradient-primary);
            color: white;
            transform: translateX(-5px);
        }

        .mobile-nav-link i {
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }

        .mobile-sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .mobile-sidebar-title {
            font-size: 1.3rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .close-mobile-menu {
            background: var(--gradient-primary);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal);
        }

        .close-mobile-menu:hover {
            transform: rotate(90deg);
            box-shadow: var(--glow-blue);
        }

        /* ===== Accessibility ===== */
        :focus-visible {
            outline: 3px solid var(--primary-cyan);
            outline-offset: 3px;
            border-radius: var(--radius-sm);
        }

        /* ===== بقية الأنماط الأساسية (بدون تغيير) ===== */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 0.875rem 0;
            z-index: var(--z-fixed);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: transform var(--transition-normal);
        }

        .brand-logo:hover {
            transform: translateY(-2px);
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .logo-text {
            text-align: right;
        }

        .logo-english {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .logo-english span {
            color: var(--primary-blue);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            position: relative;
        }

        .hero-section {
            padding: 9rem 0 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        .categories-nav {
            background: var(--bg-secondary);
            padding: 1rem 0;
            position: sticky;
            top: 76px;
            z-index: var(--z-sticky);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
            transition: top 0.3s ease;
        }

        .categories-container {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            -webkit-overflow-scrolling: touch;
        }

        .category-btn {
            padding: 0.75rem 1.25rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-full);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all var(--transition-normal);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: inherit;
            text-decoration: none;
            flex-shrink: 0;
        }

        .works-section {
            padding: 3rem 0;
        }

        .works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
            opacity: 1;
            transition: opacity var(--transition-normal);
        }

        .work-media {
            height: 250px;
            position: relative;
            overflow: hidden;
            background: var(--bg-primary);
        }

        .work-media img,
        .work-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .work-card:hover .work-media img,
        .work-card:hover .work-media video {
            transform: scale(1.1);
        }

        .work-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .work-category {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            background: var(--bg-primary);
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.8rem;
            align-self: flex-start;
        }

        .work-title {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 0.8rem;
            color: var(--text-primary);
            line-height: 1.4;
        }

        .work-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            flex: 1;
        }

        .work-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .work-date {
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .work-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .work-btn {
            padding: 0.5rem 1rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all var(--transition-normal);
            cursor: pointer;
            font-family: inherit;
            white-space: nowrap;
        }

        .file-viewer-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: var(--z-modal);
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-normal);
            padding: 1rem;
        }

        .file-viewer-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .file-viewer-content {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            overflow: hidden;
            animation: modalIn 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 2px solid var(--primary-blue);
        }

        .toast-container {
            position: fixed;
            top: 100px;
            left: 32px;
            z-index: var(--z-modal);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .toast {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-lg);
            border-right: 4px solid var(--primary-blue);
            animation: slideInRight 0.3s ease;
            max-width: 350px;
        }

        .whatsapp-float-old {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: var(--z-fixed);
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===== بقية الأنماط (Services, Features, Footer, etc.) ===== */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 600;
        }

        .services-overview {
            padding: 4rem 0;
            background: var(--bg-primary);
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 3rem;
            color: var(--text-primary);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 50%;
            transform: translateX(50%);
            width: 60px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
        }

        .countries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .country-card {
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .country-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .country-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            flex-shrink: 0;
        }

        .country-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .country-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-top: 0.25rem;
        }

        .services-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .service-item {
            background: var(--bg-primary);
            padding: 1rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
        }

        .service-item:hover {
            border-color: var(--primary-blue);
            background: rgba(37, 99, 235, 0.05);
            transform: translateX(-5px);
        }

        .service-item i {
            color: var(--primary-blue);
            margin-left: 0.5rem;
        }

        .features-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            padding: 2rem;
            text-align: center;
            border-radius: var(--radius-xl);
            transition: all var(--transition-normal);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .footer {
            background: var(--bg-secondary);
            padding: 3rem 0 1.5rem;
            border-top: 1px solid var(--border-color);
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h4 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* تحسينات للشاشات المتوسطة */
        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                padding: 0 1.5rem;
            }
            
            .works-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .countries-grid {
                grid-template-columns: 1fr;
            }
            
            .services-list {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .whatsapp-dropdown {
                width: 280px;
            }
        }

        /* تحسينات للأجهزة اللوحية */
        @media (max-width: 1024px) and (orientation: landscape) {
            .hero-section {
                padding: 7rem 0 3rem;
            }
            
            .work-media {
                height: 180px;
            }
        }

        /* تحسينات للأجهزة الصغيرة جداً */
        @media (max-width: 360px) {
            .hero-title {
                font-size: 1.7rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
            }
            
            .category-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }
            
            .work-media {
                height: 180px;
            }
            
            .work-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }
            
            .whatsapp-float {
                bottom: 15px;
                left: 15px;
            }
            
            .whatsapp-float-btn {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
            
            .whatsapp-dropdown {
                width: 250px;
                bottom: 60px;
            }
            
            .theme-toggle-btn,
            .mobile-theme-toggle {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        /* تحسينات لللمس على الجوال */
        @media (hover: none) and (pointer: coarse) {
            .work-card:hover {
                transform: none;
                animation: none;
            }
            
            .work-card:active {
                transform: scale(0.98);
            }
            
            .category-btn:active,
            .work-btn:active,
            .nav-link:active {
                transform: scale(0.95);
            }
            
            .service-item:hover {
                transform: none;
            }
            
            .service-item:active {
                transform: translateX(-3px);
            }
            
            .theme-toggle-btn:active,
            .mobile-theme-toggle:active {
                transform: rotate(30deg) scale(0.95);
            }
        }

        /* ===== Responsive Design - Mobile Sidebar ===== */
        @media (max-width: 1024px) {
            /* إظهار زر القائمة الجانبية */
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                z-index: 1001;
            }
            
            /* إخفاء قائمة التنقل العادية */
            .nav-links {
                display: none;
            }
            
            /* تعديلات للهيدر */
            .header-content {
                gap: 1rem;
            }
            
            .brand-logo {
                gap: 0.8rem;
            }
            
            .logo-icon {
                width: 50px;
                height: 50px;
            }
            
            .logo-arabic {
                font-size: 1.4rem;
            }
            
            .logo-english {
                font-size: 0.9rem;
            }
            
            /* تعديلات للمحتوى */
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .works-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .countries-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
            }
            
            .works-grid {
                grid-template-columns: 1fr;
            }
            
            .work-media {
                height: 200px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .country-card {
                padding: 1.5rem;
            }
            
            .country-title {
                font-size: 1.5rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
                  }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }
            
            .hero-section {
                padding: 7rem 0 3rem;
            }
            
            .hero-title {
                font-size: 1.6rem;
            }
            
            .categories-container {
                padding: 0.5rem 0;
            }
            
            .category-btn {
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
            }
            
            .work-media {
                height: 180px;
            }
            
            .work-content {
                padding: 1rem;
            }
            
            .work-title {
                font-size: 1.1rem;
            }
            
            .work-description {
                font-size: 0.85rem;
            }
            
            .mobile-sidebar {
                width: 250px;
            }
        }
        
        /* Responsive Design for WhatsApp Dropdown */
      
        /* ===== WhatsApp Simple Version ===== */
.whatsapp-float-simple {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.whatsapp-float-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
    border: none;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(37, 211, 102, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 1001;
}

.whatsapp-float-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(37, 211, 102, 0.7);
}

.whatsapp-numbers {
    position: absolute;
    bottom: 70px;
    left: 0;
    display: none;
    flex-direction: column;
    gap: 10px;
    animation: slideUp 0.3s ease;
    z-index: 1000;
}

.whatsapp-numbers.show {
    display: flex;
}

.whatsapp-number-btn {
    padding: 12px 20px;
    border-radius: 25px;
    border: none;
    font-family: 'Noto Kufi Arabic', sans-serif;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    min-width: 200px;
}

.whatsapp-number-btn.saudi {
    background: var(--saudi-green);
    color: white;
}

.whatsapp-number-btn.uae {
    background: var(--uae-red);
    color: white;
}

.whatsapp-number-btn:hover {
    transform: translateX(10px);
    opacity: 0.9;
}

.toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary-blue);
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    z-index: 9999;
    display: none;
    font-weight: 600;
    animation: fadeInOut 3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInOut {
    0% { opacity: 0; top: 0; }
    10% { opacity: 1; top: 20px; }
    90% { opacity: 1; top: 20px; }
    100% { opacity: 0; top: 0; }
}

/* تحسينات للجوال */
@media (max-width: 768px) {
    .whatsapp-float-simple {
        bottom: 15px;
        left: 15px;
    }
    
    .whatsapp-float-btn {
        width: 55px;
        height: 55px;
        font-size: 24px;
    }
    
    .whatsapp-number-btn {
        padding: 10px 16px;
        font-size: 13px;
        min-width: 180px;
    }
}

@media (max-width: 480px) {
    .whatsapp-float-simple {
        bottom: 10px;
        left: 10px;
    }
    
    .whatsapp-float-btn {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }
}
    </style>
</head>
<body class="dark-mode">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="sr-only">تخطي إلى المحتوى الرئيسي</a>

    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Mobile Sidebar Overlay -->
    <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

    <!-- Mobile Sidebar Navigation -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <div class="mobile-sidebar-title">القائمة</div>
            <button class="close-mobile-menu" id="closeMobileMenu" aria-label="إغلاق القائمة">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="mobile-nav-links" role="navigation" aria-label="التنقل الجانبي">
            <!-- Theme Toggle for Mobile -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1rem;">
                <button class="mobile-theme-toggle" id="mobileThemeToggle" aria-label="تبديل الوضع الليلي/النهاري">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun" style="display: none;"></i>
                </button>
                <span style="color: var(--text-primary); font-size: 0.9rem;">تبديل الوضع</span>
            </div>

            <a href="?filter=all&category=<?= urlencode($category) ?>" 
               class="mobile-nav-link filter-btn <?= $filter == 'all' ? 'active' : '' ?>" 
               data-filter="all">
                <i class="fas fa-th" aria-hidden="true"></i>
                جميع الأعمال
            </a>

            <a href="?filter=uae&category=<?= urlencode($category) ?>" 
               class="mobile-nav-link filter-btn <?= $filter == 'uae' ? 'active' : '' ?>" 
               data-filter="uae">
                <i class="fas fa-building" aria-hidden="true"></i>
                أعمال إماراتية
            </a>
            
            <a href="?filter=saudi&category=<?= urlencode($category) ?>" 
               class="mobile-nav-link filter-btn <?= $filter == 'saudi' ? 'active' : '' ?>" 
               data-filter="saudi">
                <i class="fas fa-landmark" aria-hidden="true"></i>
                أعمال سعودية
            </a>
            
            <!-- روابط إضافية للقائمة الجانبية -->
            <a href="#services" class="mobile-nav-link">
                <i class="fas fa-graduation-cap"></i>
                خدماتنا
            </a>
            
            <a href="#features" class="mobile-nav-link">
                <i class="fas fa-star"></i>
                مميزاتنا
            </a>
            
            <a href="https://wa.me/971553353672" 
               class="mobile-nav-link"
               target="_blank"
               rel="noopener noreferrer">
                <i class="fab fa-whatsapp"></i>
                تواصل معنا
            </a>
        </nav>
    </div>

    <!-- Main Header -->
    <header class="main-header" role="banner">
        <div class="container">
            <div class="header-content">
                <a href="#" class="brand-logo" aria-label="الصفحة الرئيسية - FUTURE LEADERS ACADEMY">
                    <div class="logo-icon" aria-hidden="true">
                        <img src="1.png" style="width: 100%; height: 100%; object-fit: contain;" alt="شعار الأكاديمية">
                    </div>
                    <div class="logo-text">
                        <div class="logo-arabic"> أكاديمية قادة المستقبل</div>
                        <div class="logo-english">FUTURE LEADERS <span>ACADEMY</span></div>
                    </div>
                </a>
                
                <!-- Desktop Navigation -->
                <nav class="nav-links" role="navigation" aria-label="التنقل الرئيسي">
                    <!-- Theme Toggle Button -->
                    <button class="theme-toggle-btn" id="themeToggle" aria-label="تبديل الوضع الليلي/النهاري">
                        <i class="fas fa-moon"></i>
                        <i class="fas fa-sun"></i>
                    </button>

                    <a href="?filter=all&category=<?= urlencode($category) ?>" class="nav-link filter-btn <?= $filter == 'all' ? 'active' : '' ?>" data-filter="all">
                        <i class="fas fa-th" aria-hidden="true"></i>
                        جميع الأعمال
                    </a>

                    <a href="?filter=uae&category=<?= urlencode($category) ?>" class="nav-link filter-btn <?= $filter == 'uae' ? 'active' : '' ?>" data-filter="uae">
                        <i class="fas fa-building" aria-hidden="true"></i>
                        أعمال إماراتية
                    </a>
                    <a href="?filter=saudi&category=<?= urlencode($category) ?>" class="nav-link filter-btn <?= $filter == 'saudi' ? 'active' : '' ?>" data-filter="saudi">
                        <i class="fas fa-landmark" aria-hidden="true"></i>
                        أعمال سعودية
                    </a>
                </nav>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="فتح القائمة الجانبية">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section" id="main-content">
        <div class="container">
            <h1 class="hero-title typewriter">أكاديمية قادة المستقبل</h1>
            <p class="hero-subtitle">
                استكشف إبداعاتنا من الأعمال التعليمية والإدارية للمعلمين والمعلمات في الإمارات العربية المتحدة  
                والخدمات الأكاديمية المتخصصة للطلاب والطالبات في المملكة العربية السعودية 
            </p>
        </div>
    </section>

    <!-- Categories Navigation -->
    <nav class="categories-nav" aria-label="فئات الأعمال">
        <div class="container">
            <div class="categories-container" id="categoriesContainer">
                <?php foreach ($categories as $cat): ?>
                    <a href="?filter=<?= urlencode($filter) ?>&category=<?= urlencode($cat) ?>" 
                       class="category-btn <?= $category == $cat ? 'active' : '' ?>"
                       data-category="<?= htmlspecialchars($cat) ?>">
                        <i class="fas <?= 
                            $cat == 'all' ? 'fa-th' : 
                            ($cat == 'ملفات المعلمين' ? 'fa-folder-open' : 
                            ($cat == 'الخطط العلاجية' ? 'fa-heartbeat' :
                            ($cat == 'عروض بوربوينت' ? 'fa-desktop' :
                            ($cat == 'بحوث جامعية ورسائل' ? 'fa-graduation-cap' :
                            ($cat == 'حل منصات تعليمية' ? 'fa-laptop-code' :
                            ($cat == 'ترجمة أكاديمية' ? 'fa-language' :
                            ($cat == 'تقارير وواجبات' ? 'fa-file-alt' : 'fa-tag'))))))) 
                        ?>" aria-hidden="true"></i>
                        <?= $cat == 'all' ? 'جميع الفئات' : htmlspecialchars($cat) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <!-- Works Section -->
    <section class="works-section" id="worksSection">
        <div class="container">
            <!-- Works Grid -->
            <div class="works-grid" id="worksGrid" role="list" aria-label="قائمة الأعمال">
                <?php if (empty($works)): ?>
                    <div class="empty-state" style="text-align: center; padding: 4rem 2rem; background: var(--bg-card); border-radius: var(--radius-xl); border: 2px dashed var(--border-color); grid-column: 1 / -1;">
                        <i class="fas fa-box-open" aria-hidden="true" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.5;"></i>
                        <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--text-primary);">لا توجد أعمال في هذه الفئة</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem; max-width: 500px; margin-left: auto; margin-right: auto;">جرب اختيار فئة أخرى أو تصفح جميع الأعمال المتاحة</p>
                        <a href="?filter=all&category=all" class="category-btn" style="margin-top: 1rem; display: inline-flex;">
                            <i class="fas fa-th" aria-hidden="true"></i>
                            عرض جميع الأعمال
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($works as $index => $work): ?>
                        <?php 
                        $countryName = $work['country'] === 'saudi' ? 'سعودي' : 'إماراتي';
                        $countryClass = $work['country'] === 'saudi' ? 'saudi' : 'uae';
                        $fileType = Helper::getFileType($work['file_extension']);
                        $features = is_array($work['features']) ? $work['features'] : [];
                        // تصحيح مسار الملف باستخدام الدالة المُحسَّنة
                        $correctedMediaUrl = getCorrectedMediaUrl($work['media_url']);
                        ?>
                        <article class="work-card" data-work-id="<?= $work['id'] ?>">
                            <div class="work-media" role="button" tabindex="0" 
                                 onclick="openFileViewer(<?= $work['id'] ?>,'<?= htmlspecialchars($work['title']) ?>','<?= $correctedMediaUrl ?>','<?= $fileType ?>')"
                                 onkeypress="if(event.key === 'Enter') openFileViewer(<?= $work['id'] ?>,'<?= htmlspecialchars($work['title']) ?>','<?= $correctedMediaUrl ?>','<?= $fileType ?>')">
                                <?php if ($fileType === 'image'): ?>
                                    <img src="<?= $correctedMediaUrl ?>" 
                                         alt="<?= htmlspecialchars($work['title']) ?>" 
                                         loading="lazy"
                                         onerror="this.src='https://via.placeholder.com/400x250?text=صورة+غير+متوفرة'">
                                <?php elseif ($fileType === 'video'): ?>
                                    <video src="<?= $correctedMediaUrl ?>" 
                                           preload="metadata"
                                           poster="https://cdn.pixabay.com/photo/2017/08/10/03/47/video-2617511_1280.jpg">
                                    </video>
                                <?php else: ?>
                                    <div class="document-preview" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; background: var(--bg-primary);">
                                        <div class="document-icon <?= $fileType ?>" style="width: 80px; height: 80px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin-bottom: 1rem; background: var(--gradient-primary);">
                                            <i class="fas <?= Helper::getFileIcon($fileType) ?>" aria-hidden="true" style="color: white;"></i>
                                        </div>
                                        <div class="document-info" style="text-align: center;">
                                            <div class="document-title" style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($work['title']) ?></div>
                                            <div class="document-subtitle" style="font-size: 0.85rem; color: var(--text-secondary);"><?= Helper::getTypeName($fileType) ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <span class="work-badge <?= $countryClass ?>" aria-label="بلد العمل: <?= $countryName ?>" style="position: absolute; top: 10px; left: 10px;">
                                    <?= $countryName ?>
                                </span>
                                
                                <?php if ($work['featured']): ?>
                                    <span class="work-badge featured" aria-label="عمل مميز" style="position: absolute; top: 10px; right: 10px; background: var(--gradient-primary);">مميز</span>
                                <?php endif; ?>
                                
                                <?php if ($fileType !== 'image' && $fileType !== 'video'): ?>
                                    <span class="media-type <?= $fileType ?>" aria-label="نوع الملف: <?= Helper::getTypeName($fileType) ?>" style="position: absolute; bottom: 10px; left: 10px;">
                                        <i class="fas <?= Helper::getFileIcon($fileType) ?>" aria-hidden="true"></i>
                                        <?= Helper::getTypeName($fileType) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="work-content">
                                <span class="work-category" aria-label="فئة العمل: <?= htmlspecialchars($work['category']) ?>">
                                    <i class="fas fa-tag" aria-hidden="true"></i>
                                    <?= htmlspecialchars($work['category']) ?>
                                </span>
                                
                                <h3 class="work-title"><?= htmlspecialchars($work['title']) ?></h3>
                                
                                <p class="work-description"><?= htmlspecialchars($work['description']) ?></p>
                                
                                <?php if (!empty($features)): ?>
                                    <ul class="work-features" aria-label="مميزات العمل" style="list-style: none; padding: 0; margin: 0 0 1rem 0;">
                                        <?php foreach ($features as $feature): ?>
                                            <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem; font-size: 0.85rem; color: var(--text-secondary);">
                                                <i class="fas fa-check-circle" style="color: var(--accent-emerald); font-size: 0.8rem;"></i>
                                                <?= htmlspecialchars($feature) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <div class="work-meta">
                                    <div class="work-date" aria-label="تاريخ العمل">
                                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                                        <?= Helper::formatDate($work['date']) ?>
                                    </div>
                                    
                                    <div class="work-actions">
                                        <button class="work-btn <?= $fileType ?>" 
                                                onclick="openFileViewer(<?= $work['id'] ?>,'<?= htmlspecialchars($work['title']) ?>','<?= $correctedMediaUrl ?>','<?= $fileType ?>')"
                                                aria-label="<?= $fileType === 'image' ? 'عرض الصورة' : ($fileType === 'video' ? 'تشغيل الفيديو' : 'فتح الملف') ?>">
                                            <i class="fas <?= $fileType === 'image' ? 'fa-expand' : ($fileType === 'video' ? 'fa-play' : Helper::getFileIcon($fileType)) ?>" 
                                               aria-hidden="true"></i>
                                            <?= $fileType === 'image' ? 'عرض الصورة' : ($fileType === 'video' ? 'تشغيل الفيديو' : 'فتح الملف') ?>
                                        </button>
                                        
                                        <a href="<?= Helper::getWhatsAppUrl('مرحبًا، أرغب في الحصول على خدمة مشابهة لـ "' . $work['title'] . '"') ?>" 
                                           class="work-btn" 
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           aria-label="طلب عمل مشابه عبر واتساب">
                                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                            طلب مماثل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    

    <!-- File Viewer Modal -->
    <div class="file-viewer-modal" id="fileViewerModal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="عارض الملفات">
        <div class="file-viewer-content">
            <button class="close-viewer" onclick="closeFileViewer()" aria-label="إغلاق" style="position: absolute; top: 10px; left: 10px; background: none; border: none; color: var(--text-primary); font-size: 1.5rem; cursor: pointer; z-index: 10; padding: 0.5rem; border-radius: var(--radius-sm); background: rgba(0,0,0,0.5);">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            
            <div class="file-viewer-header" style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                <h3 class="file-viewer-title" id="fileViewerTitle" style="margin: 0; font-size: 1.2rem;">عرض الملف</h3>
            </div>
            
            <div class="file-viewer-body" id="fileViewerBody" style="flex: 1; padding: 1rem; overflow: auto; display: flex; align-items: center; justify-content: center;">
                <!-- Content will be dynamically inserted here -->
            </div>
            
            <div class="file-viewer-actions" id="fileViewerActions" style="padding: 1rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <!-- Action buttons will be dynamically inserted here -->
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <section class="hero-section" id="main-content">
        <div class="container">
            <h1 class="hero-title">خدمات تعليمية متكاملة للطلاب والمعلمين</h1>
            <p class="hero-subtitle">
                نقدم خدمات تعليمية وإدارية متكاملة لطلاب الجامعات في الإمارات والمعلمين والمعلمات في المملكة العربية السعودية.
                خبرة كبيرة في مجال التعليم مع ضمان أعلى جودة وأفضل الأسعار.
            </p>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">+500</div>
                    <div class="stat-label">عمل منجز</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">رضا العملاء</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">دعم متواصل</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">+3</div>
                    <div class="stat-label">سنوات خبرة</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Overview -->
    <section class="services-overview" id="services">
        <div class="container">
            <h2 class="section-title">خدماتنا المتكاملة</h2>
            
            <div class="countries-grid">
                <!-- UAE Services - UPDATED -->
                <div class="country-card uae">
                    <div class="country-header">
                        <div class="country-icon uae">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <h3 class="country-title">خدمات الإمارات 🇦🇪</h3>
                            <p class="country-subtitle">خدمات أكاديمية متكاملة لطلاب الجامعات والدراسات العليا</p>
                        </div>
                    </div>
                    
                    <div class="services-list">
                        <!-- البحوث الأكاديمية -->
                        <div class="service-item">
                            <i class="fas fa-graduation-cap"></i>
                            بحوث جامعية ورسائل ماجستير
                            <span class="text-muted">أعلى التقديرات ومنهجية علمية دقيقة</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-file-contract"></i>
                            بحوث تخرج ودراسات عليا
                            <span class="text-muted">دكتوراة وماستر متخصصة</span>
                        </div>
                        
                        <!-- التقارير والعروض -->
                        <div class="service-item">
                            <i class="fas fa-file-alt"></i>
                            تقارير وبوربوينت احترافية
                            <span class="text-muted">عروض تقديمية مميزة وجذابة</span>
                        </div>
                        
                        <!-- المشاريع والأعمال -->
                        <div class="service-item">
                            <i class="fas fa-project-diagram"></i>
                            بروجكتات وأعمال تخرج متكاملة
                            <span class="text-muted">تصميم وتنفيذ وإخراج احترافي</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-tasks"></i>
                            اسايمنتات وواجبات أكاديمية
                            <span class="text-muted">دقة في الإنجاز والتسليم</span>
                        </div>
                        
                        <!-- الدراسات والتحليل -->
                        <div class="service-item">
                            <i class="fas fa-book"></i>
                            كيس ستدي وتحليل حالات دراسية
                            <span class="text-muted">تحليل عميق ومنهجي</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-edit"></i>
                            تلخيص ومراجعة وكتابة أبحاث
                            <span class="text-muted">عمل وكتابة متخصصة</span>
                        </div>
                        
                        <!-- الترجمة -->
                        <div class="service-item">
                            <i class="fas fa-language"></i>
                            ترجمة أكاديمية احترافية
                            <span class="text-muted">عربي/إنجليزي/فرنسي - معتمدة</span>
                        </div>
                        
                        <!-- فحص الانتحال -->
                        <div class="service-item">
                            <i class="fas fa-shield-alt"></i>
                            فحص Turnitin وضمان الأصالة
                            <span class="text-muted">ملف PDF معتمد خالٍ من النسخ</span>
                        </div>
                        
                        <!-- المنصات التعليمية -->
                        <div class="service-item">
                            <i class="fas fa-laptop-code"></i>
                            حل منصات التعليم الإلكتروني
                            <span class="text-muted">Alef, LMS, Canvas, Blackboard</span>
                        </div>
                        
                        <!-- الاختبارات -->
                        <div class="service-item">
                            <i class="fas fa-clipboard-check"></i>
                            الاختبارات الوزارية والمنازل
                            <span class="text-muted">EMSAT والاختبارات المحوسبة</span>
                        </div>
                        
                        <!-- التعليم عن بعد -->
                        <div class="service-item">
                            <i class="fas fa-chalkboard-teacher"></i>
                            تعليم أونلاين لجميع المراحل
                            <span class="text-muted">عربي/إنجليزي/فرنسي - جميع المواد</span>
                        </div>
                        
                        <!-- خدمات إضافية -->
                        <div class="service-item">
                            <i class="fas fa-star"></i>
                            ضمان أعلى التقديرات
                            <span class="text-muted">خبرة كبيرة بالكليات والجامعات</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-bolt"></i>
                            تسليم سريع وجودة عالية
                            <span class="text-muted">أسعار مناسبة ومرنة للجميع</span>
                        </div>
                    </div>
                    <br>
                    <div class="service-item">
                        <i class="fas fa-home"></i>
                        تصميم فلل ومنازل
                        <span class="text-muted">مخططات معمارية - إنشائية - صحية - كهربائية - ميكانيكية - طاقة</span>
                    </div>
                    <br>
                    <div class="service-item">
                        <i class="fas fa-cube"></i>
                        تصميم واجهات 3D
                        <span class="text-muted">واجهتين ثلاثية الأبعاد بجودة عالية</span>
                    </div>
                    <br>
                    <div class="service-item">
                        <i class="fas fa-tree"></i>
                        تنسيق حدائق 3D
                        <span class="text-muted">تصميم حدائق وحوش ثلاثية الأبعاد</span>
                    </div>
                    <br>
                    
                    <!-- ملاحظة هامة -->
                    <div style="margin-top: 2rem; padding: 1rem; background: rgba(37, 99, 235, 0.1); border-radius: var(--radius-lg); border-right: 3px solid var(--primary-blue);">
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">
                            <i class="fas fa-info-circle" style="color: var(--primary-blue); margin-left: 0.5rem;"></i>
                            <strong>ملاحظة:</strong> جميع الخدمات تشمل متابعة مستمرة، تعديلات مجانية، وضمان الجودة. خبرة كبيرة في التعامل مع مختلف الجامعات والكليات بالإمارات.
                        </p>
                    </div>
                </div>
                
                <!-- Saudi Services - UPDATED -->
                <div class="country-card saudi">
                    <div class="country-header">
                        <div class="country-icon saudi">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div>
                            <h3 class="country-title">خدمات السعودية 🇸🇦</h3>
                            <p class="country-subtitle">خدمات إدارية وتعليمية متكاملة للمعلمين والمعلمات - شغل يبيض الوجه ♥️</p>
                        </div>
                    </div>
                    
                    <div class="services-list">
                        <!-- الملفات الإدارية -->
                        <div class="service-item">
                            <i class="fas fa-folder"></i>
                            ملف الأداء الوظيفي
                            <span class="text-muted">ورقي وإلكتروني - جميع النماذج</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-files"></i>
                            ملف نافس لجميع الصفوف
                            <span class="text-muted">كامل ومنسق وجاهز</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-trophy"></i>
                            ملف الإنجاز والإنتاج المعرفي
                            <span class="text-muted">ورقي وإلكتروني - متكامل</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-user-tie"></i>
                            ملف مساعد إداري
                            <span class="text-muted">منسق وجاهز للاستخدام</span>
                        </div>
                        
                        <!-- التصاميم والإنتاج المرئي -->
                        <div class="service-item">
                            <i class="fas fa-paint-brush"></i>
                            تصاميم ولوحات إعلانية
                            <span class="text-muted">فيديوهات - احترافية وجذابة</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-video"></i>
                            فيديوهات تخرج واحتفالات
                            <span class="text-muted">إخراج احترافي وجودة عالية</span>
                        </div>
                        
                        <!-- الخطط والبرامج -->
                        <div class="service-item">
                            <i class="fas fa-calendar-alt"></i>
                            خطط أسبوعية وفصلية
                            <span class="text-muted">منسقة وجاهزة - جميع المواد</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-stethoscope"></i>
                            خطط علاجية وإثرائية
                            <span class="text-muted">متنوعة وشاملة - جميع الصفوف</span>
                        </div>
                        
                        <div class="service-item {
                            <i class="fas fa-chart-line"></i>
                            خطة التحسين والتشغيلية
                            <span class="text-muted">احترافية وقابلة للتطبيق</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-chart-bar"></i>
                            تحليل نتائج وتقارير إحصائية
                            <span class="text-muted">دقيقة ومفصلة - مهاراتي</span>
                        </div>
                        
                        <!-- السجلات والتقارير -->
                        <div class="service-item">
                            <i class="fas fa-clipboard-list"></i>
                            جميع السجلات وسجلات المتابعة
                            <span class="text-muted">شاملة ومنظمة</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-user-graduate"></i>
                            سجل التوجيه والإرشاد الطلابي
                            <span class="text-muted">موجه طلابي - موجه صحي</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-users"></i>
                            سجل رائدة النشاط واللجان
                            <span class="text-muted">مجالس وجان - نشاطات</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-heart"></i>
                            سجل العمل التطوعي والمبادرات
                            <span class="text-muted">مبادرات إبداعية ومتميزة</span>
                        </div>
                        
                        <!-- الشهادات والمبادرات -->
                        <div class="service-item">
                            <i class="fas fa-award"></i>
                            شهادات تقدير وشكر
                            <span class="text-muted">تصاميم مميزة واحترافية</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-lightbulb"></i>
                            مبادرات ومجتمعات مهنية
                            <span class="text-muted">إبداعية - تنموية - تطويرية</span>
                        </div>
                        
                        <!-- خدمات متنوعة -->
                        <div class="service-item">
                            <i class="fas fa-file-powerpoint"></i>
                            عروض بوربوينت
                            <span class="text-muted">تصاميم جذابة ومحتوى ثري</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-file-alt"></i>
                            سيرة ذاتية وبحث إجرائي
                            <span class="text-muted">احترافية ومنهجية</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-book"></i>
                            ملفات الموهوبات والتحصيل الدراسي
                            <span class="text-muted">متابعة وتطوير</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-balance-scale"></i>
                            ملف التقويم الذاتي والانضباط
                            <span class="text-muted">تقارير وتحليلات</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-handshake"></i>
                            ملف الشراكة المجتمعية
                            <span class="text-muted">برامج وشراكات</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-chart-pie"></i>
                            ملف التحصيل الدراسي والموهوبات
                            <span class="text-muted">خطط ومتابعات</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-comments"></i>
                            ملف الخدمات الإرشادية
                            <span class="text-muted">برامج إرشادية متكاملة</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-graduation-cap"></i>
                            ملف فاقد تعليمي
                            <span class="text-muted">علاج ومتابعة</span>
                        </div>
                        
                        <div class="service-item">
                            <i class="fas fa-tasks"></i>
                            تقارير نشاطات وبرامج
                            <span class="text-muted">شاملة ومفصلة</span>
                        </div>
                    </div>
                    
                    <!-- ملاحظة هامة -->
                    <div style="margin-top: 2rem; padding: 1rem; background: rgba(5, 150, 105, 0.1); border-radius: var(--radius-lg); border-right: 3px solid var(--saudi-green);">
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">
                            <i class="fas fa-heart" style="color: var(--saudi-green); margin-left: 0.5rem;"></i>
                            <strong>ملاحظة:</strong> 🎗️ أسعار تناسب الكل 💐 - شغل يبيض الوجه ♥️ - جميع الخدمات تشمل متابعة مستمرة وتعديلات مجانية. خبرة كبيرة في التعامل مع إدارات المدارس والمعلمين في السعودية.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">لماذا تختارنا؟</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3 class="feature-title">جودة عالية</h3>
                    <p class="feature-description">
                        نضمن لك أعلى معايير الجودة والدقة في جميع الأعمال، مع مراعاة أحدث المناهج والمعايير الأكاديمية.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">تسليم في الوقت المحدد</h3>
                    <p class="feature-description">
                        نلتزم بالمواعيد النهائية ونضمن تسليم العمل في الوقت المتفق عليه، مع متابعة مستمرة للتقدم.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">فحص الاقتباس</h3>
                    <p class="feature-description">
                        نقدم فحص Turnitin مع كل بحث جامعي، ونسلم ملف PDF يؤكد خلو العمل من الانتحال والنسخ.
                    </p>
                </div>
                
                <div class="feature-card {
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">دعم متواصل 24/7</h3>
                    <p class="feature-description">
                        فريق دعم فني متاح على مدار الساعة للرد على استفساراتك وتقديم المساعدة الفورية عبر الواتساب.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="feature-title">أسعار مناسبة</h3>
                    <p class="feature-description">
                        نقدم أسعاراً تناسب الجميع، مع عروض وتخفيضات خاصة للعملاء الدائمين والطلبات الكبيرة.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="feature-title">خصوصية وأمان</h3>
                    <p class="feature-description">
                        نحافظ على سرية معلوماتك وخصوصية أعمالك، ولا نشارك أي بيانات مع أطراف ثالثة.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>عن الأكاديمية</h4>
                    <p>نقدم خدمات تعليمية وإدارية متكاملة لطلاب الجامعات والمعلمين في السعودية والإمارات.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 أكاديمية قادة المستقبل - Future Leaders Academy. جميع الحقوق محفوظة.</p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    شغل يبيض الوجه ♥️ - أسعار تناسب الكل 💐
                </p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp with Dropdown -->
    
        
        <!-- WhatsApp Dropdown Menu -->
        <!-- WhatsApp Button - SIMPLIFIED VERSION -->
<div class="whatsapp-float-simple" id="whatsappFloat">
    <!-- Main WhatsApp Button -->
    <button class="whatsapp-float-btn" id="whatsappMainBtn" 
            onclick="toggleWhatsAppMenu()"
            aria-label="تواصل معنا عبر واتساب">
        <i class="fab fa-whatsapp"></i>
    </button>
    
    <!-- WhatsApp Numbers (Hidden initially) -->
    <div class="whatsapp-numbers" id="whatsappNumbers">
        <!-- Saudi Number -->
        <button class="whatsapp-number-btn saudi" 
                onclick="sendToWhatsApp('966582529631', 'السعودية')">
            <i class="fas fa-flag"></i>
            السعودية: +966582529631
        </button>
        
        <!-- UAE Number -->
        <button class="whatsapp-number-btn uae" 
                onclick="sendToWhatsApp('971553353672', 'الإمارات')">
            <i class="fas fa-flag"></i>
            الإمارات: +971553353672
        </button>
    </div>
</div>

<!-- Toast Container -->
<div id="toast" class="toast"></div>
    </div>

    <script>
        // ===== THEME TOGGLE FUNCTIONALITY =====
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const mobileThemeToggle = document.getElementById('mobileThemeToggle');
            const body = document.body;
            
            // التحقق من تفضيلات المستخدم المحفوظة
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
                updateThemeIcons('light');
            } else {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
                updateThemeIcons('dark');
            }
            
            // دالة لتحديث الأيقونات
            function updateThemeIcons(theme) {
                const moonIcons = document.querySelectorAll('.fa-moon');
                const sunIcons = document.querySelectorAll('.fa-sun');
                
                if (theme === 'light') {
                    moonIcons.forEach(icon => icon.style.display = 'none');
                    sunIcons.forEach(icon => icon.style.display = 'block');
                } else {
                    moonIcons.forEach(icon => icon.style.display = 'block');
                    sunIcons.forEach(icon => icon.style.display = 'none');
                }
            }
            
            // دالة لتبديل الوضع
            function toggleTheme() {
                if (body.classList.contains('light-mode')) {
                    // التبديل إلى الوضع الليلي
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                    updateThemeIcons('dark');
                    showToast('تم التبديل إلى الوضع الليلي', 'success');
                } else {
                    // التبديل إلى الوضع النهاري
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    localStorage.setItem('theme', 'light');
                    updateThemeIcons('light');
                    showToast('تم التبديل إلى الوضع النهاري', 'success');
                }
            }
            
            // إضافة المستمعين للأحداث
            if (themeToggle) {
                themeToggle.addEventListener('click', toggleTheme);
            }
            
            if (mobileThemeToggle) {
                mobileThemeToggle.addEventListener('click', function() {
                    toggleTheme();
                    // إغلاق القائمة الجانبية بعد التبديل
                    closeMobileSidebar();
                });
            }
            
            // ===== MOBILE SIDEBAR FUNCTIONALITY =====
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileSidebar = document.getElementById('mobileSidebar');
            const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
            const closeMobileMenu = document.getElementById('closeMobileMenu');
            
            // Function to open mobile sidebar
            function openMobileSidebar() {
                mobileSidebar.classList.add('active');
                mobileSidebarOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            
            // Function to close mobile sidebar
            function closeMobileSidebar() {
                mobileSidebar.classList.remove('active');
                mobileSidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Event listeners for mobile sidebar
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openMobileSidebar();
                });
            }
            
            if (closeMobileMenu) {
                closeMobileMenu.addEventListener('click', closeMobileSidebar);
            }
            
            if (mobileSidebarOverlay) {
                mobileSidebarOverlay.addEventListener('click', closeMobileSidebar);
            }
            
            // Close sidebar when clicking on a link
            document.querySelectorAll('.mobile-nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeMobileSidebar();
                });
            });
            
            // Close sidebar with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileSidebar.classList.contains('active')) {
                    closeMobileSidebar();
                }
            });
            
            // ===== TOUCH GESTURES FOR MOBILE SIDEBAR =====
            let touchStartX = 0;
            let touchEndX = 0;
            
            if (mobileSidebar) {
                mobileSidebar.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                });
                
                mobileSidebar.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                });
            }
            
            function handleSwipe() {
                const swipeThreshold = 50;
                const swipeDistance = touchEndX - touchStartX;
                
                // Swipe left to close (if sidebar is open and user swipes left)
                if (swipeDistance < -swipeThreshold && mobileSidebar.classList.contains('active')) {
                    closeMobileSidebar();
                }
            }
            
            // ===== ENHANCED MOBILE INTERACTIONS =====
            
            // Improve touch feedback for mobile
            document.querySelectorAll('.work-card, .category-btn, .work-btn, .mobile-nav-link, .theme-toggle-btn, .mobile-theme-toggle').forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                    this.style.transition = 'transform 0.1s ease';
                });
                
                element.addEventListener('touchend', function() {
                    this.style.transform = '';
                    this.style.transition = '';
                });
                
                element.addEventListener('touchcancel', function() {
                    this.style.transform = '';
                    this.style.transition = '';
                });
            });
            
            // Prevent zoom on double tap on iOS
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(event) {
                const now = Date.now();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            
            // ===== RESPONSIVE BEHAVIOR =====
            
            // Close sidebar on window resize to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024 && mobileSidebar.classList.contains('active')) {
                    closeMobileSidebar();
                }
            });
            
            // ===== AUTO-CLOSE SIDEBAR WHEN CLICKING OUTSIDE =====
            document.addEventListener('click', function(event) {
                if (mobileSidebar.classList.contains('active') && 
                    !mobileSidebar.contains(event.target) && 
                    !mobileMenuToggle.contains(event.target) &&
                    window.innerWidth <= 1024) {
                    closeMobileSidebar();
                }
            });
            
            // ===== TYPEWRITER ANIMATION FOR HERO TITLE =====
            const heroTitle = document.querySelector('.hero-title.typewriter');
            if (heroTitle) {
                const text = heroTitle.textContent;
                heroTitle.textContent = '';
                heroTitle.style.cssText = `
                    overflow: hidden;
                    border-right: 3px solid var(--primary-cyan);
                    white-space: nowrap;
                    margin: 0 auto;
                    letter-spacing: 0.15em;
                    display: inline-block;
                `;
                
                let i = 0;
                const typeWriter = () => {
                    if (i < text.length) {
                        heroTitle.textContent += text.charAt(i);
                        i++;
                        setTimeout(typeWriter, 100);
                    } else {
                        setTimeout(() => {
                            heroTitle.style.borderRight = 'none';
                        }, 1000);
                    }
                };
                
                // Start typing after page loads
                setTimeout(typeWriter, 1000);
            }
            
            // ===== ENHANCED FILE VIEWER FOR MOBILE =====
            window.openFileViewer = async function(workId, title, fileUrl, fileType) {
                try {
                    document.getElementById('fileViewerTitle').textContent = title || 'عرض الملف';
                    
                    let content = '';
                    let actions = '';
                    
                    const correctedUrl = correctMediaUrl(fileUrl);
                    
                    if (fileType === 'image') {
                        content = `<img src="${correctedUrl}" alt="${title}" style="width:100%; height:auto; max-height:70vh; object-fit:contain;" onerror="this.src='https://via.placeholder.com/800x600?text=صورة+غير+متوفرة'">`;
                    } else if (fileType === 'video') {
                        content = `
                            <video controls style="width:100%; height:auto; max-height:70vh;" playsinline>
                                <source src="${correctedUrl}" type="video/mp4">
                                متصفحك لا يدعم تشغيل الفيديو.
                            </video>
                        `;
                    } else if (fileType === 'pdf') {
                        content = `<iframe src="${correctedUrl}" style="width:100%; height:70vh; border:none;"></iframe>`;
                    } else {
                        const icon = getFileIcon(fileType);
                        const typeName = getTypeName(fileType);
                        
                        content = `
                            <div style="text-align:center; padding:2rem;">
                                <div style="width:100px;height:100px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2.5rem;color:white;">
                                    <i class="fas ${icon}"></i>
                                </div>
                                <h3 style="margin-bottom:0.5rem;font-size:1.2rem;">${title}</h3>
                                <p style="color:var(--text-secondary);margin-bottom:1.5rem;">${typeName} - ${fileType}</p>
                                <p style="color:var(--text-muted);font-size:0.9rem;">
                                    هذا ملف ${typeName}. انقر على الزر أدناه لفتحه أو تحميله.
                                </p>
                            </div>
                        `;
                    }
                    
                    actions = `
                        <a href="${correctedUrl}" 
                           target="_blank" 
                           class="work-btn"
                           style="flex:1;text-align:center;justify-content:center;"
                           onclick="showToast('جاري فتح الملف...', 'info')">
                            <i class="fas ${fileType === 'image' ? 'fa-expand' : (fileType === 'video' ? 'fa-play' : 'fa-external-link-alt')}"></i>
                            فتح الملف
                        </a>
                        <button class="work-btn" 
                                onclick="closeFileViewer()"
                                style="flex:1;text-align:center;justify-content:center;">
                            <i class="fas fa-times"></i>
                            إغلاق
                        </button>
                    `;
                    
                    document.getElementById('fileViewerBody').innerHTML = content;
                    document.getElementById('fileViewerActions').innerHTML = actions;
                    
                    const modal = document.getElementById('fileViewerModal');
                    modal.setAttribute('aria-hidden', 'false');
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                } catch (error) {
                    console.error('Error opening file viewer:', error);
                    showToast('حدث خطأ في فتح الملف', 'error');
                }
            };
            
            // ===== SWIPE TO CLOSE FILE VIEWER ON MOBILE =====
            let touchStartX2 = 0;
            let touchStartY2 = 0;
            const fileViewerModal = document.getElementById('fileViewerModal');
            
            if (fileViewerModal && window.innerWidth <= 768) {
                fileViewerModal.addEventListener('touchstart', function(e) {
                    touchStartX2 = e.touches[0].clientX;
                    touchStartY2 = e.touches[0].clientY;
                });
                
                fileViewerModal.addEventListener('touchmove', function(e) {
                    if (!touchStartX2 || !touchStartY2) return;
                    
                    const touchX = e.touches[0].clientX;
                    const touchY = e.touches[0].clientY;
                    
                    const diffX = touchStartX2 - touchX;
                    const diffY = touchStartY2 - touchY;
                    
                    // Only trigger if vertical swipe is minimal and horizontal swipe is significant
                    if (Math.abs(diffY) < 50 && Math.abs(diffX) > 100) {
                        closeFileViewer();
                        touchStartX2 = 0;
                        touchStartY2 = 0;
                    }
                });
            }
            
            // ===== CREATE PARTICLES ANIMATION =====
            function createParticles() {
                const container = document.createElement('div');
                container.className = 'particles-container';
                container.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    pointer-events: none;
                    z-index: -1;
                    overflow: hidden;
                `;
                document.body.appendChild(container);
                
                const colors = ['#2563EB', '#06B6D4', '#8B5CF6', '#10B981', '#EC4899'];
                
                for (let i = 0; i < 30; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.cssText = `
                        position: absolute;
                        width: ${Math.random() * 5 + 2}px;
                        height: ${Math.random() * 5 + 2}px;
                        background: ${colors[Math.floor(Math.random() * colors.length)]};
                        border-radius: 50%;
                        left: ${Math.random() * 100}vw;
                        top: 100vh;
                        opacity: ${Math.random() * 0.5 + 0.2};
                        animation: floatParticle ${Math.random() * 20 + 10}s linear infinite;
                        animation-delay: ${Math.random() * 5}s;
                    `;
                    container.appendChild(particle);
                }
            }
            
            // Initialize particle background
            createParticles();
            
            // ===== RIPPLE EFFECT FOR BUTTONS =====
            function addRippleEffect() {
                document.querySelectorAll('.nav-link, .category-btn, .work-btn, .contact-btn, .mobile-nav-link, .theme-toggle-btn, .mobile-theme-toggle').forEach(button => {
                    button.addEventListener('click', function(e) {
                        const rect = this.getBoundingClientRect();
                        const ripple = document.createElement('span');
                        ripple.className = 'ripple-effect';
                        ripple.style.cssText = `
                            position: absolute;
                            border-radius: 50%;
                            background: rgba(37, 99, 235, 0.4);
                            transform: scale(0);
                            animation: ripple-animation 0.6s linear;
                            pointer-events: none;
                        `;
                        
                        const size = Math.max(rect.width, rect.height);
                        ripple.style.width = ripple.style.height = size + 'px';
                        ripple.style.left = e.clientX - rect.left - size / 2 + 'px';
                        ripple.style.top = e.clientY - rect.top - size / 2 + 'px';
                        
                        this.style.position = 'relative';
                        this.style.overflow = 'hidden';
                        this.appendChild(ripple);
                        
                        setTimeout(() => ripple.remove(), 600);
                    });
                });
            }
            
            // Add ripple effect to buttons
            addRippleEffect();
            
            // ===== SCROLL ANIMATIONS =====
            function initScrollAnimations() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                document.querySelectorAll('.work-card, .country-card, .feature-card').forEach(card => {
                    observer.observe(card);
                });
            }
            
            // Initialize scroll animations
            initScrollAnimations();
            
            // ===== WhatsApp Dropdown Functionality =====
            const whatsappBtn = document.getElementById('whatsappMainBtn');
            const whatsappDropdown = document.getElementById('whatsappDropdown');
            const whatsappFloat = document.getElementById('whatsappFloat');
            
            // Toggle dropdown on button click
            if (whatsappBtn && whatsappDropdown) {
                whatsappBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    whatsappDropdown.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!whatsappFloat.contains(e.target)) {
                        whatsappDropdown.classList.remove('active');
                    }
                });
                
                // Close dropdown on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && whatsappDropdown.classList.contains('active')) {
                        whatsappDropdown.classList.remove('active');
                    }
                });
                
                // Close dropdown when a number is clicked (after a short delay)
                document.querySelectorAll('.whatsapp-option').forEach(option => {
                    option.addEventListener('click', function() {
                        setTimeout(() => {
                            whatsappDropdown.classList.remove('active');
                        }, 300);
                    });
                });
                
                // Handle touch events for mobile
                if ('ontouchstart' in window) {
                    let touchStartX = 0;
                    let touchStartY = 0;
                    
                    whatsappFloat.addEventListener('touchstart', function(e) {
                        touchStartX = e.touches[0].clientX;
                        touchStartY = e.touches[0].clientY;
                    });
                    
                    whatsappFloat.addEventListener('touchend', function(e) {
                        const touchEndX = e.changedTouches[0].clientX;
                        const touchEndY = e.changedTouches[0].clientY;
                        
                        const diffX = Math.abs(touchEndX - touchStartX);
                        const diffY = Math.abs(touchEndY - touchStartY);
                        
                        // If it's a tap (not a swipe)
                        if (diffX < 10 && diffY < 10) {
                            e.preventDefault();
                            whatsappDropdown.classList.toggle('active');
                        }
                    });
                }
            }
            
            // Add hover effect for desktop
            if (window.innerWidth > 768) {
                whatsappFloat.addEventListener('mouseenter', function() {
                    whatsappDropdown.classList.add('active');
                });
                
                whatsappFloat.addEventListener('mouseleave', function() {
                    setTimeout(() => {
                        if (!whatsappFloat.matches(':hover') && !whatsappDropdown.matches(':hover')) {
                            whatsappDropdown.classList.remove('active');
                        }
                    }, 300);
                });
            }
            
            // Detect system theme preference
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            const prefersLightScheme = window.matchMedia('(prefers-color-scheme: light)');
            
            // Listen for system theme changes
            prefersDarkScheme.addEventListener('change', function(e) {
                if (e.matches && !localStorage.getItem('theme')) {
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    updateThemeIcons('dark');
                }
            });
            
            prefersLightScheme.addEventListener('change', function(e) {
                if (e.matches && !localStorage.getItem('theme')) {
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    updateThemeIcons('light');
                }
            });
        });

        function closeFileViewer() {
            const modal = document.getElementById('fileViewerModal');
            modal.setAttribute('aria-hidden', 'true');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            document.getElementById('fileViewerBody').innerHTML = '';
            document.getElementById('fileViewerActions').innerHTML = '';
        }

        function correctMediaUrl(url) {
            if (url.includes('http://localhost/future_leaders_academy/')) {
                url = url.replace('http://localhost/future_leaders_academy/', '');
            }
            if (!url.startsWith('uploads/') && url.includes('uploads/')) {
                const parts = url.split('uploads/');
                if (parts.length > 1) {
                    url = 'uploads/' + parts[1];
                }
            }
            return url;
        }

        function getFileIcon(fileType) {
            const icons = {
                'pdf': 'fa-file-pdf',
                'presentation': 'fa-file-powerpoint',
                'document': 'fa-file-word',
                'spreadsheet': 'fa-file-excel',
                'archive': 'fa-file-archive',
                'image': 'fa-file-image',
                'video': 'fa-file-video'
            };
            return icons[fileType] || 'fa-file';
        }

        function getTypeName(fileType) {
            const names = {
                'pdf': 'ملف PDF',
                'presentation': 'عرض تقديمي',
                'document': 'مستند نصي',
                'spreadsheet': 'جدول بيانات',
                'archive': 'ملف مضغوط',
                'image': 'صورة',
                'video': 'فيديو'
            };
            return names[fileType] || 'ملف';
        }

        // ===== TOAST SYSTEM =====
        function showToast(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toastContainer');
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const titles = {
                success: 'تم بنجاح',
                error: 'خطأ',
                warning: 'تحذير',
                info: 'ملاحظة'
            };
            
            toast.innerHTML = `
                <div style="display:flex;align-items:flex-start;gap:1rem;">
                    <div style="color:var(--primary-blue);font-size:1.2rem;">
                        <i class="fas ${icons[type]}"></i>
                    </div>
                    <div style="flex:1;">
                        <h4 style="margin:0 0 0.3rem 0;font-size:0.95rem;color:var(--text-primary);">${titles[type]}</h4>
                        <p style="margin:0;font-size:0.85rem;color:var(--text-secondary);line-height:1.4;">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0.2rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, duration);
        }

        // Keyboard Navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('fileViewerModal');
                if (modal.classList.contains('active')) {
                    closeFileViewer();
                }
            }
        });

        // Image error handling
        document.querySelectorAll('img[src*="uploads/"]').forEach(img => {
            img.onerror = function() {
                this.src = 'https://via.placeholder.com/400x250?text=صورة+غير+متوفرة';
                this.onerror = null;
            };
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            @keyframes floatParticle {
                to {
                    transform: translateY(-100vh) rotate(360deg);
                }
            }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .toast-container {
                    left: 16px;
                    right: 16px;
                }
                
                .toast {
                    max-width: 100%;
                    padding: 1rem;
                }
            }
        `;
        document.head.appendChild(style);
        // WhatsApp Functions
let whatsappMenuOpen = false;

function toggleWhatsAppMenu() {
    const numbersDiv = document.getElementById('whatsappNumbers');
    const mainBtn = document.getElementById('whatsappMainBtn');
    
    whatsappMenuOpen = !whatsappMenuOpen;
    
    if (whatsappMenuOpen) {
        numbersDiv.classList.add('show');
        mainBtn.style.transform = 'rotate(15deg) scale(1.1)';
        showToast('اختر الرقم المناسب');
    } else {
        numbersDiv.classList.remove('show');
        mainBtn.style.transform = '';
    }
}

function sendToWhatsApp(phoneNumber, country) {
    // إغلاق القائمة
    const numbersDiv = document.getElementById('whatsappNumbers');
    const mainBtn = document.getElementById('whatsappMainBtn');
    numbersDiv.classList.remove('show');
    whatsappMenuOpen = false;
    mainBtn.style.transform = '';
    
    // إنشاء رابط الواتساب
    const whatsappUrl = `https://wa.me/${phoneNumber}`;
    
    // إظهار رسالة
    showToast(`يتم فتح واتساب ${country}...`);
    
    // فتح الرابط (سيعمل على الجوال والكمبيوتر)
    setTimeout(() => {
        window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
    }, 500);
}

function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// إغلاق قائمة الواتساب عند النقر خارجها
document.addEventListener('click', function(e) {
    const whatsappFloat = document.getElementById('whatsappFloat');
    const numbersDiv = document.getElementById('whatsappNumbers');
    const mainBtn = document.getElementById('whatsappMainBtn');
    
    if (whatsappMenuOpen && 
        !whatsappFloat.contains(e.target) && 
        !mainBtn.contains(e.target)) {
        numbersDiv.classList.remove('show');
        whatsappMenuOpen = false;
        mainBtn.style.transform = '';
    }
});

// إغلاق بالزر Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && whatsappMenuOpen) {
        const numbersDiv = document.getElementById('whatsappNumbers');
        const mainBtn = document.getElementById('whatsappMainBtn');
        
        numbersDiv.classList.remove('show');
        whatsappMenuOpen = false;
        mainBtn.style.transform = '';
    }
});
    </script>
</body>
</html>
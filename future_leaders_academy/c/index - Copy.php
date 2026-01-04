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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="معرض الأعمال الإبداعية - FUTURE LEADERS ACADEMY">
    <title>معرض الأعمال - FUTURE LEADERS ACADEMY</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 
 <style>
        /* ===== Enhanced File Preview Styles ===== */
.work-media {
    height: 250px;
    position: relative;
    overflow: hidden;
    background: var(--bg-primary);
    cursor: pointer;
    transition: transform var(--transition-slow);
}

.work-media::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, 
        transparent 0%, 
        transparent 60%, 
        rgba(0,0,0,0.3) 100%);
    z-index: 1;
    opacity: 0;
    transition: opacity var(--transition-normal);
}

.work-card:hover .work-media::before {
    opacity: 1;
}

/* Preview Container */
.preview-container {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Enhanced PDF Preview */
.pdf-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.pdf-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, 
        transparent 0%, 
        rgba(255, 71, 87, 0.1) 50%, 
        transparent 100%);
    animation: shimmer 3s infinite;
}

.pdf-preview-content {
    width: 80%;
    height: 80%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(255, 71, 87, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    z-index: 2;
    animation: float 6s ease-in-out infinite;
}

.pdf-preview-header {
    height: 35px;
    background: linear-gradient(90deg, #FF4757 0%, #FF6B81 100%);
    display: flex;
    align-items: center;
    padding: 0 15px;
    gap: 8px;
}

.pdf-preview-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: white;
    opacity: 0.9;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.pdf-preview-body {
    flex: 1;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: repeating-linear-gradient(
        transparent,
        transparent 24px,
        rgba(255, 71, 87, 0.05) 24px,
        rgba(255, 71, 87, 0.05) 25px
    );
}

.pdf-watermark {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 48px;
    font-weight: 900;
    color: rgba(255, 71, 87, 0.1);
    opacity: 0.8;
    font-family: 'Arial', sans-serif;
    transform: rotate(-15deg);
}

/* Enhanced PPT Preview */
.ppt-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.ppt-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, 
        transparent 0%, 
        rgba(236, 72, 153, 0.1) 50%, 
        transparent 100%);
    animation: shimmer 3s infinite 0.5s;
}

.ppt-preview-slide {
    width: 85%;
    height: 75%;
    background: white;
    border-radius: 16px;
    box-shadow: 0 15px 35px rgba(236, 72, 153, 0.4);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    z-index: 2;
    animation: float 6s ease-in-out infinite 1s;
}

.ppt-preview-title-bar {
    height: 45px;
    background: linear-gradient(90deg, #EC4899 0%, #F472B6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 1.1rem;
    letter-spacing: 0.5px;
}

.ppt-watermark {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 42px;
    font-weight: 900;
    color: rgba(236, 72, 153, 0.1);
    opacity: 0.8;
    font-family: 'Arial', sans-serif;
    transform: rotate(-15deg);
}

/* Enhanced Document Preview */
.doc-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.doc-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, 
        transparent 0%, 
        rgba(59, 130, 246, 0.1) 50%, 
        transparent 100%);
    animation: shimmer 3s infinite 1s;
}

.doc-preview-page {
    width: 80%;
    height: 80%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
    padding: 30px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    position: relative;
    z-index: 2;
    animation: float 6s ease-in-out infinite 2s;
}

.doc-preview-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(transparent 95%, rgba(59, 130, 246, 0.05) 100%);
    background-size: 100% 24px;
    pointer-events: none;
}

.doc-watermark {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 44px;
    font-weight: 900;
    color: rgba(59, 130, 246, 0.1);
    opacity: 0.8;
    font-family: 'Arial', sans-serif;
    transform: rotate(-15deg);
}

/* Enhanced Excel Preview */
.excel-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #064e3b 0%, #10b981 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.excel-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, 
        transparent 0%, 
        rgba(16, 185, 129, 0.1) 50%, 
        transparent 100%);
    animation: shimmer 3s infinite 1.5s;
}

.excel-preview-grid {
    width: 85%;
    height: 75%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(5, 1fr);
    border: 2px solid #10B981;
    overflow: hidden;
    position: relative;
    z-index: 2;
    animation: float 6s ease-in-out infinite 0.5s;
}

.excel-preview-cell {
    border-right: 1px solid #e5e5e5;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: #444;
    padding: 8px;
    transition: all 0.2s ease;
}

.excel-preview-cell.header {
    background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}

.excel-preview-cell:nth-child(odd):not(.header) {
    background: rgba(16, 185, 129, 0.05);
}

.excel-preview:hover .excel-preview-cell:not(.header) {
    background: rgba(16, 185, 129, 0.08);
}

.excel-watermark {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 40px;
    font-weight: 900;
    color: rgba(16, 185, 129, 0.1);
    opacity: 0.8;
    font-family: 'Arial', sans-serif;
    transform: rotate(-15deg);
}

/* Enhanced Archive Preview */
.archive-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #78350f 0%, #f59e0b 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.archive-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, 
        transparent 0%, 
        rgba(245, 158, 11, 0.1) 50%, 
        transparent 100%);
    animation: shimmer 3s infinite 2s;
}

.archive-preview-files {
    width: 80%;
    height: 80%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(245, 158, 11, 0.4);
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    z-index: 2;
    animation: float 6s ease-in-out infinite 1.5s;
}

.archive-preview-file {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.archive-preview-file:hover {
    transform: translateX(5px);
    border-color: #F59E0B;
    background: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.archive-preview-file-icon {
    font-size: 1.4rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.archive-watermark {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 46px;
    font-weight: 900;
    color: rgba(245, 158, 11, 0.1);
    opacity: 0.8;
    font-family: 'Arial', sans-serif;
    transform: rotate(-15deg);
}

/* Image and Video Previews */
.image-preview-container {
    width: 100%;
    height: 100%;
    position: relative;
}

.image-preview-container img,
.image-preview-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.image-preview-container::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, 
        transparent 0%, 
        transparent 60%, 
        rgba(0,0,0,0.6) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.work-card:hover .image-preview-container::after {
    opacity: 1;
}

.work-card:hover .image-preview-container img,
.work-card:hover .image-preview-container video {
    transform: scale(1.1);
}

/* Preview Badge */
.preview-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: rgba(0, 0, 0, 0.85);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 3;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.work-card:hover .preview-badge {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

/* Preview Hover Effects */
.preview-hover-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    background: linear-gradient(transparent, rgba(0,0,0,0.9));
    color: white;
    transform: translateY(100%);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 2;
}

.work-card:hover .preview-hover-info {
    transform: translateY(0);
}

.preview-hover-info h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    font-weight: 700;
}

.preview-hover-info p {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-bottom: 10px;
}

.preview-hover-stats {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    opacity: 0.8;
}

.preview-hover-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Animation Keyframes */
@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-10px) rotate(0.5deg);
    }
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Loading Animation */
.preview-loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.preview-loading.active {
    opacity: 1;
}

.preview-loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
}
        /* ===== CSS Variables ===== */
        :root {
            /* Primary Colors */
            --primary-gold: #C9A227;
            --primary-gold-light: #E8D48A;
            --primary-gold-dark: #9A7B1A;
            
            /* Country Colors */
            --saudi-green: #006C35;
            --saudi-green-light: #009B4D;
            --uae-red: #CE1126;
            --uae-green: #009739;
            
            /* File Type Colors */
            --pdf-red: #FF4757;
            --presentation-pink: #EC4899;
            --document-blue: #3B82F6;
            --spreadsheet-green: #10B981;
            --archive-orange: #F59E0B;
            
            /* UI Colors - Light Mode */
            --bg-primary: #F8F9FC;
            --bg-secondary: #FFFFFF;
            --bg-card: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border-color: #E2E8F0;
            
            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.15);
            
            /* Gradients */
            --gradient-gold: linear-gradient(135deg, #C9A227 0%, #E8D48A 50%, #C9A227 100%);
            --gradient-saudi: linear-gradient(135deg, #006C35 0%, #009B4D 100%);
            --gradient-uae: linear-gradient(135deg, #CE1126 0%, #FF4757 100%);
            --gradient-pdf: linear-gradient(135deg, #FF4757 0%, #FF6B81 100%);
            --gradient-presentation: linear-gradient(135deg, #EC4899 0%, #F472B6 100%);
            --gradient-document: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
            
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

        /* ===== Dark Mode ===== */
        .dark-mode {
            --bg-primary: #0B0F1A;
            --bg-secondary: #111827;
            --bg-card: #1F2937;
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted: #64748B;
            --border-color: #334155;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.4);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.5);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.6);
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
            transition: background-color var(--transition-normal);
        }

        /* ===== Accessibility ===== */
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

:focus-visible {
    outline: 2px solid var(--primary-gold);
    outline-offset: 3px;
    border-radius: var(--radius-sm);
}

/* ===== Layout Components ===== */
.container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* ===== Header ===== */
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

/* Brand Logo */
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
    color: #f9f9fd;
    flex-shrink: 0;
}

.logo-text {
    text-align: right;
}

.logo-arabic {
    font-size: 1.5rem;
    font-weight: 900;
    background: var(--gradient-gold);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.logo-english {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

.logo-english span {
    color: var(--primary-gold);
}

/* Navigation */
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

.nav-link:hover,
.nav-link:focus {
    color: var(--primary-gold);
    background: rgba(201, 162, 39, 0.1);
}

.nav-link.active {
    color: var(--primary-gold);
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -10px;
    right: 50%;
    transform: translateX(50%);
    width: 20px;
    height: 3px;
    background: var(--gradient-gold);
    border-radius: var(--radius-full);
}

/* FIXED: Filter buttons should be links, not just active class */
.nav-link.filter-btn {
    cursor: pointer;
}

/* Contact Button */
.contact-btn {
    background: var(--gradient-gold);
    color: #1A1A2E;
    border: none;
    border-radius: var(--radius-md);
    padding: 0.75rem 1.5rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all var(--transition-normal);
    text-decoration: none;
    font-family: inherit;
}

.contact-btn:hover,
.contact-btn:focus {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Admin Login Button */
.admin-login-btn {
    background: var(--gradient-saudi);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    padding: 0.75rem 1.5rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all var(--transition-normal);
    text-decoration: none;
    font-family: inherit;
}

.admin-login-btn:hover,
.admin-login-btn:focus {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Refresh Button */
.refresh-btn {
    background: var(--gradient-uae);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    padding: 0.75rem 1.5rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all var(--transition-normal);
    text-decoration: none;
    font-family: inherit;
}

.refresh-btn:hover,
.refresh-btn:focus {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--text-primary);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--radius-sm);
}

.mobile-menu-toggle:hover {
    background: rgba(201, 162, 39, 0.1);
}

/* ===== Hero Section ===== */
.hero-section {
    padding: 9rem 0 4rem;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 50%, rgba(201, 162, 39, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.hero-title {
    font-size: 3rem;
    font-weight: 900;
    margin-bottom: 1rem;
    background: var(--gradient-gold);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    max-width: 800px;
    margin: 0 auto 2rem;
    line-height: 1.6;
}

/* ===== Categories Navigation ===== */
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
    scrollbar-width: thin;
    scrollbar-color: var(--primary-gold) var(--border-color);
}

.categories-container::-webkit-scrollbar {
    height: 6px;
}

.categories-container::-webkit-scrollbar-track {
    background: var(--border-color);
    border-radius: var(--radius-full);
}

.categories-container::-webkit-scrollbar-thumb {
    background: var(--primary-gold);
    border-radius: var(--radius-full);
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
    /* FIXED: Make category buttons behave like links */
    display: inline-block;
}

.category-btn:hover,
.category-btn:focus,
.category-btn.active {
    background: var(--gradient-gold);
    color: #1A1A2E;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* ===== Works Section ===== */
.works-section {
    padding: 3rem 0;
}

/* Works Grid */
.works-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 2rem;
    opacity: 1;
    transition: opacity var(--transition-normal);
}

.works-grid.loading {
    opacity: 0.5;
    pointer-events: none;
}

/* Work Card */
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
    border: 1px solid var(--border-color);
}

.work-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-gold-light);
}

.work-media {
    height: 250px;
    position: relative;
    overflow: hidden;
    background: var(--bg-primary);
}

/* Image and Video Styles */
.work-media img,
.work-media video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.work-media.loading img,
.work-media.loading video {
    opacity: 0;
}

.work-card:hover .work-media img,
.work-card:hover .work-media video {
    transform: scale(1.08);
}

/* Document Preview Styles */
.document-preview {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
}

.document-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.document-icon.pdf { color: var(--pdf-red); }
.document-icon.presentation { color: var(--presentation-pink); }
.document-icon.document { color: var(--document-blue); }
.document-icon.spreadsheet { color: var(--spreadsheet-green); }
.document-icon.archive { color: var(--archive-orange); }

.document-info {
    text-align: center;
}

.document-title {
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-size: 1.1rem;
}

.document-subtitle {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* PDF Thumbnail Preview */
.pdf-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #FF4757 0%, #FF6B81 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.pdf-preview-content {
    width: 80%;
    height: 80%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.pdf-preview-header {
    height: 30px;
    background: #FF4757;
    display: flex;
    align-items: center;
    padding: 0 10px;
    gap: 6px;
}

.pdf-preview-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: white;
    opacity: 0.8;
}

.pdf-preview-body {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pdf-preview-line {
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
}

.pdf-preview-line.short {
    width: 60%;
}

/* PPT Thumbnail Preview */
.ppt-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #EC4899 0%, #F472B6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.ppt-preview-slide {
    width: 85%;
    height: 75%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.ppt-preview-title-bar {
    height: 40px;
    background: #EC4899;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1rem;
}

.ppt-preview-content {
    flex: 1;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.ppt-preview-bullet {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ppt-preview-bullet-dot {
    width: 8px;
    height: 8px;
    background: #EC4899;
    border-radius: 50%;
}

.ppt-preview-text {
    height: 6px;
    background: #f0f0f0;
    border-radius: 3px;
    flex: 1;
}

/* Word Document Preview */
.doc-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.doc-preview-page {
    width: 80%;
    height: 80%;
    background: white;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.doc-preview-line {
    height: 10px;
    background: #e5e5e5;
    border-radius: 2px;
}

.doc-preview-line.title {
    width: 70%;
    height: 16px;
    background: #3B82F6;
}

.doc-preview-line.subtitle {
    width: 50%;
    height: 12px;
    background: #93c5fd;
}

/* Excel Preview */
.excel-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.excel-preview-grid {
    width: 85%;
    height: 75%;
    background: white;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(5, 1fr);
    border: 1px solid #10B981;
    overflow: hidden;
}

.excel-preview-cell {
    border-right: 1px solid #e5e5e5;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: #666;
}

.excel-preview-cell.header {
    background: #10B981;
    color: white;
    font-weight: bold;
}

/* Archive Preview */
.archive-preview {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.archive-preview-files {
    width: 80%;
    height: 80%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.archive-preview-file {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8f8f8;
    border-radius: 4px;
}

.archive-preview-file-icon {
    color: #F59E0B;
    font-size: 1.2rem;
}

.archive-preview-file-name {
    height: 6px;
    background: #e5e5e5;
    border-radius: 3px;
    flex: 1;
}

.work-badge {
    position: absolute;
    top: 1rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 700;
    z-index: 2;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.work-badge.saudi {
    right: 1rem;
    background: rgba(0, 108, 53, 0.9);
    color: white;
}

.work-badge.uae {
    right: 1rem;
    background: rgba(206, 17, 38, 0.9);
    color: white;
}

.work-badge.featured {
    left: 1rem;
    background: rgba(201, 162, 39, 0.9);
    color: #1A1A2E;
}

.media-type {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.media-type.pdf { background: rgba(255, 71, 87, 0.9); }
.media-type.presentation { background: rgba(236, 72, 153, 0.9); }
.media-type.document { background: rgba(59, 130, 246, 0.9); }
.media-type.spreadsheet { background: rgba(16, 185, 129, 0.9); }
.media-type.archive { background: rgba(245, 158, 11, 0.9); }

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

.work-features {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.work-features li {
    padding: 0.3rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    position: relative;
    padding-right: 1.2rem;
}

.work-features li::before {
    content: '✓';
    position: absolute;
    right: 0;
    color: var(--primary-gold);
    font-weight: bold;
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

.work-btn:hover,
.work-btn:focus {
    background: var(--gradient-gold);
    color: #1A1A2E;
    border-color: transparent;
    transform: translateY(-2px);
}

/* Special buttons for document types */
.work-btn.pdf:hover,
.work-btn.pdf:focus {
    background: var(--gradient-pdf);
    color: white;
}

.work-btn.presentation:hover,
.work-btn.presentation:focus {
    background: var(--gradient-presentation);
    color: white;
}

.work-btn.document:hover,
.work-btn.document:focus {
    background: var(--gradient-document);
    color: white;
}

/* ===== File Viewer ===== */
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
}

.file-viewer-header {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.file-viewer-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.file-viewer-body {
    flex: 1;
    overflow: auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.file-viewer-body iframe {
    width: 100%;
    height: 100%;
    min-height: 500px;
    border: none;
    background: white;
}

.file-viewer-body .document-preview-large {
    text-align: center;
    padding: 2rem;
}

.document-preview-large .document-icon {
    font-size: 6rem;
    margin-bottom: 2rem;
}

.file-viewer-actions {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.viewer-btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all var(--transition-normal);
}

.viewer-btn.open {
    background: var(--gradient-gold);
    color: #1A1A2E;
}

.viewer-btn.download {
    background: var(--gradient-saudi);
    color: white;
}

.close-viewer {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all var(--transition-normal);
    z-index: 1;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.close-viewer:hover,
.close-viewer:focus {
    background: rgba(0,0,0,0.9);
    transform: rotate(90deg);
}

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-secondary);
    max-width: 400px;
    margin: 0 auto 1.5rem;
}

/* ===== Loading Spinner ===== */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(201, 162, 39, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary-gold);
    animation: spin 1s ease-in-out infinite;
    margin-left: 10px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
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

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.4; }
    100% { transform: scale(1.3); opacity: 0; }
}

/* ===== Toast Notifications ===== */
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
    border-right: 4px solid var(--primary-gold);
    animation: slideInRight 0.3s ease;
    max-width: 350px;
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

.toast.success {
    border-right-color: #10B981;
}

.toast.error {
    border-right-color: #EF4444;
}

.toast.warning {
    border-right-color: #F59E0B;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.toast.success .toast-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.toast.error .toast-icon {
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.toast.warning .toast-icon {
    background: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.toast-message {
    flex: 1;
}

.toast-message h4 {
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.toast-message p {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.close-toast {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
    transition: color var(--transition-normal);
}

.close-toast:hover {
    color: var(--text-primary);
}

/* ===== Floating WhatsApp ===== */
.whatsapp-float {
    position: fixed;
    bottom: 30px;
    left: 30px;
    z-index: var(--z-fixed);
}

.whatsapp-float-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: #25D366;
    color: white;
    border-radius: 50%;
    font-size: 1.8rem;
    text-decoration: none;
    box-shadow: var(--shadow-lg);
    transition: all var(--transition-normal);
}

.whatsapp-float-btn:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-xl);
}

/* ===== Responsive Design ===== */
@media (max-width: 1200px) {
    .works-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
}

@media (max-width: 992px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .container {
        padding: 0 1.5rem;
    }
}

@media (max-width: 768px) {
    /* Header */
    .main-header {
        padding: 0.75rem 0;
    }
    
    .mobile-menu-toggle {
        display: block;
    }
    
    .nav-links {
        position: fixed;
        top: 76px;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        flex-direction: column;
        padding: 1rem;
        box-shadow: var(--shadow-lg);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: all var(--transition-normal);
        z-index: var(--z-dropdown);
    }
    
    .nav-links.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    .nav-link.active::after {
        bottom: -5px;
    }
    
    /* Hero */
    .hero-section {
        padding: 7rem 0 3rem;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    /* Categories */
    .categories-nav {
        top: 60px;
    }
    
    /* Works Grid */
    .works-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    /* File Viewer */
    .file-viewer-body iframe {
        min-height: 400px;
    }
    
    .file-viewer-actions {
        flex-direction: column;
    }
    
    .viewer-btn {
        width: 100%;
        justify-content: center;
    }
    
    /* WhatsApp */
    .whatsapp-float {
        bottom: 20px;
        left: 20px;
    }
    
    .whatsapp-float-btn {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 1rem;
    }
    
    .hero-title {
        font-size: 1.75rem;
    }
    
    .work-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .work-btn {
        width: 100%;
        justify-content: center;
    }
    
    .file-viewer-body iframe {
        min-height: 300px;
    }
}

/* ===== Print Styles ===== */
@media print {
    .main-header,
    .categories-nav,
    .whatsapp-float,
    .work-actions {
        display: none !important;
    }
    
    .hero-section {
        padding: 2rem 0;
    }
    
    .work-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .works-grid {
        display: block;
    }
}
    </style>
</head>
<body>
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="sr-only">تخطي إلى المحتوى الرئيسي</a>

    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Main Header -->
    <header class="main-header" role="banner">
        <div class="container">
            <div class="header-content">
                <a href="#" class="brand-logo" aria-label="الصفحة الرئيسية - FUTURE LEADERS ACADEMY">
                    <div class="logo-icon" aria-hidden="true">
                        <img src="1.png" style="width: 80px;" alt="شعار الأكاديمية">
                    </div>
                    <div class="logo-text">
                        <div class="logo-arabic"> أكاديمية قادة المستقبل</div>
                        <div class="logo-english">FUTURE LEADERS <span>ACADEMY</span></div>
                    </div>
                </a>
                
                <button class="mobile-menu-toggle" aria-label="فتح/إغلاق قائمة التنقل" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
               
                <nav class="nav-links" role="navigation" aria-label="التنقل الرئيسي">
                    <!-- FIXED: Filter buttons should be proper links -->
                    <a href="?filter=all&category=<?= urlencode($category) ?>" class="nav-link filter-btn <?= $filter == 'all' ? 'active' : '' ?>" data-filter="all">
                        <i class="fas fa-th" aria-hidden="true"></i>
                        جميع الأعمال
                    </a>
                    <a href="?filter=saudi&category=<?= urlencode($category) ?>" class="nav-link filter-btn <?= $filter == 'saudi' ? 'active' : '' ?>" data-filter="saudi">
                        <i class="fas fa-landmark" aria-hidden="true"></i>
                        أعمال سعودية
                    </a>
                    <a href="?filter=uae&category=<?= urlencode($category) ?>" class="nav-link filter-btn <?= $filter == '' ? 'active' : '' ?>" data-filter="uae">
                        <i class="fas fa-building" aria-hidden="true"></i>
                        أعمال إماراتية
                    </a>
                    
                    <!-- <button class="refresh-btn" onclick="window.location.reload()" id="refreshBtn">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i>
                        تحديث
                    </button> -->
                    
                    <!-- <button class="admin-login-btn" onclick="window.open('../index.php', '_blank')">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        دخول المدير
                    </button> -->
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section" id="main-content">
        <div class="container">
            <h1 class="hero-title">أكاديمية قادة المستقبل</h1>
            <p class="hero-subtitle">
                استكشف إبداعاتنا من الأعمال التعليمية والإدارية للمعلمين والمعلمات في المملكة العربية السعودية، 
                والخدمات الأكاديمية المتخصصة للطلاب والطالبات في الإمارات العربية المتحدة
            </p>
        </div>
    </section>

    <!-- Categories Navigation -->
    <nav class="categories-nav" aria-label="فئات الأعمال">
        <div class="container">
            <div class="categories-container" id="categoriesContainer">
                <?php foreach ($categories as $cat): ?>
                    <!-- FIXED: Category buttons should also be proper links -->
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
                    <div class="empty-state">
                        <i class="fas fa-box-open" aria-hidden="true"></i>
                        <h3>لا توجد أعمال في هذه الفئة</h3>
                        <p>جرب اختيار فئة أخرى أو تصفح جميع الأعمال المتاحة</p>
                        <a href="?filter=all&category=all" class="category-btn" style="margin-top: 1rem;">
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
                                         >
                                <?php elseif ($fileType === 'video'): ?>
                                    <video src="<?= $correctedMediaUrl ?>" 
                                           preload="metadata"
                                           poster="https://cdn.pixabay.com/photo/2017/08/10/03/47/video-2617511_1280.jpg">
                                    </video>
                                <?php else: ?>
                                    <div class="document-preview">
                                        <div class="document-icon <?= $fileType ?>">
                                            <i class="fas <?= Helper::getFileIcon($fileType) ?>" aria-hidden="true"></i>
                                        </div>
                                        <div class="document-info">
                                            <div class="document-title"><?= htmlspecialchars($work['title']) ?></div>
                                            <div class="document-subtitle"><?= Helper::getTypeName($fileType) ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <span class="work-badge <?= $countryClass ?>" aria-label="بلد العمل: <?= $countryName ?>">
                                    <?= $countryName ?>
                                </span>
                                
                                <?php if ($work['featured']): ?>
                                    <span class="work-badge featured" aria-label="عمل مميز">مميز</span>
                                <?php endif; ?>
                                
                                <?php if ($fileType !== 'image' && $fileType !== 'video'): ?>
                                    <span class="media-type <?= $fileType ?>" aria-label="نوع الملف: <?= Helper::getTypeName($fileType) ?>">
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
                                    <ul class="work-features" aria-label="مميزات العمل">
                                        <?php foreach ($features as $feature): ?>
                                            <li><?= htmlspecialchars($feature) ?></li>
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
            <button class="close-viewer" onclick="closeFileViewer()" aria-label="إغلاق">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
            
            <div class="file-viewer-header">
                <h3 class="file-viewer-title" id="fileViewerTitle">عرض الملف</h3>
            </div>
            
            <div class="file-viewer-body" id="fileViewerBody">
                <!-- Content will be dynamically inserted here -->
            </div>
            
            <div class="file-viewer-actions" id="fileViewerActions">
                <!-- Action buttons will be dynamically inserted here -->
            </div>
        </div>
    </div>
    <div>

    
    <style>

        /* ===== Accessibility ===== */
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

        :focus-visible {
            outline: 2px solid var(--primary-gold);
            outline-offset: 3px;
            border-radius: var(--radius-sm);
        }

        /* ===== Layout Components ===== */
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ===== Header ===== */
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

        /* Brand Logo */
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
             color: #f9f9fd;
            flex-shrink: 0;
        }

        .logo-text {
            text-align: right;
        }

        .logo-arabic {
            font-size: 1.5rem;
            font-weight: 900;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .logo-english {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .logo-english span {
            color: var(--primary-gold);
        }

        /* Navigation */
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

        .nav-link:hover,
        .nav-link:focus {
            color: var(--primary-gold);
            background: rgba(201, 162, 39, 0.1);
        }

        .nav-link.active {
            color: var(--primary-gold);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 50%;
            transform: translateX(50%);
            width: 20px;
            height: 3px;
            background: var(--gradient-gold);
            border-radius: var(--radius-full);
        }

        .admin-btn {
            background: var(--gradient-admin);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-normal);
            text-decoration: none;
            font-family: inherit;
        }

        .admin-btn:hover,
        .admin-btn:focus {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .whatsapp-btn-nav {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-normal);
            text-decoration: none;
            font-family: inherit;
        }

        .whatsapp-btn-nav:hover,
        .whatsapp-btn-nav:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
        }

        .mobile-menu-toggle:hover {
            background: rgba(201, 162, 39, 0.1);
        }

        /* ===== Hero Section ===== */
        .hero-section {
            padding: 9rem 0 4rem;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(201, 162, 39, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

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
            background: var(--gradient-gold);
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

        /* ===== Services Overview ===== */
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
            background: var(--gradient-gold);
            border-radius: var(--radius-full);
        }

        .countries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        @media (max-width: 768px) {
            .countries-grid {
                grid-template-columns: 1fr;
            }
        }

        .country-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .country-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .country-card.saudi::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-saudi);
        }

        .country-card.uae::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-uae);
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

        .country-icon.saudi {
            background: var(--gradient-saudi);
        }

        .country-icon.uae {
            background: var(--gradient-uae);
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
            border-color: var(--primary-gold);
            background: rgba(201, 162, 39, 0.05);
            transform: translateX(-5px);
        }

        .service-item i {
            color: var(--primary-gold);
            margin-left: 0.5rem;
        }

        .service-item .text-muted {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* ===== Features Section ===== */
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
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient-gold);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            color: #1A1A2E;
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

        /* ===== Categories Navigation ===== */
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
            scrollbar-width: thin;
            scrollbar-color: var(--primary-gold) var(--border-color);
        }

        .categories-container::-webkit-scrollbar {
            height: 6px;
        }

        .categories-container::-webkit-scrollbar-track {
            background: var(--border-color);
            border-radius: var(--radius-full);
        }

        .categories-container::-webkit-scrollbar-thumb {
            background: var(--primary-gold);
            border-radius: var(--radius-full);
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
        }

        .category-btn:hover,
        .category-btn:focus,
        .category-btn.active {
            background: var(--gradient-gold);
            color: #1A1A2E;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* ===== Works Section ===== */
        .works-section {
            padding: 3rem 0;
        }

        /* Works Grid */
        .works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
            opacity: 1;
            transition: opacity var(--transition-normal);
        }

        .works-grid.loading {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Work Card */
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
            border: 1px solid var(--border-color);
        }

        .work-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-gold-light);
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

        .work-media.loading img,
        .work-media.loading video {
            opacity: 0;
        }

        .work-media.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
            border-top-color: var(--primary-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .work-card:hover .work-media img,
        .work-card:hover .work-media video {
            transform: scale(1.08);
        }

        .work-badge {
            position: absolute;
            top: 1rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 700;
            z-index: 2;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .work-badge.saudi {
            right: 1rem;
            background: rgba(0, 108, 53, 0.9);
            color: white;
        }

        .work-badge.uae {
            right: 1rem;
            background: rgba(206, 17, 38, 0.9);
            color: white;
        }

        .work-badge.featured {
            left: 1rem;
            background: rgba(201, 162, 39, 0.9);
            color: #1A1A2E;
        }

        .media-type {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
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

        .work-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .work-features li {
            padding: 0.3rem 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
            position: relative;
            padding-right: 1.2rem;
        }

        .work-features li::before {
            content: '✓';
            position: absolute;
            right: 0;
            color: var(--primary-gold);
            font-weight: bold;
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

        .work-btn:hover,
        .work-btn:focus {
            background: var(--gradient-gold);
            color: #1A1A2E;
            border-color: transparent;
            transform: translateY(-2px);
        }

        .whatsapp-action-btn {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.5rem 1rem;
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

        .whatsapp-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .admin-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed var(--border-color);
        }

        .edit-btn {
            background: var(--gradient-admin);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all var(--transition-normal);
        }

        .edit-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .delete-btn {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all var(--transition-normal);
        }

        .delete-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .empty-state p {
            color: var(--text-secondary);
            max-width: 400px;
            margin: 0 auto 1.5rem;
        }

        /* ===== Pricing Section ===== */
        .pricing-section {
            padding: 4rem 0;
            background: var(--bg-primary);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .pricing-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 2px solid var(--border-color);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-gold);
        }

        .pricing-card.featured {
            border-color: var(--primary-gold);
            box-shadow: var(--shadow-lg);
        }

        .featured-badge {
            position: absolute;
            top: 1rem;
            left: -2rem;
            background: var(--gradient-gold);
            color: #1A1A2E;
            padding: 0.5rem 2rem;
            transform: rotate(-45deg);
            font-weight: 700;
            font-size: 0.8rem;
        }

        .pricing-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: 900;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 1.5rem 0;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
            text-align: right;
        }

        .pricing-features li {
            padding: 0.5rem 0;
            color: var(--text-secondary);
            position: relative;
            padding-right: 1.5rem;
        }

        .pricing-features li::before {
            content: '✓';
            position: absolute;
            right: 0;
            color: var(--primary-gold);
            font-weight: bold;
        }

        .pricing-features li.disabled {
            color: var(--text-muted);
            text-decoration: line-through;
        }

        .pricing-features li.disabled::before {
            content: '✗';
            color: var(--text-muted);
        }

        /* ===== Contact Section ===== */
        .contact-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            height: 40%;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            align-items: center;
        }

        .contact-info {
            text-align: right;
        }

        .contact-title {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .contact-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .contact-details {
            margin-top: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
        }

        .contact-item:hover {
            border-color: var(--primary-gold);
            transform: translateX(-5px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-gold);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #1A1A2E;
            flex-shrink: 0;
        }

        .contact-text h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .contact-text p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .contact-form {
            background: var(--bg-card);
            padding: 2.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

          .works-section {
            padding: 3rem 0;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all var(--transition-normal);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-btn {
            background: var(--gradient-gold);
            color: #1A1A2E;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all var(--transition-normal);
            font-family: inherit;
            font-size: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* ===== Footer ===== */
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

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color var(--transition-normal);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: var(--primary-gold);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* ===== Floating WhatsApp ===== */
        .whatsapp-float {
            position: fixed;
            bottom: 32px;
            left: 32px;
            z-index: var(--z-tooltip);
        }

        .whatsapp-float-btn {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            box-shadow: var(--shadow-lg);
            transition: all var(--transition-normal);
            text-decoration: none;
            position: relative;
        }

        .whatsapp-float-btn::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: rgba(37, 211, 102, 0.3);
            animation: pulse 2s ease-out infinite;
        }

        .whatsapp-float-btn:hover,
        .whatsapp-float-btn:focus {
            transform: scale(1.1);
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

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.4; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1200px) {
            .works-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .container {
                padding: 0 1.5rem;
            }
        }

        @media (max-width: 768px) {
            /* Header */
            .main-header {
                padding: 0.75rem 0;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .nav-links {
                position: fixed;
                top: 76px;
                left: 0;
                right: 0;
                background: var(--bg-secondary);
                flex-direction: column;
                padding: 1rem;
                box-shadow: var(--shadow-lg);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all var(--transition-normal);
                z-index: var(--z-dropdown);
            }
            
            .nav-links.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-link.active::after {
                bottom: -5px;
            }
            
            /* Hero */
            .hero-section {
                padding: 7rem 0 3rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .hero-stats {
                gap: 2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            /* Categories */
            .categories-nav {
                top: 60px;
            }
            
            /* Works Grid */
            .works-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            /* Services */
            .countries-grid {
                grid-template-columns: 1fr;
            }
            
            .country-card {
                padding: 1.5rem;
            }
            
            .services-list {
                grid-template-columns: 1fr;
            }
            
            /* Contact */
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            /* Floating WhatsApp */
            .whatsapp-float {
                bottom: 20px;
                left: 20px;
            }
            
            .whatsapp-float-btn {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }
            
            .hero-title {
                font-size: 1.75rem;
            }
            
            .country-title {
                font-size: 1.5rem;
            }
            
            .work-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .work-item-actions {
                align-self: flex-end;
            }
            
            .pricing-card {
                padding: 1.5rem;
            }
        }
    </style>
<
   
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
                <!-- UAE Services -->
                <div class="country-card uae">
                    <div class="country-header">
                        <div class="country-icon uae">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <h3 class="country-title">خدمات الإمارات 🇦🇪</h3>
                            <p class="country-subtitle">خدمات أكاديمية متخصصة لطلاب الجامعات</p>
                        </div>
                    </div>
                    
                    <div class="services-list">
                        <div class="service-item">
                            <i class="fas fa-graduation-cap"></i>
                            بحوث جامعية / ماستر
                            <span class="text-muted">بفحص Turnitin وملف PDF</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-file-alt"></i>
                            تقارير وبوربوينت
                            <span class="text-muted">عروض احترافية مميزة</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-project-diagram"></i>
                            بروجكتات واسايمنتات
                            <span class="text-muted">بأعلى التقديرات</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-tasks"></i>
                            واجبات وتلخيص
                            <span class="text-muted">دقة وسرعة في التنفيذ</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-book"></i>
                            كيس ستدي وبحوث تخرج
                            <span class="text-muted">خبرة كبيرة في التخصصات</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-language"></i>
                            ترجمة متخصصة
                            <span class="text-muted">عربي/إنجليزي/فرنسي</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-university"></i>
                            رسائل الماجستير والدكتوراة
                            <span class="text-muted">منهجية علمية دقيقة</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-laptop-code"></i>
                            حل منصات التعليم
                            <span class="text-muted">Alef, LMS, Canvas</span>
                        </div>
                    </div>
                </div>
                
                <!-- Saudi Services -->
                <div class="country-card saudi">
                    <div class="country-header">
                        <div class="country-icon saudi">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div>
                            <h3 class="country-title">خدمات السعوديين 🇸🇦</h3>
                            <p class="country-subtitle">خدمات إدارية وتعليمية متكاملة</p>
                        </div>
                    </div>
                    
                    <div class="services-list">
                        <div class="service-item">
                            <i class="fas fa-folder"></i>
                            ملف الأداء الوظيفي
                            <span class="text-muted">ورقي وإلكتروني</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-paint-brush"></i>
                            تصاميم ولوحات إعلانية
                            <span class="text-muted">فيديوهات واحترافية</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-calendar-alt"></i>
                            خطط أسبوعية وفصلية
                            <span class="text-muted">منسقة وجاهزة</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-file-powerpoint"></i>
                            عروض بوربوينت
                            <span class="text-muted">تصاميم جذابة</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-trophy"></i>
                            ملفات إنجاز وإنتاج معرفي
                            <span class="text-muted">ورقي وإلكتروني</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-stethoscope"></i>
                            خطط علاجية وإثرائية
                            <span class="text-muted">متنوعة وشاملة</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-award"></i>
                            شهادات وسجلات متابعة
                            <span class="text-muted">جميع النماذج</span>
                        </div>
                        <div class="service-item">
                            <i class="fas fa-users"></i>
                            مجتمعات مهنية ومبادرات
                            <span class="text-muted">إبداعية ومتميزة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
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
                
                <div class="feature-card">
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
                
            <div class="footer-bottom">
                <p>© 2024 أكاديمية قادة المستقبل - Future Leaders Academy. جميع الحقوق محفوظة.</p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    شغل يبيض الوجه ♥️ - أسعار تناسب الكل 💐
                </p>
            </div>
        </div>
    </footer>



    

    </div>

    <!-- Floating WhatsApp -->
    <div class="whatsapp-float">
        <a href="https://wa.me/971553353672" 
           class="whatsapp-float-btn" 
           target="_blank"
           rel="noopener noreferrer"
           aria-label="تواصل معنا عبر واتساب">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
        </a>
    </div>

    <script>
        // ===== CONFIGURATION =====
        const CONFIG = {
            siteUrl: '<?= SITE_URL ?>',
            whatsappNumber: '971553353672'
        };

        // ===== دالة لتصحيح مسار الملفات في الجافاسكريبت =====
        function correctMediaUrl(url) {
            // إذا كان المسار يحتوي على localhost، قم بتصحيحه
            if (url.includes('http://localhost/future_leaders_academy/')) {
                url = url.replace('http://localhost/future_leaders_academy/', '');
            }
            // التأكد من أن المسار يبدأ بـ uploads/
            if (!url.startsWith('uploads/') && url.includes('uploads/')) {
                const parts = url.split('uploads/');
                if (parts.length > 1) {
                    url = 'uploads/' + parts[1];
                }
            }
            return url;
        }

        // ===== FILE VIEWER FUNCTIONS =====
        async function openFileViewer(workId, title, fileUrl, fileType) {
            try {
                // زيادة عدد المشاهدات
                await fetch(`api/increment-views.php?id=${workId}`);
                
                // تحديث العنوان
                document.getElementById('fileViewerTitle').textContent = title || 'عرض الملف';
                
                // تحديث المحتوى
                let content = '';
                let actions = '';
                
                const correctedUrl = correctMediaUrl(fileUrl);
                
                if (fileType === 'image') {
                    content = `<img src="${correctedUrl}" alt="${title}" style="max-width:100%; max-height:100%; object-fit:contain;" onerror="this.src='https://via.placeholder.com/800x600?text=صورة+غير+متوفرة'">`;
                } else if (fileType === 'video') {
                    content = `
                        <video controls style="max-width:100%; max-height:100%;" autoplay>
                            <source src="${correctedUrl}" type="video/mp4">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                    `;
                } else if (fileType === 'pdf') {
                    content = `<iframe src="${correctedUrl}" style="width:100%; height:500px; border:none;"></iframe>`;
                } else {
                    const icon = getFileIcon(fileType);
                    const typeName = getTypeName(fileType);
                    
                    content = `
                        <div class="document-preview-large">
                            <div class="document-icon ${fileType}">
                                <i class="fas ${icon}"></i>
                            </div>
                            <h3>${title}</h3>
                            <p>${typeName} - ${fileType}</p>
                            <p style="color:var(--text-muted); margin-top:2rem;">
                                هذا ملف ${typeName}. انقر على الزر أدناه لفتحه أو تحميله.
                            </p>
                        </div>
                    `;
                }
                
                // إعداد الأزرار
                actions = `
                    <a href="${correctedUrl}" 
                       target="_blank" 
                       class="viewer-btn open"
                       onclick="showToast('جاري فتح الملف...', 'info')">
                        <i class="fas ${fileType === 'image' ? 'fa-expand' : (fileType === 'video' ? 'fa-play' : 'fa-external-link-alt')}"></i>
                        فتح الملف
                    </a>
                    
                    
                `;
                // <a href="api/download.php?id=${workId}" 
                //        class="viewer-btn download"
                //        onclick="showToast('جاري تحميل الملف...', 'info'); incrementDownloads(${workId});">
                //         <i class="fas fa-download"></i>
                //         تحميل الملف
                //     </a>
                document.getElementById('fileViewerBody').innerHTML = content;
                document.getElementById('fileViewerActions').innerHTML = actions;
                
                // إظهار النافذة
                const modal = document.getElementById('fileViewerModal');
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
            } catch (error) {
                console.error('Error opening file viewer:', error);
                showToast('حدث خطأ في فتح الملف', 'error');
            }
        }

        function closeFileViewer() {
            const modal = document.getElementById('fileViewerModal');
            modal.setAttribute('aria-hidden', 'true');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            document.getElementById('fileViewerBody').innerHTML = '';
            document.getElementById('fileViewerActions').innerHTML = '';
        }

        async function incrementDownloads(workId) {
            try {
                await fetch(`api/increment-downloads.php?id=${workId}`);
            } catch (error) {
                console.error('Error incrementing downloads:', error);
            }
        }

        // ===== دالات مساعدة للملفات =====
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
                <div class="toast-content">
                    <div class="toast-icon">
                        <i class="fas ${icons[type]}"></i>
                    </div>
                    <div class="toast-message">
                        <h4>${titles[type]}</h4>
                        <p>${message}</p>
                    </div>
                    <button class="close-toast" onclick="this.parentElement.parentElement.remove()">
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

        // ===== MOBILE MENU =====
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navLinks = document.querySelector('.nav-links');
            
            if (mobileMenuToggle && navLinks) {
                mobileMenuToggle.addEventListener('click', function() {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    navLinks.classList.toggle('active', !isExpanded);
                    this.setAttribute('aria-expanded', !isExpanded);
                    this.innerHTML = isExpanded ? 
                        '<i class="fas fa-bars"></i>' : 
                        '<i class="fas fa-times"></i>';
                });
                
                // إغلاق القائمة عند النقر خارجها
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && 
                        !navLinks.contains(e.target) && 
                        !mobileMenuToggle.contains(e.target) &&
                        navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        mobileMenuToggle.setAttribute('aria-expanded', 'false');
                        mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            }
            
            // إغلاق عارض الملفات عند النقر خارجها
            const fileViewerModal = document.getElementById('fileViewerModal');
            if (fileViewerModal) {
                fileViewerModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeFileViewer();
                    }
                });
            }
            
            // إغلاق عارض الملفات عند الضغط على زر ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && fileViewerModal.classList.contains('active')) {
                    closeFileViewer();
                }
            });
        });

        // ===== التحقق من تحميل الصور =====
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[src*="uploads/"]');
            images.forEach(img => {
                img.onerror = function() {
                    this.src = 'https://via.placeholder.com/400x250?text=صورة+غير+متوفرة';
                    this.onerror = null;
                };
            });
        });
    </script>
</body>
</html>
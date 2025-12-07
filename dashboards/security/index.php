<?php
session_start();

// Add authentication check
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    header('Location: login.php');
    exit();
}

require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner - Security Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.5/dist/purify.min.js"></script>
    <style>
        :root {
            --primary-color: #972529;    /* Dark Red */
            --secondary-color: #E5C573;  /* Gold */
            --accent-color: #972529;     /* Dark Red for accents */
            --success-color: #27AE60;    /* Keep standard success */
            --warning-color: #E5C573;    /* Gold for warnings */
            --danger-color: #972529;     /* Dark Red for danger */
            --info-color: #E5C573;       /* Gold for info */
            
            /* Updated Gradients */
            --primary-gradient: #972529;
            --secondary-gradient: #E5C573;
            --success-gradient: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);
            --warning-gradient: #E5C573;
            --danger-gradient: #972529;
            --info-gradient: #E5C573;
            --entries-gradient: #972529;      /* Dark Red */
            --exits-gradient: #E5C573;        /* Gold */
            --student-gradient: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);      /* Green */
            --faculty-gradient: linear-gradient(135deg, #2980B9 0%, #3498DB 100%);      /* Blue */
        }
        
        body {
            background: linear-gradient(135deg, #ECF0F1 0%, #F5F6FA 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            animation: gradientShift 15s ease infinite;
            background-size: 200% 200%;
            overflow-x: hidden;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 1rem 0.5rem;
            position: relative;
            z-index: 100;
            overflow: visible !important;
        }
        
        .navbar .container-fluid {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .navbar-brand img {
            height: 45px;
            width: auto;
            margin-right: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            border-left: 4px solid #972529;
            padding: 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #333;
        }

        .stat-card p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }
        
        .stat-card.entries { border-left-color: #972529; }
        .stat-card.exits { border-left-color: #E5C573; }
        .stat-card.students { border-left-color: #28a745; }
        .stat-card.faculty { border-left-color: #007bff; }
        .stat-card.staff { border-left-color: #17a2b8; }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .activity-item {
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-color);
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 12px;
            background: rgba(255,255,255,0.9);
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .activity-feed {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 5px;
            padding: 0;
        }
        
        .activity-feed::-webkit-scrollbar {
            width: 6px;
        }
        
        .activity-feed::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
            border-radius: 3px;
        }
        
        .activity-feed::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            margin-bottom: 8px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            transition: all 0.2s ease;
            animation: none;
        }

        .activity-item:hover {
            background: #f9f9f9;
            border-color: #d0d0d0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .activity-item.border-success {
            border-left: 3px solid #972529 !important;
            border-radius: 8px;
        }

        .activity-item.border-primary {
            border-left: 3px solid #E5C573 !important;
            border-radius: 8px;
        }

        .activity-item.border-info {
            border-left: 3px solid #17a2b8 !important;
            border-radius: 8px;
        }

        .activity-user-image-container {
            position: relative;
            flex-shrink: 0;
            width: 45px;
            height: 45px;
        }

        .activity-user-image-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .activity-user-avatar-default {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: white;
            border: 2px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .activity-user-avatar-default.student {
            background: linear-gradient(135deg, #972529, #c44536);
        }

        .activity-user-avatar-default.faculty {
            background: linear-gradient(135deg, #E5C573, #f5deba);
            color: #333;
        }

        .activity-user-avatar-default.staff {
            background: linear-gradient(135deg, #72a89e, #8fb5a8);
        }

        .activity-user-image-container:hover img,
        .activity-user-avatar-default:hover {
            transform: scale(1.05);
            transition: transform 0.15s ease;
        }
        
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .alert-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
            border-radius: 20px;
            border: none;
            font-weight: 500;
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-outline-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .btn-outline-primary:hover::before {
            left: 100%;
        }

        .activity-person {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .activity-id {
            font-size: 0.9rem;
            color: #666;
            background: rgba(0,0,0,0.05);
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-left: 8px;
        }
        
        .activity-time {
            font-size: 0.85rem;
            color: #888;
            flex-shrink: 0;
        }

        .activity-avatar {
            flex-shrink: 0;
            width: 45px;
            height: 45px;
            position: relative;
        }

        .activity-user-image {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary-color);
            box-shadow: 0 3px 10px rgba(229, 197, 115, 0.2);
            background: #f8f9fa;
        }

        .activity-avatar-default {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #972529;
            color: #FEFEFE;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid var(--secondary-color);
            box-shadow: 0 3px 10px rgba(229, 197, 115, 0.2);
        }

        /* Add hover effect for images */
        .activity-user-image:hover,
        .activity-avatar-default:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }
        
        .activity-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .activity-type.student {
            background: rgba(151, 37, 41, 0.1);
            color: var(--primary-color);
        }
        
        .activity-type.faculty {
            background: rgba(229, 197, 115, 0.1);
            color: var(--secondary-color);
        }
        
        .activity-type.staff {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .activity-user-image-container {
            position: relative;
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            margin-right: 12px;
        }

        .activity-user-image-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
            box-shadow: 0 3px 10px rgba(229, 197, 115, 0.2);
        }

        .activity-user-avatar-default {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            color: white;
            border: 3px solid var(--secondary-color);
            box-shadow: 0 3px 10px rgba(229, 197, 115, 0.2);
        }

        .activity-user-avatar-default.student {
            background: linear-gradient(135deg, #27AE60, #2ECC71);
        }

        .activity-user-avatar-default.faculty {
            background: linear-gradient(135deg, #2980B9, #3498DB);
        }

        .activity-user-avatar-default.staff {
            background: linear-gradient(135deg, #17a2b8, #20c997);
        }
        
        .container-fluid {
            max-width: 1400px;
        }
        
        /* Notification Styles for Security Dashboard */
        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: rgba(255,255,255,0.9);
            font-size: 18px;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.15s ease;
            margin-right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            background: rgba(229, 197, 115, 0.2);
            color: #fff;
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
            padding: 2px 4px;
            min-width: 16px;
            height: auto;
        }

        .notification-badge:not(:empty) {
            width: auto;
            height: 18px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 600;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            overflow: visible !important;
            gap: 0.5rem;
            flex-wrap: nowrap;
            margin-left: auto;
        }

        .navbar-text {
            white-space: nowrap;
            font-size: 0.95rem;
            margin: 0 !important;
        }

        .nav-link {
            white-space: nowrap;
            padding: 0 !important;
        }

        /* Profile Dropdown Styles */
        .profile-container {
            position: relative;
            display: inline-flex;
            align-items: center;
            margin: 0;
        }

        .profile-btn {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
        }

        .profile-btn:hover {
            border-color: rgba(255, 255, 255, 0.8);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .profile-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-btn .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #E5C573, #f5deba);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .profile-dropdown {
            position: fixed;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            min-width: 220px;
            display: none;
            flex-direction: column;
            z-index: 9999;
            animation: slideInDown 0.3s ease-out;
            top: calc(100% + 10px);
            right: 20px;
        }

        .profile-dropdown.show {
            display: flex;
        }

        .profile-header {
            padding: 15px;
            border-bottom: 1px solid #e8e8e8;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-header-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: #f0f0f0;
        }

        .profile-header-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header-img .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #E5C573, #f5deba);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #333;
        }

        .profile-info h6 {
            margin: 0;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .profile-info p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #999;
        }

        .profile-body {
            padding: 8px 0;
        }

        .profile-logout-btn {
            width: 100%;
            padding: 12px 15px;
            border: none;
            background: none;
            color: #666;
            text-align: left;
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #e8e8e8;
        }

        .profile-logout-btn:hover {
            background: #f5f5f5;
            color: #dc3545;
        }

        /* Notification Container & Dropdown */
        .notification-container {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .notification-dropdown {
            position: fixed;
            top: 60px;
            right: 20px;
            width: 380px;
            max-height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 9999;
            margin-top: 0;
            animation: slideInDown 0.3s ease-out;
        }

        .notification-dropdown.show {
            display: flex;
        }

        .notification-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
            border-radius: 12px 12px 0 0;
        }

        .notification-header h6 {
            color: #333;
            font-weight: 600;
            margin: 0;
        }

        .notification-list {
            flex: 1;
            overflow-y: auto;
            min-height: 200px;
            max-height: 380px;
        }

        .notification-list::-webkit-scrollbar {
            width: 6px;
        }

        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .notification-list::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .notification-item:hover {
            background: #f9f9f9;
        }

        .notification-item.unread {
            background: rgba(220, 53, 69, 0.05);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .notification-item.type-error .notification-icon {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .notification-item.type-warning .notification-icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .notification-item.type-info .notification-icon {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notification-title .badge {
            font-size: 10px;
            padding: 2px 6px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            margin: 0 0 6px 0;
            line-height: 1.4;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
            margin: 0;
        }

        .notification-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        .notification-actions button {
            background: none;
            border: none;
            color: #666;
            font-size: 12px;
            padding: 4px 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .notification-actions button:hover {
            color: var(--primary-color);
        }

        .notification-footer {
            padding: 10px 20px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
            border-radius: 0 0 12px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .notification-footer .btn-link {
            color: #666;
            font-size: 13px;
            padding: 8px 12px;
            text-decoration: none;
        }

        .notification-footer .btn-link:hover {
            color: var(--primary-color);
            background: rgba(151, 37, 41, 0.05);
        }

        .notification-empty {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .notification-empty i {
            font-size: 32px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Animation for dropdown */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
        }

        .container-fluid {
            overflow: visible !important;
        }
        
        @media (max-width: 768px) {
            .stat-card h3 {
                font-size: 2rem;
            }
            
            .navbar .container-fluid {
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
            }

            .navbar-brand {
                display: flex;
                align-items: center;
                font-size: 1rem;
                flex-basis: auto;
                margin-bottom: 0;
                order: 1;
            }

            .navbar-brand img {
                height: 35px;
                margin-right: 10px;
            }

            .navbar-brand div:last-child small {
                display: none;
            }
            
            .navbar-nav {
                flex-basis: auto;
                justify-content: flex-end;
                margin-top: 0;
                gap: 0.5rem;
                order: 2;
                margin-left: auto;
                flex-wrap: nowrap;
            }

            .notification-btn {
                padding: 0.5rem 0.75rem;
            }

            .profile-btn {
                width: 40px;
                height: 40px;
            }

            .profile-container {
                margin: 0;
            }

            .profile-dropdown {
                position: fixed !important;
                top: auto !important;
                left: auto !important;
                right: 20px !important;
                bottom: auto !important;
                transform: none !important;
                width: 300px !important;
                max-width: none !important;
            }

            .notification-dropdown {
                position: fixed !important;
                top: 50% !important;
                left: 50% !important;
                right: auto !important;
                transform: translate(-50%, -50%);
                width: 90vw !important;
                max-width: 450px !important;
                max-height: 70vh !important;
            }

            .card-header {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 0.75rem 0.5rem;
            }

            .navbar .container-fluid {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .navbar-brand {
                flex-basis: auto;
                font-size: 0.85rem;
                margin-bottom: 0;
                order: 1;
            }

            .navbar-brand img {
                height: 30px;
                margin-right: 8px;
            }

            .navbar-brand div:last-child {
                display: flex;
                flex-direction: column;
            }

            .navbar-brand div:last-child small {
                display: none;
            }

            .navbar-nav {
                flex-basis: auto;
                gap: 0.5rem;
                justify-content: flex-end;
                order: 2;
                margin-left: auto;
                flex-wrap: nowrap;
            }

            .notification-btn {
                padding: 0.4rem 0.5rem;
                font-size: 16px;
            }

            .profile-btn {
                width: 36px;
                height: 36px;
            }

            .profile-container {
                margin: 0;
            }

            .profile-dropdown {
                position: fixed !important;
                top: auto !important;
                left: auto !important;
                right: 10px !important;
                bottom: auto !important;
                transform: none !important;
                width: 280px !important;
                max-width: none !important;
            }

            .notification-dropdown {
                position: fixed !important;
                top: 50% !important;
                left: 50% !important;
                right: auto !important;
                transform: translate(-50%, -50%) !important;
                width: 95vw !important;
                max-width: 350px !important;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="/assets/images/logo.png" alt="CTU Logo">
                <div>
                    <div>CTU Security Dashboard</div>
                    <small style="font-size: 0.7rem; opacity: 0.8;">Real-time Monitoring System</small>
                </div>
            </a>
            <div class="navbar-nav ms-auto">
                <!-- Notification Bell with Dropdown -->
                <div class="notification-container">
                    <button class="notification-btn" id="notificationBell" title="Failed Scan Alerts" onclick="toggleNotificationDropdown()">
                        <i class="fas fa-bell"></i>
                        <div class="notification-badge" id="notificationBadge">0</div>
                    </button>
                </div>

                <!-- Profile Picture with Dropdown -->
                <div class="profile-container">
                    <button class="profile-btn" id="profileBtn" title="Profile" onclick="toggleProfileDropdown()">
                        <?php 
                            $security_id = $_SESSION['security_id'] ?? 'S';
                            $profile_pic = '';
                            
                            // Fetch profile picture from database
                            try {
                                require_once '../../config/database.php';
                                $db = new Database();
                                $conn = $db->getConnection();
                                
                                $sql = "SELECT image FROM security WHERE SecurityID = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$security_id]);
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($result && $result['image']) {
                                    $profile_pic = '/' . $result['image'];
                                }
                            } catch (Exception $e) {
                                // If database query fails, use fallback
                            }
                            
                            if ($profile_pic && file_exists($_SERVER['DOCUMENT_ROOT'] . $profile_pic)) {
                                echo '<img src="' . htmlspecialchars($profile_pic) . '" alt="Profile">';
                            } else {
                                echo '<div class="default-avatar">' . substr(htmlspecialchars($security_id ?? 'S'), 0, 1) . '</div>';
                            }
                        ?>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="profile-header">
                            <div class="profile-header-img">
                                <?php 
                                    if ($profile_pic && file_exists($_SERVER['DOCUMENT_ROOT'] . $profile_pic)) {
                                        echo '<img src="' . htmlspecialchars($profile_pic) . '" alt="Profile">';
                                    } else {
                                        echo '<div class="default-avatar">' . substr(htmlspecialchars($security_id ?? 'S'), 0, 1) . '</div>';
                                    }
                                ?>
                            </div>
                            <div class="profile-info">
                                <h6><?php echo htmlspecialchars($_SESSION['security_name'] ?? 'Security Guard'); ?></h6>
                                <p><?php echo htmlspecialchars($security_id); ?></p>
                            </div>
                        </div>
                        <div class="profile-body">
                            <a href="logout.php" class="profile-logout-btn">
                                <i class="fas fa-sign-out-alt"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notification Dropdown Panel - Outside navbar to avoid clipping -->
    <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <h6 class="mb-0">Failed Scan Alerts</h6>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox" id="soundNotif" checked onchange="toggleNotificationSound()" style="cursor: pointer;">
                        <label class="form-check-label" for="soundNotif" style="cursor: pointer; margin-bottom: 0; font-size: 12px;">
                            <i class="fas fa-volume-up"></i>
                        </label>
                    </div>
                    <button class="btn-close btn-sm" onclick="toggleNotificationDropdown()"></button>
                </div>
            </div>
        </div>
        
        <div class="notification-list" id="notificationList">
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin text-muted mb-2"></i>
                <p class="text-muted small">Loading...</p>
            </div>
        </div>
        
        <div class="notification-footer">
            <button class="btn btn-sm btn-link w-100" onclick="markAllNotificationsRead()">
                <i class="fas fa-check me-1"></i>Mark all as read
            </button>
            <button class="btn btn-sm btn-link text-danger w-100" onclick="clearAllNotifications()">
                <i class="fas fa-trash me-1"></i>Clear all
            </button>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>Real-time Campus Monitoring
                        </h4>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="fas fa-clock me-1"></i>Last updated: <span id="lastUpdated">Never</span>
                            </small>
                            <button class="btn btn-sm btn-outline-primary" onclick="dashboard.loadInitialData()">
                                <i class="fas fa-sync-alt"></i> Refresh All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card entries">
                                    <h3 id="todayEntries">-</h3>
                                    <p>Today's Entries</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card exits">
                                    <h3 id="todayExits">-</h3>
                                    <p>Today's Exits</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card students">
                                    <h3 id="studentEntries">-</h3>
                                    <p>Student Entries</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card faculty">
                                    <h3 id="facultyEntries">-</h3>
                                    <p>Faculty Entries</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card staff">
                                    <h3 id="staffEntries">-</h3>
                                    <p>Staff Entries</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Visitors Section -->
        <div class="row mb-4" id="activeVisitorsSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-check me-2 text-success"></i>Currently Checked-In Visitors
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="loadActiveVisitors()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="activeVisitorsContainer">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-hourglass-start fa-3x mb-2 opacity-50"></i>
                                <p>Loading active visitors...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Registration Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>Visitor Registration
                        </h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#visitorModal">
                            <i class="fas fa-plus me-1"></i>Register New Visitor
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="visitorListContainer">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-2 opacity-50"></i>
                                <p>No visitors registered today</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-right text-success me-2"></i>Recent Entries
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshEntries()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentEntries" class="activity-feed p-3">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border text-secondary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>Loading recent entries...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-left text-warning me-2"></i>Recent Exits
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshExits()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentExits" class="activity-feed p-3">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border text-secondary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>Loading recent exits...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="row mt-5 mb-4">
            <div class="col-12">
                <h4 class="mb-3">
                    <i class="fas fa-chart-bar me-2"></i>Entry & Exit Analytics
                </h4>
            </div>
        </div>

        <!-- Entry/Exit Logs Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Entry Logs Timeline</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center" style="height: 350px;">
                        <canvas id="securityEntryLogsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Exit Logs Timeline</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center" style="height: 350px;">
                        <canvas id="securityExitLogsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entry/Exit Comparison by Hour -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-arrows-alt-h me-2"></i>Entry & Exit by Hour</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center" style="height: 350px;">
                        <canvas id="securityEntryExitHourlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/audio/notification-sound.js"></script>
    <script src="../../assets/js/security.js"></script>
    
    <script>
        // Enhanced activity rendering with better styling
        function renderActivity(items, container, type) {
            const container_el = document.getElementById(container);
            if (!items || items.length === 0) {
                container_el.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                        <div>No ${type} recorded yet</div>
                    </div>
                `;
                return;
            }
            
            const html = items.map(item => {
                const firstName = item.StudentFName || item.FacultyFName || 'Unknown';
                const lastName = item.StudentLName || item.FacultyLName || '';
                const fullName = `${firstName} ${lastName}`.trim();
                const timestamp = new Date(item.Timestamp);
                const timeStr = timestamp.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                return `
                    <div class="activity-item fade-in">
                        <div class="d-flex align-items-center gap-3">
                            <div class="activity-avatar">
                                ${item.image ? 
                                    `<img src="${item.image}" alt="${fullName}" class="activity-user-image" onerror="this.onerror=null; this.src='../../assets/images/default-avatar.png';">` :
                                    `<div class="activity-avatar-default">${fullName.charAt(0).toUpperCase()}</div>`
                                }
                            </div>
                            <div class="flex-grow-1">
                                <div class="activity-person">${fullName}</div>
                                <div class="mt-1">
                                    <span class="activity-type ${item.PersonCategory}">${item.PersonCategory}</span>
                                    <span class="activity-id">${item.PersonID}</span>
                                </div>
                            </div>
                            <div class="activity-time">
                                <i class="fas fa-clock me-1"></i>${timeStr}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            container_el.innerHTML = html;
        }

        // Update last updated time
        function updateLastUpdatedTime() {
            const now = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            const element = document.getElementById('lastUpdated');
            if (element) {
                element.textContent = now;
            }
        }

        // Status indicator management
        function showStatus(type) {
            ['connected', 'connecting', 'disconnected'].forEach(status => {
                document.getElementById(status + 'Status').classList.add('d-none');
            });
            document.getElementById(type + 'Status').classList.remove('d-none');
        }

        // Enhanced dashboard initialization
        document.addEventListener('DOMContentLoaded', () => {
            // Show connecting status initially
            showStatus('connecting');
            
            // Initialize dashboard
            if (typeof dashboard !== 'undefined') {
                dashboard.loadInitialData();
                
                // Show connected status after successful load
                setTimeout(() => {
                    showStatus('connected');
                    updateLastUpdatedTime();
                }, 1500);
                
                // Set up auto-refresh
                setInterval(() => {
                    dashboard.loadInitialData();
                    updateLastUpdatedTime();
                }, 30000); // Refresh every 30 seconds
            }
        });

        // Override existing functions if they exist
        if (typeof refreshEntries === 'undefined') {
            window.refreshEntries = function() {
                if (typeof dashboard !== 'undefined') {
                    dashboard.loadRecentEntries();
                }
            };
        }

        if (typeof refreshExits === 'undefined') {
            window.refreshExits = function() {
                if (typeof dashboard !== 'undefined') {
                    dashboard.loadRecentExits();
                }
            };
        }
    </script>

    <!-- Visitor Registration Modal -->
    <div class="modal fade" id="visitorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card me-2"></i>Register New Visitor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="visitorForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company/Organization</label>
                                <input type="text" class="form-control" name="company">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose of Visit <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="purpose" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Type</label>
                                <select class="form-control" name="id_provided_type">
                                    <option value="">Select ID Type</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Driver's License">Driver's License</option>
                                    <option value="National ID">National ID</option>
                                    <option value="School ID">School ID</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Number</label>
                                <input type="text" class="form-control" name="id_provided_number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visitor Photo</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Copy</label>
                                <input type="file" class="form-control" name="id_image" accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="registerVisitor()">
                        <i class="fas fa-check me-1"></i>Register Visitor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Visitor registration handler
        function registerVisitor() {
            const form = document.getElementById('visitorForm');
            const formData = new FormData(form);

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Registering...';

            fetch('register_visitor.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    // Try to extract JSON from response
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            parsed = JSON.parse(match[0]);
                        } catch (e2) {
                            parsed = null;
                        }
                    }
                }
                return { ok: r.ok, parsed, text };
            }))
            .then(result => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Register Visitor';

                if (result.parsed && result.parsed.success) {
                    alert('Visitor registered successfully! Code: ' + result.parsed.visitor.visitor_code);
                    form.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('visitorModal'));
                    modal.hide();
                    loadVisitors();
                } else {
                    const msg = result.parsed?.message || 'Failed to register visitor';
                    alert('Error: ' + msg);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Register Visitor';
                console.error('Visitor registration error:', err);
                alert('Network error occurred');
            });
        }

        // Load and display visitors
        function loadVisitors() {
            fetch('get_visitors.php')
            .then(r => r.json())
            .then(data => {
                if (data.visitors && data.visitors.length > 0) {
                    let html = '';
                    data.visitors.forEach(visitor => {
                        const fullName = [visitor.first_name, visitor.middle_name, visitor.last_name].filter(Boolean).join(' ');
                        html += `
                            <div class="row align-items-center border-bottom py-3">
                                <div class="col-md-2 text-center">
                                    ${visitor.image ? `<img src="../../${visitor.image}" class="rounded-circle" width="50" height="50" style="object-fit: cover;">` : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-user"></i></div>`}
                                </div>
                                <div class="col-md-4">
                                    <div><strong>${fullName}</strong></div>
                                    <div class="text-muted small">Code: <span class="badge bg-info">${visitor.visitor_code}</span></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small"><i class="fas fa-phone me-1"></i>${visitor.contact_number}</div>
                                    <div class="text-muted small"><i class="fas fa-building me-1"></i>${visitor.company || 'N/A'}</div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <button class="btn btn-sm btn-success me-2" onclick="checkInVisitor(${visitor.id})">
                                        <i class="fas fa-sign-in-alt me-1"></i>Check In
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    document.getElementById('visitorListContainer').innerHTML = html;
                } else {
                    document.getElementById('visitorListContainer').innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-2 opacity-50"></i>
                            <p>No visitors registered today</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Load visitors error:', err);
            });
        }

        // Load and display active visitors
        function loadActiveVisitors() {
            fetch('get_active_visitors.php')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('activeVisitorsContainer');
                if (!container) return;

                if (data.active_visitors && data.active_visitors.length > 0) {
                    let html = '<div class="list-group">';
                    data.active_visitors.forEach(visitor => {
                        const fullName = [visitor.first_name, visitor.middle_name, visitor.last_name].filter(Boolean).join(' ');
                        const checkinTime = new Date(visitor.check_in_time).toLocaleTimeString();
                        html += `
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        ${visitor.image ? `<img src="../../${visitor.image}" class="rounded-circle" width="45" height="45" style="object-fit: cover;">` : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;"><i class="fas fa-user"></i></div>`}
                                    </div>
                                    <div class="col-md-4">
                                        <div><strong>${fullName}</strong></div>
                                        <div class="text-muted small">${visitor.company || 'N/A'}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small"><i class="fas fa-clock me-1"></i>In: ${checkinTime}</div>
                                        <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>${visitor.location}</div>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-sm btn-warning" onclick="checkOutVisitor(${visitor.id})">
                                            <i class="fas fa-sign-out-alt me-1"></i>Check Out
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-2 opacity-50"></i>
                            <p>No active visitors</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Load active visitors error:', err);
            });
        }
        function checkInVisitor(visitorId) {
            const formData = new FormData();
            formData.append('action', 'check_in');
            formData.append('visitor_id', visitorId);
            formData.append('location', 'Main Entrance');

            fetch('visitor_checkin.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            parsed = JSON.parse(match[0]);
                        } catch (e2) {}
                    }
                }
                return { ok: r.ok, parsed };
            }))
            .then(result => {
                if (result.parsed && result.parsed.success) {
                    alert('Visitor ' + result.parsed.visitor.name + ' checked in successfully!');
                    loadVisitors();
                    loadActiveVisitors();
                } else {
                    alert('Error: ' + (result.parsed?.message || 'Check-in failed'));
                }
            })
            .catch(err => {
                console.error('Check-in error:', err);
                alert('Network error');
            });
        }

        // Check out visitor
        function checkOutVisitor(visitorId) {
            if (!confirm('Check out this visitor?')) return;

            const formData = new FormData();
            formData.append('action', 'check_out');
            formData.append('visitor_id', visitorId);

            fetch('visitor_checkin.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            parsed = JSON.parse(match[0]);
                        } catch (e2) {}
                    }
                }
                return { ok: r.ok, parsed };
            }))
            .then(result => {
                if (result.parsed && result.parsed.success) {
                    alert('Visitor checked out! Dwell time: ' + result.parsed.dwell_time);
                    loadActiveVisitors();
                } else {
                    alert('Error: ' + (result.parsed?.message || 'Check-out failed'));
                }
            })
            .catch(err => {
                console.error('Check-out error:', err);
                alert('Network error');
            });
        }

        // Track notification IDs to detect new ones
        let previousNotificationIds = new Set();
        let notificationPollingInterval = null;

        // Load visitors on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadVisitors();
            loadActiveVisitors();
            // Show active visitors section
            const section = document.getElementById('activeVisitorsSection');
            if (section) section.style.display = 'block';
            // Refresh visitors every 30 seconds
            setInterval(loadVisitors, 30000);
            // Refresh active visitors every 15 seconds
            setInterval(loadActiveVisitors, 15000);
            
            // Load notifications on page load
            fetchNotifications();
            // Refresh notifications every 0.5 seconds for real-time updates
            notificationPollingInterval = setInterval(fetchNotifications, 500);
            // Close dropdown when clicking outside
            document.addEventListener('click', closeNotificationDropdownOnClickOutside);
            
            // Reposition profile dropdown on scroll and window resize
            window.addEventListener('scroll', () => {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    positionProfileDropdown();
                }
            });
            
            window.addEventListener('resize', () => {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    positionProfileDropdown();
                }
            });
        });

        // ==========================================
        // NOTIFICATION FUNCTIONS
        // ==========================================

        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
            
            // Fetch notifications when opening dropdown
            if (dropdown.classList.contains('show')) {
                fetchNotifications();
            }
        }

        function closeNotificationDropdownOnClickOutside(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const bell = document.getElementById('notificationBell');
            
            if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        }

        // ==========================================
        // PROFILE DROPDOWN FUNCTIONS
        // ==========================================

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            const btn = document.getElementById('profileBtn');
            
            // Close notification dropdown
            closeNotificationDropdownOnClickOutside({ target: document.body });
            
            // Toggle profile dropdown
            dropdown.classList.toggle('show');
            
            // Position dropdown properly
            if (dropdown.classList.contains('show')) {
                positionProfileDropdown();
            }
        }

        function positionProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            const btn = document.getElementById('profileBtn');
            
            if (!dropdown || !btn) return;
            
            const rect = btn.getBoundingClientRect();
            const dropdownHeight = dropdown.offsetHeight;
            
            // Position to the right of profile button with proper spacing
            dropdown.style.top = (rect.bottom + 10) + 'px';
            dropdown.style.right = 'auto';
            dropdown.style.left = (rect.left - dropdown.offsetWidth + btn.offsetWidth) + 'px';
            
            // If dropdown goes off screen, adjust
            if (dropdown.getBoundingClientRect().right > window.innerWidth - 10) {
                dropdown.style.left = 'auto';
                dropdown.style.right = '10px';
            }
        }

        function closeProfileDropdownOnClickOutside(event) {
            const dropdown = document.getElementById('profileDropdown');
            const btn = document.getElementById('profileBtn');
            
            if (!dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        }

        // Close dropdowns on click outside
        document.addEventListener('click', (event) => {
            closeNotificationDropdownOnClickOutside(event);
            closeProfileDropdownOnClickOutside(event);
        });

        /**
         * Fetch notifications from API - Optimized for real-time delivery
         */
        function fetchNotifications() {
            // Use a simple fetch without extra overhead for speed
            fetch('../../api/notifications_security_api.php?action=get_all&limit=20&t=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.notifications) {
                        displayNotifications(data.notifications);
                        updateNotificationBadge(data);
                        
                        // Play notification sound only for NEW unread alerts
                        if (typeof notificationSound !== 'undefined' && notificationSound && notificationSound.isSoundEnabled) {
                            const currentNotificationIds = new Set(data.notifications.map(n => n.id));
                            const unreadNotifications = data.notifications.filter(n => !n.is_read);
                            
                            // Check if there are new unread notifications (ones we haven't seen before)
                            const newUnreadNotifications = unreadNotifications.filter(n => !previousNotificationIds.has(n.id));
                            
                            if (newUnreadNotifications.length > 0) {
                                // Determine which tone to play based on notification type
                                const hasErrors = newUnreadNotifications.some(n => n.type === 'error');
                                const hasWarnings = newUnreadNotifications.some(n => n.type === 'warning');
                                
                                if (hasErrors) {
                                    notificationSound.playErrorTone();
                                } else if (hasWarnings) {
                                    notificationSound.playWarningTone();
                                } else {
                                    notificationSound.playNotificationTone();
                                }
                            }
                            
                            // Update the set of notification IDs
                            previousNotificationIds = currentNotificationIds;
                        }
                    }
                })
                .catch(error => {
                    // Silently handle errors to avoid console spam
                    console.log('Notification fetch error:', error);
                });
        }

        /**
         * Display notifications in dropdown
         */
        function displayNotifications(notifications) {
            const listContainer = document.getElementById('notificationList');
            
            if (!notifications || notifications.length === 0) {
                listContainer.innerHTML = `
                    <div class="notification-empty">
                        <div><i class="fas fa-inbox"></i></div>
                        <p>No failed scan alerts</p>
                    </div>
                `;
                return;
            }
            
            listContainer.innerHTML = notifications.map(notif => {
                const timeAgo = getTimeAgo(notif.created_at);
                const isUnread = !notif.is_read;
                
                return `
                    <div class="notification-item type-${notif.type} ${isUnread ? 'unread' : ''}">
                        <div class="notification-icon">
                            <i class="fas ${notif.icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">
                                ${notif.title}
                                ${isUnread ? '<span class="badge bg-danger">New</span>' : ''}
                            </div>
                            <p class="notification-message">${notif.message}</p>
                            <p class="notification-time">${timeAgo}</p>
                            <div class="notification-actions">
                                ${isUnread ? `<button onclick="markNotificationRead('${notif.id}')"><i class="fas fa-check me-1"></i>Mark read</button>` : ''}
                                <button onclick="deleteNotification('${notif.id}')" class="text-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        /**
         * Update notification badge with unread count
         */
        function updateNotificationBadge(data) {
            const badge = document.getElementById('notificationBadge');
            let unreadCount = 0;
            
            if (data.notifications) {
                unreadCount = data.notifications.filter(n => !n.is_read).length;
            }
            
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
            }
        }

        /**
         * Mark single notification as read
         */
        function markNotificationRead(notifId) {
            fetch(`../../api/notifications_security_api.php?action=mark_read&notification_id=${notifId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                    }
                })
                .catch(error => console.log('Error marking read:', error));
        }

        /**
         * Mark all notifications as read
         */
        function markAllNotificationsRead() {
            fetch('../../api/notifications_security_api.php?action=mark_all_read', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchNotifications();
                }
            })
            .catch(error => console.log('Error marking all read:', error));
        }

        /**
         * Delete notification
         */
        function deleteNotification(notifId) {
            if (confirm('Delete this notification?')) {
                fetch(`../../api/notifications_security_api.php?action=delete&notification_id=${notifId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchNotifications();
                        }
                    })
                    .catch(error => console.log('Error deleting:', error));
            }
        }

        /**
         * Clear all notifications
         */
        function clearAllNotifications() {
            if (confirm('Clear all failed scan alerts? This cannot be undone.')) {
                fetch('../../api/notifications_security_api.php?action=clear_all', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                    }
                })
                .catch(error => console.log('Error clearing:', error));
            }
        }

        /**
         * Convert timestamp to "time ago" format
         */
        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
            if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
            return date.toLocaleDateString();
        }

        /**
         * Initialize notification sound on page load
         */
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof notificationSound !== 'undefined') {
                const soundToggle = document.getElementById('soundNotif');
                if (soundToggle) {
                    soundToggle.checked = notificationSound.isSoundEnabled;
                }
            }
        }, { once: true });

        /**
         * Toggle notification sound
         */
        function toggleNotificationSound() {
            const soundToggle = document.getElementById('soundNotif');
            if (typeof notificationSound !== 'undefined' && notificationSound) {
                notificationSound.setSoundPreference(soundToggle.checked);
            }
        }
    </script>
    <script src="../../assets/js/safe-dom.js"></script>
    <!-- Security Shift Schedule Checker -->
    <script src="../../assets/js/security-schedule-checker.js"></script>
</body>
</html>
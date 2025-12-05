<?php
session_start();

// Security check - require scanner login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'scanner') {
    header('Location: login.php');
    exit;
}

$page_title = 'CTU Scanner - Code 39 Barcode Scanner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#972529">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #E5C573;
            --primary-red: #972529;
            --primary-orange: #a83531;
            --gold-light: #eed490;
            --red-dark: #7a1d21;
            --orange-light: #b63d3f;
            --scanner-glow: rgba(151, 37, 41, 0.6);
            --card-shadow: rgba(0, 0, 0, 0.15);
        }

        * {
            touch-action: manipulation;
        }

        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        body {
            background: url('/assets/images/logo.png') no-repeat center/150% fixed,
                        linear-gradient(135deg, #972529 0%, #7a1d21 50%, #5a141a 100%);
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('/assets/images/logo.png') no-repeat center/150% fixed;
            filter: blur(8px);
            opacity: 0.8;
            z-index: -1;
            pointer-events: none;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(151, 37, 41, 0.7) 0%, rgba(122, 29, 33, 0.7) 50%, rgba(90, 20, 26, 0.7) 100%);
            pointer-events: none;
            z-index: 0;
        }

        .scanner-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 720px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            margin: 0 auto;
            overflow: hidden;
        }

        .scanner-header {
            text-align: center;
            color: white;
            margin: 0;
            animation: slideDown 0.6s ease-out;
            flex-shrink: 0;
            padding: 20px 16px;
            background: linear-gradient(135deg, rgba(229, 197, 115, 0.1), rgba(168, 53, 49, 0.1));
            border-radius: 20px 20px 0 0;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(229, 197, 115, 0.2);
            border-bottom: none;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 8px;
            object-fit: contain;
            border-radius: 50%;
            border: 5px solid var(--primary-gold);
            background: linear-gradient(135deg, rgba(229, 197, 115, 0.25), rgba(168, 53, 49, 0.15));
            padding: 8px;
            filter: drop-shadow(0 15px 35px rgba(229, 197, 115, 0.7));
            animation: logoPulse 3s ease-in-out infinite;
            box-shadow: inset 0 2px 10px rgba(255, 255, 255, 0.3);
        }

        @keyframes logoPulse {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 8px 20px rgba(229, 197, 115, 0.5));
            }
            50% {
                transform: scale(1.05);
                filter: drop-shadow(0 12px 30px rgba(229, 197, 115, 0.7));
            }
        }

        .scanner-header h1 {
            font-size: 1.6rem;
            font-weight: 900;
            margin-bottom: 4px;
            text-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
            letter-spacing: 1px;
        }

        .scanner-header p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 0;
            text-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        
        .logout-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logout-btn:hover {
            background: rgba(229, 197, 115, 0.25);
            color: var(--primary-gold);
            border-color: rgba(229, 197, 115, 0.4);
            transform: scale(1.1);
        }

        .scanner-card {
            background: rgba(255, 255, 255, 0.99);
            border-radius: 0 0 20px 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            backdrop-filter: blur(30px);
            border: 1px solid rgba(229, 197, 115, 0.2);
            border-top: none;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            flex: 1;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--primary-orange) 100%);
            color: #fff;
            padding: 20px;
            border: none;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: headerShine 3s ease-in-out infinite;
        }

        @keyframes headerShine {
            0%, 100% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(50px, -50px);
            }
        }

        .card-header h4 {
            margin: 0;
            font-weight: 900;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .card-body::-webkit-scrollbar {
            width: 6px;
        }

        .card-body::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        .card-body::-webkit-scrollbar-thumb {
            background: var(--primary-gold);
            border-radius: 3px;
        }

        .card-body::-webkit-scrollbar-thumb:hover {
            background: var(--primary-orange);
        }

        .scanner-location-select {
            width: 100%;
        }

        .location-label {
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--primary-red);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-select {
            border: 2.5px solid #e0e0e0 !important;
            border-radius: 12px;
            padding: 12px 14px !important;
            font-weight: 700;
            color: #333;
            background-color: white;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .form-select:hover {
            border-color: var(--primary-gold) !important;
            box-shadow: 0 6px 20px rgba(229, 197, 115, 0.25);
            transform: translateY(-2px);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-orange) !important;
            box-shadow: 0 0 0 5px rgba(229, 197, 115, 0.3), 0 6px 20px rgba(229, 197, 115, 0.25);
            background-color: white;
        }

        .scanner-status-box {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.15), rgba(32, 201, 151, 0.1));
            border: 3px solid #28a745;
            border-radius: 18px;
            padding: 28px 24px;
            text-align: center;
            margin: 0;
            animation: statusPulse 2s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }

        .scanner-status-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: statusShine 2.5s infinite;
        }

        @keyframes statusShine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 0.93; }
            50% { opacity: 1; }
        }

        .status-text {
            font-size: 1rem;
            font-weight: 900;
            margin: 8px 0 4px 0;
            color: #28a745;
            position: relative;
            z-index: 1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .scanner-status-box small {
            font-size: 0.8rem !important;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .barcode-input-group {
            margin: 8px 0 0 0;
            position: relative;
        }

        .barcode-input-group small {
            font-size: 0.75rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-weight: 600;
            color: #666;
        }

        .barcode-input-group small strong {
            font-weight: 900;
            color: var(--primary-red);
        }

        .barcode-input {
            font-size: 1.2rem;
            padding: 14px 16px;
            border: 2.5px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--primary-red);
            background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            -webkit-user-select: text;
            user-select: text;
            letter-spacing: 0.8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .barcode-input:hover {
            border-color: var(--primary-gold);
            box-shadow: 0 6px 20px rgba(229, 197, 115, 0.25);
            transform: translateY(-2px);
        }

        .barcode-input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 5px rgba(229, 197, 115, 0.3), 0 6px 20px rgba(229, 197, 115, 0.25);
            background: white;
        }

        .barcode-input::placeholder {
            color: #bbb;
        }

        /* Mobile: Make input editable for manual entry */
        @media (max-width: 768px) {
            .barcode-input {
                caret-color: var(--primary-red);
            }
        }

        /* Digital Clock Styles */
        .digital-clock-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 300;
            background: linear-gradient(135deg, rgba(229, 197, 115, 0.95), rgba(168, 53, 49, 0.95));
            border: 3px solid var(--primary-gold);
            border-radius: 15px;
            padding: 12px 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            animation: clockSlideIn 0.6s ease-out;
        }

        @keyframes clockSlideIn {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .digital-clock {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            min-width: 140px;
        }

        .digital-time {
            font-size: 2rem;
            font-weight: 900;
            color: white;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            text-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
            line-height: 1;
        }

        .digital-date {
            font-size: 0.75rem;
            color: #fff;
            font-weight: 700;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .digital-ampm {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Clock pulse animation */
        .digital-time::after {
            content: '';
            display: inline-block;
            width: 3px;
            height: 1.8rem;
            background: white;
            margin-left: 6px;
            animation: clockBlink 1s infinite;
            vertical-align: middle;
        }

        @keyframes clockBlink {
            0%, 49% {
                opacity: 1;
            }
            50%, 100% {
                opacity: 0;
            }
        }

        /* Responsive clock */
        @media (max-width: 768px) {
            .digital-clock-container {
                top: 10px;
                right: 10px;
                padding: 8px 14px;
                border-radius: 10px;
                border-width: 2px;
            }

            .digital-time {
                font-size: 1.5rem;
                letter-spacing: 1px;
            }

            .digital-date {
                font-size: 0.65rem;
            }

            .digital-ampm {
                font-size: 0.6rem;
            }

            .digital-time::after {
                width: 2px;
                height: 1.3rem;
                margin-left: 4px;
            }
        }

        @media (max-width: 480px) {
            .digital-clock-container {
                top: 8px;
                right: 8px;
                padding: 6px 10px;
                border-width: 2px;
            }

            .digital-time {
                font-size: 1.2rem;
                letter-spacing: 0.5px;
            }

            .digital-date {
                font-size: 0.6rem;
            }

            .digital-ampm {
                font-size: 0.55rem;
            }
        }

        .btn-group-custom {
            display: flex;
            gap: 12px;
            margin: 0;
        }

        .btn-custom {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .btn-reset {
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            color: white;
            box-shadow: 0 6px 20px rgba(229, 197, 115, 0.3);
        }

        .btn-reset:hover {
            background: linear-gradient(135deg, var(--gold-light), var(--primary-gold));
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(229, 197, 115, 0.4);
        }

        .btn-reset:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(229, 197, 115, 0.3);
        }

        .scan-result-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .scan-result-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .scan-result-modal {
            background: linear-gradient(135deg, #ffffff 0%, #f5f7fa 100%);
            border-radius: 28px;
            padding: 32px 28px;
            max-width: 520px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: modalSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2px solid rgba(229, 197, 115, 0.3);
            position: relative;
        }

        .scan-result-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            border-radius: 28px 28px 0 0;
        }

        .scan-result-modal.error::before {
            background: linear-gradient(135deg, var(--primary-red), #c44536);
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px) scale(0.92);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .scan-result-modal.error {
            border-color: rgba(220, 53, 69, 0.3);
        }

        .result-icon {
            font-size: 3.2rem;
            margin-bottom: 16px;
            animation: iconPulse 0.6s ease-out;
            display: flex;
            justify-content: center;
        }

        @keyframes iconPulse {
            0% {
                transform: scale(0.6);
                opacity: 0;
            }
            50% {
                transform: scale(1.15);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .result-icon.success {
            color: #28a745;
            filter: drop-shadow(0 4px 12px rgba(40, 167, 69, 0.4));
        }

        .result-icon.error {
            color: var(--primary-red);
            filter: drop-shadow(0 4px 12px rgba(151, 37, 41, 0.4));
            animation: errorShake 0.5s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .result-title {
            font-size: 1.6rem;
            font-weight: 900;
            margin-bottom: 10px;
            color: var(--primary-red);
            letter-spacing: -0.3px;
            text-transform: uppercase;
        }

        .result-message {
            font-size: 1.05rem;
            color: #555;
            margin-bottom: 18px;
            line-height: 1.6;
            font-weight: 700;
        }

        .result-details {
            text-align: left;
            background: linear-gradient(135deg, rgba(229, 197, 115, 0.05), rgba(168, 53, 49, 0.03));
            border-radius: 16px;
            padding: 16px;
            margin: 16px 0 0 0;
            border: 2px solid rgba(229, 197, 115, 0.2);
        }

        .result-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(229, 197, 115, 0.15);
            font-size: 0.9rem;
        }

        .result-detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 900;
            color: var(--primary-red);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #333;
            font-weight: 800;
            text-align: right;
            font-size: 0.9rem;
        }

        .recent-scans-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--primary-orange) 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            box-shadow: 0 15px 50px rgba(229, 197, 115, 0.5);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 500;
            border: 4px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .recent-scans-toggle:hover {
            transform: scale(1.15) translateY(-4px);
            box-shadow: 0 20px 60px rgba(229, 197, 115, 0.6);
        }

        .recent-scans-toggle:active {
            transform: scale(0.95);
        }

        .recent-scans-panel {
            position: fixed;
            bottom: 120px;
            right: 30px;
            width: 400px;
            max-height: 60vh;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            display: none;
            flex-direction: column;
            z-index: 500;
            animation: slideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            overflow: hidden;
        }

        .recent-scans-panel.show {
            display: flex;
        }

        @keyframes slideIn {
            from {
                transform: translateX(20px) scale(0.95);
                opacity: 0;
            }
            to {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
        }

        .panel-header {
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            color: white;
            padding: 22px 28px;
            border-radius: 27px 27px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(229, 197, 115, 0.3);
        }

        .panel-header h6 {
            margin: 0;
            font-weight: 900;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
        }

        .panel-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.6rem;
            cursor: pointer;
            padding: 0;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-radius: 8px;
        }

        .panel-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg) scale(1.1);
        }

        .panel-body {
            padding: 22px 28px;
            overflow-y: auto;
            flex: 1;
        }

        .scan-list-item {
            padding: 16px;
            border: 2px solid rgba(229, 197, 115, 0.2);
            border-radius: 14px;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }

        .scan-list-item:hover {
            background: linear-gradient(135deg, #f0f1f3, #fafafa);
            border-color: var(--primary-gold);
            box-shadow: 0 8px 20px rgba(229, 197, 115, 0.2);
            transform: translateX(4px) translateY(-2px);
        }

        .item-name {
            font-weight: 900;
            color: var(--primary-red);
            margin-bottom: 10px;
            font-size: 1.08rem;
            letter-spacing: -0.3px;
        }

        .item-meta {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            body {
                background-size: 120% !important;
            }

            .scanner-container {
                padding: 12px;
                max-width: 100%;
            }

            .scanner-header {
                margin-bottom: 20px;
            }

            .scanner-header h1 {
                font-size: 1.6rem;
            }

            .scanner-header p {
                font-size: 0.9rem;
            }

            .logo {
                width: 80px;
                height: 80px;
                margin-bottom: 12px;
            }

            .card-body {
                padding: 20px 15px;
                gap: 15px;
            }

            .scanner-location-select {
                margin-bottom: 15px;
            }

            .form-select {
                font-size: 1rem;
            }

            .barcode-input {
                font-size: 1.1rem;
                padding: 14px;
                border-width: 3px;
            }

            .btn-custom {
                padding: 13px;
                font-size: 0.95rem;
            }

            .scanner-status-box {
                padding: 18px;
                margin: 0;
            }

            .status-text {
                font-size: 1rem;
            }

            .recent-scans-panel {
                width: calc(100vw - 30px);
                max-height: 50vh;
                bottom: auto;
                right: 15px;
                left: 15px;
                top: 70px;
                border-radius: 20px;
            }

            .recent-scans-toggle {
                width: 55px;
                height: 55px;
                bottom: 15px;
                right: 15px;
                font-size: 1.3rem;
            }

            .scan-result-modal {
                padding: 30px 25px;
                border-radius: 20px;
                max-width: calc(100vw - 20px);
            }

            .result-title {
                font-size: 1.5rem;
            }

            .result-message {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                background-size: 140% !important;
                overflow-y: auto;
            }

            .scanner-container {
                padding: 8px;
                max-width: 100%;
                min-height: auto;
                justify-content: flex-start;
            }

            .scanner-header {
                margin-bottom: 12px;
            }

            .scanner-header h1 {
                font-size: 1.3rem;
                margin-bottom: 3px;
            }

            .scanner-header p {
                font-size: 0.8rem;
            }

            .logo {
                width: 70px;
                height: 70px;
                margin-bottom: 10px;
            }

            .card-header {
                padding: 16px;
            }

            .card-header h4 {
                font-size: 1.1rem;
            }

            .card-body {
                padding: 14px 12px;
                gap: 12px;
            }

            .scanner-location-select {
                margin-bottom: 10px;
            }

            .location-label {
                font-size: 0.95rem;
            }

            .form-select {
                font-size: 0.95rem;
                padding: 10px 12px !important;
            }

            .barcode-input {
                font-size: 1rem;
                padding: 12px;
                border-width: 2px;
            }

            .barcode-input::placeholder {
                font-size: 0.9rem;
            }

            .scanner-status-box {
                padding: 15px;
                margin: 0;
                border-radius: 12px;
            }

            .scanner-status-box i {
                font-size: 1.8rem;
            }

            .status-text {
                font-size: 0.95rem;
                margin: 8px 0;
            }

            .btn-custom {
                padding: 11px;
                font-size: 0.85rem;
            }

            .btn-custom i {
                font-size: 0.85rem;
            }

            .recent-scans-panel {
                width: calc(100vw - 20px);
                max-height: 45vh;
                bottom: auto;
                right: 10px;
                left: 10px;
                top: 60px;
                border-radius: 18px;
            }

            .panel-header {
                padding: 15px 20px;
                border-radius: 18px 18px 0 0;
            }

            .panel-header h6 {
                font-size: 0.95rem;
            }

            .panel-body {
                padding: 15px 18px;
            }

            .scan-list-item {
                padding: 12px;
                border-radius: 10px;
                margin-bottom: 10px;
            }

            .item-name {
                font-size: 0.95rem;
                margin-bottom: 6px;
            }

            .item-meta {
                font-size: 0.8rem;
            }

            .recent-scans-toggle {
                width: 50px;
                height: 50px;
                bottom: 12px;
                right: 12px;
                font-size: 1.1rem;
            }

            .scan-result-modal {
                padding: 18px 15px;
                border-radius: 16px;
                max-width: calc(100vw - 10px);
                max-height: 75vh;
            }

            .result-icon {
                font-size: 2.5rem;
                margin-bottom: 10px;
            }

            .result-title {
                font-size: 1.1rem;
                margin-bottom: 8px;
            }

            .result-message {
                font-size: 0.85rem;
                margin-bottom: 10px;
            }

            .result-details {
                padding: 10px;
                margin-top: 10px;
            }

            .detail-label {
                font-size: 0.85rem;
            }

            .detail-value {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .scanner-header h1 {
                font-size: 1.1rem;
            }

            .scanner-header p {
                font-size: 0.75rem;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .card-body {
                padding: 12px 10px;
            }

            .barcode-input {
                font-size: 0.95rem;
                padding: 10px;
            }

            .btn-custom {
                padding: 10px;
                font-size: 0.8rem;
            }

            .scan-result-modal {
                padding: 16px 12px;
                border-radius: 14px;
                max-width: calc(100vw - 8px);
                max-height: 70vh;
            }

            .result-icon {
                font-size: 2rem;
                margin-bottom: 8px;
            }

            .result-title {
                font-size: 1rem;
                margin-bottom: 6px;
            }

            .result-message {
                font-size: 0.8rem;
                margin-bottom: 8px;
            }

            .result-details {
                padding: 8px;
                margin-top: 8px;
            }

            .result-detail-row {
                padding: 5px 0;
                font-size: 0.7rem;
            }

            .detail-label {
                font-size: 0.65rem;
            }

            .detail-value {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body>
    <!-- Digital Clock -->
    <div class="digital-clock-container" id="clockContainer">
        <div class="digital-clock">
            <div class="digital-time" id="digitalTime">00:00</div>
            <div class="digital-date" id="digitalDate">Mon, Jan 01</div>
            <div class="digital-ampm" id="digitalAMPM">AM</div>
        </div>
    </div>

    <div class="scanner-container">
        <div class="scanner-header">
            <img src="/assets/images/logo.png" alt="CTU Logo" class="logo">
            <h1><i class="fas fa-barcode"></i> CTU Scanner</h1>
            <p>Code 39 Barcode Scanner System</p>
            <a href="logout.php" class="logout-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

        <div class="scanner-card">
            <div class="card-header">
                <h4><i class="fas fa-barcode me-2"></i>Barcode Scanner</h4>
            </div>

            <div class="card-body">
                <!-- Scanner Location Selection -->
                <div class="scanner-location-select">
                    <label class="location-label"><i class="fas fa-map-marker-alt me-2"></i>Scanner Location:</label>
                    <select id="scannerLocation" class="form-select form-select-lg">
                        <option value="SC001">Main Entrance</option>
                        <option value="SC002">Main Exit</option>
                        <option value="SC004">Vehicular Exit</option>
                    </select>
                </div>

                <!-- Scanner Status -->
                <div class="scanner-status-box" id="statusBox">
                    <i class="fas fa-check-circle"></i>
                    <div class="status-text">Scanner Ready</div>
                    <small style="color: #666;">Place barcode in front of scanner</small>
                </div>

                <!-- Barcode Input Field -->
                <div class="barcode-input-group">
                    <input
                        type="text"
                        id="barcodeInput"
                        class="form-control barcode-input"
                        placeholder="Tap to enter barcode"
                        autocomplete="off"
                        inputmode="text"
                    >
                    <small class="text-muted d-block text-center mt-2" style="color: var(--primary-red) !important;">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="helperText">Last Scan: <strong id="lastScanDisplay">None</strong></span>
                    </small>
                </div>

                <!-- Action Buttons -->
                <div class="btn-group-custom">
                    <button class="btn-custom btn-reset" onclick="resetScanner()">
                        <i class="fas fa-redo me-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan Result Overlay -->
    <div class="scan-result-overlay" id="resultOverlay">
        <div class="scan-result-modal" id="resultModal">
            <div class="result-icon" id="resultIcon"></div>
            <div class="result-title" id="resultTitle"></div>
            <div class="result-message" id="resultMessage"></div>
            <div class="result-details" id="resultDetails" style="display: none;"></div>
        </div>
    </div>

    <!-- Recent Scans Panel -->
    <button class="recent-scans-toggle" id="toggleScans" title="Recent Scans">
        <i class="fas fa-history"></i>
    </button>

    <div class="recent-scans-panel" id="recentScansPanel">
        <div class="panel-header">
            <h6><i class="fas fa-history me-2"></i>Recent Scans</h6>
            <button class="panel-close-btn" onclick="closeRecentScans()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="panel-body" id="scansList">
            <div class="text-center text-muted">Loading scans...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ============================================
        // HARDWARE BARCODE SCANNER - CLEAN IMPLEMENTATION
        // ============================================

        let barcodeBuffer = '';
        let barcodeTimeout = null;
        let lastScanTime = 0;
        const SCAN_COOLDOWN = 500; // ms
        let audioContext = null;
        let recentScans = [];

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Scanner dashboard loaded');
            focusScanner();
            loadRecentScans();
            attachKeyboardListener();
            detectMobileAndSetUI();
            attachResizeListener();
            initializeDigitalClock();
        });

        // Initialize Digital Clock
        function initializeDigitalClock() {
            // Update clock immediately
            updateDigitalClock();
            
            // Update clock every 1000ms (1 second)
            setInterval(updateDigitalClock, 1000);
        }

        // Update Digital Clock Display
        function updateDigitalClock() {
            const now = new Date();
            
            // Get time components
            let hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();
            
            // Determine AM/PM
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            
            // Format time with leading zeros
            const timeString = String(hours).padStart(2, '0') + ':' + 
                              String(minutes).padStart(2, '0') + ':' + 
                              String(seconds).padStart(2, '0');
            
            // Format date
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                               'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            const dayName = dayNames[now.getDay()];
            const monthName = monthNames[now.getMonth()];
            const dateString = dayName + ', ' + monthName + ' ' + String(now.getDate()).padStart(2, '0');
            
            // Update DOM
            document.getElementById('digitalTime').textContent = timeString;
            document.getElementById('digitalDate').textContent = dateString;
            document.getElementById('digitalAMPM').textContent = ampm;
        }

        // Add resize listener to adjust UI when screen is resized
        function attachResizeListener() {
            let resizeTimer = null;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    detectMobileAndSetUI();
                    adjustLayoutForScreenSize();
                }, 250);
            });
        }

        // Adjust layout based on current screen size
        function adjustLayoutForScreenSize() {
            const width = window.innerWidth;
            const body = document.body;
            const container = document.querySelector('.scanner-container');
            const modal = document.querySelector('.scan-result-modal');
            
            if (width <= 480) {
                // Extra small phones
                body.style.fontSize = '13px';
            } else if (width <= 768) {
                // Tablets
                body.style.fontSize = '14px';
            } else {
                // Desktop
                body.style.fontSize = '16px';
            }
        }

        // Detect if mobile and adjust UI
        function detectMobileAndSetUI() {
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent) || window.innerWidth <= 768;
            const input = document.getElementById('barcodeInput');
            const helperText = document.getElementById('helperText');
            
            if (isMobile) {
                input.placeholder = 'Tap here to enter barcode';
                input.setAttribute('inputmode', 'text');
            } else {
                input.placeholder = 'Barcode input area';
            }
        }

        // Focus scanner input
        function focusScanner() {
            const input = document.getElementById('barcodeInput');
            if (input) input.focus();
        }

        // Attach keyboard listener for barcode scanner input
        function attachKeyboardListener() {
            const input = document.getElementById('barcodeInput');
            
            // Handle input event for manual typing on mobile
            input.addEventListener('input', function(event) {
                barcodeBuffer = event.target.value.trim();
            });

            // Handle keydown for hardware scanner and Enter key
            document.addEventListener('keydown', function(event) {
                // Ignore special keys
                if (event.ctrlKey || event.altKey || event.metaKey) return;

                const key = event.key;

                // Process Enter key (scanner sends Enter at end)
                if (key === 'Enter') {
                    event.preventDefault();
                    if (barcodeBuffer.trim()) {
                        processScan(barcodeBuffer.trim());
                    }
                    barcodeBuffer = '';
                    document.getElementById('barcodeInput').value = '';
                    focusScanner();
                    return;
                }

                // Accept alphanumeric and hyphen only (for hardware scanner)
                if (key.length === 1 && /[a-zA-Z0-9\-]/.test(key)) {
                    // Only auto-buffer if input is readonly (hardware scanner mode)
                    // Mobile users type directly in input
                    if (document.getElementById('barcodeInput').readOnly) {
                        event.preventDefault();
                        barcodeBuffer += key;
                        document.getElementById('barcodeInput').value = barcodeBuffer;

                        // Clear buffer after 5 seconds of inactivity
                        clearTimeout(barcodeTimeout);
                        barcodeTimeout = setTimeout(() => {
                            barcodeBuffer = '';
                            document.getElementById('barcodeInput').value = '';
                        }, 5000);
                    }
                }
            });
        }

        // Process scan
        function processScan(barcode) {
            // Check cooldown
            const now = Date.now();
            if (now - lastScanTime < SCAN_COOLDOWN) {
                console.log('Scan cooldown active');
                return;
            }
            lastScanTime = now;

            console.log('Processing barcode:', barcode);
            document.getElementById('lastScanDisplay').textContent = barcode;

            // Send to server
            const location = document.getElementById('scannerLocation').value;
            const data = new URLSearchParams();
            data.append('action', 'scan');
            data.append('qr_data', barcode);
            data.append('scanner_id', location);

            fetch('scan_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data.toString()
            })
            .then(response => response.text())
            .then(text => {
                let result = null;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    showError('Invalid response from server');
                    return;
                }

                handleScanResult(result);
            })
            .catch(error => {
                console.error('Scan error:', error);
                playAlertBuzzer();
                vibrate([200, 100, 200]);
                showError('Network error - check your connection');
            })
            .finally(() => {
                focusScanner();
            });
        }

        // Handle scan result
        function handleScanResult(result) {
            if (result.success) {
                playSuccessSound();
                vibrate([100, 50, 100]);
                showSuccess(result.person);
                loadRecentScans();
            } else if (result.status === 'inactive') {
                playAlertBuzzer();
                vibrate([150, 50, 150]);
                showError(result.message || 'Person is inactive');
            } else if (result.status === 'not_enrolled') {
                playAlertBuzzer();
                vibrate([100, 30, 100, 30, 100]);
                showError(result.message || 'Not enrolled');
            } else {
                playAlertBuzzer();
                vibrate([200, 100, 200, 100, 200]);
                showError(result.message || 'Invalid barcode or access denied');
            }
        }

        // Show success result
        function showSuccess(person) {
            const modal = document.getElementById('resultModal');
            const overlay = document.getElementById('resultOverlay');

            modal.classList.remove('error');
            modal.classList.add('success');

            // Generate avatar HTML
            let avatarHtml = '';
            if (person && person.image) {
                avatarHtml = `<img src="${person.image}" alt="${person.name}" style="width: 150px; height: 150px; border-radius: 50%; border: 5px solid var(--primary-gold); object-fit: cover; margin-bottom: 20px; box-shadow: 0 12px 40px rgba(229, 197, 115, 0.5);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`;
            }
            
            let initials = '';
            if (person) {
                const nameparts = person.name ? person.name.split(' ') : ['?'];
                initials = (nameparts[0][0] + (nameparts[1] ? nameparts[1][0] : '')).toUpperCase();
            }
            
            const defaultAvatarHtml = `<div style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange)); color: white; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; border: 5px solid var(--primary-gold); margin-bottom: 20px; box-shadow: 0 12px 40px rgba(229, 197, 115, 0.5); ${person && person.image ? 'display: none;' : ''}" id="defaultAvatar">${initials}</div>`;

            document.getElementById('resultIcon').innerHTML = avatarHtml + defaultAvatarHtml;
            document.getElementById('resultTitle').textContent = 'Scan Successful';
            document.getElementById('resultMessage').textContent = person ? `Welcome, ${person.name}!` : 'Access granted';

            if (person) {
                const details = document.getElementById('resultDetails');
                let html = `
                    <div class="result-detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${escapeHtml(person.name)}</span>
                    </div>
                    <div class="result-detail-row">
                        <span class="detail-label">ID:</span>
                        <span class="detail-value">${escapeHtml(person.id)}</span>
                    </div>
                    <div class="result-detail-row">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">${escapeHtml(person.type)}</span>
                    </div>
                    <div class="result-detail-row">
                        <span class="detail-label">Action:</span>
                        <span class="detail-value">${escapeHtml(person.action)}</span>
                    </div>
                `;
                if (person.department) {
                    html += `
                        <div class="result-detail-row">
                            <span class="detail-label">Department:</span>
                            <span class="detail-value">${escapeHtml(person.department)}</span>
                        </div>
                    `;
                }
                if (person.course) {
                    html += `
                        <div class="result-detail-row">
                            <span class="detail-label">Course:</span>
                            <span class="detail-value">${escapeHtml(person.course)}</span>
                        </div>
                    `;
                }
                if (person.year) {
                    html += `
                        <div class="result-detail-row">
                            <span class="detail-label">Year:</span>
                            <span class="detail-value">${escapeHtml(person.year)}</span>
                        </div>
                    `;
                }
                details.innerHTML = html;
                details.style.display = 'block';
            }

            overlay.classList.add('show');
            setTimeout(() => {
                overlay.classList.remove('show');
            }, 3000);
        }

        // Show error result
        function showError(message) {
            const modal = document.getElementById('resultModal');
            const overlay = document.getElementById('resultOverlay');

            modal.classList.remove('success');
            modal.classList.add('error');

            document.getElementById('resultIcon').innerHTML = '<i class="fas fa-times-circle error"></i>';
            document.getElementById('resultTitle').textContent = 'Scan Failed';
            document.getElementById('resultMessage').textContent = message;
            document.getElementById('resultDetails').style.display = 'none';

            overlay.classList.add('show');
            setTimeout(() => {
                overlay.classList.remove('show');
            }, 3000);
        }

        // Audio feedback
        function getAudioContext() {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            return audioContext;
        }

        function playSound(freq, duration = 0.2) {
            try {
                const ctx = getAudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.frequency.value = freq;
                osc.type = 'sine';

                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);

                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + duration);
            } catch (e) {
                console.log('Audio not available');
            }
        }

        function playSuccessSound() {
            playSound(800, 0.15);
        }

        function playErrorSound() {
            // Make a loud continuous buzzer sound "teeeeeeeeeet"
            try {
                const ctx = getAudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                osc.frequency.value = 800; // Higher frequency for buzzer effect
                osc.type = 'square'; // Square wave for buzzer sound
                
                // LOUD volume - maximum audible without distortion
                const now = ctx.currentTime;
                const duration = 0.8; // Long sustained buzzer
                
                gain.gain.setValueAtTime(0.9, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + duration);
                
                osc.start(now);
                osc.stop(now + duration);
            } catch (e) {
                console.log('Audio error:', e);
            }
        }

        function playAlertBuzzer() {
            // Single long buzzer sound "beeeeeeeepppp"
            try {
                const ctx = getAudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                osc.frequency.value = 950;
                osc.type = 'square';
                
                const now = ctx.currentTime;
                const duration = 1.2; // Long sustained buzz
                
                gain.gain.setValueAtTime(0.85, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + duration);
                
                osc.start(now);
                osc.stop(now + duration);
            } catch (e) {
                console.log('Audio error:', e);
            }
        }

        function playWarningSound(count) {
            for (let i = 0; i < count; i++) {
                setTimeout(() => playSound(400, 0.15), i * 250);
            }
        }

        // Vibration feedback
        function vibrate(pattern) {
            if (navigator.vibrate) {
                navigator.vibrate(pattern);
            }
        }

        // Reset scanner
        function resetScanner() {
            barcodeBuffer = '';
            document.getElementById('barcodeInput').value = '';
            focusScanner();
        }

        // Recent scans
        function loadRecentScans() {
            fetch('get_recent_scans.php')
                .then(r => r.json())
                .then(data => {
                    if (data.scans) {
                        recentScans = data.scans.slice(0, 10);
                        updateRecentScansDisplay();
                    }
                })
                .catch(err => console.error('Failed to load recent scans:', err));
        }

        function updateRecentScansDisplay() {
            const list = document.getElementById('scansList');
            if (recentScans.length === 0) {
                list.innerHTML = '<div class="text-center text-muted">No scans today</div>';
                return;
            }

            list.innerHTML = recentScans.map(scan => `
                <div class="scan-list-item">
                    <div class="item-name">${escapeHtml(scan.name)}</div>
                    <div class="item-meta">
                        <i class="fas fa-clock"></i> ${formatTime(scan.timestamp)}<br>
                        <i class="fas fa-id-badge"></i> ${escapeHtml(scan.id)}<br>
                        <i class="fas fa-sign-in-alt"></i> ${escapeHtml(scan.action || 'Check-in')}
                    </div>
                </div>
            `).join('');
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }

        // Recent scans panel
        document.getElementById('toggleScans').addEventListener('click', function() {
            document.getElementById('recentScansPanel').classList.add('show');
            loadRecentScans();
        });

        function closeRecentScans() {
            document.getElementById('recentScansPanel').classList.remove('show');
        }

        // Utility
        function escapeHtml(str) {
            if (!str) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;'
            };
            return String(str).replace(/[&<>"]/g, m => map[m]);
        }

        // Prevent default zoom
        document.addEventListener('touchstart', e => {
            if (e.touches.length > 1) e.preventDefault();
        }, { passive: false });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#FF9600">
    <title>CTU Scanner - QR Code Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #DBB356;
            --primary-red: #E00000;
            --primary-orange: #FF9600;
            --gold-light: #F5D982;
            --red-dark: #B30000;
            --orange-light: #FFB347;
            --scanner-glow: rgba(219, 179, 86, 0.6);
            --card-shadow: rgba(0, 0, 0, 0.15);
        }

        /* Add these styles to your scanner index.php <style> section */

/* Scan Result Avatar Styles */
.scan-avatar-container {
    margin-bottom: 15px;
    display: flex;
    justify-content: center;
}

.scan-result-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary-gold);
    box-shadow: 0 8px 25px rgba(219, 179, 86, 0.4);
    animation: avatarPulse 0.6s ease-out;
}

.scan-result-avatar-default {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    border: 4px solid var(--primary-gold);
    box-shadow: 0 8px 25px rgba(219, 179, 86, 0.4);
    animation: avatarPulse 0.6s ease-out;
}

@keyframes avatarPulse {
    0% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Action Badge */
.scan-action-badge {
    background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    margin-top: 8px;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(219, 179, 86, 0.3);
}

.scan-action-badge.entry {
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.scan-action-badge.exit {
    background: linear-gradient(135deg, var(--primary-red), #dc3545);
    box-shadow: 0 4px 15px rgba(224, 0, 0, 0.3);
}

/* Recent Scans Avatar Styles */
.scan-item {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    padding: 16px 20px;
    border-radius: 15px;
    margin-bottom: 12px;
    border-left: 5px solid var(--primary-gold);
    font-size: 0.95rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    border: 2px solid rgba(219, 179, 86, 0.15);
    transition: all 0.3s ease;
}

.scan-item:hover {
    transform: translateX(3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    border-left-color: var(--primary-orange);
}

.scan-item-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.scan-avatar-small {
    flex-shrink: 0;
}

.recent-scan-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-gold);
    box-shadow: 0 4px 12px rgba(219, 179, 86, 0.3);
}

.recent-scan-avatar-default {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    border: 3px solid var(--primary-gold);
    box-shadow: 0 4px 12px rgba(219, 179, 86, 0.3);
}

.scan-item-info {
    flex: 1;
    min-width: 0;
}

.scan-time {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 2px;
    font-weight: 600;
}

.scan-name {
    font-weight: 800;
    color: #333;
    font-size: 1rem;
    margin-bottom: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.scan-action-mini {
    background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(219, 179, 86, 0.3);
}

.scan-action-mini.entry {
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.scan-action-mini.exit {
    background: linear-gradient(135deg, var(--primary-red), #dc3545);
    box-shadow: 0 2px 8px rgba(224, 0, 0, 0.3);
}

.scan-item-details {
    padding-left: 57px; /* Align with name after avatar */
}

.scan-role {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 3px;
    font-weight: 600;
}

.scan-data {
    font-weight: 700;
    color: var(--primary-orange);
    font-size: 0.85rem;
    margin-bottom: 4px;
}

.scan-location {
    font-size: 0.8rem;
    color: #28a745;
    font-weight: 600;
}

/* Enhanced scan result container */
.scan-result-container {
    position: relative;
    overflow: hidden;
}

.scan-success-details {
    position: relative;
    z-index: 2;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .scan-result-avatar,
    .scan-result-avatar-default {
        width: 70px;
        height: 70px;
        border-width: 3px;
    }

    .scan-result-avatar-default {
        font-size: 24px;
    }

    .recent-scan-avatar,
    .recent-scan-avatar-default {
        width: 40px;
        height: 40px;
        border-width: 2px;
    }

    .recent-scan-avatar-default {
        font-size: 14px;
    }

    .scan-item-details {
        padding-left: 52px; /* Adjust for smaller avatar */
    }

    .scan-person-name {
        font-size: 1.2rem;
    }

    .scan-action-badge {
        font-size: 0.8rem;
        padding: 5px 14px;
    }

    .scan-item {
        padding: 14px 16px;
        margin-bottom: 10px;
    }

    .scan-item-header {
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .scan-result-avatar,
    .scan-result-avatar-default {
        width: 60px;
        height: 60px;
        border-width: 2px;
    }

    .scan-result-avatar-default {
        font-size: 20px;
    }

    .recent-scan-avatar,
    .recent-scan-avatar-default {
        width: 35px;
        height: 35px;
        border-width: 2px;
    }

    .recent-scan-avatar-default {
        font-size: 12px;
    }

    .scan-item-details {
        padding-left: 47px; /* Adjust for smallest avatar */
    }

    .scan-person-name {
        font-size: 1.1rem;
    }

    .scan-action-badge {
        font-size: 0.75rem;
        padding: 4px 12px;
    }

    .scan-action-mini {
        font-size: 0.65rem;
        padding: 3px 8px;
    }

    .scan-item {
        padding: 12px 14px;
        margin-bottom: 8px;
    }
}

/* Loading state for avatars */
.scan-result-avatar,
.recent-scan-avatar {
    background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
    transition: all 0.3s ease;
}

.scan-result-avatar:hover,
.recent-scan-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(219, 179, 86, 0.5);
}

/* Avatar loading animation */
@keyframes avatarLoading {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.avatar-loading {
    background: linear-gradient(90deg, #f0f0f0 0px, #e0e0e0 40px, #f0f0f0 80px);
    background-size: 200px;
    animation: avatarLoading 1.5s infinite;
}

/* Scan result enhancements */
.scan-success-icon {
    font-size: 2.2rem;
    color: var(--primary-gold);
    margin-bottom: 8px;
    animation: successPulse 1s ease-in-out;
    filter: drop-shadow(0 0 10px rgba(219, 179, 86, 0.5));
}

/* Enhanced floating window for mobile */
@media (max-width: 768px) {
    .recent-scans-float {
        border-radius: 20px;
        border-width: 1px;
    }

    .scan-item {
        border-radius: 12px;
        padding: 12px 16px;
    }

    .scan-item-header {
        align-items: flex-start;
        gap: 8px;
    }

    .scan-name {
        font-size: 0.95rem;
        line-height: 1.3;
        white-space: normal;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: transparent;
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            position: relative;
        }

        /* Super Large CTU Logo Background */
        body::before {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(120vw, 120vh, 1200px);
            height: min(120vw, 120vh, 1200px);
            background-image: url('/assets/images/logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.08;
            z-index: -1;
            pointer-events: none;
            filter: grayscale(0) brightness(1.1) contrast(1.1);
            animation: logoFloat 25s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { 
                transform: translate(-50%, -50%) scale(1) rotate(0deg);
                opacity: 0.08;
            }
            25% { 
                transform: translate(-52%, -48%) scale(1.02) rotate(0.5deg);
                opacity: 0.06;
            }
            50% { 
                transform: translate(-48%, -52%) scale(1.01) rotate(-0.3deg);
                opacity: 0.12;
            }
            75% { 
                transform: translate(-51%, -49%) scale(1.03) rotate(0.2deg);
                opacity: 0.07;
            }
        }

        /* Additional decorative elements */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: 
                radial-gradient(circle at 20% 20%, rgba(219, 179, 86, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(224, 0, 0, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 40% 90%, rgba(255, 150, 0, 0.025) 0%, transparent 50%);
            z-index: -2;
            pointer-events: none;
        }

        .scanner-container {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            padding: 15px;
            position: relative;
            z-index: 1;
        }

        .scanner-header {
            text-align: center;
            margin-bottom: 20px;
            flex-shrink: 0;
            position: relative;
        }

        .logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            border-radius: 50%;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 
                0 10px 30px rgba(219, 179, 86, 0.4),
                0 0 0 4px rgba(255, 255, 255, 0.1),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
            border: 3px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: rotate(45deg);
            animation: logoShine 4s ease-in-out infinite;
        }

        @keyframes logoShine {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .scanner-header h1 {
            color: #333;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .scanner-header p {
            color: #555;
            font-size: 1.2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .scanner-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            box-shadow: 
                0 30px 60px var(--card-shadow),
                0 0 0 1px rgba(219, 179, 86, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
            border: 2px solid rgba(219, 179, 86, 0.2);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--primary-orange) 50%, var(--primary-red) 100%);
            padding: 25px 30px;
            border-radius: 25px 25px 0 0;
            color: white;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            animation: headerShine 5s ease-in-out infinite;
        }

        @keyframes headerShine {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }

        .card-header h4 {
            margin: 0;
            font-weight: 800;
            font-size: 1.4rem;
            text-shadow: 0 3px 6px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .scanner-controls select {
            background: rgba(255, 255, 255, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: white;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 15px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .scanner-controls select:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.15);
        }

        .scanner-controls select option {
            background: #333;
            color: white;
            padding: 12px;
        }

        .card-body {
            padding: 30px 25px 20px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 0;
            gap: 15px;
        }

        .qr-reader {
            width: 100%;
            max-width: 380px;
            height: 300px;
            border: 5px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(45deg, var(--primary-gold), var(--primary-orange), var(--primary-red)) border-box;
            border-radius: 25px;
            overflow: hidden;
            position: relative;
            background-color: #000;
            touch-action: manipulation;
            flex-shrink: 0;
            box-shadow: 
                0 20px 40px var(--card-shadow),
                inset 0 0 0 2px rgba(219, 179, 86, 0.15);
        }

        .qr-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            background: #000;
            border-radius: 20px;
        }

        /* Enhanced Scanner Frame */
        .scanner-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 220px;
            border: none;
            border-radius: 15px;
            z-index: 10;
            pointer-events: none;
            background: transparent;
        }

        /* Animated Corner Brackets with new colors */
        .scanner-frame::before,
        .scanner-frame::after {
            content: '';
            position: absolute;
            width: 35px;
            height: 35px;
            border: 5px solid var(--primary-gold);
            border-radius: 6px;
            animation: cornerPulse 2.5s ease-in-out infinite;
        }

        .scanner-frame::before {
            top: -5px;
            left: -5px;
            border-right: none;
            border-bottom: none;
            box-shadow: -3px -3px 15px rgba(219, 179, 86, 0.4);
        }

        .scanner-frame::after {
            bottom: -5px;
            right: -5px;
            border-left: none;
            border-top: none;
            box-shadow: 3px 3px 15px rgba(219, 179, 86, 0.4);
        }

        /* Additional corner brackets */
        .scanner-frame-extra::before {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 35px;
            height: 35px;
            border: 5px solid var(--primary-orange);
            border-left: none;
            border-bottom: none;
            border-radius: 6px;
            animation: cornerPulse 2.5s ease-in-out infinite 0.6s;
            box-shadow: 3px -3px 15px rgba(255, 150, 0, 0.4);
        }

        .scanner-frame-extra::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: -5px;
            width: 35px;
            height: 35px;
            border: 5px solid var(--primary-red);
            border-right: none;
            border-top: none;
            border-radius: 6px;
            animation: cornerPulse 2.5s ease-in-out infinite 0.6s;
            box-shadow: -3px 3px 15px rgba(224, 0, 0, 0.4);
        }

        @keyframes cornerPulse {
            0%, 100% { 
                opacity: 0.8;
                transform: scale(1);
                filter: brightness(1) drop-shadow(0 0 10px currentColor);
            }
            50% { 
                opacity: 1;
                transform: scale(1.1);
                filter: brightness(1.4) drop-shadow(0 0 20px currentColor);
            }
        }

        /* Scanning Line Animation with new colors */
        .scanning-line {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 88%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--primary-gold), var(--primary-orange), transparent);
            border-radius: 4px;
            animation: scanLine 3.5s ease-in-out infinite;
            box-shadow: 0 0 20px var(--primary-gold), 0 0 40px rgba(219, 179, 86, 0.6);
            opacity: 0.95;
        }

        @keyframes scanLine {
            0% { 
                top: 8%; 
                opacity: 0;
                transform: translateX(-50%) scaleX(0.4);
            }
            15% { 
                opacity: 0.95;
                transform: translateX(-50%) scaleX(1);
            }
            85% { 
                opacity: 0.95;
                transform: translateX(-50%) scaleX(1);
            }
            100% { 
                top: 92%; 
                opacity: 0;
                transform: translateX(-50%) scaleX(0.4);
            }
        }

        /* Grid Overlay with new colors */
        .scanner-grid {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 240px;
            height: 240px;
            background-image: 
                linear-gradient(rgba(219, 179, 86, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(219, 179, 86, 0.12) 1px, transparent 1px);
            background-size: 24px 24px;
            border-radius: 15px;
            opacity: 0.5;
            animation: gridFade 4.5s ease-in-out infinite;
        }

        @keyframes gridFade {
            0%, 100% { 
                opacity: 0.2; 
                transform: translate(-50%, -50%) scale(1);
            }
            50% { 
                opacity: 0.5;
                transform: translate(-50%, -50%) scale(1.02);
            }
        }

        .scanner-status {
            margin-top: 20px;
            text-align: center;
            flex-shrink: 0;
            width: 100%;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .alert {
            border-radius: 18px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            font-size: 1.05rem;
            padding: 18px 25px;
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.15);
            font-weight: 600;
            margin-bottom: 0;
            position: relative;
            animation: slideInUp 0.3s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.92), rgba(32, 201, 151, 0.92));
            color: white;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
        }

        .alert-danger {
            background: linear-gradient(135deg, var(--primary-red), #CC0000);
            color: white;
            box-shadow: 0 10px 30px rgba(224, 0, 0, 0.4);
        }

        .alert-warning {
            background: linear-gradient(135deg, var(--primary-orange), var(--primary-gold));
            color: white;
            box-shadow: 0 10px 30px rgba(255, 150, 0, 0.4);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.92), rgba(0, 123, 255, 0.92));
            color: white;
            box-shadow: 0 10px 30px rgba(23, 162, 184, 0.4);
        }

        .spinner-border {
            color: var(--primary-orange) !important;
            width: 3rem;
            height: 3rem;
            border-width: 4px;
        }

        /* Enhanced Permission Button */
        .permission-btn {
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            color: white;
            border: none;
            padding: 18px 35px;
            border-radius: 15px;
            font-size: 1.15rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(219, 179, 86, 0.5);
            margin: 15px 10px;
            border: 3px solid transparent;
            backdrop-filter: blur(15px);
            position: relative;
            overflow: hidden;
        }

        .permission-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: left 0.5s ease;
        }

        .permission-btn:hover::before {
            left: 100%;
        }

        .permission-btn:hover {
            background: linear-gradient(135deg, #c49b47, #e6890a);
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(219, 179, 86, 0.6);
            border-color: rgba(255, 255, 255, 0.25);
        }

        .permission-btn:active {
            transform: translateY(-2px);
        }

        /* Recent Scans Float Window - Enhanced with new colors */
        .recent-scans-float {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 340px;
            max-height: 60vh;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 25px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            z-index: 1000;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(110%);
            overflow: hidden;
            border: 2px solid rgba(219, 179, 86, 0.2);
        }

        .recent-scans-float.open {
            transform: translateX(0);
        }

        .float-header {
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .float-header h6 {
            margin: 0;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .close-btn, .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            min-width: 45px;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.1);
        }

        .toggle-btn {
            position: fixed;
            top: 85px;
            right: 20px;
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-orange));
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            z-index: 999;
            box-shadow: 0 10px 30px rgba(219, 179, 86, 0.4);
            border: 4px solid rgba(255, 255, 255, 0.2);
        }

        .toggle-btn:hover {
            background: linear-gradient(135deg, #c49b47, #e6890a);
            transform: scale(1.15);
            box-shadow: 0 15px 40px rgba(219, 179, 86, 0.5);
        }

        .float-body {
            padding: 20px 25px;
            max-height: calc(60vh - 80px);
            overflow-y: auto;
        }

        .scan-item {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            padding: 18px 22px;
            border-radius: 15px;
            margin-bottom: 15px;
            border-left: 5px solid var(--primary-gold);
            font-size: 0.95rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            border: 2px solid rgba(219, 179, 86, 0.15);
            transition: all 0.3s ease;
        }

        .scan-item:hover {
            transform: translateX(3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            border-left-color: var(--primary-orange);
        }

        .scan-item:last-child {
            margin-bottom: 0;
        }

        .scan-time {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .scan-name {
            font-weight: 800;
            color: #333;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .scan-role {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .scan-data {
            font-weight: 700;
            color: var(--primary-orange);
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .scan-location {
            font-size: 0.85rem;
            color: #28a745;
            font-weight: 600;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            body::before {
                width: min(130vw, 130vh, 1000px);
                height: min(130vw, 130vh, 1000px);
                opacity: 0.06;
            }

            .scanner-container {
                padding: 10px;
            }

            .scanner-header h1 {
                font-size: 1.8rem;
            }

            .scanner-header p {
                font-size: 1.1rem;
            }

            .logo {
                width: 65px;
                height: 65px;
            }

            .qr-reader {
                max-width: 100%;
                height: 280px;
            }

            .scanner-frame {
                width: 200px;
                height: 200px;
            }

            .recent-scans-float {
                width: calc(100vw - 25px);
                right: 12px;
                left: 12px;
                max-height: 50vh;
            }

            .toggle-btn {
                width: 55px;
                height: 55px;
                right: 15px;
                top: 80px;
            }

            .card-body {
                padding: 25px 20px 15px 20px;
                gap: 12px;
            }

            .scanner-status {
                margin-top: 15px;
                min-height: 100px;
            }

            .scan-result-container,
            .scan-error-container {
                padding: 15px;
                margin-top: 10px;
            }

            .scan-person-name {
                font-size: 1.2rem;
            }

            .scan-success-icon {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            body::before {
                width: min(140vw, 140vh, 800px);
                height: min(140vw, 140vh, 800px);
                opacity: 0.05;
            }

            .scanner-container {
                padding: 8px;
            }

            .qr-reader {
                height: 260px;
                border-width: 4px;
            }

            .scanner-frame {
                width: 180px;
                height: 180px;
            }

            .card-header h4 {
                font-size: 1.2rem;
            }

            .card-body {
                padding: 20px 15px 10px 15px;
                gap: 10px;
            }

            .scanner-status {
                margin-top: 10px;
                min-height: 90px;
            }

            .scan-result-container,
            .scan-error-container {
                padding: 12px;
                margin-top: 8px;
                min-height: 80px;
            }

            .scan-person-name {
                font-size: 1.1rem;
            }

            .scan-person-details {
                font-size: 0.9rem;
            }

            .scan-person-id {
                font-size: 0.85rem;
                padding: 4px 12px;
            }
        }

        /* Prevent zoom on double tap */
        * {
            touch-action: manipulation;
        }

        /* Enhanced Animations */
        @keyframes scanSuccess {
            0% { 
                transform: scale(1);
                box-shadow: 0 20px 40px var(--card-shadow);
            }
            50% { 
                transform: scale(1.03);
                box-shadow: 0 25px 60px rgba(219, 179, 86, 0.5);
            }
            100% { 
                transform: scale(1);
                box-shadow: 0 20px 40px var(--card-shadow);
            }
        }

        .scan-success {
            animation: scanSuccess 0.5s ease;
        }

        /* Status indicator improvements */
        .status-indicator {
            position: absolute;
            top: 18px;
            left: 18px;
            background: rgba(40, 167, 69, 0.93);
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 700;
            backdrop-filter: blur(15px);
            z-index: 15;
            animation: statusFade 2.5s ease-in-out infinite;
        }

        @keyframes statusFade {
            0%, 100% { opacity: 0.85; }
            50% { opacity: 1; }
        }

        /* Scan Result Enhancement */
        .scan-result-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 20px;
            margin-top: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--primary-gold);
            backdrop-filter: blur(20px);
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .scan-result-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(219, 179, 86, 0.1), transparent);
            animation: resultShine 2s ease-in-out infinite;
        }

        @keyframes resultShine {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }

        .scan-success-details {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .scan-success-icon {
            font-size: 2.5rem;
            color: var(--primary-gold);
            margin-bottom: 10px;
            animation: successPulse 1s ease-in-out;
        }

        @keyframes successPulse {
            0% { transform: scale(0.5); opacity: 0; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        .scan-person-name {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary-red);
            margin-bottom: 5px;
        }

        .scan-person-details {
            font-size: 1rem;
            color: #555;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .scan-person-id {
            font-size: 0.95rem;
            color: var(--primary-orange);
            font-weight: 700;
            background: rgba(255, 150, 0, 0.1);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }

        .scan-error-container {
            background: linear-gradient(135deg, rgba(224, 0, 0, 0.95), rgba(180, 0, 0, 0.95));
            color: white;
            border-radius: 20px;
            padding: 20px;
            margin-top: 15px;
            box-shadow: 0 15px 40px rgba(224, 0, 0, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            text-align: center;
        }

        .scan-error-icon {
            font-size: 2rem;
            margin-right: 15px;
            animation: errorShake 0.5s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <div class="scanner-header">
             <div class="logo">
                <img src="/assets/images/logo.png" alt="CTU Logo" style="width: 65px; height: 65px; object-fit: contain;">
            </div>
            <h1>CTU Access Control</h1>
            <p>Scan QR Code to Enter/Exit</p>
        </div>
        
        <div class="scanner-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <h4><i class="fas fa-qrcode me-2"></i>QR Scanner</h4>
                    <div class="scanner-controls">
                        <select id="scannerSelect" class="form-select form-select-sm">
                            <option value="SC001">Main Entrance</option>
                            <option value="SC002">Main Exit</option>
                            <option value="SC004">Vehicular Exit</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="qr-reader" id="reader">
                    <div class="scanner-frame"></div>
                    <div class="scanner-frame-extra"></div>
                    <div class="scanning-line"></div>
                    <div class="scanner-grid"></div>
                    <div class="status-indicator" style="display: none;">
                        <i class="fas fa-camera me-1"></i>Ready
                    </div>
                </div>
                
                <div class="scanner-status">
                    <div id="scanResult" style="display: none;"></div>
                    <div id="scannerStatus" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Initializing camera...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Button for Recent Scans -->
    <button class="toggle-btn" id="toggleRecentScans" title="Recent Scans">
        <i class="fas fa-history"></i>
    </button>

    <!-- Floating Recent Scans Window -->
    <div class="recent-scans-float" id="recentScansFloat">
        <div class="float-header">
            <h6><i class="fas fa-history me-2"></i>Recent Scans</h6>
            <button class="close-btn" id="closeRecentScans">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="float-body" id="recentScans">
            <div class="text-center text-muted py-3">Loading recent scans...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Enhanced mobile QR scanner with improved camera handling
        let html5QrcodeScanner;
        let isScanning = false;
        let recentScansData = [];
        let lastScanTime = 0;
        let scanCooldown = 500; // Reduced from 2000ms to 500ms
        let cameraStream = null;

        // Storage keys
        const STORAGE_KEY = 'ctu_recent_scans';
        const DATE_KEY = 'ctu_scans_date';

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent zoom on iOS
            document.addEventListener('touchstart', function(e) {
                if (e.touches.length > 1) {
                    e.preventDefault();
                }
            }, { passive: false });

            // Prevent double-tap zoom
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(e) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, false);

            loadRecentScans();
            setupRecentScansToggle();
            
            // Small delay to ensure DOM is fully ready
            setTimeout(() => {
                initializeScanner();
            }, 100);
        });

        function initializeScanner() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            
            console.log('Initializing scanner - Mobile:', isMobile, 'Secure:', isSecureContext);

            // Check for secure context on mobile
            if (isMobile && !isSecureContext) {
                showHTTPSError();
                return;
            }

            // Check for camera support
            if (!navigator.mediaDevices?.getUserMedia) {
                showError("Camera not supported in this browser. Please use Chrome, Firefox, or Safari.");
                return;
            }

            // Request camera permission immediately with improved constraints
            requestCameraAccess();
        }

        async function requestCameraAccess() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            try {
                // Enhanced camera constraints for better mobile support
                const constraints = {
                    video: {
                        facingMode: isMobile ? { ideal: 'environment' } : 'user',
                        width: { ideal: 1280, min: 640 },
                        height: { ideal: 720, min: 480 },
                        aspectRatio: { ideal: 16/9 },
                        frameRate: { ideal: 30, max: 60 }
                    }
                };

                console.log('Requesting camera access with constraints:', constraints);
                
                // Request permission
                cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                console.log('Camera permission granted');
                
                // Stop the stream immediately - we just needed permission
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;

                // Now initialize the QR scanner
                initializeQRScanner();
                
            } catch (error) {
                console.error('Camera access error:', error);
                handleCameraError(error);
            }
        }

        async function initializeQRScanner() {
            try {
                const devices = await Html5Qrcode.getCameras();
                console.log('Available cameras:', devices);
                
                if (!devices || devices.length === 0) {
                    throw new Error('No cameras found');
                }

                // Select appropriate camera
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                let selectedCamera = devices[0];
                
                if (isMobile && devices.length > 1) {
                    // Look for back/environment camera
                    const backCamera = devices.find(camera => {
                        const label = camera.label.toLowerCase();
                        return label.includes('back') || label.includes('rear') || label.includes('environment');
                    });
                    
                    if (backCamera) {
                        selectedCamera = backCamera;
                        console.log('Selected back camera:', selectedCamera.label);
                    }
                }

                // Enhanced config for better mobile performance
                const config = {
                    fps: isMobile ? 15 : 20,
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                        const qrboxSize = Math.floor(minEdge * (isMobile ? 0.75 : 0.7));
                        console.log('QR box size:', qrboxSize, 'Viewfinder:', viewfinderWidth, 'x', viewfinderHeight);
                        return {
                            width: qrboxSize,
                            height: qrboxSize
                        };
                    },
                    aspectRatio: 1.777778, // 16:9
                    showTorchButtonIfSupported: true,
                    showZoomSliderIfSupported: true,
                    defaultZoomValueIfSupported: 1,
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                    rememberLastUsedCamera: true,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                };

                html5QrcodeScanner = new Html5Qrcode("reader");
                await startScanning(selectedCamera.id, config);
                
                // Add camera selector if multiple cameras
                if (devices.length > 1) {
                    addCameraSelector(devices);
                }
                
            } catch (error) {
                console.error('QR Scanner initialization error:', error);
                showError(`Scanner initialization failed: ${error.message}`);
            }
        }

        async function startScanning(cameraId, config) {
            try {
                console.log('Starting scanner with camera:', cameraId);
                
                await html5QrcodeScanner.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                );
                
                console.log('Scanner started successfully');
                document.getElementById('scannerStatus').style.display = 'none';
                
                // Show ready indicator
                const statusIndicator = document.querySelector('.status-indicator');
                if (statusIndicator) {
                    statusIndicator.style.display = 'block';
                }
                
                isScanning = true;
                
                // Add haptic feedback if available
                if (window.navigator.vibrate) {
                    window.navigator.vibrate(50); // Short vibration to indicate scanner is ready
                }
                
            } catch (error) {
                console.error('Failed to start scanning:', error);
                throw error;
            }
        }

        function handleCameraError(error) {
            console.error('Camera error details:', error);
            
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                showPermissionError();
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                showError("No camera found on this device. Please ensure your device has a camera.");
            } else if (error.name === 'NotSupportedError') {
                showError("Camera not supported on this device or browser.");
            } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                showError("Camera is being used by another application. Please close other camera apps and try again.");
            } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
                console.log('Trying with fallback constraints...');
                tryFallbackCamera();
            } else {
                showError(`Camera error: ${error.message || 'Unknown error occurred'}`);
            }
        }

        async function tryFallbackCamera() {
            try {
                console.log('Attempting fallback camera access...');
                
                // Try with minimal constraints
                const fallbackConstraints = {
                    video: {
                        facingMode: 'environment'
                    }
                };
                
                cameraStream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
                
                // Try simpler QR config
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    const simpleConfig = {
                        fps: 10,
                        qrbox: { width: 200, height: 200 },
                        aspectRatio: 1.0
                    };
                    
                    html5QrcodeScanner = new Html5Qrcode("reader");
                    await startScanning(devices[0].id, simpleConfig);
                }
                
            } catch (fallbackError) {
                console.error('Fallback camera also failed:', fallbackError);
                showPermissionError();
            }
        }

        function showHTTPSError() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>HTTPS Required for Mobile Camera</strong><br>
                    <small>Mobile cameras require secure connection (HTTPS)</small><br>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="tryHTTPS()">
                            <i class="fas fa-lock me-1"></i> Try HTTPS
                        </button>
                        <button class="permission-btn" onclick="tryDesktop()">
                            <i class="fas fa-desktop me-1"></i> Use Desktop
                        </button>
                    </div>
                </div>
            `;
        }

        function showPermissionError() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-camera-retro me-2"></i>
                    <strong>Camera Permission Required</strong><br>
                    <small>Please allow camera access to scan QR codes.</small><br>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="retryCamera()">
                            <i class="fas fa-camera me-1"></i> Enable Camera
                        </button>
                    </div>
                    <div class="mt-3">
                        <small style="text-align: left; display: block; line-height: 1.4;">
                            <strong>Solutions:</strong><br>
                            • Click camera icon in address bar<br>
                            • Refresh page and allow camera<br>
                            • Check browser camera settings
                        </small>
                    </div>
                </div>
            `;
        }

        function showError(message) {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Scanner Error</strong><br>
                    <small>${message}</small>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="retryCamera()">
                            <i class="fas fa-refresh me-1"></i> Try Again
                        </button>
                    </div>
                </div>
            `;
        }

        // Global functions for buttons
        window.retryCamera = function() {
            console.log('Retrying camera initialization...');
            document.getElementById('scannerStatus').innerHTML = `
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Requesting camera access...</p>
            `;
            
            setTimeout(() => {
                initializeScanner();
            }, 500);
        }

        window.tryHTTPS = function() {
            const currentUrl = window.location.href;
            if (currentUrl.startsWith('http://')) {
                const httpsUrl = currentUrl.replace('http://', 'https://');
                window.location.href = httpsUrl;
            } else {
                retryCamera();
            }
        }

        window.tryDesktop = function() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Desktop Instructions</strong><br>
                    <small>Open this page on a desktop/laptop computer for easier camera access without HTTPS requirements.</small>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="retryCamera()">
                            <i class="fas fa-mobile-alt me-1"></i> Try Mobile Anyway
                        </button>
                    </div>
                </div>
            `;
        }

        function addCameraSelector(cameras) {
            const headerControls = document.querySelector('.scanner-controls');
            
            // Check if camera selector already exists
            if (document.getElementById('cameraSelect')) {
                return;
            }
            
            const cameraSelector = document.createElement('select');
            cameraSelector.id = 'cameraSelect';
            cameraSelector.className = 'form-select form-select-sm mt-1';
            cameraSelector.innerHTML = cameras.map((camera, index) => {
                const label = camera.label || `Camera ${index + 1}`;
                const isBack = label.toLowerCase().includes('back') || label.toLowerCase().includes('rear') || label.toLowerCase().includes('environment');
                return `<option value="${camera.id}">${isBack ? '📷 ' : '🤳 '}${label}</option>`;
            }).join('');
            
            cameraSelector.addEventListener('change', async function() {
                if (isScanning && html5QrcodeScanner) {
                    try {
                        console.log('Switching to camera:', this.value);
                        await html5QrcodeScanner.stop();
                        
                        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                        const config = {
                            fps: isMobile ? 15 : 20,
                            qrbox: function(viewfinderWidth, viewfinderHeight) {
                                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                const qrboxSize = Math.floor(minEdge * 0.7);
                                return { width: qrboxSize, height: qrboxSize };
                            },
                            aspectRatio: 1.777778,
                            showTorchButtonIfSupported: true,
                            showZoomSliderIfSupported: true
                        };
                        
                        await startScanning(this.value, config);
                    } catch (error) {
                        console.error('Camera switch error:', error);
                        showError('Failed to switch camera: ' + error.message);
                    }
                }
            });
            
            headerControls.appendChild(cameraSelector);
        }

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = Date.now();
            if (currentTime - lastScanTime < scanCooldown) {
                return;
            }
            
            lastScanTime = currentTime;
            console.log('QR Code scanned:', decodedText);

            // Pause scanning temporarily
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause(true);
            }

            // Add scan success animation
            const reader = document.getElementById('reader');
            reader.classList.add('scan-success');
            setTimeout(() => {
                reader.classList.remove('scan-success');
            }, 400);

            // Haptic feedback for successful scan
            if (window.navigator.vibrate) {
                window.navigator.vibrate([100, 50, 100]);
            }

            const scannerSelect = document.getElementById('scannerSelect');
            const scannerId = scannerSelect.value;
            
            // Send scan data to backend
            fetch('scan_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=scan&qr_data=${encodeURIComponent(decodedText)}&scanner_id=${scannerId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showScanResult(data, 'success');
                    addToRecentScansFromBackend(data.person);
                    loadRecentScans();
                } else {
                    showScanResult(data, 'error');
                    if (window.navigator.vibrate) {
                        window.navigator.vibrate(300);
                    }
                }
                
                // Resume scanning after shorter delay
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 500); // Reduced from 3000ms to 500ms
            })
            .catch(error => {
                console.error('Scan processing error:', error);
                showScanResult({ message: 'Network error occurred' }, 'error');
                
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 500); // Reduced from 3000ms to 500ms
            });
        }

        function onScanFailure(error) {
            // Only log non-routine scanning errors
            if (!error.includes('NotFoundException') && 
                !error.includes('No MultiFormat Readers') &&
                !error.includes('No QR code found') &&
                !error.includes('QR code parse error')) {
                console.warn('Scan failure:', error);
            }
        }

        function showScanResult(data, type) {
            const resultDiv = document.getElementById('scanResult');
            
            if (type === 'success' && data.person) {
                resultDiv.innerHTML = `
                    <div class="scan-result-container">
                        <div class="scan-success-details">
                            <div class="scan-success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="scan-person-name">${data.person.name}</div>
                            <div class="scan-person-details">${data.person.type}</div>
                            <div class="scan-person-id">ID: ${data.person.id}</div>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="scan-error-container">
                        <div class="scan-error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <strong>${data.message || 'Scan failed'}</strong>
                        </div>
                    </div>
                `;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.classList.add('scan-success');
            
            // Auto-hide after 3 seconds (reduced from 4 seconds)
            setTimeout(() => {
                resultDiv.style.display = 'none';
                resultDiv.classList.remove('scan-success');
            }, 3000);
        }

        function addToRecentScansFromBackend(person) {
            const location = document.getElementById('scannerSelect').selectedOptions[0].text;
            const now = new Date();
            const newScan = {
                timestamp: now.getTime(),
                time: formatTime(now),
                name: person.name,
                role: person.type,
                id: person.id,
                location: location,
                action: person.action
            };

            recentScansData.unshift(newScan);
            if (recentScansData.length > 15) {
                recentScansData.pop();
            }

            saveRecentScans();
            updateRecentScansDisplay();
        }

        function formatTime(date) {
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) {
                return 'Just now';
            } else if (diffInMinutes < 60) {
                return `${diffInMinutes}m ago`;
            } else {
                const diffInHours = Math.floor(diffInMinutes / 60);
                if (diffInHours < 24) {
                    return `${diffInHours}h ago`;
                } else {
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
            }
        }

        function loadRecentScans() {
            fetch('get_recent_scans.php')
            .then(response => response.json())
            .then(data => {
                if (data.scans) {
                    const backendScans = data.scans.map(scan => ({
                        timestamp: new Date(scan.timestamp || Date.now()).getTime(),
                        time: scan.time || formatTime(new Date()),
                        name: scan.name,
                        role: scan.type,
                        id: scan.id,
                        location: scan.location || 'Scanner Location',
                        action: scan.action
                    }));
                    
                    const today = new Date().toDateString();
                    const savedDate = localStorage?.getItem(DATE_KEY);
                    
                    if (savedDate !== today) {
                        recentScansData = backendScans;
                        if (localStorage) {
                            localStorage.setItem(DATE_KEY, today);
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(recentScansData));
                        }
                    } else {
                        const savedData = localStorage?.getItem(STORAGE_KEY);
                        if (savedData) {
                            try {
                                const localScans = JSON.parse(savedData);
                                const combined = [...localScans, ...backendScans];
                                recentScansData = combined.slice(0, 15);
                            } catch (e) {
                                recentScansData = backendScans;
                            }
                        } else {
                            recentScansData = backendScans;
                        }
                    }
                    
                    updateRecentScansDisplay();
                }
            })
            .catch(error => {
                console.error('Failed to load recent scans:', error);
                
                const today = new Date().toDateString();
                const savedDate = localStorage?.getItem(DATE_KEY);
                
                if (savedDate !== today) {
                    recentScansData = [];
                    if (localStorage) {
                        localStorage.setItem(DATE_KEY, today);
                        localStorage.removeItem(STORAGE_KEY);
                    }
                } else {
                    if (localStorage) {
                        const savedData = localStorage.getItem(STORAGE_KEY);
                        if (savedData) {
                            try {
                                recentScansData = JSON.parse(savedData);
                            } catch (e) {
                                recentScansData = [];
                            }
                        }
                    }
                }
                
                updateRecentScansDisplay();
            });
        }

        function saveRecentScans() {
            if (localStorage) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(recentScansData));
                } catch (e) {
                    console.warn('Unable to save recent scans to localStorage');
                }
            }
        }

        function updateRecentScansDisplay() {
            const recentScansContainer = document.getElementById('recentScans');
            
            if (recentScansData.length === 0) {
                recentScansContainer.innerHTML = '<div class="text-center text-muted py-3">No recent scans today</div>';
                return;
            }

            recentScansContainer.innerHTML = '';

            recentScansData.forEach(scan => {
                const scanItem = document.createElement('div');
                scanItem.className = 'scan-item';
                scanItem.innerHTML = `
                    <div class="scan-time">${scan.time}</div>
                    <div class="scan-name">${scan.name}</div>
                    <div class="scan-role">${scan.role}</div>
                    <div class="scan-data">ID: ${scan.id}</div>
                    <div class="scan-location">${scan.action ? scan.action + ' - ' : ''}${scan.location}</div>
                `;
                recentScansContainer.appendChild(scanItem);
            });
        }

        function setupRecentScansToggle() {
            const toggleBtn = document.getElementById('toggleRecentScans');
            const floatWindow = document.getElementById('recentScansFloat');
            const closeBtn = document.getElementById('closeRecentScans');

            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                floatWindow.classList.add('open');
            });

            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                floatWindow.classList.remove('open');
            });

            // Close when clicking outside
            document.addEventListener('click', function(event) {
                if (!floatWindow.contains(event.target) && 
                    !toggleBtn.contains(event.target) && 
                    floatWindow.classList.contains('open')) {
                    floatWindow.classList.remove('open');
                }
            });

            updateRecentScansDisplay();
        }

        // Update time display periodically
        setInterval(() => {
            if (recentScansData.length > 0) {
                updateRecentScansDisplay();
            }
        }, 60000);

        // Handle scanner selection change
        document.getElementById('scannerSelect').addEventListener('change', function() {
            console.log('Scanner location changed to:', this.value);
        });

        // Handle page visibility changes (pause/resume scanner)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden, pause scanner to save battery
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.pause(true);
                }
            } else {
                // Page is visible again, resume scanner
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 100);
            }
        });

        // Handle orientation changes
        window.addEventListener('orientationchange', function() {
            setTimeout(() => {
                // Restart scanner after orientation change for better performance
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.pause(true);
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 300);
                }
            }, 100);
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#972529">
    <title>CTU Scanner - QR Code Scanner</title>
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
    box-shadow: 0 8px 25px rgba(229, 197, 115, 0.4);
    animation: avatarPulse 0.6s ease-out;
}

.scan-result-avatar-default {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #972529;
    color: #FEFEFE;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    border: 4px solid var(--primary-gold);
    box-shadow: 0 8px 25px rgba(229, 197, 115, 0.4);
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
    background: #E5C573;
    color: #333;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    margin-top: 8px;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(229, 197, 115, 0.3);
}

.scan-action-badge.entry {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.scan-action-badge.exit {
    background: #972529;
    color: #FEFEFE;
    box-shadow: 0 4px 15px rgba(151, 37, 41, 0.3);
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
    background: #972529;
    box-shadow: 0 2px 8px rgba(151, 37, 41, 0.3);
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

        .scan-details-mini {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 6px;
            font-weight: 700;
            text-transform: none;
            word-break: break-word;
        }

.scan-location {
    font-size: 0.8rem;
    color: #28a745;
    font-weight: 600;
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
                radial-gradient(circle at 20% 20%, rgba(151, 37, 41, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(151, 37, 41, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 40% 90%, rgba(156, 38, 43, 0.025) 0%, transparent 50%);
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
                0 10px 30px rgba(229, 197, 115, 0.4),
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
                0 0 0 1px rgba(151, 37, 41, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
            border: 2px solid rgba(151, 37, 41, 0.2);
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
                inset 0 0 0 2px rgba(151, 37, 41, 0.15);
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
            box-shadow: -3px -3px 15px rgba(229, 197, 115, 0.4);
        }

        .scanner-frame::after {
            bottom: -5px;
            right: -5px;
            border-left: none;
            border-top: none;
            box-shadow: 3px 3px 15px rgba(229, 197, 115, 0.4);
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
            box-shadow: 3px -3px 15px rgba(156, 38, 43, 0.4);
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
            box-shadow: -3px 3px 15px rgba(138, 33, 37, 0.4);
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

        #scanResult {
            height: 0;
            overflow: hidden;
            pointer-events: none;
        }

        #scanResult:not(:empty) {
            height: auto;
            pointer-events: auto;
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
            box-shadow: 0 10px 30px rgba(138, 33, 37, 0.4);
        }

        .alert-warning {
            background: linear-gradient(135deg, var(--primary-orange), var(--primary-gold));
            color: white;
            box-shadow: 0 10px 30px rgba(223, 187, 101, 0.4);
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

        /* Resizable handle (bottom-left) */
        .recent-scans-float .resize-handle {
            position: absolute;
            bottom: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(0,0,0,0.06), rgba(0,0,0,0.04));
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            cursor: nwse-resize;
            z-index: 1010;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.12s ease;
            touch-action: none;
        }

        .recent-scans-float .resize-handle:hover {
            transform: scale(1.05);
        }

        .recent-scans-float .resize-icon {
            font-size: 0.8rem;
            color: #666;
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

        /* Scan Result Enhancement - Fixed Position */
        .scan-result-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--primary-gold);
            backdrop-filter: blur(20px);
            z-index: 998;
            animation: fadeIn 0.2s ease-out;
            will-change: opacity;
        }

        .scan-error-container {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            max-width: 400px;
            background: linear-gradient(135deg, rgba(138, 33, 37, 0.95), rgba(106, 20, 24, 0.95));
            color: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 40px rgba(138, 33, 37, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            z-index: 998;
            text-align: center;
            animation: fadeIn 0.2s ease-out;
            will-change: opacity;
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .scan-success-details {
            text-align: center;
            position: relative;
            z-index: 2;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .scan-success-icon {
            font-size: 2.5rem;
            color: var(--primary-gold);
            margin-bottom: 10px;
            animation: successPulse 1s ease-in-out;
            filter: drop-shadow(0 0 10px rgba(219, 179, 86, 0.5));
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
            background: rgba(156, 38, 43, 0.1);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .scan-person-additional {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 10px;
            width: 100%;
            background: rgba(0, 0, 0, 0.05);
            padding: 10px;
            border-radius: 8px;
        }

        /* Nicely formatted label/value rows for additional information */
        .scan-person-additional {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 12px;
            align-items: center;
        }

        .scan-info-label {
            color: #444;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: capitalize;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .scan-info-value {
            color: #333;
            font-weight: 800;
            font-size: 0.9rem;
            text-align: right;
        }

        /* Grid layout for main scan result to show avatar + info + action */
        .scan-result-grid {
            display: flex;
            gap: 18px;
            align-items: center;
            width: 100%;
        }

        .scan-info {
            flex: 1 1 auto;
            text-align: left;
            min-width: 0;
        }

        .scan-actions-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .scan-action-badge {
            padding: 6px 14px;
            font-size: 0.85rem;
            letter-spacing: 0.6px;
            border-radius: 14px;
            transform: translateZ(0);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .scan-action-badge:hover { transform: translateY(-2px) scale(1.02); }

        /* Subtle pop for the action badge */
        .scan-action-badge.entry, .scan-action-badge.exit {
            animation: badgePop 0.45s ease-out;
        }

        @keyframes badgePop {
            0% { transform: scale(0.85); opacity: 0; }
            60% { transform: scale(1.06); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        .scan-info-row {
            margin: 5px 0;
            line-height: 1.4;
        }

        .scan-error-icon {
            font-size: 2rem;
            animation: errorShake 0.5s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Mobile adjustments for fixed position */
        @media (max-width: 768px) {
            .scan-result-container,
            .scan-error-container {
                bottom: 15px;
                left: 15px;
                right: 15px;
                max-width: none;
                padding: 20px;
            }

            .scan-result-avatar,
            .scan-result-avatar-default {
                width: 70px;
                height: 70px;
                border-width: 3px;
            }

            .scan-result-avatar-default {
                font-size: 24px;
            }

            .scan-person-name {
                font-size: 1.2rem;
            }

            .scan-action-badge {
                font-size: 0.8rem;
                padding: 5px 14px;
            }
        }

            .scan-person-additional {
                grid-template-columns: 1fr;
                gap: 8px 0;
            }

            .scan-info-value { text-align: left; }

        @media (max-width: 480px) {
            .scan-result-container,
            .scan-error-container {
                bottom: 10px;
                left: 10px;
                right: 10px;
                max-width: none;
                padding: 15px;
            }

            .scan-result-avatar,
            .scan-result-avatar-default {
                width: 60px;
                height: 60px;
                border-width: 2px;
            }

            .scan-result-avatar-default {
                font-size: 20px;
            }

            .scan-avatar-container {
                margin-bottom: 10px;
            }

            .scan-person-name {
                font-size: 1.1rem;
            }

            .scan-action-badge {
                font-size: 0.75rem;
                padding: 4px 12px;
                margin-top: 6px;
            }

            .scan-success-icon {
                font-size: 2rem;
                margin-bottom: 8px;
            }

            .scan-error-icon {
                font-size: 1.5rem;
                margin-right: 10px;
            }
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
        <div class="resize-handle" id="recentResizeHandle" title="Drag to resize">
            <i class="fas fa-grip-lines resize-icon" aria-hidden="true"></i>
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
            .then(response => {
                // Read as text first because some hosts (e.g., free hosts) may inject HTML into responses
                return response.text().then(text => {
                    let parsed = null;
                    try {
                        parsed = JSON.parse(text);
                    } catch (e) {
                        // not JSON
                    }
                    return { ok: response.ok, status: response.status, parsed, text };
                });
            })
            .then(result => {
                // Log raw response (trimmed) for debugging on hosts that inject HTML
                try {
                    console.log('scan response status:', result.status);
                    console.log('scan response text (first 2000 chars):', result.text ? result.text.substring(0, 2000) : '');
                } catch (e) {
                    // ignore logging errors
                }

                // If server returned JSON, respect its success flag
                if (result.parsed) {
                    console.log('Parsed JSON from scan_process.php:', result.parsed);
                    const data = result.parsed;
                    console.log('Parsed data.success:', data.success);
                    if (data.success) {
                        console.log('Calling showScanResult with parsed person:', data.person);
                        showScanResult(data, 'success');
                        addToRecentScansFromBackend(data.person);
                        loadRecentScans();
                    } else {
                        console.log('Parsed response indicates failure:', data);
                        showScanResult(data, 'error');
                        if (window.navigator.vibrate) {
                            window.navigator.vibrate(300);
                        }
                    }

                } else {
                    // Try to extract JSON that may be embedded within HTML wrappers
                    console.log('No parsed JSON; attempting to extract JSON from response text');
                    let extractedJson = null;
                    if (result.text) {
                        const match = result.text.match(/\{[\s\S]*\}/);
                        if (match && match[0]) {
                            try {
                                extractedJson = JSON.parse(match[0]);
                                console.log('Extracted JSON from wrapped response:', extractedJson);
                            } catch (e) {
                                console.warn('Failed to parse extracted JSON block', e);
                                extractedJson = null;
                            }
                        }
                    }

                    if (extractedJson) {
                        const data = extractedJson;
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

                    } else if (result.ok) {
                        // If not JSON but response is 2xx, attempt to fetch recent scans (no-store) and match by scanned QR
                        console.log('Response OK but no JSON; fetching recent scans as fallback');
                        fetch('get_recent_scans.php', { cache: 'no-store' })
                        .then(r => r.json())
                        .then(d => {
                            if (d.scans && d.scans.length) {
                                // Try to find a scan that matches the scanned ID (decodedText). Use first match, else use latest.
                                const matched = d.scans.find(s => String(s.id) === String(decodedText) || s.id === decodedText);
                                const latest = matched || d.scans[0];
                                if (latest) {
                                    const personObj = {
                                        name: latest.name,
                                        id: latest.id,
                                        type: latest.type,
                                        action: latest.action,
                                        image: latest.image || null,
                                        firstName: latest.firstName || '',
                                        middleName: latest.middleName || '',
                                        lastName: latest.lastName || '',
                                        department: latest.department || null,
                                        course: latest.course || null,
                                        year: latest.year || null,
                                        section: latest.section || null,
                                        isEnroll: latest.isEnroll !== undefined ? latest.isEnroll : 1
                                    };

                                    console.log('Using fallback scan entry for popup:', latest);
                                    showScanResult({ success: true, person: personObj }, 'success');
                                    addToRecentScansFromBackend(personObj);
                                    return;
                                }
                            }

                            // Fallback generic message if no scans returned
                            console.warn('No recent scans returned for fallback; showing generic message');
                            showScanResult({ message: 'Scan recorded' }, 'success');
                        })
                        .catch(err => {
                            console.warn('Failed to fetch recent scans for popup fallback', err);
                            showScanResult({ message: 'Scan recorded' }, 'success');
                        });

                    } else {
                        // Non-2xx and non-JSON -> show server text or generic error
                        const msg = result.text ? result.text.replace(/<[^>]*>/g, '').trim() : 'Server error';
                        showScanResult({ message: msg || 'Server error occurred' }, 'error');
                        if (window.navigator.vibrate) {
                            window.navigator.vibrate(300);
                        }
                    }
                }

                // Resume scanning after shorter delay
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 500);
            })
            .catch(error => {
                console.error('Scan processing error:', error);
                showScanResult({ message: 'Network error occurred. Check your connection.' }, 'error');

                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 500);
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
                // Generate avatar HTML
                let avatarHtml = '';
                if (data.person.image) {
                    avatarHtml = `<img src="${data.person.image}" alt="Profile" class="scan-result-avatar" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;scan-result-avatar-default&quot;>${getInitials(data.person.firstName, data.person.lastName)}</div>'">`;
                } else {
                    const initials = getInitials(data.person.firstName, data.person.lastName);
                    avatarHtml = `<div class="scan-result-avatar-default">${initials}</div>`;
                }
                
                // Build additional info based on user type (nicely labeled)
                let additionalInfoHtml = '';
                if (data.person.type === 'Student') {
                    // Check if student is enrolled
                    const enrollmentStatusHtml = data.person.isEnroll == 0 ? 
                        '<div style="margin-top: 10px;"><span class="badge bg-warning"><i class="fas fa-exclamation-circle me-1"></i>Not Enrolled</span></div>' : '';
                    
                    additionalInfoHtml = `
                        <div class="scan-person-additional">
                            <div class="scan-info-label"><i class="fas fa-building"></i> department</div>
                            <div class="scan-info-value">${escapeHtml(data.person.department) || 'N/A'}</div>

                            <div class="scan-info-label"><i class="fas fa-book"></i> course</div>
                            <div class="scan-info-value">${escapeHtml(data.person.course) || 'N/A'}</div>

                            <div class="scan-info-label"><i class="fas fa-graduation-cap"></i> year</div>
                            <div class="scan-info-value">${escapeHtml(data.person.year) || 'N/A'}</div>

                            <div class="scan-info-label"><i class="fas fa-users"></i> section</div>
                            <div class="scan-info-value">${escapeHtml(data.person.section) || 'N/A'}</div>
                            ${enrollmentStatusHtml}
                        </div>
                    `;
                } else if (data.person.type === 'Faculty') {
                    additionalInfoHtml = `
                        <div class="scan-person-additional">
                            <div class="scan-info-label"><i class="fas fa-building"></i> department</div>
                            <div class="scan-info-value">${escapeHtml(data.person.department) || 'N/A'}</div>
                        </div>
                    `;
                }
                
                resultDiv.innerHTML = `
                    <div class="scan-result-container">
                        <div class="scan-success-details">
                            <div class="scan-result-grid">
                                <div class="scan-avatar-container">
                                    ${avatarHtml}
                                </div>

                                <div class="scan-info">
                                                    <div class="scan-person-name">${escapeHtml(data.person.name)}</div>
                                                    <div class="scan-person-details">${escapeHtml(data.person.type)}</div>
                                                    <div class="scan-person-id">ID: ${escapeHtml(data.person.id)}</div>
                                    ${additionalInfoHtml}
                                </div>

                                <div class="scan-actions-right">
                                    <div class="scan-success-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="scan-action-badge ${escapeHtml(data.person.action.toLowerCase())}">
                                        <i class="fas fa-${data.person.action === 'Entry' ? 'arrow-right' : 'arrow-left'} me-1"></i>${data.person.action}
                                    </div>
                                </div>
                            </div>
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

        function getInitials(firstName, lastName) {
            const f = (firstName || '').charAt(0).toUpperCase();
            const l = (lastName || '').charAt(0).toUpperCase();
            return (f + l) || '?';
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
                action: person.action,
                image: person.image || null,
                firstName: person.firstName || '',
                lastName: person.lastName || ''
                , department: person.department || null
                , course: person.course || null
                , year: person.year || null
                , section: person.section || null
                , isEnroll: person.isEnroll || 1
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
                        action: scan.action,
                        image: scan.image || null,
                        firstName: scan.firstName || '',
                        lastName: scan.lastName || ''
                        , department: scan.department || null
                        , course: scan.course || null
                        , year: scan.year || null
                        , section: scan.section || null
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
                // Generate avatar HTML
                let avatarHtml = '';
                if (scan.image) {
                    avatarHtml = `<img src="${scan.image}" alt="Avatar" class="recent-scan-avatar" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;recent-scan-avatar-default&quot;>${getInitials(scan.firstName, scan.lastName)}</div>'">`;
                } else {
                    const initials = getInitials(scan.firstName, scan.lastName);
                    avatarHtml = `<div class="recent-scan-avatar-default">${initials}</div>`;
                }
                
                // Check if student is not enrolled
                const enrollmentWarning = scan.role === 'Student' && scan.isEnroll == 0 ? 
                    '<div class="mt-2"><span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Not Enrolled</span></div>' : '';
                
                const scanItem = document.createElement('div');
                scanItem.className = 'scan-item';
                scanItem.innerHTML = `
                    <div class="scan-item-header">
                        <div class="scan-avatar-small">
                            ${avatarHtml}
                        </div>
                        <div class="scan-item-info">
                            <div class="scan-time">${escapeHtml(scan.time)}</div>
                            <div class="scan-name">${escapeHtml(scan.name)}</div>
                        </div>
                        <div class="scan-action-mini ${scan.action.toLowerCase()}">
                            ${scan.action}
                        </div>
                    </div>
                    <div class="scan-item-details">
                        <div class="scan-role">${escapeHtml(scan.role)}</div>
                        <div class="scan-data">ID: ${escapeHtml(scan.id)}</div>
                            <div class="scan-details-mini">${scan.department ? escapeHtml(scan.department) : ''}${scan.course ? (scan.department ? ' • ' : '') + escapeHtml(scan.course) : ''}${scan.year ? (scan.course||scan.department ? ' • ' : '') + 'Year ' + escapeHtml(scan.year) : ''}${scan.section ? (scan.year||scan.course||scan.department ? ' • ' : '') + escapeHtml(scan.section) : ''}</div>
                            <div class="scan-location">${escapeHtml(scan.location)}</div>
                            ${enrollmentWarning}
                    </div>
                `;
                recentScansContainer.appendChild(scanItem);
            });
        }


            // Small utility to escape HTML when injecting user text
            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                // Coerce non-string values (numbers, booleans) to string before escaping
                str = String(str);
                return str.replace(/[&<>\"]+/g, function(match) {
                    switch (match) {
                        case '&': return '&amp;';
                        case '<': return '&lt;';
                        case '>': return '&gt;';
                        case '"': return '&quot;';
                        default: return match;
                    }
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
            setupRecentScansResizer();
        }

        function setupRecentScansResizer() {
            const floatWindow = document.getElementById('recentScansFloat');
            const handle = document.getElementById('recentResizeHandle');
            const body = floatWindow.querySelector('.float-body');

            if (!floatWindow || !handle || !body) return;

            // Load saved size
            const saved = JSON.parse(localStorage.getItem('ctu_recent_scans_size') || '{}');
            if (saved.width) floatWindow.style.width = saved.width + 'px';
            if (saved.height) {
                floatWindow.style.maxHeight = saved.height + 'px';
                // adjust body
                const headerH = floatWindow.querySelector('.float-header').offsetHeight;
                body.style.maxHeight = (saved.height - headerH - 40) + 'px';
            }

            let isResizing = false;
            let startX = 0, startY = 0, startW = 0, startH = 0;

            function onPointerMove(e) {
                if (!isResizing) return;
                // unify touch / pointer
                const clientX = e.clientX || (e.touches && e.touches[0].clientX);
                const clientY = e.clientY || (e.touches && e.touches[0].clientY);

                const deltaX = startX - clientX; // dragging left -> increase width
                const deltaY = clientY - startY; // dragging down -> increase height

                const minW = 260;
                const maxW = Math.min(window.innerWidth - 40, 900);
                const minH = 160;
                const maxH = Math.min(window.innerHeight - 80, 1200);

                let newW = Math.max(minW, Math.min(maxW, startW + deltaX));
                let newH = Math.max(minH, Math.min(maxH, startH + deltaY));

                floatWindow.style.width = newW + 'px';
                floatWindow.style.maxHeight = newH + 'px';

                const headerH = floatWindow.querySelector('.float-header').offsetHeight;
                body.style.maxHeight = (newH - headerH - 40) + 'px';

                // Save to localStorage
                localStorage.setItem('ctu_recent_scans_size', JSON.stringify({ width: newW, height: newH }));
            }

            function onPointerUp(e) {
                if (!isResizing) return;
                isResizing = false;
                // Remove global listeners
                window.removeEventListener('pointermove', onPointerMove);
                window.removeEventListener('pointerup', onPointerUp);
                window.removeEventListener('touchmove', onPointerMove);
                window.removeEventListener('touchend', onPointerUp);
                try { handle.releasePointerCapture(e.pointerId); } catch (err) {}
            }

            handle.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                isResizing = true;
                startX = e.clientX;
                startY = e.clientY;
                startW = floatWindow.offsetWidth;
                startH = floatWindow.offsetHeight;
                handle.setPointerCapture(e.pointerId);

                window.addEventListener('pointermove', onPointerMove);
                window.addEventListener('pointerup', onPointerUp);
            });

            // fallback for touch
            handle.addEventListener('touchstart', function (e) {
                isResizing = true;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startW = floatWindow.offsetWidth;
                startH = floatWindow.offsetHeight;

                window.addEventListener('touchmove', onPointerMove, { passive: false });
                window.addEventListener('touchend', onPointerUp);
            });
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
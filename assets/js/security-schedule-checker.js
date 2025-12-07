/**
 * Security Shift Schedule Checker
 * Periodically checks if security staff's shift is ending soon
 * and displays notifications
 */

class SecurityScheduleChecker {
    constructor(options = {}) {
        this.apiEndpoint = options.apiEndpoint || '/api/check_schedule_end.php';
        this.checkInterval = options.checkInterval || 60000; // Check every 60 seconds
        this.minutesBeforeNotify = options.minutesBeforeNotify || 15; // Notify 15 minutes before
        this.isRunning = false;
        this.intervalId = null;
        this.lastCheckTime = null;
        this.shiftEndingSoonNotified = false;
        this.shiftCompleteNotified = false;
    }

    /**
     * Start checking schedule
     */
    start() {
        if (this.isRunning) {
            console.log('Schedule checker already running');
            return;
        }

        this.isRunning = true;
        console.log('Starting security shift schedule checker');
        
        // Run immediate check
        this.checkSchedule();
        
        // Set up interval
        this.intervalId = setInterval(() => this.checkSchedule(), this.checkInterval);
    }

    /**
     * Stop checking schedule
     */
    stop() {
        if (!this.isRunning) {
            return;
        }

        this.isRunning = false;
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        console.log('Stopped security shift schedule checker');
    }

    /**
     * Check security schedule
     */
    async checkSchedule() {
        try {
            const params = new URLSearchParams({
                action: 'check',
                minutes_before: this.minutesBeforeNotify
            });

            const response = await fetch(`${this.apiEndpoint}?${params.toString()}`);
            const data = await response.json();

            if (!data.success) {
                console.warn('Schedule check failed:', data.error);
                return;
            }

            this.lastCheckTime = new Date();

            // Log schedule status
            console.log('Schedule check result:', {
                isActive: data.is_shift_active,
                minutesUntilEnd: data.minutes_until_end,
                shiftEnding: data.shift_ending_soon,
                shiftEnded: data.shift_ended
            });

            // Handle shift ending soon
            if (data.shift_ending_soon && !this.shiftEndingSoonNotified) {
                this.shiftEndingSoonNotified = true;
                this.handleShiftEndingSoon(data);
            } else if (!data.shift_ending_soon) {
                this.shiftEndingSoonNotified = false;
            }

            // Handle shift complete
            if (data.shift_ended && !this.shiftCompleteNotified) {
                this.shiftCompleteNotified = true;
                this.handleShiftComplete(data);
            }

        } catch (error) {
            console.error('Error checking schedule:', error);
        }
    }

    /**
     * Handle shift ending soon notification
     */
    handleShiftEndingSoon(data) {
        console.log('Shift ending soon:', data.message);
        
        // Show browser notification if permission granted
        this.showBrowserNotification(
            'Shift Ending Soon',
            data.message,
            'warning'
        );

        // Play audio alert
        this.playNotificationSound();

        // Display UI alert
        this.showUIAlert(
            'warning',
            'Your shift is ending soon',
            data.message
        );

        // Trigger any custom callbacks
        if (typeof window.onShiftEndingSoon === 'function') {
            window.onShiftEndingSoon(data);
        }
    }

    /**
     * Handle shift complete notification
     */
    handleShiftComplete(data) {
        console.log('Shift complete:', data.message);
        
        // Show browser notification
        this.showBrowserNotification(
            'Shift Complete',
            data.message,
            'success'
        );

        // Play audio alert
        this.playNotificationSound();

        // Display UI alert
        this.showUIAlert(
            'success',
            'Your shift has ended',
            data.message
        );

        // Trigger any custom callbacks
        if (typeof window.onShiftComplete === 'function') {
            window.onShiftComplete(data);
        }

        // Optionally redirect to logout after a delay
        // window.location.href = 'logout.php';
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(title, message, type = 'info') {
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            console.log('This browser does not support notifications');
            return;
        }

        // Check if permission is granted
        if (Notification.permission === 'granted') {
            const iconMap = {
                'warning': '⏰',
                'success': '✅',
                'error': '❌',
                'info': 'ℹ️'
            };

            new Notification(title, {
                body: message,
                icon: `/assets/images/ctu-logo.png`,
                tag: 'security-schedule-' + type,
                requireInteraction: type === 'warning'
            });
        }
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        try {
            // Try to use the existing notification sound if available
            if (typeof notificationSound !== 'undefined' && notificationSound.play) {
                notificationSound.play();
            } else {
                // Create a simple beep sound using Web Audio API
                this.playBeep();
            }
        } catch (error) {
            console.warn('Could not play notification sound:', error);
        }
    }

    /**
     * Play beep sound using Web Audio API
     */
    playBeep(frequency = 800, duration = 300) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration / 1000);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + duration / 1000);
        } catch (error) {
            console.warn('Could not create beep sound:', error);
        }
    }

    /**
     * Show UI alert in the page
     */
    showUIAlert(type, title, message) {
        // Create alert element if it doesn't exist
        let alertContainer = document.getElementById('schedule-alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'schedule-alert-container';
            alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
            document.body.appendChild(alertContainer);
        }

        // Create alert element
        const alertId = 'schedule-alert-' + Date.now();
        const alertClass = `alert alert-${type === 'warning' ? 'warning' : 'success'} alert-dismissible fade show`;
        
        const alertHTML = `
            <div id="${alertId}" class="${alertClass}" role="alert">
                <strong>${title}</strong><br>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const alertElement = document.createElement('div');
        alertElement.innerHTML = alertHTML;
        alertContainer.appendChild(alertElement.firstElementChild);

        // Auto remove after 10 seconds
        setTimeout(() => {
            const element = document.getElementById(alertId);
            if (element) {
                element.remove();
            }
        }, 10000);
    }

    /**
     * Request browser notification permission
     */
    static requestNotificationPermission() {
        if (!('Notification' in window)) {
            console.log('This browser does not support notifications');
            return;
        }

        if (Notification.permission !== 'granted') {
            Notification.requestPermission();
        }
    }

    /**
     * Get schedule info as formatted string
     */
    getScheduleInfo() {
        if (!this.lastCheckTime) {
            return 'No check performed yet';
        }

        return {
            lastChecked: this.lastCheckTime,
            isRunning: this.isRunning
        };
    }
}

// Initialize globally
const securityScheduleChecker = new SecurityScheduleChecker({
    checkInterval: 60000, // Check every 60 seconds
    minutesBeforeNotify: 15 // Notify 15 minutes before shift end
});

// Start on document ready
document.addEventListener('DOMContentLoaded', () => {
    // Request notification permission
    SecurityScheduleChecker.requestNotificationPermission();
    
    // Start the schedule checker
    securityScheduleChecker.start();
});

// Stop when leaving page
window.addEventListener('beforeunload', () => {
    securityScheduleChecker.stop();
});

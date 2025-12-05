/**
 * Notification Sound System
 * Provides notification alert sounds for the CTU Scanner system
 */

class NotificationSound {
    constructor() {
        this.audioContext = null;
        this.isSoundEnabled = this.getSoundPreference();
        this.setupAudioContext();
    }

    /**
     * Setup Web Audio API context
     */
    setupAudioContext() {
        if (!window.AudioContext && !window.webkitAudioContext) {
            console.log('Web Audio API not supported');
            return;
        }

        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.audioContext = new AudioContext();
        } catch (e) {
            console.log('Could not create audio context:', e);
        }
    }

    /**
     * Get sound preference from localStorage
     */
    getSoundPreference() {
        const stored = localStorage.getItem('notificationSoundEnabled');
        return stored === null ? true : stored === 'true';
    }

    /**
     * Save sound preference to localStorage
     */
    setSoundPreference(enabled) {
        localStorage.setItem('notificationSoundEnabled', enabled ? 'true' : 'false');
        this.isSoundEnabled = enabled;
    }

    /**
     * Play a notification sound using Web Audio API
     * Creates a simple beep tone
     */
    playNotificationTone() {
        if (!this.isSoundEnabled || !this.audioContext) {
            return;
        }

        try {
            const ctx = this.audioContext;
            const currentTime = ctx.currentTime;

            // Create oscillator
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            // Set frequency and duration
            osc.frequency.setValueAtTime(800, currentTime);
            osc.frequency.setValueAtTime(600, currentTime + 0.1);

            gain.gain.setValueAtTime(0.3, currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, currentTime + 0.2);

            osc.start(currentTime);
            osc.stop(currentTime + 0.2);
        } catch (e) {
            console.log('Error playing notification tone:', e);
        }
    }

    /**
     * Play warning sound (double beep)
     */
    playWarningTone() {
        if (!this.isSoundEnabled || !this.audioContext) {
            return;
        }

        try {
            const ctx = this.audioContext;
            const currentTime = ctx.currentTime;

            for (let i = 0; i < 2; i++) {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                const startTime = currentTime + (i * 0.25);
                osc.frequency.setValueAtTime(600, startTime);
                gain.gain.setValueAtTime(0.3, startTime);
                gain.gain.exponentialRampToValueAtTime(0.01, startTime + 0.15);

                osc.start(startTime);
                osc.stop(startTime + 0.15);
            }
        } catch (e) {
            console.log('Error playing warning tone:', e);
        }
    }

    /**
     * Play error sound (descending tone)
     */
    playErrorTone() {
        if (!this.isSoundEnabled || !this.audioContext) {
            return;
        }

        try {
            const ctx = this.audioContext;
            const currentTime = ctx.currentTime;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            // Descending frequency sweep
            osc.frequency.setValueAtTime(900, currentTime);
            osc.frequency.exponentialRampToValueAtTime(400, currentTime + 0.3);

            gain.gain.setValueAtTime(0.4, currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, currentTime + 0.3);

            osc.start(currentTime);
            osc.stop(currentTime + 0.3);
        } catch (e) {
            console.log('Error playing error tone:', e);
        }
    }

    /**
     * Play success sound (ascending tone)
     */
    playSuccessTone() {
        if (!this.isSoundEnabled || !this.audioContext) {
            return;
        }

        try {
            const ctx = this.audioContext;
            const currentTime = ctx.currentTime;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            // Ascending frequency sweep
            osc.frequency.setValueAtTime(400, currentTime);
            osc.frequency.exponentialRampToValueAtTime(800, currentTime + 0.2);

            gain.gain.setValueAtTime(0.3, currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, currentTime + 0.2);

            osc.start(currentTime);
            osc.stop(currentTime + 0.2);
        } catch (e) {
            console.log('Error playing success tone:', e);
        }
    }
}

// Create global notification sound instance
const notificationSound = new NotificationSound();

class SecurityDashboard {
    constructor() {
        this.updateInterval = 500; // 0.5 seconds for faster scan reception
        this.intervalId = null;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.currentDate = new Date().toLocaleDateString('en-US', { timeZone: 'Asia/Manila' });
        this.dateCheckInterval = null;
        this.init();
    }

    init() {
        console.log('Initializing Security Dashboard...');
        this.loadInitialData();
        this.startRealTimeUpdates();
        this.bindEvents();
        this.initializeCharts();
        this.loadAnalyticsData();
        this.startDateChangeDetection();
    }

    loadInitialData() {
        console.log('Loading initial data...');
        this.updateStats();
        this.refreshEntries();
        this.refreshExits();
    }

    startRealTimeUpdates() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
        
        this.intervalId = setInterval(() => {
            this.updateStats();
            this.refreshEntries();
            this.refreshExits();
        }, this.updateInterval);
        
        console.log('Real-time updates started');
    }

    updateStats() {
        fetch('realtime_data.php?action=stats', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Stats data received:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Update stats with null checks
            this.updateElement('todayEntries', data.total_entries || 0);
            this.updateElement('todayExits', data.total_exits || 0);
            this.updateElement('studentEntries', data.student_entries || 0);
            this.updateElement('facultyEntries', data.faculty_entries || 0);
            this.updateElement('staffEntries', data.staff_entries || 0);
            
            this.retryCount = 0; // Reset retry count on success
        })
        .catch(error => {
            console.error('Error updating stats:', error);
            this.handleError('stats', error);
        });
    }

    refreshEntries() {
        const container = document.getElementById('recentEntries');
        
        fetch('realtime_data.php?action=entries', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Entries data received:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            if (!container) {
                throw new Error('Recent entries container not found');
            }
            
            container.innerHTML = '';
            
            if (data.entries && data.entries.length > 0) {
                data.entries.forEach(entry => {
                    const item = this.createActivityItem(entry, 'entry');
                    container.appendChild(item);
                });
            } else {
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><div>No recent entries</div></div>';
            }
        })
        .catch(error => {
            console.error('Error refreshing entries:', error);
            this.handleError('entries', error);
        });
    }

    refreshExits() {
        const container = document.getElementById('recentExits');
        
        fetch('realtime_data.php?action=exits', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Exits data received:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            const container = document.getElementById('recentExits');
            if (!container) {
                throw new Error('Recent exits container not found');
            }
            
            container.innerHTML = '';
            
            if (data.exits && data.exits.length > 0) {
                data.exits.forEach(exit => {
                    const item = this.createActivityItem(exit, 'exit');
                    container.appendChild(item);
                });
            } else {
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><div>No recent exits</div></div>';
            }
        })
        .catch(error => {
            console.error('Error refreshing exits:', error);
            this.handleError('exits', error);
        });
    }

    createActivityItem(data, type) {
        const item = document.createElement('div');
        
        // Handle student, faculty, and staff data with fallbacks
        const firstName = data.StudentFName || 'Unknown';
        const lastName = data.StudentLName || '';
        const name = `${firstName} ${lastName}`.trim();
        const avatar = this.getInitials(name);
        
        // Format timestamp in Manila timezone with 12-hour format
        let time = 'Unknown time';
        if (data.Timestamp || data.timestamp || data.Time) {
            const timestamp = data.Timestamp || data.timestamp || data.Time;
            try {
                const dateObj = new Date(timestamp);
                // Format in Manila timezone (Asia/Manila)
                time = dateObj.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true,
                    timeZone: 'Asia/Manila'
                });
            } catch (e) {
                console.warn('Invalid timestamp:', timestamp);
                time = timestamp;
            }
        }
        
        const personId = data.PersonID || data.person_id || data.StudentID || data.FacultyID || data.StaffID || 'N/A';
        const category = data.PersonCategory || data.person_category || 'Unknown';
        
        // Determine styling based on category
        let avatarClass = 'activity-user-avatar-default ' + category;
        let borderColor = category === 'student' ? 'border-success' : 
                         category === 'faculty' ? 'border-primary' : 'border-info';
        
        let profileImageHtml = '';
        
        // Handle image display
        if (data.image && data.image.trim()) {
            const escapedImage = this.escapeHtml(data.image);
            profileImageHtml = `<img src="${escapedImage}" alt="${this.escapeHtml(name)}" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="${avatarClass}" style="display:none;">${avatar}</div>`;
        } else {
            profileImageHtml = `<div class="${avatarClass}">${avatar}</div>`;
        }
        
        item.className = `activity-item ${borderColor}`;
        
        // Get user type display name
        const userTypeDisplay = category.charAt(0).toUpperCase() + category.slice(1);
        const userTypeBadgeColor = category === 'student' ? '#972529' : 
                                   category === 'faculty' ? '#E5C573' : '#72a89e';
        const userTypeTextColor = category === 'faculty' ? '#333' : '#fff';
        
        item.innerHTML = `
            <div class="activity-user-image-container">
                ${profileImageHtml}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.95rem; font-weight: 500; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${this.escapeHtml(name)}
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 3px;">
                            <span style="font-size: 0.75rem; padding: 2px 8px; background-color: ${userTypeBadgeColor}; color: ${userTypeTextColor}; border-radius: 12px; font-weight: 500;">
                                ${userTypeDisplay}
                            </span>
                            <span style="font-size: 0.75rem; color: #999;">
                                ${this.escapeHtml(personId)}
                            </span>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #666; white-space: nowrap; text-align: right;">
                        ${time}
                    </div>
                </div>
            </div>
        `;
        
        return item;
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    updateElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        } else {
            console.warn(`Element with ID '${elementId}' not found`);
        }
    }

    handleError(type, error) {
        this.retryCount++;
        
        if (this.retryCount <= this.maxRetries) {
            console.log(`Retrying ${type} request (${this.retryCount}/${this.maxRetries})...`);
            return;
        }
        
        // Show error message to user
        const container = type === 'stats' ? null : 
                         type === 'entries' ? document.getElementById('recentEntries') :
                         document.getElementById('recentExits');
        
        if (container) {
            container.innerHTML = `
                <div class="text-center text-danger py-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading ${type}: ${error.message}
                    <br>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="dashboard.loadInitialData()">
                        <i class="fas fa-retry me-1"></i>Retry
                    </button>
                </div>
            `;
        }
    }

    getInitials(name) {
        if (!name || typeof name !== 'string') return 'U';
        
        return name.split(' ')
                  .map(n => n.charAt(0))
                  .join('')
                  .toUpperCase()
                  .substring(0, 2);
    }

    bindEvents() {
        // Manual refresh buttons
        window.refreshEntries = () => {
            console.log('Manual refresh entries triggered');
            this.refreshEntries();
        };
        
        window.refreshExits = () => {
            console.log('Manual refresh exits triggered');
            this.refreshExits();
        };
        
        // Pause updates when window is not visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('Page hidden, pausing updates');
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }
            } else {
                console.log('Page visible, resuming updates');
                this.startRealTimeUpdates();
                this.loadInitialData(); // Refresh data when page becomes visible
            }
        });
    }

    startDateChangeDetection() {
        // Check every minute if the date has changed
        this.dateCheckInterval = setInterval(() => {
            const newDate = new Date().toLocaleDateString('en-US', { timeZone: 'Asia/Manila' });
            if (newDate !== this.currentDate) {
                console.log(`Date changed from ${this.currentDate} to ${newDate}. Clearing entries and exits.`);
                this.currentDate = newDate;
                
                // Clear the activity feeds when date changes
                const recentEntries = document.getElementById('recentEntries');
                const recentExits = document.getElementById('recentExits');
                
                if (recentEntries) {
                    recentEntries.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><div>No recent entries</div></div>';
                }
                if (recentExits) {
                    recentExits.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><div>No recent exits</div></div>';
                }
                
                // Reload data for the new day
                this.loadInitialData();
                this.loadAnalyticsData();
            }
        }, 60000); // Check every minute (60000ms)
    }

    destroy() {
        console.log('Destroying dashboard...');
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        if (this.dateCheckInterval) {
            clearInterval(this.dateCheckInterval);
            this.dateCheckInterval = null;
        }
    }

    initializeCharts() {
        this.charts = {};

        // Entry Logs Timeline Chart
        const entryLogsCtx = document.getElementById('securityEntryLogsChart');
        if (entryLogsCtx) {
            this.charts.entryLogs = new Chart(entryLogsCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Entries',
                        data: new Array(24).fill(0),
                        borderColor: 'rgba(46, 204, 113, 1)',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(46, 204, 113, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        // Exit Logs Timeline Chart
        const exitLogsCtx = document.getElementById('securityExitLogsChart');
        if (exitLogsCtx) {
            this.charts.exitLogs = new Chart(exitLogsCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Exits',
                        data: new Array(24).fill(0),
                        borderColor: 'rgba(231, 76, 60, 1)',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(231, 76, 60, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        // Entry/Exit Hourly Comparison Chart
        const entryExitHourlyCtx = document.getElementById('securityEntryExitHourlyChart');
        if (entryExitHourlyCtx) {
            this.charts.entryExitHourly = new Chart(entryExitHourlyCtx, {
                type: 'bar',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [
                        {
                            label: 'Entries',
                            data: new Array(24).fill(0),
                            backgroundColor: 'rgba(46, 204, 113, 0.7)',
                            borderColor: 'rgba(46, 204, 113, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Exits',
                            data: new Array(24).fill(0),
                            backgroundColor: 'rgba(231, 76, 60, 0.7)',
                            borderColor: 'rgba(231, 76, 60, 1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true },
                        x: { stacked: false }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }
    }

    loadAnalyticsData() {
        const today = new Date().toISOString().split('T')[0];
        
        // Load entry logs
        fetch(`realtime_data.php?action=entry_logs_hourly&dateRange=today`, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Entry logs data:', data);
            if (this.charts.entryLogs && Array.isArray(data)) {
                const entryData = new Array(24).fill(0);
                data.forEach(item => {
                    const hour = parseInt(item.hour);
                    if (hour >= 0 && hour <= 23) {
                        entryData[hour] = parseInt(item.count);
                    }
                });
                this.charts.entryLogs.data.datasets[0].data = entryData;
                this.charts.entryLogs.update();
            }
        })
        .catch(error => console.error('Error loading entry logs:', error));

        // Load exit logs
        fetch(`realtime_data.php?action=exit_logs_hourly&dateRange=today`, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Exit logs data:', data);
            if (this.charts.exitLogs && Array.isArray(data)) {
                const exitData = new Array(24).fill(0);
                data.forEach(item => {
                    const hour = parseInt(item.hour);
                    if (hour >= 0 && hour <= 23) {
                        exitData[hour] = parseInt(item.count);
                    }
                });
                this.charts.exitLogs.data.datasets[0].data = exitData;
                this.charts.exitLogs.update();
            }
        })
        .catch(error => console.error('Error loading exit logs:', error));

        // Load entry/exit hourly comparison
        fetch(`realtime_data.php?action=entry_exit_hourly&dateRange=today`, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Entry/Exit hourly data:', data);
            if (this.charts.entryExitHourly && Array.isArray(data)) {
                const entryData = new Array(24).fill(0);
                const exitData = new Array(24).fill(0);
                
                data.forEach(item => {
                    const hour = parseInt(item.hour);
                    if (hour >= 0 && hour <= 23) {
                        if (item.type === 'entry') {
                            entryData[hour] = parseInt(item.count);
                        } else if (item.type === 'exit') {
                            exitData[hour] = parseInt(item.count);
                        }
                    }
                });
                
                this.charts.entryExitHourly.data.datasets[0].data = entryData;
                this.charts.entryExitHourly.data.datasets[1].data = exitData;
                this.charts.entryExitHourly.update();
            }
        })
        .catch(error => console.error('Error loading entry/exit hourly:', error));
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing dashboard...');
    window.dashboard = new SecurityDashboard();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (window.dashboard) {
            window.dashboard.destroy();
        }
    });
});
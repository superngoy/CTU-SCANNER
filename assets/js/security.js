class SecurityDashboard {
    constructor() {
        this.updateInterval = 5000; // 5 seconds
        this.intervalId = null;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.init();
    }

    init() {
        console.log('Initializing Security Dashboard...');
        this.loadInitialData();
        this.startRealTimeUpdates();
        this.bindEvents();
        this.initializeCharts();
        this.loadAnalyticsData();
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
            
            this.retryCount = 0; // Reset retry count on success
        })
        .catch(error => {
            console.error('Error updating stats:', error);
            this.handleError('stats', error);
        });
    }

    refreshEntries() {
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
            
            const container = document.getElementById('recentEntries');
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
                container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-info-circle me-2"></i>No recent entries</div>';
            }
        })
        .catch(error => {
            console.error('Error refreshing entries:', error);
            this.handleError('entries', error);
        });
    }

    refreshExits() {
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
                container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-info-circle me-2"></i>No recent exits</div>';
            }
        })
        .catch(error => {
            console.error('Error refreshing exits:', error);
            this.handleError('exits', error);
        });
    }

    createActivityItem(data, type) {
        const item = document.createElement('div');
        item.className = 'activity-item fade-in mb-2 p-3 border rounded';
        
        // Handle both student and faculty data with fallbacks
        const firstName = data.StudentFName || data.FacultyFName || 'Unknown';
        const lastName = data.StudentLName || data.FacultyLName || 'User';
        const name = `${firstName} ${lastName}`;
        const avatar = this.getInitials(name);
        
        // Format timestamp
        let time = 'Unknown time';
        if (data.Timestamp || data.timestamp || data.Time) {
            const timestamp = data.Timestamp || data.timestamp || data.Time;
            try {
                time = new Date(timestamp).toLocaleTimeString();
            } catch (e) {
                console.warn('Invalid timestamp:', timestamp);
                time = timestamp; // Use as-is if parsing fails
            }
        }
        
        const bgColor = type === 'entry' ? '#27ae60' : '#f39c12';
        const personId = data.PersonID || data.person_id || data.StudentID || data.FacultyID || 'N/A';
        const category = data.PersonCategory || data.person_category || 'Unknown';
        
        item.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="activity-avatar rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="background-color: ${bgColor}; width: 40px; height: 40px; color: white; font-weight: bold;">
                    ${avatar}
                </div>
                <div class="activity-details flex-grow-1">
                    <h6 class="mb-0">${name}</h6>
                    <small class="text-muted">${personId} - ${category}</small>
                </div>
                <div class="activity-time text-end">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>${time}
                    </small>
                </div>
            </div>
        `;
        
        return item;
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

    destroy() {
        console.log('Destroying dashboard...');
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
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
        fetch(`../admin/analytics.php?action=entry_logs_hourly&dateRange=today`, {
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
        fetch(`../admin/analytics.php?action=exit_logs_hourly&dateRange=today`, {
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
        fetch(`../admin/analytics.php?action=entry_exit_hourly&dateRange=today`, {
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
class SecurityDashboard {
    constructor() {
        this.updateInterval = 5000; // 5 seconds
        this.intervalId = null;
        this.init();
    }

    init() {
        this.loadInitialData();
        this.startRealTimeUpdates();
        this.bindEvents();
    }

    loadInitialData() {
        this.updateStats();
        this.refreshEntries();
        this.refreshExits();
    }

    startRealTimeUpdates() {
        this.intervalId = setInterval(() => {
            this.updateStats();
            this.refreshEntries();
            this.refreshExits();
        }, this.updateInterval);
    }

    updateStats() {
        fetch('realtime_data.php?action=stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('todayEntries').textContent = data.total_entries;
            document.getElementById('todayExits').textContent = data.total_exits;
            document.getElementById('studentEntries').textContent = data.student_entries;
            document.getElementById('facultyEntries').textContent = data.faculty_entries;
        })
        .catch(error => {
            console.error('Error updating stats:', error);
        });
    }

    refreshEntries() {
        fetch('realtime_data.php?action=entries')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentEntries');
            container.innerHTML = '';
            
            data.entries.forEach(entry => {
                const item = this.createActivityItem(entry, 'entry');
                container.appendChild(item);
            });
        })
        .catch(error => {
            console.error('Error refreshing entries:', error);
        });
    }

    refreshExits() {
        fetch('realtime_data.php?action=exits')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentExits');
            container.innerHTML = '';
            
            data.exits.forEach(exit => {
                const item = this.createActivityItem(exit, 'exit');
                container.appendChild(item);
            });
        })
        .catch(error => {
            console.error('Error refreshing exits:', error);
        });
    }

    createActivityItem(data, type) {
        const item = document.createElement('div');
        item.className = 'activity-item fade-in';
        
        const name = `${data.StudentFName || data.FacultyFName} ${data.StudentLName || data.FacultyLName}`;
        const avatar = this.getInitials(name);
        const time = new Date(data.Timestamp).toLocaleTimeString();
        const bgColor = type === 'entry' ? '#27ae60' : '#f39c12';
        
        item.innerHTML = `
            <div class="activity-avatar" style="background-color: ${bgColor}">
                ${avatar}
            </div>
            <div class="activity-details">
                <h6>${name}</h6>
                <p>${data.PersonID} - ${data.PersonCategory}</p>
            </div>
            <div class="activity-time">
                <i class="fas fa-clock me-1"></i>${time}
            </div>
        `;
        
        return item;
    }

    getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }

    bindEvents() {
        // Manual refresh buttons
        window.refreshEntries = () => this.refreshEntries();
        window.refreshExits = () => this.refreshExits();
        
        // Pause updates when window is not visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
            } else {
                this.startRealTimeUpdates();
            }
        });
    }

    destroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new SecurityDashboard();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        dashboard.destroy();
    });
});
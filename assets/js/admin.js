class AdminDashboard {
    constructor() {
        this.charts = {};
        this.init();
    }

    init() {
        this.initializeCharts();
        this.bindEvents();
        this.loadAnalytics();
    }

    initializeCharts() {
        // Peak Hours Chart
        const peakHoursCtx = document.getElementById('peakHoursChart');
        if (peakHoursCtx) {
            this.charts.peakHours = new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: ['6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM'],
                    datasets: [{
                        label: 'Entries',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], // Initialize with zeros
                        backgroundColor: 'rgba(52, 152, 219, 0.6)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Entry Distribution by Hour'
                        }
                    }
                }
            });
        }

        // Department Chart
        const departmentCtx = document.getElementById('departmentChart');
        if (departmentCtx) {
            this.charts.department = new Chart(departmentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['COTE', 'COED'],
                    datasets: [{
                        data: [0, 0], // Initialize with zeros
                        backgroundColor: [
                            'rgba(231, 76, 60, 0.7)',
                            'rgba(46, 204, 113, 0.7)'
                        ],
                        borderColor: [
                            'rgba(231, 76, 60, 1)',
                            'rgba(46, 204, 113, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Entries by Department'
                        }
                    }
                }
            });
        }

        // Weekly Trend Chart
        const weeklyCtx = document.getElementById('weeklyTrendChart');
        if (weeklyCtx) {
            this.charts.weekly = new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Entries',
                        data: [0, 0, 0, 0, 0, 0, 0], // Initialize with zeros
                        borderColor: 'rgba(155, 89, 182, 1)',
                        backgroundColor: 'rgba(155, 89, 182, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Weekly Entry Trends'
                        }
                    }
                }
            });
        }
    }

    loadAnalytics() {
        console.log('Loading analytics...');
        
        // Load peak hours data from PHP variable
        if (typeof peakHoursData !== 'undefined' && this.charts.peakHours) {
            console.log('Peak hours data:', peakHoursData);
            const hourlyData = new Array(13).fill(0);
            
            // Map the data to the correct hour indices (6AM to 6PM)
            peakHoursData.forEach(item => {
                const hour = parseInt(item.hour);
                const count = parseInt(item.count);
                if (hour >= 6 && hour <= 18) {
                    hourlyData[hour - 6] = count;
                }
            });
            
            this.charts.peakHours.data.datasets[0].data = hourlyData;
            this.charts.peakHours.update();
            console.log('Peak hours chart updated with data:', hourlyData);
        } else {
            console.log('Peak hours data not available or chart not initialized');
        }

        // Load department data
        this.loadDepartmentData();
        
        // Load weekly trend data
        this.loadWeeklyTrendData();
    }

    loadDepartmentData() {
        console.log('Loading department data...');
        fetch('analytics.php?action=department')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Department data received:', data);
            if (this.charts.department) {
                this.charts.department.data.datasets[0].data = [
                    data.COTE || 0,
                    data.COED || 0
                ];
                this.charts.department.update();
                console.log('Department chart updated');
            }
        })
        .catch(error => {
            console.error('Error loading department data:', error);
        });
    }

    loadWeeklyTrendData() {
        console.log('Loading weekly trend data...');
        fetch('analytics.php?action=weekly')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Weekly data received:', data);
            if (this.charts.weekly) {
                const weeklyData = new Array(7).fill(0);
                
                // Map MySQL DAYOFWEEK (1=Sunday, 2=Monday, ..., 7=Saturday) to our array (0=Monday, ..., 6=Sunday)
                data.forEach(item => {
                    const mysqlDay = parseInt(item.day);
                    const count = parseInt(item.count);
                    
                    // Convert MySQL DAYOFWEEK to our Monday-first array index
                    let arrayIndex;
                    if (mysqlDay === 1) { // Sunday
                        arrayIndex = 6;
                    } else { // Monday-Saturday
                        arrayIndex = mysqlDay - 2;
                    }
                    
                    if (arrayIndex >= 0 && arrayIndex <= 6) {
                        weeklyData[arrayIndex] = count;
                    }
                });
                
                this.charts.weekly.data.datasets[0].data = weeklyData;
                this.charts.weekly.update();
                console.log('Weekly chart updated with data:', weeklyData);
            }
        })
        .catch(error => {
            console.error('Error loading weekly trend data:', error);
        });
    }

    bindEvents() {
        // Export functions
        window.exportExcel = () => {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            window.open(`export_excel.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
        };

        window.exportPDF = () => {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
        };

        window.generateReport = () => {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            this.generateCustomReport(startDate, endDate);
        };

        // Updated management function to work properly
        window.manageUsers = (type) => {
            // Validate user type
            const validTypes = ['students', 'faculty', 'security'];
            if (!validTypes.includes(type)) {
                console.error('Invalid user type:', type);
                return;
            }
            
            // Navigate to management page
            window.location.href = `manage_users.php?type=${type}`;
        };

        // Add click handlers for management cards if they exist
        const managementCards = document.querySelectorAll('.management-card');
        managementCards.forEach(card => {
            if (!card.hasAttribute('data-click-bound')) {
                card.addEventListener('click', function(e) {
                    // Determine user type from the card content or data attribute
                    const cardText = this.textContent.toLowerCase();
                    let userType = '';
                    
                    if (cardText.includes('student')) {
                        userType = 'students';
                    } else if (cardText.includes('faculty')) {
                        userType = 'faculty';
                    } else if (cardText.includes('security')) {
                        userType = 'security';
                    }
                    
                    if (userType) {
                        window.manageUsers(userType);
                    }
                });
                card.setAttribute('data-click-bound', 'true');
            }
        });
    }

    generateCustomReport(startDate, endDate) {
        console.log(`Generating custom report for ${startDate} to ${endDate}`);
        
        fetch('analytics.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=custom_report&start_date=${startDate}&end_date=${endDate}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Custom report data:', data);
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            this.displayCustomReport(data);
        })
        .catch(error => {
            console.error('Error generating custom report:', error);
            alert('Error generating report. Please try again.');
        });
    }

    displayCustomReport(data) {
        // Create a modal to display the report
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'customReportModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Custom Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">${data.total_entries || 0}</h3>
                                        <p>Total Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-warning">${data.total_exits || 0}</h3>
                                        <p>Total Exits</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-success">${data.student_entries || 0}</h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-info">${data.faculty_entries || 0}</h3>
                                        <p>Faculty Entries</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Remove modal from DOM when hidden
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing admin dashboard...');
    new AdminDashboard();
});
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
                        data: [],
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
                        data: [],
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
                        data: [],
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
        // Load peak hours data
        if (typeof peakHoursData !== 'undefined' && this.charts.peakHours) {
            const hourlyData = new Array(13).fill(0);
            peakHoursData.forEach(item => {
                if (item.hour >= 6 && item.hour <= 18) {
                    hourlyData[item.hour - 6] = item.count;
                }
            });
            this.charts.peakHours.data.datasets[0].data = hourlyData;
            this.charts.peakHours.update();
        }

        // Load department data
        this.loadDepartmentData();
        
        // Load weekly trend data
        this.loadWeeklyTrendData();
    }

    loadDepartmentData() {
        fetch('analytics.php?action=department')
        .then(response => response.json())
        .then(data => {
            if (this.charts.department) {
                this.charts.department.data.datasets[0].data = [
                    data.COTE || 0,
                    data.COED || 0
                ];
                this.charts.department.update();
            }
        })
        .catch(error => {
            console.error('Error loading department data:', error);
        });
    }

    loadWeeklyTrendData() {
        fetch('analytics.php?action=weekly')
        .then(response => response.json())
        .then(data => {
            if (this.charts.weekly) {
                const weeklyData = new Array(7).fill(0);
                data.forEach(item => {
                    if (item.day >= 1 && item.day <= 7) {
                        weeklyData[item.day - 1] = item.count;
                    }
                });
                this.charts.weekly.data.datasets[0].data = weeklyData;
                this.charts.weekly.update();
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

        window.manageUsers = (type) => {
            window.location.href = `manage_users.php?type=${type}`;
        };
    }

    generateCustomReport(startDate, endDate) {
        fetch('analytics.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=custom_report&start_date=${startDate}&end_date=${endDate}`
        })
        .then(response => response.json())
        .then(data => {
            this.displayCustomReport(data);
        })
        .catch(error => {
            console.error('Error generating custom report:', error);
        });
    }

    displayCustomReport(data) {
        // Create a modal to display the report
        const modal = document.createElement('div');
        modal.className = 'modal fade';
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
                                        <h3 class="text-primary">${data.total_entries}</h3>
                                        <p>Total Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-warning">${data.total_exits}</h3>
                                        <p>Total Exits</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-success">${data.student_entries}</h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-info">${data.faculty_entries}</h3>
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
    new AdminDashboard();
});
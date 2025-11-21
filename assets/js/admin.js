class AdminDashboard {
    constructor() {
        this.basePath = this.getBasePath();
        this.charts = {};
        this.filters = {
            dateRange: 'today',
            department: 'all',
            userType: 'all'
        };
        this.autoRefreshInterval = null;
        this.autoRefreshEnabled = true;
        this.autoRefreshRate = 15000; // 15 seconds for faster updates
        this.dashboardRefreshInterval = null;
        this.dashboardRefreshRate = 15000; // 15 seconds for dashboard
        // Don't initialize automatically - wait for showSection to call it
    }

    getBasePath() {
        // Get the directory containing the current page
        const path = window.location.pathname;
        const lastSlash = path.lastIndexOf('/');
        return path.substring(0, lastSlash) + '/';
    }

    buildAnalyticsURL(action, params) {
        // Use relative path - resolves from page context (dashboards/admin/index.php)
        return `analytics.php?action=${action}&${params}`;
    }

    init() {
        console.log('AdminDashboard init() called');
        this.initializeCharts();
        console.log('Charts initialized:', Object.keys(this.charts));
        this.setupFilterHandlers();
        this.bindEvents();
        console.log('Calling loadAnalytics...');
        this.loadAnalytics();
        this.startAutoRefresh();
    }

    initializeCharts() {
        console.log('Initializing charts...');
        // Peak Hours Chart
        const peakHoursCtx = document.getElementById('peakHoursChart');
        console.log('peakHoursChart element:', peakHoursCtx);
        if (peakHoursCtx) {
            this.charts.peakHours = new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: Array.from({length: 24}, (_, i) => `${i}:00`),
                    datasets: [{
                        label: 'Entries',
                        data: new Array(24).fill(0),
                        backgroundColor: 'rgba(52, 152, 219, 0.6)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        title: { display: true, text: 'Entry Distribution by Hour' },
                        legend: { display: true }
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
                        data: [0, 0],
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
                        title: { display: true, text: 'Entries by Department' }
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
                        data: [0, 0, 0, 0, 0, 0, 0],
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
                    scales: { y: { beginAtZero: true } },
                    plugins: {
                        title: { display: true, text: 'Weekly Entry Trends' }
                    }
                }
            });
        }

        // Entry vs Exit Chart
        const entryExitCtx = document.getElementById('entryExitChart');
        if (entryExitCtx) {
            this.charts.entryExit = new Chart(entryExitCtx, {
                type: 'bar',
                data: {
                    labels: ['Entries', 'Exits'],
                    datasets: [{
                        label: 'Count',
                        data: [0, 0],
                        backgroundColor: [
                            'rgba(46, 204, 113, 0.7)',
                            'rgba(231, 76, 60, 0.7)'
                        ],
                        borderColor: [
                            'rgba(46, 204, 113, 1)',
                            'rgba(231, 76, 60, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } },
                    plugins: {
                        title: { display: true, text: 'Entry vs Exit Comparison' }
                    }
                }
            });
        }

        // User Type Distribution Chart
        const userTypeCtx = document.getElementById('userTypeChart');
        if (userTypeCtx) {
            this.charts.userType = new Chart(userTypeCtx, {
                type: 'pie',
                data: {
                    labels: ['Students', 'Faculty', 'Staff'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.7)',
                            'rgba(155, 89, 182, 0.7)',
                            'rgba(23, 162, 184, 0.7)'
                        ],
                        borderColor: [
                            'rgba(52, 152, 219, 1)',
                            'rgba(155, 89, 182, 1)',
                            'rgba(23, 162, 184, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Entry Distribution by User Type' }
                    }
                }
            });
        }

        // Scanner Activity Chart
        const scannerCtx = document.getElementById('scannerChart');
        if (scannerCtx) {
            this.charts.scanner = new Chart(scannerCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Scans',
                        data: [],
                        backgroundColor: 'rgba(241, 196, 15, 0.7)',
                        borderColor: 'rgba(241, 196, 15, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: { x: { beginAtZero: true } },
                    plugins: {
                        title: { display: true, text: 'Scanner Activity' }
                    }
                }
            });
        }

        // Entry Logs Timeline Chart
        const entryLogsCtx = document.getElementById('entryLogsChart');
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
                        y: { beginAtZero: true, title: { display: true, text: 'Entry Count' } }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        // Exit Logs Timeline Chart
        const exitLogsCtx = document.getElementById('exitLogsChart');
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
                        y: { beginAtZero: true, title: { display: true, text: 'Exit Count' } }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        // Entry/Exit Hourly Comparison Chart
        const entryExitHourlyCtx = document.getElementById('entryExitHourlyChart');
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

        // Attempts Summary Chart (Success vs Failed)
        const attemptsSummaryCtx = document.getElementById('attemptsSummaryChart');
        if (attemptsSummaryCtx) {
            this.charts.attemptsSummary = new Chart(attemptsSummaryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Success','Failed'],
                    datasets: [{
                        data: [0,0],
                        backgroundColor: ['rgba(46,204,113,0.85)', 'rgba(241,196,15,0.85)'],
                        borderColor: ['rgba(46,204,113,1)', 'rgba(241,196,15,1)'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { title: { display: true, text: 'Scan Attempts Summary' } }
                }
            });
        }

        // Attempts by Reason Chart
        const attemptsReasonCtx = document.getElementById('attemptsReasonChart');
        if (attemptsReasonCtx) {
            this.charts.attemptsReason = new Chart(attemptsReasonCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Failed Scans',
                        data: [],
                        backgroundColor: 'rgba(231,76,60,0.85)',
                        borderColor: 'rgba(231,76,60,1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'x',
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: false }, title: { display: true, text: 'Failed Attempts by Reason' } }
                }
            });
        }
    }



    setupFilterHandlers() {
        // Date range filter
        const dateRangeSelect = document.getElementById('dateRangeFilter');
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', (e) => {
                this.filters.dateRange = e.target.value;
                this.loadAnalytics();
            });
        }

        // Department filter
        const departmentSelect = document.getElementById('departmentFilter');
        if (departmentSelect) {
            departmentSelect.addEventListener('change', (e) => {
                this.filters.department = e.target.value;
                this.loadAnalytics();
            });
        }

        // User type filter
        const userTypeSelect = document.getElementById('userTypeFilter');
        if (userTypeSelect) {
            userTypeSelect.addEventListener('change', (e) => {
                this.filters.userType = e.target.value;
                this.loadAnalytics();
            });
        }
    }

    loadAnalytics() {
        console.log('=== ANALYTICS LOAD START ===');
        console.log('Filters:', this.filters);
        
        // Build query parameters
        const params = new URLSearchParams(this.filters);
        console.log('Query params:', params.toString());
        
        // Log which charts exist
        const chartsStatus = {
            peakHours: !!this.charts.peakHours,
            department: !!this.charts.department,
            weekly: !!this.charts.weekly,
            entryExit: !!this.charts.entryExit,
            userType: !!this.charts.userType,
            scanner: !!this.charts.scanner,
            entryLogs: !!this.charts.entryLogs,
            exitLogs: !!this.charts.exitLogs,
            entryExitHourly: !!this.charts.entryExitHourly
        };
        console.log('Available charts:', chartsStatus);
        
        const promises = [
            this.loadPeakHoursData(params),
            this.loadDepartmentData(params),
            this.loadWeeklyTrendData(params),
            this.loadEntryExitData(params),
            this.loadUserTypeData(params),
            this.loadScannerActivityData(params),
            this.updateDashboardStats(params),
            this.loadEntryLogsData(params),
            this.loadExitLogsData(params),
            this.loadEntryExitHourlyData(params),
            this.loadAttemptsSummaryData(params),
            this.loadAttemptsReasonData(params)
        ];
        
        console.log(`Starting ${promises.length} analytics data loading operations...`);
        
        Promise.allSettled(promises).then((results) => {
            console.log('=== ANALYTICS LOAD COMPLETE ===');
            
            // Count successes and failures
            const succeeded = results.filter(r => r.status === 'fulfilled').length;
            const failed = results.filter(r => r.status === 'rejected').length;
            console.log(`Results: ${succeeded} succeeded, ${failed} failed`);
            
            // Show detailed results
            results.forEach((result, index) => {
                if (result.status === 'fulfilled') {
                    console.log(`✓ Promise ${index}: fulfilled`);
                } else {
                    console.error(`✗ Promise ${index}: rejected -`, result.reason);
                }
            });
            
            // Load recent scans after charts are done
            console.log('Loading recent entries and exits...');
            this.loadRecentEntries(params);
            this.loadRecentExits(params);
            console.log('=== ANALYTICS LOAD END ===');
        }).catch(error => {
            console.error('=== ANALYTICS LOAD ERROR ===', error);
        });
    }

    loadPeakHoursData(params) {
        const url = `analytics.php?action=peak_hours_by_day&${params}`;
        console.log('Fetching peak hours from:', url);
        
        return fetch(url)
            .then(response => {
                console.log('Peak hours response status:', response.status);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Peak hours data received:', data);
                if (this.charts.peakHours) {
                    if (data && Object.keys(data).length > 0) {
                        const hourlyData = new Array(24).fill(0);
                        
                        // Aggregate data across all days
                        Object.values(data).forEach(dayData => {
                            Object.entries(dayData).forEach(([hour, count]) => {
                                hourlyData[parseInt(hour)] += count;
                            });
                        });
                        
                        console.log('Processed hourly data:', hourlyData);
                        this.charts.peakHours.data.datasets[0].data = hourlyData;
                    } else {
                        // Clear chart when no data
                        this.charts.peakHours.data.datasets[0].data = new Array(24).fill(0);
                    }
                    this.charts.peakHours.update();
                }
            })
            .catch(error => {
                console.error('Error loading peak hours:', error);
                // Re-throw to maintain Promise chain
                throw error;
            });
    }

    loadDepartmentData(params) {
        const url = this.buildAnalyticsURL('department', params);
        console.log('Fetching department data from:', url);
        
        return fetch(url)
            .then(response => {
                console.log('Department response status:', response.status);
                console.log('Department response headers:', {
                    contentType: response.headers.get('content-type'),
                    contentLength: response.headers.get('content-length')
                });
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Department data received:', data);
                if (this.charts.department && data && data.daily) {
                    const chartData = [
                        data.daily.COTE || 0,
                        data.daily.COED || 0
                    ];
                    console.log('Updating department chart with data:', chartData);
                    this.charts.department.data.datasets[0].data = chartData;
                    this.charts.department.update();
                } else {
                    console.warn('Invalid department data structure:', data);
                }
            })
            .catch(error => {
                console.error('Error loading department data:', error);
                throw error;
            });
    }

    loadWeeklyTrendData(params) {
        const url = this.buildAnalyticsURL('weekly', params);
        console.log('Fetching weekly data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Weekly data received:', data);
                if (this.charts.weekly) {
                    const weeklyData = new Array(7).fill(0);
                    
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(item => {
                            const mysqlDay = parseInt(item.day);
                            const count = parseInt(item.count);
                            
                            let arrayIndex;
                            if (mysqlDay === 1) arrayIndex = 6;
                            else arrayIndex = mysqlDay - 2;
                            
                            if (arrayIndex >= 0 && arrayIndex <= 6) {
                                weeklyData[arrayIndex] = count;
                            }
                        });
                    }
                    
                    console.log('Processed weekly data:', weeklyData);
                    this.charts.weekly.data.datasets[0].data = weeklyData;
                    this.charts.weekly.update();
                }
            })
            .catch(error => {
                console.error('Error loading weekly data:', error);
                throw error;
            });
    }

    loadEntryExitData(params) {
        const url = this.buildAnalyticsURL('entry_exit_comparison', params);
        console.log('Fetching entry/exit data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Entry/Exit data received:', data);
                if (this.charts.entryExit) {
                    const chartData = [
                        data.total_entries || 0,
                        data.total_exits || 0
                    ];
                    console.log('Updating entry/exit chart with data:', chartData);
                    this.charts.entryExit.data.datasets[0].data = chartData;
                    this.charts.entryExit.update();
                }
            })
            .catch(error => {
                console.error('Error loading entry/exit data:', error);
                throw error;
            });
    }

    loadUserTypeData(params) {
        const url = this.buildAnalyticsURL('user_type_distribution', params);
        console.log('Fetching user type data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('User type data received:', data);
                if (this.charts.userType) {
                    const chartData = [
                        data.student_entries || 0,
                        data.faculty_entries || 0,
                        data.staff_entries || 0
                    ];
                    console.log('Updating user type chart with data:', chartData);
                    this.charts.userType.data.datasets[0].data = chartData;
                    this.charts.userType.update();
                }
            })
            .catch(error => {
                console.error('Error loading user type data:', error);
                throw error;
            });
    }

    loadScannerActivityData(params) {
        const url = this.buildAnalyticsURL('scanner_activity', params);
        console.log('Fetching scanner activity data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Scanner activity data received:', data);
                if (this.charts.scanner) {
                    if (data.scanners && Array.isArray(data.scanners) && data.scanners.length > 0) {
                        const labels = data.scanners.map(s => s.location || `Scanner ${s.scanner_id}`);
                        const chartData = data.scanners.map(s => s.scan_count || 0);
                        console.log('Updating scanner chart with data:', { labels, chartData });
                        this.charts.scanner.data.labels = labels;
                        this.charts.scanner.data.datasets[0].data = chartData;
                    } else {
                        // Clear chart when no data
                        this.charts.scanner.data.labels = [];
                        this.charts.scanner.data.datasets[0].data = [];
                    }
                    this.charts.scanner.update();
                }
            })
            .catch(error => {
                console.error('Error loading scanner activity:', error);
                throw error;
            });
    }

    updateDashboardStats(params) {
        const url = this.buildAnalyticsURL('dashboard_stats', params);
        console.log('Fetching dashboard stats from:', url);
        
        return fetch(url)
            .then(response => {
                console.log('Dashboard stats response status:', response.status);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Dashboard stats received:', data);
                
                // Check if error in response
                if (data.error) {
                    console.error('API Error in dashboard stats:', data.error);
                    throw new Error(data.error);
                }
                
                // Update total entries stat
                const totalEntriesEl = document.getElementById('totalEntries');
                if (totalEntriesEl) {
                    const value = data.total_entries || 0;
                    console.log('Setting totalEntries to:', value, 'element:', totalEntriesEl);
                    totalEntriesEl.textContent = value;
                } else {
                    console.warn('totalEntries element not found in DOM');
                    console.warn('Available elements:', Array.from(document.querySelectorAll('h2')).filter(el => el.id).map(el => el.id));
                }
                
                // Update total exits stat
                const totalExitsEl = document.getElementById('totalExits');
                if (totalExitsEl) {
                    const value = data.total_exits || 0;
                    console.log('Setting totalExits to:', value, 'element:', totalExitsEl);
                    totalExitsEl.textContent = value;
                } else {
                    console.warn('totalExits element not found in DOM');
                }
                
                // Update average dwell time stat
                const avgDwellEl = document.getElementById('avgDwellTime');
                if (avgDwellEl) {
                    const value = data.avg_dwell_time || '0h';
                    console.log('Setting avgDwellTime to:', value, 'element:', avgDwellEl);
                    avgDwellEl.textContent = value;
                } else {
                    console.warn('avgDwellTime element not found in DOM');
                }
                
                // Update peak hour stat
                const peakHourEl = document.getElementById('peakHour');
                if (peakHourEl) {
                    const value = data.peak_hour || 'N/A';
                    console.log('Setting peakHour to:', value, 'element:', peakHourEl);
                    peakHourEl.textContent = value;
                } else {
                    console.warn('peakHour element not found in DOM');
                }
                
                // Update busiest day stat
                const busiestDayEl = document.getElementById('busiestDay');
                if (busiestDayEl) {
                    const value = data.busiest_day || 'N/A';
                    console.log('Setting busiestDay to:', value, 'element:', busiestDayEl);
                    busiestDayEl.textContent = value;
                } else {
                    console.warn('busiestDay element not found in DOM');
                }
                
                console.log('Dashboard stats update complete');
            })
            .catch(error => {
                console.error('Error loading dashboard stats:', error);
                throw error;
            });
    }

    bindEvents() {
        // Export functions
        window.exportExcel = () => {
            const startDate = document.getElementById('startDate')?.value || '';
            const endDate = document.getElementById('endDate')?.value || '';
            window.open(`export_excel.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
        };

        window.exportPDF = () => {
            const startDate = document.getElementById('startDate')?.value || '';
            const endDate = document.getElementById('endDate')?.value || '';
            window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
        };

        window.generateReport = () => {
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }
            
            this.generateCustomReport(startDate, endDate);
        };

        // Updated management function to work properly
        window.manageUsers = (type) => {
            const validTypes = ['students', 'faculty', 'security'];
            if (!validTypes.includes(type)) {
                console.error('Invalid user type:', type);
                return;
            }
            
            window.location.href = `manage_users.php?type=${type}`;
        };

        // Add click handlers for management cards
        const managementCards = document.querySelectorAll('.management-card');
        managementCards.forEach(card => {
            if (!card.hasAttribute('data-click-bound')) {
                card.addEventListener('click', function(e) {
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

        // Refresh analytics button
        window.refreshAnalytics = () => {
            console.log('Refreshing analytics...');
            this.loadAnalytics();
        };
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

    loadEntryLogsData(params) {
        const url = this.buildAnalyticsURL('entry_logs_hourly', params);
        console.log('Fetching entry logs data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Entry logs data received:', data);
                if (this.charts.entryLogs) {
                    const entryData = new Array(24).fill(0);
                    
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(item => {
                            const hour = parseInt(item.hour);
                            const count = parseInt(item.count);
                            if (hour >= 0 && hour <= 23) {
                                entryData[hour] = count;
                            }
                        });
                    }
                    
                    console.log('Processed entry logs data:', entryData);
                    this.charts.entryLogs.data.datasets[0].data = entryData;
                    this.charts.entryLogs.update();
                }
            })
            .catch(error => {
                console.error('Error loading entry logs data:', error);
                throw error;
            });
    }

    loadExitLogsData(params) {
        const url = this.buildAnalyticsURL('exit_logs_hourly', params);
        console.log('Fetching exit logs data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Exit logs data received:', data);
                if (this.charts.exitLogs) {
                    const exitData = new Array(24).fill(0);
                    
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(item => {
                            const hour = parseInt(item.hour);
                            const count = parseInt(item.count);
                            if (hour >= 0 && hour <= 23) {
                                exitData[hour] = count;
                            }
                        });
                    }
                    
                    console.log('Processed exit logs data:', exitData);
                    this.charts.exitLogs.data.datasets[0].data = exitData;
                    this.charts.exitLogs.update();
                }
            })
            .catch(error => {
                console.error('Error loading exit logs data:', error);
                throw error;
            });
    }

    loadEntryExitHourlyData(params) {
        const url = this.buildAnalyticsURL('entry_exit_hourly', params);
        console.log('Fetching entry/exit hourly data from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Entry/Exit hourly data received:', data);
                if (this.charts.entryExitHourly) {
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
                    
                    console.log('Processed entry/exit hourly data:', { entryData, exitData });
                    this.charts.entryExitHourly.data.datasets[0].data = entryData;
                    this.charts.entryExitHourly.data.datasets[1].data = exitData;
                    this.charts.entryExitHourly.update();
                }
            })
            .catch(error => {
                console.error('Error loading entry/exit hourly data:', error);
                throw error;
            });
    }

    loadAttemptsSummaryData(params) {
        const url = this.buildAnalyticsURL('attempts_summary', params);
        console.log('Fetching attempts summary from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Attempts summary data:', data);
                if (this.charts.attemptsSummary) {
                    const total = data.total || (data.success + data.failed);
                    const successRate = total > 0 ? Math.round((data.success / total) * 100) : 0;
                    const failureRate = total > 0 ? Math.round((data.failed / total) * 100) : 0;
                    
                    this.charts.attemptsSummary.data.datasets[0].data = [data.success, data.failed];
                    this.charts.attemptsSummary.data.labels = [`Success (${successRate}%)`, `Failed (${failureRate}%)`];
                    this.charts.attemptsSummary.update();
                }
            })
            .catch(error => {
                console.error('Error loading attempts summary:', error);
                throw error;
            });
    }

    loadAttemptsReasonData(params) {
        const url = this.buildAnalyticsURL('attempts_by_reason', params);
        console.log('Fetching attempts by reason from:', url);
        
        return fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Attempts by reason data:', data);
                if (this.charts.attemptsReason && Array.isArray(data) && data.length > 0) {
                    const labels = data.map(r => {
                        // Format reason names for display
                        return r.reason
                            .replace(/_/g, ' ')
                            .replace(/\b\w/g, l => l.toUpperCase());
                    });
                    const counts = data.map(r => parseInt(r.cnt));
                    
                    this.charts.attemptsReason.data.labels = labels;
                    this.charts.attemptsReason.data.datasets[0].data = counts;
                    this.charts.attemptsReason.update();
                }
            })
            .catch(error => {
                console.error('Error loading attempts reason data:', error);
                throw error;
            });
    }

    loadRecentEntries(params) {
        // Build query string from params
        let queryString = '';
        if (params) {
            // If params is URLSearchParams object, convert to string
            if (params instanceof URLSearchParams) {
                queryString = params.toString();
            } else if (typeof params === 'string') {
                queryString = params;
            }
        }
        
        const url = `analytics.php?action=recent_entries&limit=10${queryString ? '&' + queryString : ''}`;
        console.log('Fetching recent entries from:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Recent entries received:', data);
                this.displayRecentEntries(data);
            })
            .catch(error => {
                console.error('Error loading recent entries:', error);
                const tbody = document.getElementById('entriesTableBody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-circle me-2"></i>Error loading entries
                            </td>
                        </tr>
                    `;
                }
            });
    }

    loadRecentExits(params) {
        // Build query string from params
        let queryString = '';
        if (params) {
            // If params is URLSearchParams object, convert to string
            if (params instanceof URLSearchParams) {
                queryString = params.toString();
            } else if (typeof params === 'string') {
                queryString = params;
            }
        }
        
        const url = `analytics.php?action=recent_exits&limit=10${queryString ? '&' + queryString : ''}`;
        console.log('Fetching recent exits from:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Recent exits received:', data);
                this.displayRecentExits(data);
            })
            .catch(error => {
                console.error('Error loading recent exits:', error);
                const tbody = document.getElementById('exitsTableBody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-circle me-2"></i>Error loading exits
                            </td>
                        </tr>
                    `;
                }
            });
    }

    displayRecentEntries(data) {
        const tbody = document.getElementById('entriesTableBody');
        if (!tbody) return;

        // Check if data contains error
        if (data && data.error) {
            console.error('API Error in recent entries:', data.error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-circle me-2"></i>Error: ${data.error}
                    </td>
                </tr>
            `;
            return;
        }

        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fas fa-inbox me-2"></i>No entries found
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((entry, index) => {
            const timestamp = new Date(entry.Timestamp);
            const time = timestamp.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            let personType, typeColor, typeBgColor, typeIcon;
            
            if (entry.PersonType === 'student') {
                personType = 'Student';
                typeColor = '#27AE60';
                typeBgColor = 'rgba(39, 174, 96, 0.1)';
                typeIcon = 'fa-user-graduate';
            } else if (entry.PersonType === 'faculty') {
                personType = 'Faculty';
                typeColor = '#2980B9';
                typeBgColor = 'rgba(41, 128, 185, 0.1)';
                typeIcon = 'fa-chalkboard-teacher';
            } else if (entry.PersonType === 'staff') {
                personType = 'Staff';
                typeColor = '#17a2b8';
                typeBgColor = 'rgba(23, 162, 184, 0.1)';
                typeIcon = 'fa-user-tie';
            } else {
                personType = 'Other';
                typeColor = '#7f8c8d';
                typeBgColor = 'rgba(127, 140, 141, 0.1)';
                typeIcon = 'fa-user';
            }
            
            const fullName = entry.FullName || 'Unknown';
            const personId = entry.PersonID;

            return `
                <tr style="border-bottom: 1px solid #ecf0f1; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                    <td style="padding: 12px 15px; font-weight: 600; color: #2c3e50;">
                        ${fullName}
                    </td>
                    <td style="padding: 12px 15px; color: #7f8c8d;">
                        <code style="background-color: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.9rem;">${personId}</code>
                    </td>
                    <td style="padding: 12px 15px;">
                        <span style="
                            display: inline-block;
                            padding: 4px 12px;
                            background-color: ${typeBgColor};
                            color: ${typeColor};
                            border-radius: 20px;
                            font-size: 0.85rem;
                            font-weight: 600;
                            border: 1px solid ${typeColor};
                        ">
                            <i class="fas ${typeIcon} me-1"></i>${personType.toLowerCase()}
                        </span>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; color: #7f8c8d; font-weight: 500;">
                        <i class="fas fa-clock me-1" style="color: #f39c12;"></i>${time}
                    </td>
                </tr>
            `;
        }).join('');
    }

    displayRecentExits(data) {
        const tbody = document.getElementById('exitsTableBody');
        if (!tbody) return;

        // Check if data contains error
        if (data && data.error) {
            console.error('API Error in recent exits:', data.error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-circle me-2"></i>Error: ${data.error}
                    </td>
                </tr>
            `;
            return;
        }

        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fas fa-inbox me-2"></i>No exits found
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((exit, index) => {
            const timestamp = new Date(exit.Timestamp);
            const time = timestamp.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            let personType, typeColor, typeBgColor, typeIcon;
            
            if (exit.PersonType === 'student') {
                personType = 'Student';
                typeColor = '#27AE60';
                typeBgColor = 'rgba(39, 174, 96, 0.1)';
                typeIcon = 'fa-user-graduate';
            } else if (exit.PersonType === 'faculty') {
                personType = 'Faculty';
                typeColor = '#2980B9';
                typeBgColor = 'rgba(41, 128, 185, 0.1)';
                typeIcon = 'fa-chalkboard-teacher';
            } else if (exit.PersonType === 'staff') {
                personType = 'Staff';
                typeColor = '#17a2b8';
                typeBgColor = 'rgba(23, 162, 184, 0.1)';
                typeIcon = 'fa-user-tie';
            } else {
                personType = 'Other';
                typeColor = '#7f8c8d';
                typeBgColor = 'rgba(127, 140, 141, 0.1)';
                typeIcon = 'fa-user';
            }
            
            const fullName = exit.FullName || 'Unknown';
            const personId = exit.PersonID;

            return `
                <tr style="border-bottom: 1px solid #ecf0f1; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f8f9fa';" onmouseout="this.style.backgroundColor='transparent';">
                    <td style="padding: 12px 15px; font-weight: 600; color: #2c3e50;">
                        ${fullName}
                    </td>
                    <td style="padding: 12px 15px; color: #7f8c8d;">
                        <code style="background-color: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.9rem;">${personId}</code>
                    </td>
                    <td style="padding: 12px 15px;">
                        <span style="
                            display: inline-block;
                            padding: 4px 12px;
                            background-color: ${typeBgColor};
                            color: ${typeColor};
                            border-radius: 20px;
                            font-size: 0.85rem;
                            font-weight: 600;
                            border: 1px solid ${typeColor};
                        ">
                            <i class="fas ${typeIcon} me-1"></i>${personType.toLowerCase()}
                        </span>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; color: #7f8c8d; font-weight: 500;">
                        <i class="fas fa-clock me-1" style="color: #f39c12;"></i>${time}
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Auto-refresh functionality for real-time data
    startAutoRefresh() {
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
        }

        // Auto-refresh analytics every 15 seconds when analytics section is visible
        this.autoRefreshInterval = setInterval(() => {
            const analyticsSection = document.getElementById('analytics-section');
            if (analyticsSection && analyticsSection.style.display !== 'none' && this.autoRefreshEnabled) {
                this.loadAnalytics();
            }
        }, this.autoRefreshRate);
    }

    startDashboardAutoRefresh() {
        if (this.dashboardRefreshInterval) {
            clearInterval(this.dashboardRefreshInterval);
        }

        // Auto-refresh dashboard every 15 seconds when dashboard section is visible
        this.dashboardRefreshInterval = setInterval(() => {
            const dashboardSection = document.getElementById('dashboard-section');
            if (dashboardSection && dashboardSection.style.display !== 'none') {
                this.refreshDashboardStats();
            }
        }, this.dashboardRefreshRate);
    }

    stopAutoRefresh() {
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
            this.autoRefreshInterval = null;
        }
    }

    stopDashboardAutoRefresh() {
        if (this.dashboardRefreshInterval) {
            clearInterval(this.dashboardRefreshInterval);
            this.dashboardRefreshInterval = null;
        }
    }

    disableAutoRefresh() {
        this.autoRefreshEnabled = false;
    }

    enableAutoRefresh() {
        this.autoRefreshEnabled = true;
    }

    setAutoRefreshRate(milliseconds) {
        this.autoRefreshRate = milliseconds;
        this.startAutoRefresh();
    }

    refreshDashboardStats() {
        // Refresh dashboard statistics without page reload
        fetch('index.php?ajax=1')
            .then(response => response.json())
            .then(data => {
                // Update dashboard stat cards with new data
                const entriesEl = document.querySelector('[data-stat="entries"]');
                const exitsEl = document.querySelector('[data-stat="exits"]');
                const studentEl = document.querySelector('[data-stat="student"]');
                const facultyEl = document.querySelector('[data-stat="faculty"]');
                
                // Map response data to HTML elements
                if (entriesEl) entriesEl.textContent = data.total_entries || 0;
                if (exitsEl) exitsEl.textContent = data.total_exits || 0;
                if (studentEl) studentEl.textContent = data.student_entries || 0;
                if (facultyEl) facultyEl.textContent = data.faculty_entries || 0;
            })
            .catch(error => console.log('Dashboard auto-refresh continues...'));
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing admin dashboard...');
    new AdminDashboard();
});
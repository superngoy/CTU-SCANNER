/**
 * XSS Prevention Patch for Admin Dashboard
 * Adds safe rendering functions and patches critical innerHTML operations
 * 
 * Add this script AFTER admin.js is loaded:
 * <script src="../../assets/js/safe-admin-patch.js"></script>
 */

// Store original innerHTML setter
const originalInnerHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML');

// Override innerHTML to sanitize content
Object.defineProperty(Element.prototype, 'innerHTML', {
    set: function(value) {
        // Only sanitize if DOMPurify is available and content contains HTML tags
        if (typeof DOMPurify !== 'undefined' && value && value.includes('<')) {
            value = DOMPurify.sanitize(value);
        }
        originalInnerHTML.set.call(this, value);
    },
    get: originalInnerHTML.get
});

/**
 * Safe version of updateTableRows for visitor table
 */
function updateVisitorTableSafely(visitors) {
    const tbody = document.getElementById('visitorTableBody');
    if (!tbody) return;

    if (!visitors || visitors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No visitors found</td></tr>';
        return;
    }

    let html = '';
    visitors.forEach(visitor => {
        const code = escapeHtml(visitor.visitor_code || visitor.VisitorCode || '');
        const firstName = escapeHtml(visitor.first_name || '');
        const lastName = escapeHtml(visitor.last_name || '');
        const company = escapeHtml(visitor.company || 'N/A');
        const purpose = escapeHtml((visitor.purpose || '').substring(0, 30));
        const contact = escapeHtml(visitor.contact_number || 'N/A');
        const date = visitor.created_at ? new Date(visitor.created_at).toLocaleDateString() : '';
        const time = visitor.created_at ? new Date(visitor.created_at).toLocaleTimeString() : '';

        html += `
            <tr>
                <td><span class="badge bg-info">${code}</span></td>
                <td><strong>${firstName} ${lastName}</strong></td>
                <td>${company}</td>
                <td>${purpose}</td>
                <td>${contact}</td>
                <td>${date} ${time}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

/**
 * Safe version of updateVisitorLogs
 */
function updateVisitorLogsSafely(logs) {
    const tbody = document.getElementById('visitorLogsBody');
    if (!tbody) return;

    if (!logs || logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No logs found</td></tr>';
        return;
    }

    let html = '';
    logs.forEach(log => {
        const code = escapeHtml(log.visitor_code || '');
        const firstName = escapeHtml(log.first_name || '');
        const lastName = escapeHtml(log.last_name || '');
        const checkIn = log.check_in_time ? new Date(log.check_in_time).toLocaleString() : '';
        const checkOut = log.check_out_time ? new Date(log.check_out_time).toLocaleString() : '--';
        const dwellTime = escapeHtml(log.dwell_time || '--');
        
        const statusHTML = log.check_out_time 
            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Checked Out</span>'
            : '<span class="badge bg-warning"><i class="fas fa-hourglass-start me-1"></i>Checked In</span>';

        html += `
            <tr>
                <td><strong>${code}</strong></td>
                <td>${firstName} ${lastName}</td>
                <td>${checkIn}</td>
                <td>${checkOut}</td>
                <td>${dwellTime}</td>
                <td>${statusHTML}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

/**
 * Safe version of user logs display
 */
function displayUserLogsTableSafely(logs) {
    const tbody = document.getElementById('userLogsTableBody');
    tbody.innerHTML = '';

    if (logs.length === 0) {
        return;
    }

    logs.forEach((log, index) => {
        const time = new Date(log.Timestamp).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const isEntry = log.Type === 'Entry';
        const badgeStyle = isEntry 
            ? 'background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border: none;'
            : 'background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; border: none;';
        const badgeIcon = isEntry ? '📥' : '📤';
        const scannerLocation = escapeHtml(log.ScannerID || 'N/A');

        const row = `
            <tr style="border-bottom: 1px solid #e5e7eb; transition: all 0.2s;">
                <td style="padding: 15px; font-weight: 500; color: #333;">${escapeHtml(time)}</td>
                <td style="padding: 15px;">
                    <span style="${badgeStyle} padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        ${badgeIcon} ${log.Type}
                    </span>
                </td>
                <td style="padding: 15px; color: #666;">${scannerLocation}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

/**
 * Safe display for user logs info card
 */
function displayUserLogsInfoSafely(info) {
    const content = document.getElementById('userLogsInfoContent');
    
    const fullName = escapeHtml((info.StudentFName || info.FacultyFName || info.StaffFName || 'Unknown') + ' ' + 
                                 (info.StudentMName || info.FacultyMName || info.StaffMName || '') + ' ' +
                                 (info.StudentLName || info.FacultyLName || info.StaffLName || ''));
    const userId = escapeHtml(info.StudentID || info.FacultyID || info.StaffID || 'N/A');
    const userType = escapeHtml(info.Type || 'Unknown');
    const department = escapeHtml(info.Department || 'N/A');
    const yearLevel = escapeHtml(info.YearLvl || 'N/A');
    const section = escapeHtml(info.Section || 'N/A');

    const html = `
        <div class="col-md-3">
            <small class="text-muted">Name</small>
            <p class="mb-2"><strong>${fullName}</strong></p>
        </div>
        <div class="col-md-3">
            <small class="text-muted">ID</small>
            <p class="mb-2"><strong>${userId}</strong></p>
        </div>
        <div class="col-md-3">
            <small class="text-muted">Type</small>
            <p class="mb-2"><strong>${userType}</strong></p>
        </div>
        <div class="col-md-3">
            <small class="text-muted">Department</small>
            <p class="mb-2"><strong>${department}</strong></p>
        </div>
    `;
    
    content.innerHTML = html;
}

console.log('Safe Admin Patch loaded - XSS prevention active');

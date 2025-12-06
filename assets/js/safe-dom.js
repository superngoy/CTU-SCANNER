/**
 * Safe DOM Manipulation Helpers
 * Prevents XSS vulnerabilities when working with innerHTML
 */

/**
 * Safely set HTML content on an element, sanitizing user data
 * @param element - DOM element
 * @param content - HTML/text content
 * @param useTextContent - If true, uses textContent instead of innerHTML (safer)
 */
function setSafeInnerHTML(element, content, useTextContent = false) {
    if (!element) return;
    
    if (useTextContent || typeof DOMPurify === 'undefined') {
        // Safe fallback: just set text content (no HTML tags)
        element.textContent = content;
    } else {
        // Use DOMPurify to sanitize HTML
        element.innerHTML = DOMPurify.sanitize(content);
    }
}

/**
 * Escape special characters in a string for use in HTML
 * @param str - String to escape
 * @returns - Escaped string
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Create a table row from an object, escaping all values
 * @param data - Object with data
 * @param columns - Array of column keys
 * @returns - HTML table row string
 */
function createSafeTableRow(data, columns) {
    let html = '<tr>';
    
    columns.forEach(col => {
        const value = data[col] || '';
        const escapedValue = escapeHtml(String(value));
        html += `<td>${escapedValue}</td>`;
    });
    
    html += '</tr>';
    return html;
}

/**
 * Update table body with data, safely escaping all user input
 * @param tableBodyId - ID of tbody element
 * @param items - Array of item objects
 * @param columns - Array of column keys to display
 */
function updateTableSafely(tableBodyId, items, columns) {
    const tbody = document.getElementById(tableBodyId);
    if (!tbody) return;
    
    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="' + columns.length + '" class="text-center text-muted py-4">No data found</td></tr>';
        return;
    }
    
    // Build HTML safely
    let html = '';
    items.forEach(item => {
        html += createSafeTableRow(item, columns);
    });
    
    tbody.innerHTML = html;
}

/**
 * Build a grid of cards with escaped data
 * @param containerId - ID of container element
 * @param items - Array of item objects
 * @param templateFn - Function that takes an item and returns HTML string
 */
function populateGridSafely(containerId, items, templateFn) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-4">No items found</div>';
        return;
    }
    
    // Build HTML from template
    let html = '';
    items.forEach(item => {
        html += templateFn(item);
    });
    
    // Use DOMPurify if available, otherwise just set as text
    if (typeof DOMPurify !== 'undefined') {
        container.innerHTML = DOMPurify.sanitize(html);
    } else {
        // Fallback: don't render HTML at all
        container.textContent = items.length + ' items';
    }
}

/**
 * Safely update a list of activity items
 * @param containerId - ID of container
 * @param items - Array of activity items
 */
function updateActivityListSafely(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    if (!items || items.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <div>No recent activity</div>
            </div>
        `;
        return;
    }
    
    let html = '';
    items.forEach(item => {
        const firstName = escapeHtml(item.StudentFName || item.FacultyFName || 'Unknown');
        const lastName = escapeHtml(item.StudentLName || item.FacultyLName || '');
        const fullName = `${firstName} ${lastName}`.trim();
        const personId = escapeHtml(String(item.PersonID || ''));
        const category = escapeHtml(String(item.PersonCategory || ''));
        
        // Format timestamp
        const timestamp = new Date(item.Timestamp);
        const timeStr = timestamp.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
        // Build the activity item with escaped data
        html += `
            <div class="activity-item fade-in">
                <div class="d-flex align-items-center gap-3">
                    <div class="activity-avatar">
                        ${item.image ? 
                            `<img src="${escapeHtml(item.image)}" alt="${fullName}" class="activity-user-image" onerror="this.onerror=null; this.src='../../assets/images/default-avatar.png';">` :
                            `<div class="activity-avatar-default">${fullName.charAt(0).toUpperCase()}</div>`
                        }
                    </div>
                    <div class="flex-grow-1">
                        <div class="activity-person">${fullName}</div>
                        <div class="mt-1">
                            <span class="activity-type ${category}">${category}</span>
                            <span class="activity-id">${personId}</span>
                        </div>
                    </div>
                    <div class="activity-time">
                        <i class="fas fa-clock me-1"></i>${timeStr}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Build a safe list of items for a dropdown/select
 * @param items - Array of items
 * @param labelKey - Key for label
 * @param valueKey - Key for value
 * @returns - HTML string with options
 */
function buildSafeOptions(items, labelKey = 'name', valueKey = 'id') {
    let html = '';
    
    if (!items || !Array.isArray(items)) {
        return html;
    }
    
    items.forEach(item => {
        const label = escapeHtml(String(item[labelKey] || ''));
        const value = escapeHtml(String(item[valueKey] || ''));
        html += `<option value="${value}">${label}</option>`;
    });
    
    return html;
}

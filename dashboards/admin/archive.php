<?php
session_start();
require_once '../../includes/functions.php';

$scanner = new CTUScanner();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Management - CTU Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #972529;
            --secondary-color: #E5C573;
        }

        body {
            background: #f5f6fa;
        }

        .stat-card {
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }

        .stat-card p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 0.9rem;
        }

        .archive-card {
            transition: all 0.3s ease;
            border-left: 4px solid #972529;
        }

        .archive-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .archive-card.graduated {
            border-left-color: #28a745;
        }

        .archive-card.resigned {
            border-left-color: #E5C573;
        }

        .archive-card.inactive {
            border-left-color: #6c757d;
        }

        .badge-reason {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .no-records {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .archive-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .archive-grid {
                grid-template-columns: 1fr;
            }

            .stats-number {
                font-size: 1.5rem;
            }
        }

        .timeline-item {
            display: flex;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #6c757d;
            min-width: 120px;
        }

        .badge-gradient {
            background: linear-gradient(135deg, #8A2125 0%, #DFBB65 100%);
            color: white !important;
        }
    </style>
</head>
<body style="background: #f5f6fa;">

    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-archive me-2"></i>Archive Management
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-box me-1"></i>Archived Records
                </span>
                <a class="nav-link" href="logout.php" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Statistics -->
        <div class="row mb-4" id="statsContainer">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="totalArchived">0</h3>
                    <p><i class="fas fa-archive me-1"></i>Total Archived</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="studentArchived">0</h3>
                    <p><i class="fas fa-graduation-cap me-1"></i>Students</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="facultyArchived">0</h3>
                    <p><i class="fas fa-chalkboard-user me-1"></i>Faculty</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3 id="securityArchived">0</h3>
                    <p><i class="fas fa-shield me-1"></i>Security</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Type</label>
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="students">Students</option>
                            <option value="faculty">Faculty</option>
                            <option value="security">Security</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter by Reason</label>
                        <select class="form-select" id="reasonFilter">
                            <option value="">All Reasons</option>
                            <option value="deleted">Deleted</option>
                            <option value="graduated">Graduated</option>
                        <option value="resigned">Resigned</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by name or ID...">
                    </div>
                </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-3">Loading archived records...</p>
        </div>

        <!-- Archive Grid -->
        <div id="archiveContainer" style="display: none;">
            <div class="archive-grid" id="archiveGrid">
                <!-- Cards will be populated here -->
            </div>
        </div>

        <!-- No Records -->
        <div class="no-records" id="noRecords" style="display: none;">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>No archived records found</p>
        </div>
    </div>

    <!-- Restore Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restore User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to restore this user to the active records?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> The user will be restored with their original information.
                    </div>
                    <input type="hidden" id="restoreArchiveId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmRestore()">
                        <i class="fas fa-undo me-2"></i>Restore User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Permanently Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone!</p>
                    <p>Are you sure you want to permanently delete this archived record?</p>
                    <input type="hidden" id="deleteArchiveId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash me-2"></i>Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allArchives = [];
        const restoreModal = new bootstrap.Modal(document.getElementById('restoreModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        document.addEventListener('DOMContentLoaded', function() {
            loadArchiveStats();
            loadArchiveRecords();
            setupFilters();
        });

        function setupFilters() {
            document.getElementById('typeFilter').addEventListener('change', filterRecords);
            document.getElementById('reasonFilter').addEventListener('change', filterRecords);
            document.getElementById('searchInput').addEventListener('input', filterRecords);
        }

        function loadArchiveStats() {
            fetch('archive_api.php?action=get_archive_stats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalArchived').textContent = data.total;

                    // Count by type
                    const byType = {};
                    data.by_type.forEach(item => {
                        byType[item.OriginalUserType] = item.count;
                    });

                    document.getElementById('studentArchived').textContent = byType['students'] || 0;
                    document.getElementById('facultyArchived').textContent = byType['faculty'] || 0;
                    document.getElementById('securityArchived').textContent = byType['security'] || 0;
                })
                .catch(error => {
                    console.error('Error loading stats:', error);
                });
        }

        function loadArchiveRecords() {
            document.querySelector('.loading').style.display = 'block';
            document.getElementById('archiveContainer').style.display = 'none';
            document.getElementById('noRecords').style.display = 'none';

            fetch('archive_api.php?action=get_archived')
                .then(response => response.json())
                .then(data => {
                    allArchives = data;
                    displayRecords(data);
                })
                .catch(error => {
                    console.error('Error loading records:', error);
                    document.querySelector('.loading').innerHTML = '<div class="alert alert-danger">Error loading data</div>';
                });
        }

        function filterRecords() {
            const typeFilter = document.getElementById('typeFilter').value;
            const reasonFilter = document.getElementById('reasonFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            let filtered = allArchives.filter(record => {
                let match = true;

                if (typeFilter && record.OriginalUserType !== typeFilter) match = false;
                if (reasonFilter && record.ArchiveReason !== reasonFilter) match = false;

                if (searchTerm) {
                    const fullName = `${record.FirstName || ''} ${record.MiddleName || ''} ${record.LastName || ''}`.toLowerCase();
                    const userId = record.OriginalUserID.toLowerCase();
                    match = match && (fullName.includes(searchTerm) || userId.includes(searchTerm));
                }

                return match;
            });

            displayRecords(filtered);
        }

        function displayRecords(records) {
            const container = document.getElementById('archiveGrid');
            const archiveContainer = document.getElementById('archiveContainer');
            const noRecords = document.getElementById('noRecords');

            document.querySelector('.loading').style.display = 'none';

            if (records.length === 0) {
                archiveContainer.style.display = 'none';
                noRecords.style.display = 'block';
                return;
            }

            container.innerHTML = '';
            records.forEach(record => {
                const card = createArchiveCard(record);
                container.innerHTML += card;
            });

            archiveContainer.style.display = 'block';
            noRecords.style.display = 'none';
        }

        function createArchiveCard(record) {
            const fullName = `${record.FirstName || ''} ${record.MiddleName || ''} ${record.LastName || ''}`.trim();
            const archiveDate = new Date(record.ArchiveDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            const reasonIcons = {
                'deleted': '<i class="fas fa-trash"></i>',
                'graduated': '<i class="fas fa-graduation-cap"></i>',
                'resigned': '<i class="fas fa-door-open"></i>',
                'inactive': '<i class="fas fa-ban"></i>'
            };

            const reasonColors = {
                'deleted': 'danger',
                'graduated': 'success',
                'resigned': 'warning',
                'inactive': 'secondary'
            };

            return `
                <div class="card archive-card ${record.ArchiveReason}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title mb-1">${fullName || 'N/A'}</h6>
                                <small class="text-muted">${record.OriginalUserID}</small>
                            </div>
                            <span class="badge bg-${reasonColors[record.ArchiveReason]} badge-reason">
                                ${reasonIcons[record.ArchiveReason]} ${record.ArchiveReason}
                            </span>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-date"><i class="fas fa-user"></i></span>
                            <span>${getTypeLabel(record.OriginalUserType)}</span>
                        </div>

                        <div class="timeline-item">
                            <span class="timeline-date"><i class="fas fa-building"></i></span>
                            <span>${record.Department || 'N/A'}</span>
                        </div>

                        ${record.CourseOrSchedule ? `
                        <div class="timeline-item">
                            <span class="timeline-date"><i class="fas fa-book"></i></span>
                            <span>${record.CourseOrSchedule}</span>
                        </div>
                        ` : ''}

                        <div class="timeline-item">
                            <span class="timeline-date"><i class="fas fa-calendar"></i></span>
                            <span>Archived: ${archiveDate}</span>
                        </div>

                        ${record.AdminNotes ? `
                        <div class="alert alert-light mt-3 mb-0">
                            <small><strong>Notes:</strong> ${record.AdminNotes}</small>
                        </div>
                        ` : ''}

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="prepareRestore(${record.ArchiveID})">
                                <i class="fas fa-undo me-1"></i>Restore
                            </button>
                            <button class="btn btn-sm btn-outline-danger flex-grow-1" onclick="prepareDelete(${record.ArchiveID})">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function getTypeLabel(type) {
            const labels = {
                'students': '<span class="badge bg-primary">Student</span>',
                'faculty': '<span class="badge bg-info">Faculty</span>',
                'security': '<span class="badge bg-warning">Security</span>',
                'staff': '<span class="badge bg-secondary">Staff</span>'
            };
            return labels[type] || type;
        }

        function prepareRestore(archiveId) {
            document.getElementById('restoreArchiveId').value = archiveId;
            restoreModal.show();
        }

        function confirmRestore() {
            const archiveId = document.getElementById('restoreArchiveId').value;
            const formData = new FormData();
            formData.append('action', 'restore_user');
            formData.append('archive_id', archiveId);

            fetch('archive_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    restoreModal.hide();
                    showAlert('success', data.message);
                    loadArchiveStats();
                    loadArchiveRecords();
                } else {
                    showAlert('danger', data.message || 'Failed to restore user');
                }
            })
            .catch(error => {
                console.error('Error restoring user:', error);
                showAlert('danger', 'Network error');
            });
        }

        function prepareDelete(archiveId) {
            document.getElementById('deleteArchiveId').value = archiveId;
            deleteModal.show();
        }

        function confirmDelete() {
            const archiveId = document.getElementById('deleteArchiveId').value;
            const formData = new FormData();
            formData.append('action', 'delete_archived');
            formData.append('archive_id', archiveId);

            fetch('archive_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    deleteModal.hide();
                    showAlert('success', data.message);
                    loadArchiveStats();
                    loadArchiveRecords();
                } else {
                    showAlert('danger', data.message || 'Failed to delete record');
                }
            })
            .catch(error => {
                console.error('Error deleting record:', error);
                showAlert('danger', 'Network error');
            });
        }

        function showAlert(type, message) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
            `;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alert);

            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>

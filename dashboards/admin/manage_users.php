<?php
session_start();
require_once '../../includes/functions.php';

$userType = $_GET['type'] ?? 'students';
$scanner = new CTUScanner();

// Validate user type
if (!in_array($userType, ['students', 'faculty', 'security', 'staff'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage <?php echo ucfirst($userType); ?> - CTU Scanner</title>
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

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .card-header {
            background: #fff;
            border-bottom: 2px solid #f0f0f0;
            padding: 1.5rem;
        }

        .card-header h5 {
            color: var(--primary-color);
            font-weight: 700;
        }

        .table-responsive {
            border-radius: 0 0 8px 8px;
        }

        #usersTable thead {
            background: var(--secondary-color);
            color: #333;
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

        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        /* Image upload styles */
        .image-upload-container {
            position: relative;
            display: inline-block;
        }
        
        .image-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .image-preview:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        
        .image-preview.has-image {
            border-style: solid;
            border-color: #28a745;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .image-placeholder {
            text-align: center;
            color: #6c757d;
        }
        
        .image-actions {
            position: absolute;
            top: -10px;
            right: -10px;
            display: none;
        }
        
        .image-preview.has-image:hover .image-actions {
            display: block;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .default-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .file-input-label {
            cursor: pointer;
            font-size: 0.875rem;
            margin-top: 8px;
        }
        
        .image-info {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Search and Sort Styles */
        .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            overflow: hidden;
        }

        .input-group .form-control {
            border: none;
            padding: 0.5rem 0.75rem;
        }

        .input-group .form-control:focus {
            box-shadow: none;
            border: none;
        }

        .input-group .input-group-text {
            border: none;
            padding: 0.5rem 0.75rem;
        }

        .dropdown-menu {
            min-width: 200px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f0f0f0;
        }

        .dropdown-item.active {
            background-color: #007bff;
            color: white;
        }

        .sort-section {
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 600;
        }

        /* Highlight search results */
        .table tbody tr.search-hidden {
            display: none;
        }

        .highlight {
            background-color: #fff3cd;
            font-weight: 600;
        }

        .card-header {
            flex-wrap: wrap;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: stretch !important;
            }

            .card-header > div {
                flex-direction: column !important;
            }

            .input-group {
                min-width: 100% !important;
            }

            .dropdown, .btn-primary {
                width: 100%;
            }
        }

        /* Table row clickable styling */
        #usersTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        #usersTable tbody tr:hover {
            background-color: #f8f9fa;
        }

        #usersTable tbody tr.action-row {
            cursor: default;
        }

        #usersTable tbody tr.action-row:hover {
            background-color: transparent;
        }
    </style>
</head>
<body style="background: #f5f6fa;">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-<?php echo $userType === 'students' ? 'user-graduate' : ($userType === 'faculty' ? 'chalkboard-teacher' : ($userType === 'security' ? 'shield-alt' : 'user-tie')); ?> me-2"></i>Manage <?php echo ucfirst($userType); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-users me-1"></i><?php echo ucfirst($userType); ?> Management
                </span>
                <a class="nav-link" href="logout.php" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- User Type Navigation Tabs -->
    <nav style="background: #fff; border-bottom: 2px solid #e9ecef; padding: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div class="container-fluid d-flex" style="overflow-x: auto; padding: 0;">
            <a href="manage_users.php?type=students" class="nav-link" style="border-bottom: 3px solid <?php echo $userType === 'students' ? '#972529' : 'transparent'; ?>; padding: 1rem 1.5rem; color: <?php echo $userType === 'students' ? '#972529' : '#666'; ?>; text-decoration: none; font-weight: <?php echo $userType === 'students' ? '600' : '500'; ?>;">
                <i class="fas fa-user-graduate me-2"></i>Students
            </a>
            <a href="manage_users.php?type=faculty" class="nav-link" style="border-bottom: 3px solid <?php echo $userType === 'faculty' ? '#972529' : 'transparent'; ?>; padding: 1rem 1.5rem; color: <?php echo $userType === 'faculty' ? '#972529' : '#666'; ?>; text-decoration: none; font-weight: <?php echo $userType === 'faculty' ? '600' : '500'; ?>;">
                <i class="fas fa-chalkboard-teacher me-2"></i>Faculty
            </a>
            <a href="manage_users.php?type=security" class="nav-link" style="border-bottom: 3px solid <?php echo $userType === 'security' ? '#972529' : 'transparent'; ?>; padding: 1rem 1.5rem; color: <?php echo $userType === 'security' ? '#972529' : '#666'; ?>; text-decoration: none; font-weight: <?php echo $userType === 'security' ? '600' : '500'; ?>;">
                <i class="fas fa-shield-alt me-2"></i>Security
            </a>
            <a href="manage_users.php?type=staff" class="nav-link" style="border-bottom: 3px solid <?php echo $userType === 'staff' ? '#972529' : 'transparent'; ?>; padding: 1rem 1.5rem; color: <?php echo $userType === 'staff' ? '#972529' : '#666'; ?>; text-decoration: none; font-weight: <?php echo $userType === 'staff' ? '600' : '500'; ?>;">
                <i class="fas fa-user-tie me-2"></i>Staff
            </a>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $userType === 'students' ? 'user-graduate' : ($userType === 'faculty' ? 'chalkboard-teacher' : ($userType === 'security' ? 'shield-alt' : 'user-tie')); ?> me-2"></i>
                            <?php echo ucfirst($userType); ?> Management
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <!-- Search Bar -->
                            <div class="input-group" style="min-width: 300px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by ID, Name...">
                            </div>
                            
                            <!-- Sort Dropdown Button -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-sort me-2"></i>Sort
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="sortDropdown" id="sortMenu">
                                    <!-- Sort options will be populated by JavaScript -->
                                </ul>
                            </div>
                            
                            <!-- Add User Button -->
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus me-2"></i>Add New <?php echo ucfirst(rtrim($userType, 's')); ?>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading data...
                        </div>
                        <div class="table-responsive" id="tableContainer" style="display: none;">
                            <table class="table table-striped table-hover" id="usersTable">
                                <thead style="background: var(--secondary-color); color: #333;">
                                    <tr id="tableHeaders">
                                        <!-- Headers will be populated by JavaScript -->
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <!-- Data will be loaded via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New <?php echo ucfirst(rtrim($userType, 's')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addUserForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center mb-4">
                                    <div class="image-upload-container">
                                        <div class="image-preview" id="addImagePreview">
                                            <div class="image-placeholder">
                                                <i class="fas fa-camera fa-2x mb-2"></i>
                                                <div>Click to upload photo</div>
                                            </div>
                                            <div class="image-actions">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeImage('add')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <input type="file" id="addImageInput" name="image" accept="image/*" style="display: none;">
                                        <label for="addImageInput" class="file-input-label text-primary">
                                            <i class="fas fa-upload me-1"></i>Choose Photo
                                        </label>
                                        <div class="image-info">
                                            JPG, PNG, GIF up to 5MB<br>
                                            Recommended: 800x800px
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div id="modalFormFields">
                                    <!-- Form fields will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="submit-text">Add <?php echo ucfirst(rtrim($userType, 's')); ?></span>
                            <span class="submit-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Adding...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit <?php echo ucfirst(rtrim($userType, 's')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="text-center mb-3" id="editLoadingIndicator" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Loading user data...
                        </div>
                        <div id="editFormContainer" style="display: none;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center mb-4">
                                        <div class="image-upload-container">
                                            <div class="image-preview" id="editImagePreview">
                                                <div class="image-placeholder">
                                                    <i class="fas fa-camera fa-2x mb-2"></i>
                                                    <div>Click to upload photo</div>
                                                </div>
                                                <div class="image-actions">
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeImage('edit')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="file" id="editImageInput" name="image" accept="image/*" style="display: none;">
                                            <label for="editImageInput" class="file-input-label text-primary">
                                                <i class="fas fa-upload me-1"></i>Change Photo
                                            </label>
                                            <div class="image-info">
                                                JPG, PNG, GIF up to 5MB<br>
                                                Recommended: 800x800px
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="deleteImageBtn" style="display: none;" onclick="deleteUserImage()">
                                                <i class="fas fa-trash me-1"></i>Delete Current Photo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div id="editModalFormFields">
                                        <!-- Form fields will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="editUserId" name="user_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="edit-submit-text">Update <?php echo ucfirst(rtrim($userType, 's')); ?></span>
                            <span class="edit-submit-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archive User Modal -->
    <div class="modal fade" id="archiveUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-archive me-2"></i>Archive User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Archive this user instead of permanently deleting. You can restore them later if needed.</p>
                    
                    <div class="mb-3">
                        <label for="archiveReason" class="form-label">Archive Reason <span class="text-danger">*</span></label>
                        <select class="form-select" id="archiveReason" required>
                            <option value="">Select a reason...</option>
                            <option value="deleted">Deleted</option>
                            <option value="graduated">Graduated</option>
                            <option value="resigned">Resigned</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <small class="text-muted">Select the reason for archiving this user</small>
                    </div>

                    <div class="mb-3">
                        <label for="archiveNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="archiveNotes" rows="3" placeholder="Enter any additional notes (optional)"></textarea>
                        <small class="text-muted">Add notes like contact info, forwarding address, etc.</small>
                    </div>

                    <input type="hidden" id="archiveUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="confirmArchive()">
                        <i class="fas fa-archive me-2"></i>Archive User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div class="modal fade" id="userDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detailLoadingIndicator" style="text-align: center; display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Loading user details...
                    </div>
                    <div id="detailContent" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div id="detailImageContainer" style="margin-bottom: 20px;">
                                    <img id="detailImage" src="" alt="User Photo" style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div id="detailInfo">
                                    <!-- User info will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="detailEditBtn" onclick="editUserFromDetail()">
                        <i class="fas fa-edit me-2"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userType = '<?php echo $userType; ?>';
        let users = [];
        let currentEditUserId = null;
        let currentSortBy = null;
        let isAscending = true;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupFormFields();
            setupTableHeaders();
            setupImageHandlers();
            setupSortMenu();
            setupSearchHandler();
            loadUsers();
            
            // Setup form submission
            document.getElementById('addUserForm').addEventListener('submit', handleFormSubmit);
            document.getElementById('editUserForm').addEventListener('submit', handleEditFormSubmit);
        });

        function setupImageHandlers() {
            // Add form image handlers
            const addImagePreview = document.getElementById('addImagePreview');
            const addImageInput = document.getElementById('addImageInput');
            
            addImagePreview.addEventListener('click', () => addImageInput.click());
            addImageInput.addEventListener('change', (e) => handleImagePreview(e, 'add'));
            
            // Edit form image handlers
            const editImagePreview = document.getElementById('editImagePreview');
            const editImageInput = document.getElementById('editImageInput');
            
            editImagePreview.addEventListener('click', () => editImageInput.click());
            editImageInput.addEventListener('change', (e) => handleImagePreview(e, 'edit'));
        }

        function handleImagePreview(event, formType) {
            const file = event.target.files[0];
            const preview = document.getElementById(formType + 'ImagePreview');
            
            if (file) {
                // Validate file
                if (!file.type.startsWith('image/')) {
                    showAlert('danger', 'Please select a valid image file');
                    event.target.value = '';
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) { // 5MB
                    showAlert('danger', 'File size must be less than 5MB');
                    event.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="image-actions">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeImage('${formType}')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage(formType) {
            const preview = document.getElementById(formType + 'ImagePreview');
            const input = document.getElementById(formType + 'ImageInput');
            
            input.value = '';
            preview.innerHTML = `
                <div class="image-placeholder">
                    <i class="fas fa-camera fa-2x mb-2"></i>
                    <div>Click to upload photo</div>
                </div>
            `;
            preview.classList.remove('has-image');
        }

        function deleteUserImage() {
            if (!currentEditUserId) return;
            
            if (!confirm('Are you sure you want to delete this user\'s photo?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete_image');
            formData.append('type', userType);
            formData.append('user_id', currentEditUserId);
            
            fetch('manage_users_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset image preview to default
                    const preview = document.getElementById('editImagePreview');
                    preview.innerHTML = `
                        <div class="image-placeholder">
                            <i class="fas fa-camera fa-2x mb-2"></i>
                            <div>Click to upload photo</div>
                        </div>
                    `;
                    preview.classList.remove('has-image');
                    
                    // Hide delete button
                    document.getElementById('deleteImageBtn').style.display = 'none';
                    
                    showAlert('success', data.message);
                    loadUsers(); // Refresh table
                } else {
                    showAlert('danger', data.message || 'Failed to delete image');
                }
            })
            .catch(error => {
                console.error('Error deleting image:', error);
                showAlert('danger', 'Network error. Please try again.');
            });
        }

        function setupTableHeaders() {
            const headersRow = document.getElementById('tableHeaders');
            let headers = '';
            
            if (userType === 'students') {
                headers = `
                    <th>Photo</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Section</th>
                    <th>Department</th>
                    <th>Gender</th>
                    <th>Enrollment</th>
                    <th>Status</th>
                    <th>Actions</th>
                `;
            } else if (userType === 'faculty') {
                headers = `
                    <th>Photo</th>
                    <th>Faculty ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                `;
            } else if (userType === 'security') {
                headers = `
                    <th>Photo</th>
                    <th>Security ID</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Actions</th>
                `;
            } else if (userType === 'staff') {
                headers = `
                    <th>Photo</th>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                `;
            }
            
            headersRow.innerHTML = headers;
        }

        function setupSortMenu() {
            const sortMenu = document.getElementById('sortMenu');
            let sortOptions = [];

            if (userType === 'students') {
                sortOptions = [
                    { label: 'Student ID (A-Z)', value: 'student_id_asc' },
                    { label: 'Student ID (Z-A)', value: 'student_id_desc' },
                    { label: 'Name (A-Z)', value: 'name_asc' },
                    { label: 'Name (Z-A)', value: 'name_desc' },
                    { label: 'Course (A-Z)', value: 'course_asc' },
                    { label: 'Year Level (1-4)', value: 'year_asc' },
                    { label: 'Year Level (4-1)', value: 'year_desc' },
                    { label: 'Department (COTE-COED)', value: 'department_asc' },
                    { label: 'Section (A-Z)', value: 'section_asc' }
                ];
            } else if (userType === 'faculty') {
                sortOptions = [
                    { label: 'Faculty ID (A-Z)', value: 'faculty_id_asc' },
                    { label: 'Faculty ID (Z-A)', value: 'faculty_id_desc' },
                    { label: 'Name (A-Z)', value: 'name_asc' },
                    { label: 'Name (Z-A)', value: 'name_desc' },
                    { label: 'Department (COTE-COED)', value: 'department_asc' },
                    { label: 'Birth Date (Newest)', value: 'birthdate_desc' },
                    { label: 'Birth Date (Oldest)', value: 'birthdate_asc' }
                ];
            } else if (userType === 'security') {
                sortOptions = [
                    { label: 'Security ID (A-Z)', value: 'security_id_asc' },
                    { label: 'Security ID (Z-A)', value: 'security_id_desc' },
                    { label: 'Name (A-Z)', value: 'name_asc' },
                    { label: 'Name (Z-A)', value: 'name_desc' },
                    { label: 'Schedule (A-Z)', value: 'schedule_asc' },
                    { label: 'Birth Date (Newest)', value: 'birthdate_desc' },
                    { label: 'Birth Date (Oldest)', value: 'birthdate_asc' }
                ];
            } else if (userType === 'staff') {
                sortOptions = [
                    { label: 'Staff ID (A-Z)', value: 'staff_id_asc' },
                    { label: 'Staff ID (Z-A)', value: 'staff_id_desc' },
                    { label: 'Name (A-Z)', value: 'name_asc' },
                    { label: 'Name (Z-A)', value: 'name_desc' },
                    { label: 'Position (A-Z)', value: 'position_asc' },
                    { label: 'Department (A-Z)', value: 'department_asc' },
                    { label: 'Birth Date (Newest)', value: 'birthdate_desc' },
                    { label: 'Birth Date (Oldest)', value: 'birthdate_asc' }
                ];
            }

            let menuHTML = '<li><div class="sort-section">Sort By</div></li>';
            sortOptions.forEach(option => {
                menuHTML += `<li><a class="dropdown-item" href="#" onclick="applySorting('${option.value}', event)">${option.label}</a></li>`;
            });

            sortMenu.innerHTML = menuHTML;
        }

        function setupSearchHandler() {
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function(e) {
                filterUsers(e.target.value);
            });
        }

        function filterUsers(searchTerm) {
            const rows = document.querySelectorAll('#tableBody tr');
            const term = searchTerm.toLowerCase().trim();

            if (term === '') {
                rows.forEach(row => row.classList.remove('search-hidden'));
                return;
            }

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let found = false;

                // Search in all cells except the first (photo) and last (actions)
                for (let i = 1; i < cells.length - 1; i++) {
                    const cellText = cells[i].textContent.toLowerCase();
                    if (cellText.includes(term)) {
                        found = true;
                        // Highlight the matching text
                        cells[i].innerHTML = cells[i].textContent.replace(
                            new RegExp(term, 'gi'),
                            match => `<mark class="highlight">${match}</mark>`
                        );
                    } else {
                        // Restore original text if not matching
                        cells[i].textContent = cells[i].textContent;
                    }
                }

                row.classList.toggle('search-hidden', !found);
            });
        }

        function applySorting(sortType, event) {
            event.preventDefault();

            // Determine sort field and direction
            const [field, direction] = sortType.split('_');
            isAscending = direction === 'asc';
            currentSortBy = sortType;

            // Sort the users array
            const sortedUsers = [...users].sort((a, b) => {
                let aValue, bValue;

                if (userType === 'students') {
                    switch (field) {
                        case 'student':
                            aValue = a.StudentID;
                            bValue = b.StudentID;
                            break;
                        case 'name':
                            aValue = `${a.StudentFName} ${a.StudentLName}`;
                            bValue = `${b.StudentFName} ${b.StudentLName}`;
                            break;
                        case 'course':
                            aValue = a.Course;
                            bValue = b.Course;
                            break;
                        case 'year':
                            aValue = parseInt(a.YearLvl);
                            bValue = parseInt(b.YearLvl);
                            break;
                        case 'section':
                            aValue = a.Section;
                            bValue = b.Section;
                            break;
                        case 'department':
                            aValue = a.Department;
                            bValue = b.Department;
                            break;
                        default:
                            return 0;
                    }
                } else if (userType === 'faculty') {
                    switch (field) {
                        case 'faculty':
                            aValue = a.FacultyID;
                            bValue = b.FacultyID;
                            break;
                        case 'name':
                            aValue = `${a.FacultyFName} ${a.FacultyLName}`;
                            bValue = `${b.FacultyFName} ${b.FacultyLName}`;
                            break;
                        case 'department':
                            aValue = a.Department;
                            bValue = b.Department;
                            break;
                        case 'birthdate':
                            aValue = new Date(a.Birthdate);
                            bValue = new Date(b.Birthdate);
                            break;
                        default:
                            return 0;
                    }
                } else if (userType === 'security') {
                    switch (field) {
                        case 'security':
                            aValue = a.SecurityID;
                            bValue = b.SecurityID;
                            break;
                        case 'name':
                            aValue = `${a.SecurityFName} ${a.SecurityLName}`;
                            bValue = `${b.SecurityFName} ${b.SecurityLName}`;
                            break;
                        case 'schedule':
                            aValue = a.TimeSched;
                            bValue = b.TimeSched;
                            break;
                        case 'birthdate':
                            aValue = new Date(a.BirthDate);
                            bValue = new Date(b.BirthDate);
                            break;
                        default:
                            return 0;
                    }
                } else if (userType === 'staff') {
                    switch (field) {
                        case 'staff':
                            aValue = a.StaffID;
                            bValue = b.StaffID;
                            break;
                        case 'name':
                            aValue = `${a.StaffFName} ${a.StaffLName}`;
                            bValue = `${b.StaffFName} ${b.StaffLName}`;
                            break;
                        case 'position':
                            aValue = a.Position;
                            bValue = b.Position;
                            break;
                        case 'department':
                            aValue = a.Department;
                            bValue = b.Department;
                            break;
                        case 'birthdate':
                            aValue = new Date(a.BirthDate);
                            bValue = new Date(b.BirthDate);
                            break;
                        default:
                            return 0;
                    }
                }

                // Compare values
                if (typeof aValue === 'string') {
                    aValue = aValue.toLowerCase();
                    bValue = bValue.toLowerCase();
                    return isAscending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                } else {
                    return isAscending ? aValue - bValue : bValue - aValue;
                }
            });

            // Update table with sorted data
            populateTable(sortedUsers);
            
            // Update sort button text
            const sortDropdown = document.getElementById('sortDropdown');
            const activeOption = document.querySelector(`[onclick*="${sortType}"]`);
            if (activeOption) {
                sortDropdown.innerHTML = `<i class="fas fa-sort me-2"></i>${activeOption.textContent}`;
            }
        }

        function setupFormFields() {
            const modalFormFields = document.getElementById('modalFormFields');
            let formHTML = '';

            if (userType === 'students') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="student_id" required>
                                <label>Student ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="COTE">COTE</option>
                                    <option value="COED">COED</option>
                                </select>
                                <label>Department</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="course" required>
                                <label>Course</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="year_level" required>
                                    <option value="">Select Year</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                                <label>Year Level</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="section" required>
                                <label>Section</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isEnrollCheck" name="is_enroll" value="1" checked>
                                <label class="form-check-label" for="isEnrollCheck">
                                    Student is Enrolled
                                </label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (userType === 'faculty') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="faculty_id" required>
                                <label>Faculty ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="COTE">COTE</option>
                                    <option value="COED">COED</option>
                                </select>
                                <label>Department</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (userType === 'security') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="security_id" required>
                                <label>Security ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="time_sched" required placeholder="e.g., 6AM-6PM">
                                <label>Time Schedule</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="password" required minlength="6">
                                <label>Password (min 6 characters)</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (userType === 'staff') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="staff_id" required>
                                <label>Staff ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="position" required placeholder="e.g., Registrar, Librarian">
                                <label>Position</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="COTE">COTE</option>
                                    <option value="COED">COED</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Support">Support</option>
                                </select>
                                <label>Department</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                    </div>
                `;
            }

            modalFormFields.innerHTML = formHTML;
        }

        function loadUsers() {
            document.querySelector('.loading').style.display = 'block';
            document.getElementById('tableContainer').style.display = 'none';

            fetch(`manage_users_api.php?action=get_users&type=${userType}`)
                .then(response => response.json())
                .then(data => {
                    users = data;
                    // Fix image paths for this page location (dashboards/admin/)
                    users = users.map(user => ({
                        ...user,
                        imageUrl: user.imageUrl ? '../../../' + user.imageUrl : user.imageUrl
                    }));
                    populateTable(users);
                    document.querySelector('.loading').style.display = 'none';
                    document.getElementById('tableContainer').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    document.querySelector('.loading').style.display = 'none';
                    document.getElementById('tableContainer').innerHTML = 
                        '<div class="alert alert-danger">Error loading data. Please refresh the page.</div>';
                    document.getElementById('tableContainer').style.display = 'block';
                });
        }

        function populateTable(data) {
            const tbody = document.getElementById('tableBody');
            let html = '';

            if (data.length === 0) {
                html = `<tr><td colspan="100%" class="text-center text-muted">No ${userType} found</td></tr>`;
            } else {
                data.forEach(user => {
                    html += generateTableRow(user);
                });
            }

            tbody.innerHTML = html;
            
            // Add click handlers to table rows for detail view
            document.querySelectorAll('#usersTable tbody tr').forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't open detail if clicking on action buttons
                    if (e.target.closest('.table-actions')) {
                        return;
                    }
                    
                    // Get the user ID from the second cell (ID column)
                    const cells = this.querySelectorAll('td');
                    if (cells.length >= 2) {
                        const userId = cells[1].textContent.trim();
                        if (userId) {
                            viewUserDetail(userId);
                        }
                    }
                });
            });
        }

        function generateTableRow(user) {
            let row = '<tr>';
            
            // Generate avatar/image cell
            const avatarHtml = user.imageUrl && user.imageUrl !== 'assets/images/default-avatar.png' 
                ? `<img src="${user.imageUrl}" alt="Avatar" class="user-avatar">`
                : `<div class="default-avatar">${getInitials(user)}</div>`;
            
            row += `<td>${avatarHtml}</td>`;
            
            let userId, fullName, additionalCells, actionHtml;
            
            if (userType === 'students') {
                userId = user.StudentID;
                fullName = `${user.StudentFName} ${user.StudentMName || ''} ${user.StudentLName}`.replace(/\s+/g, ' ').trim();
                const enrollmentBadge = user.IsEnroll == 1 ? 'bg-success' : 'bg-warning';
                const enrollmentText = user.IsEnroll == 1 ? 'Enrolled' : 'Not Enrolled';
                additionalCells = `
                    <td>${user.StudentID}</td>
                    <td>${fullName}</td>
                    <td>${user.Course}</td>
                    <td>${user.YearLvl}</td>
                    <td>${user.Section}</td>
                    <td><span class="badge bg-${user.Department === 'COTE' ? 'primary' : 'success'}">${user.Department}</span></td>
                    <td>${user.Gender}</td>
                    <td><button class="btn btn-sm btn-${enrollmentBadge}" onclick="toggleEnrollment('${userId}', ${user.IsEnroll == 1 ? 0 : 1}); event.stopPropagation();" title="Toggle Enrollment"><i class="fas fa-${user.IsEnroll == 1 ? 'check-circle' : 'times-circle'}"></i> ${enrollmentText}</button></td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                `;
            } else if (userType === 'faculty') {
                userId = user.FacultyID;
                fullName = `${user.FacultyFName} ${user.FacultyMName || ''} ${user.FacultyLName}`.replace(/\s+/g, ' ').trim();
                additionalCells = `
                    <td>${user.FacultyID}</td>
                    <td>${fullName}</td>
                    <td><span class="badge bg-${user.Department === 'COTE' ? 'primary' : 'success'}">${user.Department}</span></td>
                    <td>${user.Gender}</td>
                    <td>${user.Birthdate}</td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                `;
            } else if (userType === 'security') {
                userId = user.SecurityID;
                fullName = `${user.SecurityFName} ${user.SecurityMName || ''} ${user.SecurityLName}`.replace(/\s+/g, ' ').trim();
                additionalCells = `
                    <td>${user.SecurityID}</td>
                    <td>${fullName}</td>
                    <td>${user.Gender}</td>
                    <td>${user.BirthDate}</td>
                    <td>${user.TimeSched}</td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                `;
            } else if (userType === 'staff') {
                userId = user.StaffID;
                fullName = `${user.StaffFName} ${user.StaffMName || ''} ${user.StaffLName}`.replace(/\s+/g, ' ').trim();
                additionalCells = `
                    <td>${user.StaffID}</td>
                    <td>${fullName}</td>
                    <td>${user.Position}</td>
                    <td><span class="badge bg-${user.Department === 'Admin' ? 'warning' : (user.Department === 'Support' ? 'info' : 'primary')}">${user.Department}</span></td>
                    <td>${user.Gender}</td>
                    <td>${user.BirthDate}</td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                `;
            }
            
            actionHtml = `
                <td class="table-actions">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${userId}'); event.stopPropagation();" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm ${user.isActive == 1 ? 'btn-outline-warning' : 'btn-outline-success'}" 
                            onclick="toggleStatus('${userId}', ${user.isActive == 1 ? 0 : 1}); event.stopPropagation();" 
                            title="${user.isActive == 1 ? 'Deactivate' : 'Activate'}">
                        <i class="fas ${user.isActive == 1 ? 'fa-ban' : 'fa-check'}"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${userId}'); event.stopPropagation();" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            row += additionalCells + actionHtml + '</tr>';
            return row;
        }

        function getInitials(user) {
            let firstName, lastName;
            
            if (userType === 'students') {
                firstName = user.StudentFName || '';
                lastName = user.StudentLName || '';
            } else if (userType === 'faculty') {
                firstName = user.FacultyFName || '';
                lastName = user.FacultyLName || '';
            } else if (userType === 'security') {
                firstName = user.SecurityFName || '';
                lastName = user.SecurityLName || '';
            }
            
            return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            
            const submitButton = e.target.querySelector('button[type="submit"]');
            const submitText = submitButton.querySelector('.submit-text');
            const submitLoading = submitButton.querySelector('.submit-loading');
            
            // Show loading state
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline';
            submitButton.disabled = true;
            
            const formData = new FormData(e.target);
            formData.append('action', 'add_user');
            formData.append('type', userType);
            
            fetch('manage_users_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                    modal.hide();
                    
                    // Reset form and image preview
                    e.target.reset();
                    removeImage('add');
                    
                    // Reload data
                    loadUsers();
                    
                    // Show success message
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error adding user:', error);
                showAlert('danger', 'Network error. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitText.style.display = 'inline';
                submitLoading.style.display = 'none';
                submitButton.disabled = false;
            });
        }

        function editUser(userId) {
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            const loadingIndicator = document.getElementById('editLoadingIndicator');
            const formContainer = document.getElementById('editFormContainer');
            const userIdField = document.getElementById('editUserId');
            
            currentEditUserId = userId;
            
            // Show loading
            loadingIndicator.style.display = 'block';
            formContainer.style.display = 'none';
            userIdField.value = userId;
            
            modal.show();
            
            // Fetch user data
            fetch(`manage_users_api.php?action=get_user&type=${userType}&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showAlert('danger', data.error);
                        modal.hide();
                        return;
                    }
                    
                    // Fix image path for this page location (dashboards/admin/)
                    if (data.imageUrl && !data.imageUrl.startsWith('assets/')) {
                        data.imageUrl = '../../../' + data.imageUrl;
                    }
                    
                    // Populate edit form
                    populateEditForm(data);
                    
                    // Hide loading and show form
                    loadingIndicator.style.display = 'none';
                    formContainer.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    showAlert('danger', 'Error loading user data');
                    modal.hide();
                });
        }

        function populateEditForm(userData) {
            const formFields = document.getElementById('editModalFormFields');
            const imagePreview = document.getElementById('editImagePreview');
            const deleteImageBtn = document.getElementById('deleteImageBtn');
            
            // Handle image preview
            if (userData.imageUrl && userData.imageUrl !== 'assets/images/default-avatar.png') {
                imagePreview.innerHTML = `
                    <img src="${userData.imageUrl}" alt="Current Photo">
                    <div class="image-actions">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeImage('edit')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                imagePreview.classList.add('has-image');
                deleteImageBtn.style.display = 'block';
            } else {
                imagePreview.innerHTML = `
                    <div class="image-placeholder">
                        <i class="fas fa-camera fa-2x mb-2"></i>
                        <div>Click to upload photo</div>
                    </div>
                `;
                imagePreview.classList.remove('has-image');
                deleteImageBtn.style.display = 'none';
            }
            
            let formHTML = '';

            if (userType === 'students') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="student_id" value="${userData.StudentID}" readonly style="background-color: #f8f9fa;">
                                <label>Student ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="COTE" ${userData.Department === 'COTE' ? 'selected' : ''}>COTE</option>
                                    <option value="COED" ${userData.Department === 'COED' ? 'selected' : ''}>COED</option>
                                </select>
                                <label>Department</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" value="${userData.StudentFName || ''}" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name" value="${userData.StudentMName || ''}">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" value="${userData.StudentLName || ''}" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="course" value="${userData.Course || ''}" required>
                                <label>Course</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="year_level" required>
                                    <option value="">Select Year</option>
                                    <option value="1" ${userData.YearLvl == 1 ? 'selected' : ''}>1st Year</option>
                                    <option value="2" ${userData.YearLvl == 2 ? 'selected' : ''}>2nd Year</option>
                                    <option value="3" ${userData.YearLvl == 3 ? 'selected' : ''}>3rd Year</option>
                                    <option value="4" ${userData.YearLvl == 4 ? 'selected' : ''}>4th Year</option>
                                </select>
                                <label>Year Level</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="section" value="${userData.Section || ''}" required>
                                <label>Section</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" ${userData.Gender === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${userData.Gender === 'Female' ? 'selected' : ''}>Female</option>
                                    <option value="Other" ${userData.Gender === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" value="${userData.BirthDate || ''}" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (userType === 'faculty') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="faculty_id" value="${userData.FacultyID}" readonly style="background-color: #f8f9fa;">
                                <label>Faculty ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="COTE" ${userData.Department === 'COTE' ? 'selected' : ''}>COTE</option>
                                    <option value="COED" ${userData.Department === 'COED' ? 'selected' : ''}>COED</option>
                                </select>
                                <label>Department</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" value="${userData.FacultyFName || ''}" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name" value="${userData.FacultyMName || ''}">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" value="${userData.FacultyLName || ''}" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" ${userData.Gender === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${userData.Gender === 'Female' ? 'selected' : ''}>Female</option>
                                    <option value="Other" ${userData.Gender === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" value="${userData.Birthdate || ''}" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (userType === 'security') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="security_id" value="${userData.SecurityID}" readonly style="background-color: #f8f9fa;">
                                <label>Security ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="time_sched" value="${userData.TimeSched || ''}" required>
                                <label>Time Schedule</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" value="${userData.SecurityFName || ''}" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name" value="${userData.SecurityMName || ''}">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" value="${userData.SecurityLName || ''}" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" ${userData.Gender === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${userData.Gender === 'Female' ? 'selected' : ''}>Female</option>
                                    <option value="Other" ${userData.Gender === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" value="${userData.BirthDate || ''}" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="password" minlength="6" placeholder="Leave empty to keep current password">
                                <label>New Password (leave empty to keep current)</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (userType === 'staff') {
                formHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="staff_id" value="${userData.StaffID}" readonly style="background-color: #f8f9fa;">
                                <label>Staff ID</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="position" value="${userData.Position || ''}" required placeholder="e.g., Registrar, Librarian">
                                <label>Position</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="COTE" ${userData.Department === 'COTE' ? 'selected' : ''}>COTE</option>
                                    <option value="COED" ${userData.Department === 'COED' ? 'selected' : ''}>COED</option>
                                    <option value="Admin" ${userData.Department === 'Admin' ? 'selected' : ''}>Admin</option>
                                    <option value="Support" ${userData.Department === 'Support' ? 'selected' : ''}>Support</option>
                                </select>
                                <label>Department</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" ${userData.Gender === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${userData.Gender === 'Female' ? 'selected' : ''}>Female</option>
                                    <option value="Other" ${userData.Gender === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="first_name" value="${userData.StaffFName || ''}" required>
                                <label>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="middle_name" value="${userData.StaffMName || ''}">
                                <label>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="last_name" value="${userData.StaffLName || ''}" required>
                                <label>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" name="birthdate" value="${userData.BirthDate || ''}" required>
                                <label>Birth Date</label>
                            </div>
                        </div>
                    </div>
                `;
            }

            formFields.innerHTML = formHTML;
        }

        function handleEditFormSubmit(e) {
            e.preventDefault();
            
            const submitButton = e.target.querySelector('button[type="submit"]');
            const submitText = submitButton.querySelector('.edit-submit-text');
            const submitLoading = submitButton.querySelector('.edit-submit-loading');
            
            // Show loading state
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline';
            submitButton.disabled = true;
            
            const formData = new FormData(e.target);
            formData.append('action', 'update_user');
            formData.append('type', userType);
            
            fetch('manage_users_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                    modal.hide();
                    
                    // Reload data with a small delay to ensure server processes the file
                    setTimeout(() => {
                        loadUsers();
                    }, 300);
                    
                    // Show success message
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error updating user:', error);
                showAlert('danger', 'Network error. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitText.style.display = 'inline';
                submitLoading.style.display = 'none';
                submitButton.disabled = false;
            });
        }

        function toggleStatus(userId, newStatus) {
            if (!confirm(`Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this user?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('type', userType);
            formData.append('user_id', userId);
            formData.append('status', newStatus);
            
            fetch('manage_users_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadUsers(); // Reload table
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                showAlert('danger', 'Network error. Please try again.');
            });
        }

        function toggleEnrollment(userId, newEnrollment) {
            if (!confirm(`Are you sure you want to mark this student as ${newEnrollment ? 'enrolled' : 'not enrolled'}?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'update_enrollment');
            formData.append('type', 'students');
            formData.append('user_id', userId);
            formData.append('is_enroll', newEnrollment);
            
            fetch('manage_users_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadUsers(); // Reload table
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error updating enrollment:', error);
                showAlert('danger', 'Network error. Please try again.');
            });
        }

        function deleteUser(userId) {
            // Open archive modal instead of direct delete
            document.getElementById('archiveUserId').value = userId;
            const archiveModal = new bootstrap.Modal(document.getElementById('archiveUserModal'));
            archiveModal.show();
        }

        function viewUserDetail(userId) {
            const modal = new bootstrap.Modal(document.getElementById('userDetailModal'));
            const loadingIndicator = document.getElementById('detailLoadingIndicator');
            const detailContent = document.getElementById('detailContent');
            
            // Show loading
            loadingIndicator.style.display = 'block';
            detailContent.style.display = 'none';
            
            modal.show();
            
            // Fetch user data
            fetch(`manage_users_api.php?action=get_user&type=${userType}&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showAlert('danger', data.error);
                        modal.hide();
                        return;
                    }
                    
                    // Fix image path
                    if (data.imageUrl && !data.imageUrl.startsWith('assets/')) {
                        data.imageUrl = '../../../' + data.imageUrl;
                    }
                    
                    // Populate detail view
                    populateDetailView(data);
                    
                    // Hide loading and show content
                    loadingIndicator.style.display = 'none';
                    detailContent.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    showAlert('danger', 'Error loading user details');
                    modal.hide();
                });
        }

        function populateDetailView(userData) {
            const detailImage = document.getElementById('detailImage');
            const detailInfo = document.getElementById('detailInfo');
            
            // Handle image
            if (userData.imageUrl && userData.imageUrl !== 'assets/images/default-avatar.png') {
                detailImage.src = userData.imageUrl;
                detailImage.style.display = 'block';
            } else {
                detailImage.style.display = 'none';
            }
            
            let infoHtml = '';
            
            if (userType === 'students') {
                infoHtml = `
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Student ID:</strong></div>
                        <div class="col-sm-7">${userData.StudentID}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Full Name:</strong></div>
                        <div class="col-sm-7">${userData.StudentFName} ${userData.StudentMName || ''} ${userData.StudentLName}`.replace(/\s+/g, ' ').trim() + `</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Department:</strong></div>
                        <div class="col-sm-7"><span class="badge bg-${userData.Department === 'COTE' ? 'primary' : 'success'}">${userData.Department}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Course:</strong></div>
                        <div class="col-sm-7">${userData.Course}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Year Level:</strong></div>
                        <div class="col-sm-7">Year ${userData.YearLvl}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Section:</strong></div>
                        <div class="col-sm-7">${userData.Section}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Gender:</strong></div>
                        <div class="col-sm-7">${userData.Gender}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Birth Date:</strong></div>
                        <div class="col-sm-7">${userData.BirthDate || 'N/A'}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Enrollment:</strong></div>
                        <div class="col-sm-7"><span class="badge ${userData.IsEnroll == 1 ? 'bg-success' : 'bg-warning'}">${userData.IsEnroll == 1 ? 'Enrolled' : 'Not Enrolled'}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Status:</strong></div>
                        <div class="col-sm-7"><span class="badge ${userData.isActive == 1 ? 'bg-success' : 'bg-danger'}">${userData.isActive == 1 ? 'Active' : 'Inactive'}</span></div>
                    </div>
                `;
            } else if (userType === 'faculty') {
                infoHtml = `
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Faculty ID:</strong></div>
                        <div class="col-sm-7">${userData.FacultyID}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Full Name:</strong></div>
                        <div class="col-sm-7">${userData.FacultyFName} ${userData.FacultyMName || ''} ${userData.FacultyLName}`.replace(/\s+/g, ' ').trim() + `</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Department:</strong></div>
                        <div class="col-sm-7"><span class="badge bg-${userData.Department === 'COTE' ? 'primary' : 'success'}">${userData.Department}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Gender:</strong></div>
                        <div class="col-sm-7">${userData.Gender}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Birth Date:</strong></div>
                        <div class="col-sm-7">${userData.Birthdate || 'N/A'}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Status:</strong></div>
                        <div class="col-sm-7"><span class="badge ${userData.isActive == 1 ? 'bg-success' : 'bg-danger'}">${userData.isActive == 1 ? 'Active' : 'Inactive'}</span></div>
                    </div>
                `;
            } else if (userType === 'security') {
                infoHtml = `
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Security ID:</strong></div>
                        <div class="col-sm-7">${userData.SecurityID}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Full Name:</strong></div>
                        <div class="col-sm-7">${userData.SecurityFName} ${userData.SecurityMName || ''} ${userData.SecurityLName}`.replace(/\s+/g, ' ').trim() + `</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Gender:</strong></div>
                        <div class="col-sm-7">${userData.Gender}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Birth Date:</strong></div>
                        <div class="col-sm-7">${userData.BirthDate || 'N/A'}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Time Schedule:</strong></div>
                        <div class="col-sm-7">${userData.TimeSched}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-5"><strong>Status:</strong></div>
                        <div class="col-sm-7"><span class="badge ${userData.isActive == 1 ? 'bg-success' : 'bg-danger'}">${userData.isActive == 1 ? 'Active' : 'Inactive'}</span></div>
                    </div>
                `;
            }
            
            detailInfo.innerHTML = infoHtml;
            
            // Store current user ID for edit button
            document.getElementById('detailEditBtn').dataset.userId = 
                userType === 'students' ? userData.StudentID : 
                userType === 'faculty' ? userData.FacultyID : 
                userData.SecurityID;
        }

        function editUserFromDetail() {
            const userId = document.getElementById('detailEditBtn').dataset.userId;
            const modal = bootstrap.Modal.getInstance(document.getElementById('userDetailModal'));
            modal.hide();
            
            // Open edit modal after closing detail modal
            setTimeout(() => {
                editUser(userId);
            }, 300);
        }

        function confirmArchive() {
            const userId = document.getElementById('archiveUserId').value;
            const reason = document.getElementById('archiveReason').value;
            const notes = document.getElementById('archiveNotes').value;

            if (!reason) {
                showAlert('warning', 'Please select an archive reason');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'archive_user');
            formData.append('type', userType);
            formData.append('user_id', userId);
            formData.append('reason', reason);
            formData.append('notes', notes);

            fetch('archive_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('archiveUserModal'));
                    modal.hide();
                    loadUsers();
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message || 'Failed to archive user');
                }
            })
            .catch(error => {
                console.error('Error archiving user:', error);
                showAlert('danger', 'Network error. Please try again.');
            });
        }

        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert-floating');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create new alert
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
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
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
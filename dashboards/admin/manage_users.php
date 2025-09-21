<?php
session_start();
require_once '../../includes/functions.php';

$userType = $_GET['type'] ?? 'students';
$scanner = new CTUScanner();

// Validate user type
if (!in_array($userType, ['students', 'faculty', 'security'])) {
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <h4 class="text-white mb-0">Manage <?php echo ucfirst($userType); ?></h4>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $userType === 'students' ? 'user-graduate' : ($userType === 'faculty' ? 'chalkboard-teacher' : 'shield-alt'); ?> me-2"></i>
                            <?php echo ucfirst($userType); ?> Management
                        </h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add New <?php echo ucfirst(rtrim($userType, 's')); ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading data...
                        </div>
                        <div class="table-responsive" id="tableContainer" style="display: none;">
                            <table class="table table-striped table-hover" id="usersTable">
                                <thead class="table-dark">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userType = '<?php echo $userType; ?>';
        let users = [];
        let currentEditUserId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            setupFormFields();
            setupTableHeaders();
            setupImageHandlers();
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
            }
            
            headersRow.innerHTML = headers;
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
                    populateTable(data);
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
        }

        function generateTableRow(user) {
            let row = '<tr>';
            
            // Generate avatar/image cell
            const avatarHtml = user.imageUrl && user.imageUrl !== 'assets/images/default-avatar.png' 
                ? `<img src="${user.imageUrl}" alt="Avatar" class="user-avatar">`
                : `<div class="default-avatar">${getInitials(user)}</div>`;
            
            row += `<td>${avatarHtml}</td>`;
            
            if (userType === 'students') {
                const fullName = `${user.StudentFName} ${user.StudentMName || ''} ${user.StudentLName}`.replace(/\s+/g, ' ').trim();
                row += `
                    <td>${user.StudentID}</td>
                    <td>${fullName}</td>
                    <td>${user.Course}</td>
                    <td>${user.YearLvl}</td>
                    <td>${user.Section}</td>
                    <td><span class="badge bg-${user.Department === 'COTE' ? 'primary' : 'success'}">${user.Department}</span></td>
                    <td>${user.Gender}</td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${user.StudentID}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm ${user.isActive == 1 ? 'btn-outline-warning' : 'btn-outline-success'}" 
                                onclick="toggleStatus('${user.StudentID}', ${user.isActive == 1 ? 0 : 1})" 
                                title="${user.isActive == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="fas ${user.isActive == 1 ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.StudentID}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
            } else if (userType === 'faculty') {
                const fullName = `${user.FacultyFName} ${user.FacultyMName || ''} ${user.FacultyLName}`.replace(/\s+/g, ' ').trim();
                row += `
                    <td>${user.FacultyID}</td>
                    <td>${fullName}</td>
                    <td><span class="badge bg-${user.Department === 'COTE' ? 'primary' : 'success'}">${user.Department}</span></td>
                    <td>${user.Gender}</td>
                    <td>${user.Birthdate}</td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${user.FacultyID}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm ${user.isActive == 1 ? 'btn-outline-warning' : 'btn-outline-success'}" 
                                onclick="toggleStatus('${user.FacultyID}', ${user.isActive == 1 ? 0 : 1})" 
                                title="${user.isActive == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="fas ${user.isActive == 1 ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.FacultyID}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
            } else if (userType === 'security') {
                const fullName = `${user.SecurityFName} ${user.SecurityMName || ''} ${user.SecurityLName}`.replace(/\s+/g, ' ').trim();
                row += `
                    <td>${user.SecurityID}</td>
                    <td>${fullName}</td>
                    <td>${user.Gender}</td>
                    <td>${user.BirthDate}</td>
                    <td>${user.TimeSched}</td>
                    <td><span class="badge ${user.isActive == 1 ? 'bg-success' : 'bg-danger'}">${user.isActive == 1 ? 'Active' : 'Inactive'}</span></td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser('${user.SecurityID}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm ${user.isActive == 1 ? 'btn-outline-warning' : 'btn-outline-success'}" 
                                onclick="toggleStatus('${user.SecurityID}', ${user.isActive == 1 ? 0 : 1})" 
                                title="${user.isActive == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="fas ${user.isActive == 1 ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.SecurityID}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
            }
            
            row += '</tr>';
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
                    
                    // Reload data
                    loadUsers();
                    
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

        function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('type', userType);
            formData.append('user_id', userId);
            
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
                console.error('Error deleting user:', error);
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
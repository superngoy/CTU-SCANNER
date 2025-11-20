<?php
// includes/image_upload_helper.php
class ImageUploadHelper {
    private $uploadDir;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $maxWidth = 800;
    private $maxHeight = 800;

    public function __construct($baseUploadDir = '../../uploads/') {
        $this->uploadDir = $baseUploadDir;
        $this->createDirectories();
    }

    private function createDirectories() {
        $directories = [
            $this->uploadDir,
            $this->uploadDir . 'students/',
            $this->uploadDir . 'faculty/',
            $this->uploadDir . 'security/',
            $this->uploadDir . 'staff/'
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
                // Create .htaccess for security
                file_put_contents($dir . '.htaccess', "Options -Indexes\n");
            }
        }
    }

    public function uploadImage($file, $userType, $userId) {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }

            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $userId . '_' . time() . '.' . $extension;
            $targetDir = $this->uploadDir . $userType . '/';
            $targetPath = $targetDir . $filename;

            // Remove old image if exists
            $this->removeOldImage($userType, $userId);

            // Resize and optimize image
            if ($this->resizeImage($file['tmp_name'], $targetPath, $extension)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => 'uploads/' . $userType . '/' . $filename
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to process image'
                ];
            }

        } catch (Exception $e) {
            error_log("Image upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'File upload error: ' . $this->getUploadErrorMessage($file['error'])
            ];
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'message' => 'File too large. Maximum size is 5MB'
            ];
        }

        // Check file type
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, $this->allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowedTypes)
            ];
        }

        // Verify it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'message' => 'File is not a valid image'
            ];
        }

        return ['success' => true];
    }

    private function resizeImage($sourcePath, $targetPath, $extension) {
        $imageInfo = getimagesize($sourcePath);
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];

        // Calculate new dimensions
        $ratio = min($this->maxWidth / $sourceWidth, $this->maxHeight / $sourceHeight);
        if ($ratio >= 1) {
            // Image is already smaller than max dimensions, just copy
            return move_uploaded_file($sourcePath, $targetPath);
        }

        $newWidth = intval($sourceWidth * $ratio);
        $newHeight = intval($sourceHeight * $ratio);

        // Create source image
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Create resized image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($extension === 'png' || $extension === 'gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize image
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, 
                          $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        // Save resized image
        $success = false;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($resizedImage, $targetPath, 85);
                break;
            case 'png':
                $success = imagepng($resizedImage, $targetPath, 8);
                break;
            case 'gif':
                $success = imagegif($resizedImage, $targetPath);
                break;
            case 'webp':
                $success = imagewebp($resizedImage, $targetPath, 85);
                break;
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $success;
    }

    public function removeOldImage($userType, $userId) {
        $targetDir = $this->uploadDir . $userType . '/';
        $pattern = $targetDir . $userId . '_*.*';
        $files = glob($pattern);
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function deleteImage($imagePath) {
        if (!empty($imagePath) && file_exists('../../' . $imagePath)) {
            return unlink('../../' . $imagePath);
        }
        return true;
    }

    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File too large (exceeds php.ini limit)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File too large (exceeds form limit)';
            case UPLOAD_ERR_PARTIAL:
                return 'File only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'No temporary directory';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Cannot write to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    public function getImageUrl($imagePath) {
        if (empty($imagePath)) {
            return 'assets/images/default-avatar.png'; // Default image
        }
        
        // Add cache-busting query parameter to force browser to reload updated images
        // Try different paths to find the file
        $possiblePaths = [
            '../../' . $imagePath,        // From manage_users_api.php location
            '../' . $imagePath,           // From dashboards/admin/ location
            $imagePath                    // Direct path
        ];
        
        foreach ($possiblePaths as $checkPath) {
            if (file_exists($checkPath)) {
                $lastModified = filemtime($checkPath);
                return $imagePath . '?v=' . $lastModified;
            }
        }
        
        // If file not found, still return path with current timestamp for debugging
        return $imagePath . '?v=' . time();
    }
}
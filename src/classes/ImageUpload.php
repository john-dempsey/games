<?php

class ImageUpload {
    private $targetDir;
    private $targetWidth = 300;
    private $targetHeight = 375;

    public function __construct($targetDir = null) {
        if ($targetDir === null) {
            $this->targetDir = dirname(__DIR__) . '/images/';
        } else {
            $this->targetDir = $targetDir;
        }

        // Ensure target directory exists
        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0755, true);
        }
    }

    /**
     * Process an uploaded image file.
     * Assumes validation has already been performed.
     *
     * @param array $file The $_FILES array element for the uploaded file
     * @param string|null $existingFilename The filename of an existing image to replace (will be deleted)
     * @return string|false The filename of the saved image, or false on failure
     */
    public function process($file, $existingFilename = null) {
        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Get image info for processing
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }

        // Generate unique filename
        $extension = $this->getExtensionFromMimeType($mimeType);
        $filename = $this->generateUniqueFilename($extension);
        $targetPath = $this->targetDir . $filename;

        // Resize and save the image
        if (!$this->resizeAndSave($file['tmp_name'], $targetPath, $imageInfo)) {
            return false;
        }

        // Delete old image if updating
        if ($existingFilename && $existingFilename !== $filename) {
            $this->deleteImage($existingFilename);
        }

        return $filename;
    }

    private function resizeAndSave($sourcePath, $targetPath, $imageInfo) {
        list($sourceWidth, $sourceHeight, $imageType) = $imageInfo;

        // Calculate aspect ratio
        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $this->targetWidth / $this->targetHeight;

        // Calculate crop dimensions to match target aspect ratio
        if ($sourceRatio > $targetRatio) {
            // Source is wider, crop width
            $cropHeight = $sourceHeight;
            $cropWidth = $sourceHeight * $targetRatio;
            $cropX = ($sourceWidth - $cropWidth) / 2;
            $cropY = 0;
        } else {
            // Source is taller, crop height
            $cropWidth = $sourceWidth;
            $cropHeight = $sourceWidth / $targetRatio;
            $cropX = 0;
            $cropY = ($sourceHeight - $cropHeight) / 2;
        }

        // Create source image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Create target image
        $targetImage = imagecreatetruecolor($this->targetWidth, $this->targetHeight);

        // Preserve transparency for PNG
        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
            imagefilledrectangle($targetImage, 0, 0, $this->targetWidth, $this->targetHeight, $transparent);
        }

        // Resize and crop
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0,
            $cropX, $cropY,
            $this->targetWidth, $this->targetHeight,
            $cropWidth, $cropHeight
        );

        // Save the image
        $success = false;
        if ($imageType === IMAGETYPE_PNG) {
            $success = imagepng($targetImage, $targetPath, 9);
        } else {
            $success = imagejpeg($targetImage, $targetPath, 90);
        }

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $success;
    }

    public function deleteImage($filename) {
        if (empty($filename)) {
            return true;
        }

        $filePath = $this->targetDir . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    private function generateUniqueFilename($extension) {
        do {
            $filename = uniqid('game_', true) . '.' . $extension;
            $filePath = $this->targetDir . $filename;
        } while (file_exists($filePath));

        return $filename;
    }

    private function getExtensionFromMimeType($mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return 'jpg';
            case 'image/png':
                return 'png';
            default:
                return 'jpg';
        }
    }
}

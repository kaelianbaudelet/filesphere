<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;
use App\Entity\File;

class FileController
{
    private $twig;
    private $fileModel;
    private $userModel;
    private $uploadDir;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->fileModel = $dependencyContainer->get('FileModel');
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->uploadDir = realpath(__DIR__ . '/../../public/assets/upload') . '/';
    }

    public function uploadFile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $files = $_FILES['files'] ?? [];

            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
                'text/plain',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/pdf'
            ];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'zip', 'rar', '7z', 'tar', 'gz', 'txt', 'ppt', 'pptx', 'doc', 'docx', 'pdf'];

            foreach ($files['name'] as $key => $fileName) {
                $fileTmpName = $files['tmp_name'][$key];
                $fileError = $files['error'][$key];

                if (empty($fileTmpName) || $fileError !== UPLOAD_ERR_OK) {
                    $_SESSION['alert'] = ['status' => 'error', 'message' => 'File upload failed.'];
                    header('Location: /dashboard/files');
                    exit;
                }

                $fileType = mime_content_type($fileTmpName);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
                    $_SESSION['alert'] = ['status' => 'error', 'message' => 'Invalid file type or extension.'];
                    header('Location: /dashboard/files');
                    exit;
                }

                $owner = $this->userModel->getUserById($_SESSION['user']['id']);
                $token = bin2hex(random_bytes(16));
                $newFileName = $token . '.' . $fileExtension;
                $uploadFile = $this->uploadDir . $newFileName;

                if (!move_uploaded_file($fileTmpName, $uploadFile)) {
                    $_SESSION['alert'] = ['status' => 'error', 'message' => 'File move failed.'];
                    header('Location: /dashboard/files');
                    exit;
                }

                $fileSize = filesize($uploadFile);

                $file = new File(null, $token, $fileName, $fileExtension, $fileSize, $owner, new \DateTime(), new \DateTime());
                $this->fileModel->createFile($file);
            }

            $_SESSION['alert'] = ['status' => 'success', 'message' => 'Files uploaded successfully.'];
            header('Location: /dashboard/files');
            exit;
        }

        header('Location: /dashboard/files');
        exit;
    }

    public function deleteFile(string $fileId)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $file = $this->fileModel->getFileById($fileId);

        if (!$file) {
            $_SESSION['alert'] = ['status' => 'error', 'message' => 'File not found.'];
            header('Location: /dashboard/files');
            exit;
        }

        try {
            $filePath = $this->uploadDir . $file->getToken() . '.' . $file->getExtension();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->fileModel->deleteFile($fileId);
        } catch (\Exception $e) {
            $_SESSION['alert'] = ['status' => 'error', 'message' => 'Failed to delete file.'];
            header('Location: /dashboard/files');
            exit;
        }

        $_SESSION['alert'] = ['status' => 'success', 'message' => 'File deleted successfully.'];
        header('Location: /dashboard/files');
        exit;
    }

    public function downloadFile($fileId)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $file = $this->fileModel->getFileById($fileId);

        if (!$file) {
            $_SESSION['alert'] = ['status' => 'error', 'message' => 'File not found.'];
            header('Location: /dashboard/files');
            exit;
        }

        $filePath = $this->uploadDir . $file->getToken() . '.' . $file->getExtension();

        if (!file_exists($filePath)) {
            $_SESSION['alert'] = ['status' => 'error', 'message' => 'File not found.'];
            header('Location: /dashboard/files');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file->getName()) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function files()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $files = $this->fileModel->getAllFiles();
        echo $this->twig->render('fileController/files.html.twig', ['files' => $files]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ImageModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function uploadAndOptimizeImage(array $file, string $destinationPath, string $slug): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Vérification MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }

        // Limite taille (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        // Créer dossier si nécessaire
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Nom SEO
        $filename = $slug . '-' . uniqid() . '.webp';
        $targetPath = rtrim($destinationPath, '/') . '/' . $filename;

        // Création image source
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
            default:
                return null;
        }

        if (!$image) {
            return null;
        }

        // Conversion en WebP (qualité 75 = bon compromis)
        imagewebp($image, $targetPath, 75);

        // Libérer mémoire
        imagedestroy($image);

        return '/iran/public/images/' .  $filename;
    }
}

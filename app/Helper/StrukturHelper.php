<?php

namespace App\Helper;
use Illuminate\Support\Facades\File;
class StrukturHelper
{
    public static function saveImageBase64($base64Image, $folder_name, $keterangan)
    {
        // Remove the prefix (e.g., "data:image/jpeg;base64,")
        $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $imageData = base64_decode($base64Image);

        if ($imageData === false) {
            throw new \Exception('Base64 decode failed.');
        }

        // Check for the MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        $extensionMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        if (!isset($extensionMap[$mimeType])) {
            throw new \Exception('Unsupported file format. Only PNG, JPG, JPEG, GIF, and WEBP are allowed.');
        }

        $extension = $extensionMap[$mimeType];
        $filename = $keterangan . '_' . date('YmdHis') . '.' . $extension;
        $absolutePath = public_path('images/' . $folder_name . '/');
        $relativePath = 'images/' . $folder_name . '/' . $filename;

        // Create the directory if it doesn't exist
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }

        // Write the image to the file
        File::put($absolutePath . $filename, $imageData);

        return $relativePath;
    }

}
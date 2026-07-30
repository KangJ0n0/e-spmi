<?php

namespace App\Helper;

class StrukturHelper
{
    public static function saveImageBase64($image, $folder_name, $keterangan)
    {

        $dir = 'images/' . $folder_name . '/';
        $originalName = $image->getClientOriginalName();
        $extension = $image->getClientOriginalExtension();
        $fileSizeInBytes = $image->getSize();
        $sanitizedFileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $filename = $keterangan . date('YmdHis') . '-' . $sanitizedFileName . '.' . $extension;
        $maxFileSizeInBytes = 2 * 1024 * 1024;
        if ($fileSizeInBytes > $maxFileSizeInBytes) {
            throw new ErrorLogicExeption('Maksimum Ukuran File (2MB).');
        }
        if (!in_array(strtolower($extension), ['png', 'jpg', 'jpeg'])) {
            throw new ErrorLogicExeption('Format File Bukan PNG,JPG,JPEG');
        }
        $absolutePath = public_path($dir);
        $relativePath = $dir . $filename;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        $image->move($absolutePath, $filename);

        return $relativePath;
    }

}
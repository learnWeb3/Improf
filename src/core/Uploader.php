<?php

namespace Application\Core;

class Uploader
{
    const PHP_UPLOAD_ERRORS = array(
        1 => 'file exceeds the upload_max_filesize directive in php.ini',
        2 => 'file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'file was only partially uploaded',
        4 => 'no file was uploaded',
        6 => 'missing a temporary folder',
        7 => 'failed to write file to disk.',
        8 => 'a PHP extension stopped the file upload.',
    );
    static function upload(array $files): array
    {
        $uploads = [];
        $authorized_file_types = ["image/jpeg", "image/jpg", "image/png", "image/svg"];
        foreach ($files as $key => $file) {
            $file_name = $file["name"];
            $file_type = $file["type"];
            $file_tmp_name = $file["tmp_name"];
            $upload_error_code = $file["error"];
            $file_size = $file['size'];

            $time = time();
            $upload_errors = [];
            $file_from_path = $file_tmp_name;
            $uploaded_file_name =  $file_name . '_' . $time;
            $file_to_path =  UPLOAD_IMG_PATH . "/" . $uploaded_file_name;

            if ($upload_error_code !== 0) {
                $upload_errors[] = self::PHP_UPLOAD_ERRORS[$upload_error_code];
            }
            if ($file_size > UPLOAD_IMG_SIZE_LIMIT) {
                $upload_errors[] = "incorrect file size";
            }
            if (in_array($file_type, $authorized_file_types) === false) {
                $upload_errors[] = "incorrect file format";
            }
            if (count($upload_errors) === 0) {
                move_uploaded_file($file_from_path, $file_to_path);
                $upload = new HyperMediaFile($uploaded_file_name, $file_type, $file_to_path, $upload_errors);
            } else {
                $upload = new HyperMediaFile(null, $file_type, $file_from_path, $upload_errors);
            }
            $uploads[] = $upload;
        }
        return $uploads;
    }
}

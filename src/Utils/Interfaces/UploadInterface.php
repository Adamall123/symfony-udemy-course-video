<?php 

namespace App\Utils\Interfaces; 

interface UploadInterface {

    public function upload($file);

    public function delete($path);
}
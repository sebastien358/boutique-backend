<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadProvider
{
    public function __construct(
        private string $picturePath
    ){
    }

    public function upload(UploadedFile $file)
    {
        $newFileName = uniqid() . '.' . $file->guessExtension();
        $file->move($this->picturePath, $newFileName);
        return $newFileName;
    }
}
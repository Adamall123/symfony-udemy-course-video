<?php 

namespace App\Utils;

use App\Utils\Interfaces\UploadInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LocalUploader implements UploadInterface
{
    private $targetDirectory; 

    public $file;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload($file)
    {
        $video_number = random_int(1,10000000);
        $fileName = $video_number .'.'. $file->guessExtension(); 

        try{
            $file->move($this->getTargetDirectory(), $fileName);
        } catch(FileException $e)
        {
            ////
        }

        $oryg_file_name = $this->clear(pathinfo($file->getClientOriginalName()
        , PATHINFO_FILENAME)); 
        return [$fileName, $oryg_file_name];
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    private function clear($string)
    {
        $string =  preg_replace('/[^A-Za-z0-9- ]+/', '', $string);
        return $string;
    }

    public function delete($path)
    {
        $fileSystem = new Filesystem();

        try{

            $fileSystem->remove('.', $path);
        }catch(IOExceptionInterface $e){
            echo "An error occured while deleting your file at " .$e->getPath();
        }
        return true;
    }
}


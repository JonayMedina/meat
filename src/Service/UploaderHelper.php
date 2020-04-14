<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Asset\Context\RequestStackContext;

class UploaderHelper
{
    /**
     * Location folder.
     */
    const LOCATION_IMAGE = 'location_image';

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var RequestStackContext
     */
    private $requestStackContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $publicAssetBaseUrl;

    public function __construct(FilesystemInterface $uploadsFilesystem, RequestStackContext $requestStackContext, LoggerInterface $logger, string $uploadedAssetsBaseUrl)
    {
        $this->filesystem = $uploadsFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    /**
     * @param File $file
     * @param string|null $existingFilename
     * @return string
     * @throws \League\Flysystem\FileExistsException
     * @throws \Exception
     */
    public function uploadLocationImage(File $file, ?string $existingFilename): string
    {
        $newFilename = $this->uploadFile($file,  self::LOCATION_IMAGE, true);

        if ($existingFilename) {
            try {
                $result = $this->deleteLocationImage($existingFilename);

                if ($result === false) {
                    throw new \Exception(sprintf('Could not delete old uploaded file "%s"', $existingFilename));
                }
            } catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('Old uploaded file "%s" was missing when trying to delete', $existingFilename));
            }
        }

        return $newFilename;
    }

    /**
     * @param $existingFilename
     * @return bool
     * @throws FileNotFoundException
     */
    public function deleteLocationImage($existingFilename)
    {
        if (empty($existingFilename)) {
            return false;
        }

        return $this->filesystem->delete( self::LOCATION_IMAGE.'/'.$existingFilename);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getPublicPath(string $path): string
    {
        $fullPath = $this->publicAssetBaseUrl.'/'  . $path;
        // if it's already absolute, just return
        if (strpos($fullPath, '://') !== false) {
            return $fullPath;
        }

        // needed if you deploy under a subdirectory
        return $this->requestStackContext
            ->getBasePath().$fullPath;
    }

    /**
     * @param string $path
     * @return resource
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function readStream(string $path)
    {
        $resource = $this->filesystem->readStream($path);

        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }

        return $resource;
    }

    /**
     * @param string $path
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function deleteFile(string $path)
    {
        $result = $this->filesystem->delete($path);

        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    }

    /**
     * @param File $file
     * @param string $directory
     * @param bool $isPublic
     * @return string
     * @throws \League\Flysystem\FileExistsException
     * @throws \Exception
     */
    private function uploadFile(File $file, string $directory, bool $isPublic): string
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }
        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), 'r');
        $result = $this->filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream,
            [
                'visibility' => $isPublic ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE
            ]
        );

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;
    }
}

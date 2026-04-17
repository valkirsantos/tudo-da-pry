<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class S3PresignedService
{
    public function generateUploadUrl(string $path, string $mimeType): string
    {
        /** @var \League\Flysystem\AwsS3V3\AwsS3V3Adapter $adapter */
        $client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');

        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $path,
            'ContentType' => $mimeType,
        ]);

        return (string) $client->createPresignedRequest($command, '+5 minutes')->getUri();
    }

    public function generateDownloadUrl(string $path): string
    {
        /** @var \League\Flysystem\AwsS3V3\AwsS3V3Adapter $adapter */
        $client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');

        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $path,
        ]);

        return (string) $client->createPresignedRequest($command, '+1 hour')->getUri();
    }
}

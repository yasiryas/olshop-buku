<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;

trait CopiesImages
{
    protected function copyImageToStorage(string $sourcePath, string $destinationFolder, string $fileName): string
    {
        $disk = Storage::disk('public');
        $destination = $destinationFolder . '/' . $fileName;

        if (! $disk->exists($destination)) {
            $source = public_path($sourcePath);

            if (file_exists($source)) {
                $disk->put($destination, file_get_contents($source));
            }
        }

        return $destination;
    }
}
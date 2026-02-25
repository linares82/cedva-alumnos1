<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    public static function upload(UploadedFile $file, string $folder, $disk = 'public'): string
    {
        // filename withouth extension
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // extension
        $extension = $file->getClientOriginalExtension();
        // add time to filename
        $filename = $filename . '-' . time() . '.' . $extension;
        //dd($folder . $filename);
        //return $file->storeAs($folder, $filename, $disk);
        $guardado = Storage::disk($disk)->put($folder . $filename, \File::get($file));
        Storage::disk($disk)->setVisibility($folder . $filename, 'public');
        if ($guardado == 1) {
            return $filename;
        } else {
            return $guardado;
        }
    }

    public static function delete(string $path, $disk = 'public'): bool
    {
        //dd($path);
        //dd(Storage::disk($disk)->exists($path));
        if (! Storage::disk($disk)->exists($path)) {
            return false;
        }

        //dd(Storage::disk($disk)->delete($path));
        return Storage::disk($disk)->delete($path);
    }

    public static function urlFile(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }
}

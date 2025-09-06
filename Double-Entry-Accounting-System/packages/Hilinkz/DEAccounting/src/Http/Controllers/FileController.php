<?php

namespace Hilinkz\DEAccounting\Http\Controllers;

use Hilinkz\DEAccounting\Models\DeFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function deleteOld($id)
    {
        $file = DeFile::findOrFail($id);

        if ($file) {
            if (file_exists(public_path($file->path))) {
                    unlink(public_path($file->path));
                }
            $file->delete();
            return redirect()->back()->with('success', 'File deleted successfully.');
        }

        return redirect()->back()->with('error', 'Failed to delete.');
    }

    public function delete($id)
    {
        $file = DeFile::findOrFail($id);
        if ($file) {
            DeFile::destroy($file);
            return redirect()->back()->with('success', 'File deleted successfully.');
        }else{
            return redirect()->back()->with('error', 'Failed to delete.');
        }
        
    }

    public function download($id)
    {
        $file = DeFile::findOrFail($id);
        if ($file) {
            $disk = env('FILE_UPLOAD') === 'S3' ? 's3' : 'public';
            $path = $file->path;

            if (Str::startsWith($path, ['http://', 'https://'])) {
                $bucketUrl = env('AWS_URL');
                $bucketUrl = rtrim($bucketUrl, '/');
                $path = Str::after($path, $bucketUrl . '/');
            } else {
                $path = str_replace(asset('storage') . '/', '', $path);
            }
            if (Storage::disk($disk)->exists($path)) {
                return response()->redirectTo($file->path);
                // return Storage::disk($disk)->download($path);
            }else{
                return redirect()->back()->with('error', 'File not found.');
            }

        }else{
            return redirect()->back()->with('error', 'Failed to download.');
        }
        
    }


}

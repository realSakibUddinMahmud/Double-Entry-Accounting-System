<?php

namespace Hilinkz\DEAccounting\Models;

use Illuminate\Database\Eloquent\Model;
use Hilinkz\DEAccounting\Models\DeAccountType;

use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeFile extends Model implements AuditableContract
{
    use Auditable;
    use UsesTenantConnection;

    protected $table = 'files';

    protected $fillable = ['title', 'path', 'fileable_type', 'fileable_id'];

    public function fileable()
    {
        return $this->morphTo();
    }

    public static function upload($files,$result)
    {
        if (!empty($files)) {
            $storageDisk = env('FILE_UPLOAD') === 'S3' ? 's3' : 'public';
            $uploadPath = 'uploads/de-files';

            foreach ($files as $file) {
                // Generate a unique filename with original name, timestamp, and random string
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $uniqueName = Str::slug($originalName) . '-' . time() . '-' . Str::random(8) . '.' . $extension;

                $storedPath = $file->storeAs($uploadPath, $uniqueName, $storageDisk);

                // Get full URL for S3 or local
                $fileUrl = Storage::disk($storageDisk)->url($storedPath);

                DeFile::create([
                    'title' => $file->getClientOriginalName(),
                    'path' => $fileUrl,
                    'fileable_type' => get_class($result['data']) ?? null,
                    'fileable_id' => $result['data']['id'] ?? null,
                ]);
            }
        }
    }

    public static function destroy($file)
    {
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
                Storage::disk($disk)->delete($path);
            }
            $file->delete();
        }
        
    }


}

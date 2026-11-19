<?php

namespace App\Http\Controllers\Panel;

use App\http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    public function download(Attachment $attachment)
    {
        $this->authorize('download', $attachment);

        $filePath = storage_path('app/public/' . $attachment->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'فایل روی سرور موجود نیست.');
        }

        return response()->download($filePath, $attachment->file_name);
    }
}
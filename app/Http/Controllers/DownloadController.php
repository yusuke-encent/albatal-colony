<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __invoke(Purchase $purchase): StreamedResponse
    {
        $this->authorize('download', $purchase);

        $stream = Storage::disk(config('marketplace.content_disk'))->readStream(
            $purchase->content->download_path,
        );

        abort_unless(is_resource($stream), 404);

        return response()->streamDownload(
            function () use ($stream): void {
                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            },
            $purchase->content->download_name,
            array_filter([
                'Content-Type' => $purchase->content->download_mime_type,
            ]),
        );
    }
}

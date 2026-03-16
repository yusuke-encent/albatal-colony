<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __invoke(Purchase $purchase): StreamedResponse
    {
        $this->authorize('download', $purchase);

        return Storage::disk(config('marketplace.content_disk'))->download(
            $purchase->content->download_path,
            $purchase->content->download_name,
        );
    }
}

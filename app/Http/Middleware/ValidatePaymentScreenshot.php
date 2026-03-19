<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidatePaymentScreenshot
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasFile('screenshot')) {
            return $next($request);
        }

        $file = $request->file('screenshot');

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->errorBack($request, 'File size must not exceed 2MB.');
        }

        $allowedMimes = ['image/jpeg', 'image/jpg'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            return $this->errorBack($request, 'Only JPEG images are allowed.');
        }

        $allowedExtensions = ['jpg', 'jpeg'];
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        if (! in_array($extension, $allowedExtensions)) {
            return $this->errorBack($request, 'Only .jpg or .jpeg files are allowed.');
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return $this->errorBack($request, 'Unable to read uploaded file.');
        }
        $header = fread($handle, 3);
        fclose($handle);

        if ($header === false || bin2hex($header) !== 'ffd8ff') {
            return $this->errorBack($request, 'The file is not a valid image.');
        }

        return $next($request);
    }

    private function errorBack(Request $request, string $message): Response
    {
        $userId = Auth::check() ? Auth::id() : null;

        \Illuminate\Support\Facades\Log::warning('Payment screenshot validation failed', [
            'user_id' => $userId,
            'ip' => $request->ip(),
            'message' => $message,
            'file_name' => $request->file('screenshot')?->getClientOriginalName(),
            'mime_type' => $request->file('screenshot')?->getMimeType(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 422);
        }

        return redirect()->back()->with('error', $message);
    }
}

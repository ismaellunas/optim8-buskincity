<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseVariable;

class StoredCssController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    public function __invoke(string $cssName)
    {
        $result = $this->settingService->storedCss($cssName);

        if (!is_null($result)) {
            $response = Response::make($result['content']);
            $response->header('Content-Type', 'text/css');
            $response->header('Last-Modified', $result['lastModified']->toRfc7231String());
            $response->header('Cache-Control', 'private');

            if (config('app.env') == 'local') {
                $response->header('Expires', now()->addMonths(3)->toRfc7231String());
            }

            return $response;
        }

        return abort(ResponseVariable::HTTP_NOT_FOUND);
    }
}

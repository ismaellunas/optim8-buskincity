<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PageTranslation;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseVariable;

class StylePageBuilderController extends Controller
{
    public function __invoke(string $uidPageBuilder)
    {
        if (strlen($uidPageBuilder) <= 16) {
            abort(ResponseVariable::HTTP_NOT_FOUND);
        }

        $pageTranslation = PageTranslation::select([
                'id',
                'generated_style',
                'data',
                'updated_at'
            ])
            ->uid($uidPageBuilder)
            ->first();

        if ($pageTranslation) {
            if (!$pageTranslation->hasGeneratedStyle()) {
                $pageTranslation->generatePageStyle();
            }

            $response = Response::make($pageTranslation->generated_style);
            $response->header('Content-Type', 'text/css');
            $response->header('Last-Modified', $pageTranslation->updated_at);
            $response->header('Cache-Control', 'private');
            $response->header('Expires', date('D, d M Y H:i:s \G\M\T', strtotime("+30 days")));

            return $response;
        }

        return abort(ResponseVariable::HTTP_NOT_FOUND);
    }
}

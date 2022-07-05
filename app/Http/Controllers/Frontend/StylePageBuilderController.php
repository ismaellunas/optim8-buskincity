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
        if (strlen($uidPageBuilder) <= 6) {
            abort(ResponseVariable::HTTP_NOT_FOUND);
        }

        $pageTranslation = PageTranslation::select([
                'generate_style',
                'id',
                'data'
            ])
            ->uid($uidPageBuilder)
            ->first();

        if ($pageTranslation) {
            if (!$pageTranslation->hasGeneratedStyle()) {
                $pageTranslation->generatePageStyle();
            }

            $response = Response::make($pageTranslation->generate_style);
            $response->header('Content-Type', 'text/css');
            return $response;
        }

        return abort(ResponseVariable::HTTP_NOT_FOUND);
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PageTranslation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseVariable;

class StylePageBuilderController extends Controller
{
    public function __invoke(string $uidPageBuilder)
    {
        $pageTranslation = PageTranslation::select([
                'id',
                'generated_style',
                'data',
                'updated_at'
            ])
            ->uid($uidPageBuilder)
            ->first();

        if ($pageTranslation) {
            $response = Response::make($pageTranslation->generated_style);
            $response->header('Content-Type', 'text/css');
            $response->header('Last-Modified', $pageTranslation->updated_at->toRfc7231String());
            $response->header('Cache-Control', 'private');
            $response->header('Expires', Carbon::now()->addMonth()->toRfc7231String());

            return $response;
        }

        return abort(ResponseVariable::HTTP_NOT_FOUND);
    }
}

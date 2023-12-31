<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\{BannerResource, ProductResource};
use App\Models\{Banner, Product};
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HomePageController extends Controller
{
  public function __invoke(): JsonResponse
  {
    $banners = Banner::with(['media'])->take(10)->get();

    $products = Product::with(['media', 'category', 'brand'])
      ->published()
      ->latest('id')
      ->take(10)
      ->get();

    return response()->json([
      'data' => [
        'banners' => BannerResource::collection($banners),
        'products' => ProductResource::collection($products),
      ]
    ], Response::HTTP_OK);
  }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backing\StoreBackingRequest;
use App\Http\Resources\BackingResource;
use App\Services\BackingService;
use Exception;
use Illuminate\Http\Request;
use RuntimeException;

class BackingController extends Controller
{
    public function __construct(private BackingService $backingService) {}

    public function store(StoreBackingRequest $request, $id)
    {
        try {
            $backing = $this->backingService->store(
                $id,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Donasi berhasil diproses.',
                'data' => new BackingResource($backing)
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $backings = $this->backingService->getMyBackings($request->user());

            return response()->json([
                'success' => true,
                'data' => BackingResource::collection($backings)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
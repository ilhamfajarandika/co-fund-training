<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backing\StoreBackingRequest;
use App\Services\BackingService;
use Exception;
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
                'data' => $backing
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
}
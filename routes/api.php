<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackingController;
use App\Http\Controllers\Api\CampaignController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Models\Campaign;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

    Route::post('/');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        Route::post('/email/verification-notification', function (Request $request) {
            $request->user()->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Email verifikasi telah dikirim.'
            ]);
        })->middleware('throttle:6,1');

        Route::apiResource('campaigns', CampaignController::class);

        Route::post(
            'campaigns/{campaign}/updates',
            [CampaignController::class, 'storeUpdate']
        );

        Route::post(
            'campaigns/{campaign}/approve',
            [CampaignController::class, 'approve']
        );

        Route::post(
            'campaigns/{campaign}/reject',
            [CampaignController::class, 'reject']
        );

        Route::post(
            'campaigns/{campaign}/tiers',
            [CampaignController::class, 'storeTier']
        );

        Route::put(
            'campaigns/{campaign}/tiers/{tier}',
            [CampaignController::class, 'updateTier']
        );

        Route::delete(
            'campaigns/{campaign}/tiers/{tier}',
            [CampaignController::class, 'destroyTier']
        );

        Route::post(
            'campaigns/{id}/back',
            [BackingController::class, 'store']
        );

        Route::get('/health', function () {
            return response()->json([
                'success' => true,
                'message' => 'API is healthy',
                'timestamp' => now()
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Future Routes: Campaign & Backing (Require Email Verified)
    |--------------------------------------------------------------------------
    | Gunakan middleware ['auth:sanctum', 'verified'] untuk route yang
    | hanya bisa diakses user yang sudah login DAN email terverifikasi.
    |
    | Contoh:
    | Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    |     // Creator only - buat/edit/hapus campaign
    |     Route::post('/campaigns', [CampaignController::class, 'store']);
    |     Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    |     Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
    |
    |     // Backer only - backing campaign
    |     Route::post('/campaigns/{id}/back', [BackingController::class, 'store']);
    |     Route::get('/my-backings', [BackingController::class, 'index']);
    | });
    |
    | Middleware 'verified' = \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class
    | Return 403 jika user belum verifikasi email.
    */

    Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {

        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Link verifikasi tidak valid.'
            ], 403);
        }

        if (! $request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Link verifikasi sudah tidak valid.'
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi.'
        ]);
    })->name('verification.verify');
});

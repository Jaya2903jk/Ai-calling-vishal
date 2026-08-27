<?php

use App\Http\Controllers\API\AIAgentController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CallController;
use App\Http\Controllers\API\CampaignController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\KnowledgeBaseController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\TwilioWebhookController;
use App\Http\Controllers\API\WebhookController;
use Illuminate\Support\Facades\Route;

// Public auth & webhooks
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/webhooks/call', [WebhookController::class, 'handle']);

// Twilio Voice, Real Speech Gather & Real Recording Webhooks
Route::match(['get', 'post'], '/webhooks/twilio-voice', [TwilioWebhookController::class, 'handleVoice']);
Route::match(['get', 'post'], '/webhooks/twilio-gather', [TwilioWebhookController::class, 'handleGather']);
Route::post('/webhooks/twilio-recording', [TwilioWebhookController::class, 'handleRecording']);

// Authenticated or public API endpoints for seamless web app SPA usage
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// SaaS platform resources
Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);
Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

Route::apiResource('customers', CustomerController::class);
Route::post('/customers/{id}/notes', [CustomerController::class, 'addNote']);
Route::post('/customers/{id}/ai-call', [CustomerController::class, 'startAICall']);

Route::get('/calls', [CallController::class, 'index']);
Route::post('/calls/trigger', [CallController::class, 'triggerCall']);
Route::post('/calls/start-interactive', [CallController::class, 'startInteractive']);
Route::post('/calls/interactive-turn', [CallController::class, 'interactiveTurn']);
Route::post('/calls/{id}/finalize', [CallController::class, 'finalizeCall']);
Route::post('/calls/simulate-step', [CallController::class, 'simulateTurn']);
Route::get('/calls/{id}', [CallController::class, 'show']);
Route::post('/calls/{id}/retry', [CallController::class, 'retryCall']);
Route::post('/calls/{id}/outcome', [CallController::class, 'updateOutcome']);

Route::apiResource('ai-agents', AIAgentController::class);

Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
Route::apiResource('appointments', AppointmentController::class);

Route::get('/campaigns', [CampaignController::class, 'index']);
Route::post('/campaigns', [CampaignController::class, 'store']);
Route::post('/campaigns/{id}/start', [CampaignController::class, 'start']);
Route::post('/campaigns/{id}/pause', [CampaignController::class, 'pause']);
Route::post('/campaigns/{id}/stop', [CampaignController::class, 'stop']);

Route::get('/employees/roles', [EmployeeController::class, 'roles']);
Route::apiResource('employees', EmployeeController::class);

Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index']);
Route::post('/knowledge-base/item', [KnowledgeBaseController::class, 'storeKnowledge']);
Route::post('/knowledge-base/faq', [KnowledgeBaseController::class, 'storeFaq']);
Route::delete('/knowledge-base/item/{id}', [KnowledgeBaseController::class, 'destroyKnowledge']);
Route::delete('/knowledge-base/faq/{id}', [KnowledgeBaseController::class, 'destroyFaq']);

Route::get('/analytics/calls', [AnalyticsController::class, 'callAnalytics']);
Route::get('/analytics/sales', [AnalyticsController::class, 'salesAnalytics']);
Route::get('/analytics/ai', [AnalyticsController::class, 'aiAnalytics']);

Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

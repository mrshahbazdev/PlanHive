<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => Inertia::render('Welcome'));
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dashboard (Calendar Homepage)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Project Members
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::put('/projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Goals
    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::post('/projects/{project}/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');

    // Calendar Events (JSON API for FullCalendar)
    Route::get('/api/calendar-events', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::post('/api/calendar-events', [CalendarEventController::class, 'store'])->name('calendar.store');
    Route::put('/api/calendar-events/{calendarEvent}', [CalendarEventController::class, 'update'])->name('calendar.update');
    Route::delete('/api/calendar-events/{calendarEvent}', [CalendarEventController::class, 'destroy'])->name('calendar.destroy');

    // Notes
    Route::resource('notes', NoteController::class);

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Contacts
    Route::get('/contacts/scan', [ContactController::class, 'scan'])->name('contacts.scan');
    Route::resource('contacts', ContactController::class);

    // Reminders
    Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders', [ReminderController::class, 'store'])->name('reminders.store');
    Route::delete('/reminders/{reminder}', [ReminderController::class, 'destroy'])->name('reminders.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Notification API (for real-time unread count)
    Route::get('/api/notifications/unread-count', function (\Illuminate\Http\Request $request) {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    })->name('notifications.unreadCount');

    // Integrations (Outlook / Teams)
    Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
    Route::post('/integrations/microsoft-app', [IntegrationController::class, 'saveMicrosoftAppCredentials'])->name('integrations.microsoft.save');
    Route::get('/integrations/outlook/connect', [IntegrationController::class, 'connectOutlook'])->name('integrations.outlook.connect');
    Route::get('/integrations/outlook/callback', [IntegrationController::class, 'outlookCallback'])->name('integrations.outlook.callback');
    Route::post('/integrations/outlook/sync', [IntegrationController::class, 'syncCalendar'])->name('integrations.outlook.sync');
    Route::post('/integrations/teams', [IntegrationController::class, 'connectTeams'])->name('integrations.teams.connect');
    Route::post('/integrations/teams/test', [IntegrationController::class, 'testTeamsWebhook'])->name('integrations.teams.test');
    Route::delete('/integrations/{provider}', [IntegrationController::class, 'disconnect'])->name('integrations.disconnect');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // API Token Management
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('users.toggleAdmin');
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

// ─── Públicas ─────────────────────────────────────────────────────────────────

Route::get('/', fn() => redirect('/dashboard'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ─── OAuth (públicas) ─────────────────────────────────────────────────────────
Route::get('/auth/{provider}/redirect', [AuthController::class, 'oauthRedirect'])->name('oauth.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'oauthCallback'])->name('oauth.callback');

// ─── Login 2FA (pública — sin sesión completa todavía) ────────────────────────
Route::get('/login/2fa', [AuthController::class, 'show2fa'])->name('login.2fa');
Route::post('/login/2fa', [AuthController::class, 'login2fa']);

// ─── Dentro del grupo auth.session ───────────────────────────────────────────


// ─── Protegidas (requieren sesión) ────────────────────────────────────────────

Route::middleware('auth.session')->group(function () {

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Student Portal
    Route::get('/profile', [StudentController::class, 'profile'])->name('student.profile');
    Route::get('/schedule', [StudentController::class, 'schedule'])->name('student.schedule');
    Route::get('/subjects', [StudentController::class, 'subjects'])->name('student.subjects');

    // Library — catálogo y préstamos
    Route::get('/library/books', [LibraryController::class, 'books'])->name('library.books');
    Route::get('/library/books/{id}', [LibraryController::class, 'book'])->name('library.book');
    Route::post('/library/loans/{id}', [LibraryController::class, 'requestLoan'])->name('library.loan.request');
    Route::get('/library/loans', [LibraryController::class, 'loans'])->name('library.loans');
    Route::post('/library/loans/{id}/renew', [LibraryController::class, 'renewLoan'])->name('library.loan.renew');
    Route::post('/library/loans/{id}/return', [LibraryController::class, 'returnBook'])->name('library.loan.return');

    // Library — multas
    Route::get('/library/fines', [LibraryController::class, 'fines'])->name('library.fines');
    Route::post('/library/fines/{id}/pay', [LibraryController::class, 'payFine'])->name('library.fine.pay');

    Route::get('/library/inventory', [LibraryController::class, 'inventory'])->name('library.inventory');
    Route::post('/library/books', [LibraryController::class, 'createBook'])->name('library.inventory.create');
    Route::patch('/library/books/{id}', [LibraryController::class, 'updateBook'])->name('library.inventory.update');
    Route::patch('/library/books/{id}/stock', [LibraryController::class, 'updateStock'])->name('library.inventory.stock');


    // Support — estudiante
    Route::get('/support/tickets', [SupportController::class, 'myTickets'])->name('support.my-tickets');
    Route::get('/support/tickets/new', [SupportController::class, 'createForm'])->name('support.create');
    Route::post('/support/tickets', [SupportController::class, 'createTicket'])->name('support.create.ticket');

    // Support — agente
    Route::get('/support/all-tickets', [SupportController::class, 'allTickets'])->name('support.all-tickets');

    // Support — detalle y acciones (compartido)
    Route::get('/support/tickets/{id}', [SupportController::class, 'ticket'])->name('support.ticket');
    Route::post('/support/tickets/{id}/comments', [SupportController::class, 'comment'])->name('support.comment');
    Route::patch('/support/tickets/{id}/assign', [SupportController::class, 'assign'])->name('support.assign');
    Route::patch('/support/tickets/{id}/status', [SupportController::class, 'updateStatus'])->name('support.status');
    Route::patch('/support/tickets/{id}/reopen', [SupportController::class, 'reopen'])->name('support.reopen');
    Route::get('/support/tickets/{id}/security-context', [SupportController::class, 'securityContext'])->name('support.security-context');


    // Seguridad (todos los usuarios autenticados)
    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::delete('/security/sessions/{id}', [SecurityController::class, 'deleteSession'])->name('security.session.delete');
    Route::post('/security/2fa/enable', [SecurityController::class, 'enable2fa'])->name('security.2fa.enable');
    Route::get('/security/2fa/enable', [SecurityController::class, 'enable2fa'])->name('security.2fa.setup');
    Route::post('/security/2fa/verify', [SecurityController::class, 'verify2fa'])->name('security.2fa.verify');
    Route::post('/security/2fa/disable', [SecurityController::class, 'disable2fa'])->name('security.2fa.disable');

    // Admin
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/roles/assign', [AdminController::class, 'assignRole'])->name('admin.roles.assign');
    Route::delete('/admin/users/{userId}/roles/{role}', [AdminController::class, 'revokeRole'])->name('admin.roles.revoke');
    Route::get('/admin/security-status', [AdminController::class, 'userSecurityStatus'])->name('admin.security-status');
    Route::get('/admin/audit', [AdminController::class, 'audit'])->name('admin.audit');
});

Route::get('/health', function () {
    $writable = is_writable(storage_path('framework/sessions'));

    return response()->json([
        'status'  => $writable ? 'ok' : 'degraded',
        'service' => config('app.name'),
        'storage' => $writable ? 'writable' : 'unwritable',
    ], $writable ? 200 : 503);
});
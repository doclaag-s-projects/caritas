<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ViewController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/search', [FileController::class, 'searchByName'])->name('search');
Route::get('/public-files', [FileController::class, 'listPublicFiles'])->name('public.files');
Route::get('/categories-with-files', [FileController::class, 'listCategoriesWithFiles'])->name('categories.with.files');
Route::get('/categories-with-files-private', [FileController::class, 'listCategoriesWithFilesPrivate'])->name('categories.with.files.private');
Route::get('/files/{id}/preview', [FileController::class, 'preview'])->name('files.preview');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Ruta para subir archivos
Route::middleware(['auth'])->group(function () {
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::delete('/files/{id}', [FileController::class, 'delete'])->name('files.delete');
    Route::put('/files/{id}/rename', [FileController::class, 'rename'])->name('files.rename');
    Route::get('/files/list', [FileController::class, 'list'])->name('list');
    Route::get('/files/upload', function () {return Inertia::render('Files/Upload');})->name('files');
});

// Rutas para obtener las categorías.
Route::middleware(['auth'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'getCategorias'])->name('categories');
    Route::get('/categories/{categoriaPadreId}/subcategories', [CategoryController::class, 'getSubcategorias']);
    Route::get('/categorias/recursivas', [CategoryController::class, 'obtenerCategoriasRecursivas']);
    Route::get('/categorias/principales', [CategoryController::class, 'getCategoriasPrincipales']);
    Route::post('/categorias/crear', [CategoryController::class, 'CrearCategoriaAjax']);
    Route::post('/subcategorias/crear', [CategoryController::class, 'CrearSubCategoriaAjax']);
    Route::put('/categorias/{id}', [CategoryController::class, 'updateCategoria']);
    Route::put('/subcategorias/{id}', [CategoryController::class, 'updateSubcategoria']);
    Route::get('/categorias/{id}', [CategoryController::class, 'getCategoriaById']);
    Route::get('/subcategorias/{id}', [CategoryController::class, 'getSubcategoriaById']);
    Route::delete('/categorias/{id}', [CategoryController::class, 'destroy']);
    Route::get('/tags', [TagController::class, 'index']);
});

// Ruta para crear, actualizar y eliminar categorías.
Route::middleware(['auth'])->group(function () {
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::put('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
    Route::put('/tags/{tag}/estado', [TagController::class, 'cambiarEstado']);
});

// Ruta para manejo usuario. 
Route::middleware(['auth'])->group(function () {
    Route::get('/users', function () {
        return Inertia::render('Users/AllUsers');
    })->name('all.users');
});

// Rutas para roles
Route::middleware(['auth'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
});

// Rutas para permisos
Route::middleware(['auth'])->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
});

// Ruta para vistas
Route::get('/views', [ViewController::class, 'Index'])->name('views.index');
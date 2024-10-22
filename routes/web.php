<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// RUTA ARCHIVOS
Route::get('/files/list', [FileController::class, 'list'])->name('list');
Route::get('/files/upload', function () {
    return Inertia::render('Files/Upload');
})->name('files');

// Ruta para subir archivos
Route::middleware(['auth'])->group(function () {
    Route::post('/files/upload', [FileController::class, 'upload']);
});

// Ruta para obtener las categorías.
Route::middleware(['auth'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'getCategorias'])->name('categories');
    Route::get('/categories/{categoriaPadreId}/subcategories', [CategoryController::class, 'getSubcategorias']);
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


// Listar todos los usuarios
Route::get('/', [UserController::class, 'AllUsers'])->name('all.users');

// Mostrar el formulario para agregar un nuevo usuario
Route::get('/add', [UserController::class, 'AddUser'])->name('add.user');

// Almacenar un nuevo usuario
Route::post('/store', [UserController::class, 'StoreUser'])->name('store.user');

// Mostrar el formulario para editar un usuario
Route::get('/edit/{id}', [UserController::class, 'EditUser'])->name('edit.user');

// Actualizar un usuario existente
Route::put('/update/{id}', [UserController::class, 'UpdateUser'])->name('update.user');

// Eliminar un usuario
Route::delete('/delete/{id}', [UserController::class, 'DeleteUser'])->name('delete.user');

// Rutas para roles
// Ruta para obtener todos los roles y permisos
Route::get('/', [RoleController::class, 'allRolesAndPermissions'])->name('all.roles.permissions');

// Ruta para crear un nuevo rol
Route::post('/create', [RoleController::class, 'createRole'])->name('create.role');

// Ruta para crear un nuevo permiso
Route::post('/permissions/create', [RoleController::class, 'createPermission'])->name('create.permission');


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

// Ruta para subir archivos
Route::middleware(['auth'])->group(function () {
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::delete('/files/{id}', [FileController::class, 'delete'])->name('files.delete');
    Route::put('/files/{id}/rename', [FileController::class, 'rename'])->name('files.rename');
    Route::get('/files/{id}/preview', [FileController::class, 'preview'])->name('files.preview');
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



/// Rutas protegidas por middleware de autenticación y roles
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function() {
    // Rutas de usuarios
    Route::get('/users', [UserController::class, 'AllUsers'])->name('users.index');  // Listar todos los usuarios
    Route::get('/users/create', [UserController::class, 'AddUser'])->name('users.create');  // Mostrar el formulario para agregar un nuevo usuario
    Route::post('/users', [UserController::class, 'StoreUser'])->name('users.store');  // Almacenar un nuevo usuario
    Route::get('/users/{id}/edit', [UserController::class, 'EditUser'])->name('users.edit');  // Mostrar el formulario para editar un usuario
    Route::put('/users/{id}', [UserController::class, 'UpdateUser'])->name('users.update');  // Actualizar un usuario existente
    Route::delete('/users/{id}', [UserController::class, 'DeleteUser'])->name('users.destroy');  // Eliminar un usuario

    // Rutas de roles y permisos
    Route::get('/roles-permissions', [RoleController::class, 'allRolesAndPermissions'])->name('roles-permissions.index');  // Obtener todos los roles y permisos
    Route::post('/roles', [RoleController::class, 'createRole'])->name('roles.store');  // Crear un nuevo rol
    Route::post('/permissions', [RoleController::class, 'createPermission'])->name('permissions.store');  // Crear un nuevo permiso
});


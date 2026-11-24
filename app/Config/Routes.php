<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public Routes
$routes->get('/', 'AuthController::loginForm');
$routes->get('/login', 'AuthController::loginForm');
$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

// Protected Routes (all logged in)
$routes->group('', ['filter' => 'auth'], function ($routes) {

    $routes->get('/dashboard', 'DashboardController::index');

    // ── Employee Routes ──────────────────────────────────────────────
    $routes->get('/my-requests', 'RequestController::myRequests');
    $routes->get('/requests/create', 'RequestController::create');
    $routes->post('/requests/store', 'RequestController::store');
    $routes->get('/requests/(:num)', 'RequestController::show/$1');
    $routes->post('/requests/(:num)/comment', 'RequestController::addComment/$1');

    // ── Agent Routes ─────────────────────────────────────────────────
    $routes->group('agent', ['filter' => 'role:agent,admin'], function ($routes) {
        $routes->get('requests', 'RequestController::agentRequests');
    });
    $routes->post('/requests/(:num)/status', 'RequestController::updateStatus/$1', ['filter' => 'role:agent,admin']);

    // ── Admin Routes ──────────────────────────────────────────────────
    $routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('dashboard', 'AdminController::dashboard');

        // All Requests
        $routes->get('requests', 'RequestController::adminRequests');
        $routes->post('requests/(:num)/reassign', 'RequestController::reassign/$1');

        // Users
        $routes->get('users', 'AdminController::users');
        $routes->get('users/create', 'AdminController::createUserForm');
        $routes->post('users/store', 'AdminController::storeUser');
        $routes->get('users/(:num)/edit', 'AdminController::editUserForm/$1');
        $routes->post('users/(:num)/update', 'AdminController::updateUser/$1');
        $routes->post('users/(:num)/delete', 'AdminController::deleteUser/$1');

        // Categories
        $routes->get('categories', 'AdminController::categories');
        $routes->get('categories/create', 'AdminController::createCategoryForm');
        $routes->post('categories/store', 'AdminController::storeCategory');
        $routes->get('categories/(:num)/edit', 'AdminController::editCategoryForm/$1');
        $routes->post('categories/(:num)/update', 'AdminController::updateCategory/$1');

        // Activity Log
        $routes->get('activity-log', 'AdminController::activityLog');
    });
});

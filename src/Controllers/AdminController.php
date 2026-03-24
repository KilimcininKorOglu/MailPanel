<?php

declare(strict_types=1);

namespace App\Controllers;

use App\CsrfProtection;
use App\Middleware;
use App\Models\Admin;
use App\Models\UserPassword;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;
use App\Utils\PasswordUtils;

class AdminController
{
    /**
     * Displays the admin list page.
     */
    public static function adminList(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();

        $admins = RepositoryFactory::getAdminRepository()->getAdmins();

        $tpl->render('adminList.php', [
            'admins' => $admins,
        ]);
    }

    /**
     * Displays the admin creation form and handles creation.
     */
    public static function adminCreate(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();

        $error = null;
        $validationErrors = [];
        $admin = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $admin = Admin::fromFormData($_POST);
                $password = $_POST['password'] ?? '';
                $passwordRepeat = $_POST['password_repeat'] ?? '';

                // Validate email
                if (empty($admin->username) || !filter_var($admin->username, FILTER_VALIDATE_EMAIL)) {
                    $validationErrors['username'] = 'A valid email address is required';
                }

                // Validate password
                $validationErrors = array_merge($validationErrors, UserPassword::validate($password, $passwordRepeat));

                if (empty($validationErrors)) {
                    $repo = RepositoryFactory::getAdminRepository();

                    // Check for duplicate
                    if ($repo->getAdmin($admin->username) !== null) {
                        $validationErrors['username'] = "Admin '{$admin->username}' already exists";
                    } else {
                        $passwordHash = PasswordUtils::generatePasswordHash($password);
                        $repo->createAdmin($admin, $passwordHash);
                        header("Location: /admins");
                        exit;
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $tpl->render('adminCreate.php', [
            'admin' => $admin,
            'error' => $error,
            'validationErrors' => $validationErrors,
        ]);
    }

    /**
     * Displays and handles admin profile editing.
     */
    public static function adminView(TemplateEngine $tpl, string $adminEmail, string $editMode): void
    {
        Middleware::globalAdminRequired();

        if (!in_array($editMode, ['general', 'password', 'domains'], true)) {
            http_response_code(404);
            $tpl->render('page404.php');
            return;
        }

        $adminRepo = RepositoryFactory::getAdminRepository();
        $error = null;
        $validationErrors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($editMode === 'general') {
                    $formAdmin = Admin::fromFormData($_POST);
                    $admin = new Admin(
                        username: $adminEmail,
                        name: $formAdmin->name,
                        active: $formAdmin->active,
                        isGlobalAdmin: $formAdmin->isGlobalAdmin,
                        isMailboxAdmin: $adminRepo->getAdmin($adminEmail)?->isMailboxAdmin ?? false,
                    );
                    $adminRepo->updateAdmin($admin);
                    $success = 'Admin updated successfully!';
                } elseif ($editMode === 'password') {
                    $password = $_POST['password'] ?? '';
                    $passwordRepeat = $_POST['password_repeat'] ?? '';
                    $validationErrors = UserPassword::validate($password, $passwordRepeat);

                    if (empty($validationErrors)) {
                        $passwordHash = PasswordUtils::generatePasswordHash($password);
                        $adminRepo->updateAdminPassword($adminEmail, $passwordHash);
                        $success = 'Password updated successfully!';
                    }
                } elseif ($editMode === 'domains') {
                    $action = $_POST['action'] ?? '';
                    $domain = $_POST['domain'] ?? '';

                    if ($action === 'assign' && !empty($domain)) {
                        $adminRepo->assignDomainToAdmin($adminEmail, $domain);
                        $success = "Domain '{$domain}' assigned!";
                    } elseif ($action === 'revoke' && !empty($domain)) {
                        $adminRepo->revokeDomainFromAdmin($adminEmail, $domain);
                        $success = "Domain '{$domain}' revoked!";
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $admin = $adminRepo->getAdmin($adminEmail);
        if ($admin === null) {
            http_response_code(404);
            $tpl->render('page404.php');
            return;
        }

        $managedDomains = $adminRepo->getManagedDomains($adminEmail);
        $allDomains = RepositoryFactory::getDomainRepository()->getDomains();
        $allDomainNames = array_map(fn($d) => $d['domainName'], $allDomains);

        $tpl->render('adminView.php', [
            'admin' => $admin,
            'editMode' => $editMode,
            'managedDomains' => $managedDomains,
            'allDomainNames' => $allDomainNames,
            'error' => $error,
            'validationErrors' => $validationErrors,
            'success' => $success,
        ]);
    }

    /**
     * Handles admin deletion (POST only).
     */
    public static function adminDelete(TemplateEngine $tpl, string $adminEmail): void
    {
        Middleware::globalAdminRequired();
        CsrfProtection::validateToken();

        try {
            RepositoryFactory::getAdminRepository()->deleteAdmin($adminEmail);
            header("Location: /admins");
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            $tpl->render('page404.php');
        }
    }
}

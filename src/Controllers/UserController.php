<?php

declare(strict_types=1);

namespace App\Controllers;

use App\CsrfProtection;
use App\Middleware;
use App\Models\Settings;
use App\Models\User;
use App\Models\UserPassword;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;
use App\Utils\PasswordUtils;

class UserController
{
    /**
     * Displays the user list page.
     */
    public static function userList(TemplateEngine $tpl, string $domain): void
    {
        Middleware::loginRequired();

        $settings = Settings::getInstance();
        $userRepo = RepositoryFactory::getUserRepository();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $settings->paginationPerPage;
        $startsWith = $_GET['letter'] ?? null;
        $sortBy = $_GET['sort'] ?? 'uid';
        $sortDir = $_GET['dir'] ?? 'asc';

        $paginatedResult = $userRepo->getUsersPaginated($domain, $page, $perPage, $startsWith, null, $sortBy, $sortDir);

        $tpl->render('userList.php', [
            'domain' => $domain,
            'users' => $paginatedResult->items,
            'paginatedResult' => $paginatedResult,
            'supportsCreate' => $userRepo->supportsCreateUser(),
            'currentLetter' => $startsWith,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    /**
     * Displays the user detail/edit page.
     */
    public static function userView(TemplateEngine $tpl, string $domain, string $userUid, string $editMode): void
    {
        Middleware::loginRequired();

        if (!in_array($editMode, ['general', 'password'], true)) {
            http_response_code(404);
            $tpl->render('page404.php');
            return;
        }

        $userRepo = RepositoryFactory::getUserRepository();
        $error = null;
        $validationErrors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($editMode === 'general') {
                    $user = User::fromFormData($_POST);
                    $user->uid = $userUid;
                    $userRepo->updateUser($domain, $user);
                    $success = 'Information updated successfully!';
                } elseif ($editMode === 'password') {
                    $password = $_POST['password'] ?? '';
                    $passwordRepeat = $_POST['password_repeat'] ?? '';
                    $validationErrors = UserPassword::validate($password, $passwordRepeat);

                    if (empty($validationErrors)) {
                        $passwordHash = PasswordUtils::generatePasswordHash($password);
                        $userRepo->updateUserPassword($domain, $userUid, $passwordHash);
                        $success = 'Password updated successfully!';
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $user = $userRepo->getUser($domain, $userUid);
        if ($user === null) {
            http_response_code(404);
            $tpl->render('page404.php');
            return;
        }

        $tpl->render('userView.php', [
            'domain' => $domain,
            'user' => $user,
            'error' => $error,
            'validationErrors' => $validationErrors,
            'success' => $success,
            'editMode' => $editMode,
        ]);
    }

    /**
     * Handles user deletion (POST only).
     */
    public static function userDelete(TemplateEngine $tpl, string $domain, string $userUid): void
    {
        Middleware::loginRequired();
        CsrfProtection::validateToken();

        try {
            $adminEmail = $_SESSION['email'] ?? '';
            RepositoryFactory::getUserRepository()->deleteUser($domain, $userUid, $adminEmail);
            header("Location: /{$domain}/users");
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            $tpl->render('page404.php');
        }
    }

    /**
     * Displays the user creation page.
     */
    public static function userCreateView(TemplateEngine $tpl, string $domain): void
    {
        Middleware::loginRequired();

        $userRepo = RepositoryFactory::getUserRepository();
        $validationErrors = [];
        $error = null;
        $user = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userUid = $_POST['uid'] ?? '';
            $user = $userRepo->getUser($domain, $userUid);

            if ($user !== null) {
                $validationErrors['uid'] = "User with identifier {$userUid} already exists";
            } else {
                try {
                    $user = User::fromFormData($_POST);
                    $password = $_POST['password'] ?? '';
                    $passwordRepeat = $_POST['password_repeat'] ?? '';
                    $validationErrors = UserPassword::validate($password, $passwordRepeat);

                    if (empty($validationErrors)) {
                        $passwordHash = PasswordUtils::generatePasswordHash($password);
                        $userRepo->createUser($domain, $user, $passwordHash);
                        header("Location: /{$domain}/users");
                        exit;
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $tpl->render('userCreate.php', [
            'domain' => $domain,
            'validationErrors' => $validationErrors,
            'error' => $error,
            'user' => $user,
        ]);
    }
}

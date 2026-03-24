<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
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

        $userRepo = RepositoryFactory::getUserRepository();
        $users = $userRepo->getUsers($domain);
        usort($users, fn(User $a, User $b) => strcmp($a->uid, $b->uid));

        $tpl->render('userList.php', [
            'domain' => $domain,
            'users' => $users,
        ]);
    }

    /**
     * Displays the user detail/edit page.
     */
    public static function userView(TemplateEngine $tpl, string $domain, string $userUid, string $editMode): void
    {
        Middleware::loginRequired();

        $userRepo = RepositoryFactory::getUserRepository();
        $error = null;
        $validationErrors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($editMode === 'general') {
                    $user = User::fromFormData($_POST);
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
     * Displays the user creation page.
     */
    public static function userCreateView(TemplateEngine $tpl, string $domain): void
    {
        Middleware::loginRequired();

        $userRepo = RepositoryFactory::getUserRepository();
        $validationErrors = [];
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
                } catch (\Exception $e) {
                    $validationErrors['uid'] = $e->getMessage();
                }
            }
        }

        $tpl->render('userCreate.php', [
            'domain' => $domain,
            'validationErrors' => $validationErrors,
            'user' => $user,
        ]);
    }
}

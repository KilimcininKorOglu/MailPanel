<?php

declare(strict_types=1);

namespace App\Controllers;

use App\CsrfProtection;
use App\Middleware;
use App\Models\Settings;
use App\Models\User;
use App\Models\UserPassword;
use App\Repositories\RepositoryFactory;
use App\Services\ActivityLogger;
use App\TemplateEngine;
use App\Utils\PasswordUtils;

class UserController
{
    /**
     * Displays the user list page.
     */
    public static function userList(TemplateEngine $tpl, string $domain): void
    {
        Middleware::domainAdminRequired($domain);

        $settings = Settings::getInstance();
        $userRepo = RepositoryFactory::getUserRepository();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $settings->paginationPerPage;
        $startsWith = $_GET['letter'] ?? null;
        $sortBy = $_GET['sort'] ?? 'uid';
        $sortDir = $_GET['dir'] ?? 'asc';
        $statusFilter = $_GET['status'] ?? null;
        $activeOnly = match ($statusFilter) {
            'active' => true,
            'disabled' => false,
            default => null,
        };

        $paginatedResult = $userRepo->getUsersPaginated($domain, $page, $perPage, $startsWith, $activeOnly, $sortBy, $sortDir);
        $usedQuotas = RepositoryFactory::getQuotaRepository()->getDomainUsedQuotas($domain);

        $tpl->render('userList.php', [
            'domain' => $domain,
            'users' => $paginatedResult->items,
            'paginatedResult' => $paginatedResult,
            'supportsCreate' => $userRepo->supportsCreateUser(),
            'usedQuotas' => $usedQuotas,
            'currentLetter' => $startsWith,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Displays the user detail/edit page.
     */
    public static function userView(TemplateEngine $tpl, string $domain, string $userUid, string $editMode): void
    {
        Middleware::domainAdminRequired($domain);

        if (!in_array($editMode, ['general', 'password', 'services', 'forwarding', 'aliases', 'bcc', 'relay'], true)) {
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
                    $existingUser = $userRepo->getUser($domain, $userUid);
                    $user = User::fromFormData($_POST);
                    $user->uid = $userUid;

                    // Prevent privilege escalation: only global admins can change domainGlobalAdmin
                    if (!Middleware::isGlobalAdmin()) {
                        $user->domainGlobalAdmin = $existingUser ? $existingUser->domainGlobalAdmin : false;
                    }

                    $userRepo->updateUser($domain, $user);
                    ActivityLogger::logUpdate($domain, $userUid, "User profile updated");
                    $success = 'Information updated successfully!';
                } elseif ($editMode === 'password') {
                    // Old password verification if enabled
                    $settings = Settings::getInstance();
                    if ($settings->requireOldPasswordOnChange) {
                        $oldPassword = $_POST['old_password'] ?? '';
                        if (!$userRepo->verifyUserPassword($domain, $userUid, $oldPassword)) {
                            $validationErrors['old_password'] = 'Current password is incorrect';
                        }
                    }

                    if (empty($validationErrors)) {
                        $password = $_POST['password'] ?? '';
                        $passwordRepeat = $_POST['password_repeat'] ?? '';
                        $validationErrors = UserPassword::validate($password, $passwordRepeat);

                        if (empty($validationErrors)) {
                            $passwordHash = PasswordUtils::generatePasswordHash($password);
                            $userRepo->updateUserPassword($domain, $userUid, $passwordHash);
                            ActivityLogger::logUpdate($domain, $userUid, "Password changed");
                            $success = 'Password updated successfully!';
                        }
                    }
                } elseif ($editMode === 'services') {
                    $currentUser = $userRepo->getUser($domain, $userUid);
                    if ($currentUser !== null) {
                        $currentUser->enableSmtp = isset($_POST['enableSmtp']);
                        $currentUser->enableSmtpSecured = isset($_POST['enableSmtpSecured']);
                        $currentUser->enablePop3 = isset($_POST['enablePop3']);
                        $currentUser->enablePop3Secured = isset($_POST['enablePop3Secured']);
                        $currentUser->enableImap = isset($_POST['enableImap']);
                        $currentUser->enableImapSecured = isset($_POST['enableImapSecured']);
                        $currentUser->enableManagesieve = isset($_POST['enableManagesieve']);
                        $currentUser->enableManagesieveSecured = isset($_POST['enableManagesieveSecured']);
                        $currentUser->enableSogo = isset($_POST['enableSogo']);
                        $userRepo->updateUser($domain, $currentUser);
                        ActivityLogger::logUpdate($domain, $userUid, "Mail services updated");
                        $success = 'Mail services updated successfully!';
                    }
                } elseif ($editMode === 'forwarding') {
                    $email = "{$userUid}@{$domain}";
                    $forwardingRepo = RepositoryFactory::getForwardingRepository();
                    $addressesRaw = $_POST['forwardingAddresses'] ?? '';
                    $addresses = array_filter(array_map('trim', explode("\n", $addressesRaw)));
                    $keepCopy = isset($_POST['keepCopy']);

                    $forwardingRepo->setForwardings($email, $domain, $addresses);
                    $forwardingRepo->setKeepCopy($email, $domain, $keepCopy);
                    ActivityLogger::logUpdate($domain, $userUid, "Forwarding settings updated");
                    $success = 'Forwarding settings updated successfully!';
                } elseif ($editMode === 'aliases') {
                    CsrfProtection::validateToken();
                    $email = "{$userUid}@{$domain}";
                    $aliasRepo = RepositoryFactory::getAliasRepository();
                    $action = $_POST['action'] ?? '';

                    if ($action === 'add') {
                        $newAlias = trim($_POST['newAlias'] ?? '');
                        if ($newAlias !== '') {
                            $aliasRepo->addUserAlias($email, $newAlias);
                            ActivityLogger::logUpdate($domain, $userUid, "Added alias: {$newAlias}");
                        }
                    } elseif ($action === 'remove') {
                        $aliasToRemove = $_POST['aliasAddress'] ?? '';
                        if ($aliasToRemove !== '') {
                            $aliasRepo->removeUserAlias($email, $aliasToRemove);
                            ActivityLogger::logUpdate($domain, $userUid, "Removed alias: {$aliasToRemove}");
                        }
                    }
                    $success = 'Alias addresses updated successfully!';
                } elseif ($editMode === 'bcc') {
                    CsrfProtection::validateToken();
                    $email = "{$userUid}@{$domain}";
                    $bccRepo = RepositoryFactory::getBccRepository();
                    $senderBcc = trim($_POST['senderBcc'] ?? '');
                    $recipientBcc = trim($_POST['recipientBcc'] ?? '');
                    $bccRepo->setUserSenderBcc($email, $senderBcc !== '' ? $senderBcc : null);
                    $bccRepo->setUserRecipientBcc($email, $recipientBcc !== '' ? $recipientBcc : null);
                    ActivityLogger::logUpdate($domain, $userUid, "BCC settings updated");
                    $success = 'BCC settings updated successfully!';
                } elseif ($editMode === 'relay') {
                    CsrfProtection::validateToken();
                    $email = "{$userUid}@{$domain}";
                    $relayRepo = RepositoryFactory::getRelayRepository();
                    $relayhost = trim($_POST['relayhost'] ?? '');
                    $relayRepo->setRelayhost($email, $relayhost !== '' ? $relayhost : null);
                    ActivityLogger::logUpdate($domain, $userUid, "Relay settings updated");
                    $success = 'Relay settings updated successfully!';
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

        // Fetch forwarding data if on forwarding tab
        $forwardings = [];
        $keepCopy = true;
        if ($editMode === 'forwarding') {
            $email = "{$userUid}@{$domain}";
            $forwardingRepo = RepositoryFactory::getForwardingRepository();
            $forwardings = $forwardingRepo->getForwardings($email);
            $keepCopy = $forwardingRepo->getKeepCopy($email);
        }

        // Fetch user aliases if on aliases tab
        $userAliases = [];
        if ($editMode === 'aliases') {
            $email = "{$userUid}@{$domain}";
            $userAliases = RepositoryFactory::getAliasRepository()->getUserAliases($email);
        }

        // Fetch BCC data if on bcc tab
        $userSenderBcc = null;
        $userRecipientBcc = null;
        if ($editMode === 'bcc') {
            $email = "{$userUid}@{$domain}";
            $bccRepo = RepositoryFactory::getBccRepository();
            $userSenderBcc = $bccRepo->getUserSenderBcc($email);
            $userRecipientBcc = $bccRepo->getUserRecipientBcc($email);
        }

        // Fetch relay data if on relay tab
        $userRelayhost = null;
        if ($editMode === 'relay') {
            $email = "{$userUid}@{$domain}";
            $userRelayhost = RepositoryFactory::getRelayRepository()->getRelayhost($email);
        }

        $tpl->render('userView.php', [
            'domain' => $domain,
            'user' => $user,
            'error' => $error,
            'validationErrors' => $validationErrors,
            'success' => $success,
            'editMode' => $editMode,
            'forwardings' => $forwardings,
            'keepCopy' => $keepCopy,
            'userAliases' => $userAliases,
            'userSenderBcc' => $userSenderBcc,
            'userRecipientBcc' => $userRecipientBcc,
            'userRelayhost' => $userRelayhost,
            'requireOldPassword' => Settings::getInstance()->requireOldPasswordOnChange,
        ]);
    }

    /**
     * Handles bulk operations on selected users (POST only).
     */
    public static function renameUser(TemplateEngine $tpl, string $domain, string $userUid): void
    {
        Middleware::globalAdminRequired();
        CsrfProtection::validateToken();

        $newUid = trim($_POST['newUid'] ?? '');
        if ($newUid === '' || $newUid === $userUid) {
            header("Location: /{$domain}/users/{$userUid}/general");
            exit;
        }

        try {
            $userRepo = RepositoryFactory::getUserRepository();
            $userRepo->renameUser($domain, $userUid, $newUid);
            ActivityLogger::logUpdate($domain, $newUid, "Renamed user from {$userUid}@{$domain} to {$newUid}@{$domain}");
            header("Location: /{$domain}/users/{$newUid}/general");
            exit;
        } catch (\Exception $e) {
            error_log("User rename failed: " . $e->getMessage());
            header("Location: /{$domain}/users/{$userUid}/general");
            exit;
        }
    }

    public static function bulkAction(TemplateEngine $tpl, string $domain): void
    {
        Middleware::domainAdminRequired($domain);
        CsrfProtection::validateToken();

        $selectedUsers = $_POST['selectedUsers'] ?? [];
        $action = $_POST['action'] ?? '';
        $adminEmail = $_SESSION['email'] ?? '';

        if (empty($selectedUsers) || !is_array($selectedUsers)) {
            header("Location: /{$domain}/users");
            exit;
        }

        $userRepo = RepositoryFactory::getUserRepository();

        foreach ($selectedUsers as $uid) {
            try {
                if ($action === 'enable' || $action === 'disable') {
                    $user = $userRepo->getUser($domain, $uid);
                    if ($user !== null) {
                        $user->accountStatus = ($action === 'enable');
                        $userRepo->updateUser($domain, $user);
                    }
                } elseif ($action === 'delete') {
                    $userRepo->deleteUser($domain, $uid, $adminEmail);
                }
            } catch (\Exception $e) {
                error_log("Bulk action '{$action}' failed for user '{$uid}@{$domain}': " . $e->getMessage());
            }
        }

        ActivityLogger::log($action, $domain, '', "Bulk {$action} on " . count($selectedUsers) . " users");
        header("Location: /{$domain}/users");
        exit;
    }

    /**
     * Handles user deletion (POST only).
     */
    public static function userDelete(TemplateEngine $tpl, string $domain, string $userUid): void
    {
        Middleware::domainAdminRequired($domain);
        CsrfProtection::validateToken();

        try {
            $adminEmail = $_SESSION['email'] ?? '';
            RepositoryFactory::getUserRepository()->deleteUser($domain, $userUid, $adminEmail);
            ActivityLogger::logDelete($domain, $userUid, "User deleted");
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
        Middleware::domainAdminRequired($domain);

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
                        ActivityLogger::logCreate($domain, $user->uid, "User created");
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

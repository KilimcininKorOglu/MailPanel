<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Models\SpamPolicy;
use App\Repositories\SpamPolicyRepositoryInterface;

class MysqlSpamPolicyRepository implements SpamPolicyRepositoryInterface
{
    public function getPolicy(string $account): ?SpamPolicy
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT p.* FROM policy p
             JOIN users u ON u.policy_id = p.id
             WHERE u.email = :account
             LIMIT 1"
        );
        $stmt->execute(['account' => $account]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return SpamPolicy::fromRow($row);
    }

    public function createOrUpdatePolicy(string $account, SpamPolicy $policy): bool
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT u.id, u.policy_id FROM users u WHERE u.email = :account LIMIT 1");
            $stmt->execute(['account' => $account]);
            $user = $stmt->fetch();

            if ($user !== false && $user['policy_id'] !== null) {
                $this->updatePolicyRow($pdo, (int) $user['policy_id'], $policy);
            } else {
                $policyId = $this->insertPolicyRow($pdo, $policy, $account);

                if ($user !== false) {
                    $pdo->prepare("UPDATE users SET policy_id = :pid WHERE id = :uid")
                        ->execute(['pid' => $policyId, 'uid' => $user['id']]);
                } else {
                    $priority = $this->getPriority($account);
                    $pdo->prepare(
                        "INSERT INTO users (email, priority, policy_id) VALUES (:email, :priority, :pid)"
                    )->execute(['email' => $account, 'priority' => $priority, 'pid' => $policyId]);
                }
            }

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deletePolicy(string $account): bool
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("SELECT u.id, u.policy_id FROM users u WHERE u.email = :account LIMIT 1");
        $stmt->execute(['account' => $account]);
        $user = $stmt->fetch();

        if ($user === false) {
            return true;
        }

        if ($user['policy_id'] !== null) {
            $pdo->prepare("UPDATE users SET policy_id = NULL WHERE id = :uid")
                ->execute(['uid' => $user['id']]);
            $pdo->prepare("DELETE FROM policy WHERE id = :pid")
                ->execute(['pid' => $user['policy_id']]);
        }

        return true;
    }

    public function listPolicies(?string $domain = null): array
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $where = "";
        $params = [];
        if ($domain !== null) {
            $where = "WHERE u.email = :domain OR u.email LIKE :pattern";
            $params = ['domain' => '@' . $domain, 'pattern' => '%@' . $domain];
        }

        $stmt = $pdo->prepare(
            "SELECT u.email, p.* FROM users u
             JOIN policy p ON u.policy_id = p.id
             {$where}
             ORDER BY u.email"
        );
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'account' => $row['email'],
                'policy' => SpamPolicy::fromRow($row),
            ];
        }

        return $results;
    }

    private function insertPolicyRow(\PDO $pdo, SpamPolicy $policy, string $account): int
    {
        $policyName = $policy->policyName !== '' ? $policy->policyName : $account;

        $stmt = $pdo->prepare(
            "INSERT INTO policy (policy_name, spam_tag_level, spam_tag2_level, spam_kill_level,
             spam_subject_tag, spam_subject_tag2, bypass_virus_checks, bypass_spam_checks,
             virus_lover, spam_lover, banned_files_lover, bad_header_lover)
             VALUES (:name, :tag, :tag2, :kill, :subj, :subj2, :bvc, :bsc, :vl, :sl, :bfl, :bhl)"
        );
        $stmt->execute($this->policyParams($policy, $policyName));

        return (int) $pdo->lastInsertId();
    }

    private function updatePolicyRow(\PDO $pdo, int $policyId, SpamPolicy $policy): void
    {
        $pdo->prepare(
            "UPDATE policy SET spam_tag_level = :tag, spam_tag2_level = :tag2, spam_kill_level = :kill,
             spam_subject_tag = :subj, spam_subject_tag2 = :subj2, bypass_virus_checks = :bvc,
             bypass_spam_checks = :bsc, virus_lover = :vl, spam_lover = :sl,
             banned_files_lover = :bfl, bad_header_lover = :bhl
             WHERE id = :id"
        )->execute(array_merge($this->policyUpdateParams($policy), ['id' => $policyId]));
    }

    private function policyParams(SpamPolicy $policy, string $name): array
    {
        return [
            'name' => $name,
            'tag' => $policy->spamTagLevel,
            'tag2' => $policy->spamTag2Level,
            'kill' => $policy->spamKillLevel,
            'subj' => $policy->spamSubjectTag,
            'subj2' => $policy->spamSubjectTag2,
            'bvc' => SpamPolicy::boolToYn($policy->bypassVirusChecks),
            'bsc' => SpamPolicy::boolToYn($policy->bypassSpamChecks),
            'vl' => SpamPolicy::boolToYn($policy->virusLover),
            'sl' => SpamPolicy::boolToYn($policy->spamLover),
            'bfl' => SpamPolicy::boolToYn($policy->bannedFilesLover),
            'bhl' => SpamPolicy::boolToYn($policy->badHeaderLover),
        ];
    }

    private function policyUpdateParams(SpamPolicy $policy): array
    {
        return [
            'tag' => $policy->spamTagLevel,
            'tag2' => $policy->spamTag2Level,
            'kill' => $policy->spamKillLevel,
            'subj' => $policy->spamSubjectTag,
            'subj2' => $policy->spamSubjectTag2,
            'bvc' => SpamPolicy::boolToYn($policy->bypassVirusChecks),
            'bsc' => SpamPolicy::boolToYn($policy->bypassSpamChecks),
            'vl' => SpamPolicy::boolToYn($policy->virusLover),
            'sl' => SpamPolicy::boolToYn($policy->spamLover),
            'bfl' => SpamPolicy::boolToYn($policy->bannedFilesLover),
            'bhl' => SpamPolicy::boolToYn($policy->badHeaderLover),
        ];
    }

    private function getPriority(string $account): int
    {
        if ($account === '@.') {
            return 0;
        }
        if (str_starts_with($account, '@')) {
            return 2;
        }
        return 7;
    }
}

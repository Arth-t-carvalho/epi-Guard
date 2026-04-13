<?php

namespace Facchini\Infrastructure\Auth;

class LdapService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function authenticate(string $username, string $password)
    {
        if (!$this->config['enabled']) return false;
        if ($this->config['mock_mode'] ?? false) return $this->authenticateMock($username, $password);
        return $this->authenticateReal($username, $password);
    }

    public function saveUser(string $username, array $data)
    {
        if (!$this->config['enabled']) return false;
        if ($this->config['mock_mode'] ?? false) return $this->saveUserMock($username, $data);
        return false; 
    }

    public function deleteUser(string $username)
    {
        if (!$this->config['enabled']) return false;
        if ($this->config['mock_mode'] ?? false) return $this->deleteUserMock($username);
        return false;
    }

    private function authenticateReal($username, $password)
    {
        $ldapConn = @ldap_connect($this->config['host'], $this->config['port']);
        if (!$ldapConn) return false;
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
        $bind = @ldap_bind($ldapConn, "{$this->config['domain']}\\$username", $password);
        if ($bind) {
            $filter = "({$this->config['user_lookup_field']}=$username)";
            $search = ldap_search($ldapConn, $this->config['base_dn'], $filter);
            $entries = ldap_get_entries($ldapConn, $search);
            $details = ['username' => $username];
            if ($entries['count'] > 0) {
                $details['name'] = $entries[0]['displayname'][0] ?? $username;
                $details['email'] = $entries[0]['mail'][0] ?? "$username@facchini.local";
                $details['department'] = $entries[0]['department'][0] ?? 'TI';
            }
            ldap_unbind($ldapConn);
            return $details;
        }
        return false;
    }

    private function authenticateMock($username, $password)
    {
        error_log("LDAP Mock: Authenticating via Email or CPF: $username");
        
        $users = $this->getMockUsers();
        foreach ($users as $user) {
            $mockEmailLower = isset($user['email']) ? strtolower($user['email']) : '';
            $mockCpf = isset($user['cpf']) ? (string)$user['cpf'] : '';

            // Verificar se bate apenas com o e-mail ou o CPF
            if (($mockEmailLower === strtolower($username) || $mockCpf === $username) 
                && $user['password'] === $password) {
                error_log("LDAP Mock: Match found for $username");
                return $user;
            }
        }
        error_log("LDAP Mock: No match found for $username (Username login is disabled)");
        return false;
    }



    public function saveUserMock($username, $data)
    {
        $users = $this->getMockUsers();
        $found = false;
        foreach ($users as &$user) {
            if (strtolower($user['username']) === strtolower($username)) {
                $user = array_merge($user, $data);
                $found = true;
                break;
            }
        }
        if (!$found) $users[] = array_merge(['username' => $username, 'password' => 'initial123'], $data);
        return $this->saveMockUsers($users);
    }

    public function deleteUserMock($username)
    {
        $users = $this->getMockUsers();
        $initialCount = count($users);
        $users = array_filter($users, fn($u) => strtolower($u['username']) !== strtolower($username));
        if (count($users) < $initialCount) {
            return $this->saveMockUsers(array_values($users));
        }
        return true;
    }

    public function getMockUsers(): array
    {
        $file = $this->config['mock_storage'];
        if (!file_exists($file)) {
            error_log("LDAP Mock: Storage file not found: $file");
            return [];
        }
        $content = file_get_contents($file);
        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException $e) {
            error_log("LDAP Mock: JSON Decode Error: " . $e->getMessage() . " in $file");
            return [];
        }
    }

    private function saveMockUsers(array $users): bool
    {
        $file = $this->config['mock_storage'];
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        try {
            $content = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            return (bool) file_put_contents($file, $content, LOCK_EX);
        } catch (\JsonException $e) {
            error_log("LDAP Mock: JSON Encode Error: " . $e->getMessage());
            return false;
        }
    }

}


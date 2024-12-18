<?php

namespace App\Service;

use Exception;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;

class LdapService
{
    private Ldap $ldap;

    public function __construct()
    {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => 'ldap.example.com',
            'port' => 389,
            'encryption' => 'none', // 'ssl' si nécessaire
        ]);
    }

    /**
     * @throws Exception
     */
    public function authenticate(string $username, string $password): bool
    {

        $ldap_host = "ldap://iut-larochelle.fr"; // Adresse du serveur LDAP
        $ldap_port = 389; // Port par défaut

        $username = "cpiovesa"; // Remplacer par votre nom d'utilisateur
        $password = 'aC.7$v456abcazerty';


        $dn = "uid=$username,ou=students,dc=iut-larochelle,dc=fr";

        $ldap_conn = ldap_connect($ldap_host, $ldap_port);
        if (!$ldap_conn) {
            throw new Exception("Impossible de se connecter au serveur LDAP.");
        }
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);



        try {
            // Connexion au serveur LDAP
            $this->ldap->bind($dn, $password);

            return true;
        } catch (ConnectionException $e) {
            throw new Exception("Impossible de se connecter au serveur LDAP.");

            return false;
        }
    }
}

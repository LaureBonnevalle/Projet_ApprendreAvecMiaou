
<?php

require_once("models/Users.php");
require_once("services/Functionality.php");
require_once("services/CSRFTokenManager.php");

class UserManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * Créer un utilisateur
     */
    public function createUser(Users $user): void
    {
        $sql = "INSERT INTO users (email, password, firstname, age, avatar, newsletter, createdAt) " .
               "VALUES (:email, :password, :firstname, :age, :avatar, :newsletter, :createdAt)";
        
        $parameters = [
            "email" => $user->getEmail(),
            "password" => $user->getPassword(),
            "firstname" => $user->getFirstname(),
            "age" => $user->getAge(),
            "avatar" => $user->getAvatar(),
            "newsletter" => $user->getNewsletter(),
            "createdAt" => $user->getCreatedAt()
        ];
        
        $this->execute($sql, $parameters);
    }

    /**
     * Lire un utilisateur par son ID
     */
    public function readOneUser(int $userId): ?array 
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $parameters = ['id' => $userId];
        
        return $this->findOne($sql, $parameters);
    }
    
    /**
     * Trouver un utilisateur par son email
     */
    public function findByEmail(string $email): ?array
{
    $sql = "SELECT * FROM users WHERE email = :email";
    $parameters = ['email' => $email];
    
    $result = $this->findOne($sql, $parameters);
    
    // Convertir false en null pour respecter le type de retour
    return $result === false ? null : $result;
}
    
    /**
     * Mettre à jour un utilisateur (méthode à compléter selon vos besoins)
     */
    public function update(Users $user): bool
    {
        $sql = "UPDATE users SET email = :email, password = :password, firstname = :firstname, 
                age = :age, avatar = :avatar, newsletter = :newsletter, role = :role, statut = :statut 
                WHERE id = :id";
        
        $parameters = [
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "password" => $user->getPassword(),
            "firstname" => $user->getFirstname(),
            "age" => $user->getAge(),
            "avatar" => $user->getAvatar(),
            "newsletter" => $user->getNewsletter(),
            "role" => $user->getRole(),
            "statut" => $user->getStatut()
        ];

        try {
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur update user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un utilisateur par son ID
     */
    public function delete(int $id): bool 
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $parameters = ["id" => $id];
        
        try {
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur delete user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un utilisateur par son email
     */
    public function deleteByEmail(string $email): bool 
    {
        $sql = "DELETE FROM users WHERE email = :email";
        $parameters = ["email" => $email];
        
        try {
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur deleteByEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Changer le statut newsletter d'un utilisateur
     */
    public function changeNewsletter(Users $user, int $newsletter): void 
    {
        $sql = "UPDATE users SET newsletter = :newsletter WHERE id = :id";
        $params = [
            'id' => $user->getId(),
            'newsletter' => $newsletter
        ];
        
        $this->execute($sql, $params);
    }
    
    /**
     * ✅ CORRECTION PRINCIPALE - Changer le mot de passe et le statut
     */
    public function changePasswordAndStatut(Users $user): void 
    {
        // ✅ Utiliser une chaîne SQL, pas un PDOStatement
        $sql = "UPDATE users SET password = :password, statut = 1 WHERE id = :id";
        
        $parameters = [
            'password' => $user->getPassword(),
            'id' => $user->getId()
        ];

        $this->execute($sql, $parameters);
    }
    
    /**
     * Changer le statut d'un utilisateur
     */
    public function changeStatut(Users $user, int $statut): void 
    {
        $sql = "UPDATE users SET statut = :statut WHERE id = :id";
        
        $parameters = [
            'id' => $user->getId(),
            'statut' => $statut
        ];
            
        $this->execute($sql, $parameters);
    }
    
    /**
     * Changer le mot de passe d'un utilisateur
     */
    public function changePassword(Users $user, string $password): void 
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        
        $parameters = [
            'id' => $user->getId(),
            'password' => $password
        ];
            
        $this->execute($sql, $parameters);
    }
    
    /**
     * Réinitialiser le mot de passe et le statut
     */
    public function resetOneUserPasswordAndStatus(int $userId, string $passwordHash): bool 
    {
        try {
            $sql = "UPDATE users SET password = :password, statut = 0 WHERE id = :id";
            
            $parameters = [
                'password' => $passwordHash,
                'id' => $userId
            ];
            
            $this->execute($sql, $parameters);
            
            // Vérifier que la mise à jour a bien eu location
            $checkSql = "SELECT id FROM users WHERE id = :id AND statut = 0";
            $result = $this->findOne($checkSql, ['id' => $userId]);
            
            return $result !== false;
            
        } catch (PDOException $e) {
            error_log("Erreur resetPasswordAndStatus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir tous les utilisateurs avec formatage
     */
    public function getAllUsers(): array 
    {
        $sql = "SELECT id, email, firstname, 
                CASE
                    WHEN newsletter = 0 THEN 'NON'
                    ELSE 'OUI' 
                END AS newsletter, 
                CASE
                    WHEN role = 1 THEN 'USER'
                    ELSE 'ADMIN' 
                END AS role,
                CASE
                    WHEN statut = 0 THEN 'COMPTE NON VALIDE'
                    WHEN statut = 1 THEN 'COMPTE VALIDE'
                    WHEN statut = 2 THEN 'COMPTE BANNI'
                    ELSE 'COMPTE EN ATTENTE' 
                END AS statut       
                FROM users";
        
        return $this->findAll($sql);
    }

    /**
     * Rechercher des utilisateurs par email (LIKE)
     */
    public function getAllUsersByLike(string $ref): array 
    {
        $sql = "SELECT id, email, firstname, 
                CASE
                    WHEN newsletter = 0 THEN 'NON'
                    ELSE 'OUI' 
                END AS newsletter, 
                CASE
                    WHEN role = 1 THEN 'USER'
                    ELSE 'ADMIN' 
                END AS role,
                CASE
                    WHEN statut = 0 THEN 'COMPTE NON VALIDE'
                    WHEN statut = 1 THEN 'COMPTE VALIDE'
                    WHEN statut = 2 THEN 'COMPTE BANNI'
                    ELSE 'COMPTE EN ATTENTE' 
                END AS statut       
                FROM users WHERE email LIKE :ref ORDER BY id DESC LIMIT 100";
        
        $parameters = ["ref" => $ref];
        
        return $this->findAll($sql, $parameters);
    }
    
    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(int $userId, string $passwordHash): bool 
    {
        try {
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $parameters = [
                'password' => $passwordHash,
                'id' => $userId
            ];
            
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur updatePassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour le statut
     */
    public function updateStatus(int $userId, int $status): bool 
    {
        try {
            $sql = "UPDATE users SET statut = :statut WHERE id = :id";
            $parameters = [
                'statut' => $status,
                'id' => $userId
            ];
            
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur updateStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour le rôle avec vérifications
     */
    public function updateRole(int $userId, int $role): bool 
    {
        try {
            // Vérification que le rôle est valide (1 = utilisateur, 2 = administrateur)
            if (!in_array($role, [1, 2])) {
                error_log("Rôle invalide: $role (doit être 1 ou 2)");
                return false;
            }
            
            // Vérifier que l'utilisateur existe
            $checkSql = "SELECT id FROM users WHERE id = :id";
            $result = $this->findOne($checkSql, ['id' => $userId]);
            
            if (!$result) {
                error_log("Utilisateur non trouvé avec l'ID: $userId");
                return false;
            }
            
            // Mettre à jour le rôle
            $sql = "UPDATE users SET role = :role WHERE id = :id";
            $parameters = [
                'role' => $role,
                'id' => $userId
            ];
            
            $this->execute($sql, $parameters);
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur PDO updateRole: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Erreur updateRole: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatar(int $userId, int $avatar): bool 
    {
        try {
            $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
            $parameters = [
                'avatar' => $avatar,
                'id' => $userId
            ];
            
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur updateAvatar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour la newsletter
     */
    public function updateNewsletter(int $userId, int $newsletter): bool 
    {
        try {
            $sql = "UPDATE users SET newsletter = :newsletter WHERE id = :id";
            $parameters = [
                'newsletter' => $newsletter,
                'id' => $userId
            ];
            
            $this->execute($sql, $parameters);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur updateNewsletter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir un utilisateur par ID et retourner un item Users
     */
    public function getOneUserById(int $id): ?Users 
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $parameters = ['id' => $id];
        
        $userData = $this->findOne($sql, $parameters);
        
        if ($userData) {
            $user = new Users();
            $user->setId($userData['id']);
            $user->setEmail($userData['email']);
            $user->setFirstname($userData['firstname']);
            $user->setAge($userData['age']);
            $user->setAvatar($userData['avatar']);
            $user->setNewsletter($userData['newsletter']);
            $user->setRole($userData['role']);
            $user->setStatut($userData['statut']);
            $user->setCreatedAt($userData['createdAt']);
            
            return $user;
        }
        
        return null;
    }
}

<?php
class GameManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }
/**
 * ******************************* JEU CLICK SOURIS*****************
 * 
 * 
     * Récupère le meilleur score enregistré pour un utilisateur ET un niveau donnés.
     * @param User $user L'objet utilisateur.
     * @param string $level Clé du niveau ('facile', 'intermediaire', 'difficile').
     * @return array|null Le meilleur score et l'ID de l'enregistrement, ou null.
     */
    public function getBestScoreByUser(User $user, string $level): ?array
    {
        $userId = $user->getId(); // Récupération de l'ID à partir de l'objet
       
        $query = 'SELECT score, id FROM ' . self::TABLE
               . ' WHERE user_id = :user_id AND level = :level'
               . ' ORDER BY score DESC LIMIT 1';
       
        $statement = $this->db->prepare($query);
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('level', $level, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Insère un nouveau score pour un niveau.
     * @param User $user L'objet utilisateur.
     */
    public function insertScore(User $user, int $score, string $level): bool
    {
        $userId = $user->getId(); // Récupération de l'ID à partir de l'objet
       
        $query = 'INSERT INTO ' . self::TABLE . ' (user_id, score, level, created_at) VALUES (:user_id, :score, :level, NOW())';

        $statement = $this->db->prepare($query);
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('score', $score, PDO::PARAM_INT);
        $statement->bindValue('level', $level, PDO::PARAM_STR);
        return $statement->execute();
    }

    /**
     * Met à jour le score existant (méthode interne, ne change pas car elle n'a pas besoin de l'utilisateur).
     */
    public function updateScore(int $recordId, int $newScore): bool
    {
        $query = 'UPDATE ' . self::TABLE . ' SET score = :score, created_at = NOW() WHERE id = :id';

        $statement = $this->db->prepare($query);
        $statement->bindValue('score', $newScore, PDO::PARAM_INT);
        $statement->bindValue('id', $recordId, PDO::PARAM_INT);
        return $statement->execute();
    }

    /**
     * Logique principale : insère ou met à jour le meilleur score pour un niveau donné.
     * @param User $user L'objet utilisateur.
     * @param int $newScore
     * @param string $level
     * @return array Résultat de l'opération
     */
    public function saveOrUpdateBestScore(User $user, int $newScore, string $level): array
    {
        // 1. Récupérer le meilleur score actuel pour ce NIVEAU et cet utilisateur
        $existingScoreData = $this->getBestScoreByUser($user, $level); // Utilisation de la nouvelle méthode
        $existingBestScore = $existingScoreData['score'] ?? 0;

        if ($existingScoreData === null) {
            // 2. Si aucun score n'existe, on insère
            $success = $this->insertScore($user, $newScore, $level); // Passage de l'objet $user
            return ['success' => $success, 'message' => "Score enregistré pour la première fois en {$level}.", 'newBestScore' => $newScore];
        }

        if ($newScore > $existingBestScore) {
            // 3. Si le score est meilleur, on met à jour l'enregistrement existant
            $recordId = $existingScoreData['id'];
            $success = $this->updateScore($recordId, $newScore);
            return ['success' => $success, 'message' => "Nouveau meilleur score enregistré en {$level} !", 'newBestScore' => $newScore];
        }

        // 4. Score non amélioré
        return ['success' => true, 'message' => "Score non amélioré en {$level}.", 'newBestScore' => $existingBestScore];
    }



/***************************************************JEU MEMO****************************************************** */

   public function getBestScoresByLevel(string $level): array
{
    $sql = "SELECT MIN(time) AS best_time, MIN(score) AS best_moves
            FROM memory
            WHERE level = :level";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':level', $level, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'time'  => $row['best_time'] !== null ? (int)$row['best_time'] : null,
        'moves' => $row['best_moves'] !== null ? (int)$row['best_moves'] : null,
    ];
}

    public function saveScore(int $userId, int $score, string $level, int $time): void
{
    $sql = "INSERT INTO memory (user_id, score, level, created_at, time)
            VALUES (:user_id, :score, :level, NOW(), :time)";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':score', $score, PDO::PARAM_INT);
    $stmt->bindValue(':level', $level, PDO::PARAM_STR);
    $stmt->bindValue(':time', $time, PDO::PARAM_INT); // ← INT au lieu de STR
    $stmt->execute();
}


/**
 * Récupère les meilleurs scores d'un utilisateur pour un niveau donné
 */
public function getBestScoresByUserAndLevel(int $userId, string $level): array
{
    $sql = "SELECT MIN(time) AS best_time, MIN(score) AS best_moves
            FROM memory
            WHERE user_id = :user_id AND level = :level";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':level', $level, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'time'  => $row['best_time'] !== null ? (int)$row['best_time'] : null,
        'moves' => $row['best_moves'] !== null ? (int)$row['best_moves'] : null,
    ];
}

/**
 * Sauvegarde OU met à jour le meilleur score pour un niveau
 */
public function saveOrUpdateMemoryScore(int $userId, int $moves, string $level, int $time): bool
{
    // Récupérer le meilleur score actuel
    $bestScores = $this->getBestScoresByUserAndLevel($userId, $level);
    
    $isBetterMoves = $bestScores['moves'] === null || $moves < $bestScores['moves'];
    $isBetterTime = $bestScores['time'] === null || $time < $bestScores['time'];
    
    // Ne sauvegarder que si c'est un meilleur score (moins de coups OU moins de temps)
    if ($isBetterMoves || $isBetterTime) {
        // Si pas de score existant OU meilleur score, on insère
        $sql = "INSERT INTO memory (user_id, score, level, created_at, time)
                VALUES (:user_id, :score, :level, NOW(), :time)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':score', $moves, PDO::PARAM_INT);
        $stmt->bindValue(':level', $level, PDO::PARAM_STR);
        $stmt->bindValue(':time', $time, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    return false; // Pas de nouveau record
}


}



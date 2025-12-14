<?php
class GameManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }
/**
 * ==================== JEU CLICK SOURIS ====================
 */

/**
 * Récupère le meilleur score pour un utilisateur et un niveau donnés
 * @param int $userId L'ID de l'utilisateur
 * @param string $level Clé du niveau ('facile', 'intermediaire', 'difficile')
 * @return array Tableau avec 'score' (int ou 0 si aucun)
 */
public function getBestScoreByUserId(int $userId, string $level): array
{
    $query = 'SELECT MAX(score) AS best_score 
              FROM click
              WHERE user_id = :user_id AND level = :level';
   
    $statement = $this->db->prepare($query);
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':level', $level, PDO::PARAM_STR);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    
    $score = $result['best_score'] !== null ? (int)$result['best_score'] : 0;
    
    // Debug
    error_log("GameManager - getBestScoreByUserId: user=$userId, level=$level, score=$score");
    
    return ['score' => $score];
}

/**
 * Insère un nouveau score
 * @param int $userId L'ID de l'utilisateur
 * @param int $score Le score à enregistrer
 * @param string $level Le niveau de difficulté
 * @return bool Succès de l'insertion
 */
public function insertClickScore(int $userId, int $score, string $level): bool
{
    $query = 'INSERT INTO click (user_id, score, level, created_at) 
              VALUES (:user_id, :score, :level, NOW())';

    $statement = $this->db->prepare($query);
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':score', $score, PDO::PARAM_INT);
    $statement->bindValue(':level', $level, PDO::PARAM_STR);
    
    $success = $statement->execute();
    
    // Debug
    error_log("GameManager - insertClickScore: user=$userId, score=$score, level=$level, success=" . ($success ? 'true' : 'false'));
    
    return $success;
}

/**
 * Sauvegarde un score et détermine si c'est un nouveau record
 * @param int $userId L'ID de l'utilisateur
 * @param int $newScore Le nouveau score obtenu
 * @param string $level Le niveau de difficulté
 * @return array Résultat avec success, message et newBestScore
 */
public function saveOrUpdateBestScore(int $userId, int $score, string $level): array {
    $best = $this->getBestScoreByUserId($userId, $level);

    if ($best === null) {
        // Première entrée pour ce niveau
        $sql = "INSERT INTO click (user_id, level, score, created_at)
                VALUES (:user_id, :level, :score, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':level'   => $level,
            ':score'   => $score
        ]);
        return [
            'success' => true,
            'message' => '🏆 Premier score enregistré !',
            'newBestScore' => $score
        ];
    }

    if ($score > $best) {
        // Nouveau record → UPDATE
        $sql = "UPDATE click 
                SET score = :score, created_at = NOW()
                WHERE user_id = :user_id AND level = :level";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':score'   => $score,
            ':user_id' => $userId,
            ':level'   => $level
        ]);
        return [
            'success' => true,
            'message' => '🏆 Nouveau record enregistré !',
            'newBestScore' => $score
        ];
    }

    // Pas de nouveau record → rien à insérer
    return [
        'success' => true,
        'message' => 'Belle partie, mais pas de nouveau record.',
        'newBestScore' => $best
    ];
}

/**
 * Récupère les meilleurs scores de l'utilisateur pour tous les niveaux
 * @param int $userId L'ID de l'utilisateur
 * @return array Tableau associatif par niveau
 */
public function getBestScoresForAllLevels(int $userId): array
{
    $query = 'SELECT level, MAX(score) AS best_score
              FROM click
              WHERE user_id = :user_id
              GROUP BY level';
    
    $statement = $this->db->prepare($query);
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->execute();
    
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialiser tous les niveaux à 0
    $scores = [
        'facile' => 0,
        'intermediaire' => 0,
        'difficile' => 0
    ];
    
    // Remplir avec les scores réels
    foreach ($results as $row) {
        $scores[$row['level']] = (int)$row['best_score'];
    }
    
    return $scores;
}

public function saveClickScore(int $userId, string $level, int $score): array {
    $best = $this->getBestScoreByUserId($userId, $level);

    if ($best === null || $score > $best) {
        // Insert ou update
        $sql = "INSERT INTO click (user_id, level, score, created_at)
                VALUES (:user_id, :level, :score, NOW())
                ON DUPLICATE KEY UPDATE score = :score, created_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':level'   => $level,
            ':score'   => $score
        ]);
        return ['success' => true, 'isNewRecord' => true, 'newBestScore' => $score, 'level' => $level];
    }

    return ['success' => true, 'isNewRecord' => false, 'newBestScore' => $best, 'level' => $level];
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



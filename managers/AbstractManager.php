<?php
require_once("ErrorManager.php");

abstract class AbstractManager {
    protected PDO $db;
    
    /**
     * Constructor of the DatabaseManager class.
     * Initializes a new instance of the PDO class for database management.
     * Uses DB_HOST, DB_NAME, DB_USER and DB_PASSWORD constants for connection configuration.
     * Also configures default recovery mode and error handling mode.
     * @throws \PDOException If database connection fails.
     */
    public function __construct()
    {
        $connexion = "mysql:host=".$_ENV["DB_HOST"].";port=3306;charset=".$_ENV["DB_CHARSET"].";dbname=".$_ENV["DB_NAME"];
        $this->db = new PDO(
            $connexion,
            $_ENV["DB_USER"],
            $_ENV["DB_PASSWORD"],
            [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, // Returns an array indexed by the column name
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION       // throws PDOExceptions
            ]
        );
    }
    
    /**
     * Method which allows you to execute an SQL query and return a record set.
     *
     * @param    string  $req    - SQL request.
     * @param    array   $params - Parameters array for the query.
     *
     * @return   array           - Array containing a recordset.
     */
    protected function findAll(string $req, array $params = []) {
        try {
            $query = $this->db->prepare($req);    // ✅ CORRIGÉ : requête à prepare()
            $query->execute($params);             // ✅ CORRIGÉ : paramètres à execute()
            return $query->fetchAll();
        } catch (\PDOException $e) {
            // Gestion d'erreur cohérente
            error_log("Erreur findAll: " . $e->getMessage());
            throw $e; // Re-lancer l'exception ou retourner un tableau vide selon vos besoins
        }
    }
    
    /**
     * Method which allows you to execute an SQL query and return a single record.
     *
     * @param    string  $req    - SQL request.
     * @param    array   $params - Parameters array for the query.
     *
     * @return   array|false     - Array containing a single record or false if not found.
     */
    protected function findOne(string $req, array $params = []) {
        try {
            $query = $this->db->prepare($req);
            $query->execute($params);
            return $query->fetch();
        } catch (\PDOException $e) {
            error_log("Erreur findOne: " . $e->getMessage());
            throw $e; // Re-lancer l'exception pour une gestion appropriée
        }
    }
    
    /**
     * Method for execute a query with parameters.
     *
     * @param    string  $sql    - SQL request.
     * @param    array   $params - Parameters array for the query.
     *
     * @return   void
     */
    protected function execute(string $sql, array $params = []) {
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            $query->closeCursor();
        } catch (\PDOException $e) {
            error_log("Erreur execute: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Method for execute a query with parameters and return the statement.
     *
     * @param    string  $sql    - SQL request.
     * @param    array   $params - Parameters array for the query.
     *
     * @return   PDOStatement    - The executed statement.
     */
    protected function executeQuery(string $sql, array $params = []) {
        try {
            $query = $this->db->prepare($sql);
            $query->execute($params);
            return $query;
        } catch (\PDOException $e) {
            error_log("Erreur executeQuery: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get the last inserted ID.
     *
     * @return   string  - The last inserted ID.
     */
    protected function getLastInsertId(): string {
        return $this->db->lastInsertId();
    }
    
    protected function renderView(string $template, array $data = [], array $scripts = []) : void
{
    $data['scripts'] = $scripts;
    $data['elapsed_time'] = (new TimesModels())->getElapsedTime();

    echo $this->twig->render($template, $data);
    exit();
}
}
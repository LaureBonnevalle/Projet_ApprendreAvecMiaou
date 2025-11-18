<?php

class ContactsManager extends AbstractManager {

    /**
     * Inserts a new user into the database
     *
     * This method takes a `Contacts` object as a parameter and inserts the contact's information
     * into the database using a predefined SQL query. Extracte from the `Contacts` object 
     * and passed as parameters to the SQL query.
     *
     * @param Contacts $newContact - The Contacts object containing the contact's information to be inserted
     * @return void
     */
    public function insert(Contacts $newContact):void {
        $sql = "INSERT INTO `messages`(`receptedDate`, `firstname`, `email`, `subject`, `content`, `statut`) VALUES (?, ?, ?, ?,?,?)";

        $datas = [
            $newContact->getReceptedDate(),
            $newContact->getFirstname(),
            $newContact->getEmail(),
            $newContact->getSubject(),
            $newContact->getContent(),
            $newContact->getStatut(),
        ];
        
        $this->execute($sql, $datas);
    }
    
    public function getAll(): array  {
        $query = $this->db->prepare ( "SELECT * FROM `messages` ORDER BY statut ASC, receptedDate DESC");   
        
        
        
         $query->execute();
        $messages = $query->fetchAll(PDO::FETCH_ASSOC);
    
        return $messages; // Make sure to return the $search array
    }
    
    
    public function getAllNotRead() {
        $sql = "SELECT COUNT(*) as unread_count FROM `messages` WHERE statut = 0";        
        $result = $this->findOne($sql); // Assurez-vous que 'find' retourne une seule ligne
        
        return $result['unread_count']; // Retourne uniquement le nombre de messages non lus
    }

    public function getOne($id) { 
        
        $query = $this->db->prepare("
            SELECT * FROM messages 
            WHERE id = :id
        ");
        
        $parameters = [
            "id" => $id
        ];

        $query->execute($parameters);
        $data = $query->fetch();
        
        return $data;
        //$data = [ $content->getId() ];     
        //return $this->findAll($query, $data);
    }
    
    
    
    

    public function updateStatut(Contacts $content) {
        $sql = "UPDATE `messages` SET `statut` = '1' WHERE id = ?";   
        $data = [ $content->getId() ];     
        $this->execute($sql, $data);
    }

    public function deleteOne(int $id) {
    $sql = "DELETE FROM `messages` WHERE id = ?";
    $params = [$id]; // Utiliser un tableau indexé
    $this->execute($sql, $params);
}
    
}
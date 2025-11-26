<?php
class Items {
    private int $id;
    private string $item_name;
    private string $item_description;
    private string $url;
    private string $alt;
    
    // PROBLÈME CORRIGÉ: Constructeur avec tous les paramètres optionnels sauf required
    public function __construct(int $id = 0, string $item_name = '', string $item_description = '', string $url = '', string $alt = '') {
        $this->id = $id;
        $this->item_name = $item_name;
        $this->item_description = $item_description;
        $this->url = $url;
        $this->alt = $alt;
    }
    
    // Getters
    public function getId(): int {
        return $this->id;
    }
    
    public function getItemName(): string {
        return $this->item_name;
    }
    
    public function getItemDescription(): string {
        return $this->item_description;
    }
    
    public function getUrl(): string {
        return $this->url;
    }
    
    public function getAlt(): string {
        return $this->alt;
    }
    
    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }
    
    public function setItemName(string $item_name): void {
        $this->item_name = $item_name;
    }
    
    public function setItemDescription(string $item_description): void {
        $this->item_description = $item_description;
    }
    
    public function setUrl(string $url): void {
        $this->url = $url;
    }
    
    public function setAlt(string $alt): void {
        $this->alt = $alt;
    }
    
    // Méthode utile pour créer un item depuis un array (comme celui de la BDD)
    public static function fromArray(array $data): self {
        return new self(
            $data['id'] ?? 0,
            $data['item_name'] ?? '', // Correspond à votre colonne
            $data['item_description'] ?? '', // Correspond à votre colonne
            $data['url'] ?? '',
            $data['alt'] ?? ''
        );
    }
    
    // Méthode pour convertir en array
    public function toArray(): array {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'item_description' => $this->item_description,
            'url' => $this->url,
            'alt' => $this->alt
        ];
    }
}
?>
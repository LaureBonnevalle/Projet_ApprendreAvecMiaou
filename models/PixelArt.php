<?php

class PixelArt {
    private $gridSize = 12;

    public function getGridSize() {
        return $this->gridSize;
    }

    public function getDefaultColors() {
        return [
            "#FF5733", "#33FF57", "#3357FF", "#FFFF33", "#FF33FF", "#33FFFF",
            "#FF8800", "#8800FF", "#0088FF", "#00FF88", "#FF0088", "#888888",
            "#000000", "#FFFFFF"
        ];
    }
}

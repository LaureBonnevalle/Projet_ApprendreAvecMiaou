document.addEventListener('DOMContentLoaded', function() {
    const colors = [
        "#FF5733", "#33FF57", "#3357FF", "#FFFF33", "#FF33FF", "#33FFFF",
        "#FF8800", "#8800FF", "#0088FF", "#00FF88", "#FF0088", "#888888",
        "#000000", "#FFFFFF", "#FF5733", "#33FF57", "#3357FF", "#FFFF33",
        "#FF33FF", "#33FFFF"
        ];
    
    const container = document.getElementById('pixelArtContainer');
    const colorPalette = document.getElementById('colorPalette');
    const resetButton = document.getElementById('resetButton');
    let selectedColor = colors[0];
    
    // Create pixels
    for (let i = 0; i < 12 * 12; i++) {
        const pixel = document.createElement('div');
        pixel.classList.add('pixel');
        pixel.addEventListener('click', () => {
            pixel.style.backgroundColor = selectedColor;
        });
        container.appendChild(pixel);
    }
    
    // Create color palette
    colors.forEach(color => {
        const colorDiv = document.createElement('div');
        colorDiv.classList.add('color');
        colorDiv.style.backgroundColor = color;
        colorDiv.addEventListener('click', () => {
            selectedColor = color;
        });
        colorPalette.appendChild(colorDiv);
    });
    
    // Reset button
    resetButton.addEventListener('click', () => {
        const pixels = document.querySelectorAll('.pixel');
        pixels.forEach(pixel => {
            pixel.style.backgroundColor = 'white';
        });
    });
);
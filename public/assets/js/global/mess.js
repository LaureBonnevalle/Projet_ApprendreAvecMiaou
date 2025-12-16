/**
 * Gestion des messages de session avec délai avant clignotement
 * Session messages handling with delay before blinking
 *
 * @author Miaou Team
 * @version 2.0
 */

document.addEventListener('DOMContentLoaded', function() {
  const messageElement = document.getElementById('session-message');   // ✅ Success
  const errorElement   = document.getElementById('session-error');     // ❌ Error
  const alertElement   = document.getElementById('session-alert');     // ⚠️ Warning

  // Vérifie si l'élément existe ET contient du texte
  function hasContent(el) {
    return el && el.textContent.trim() !== '';
  }

  const activeElements = [messageElement, errorElement, alertElement].filter(hasContent);

  if (activeElements.length > 0) {
    let isVisible = true;

    // Affiche pendant 1,5s avant de commencer le clignotement
    setTimeout(() => {
      const interval = setInterval(() => {
        isVisible = !isVisible;
        activeElements.forEach(el => {
          el.style.visibility = isVisible ? 'visible' : 'hidden';
        });
      }, 500);

      // Après 5s supplémentaires, stoppe le clignotement et cache les messages
      setTimeout(() => {
        clearInterval(interval);
        activeElements.forEach(el => {
          el.style.display = 'none';
        });
      }, 5000);
    }, 1500); // 👈 délai initial avant clignotement
  } else {
    // Si aucun message → on force display:none
    [messageElement, errorElement, alertElement].forEach(el => {
      if (el) el.style.display = 'none';
    });
  }
});

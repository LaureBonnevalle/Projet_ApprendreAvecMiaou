/*document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.querySelector('select[name="categorie_id"]');
    const coloriagesContainer = document.getElementById('coloriages-container');

    selectElement.addEventListener('change', function() {
        const categorieId = this.value;

        fetch(`/path/to/getColoriagesByCategorie/${categorieId}`)
            .then(response => response.json())
            .then(coloriages => {
                coloriagesContainer.innerHTML = ''; // Clear the container

                coloriages.forEach(coloriage => {
                    const coloriageElement = document.createElement('div');
                    coloriageElement.innerHTML = `
                        <a href="${coloriage.url}" download="${coloriage.name}.pdf">${coloriage.name}</a>
                        <img src="${coloriage.thumbnail_url}" alt="${coloriage.name}">
                    `;
                    coloriagesContainer.appendChild(coloriageElement);
                });
            })
            .catch(error => console.error('Error:', error));
    });
});*/



document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('categorie-select');
    //const fetchButton = document.getElementById('fetch-coloring');
    const coloringList = document.getElementById('colorings-list');
    const previewSection = document.getElementById('preview-section');
    const preview = document.getElementById('preview');

    selectElement.addEventListener('change', function() {
        
        const categorieId = selectElement.value;
        previewSection.innerHTML = "";
        preview.innerHTML = "";
        
        let myRequest = new Request('?route=coloringsListe', {
            method: 'POST',
            body: JSON.stringify({ 
                id: categorieId
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        // On attend la réponse de PHP pour obtenir la liste des coloriages en fonction de la catégorie demandée
        
        fetch(myRequest)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ');
                }
                return response.json();
            })
            .then(data => {
                //console.log("Fetched Data:", data);  
                render(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        
        
       function render(data) {
           
            coloringList.innerHTML = ''; // Vider la liste
            const listing = document.createElement('listing');
            
            // Créer un élément <li>        
            const h3 = document.createElement('h3');
            h3.textContent = "Choisis un coloriage pour pouvoir le télécharger et l'imprimer";
            
            // Ajouter le lien à l'élément <h3>
            listing.appendChild(h3);
             
            data.forEach(element => {
                
                
                
                const coloriageElement = document.createElement('li');
                
                // Créer un élément <a>
                const linkElement = document.createElement('a');
                linkElement.textContent = element.name;             // Nom du lien
                linkElement.href = "../" + element.url;             // URL du lien
                linkElement.setAttribute('data-id', element.id);    // Ajouter l'ID en tant qu'attribut data
                
                // Ajouter l'événement 'click' pour prévisualiser
                linkElement.addEventListener('click', function(e) {
                    e.preventDefault(); // Empêcher la navigation par défaut
                    showUpload(element.url, element.id);            // Appel de la fonction showUpload
                    showPreview("../old/" + element.url, element.id);   // Appel de la fonction showPreview
                });
        
                
                // Ajouter le lien à l'élément <li>
                coloriageElement.appendChild(linkElement);
                
                // Ajouter l'élément <li> à la liste
                coloringList.appendChild(coloriageElement);
            });
        }      
    });
    
    
    // Fonction permettant de créé un lien de téléchargement et de le mettre dans la div
    function showUpload(url, id) {
        previewSection.innerHTML = `
            <a href="${url}" download>Télécharger</a>
            <div class="progress-bar" id="progress-bar"></div>
            <img src="path/to/thumbnails/${id}.jpg" alt="Aperçu du coloriage">
        `;        
    }
    
    // Fonction permettant d'afficher la préview du document dans la div
    function showPreview(url, id) {
        // Sélectionner ou créer un conteneur pour la prévisualisation (par exemple un <div>)
        const previewContainer = document.getElementById('preview'); // Assurez-vous d'avoir un div avec cet ID dans votre HTML
        
        // Si le conteneur n'existe pas encore, en créer un
        if (!previewContainer) {
            const newPreviewContainer = document.createElement('div');
            newPreviewContainer.id = 'preview';
            document.body.appendChild(newPreviewContainer); // Ajouter le conteneur à la fin du body (ou ailleurs)
        }
        
        // Vider le contenu précédent
        previewContainer.innerHTML = ''; 
        
        // Créer un élément iframe pour afficher le PDF
        const iframe = document.createElement('iframe');
        iframe.src = url; // URL du PDF
        iframe.width = '600'; // Largeur de l'iframe
        iframe.height = '400'; // Hauteur de l'iframe
        iframe.setAttribute('frameborder', '0'); // Optionnel : supprimer la bordure
        iframe.setAttribute('allowfullscreen', true); // Optionnel : autoriser le plein écran
        
        // Ajouter l'iframe au conteneur
        previewContainer.appendChild(iframe);
    }
    
    
    /*

        const downloadLink = previewSection.querySelector('a');
        const progressBar = document.getElementById('progress-bar');

        downloadLink.addEventListener('click', function(e) {
            e.preventDefault();

            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.responseType = 'blob';

            xhr.onprogress = function(event) {
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    progressBar.style.width = `${percentComplete}%`;
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const blob = xhr.response;
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `${id}.pdf`;
                    link.click();
                }
            };

            xhr.send();
        });
    }*/
});
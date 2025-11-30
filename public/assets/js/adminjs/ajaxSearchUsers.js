document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('search').addEventListener('keyup', handleButtonClick);   

    function handleButtonClick() {
        let search  = document.getElementById('search').value;
        
        console.log("le champ à changé : il faut rechercher : " + search)     
        
        let myRequest = new Request('?route=ajaxSearchUsers', {
            method: 'POST',
            body: JSON.stringify({ ref: search }),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        fetch(myRequest)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                render(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});

function render(data) {
    
    let table = document.querySelector('#users table');
    if (!table) {
        console.error('Table element not found!');
        return;
    }

    let tbody = table.querySelector('tbody');
    if (tbody) {
        tbody.innerHTML = '';
    } else {
        tbody = document.createElement('tbody');
        table.appendChild(tbody);
    }
    
    data.forEach(user => {
        const row = document.createElement('tr');

        const idCell = document.createElement('td');
        idCell.textContent = user.id;
        row.appendChild(idCell);

        const firstnameCell = document.createElement('td');
        firstnameCell.innerHTML = user.firstname;
        row.appendChild(firstnameCell);

        const emailCell = document.createElement('td');
        emailCell.textContent = user.email;
        row.appendChild(emailCell);


        const viewCell = document.createElement('td');
        const viewLink = document.createElement('a');
        viewLink.href = `?route=readOneUser&id=${user.id}`;
        viewLink.textContent = 'Voir';
        viewCell.appendChild(viewLink);
        row.appendChild(viewCell);

        tbody.appendChild(row);
    });
}

function formatDateTime(dateString, format) {
    return new Date(dateString).toLocaleDateString();
}
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keyup', handleSearch);
    }

    function handleSearch() {
        let search = searchInput.value;
        console.log("Le champ a changé : il faut rechercher : " + search);

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
    const container = document.querySelector('#users .table-container');
    if (!container) {
        console.error('Table container not found!');
        return;
    }

    // ✅ Reconstruire la table complète avec thead + tbody
    container.innerHTML = `
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Role</th>
                    <th>Newsletter</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `;

    const tbody = container.querySelector('tbody');

    data.forEach(user => {
        const row = document.createElement('tr');

        // Id
        const idCell = document.createElement('td');
        idCell.textContent = user.id;
        row.appendChild(idCell);

        // Prénom
        const firstnameCell = document.createElement('td');
        firstnameCell.textContent = user.firstname;
        row.appendChild(firstnameCell);

        // Email
        const emailCell = document.createElement('td');
        emailCell.textContent = user.email;
        row.appendChild(emailCell);

        // Statut
        const statutCell = document.createElement('td');
        statutCell.textContent = user.statut;
        row.appendChild(statutCell);

        // Role
        const roleCell = document.createElement('td');
        roleCell.textContent = user.role;
        row.appendChild(roleCell);

        // Newsletter
        const newsletterCell = document.createElement('td');
        newsletterCell.textContent = user.newsletter;
        row.appendChild(newsletterCell);

        // Action (bouton Voir)
        const viewCell = document.createElement('td');
        const viewLink = document.createElement('a');
        viewLink.href = `?route=readOneUser&id=${user.id}`;
        viewLink.textContent = 'Voir';
        viewCell.appendChild(viewLink);
        row.appendChild(viewCell);

        tbody.appendChild(row);
    });
}

// ✅ Utilitaire pour formater une date si besoin
function formatDateTime(dateString, format) {
    return new Date(dateString).toLocaleDateString();
}

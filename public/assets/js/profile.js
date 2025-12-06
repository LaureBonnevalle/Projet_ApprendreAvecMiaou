document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("profile-form");
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        const response = await fetch("?route=updateProfile", {
            method: "POST",
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            document.querySelector(".alert-success").innerText = result.message;
        } else {
            document.querySelector(".alert-danger").innerText = result.message;
        }
    });document.addEventListener("DOMContentLoaded", () => {
    // ===================== PROFIL =====================
    const profileForm = document.getElementById("profile-form");
    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(profileForm);

            try {
                const response = await fetch("?route=updateProfile", {
                    method: "POST",
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    document.querySelector(".alert-success").innerText = result.message;
                } else {
                    document.querySelector(".alert-danger").innerText = result.message;
                }
            } catch (err) {
                console.error("Erreur lors de la mise à jour du profil:", err);
            }
        });
    }

    // ===================== CONTACT =====================
    const openContactBtn = document.getElementById("open-contact");
    const contactContainer = document.getElementById("contact-form-container");

    if (openContactBtn && contactContainer) {
        openContactBtn.addEventListener("click", async () => {
            try {
                // Charger le formulaire de contact (Twig fragment ou HTML renvoyé par ton contrôleur)
                const response = await fetch("?route=contactUsForm");
                const html = await response.text();
                contactContainer.innerHTML = html;

                // Ajouter un listener sur le formulaire chargé
                const contactForm = contactContainer.querySelector("form");
                if (contactForm) {
                    contactForm.addEventListener("submit", async (e) => {
                        e.preventDefault();
                        const formData = new FormData(contactForm);

                        try {
                            const res = await fetch("?route=contactUs", {
                                method: "POST",
                                body: formData
                            });
                            const result = await res.json();

                            let feedback = contactContainer.querySelector(".contact-feedback");
                            if (!feedback) {
                                feedback = document.createElement("div");
                                feedback.classList.add("contact-feedback");
                                contactContainer.appendChild(feedback);
                            }

                            if (result.success) {
                                feedback.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                                contactForm.reset();
                            } else {
                                feedback.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                            }
                        } catch (err) {
                            console.error("Erreur lors de l'envoi du message de contact:", err);
                        }
                    });
                }
            } catch (err) {
                console.error("Erreur lors du chargement du formulaire de contact:", err);
            }
        });
    }
});
});

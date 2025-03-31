document.addEventListener("DOMContentLoaded", function () {

    const currentUrl = window.location.pathname;

    if (currentUrl.includes("Sign-up.html")) {
        document.getElementById('soummission_btn').addEventListener('click', creerUser);
    } else if (currentUrl.includes("Sign-in.html")) {
        document.getElementById('connexion_btn').addEventListener('click', connecterUser);
    }
});

  

//document.getElementById('soummission_btn').addEventListener('click', creerUser);
async function creerUser(event) {
    event.preventDefault();
    // Get the form and the submit button
    const form = document.querySelector('#form_new_user_id');

    // Create a FormData object from the form
    const formData = new FormData(form);
    console.log(formData.values);

    try {
        const response = await fetch('http://localhost:9999/api/creerUser', {
            method: 'POST',
            body: formData,
        });

        // Traitement de la reponse
        if (response.ok) {
            //const result = await response.json();
            //form.reset(); // Reinitialiser le formulaire si la soummission est reussie.
        } else {
            console.log('FRONT-END:Echec creation nouveau compte.');
        }
    } catch (error) {
        console.error('Error:', error);
        console.log('Une erreur est survenue lors de la soummission.');
    }
}

//document.getElementById('connexion_btn').addEventListener('click', connecterUser);
async function connecterUser(event) {
    event.preventDefault();
    // Get the form and the submit button
    const form = document.querySelector('#form_connect_user_id');

    // Create a FormData object from the form
    const formData = new FormData(form);
    console.log(formData.values);

    try {
        const response = await fetch('http://localhost:9999/api/connexionUser', {
            method: 'POST',
            body: formData,
        });

        // Traitement de la reponse
        if (response.ok) {
            //const result = await response.json();
            //form.reset(); // Reinitialiser le formulaire si la soummission est reussie.
        } else {
            console.log('FRONT-END:Echec connexion.');
        }
    } catch (error) {
        console.error('Error:', error);
        console.log('Une erreur est survenue lors de la soummission.');
    }
}
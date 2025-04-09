document.addEventListener("DOMContentLoaded", function () {

    //fetchUserData();
    const currentUrl = window.location.pathname;

    if (currentUrl.includes("Sign-up.html")) {
        document.getElementById('soummission_btn').addEventListener('click', creerUser);
    } else if (currentUrl.includes("Sign-in.html")) {
        document.getElementById('connexion_btn').addEventListener('click', connecterUser);
    }
    else if (currentUrl.includes("chatPrivate.html")) {
        document.getElementById('chatBtnId').addEventListener('click', creerChat);
        document.getElementById('ses').addEventListener('click', fetchUserData);
    }
});



async function fetchUserData() {
    //const params = new URLSearchParams(window.location.search);
    const userID = 8;//params.get('userID');
    
    if (!userID) {
        console.error('userID not found in URL parameters.');
        return null;
    }
    
    console.log("ID ACTIVITY: "+userID);
    
    const apiUrl = `http://localhost:9999/api/inconnu/${userID}`;
    
    try {
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Error: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        console.log('User Data:', data);
        return data;
    } catch (error) {
        console.error(`Failed to fetch data from ${apiUrl}:`, error.message);
        return null;
    }
}

async function creerChat(event) {
    event.preventDefault();
    // Get the form and the submit button
    const form = document.querySelector('#form_chat');

    // Create a FormData object from the form
    const formData = new FormData(form);
    console.log(formData.values);

    try {
        const response = await fetch('http://localhost:9999/api/creerChat', {
            method: 'POST',
            body: formData,
        });

        // Traitement de la reponse
        if (response.ok) {
            //const result = await response.json();
            //form.reset(); // Reinitialiser le formulaire si la soummission est reussie.
        } else {
            console.log('FRONT-END:Echec creation nouveau chat.');
        }
    } catch (error) {
        console.error('Error:', error);
        console.log('Une erreur est survenue lors de la soummission.');
    }
}
  

document.getElementById('soummission_btn').addEventListener('click', creerUser);
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

let apiUrl = "http://162.243.167.200:9999";
//let apiUrl = "http://localhost:9999";
//CECI EST UN EXEMPLE DU JS QUI CE CONNECTE À L'API, MODIFIER LE LORSQUE VOUS Y SERAIT RENDU.
let host = null;

document.addEventListener("DOMContentLoaded", () => {
    const nomPage = document.title;

    console.log(nomPage);
    if (nomPage == 'Page1') {
        //lancer fonction pour page1
    } else if (nomPage == 'Page2') {
        //lancer fonction pour page1
    } else {
        console.log('Page non-contrôlé par mainScript.');
    }
});


async function connecterUser(event) {
    event.preventDefault();
    const form = event.target.form;
    const formData = new FormData(form);

    try {

        var url  = apiUrl + '/api/connexionUser';
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        // Traitement de la reponse
        if (response.ok) {
            const jsonData = await response.json(); // Parse the JSON and store it
            alert(JSON.stringify(jsonData)); 
            //window.location.href = 'Main.html';

        } else {
            alert("User or Email Invalid");
            console.log('FRONT-END:Echec connexion.');
        }
    } catch (error) {
        console.error('Error:', error);
        console.log('Une erreur est survenue lors de la soummission.');
    }
}


async function creerUser(event) {
    event.preventDefault();
    const form = event.target.form;

    const formData = new FormData(form);

    try {
        var url  = apiUrl + '/api/creerUser';
        console.log(url);
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        // Traitement de la reponse
        if (response.ok) {
            const jsonData = await response.json(); // Parse the JSON and store it
            alert(JSON.stringify(jsonData)); 
            //window.location.href = 'Sign-in.html';

        } else {
            console.log('FRONT-END:Echec creation nouveau compte.');
        }
    } catch (error) {
        console.error('Error:', error);
        console.log('Une erreur est survenue lors de la soummission.');
    }
}

    /**
     * Fetches Steam game data for a given app ID from a local API.
     * Logs the data to the console or logs an error if the request fails.
     * @param {string} appid - The Steam application ID of the game.
     */
    function GetSteamGameData(appid) {
        var url  = apiUrl + `/api/steam/game/${appid}`;
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error fetching game data');
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    /**
     * Fetches Steam user data for a given user ID from a local API.
     * Logs the data to the console or logs an error if the request fails.
     * @param {string} userid - The Steam user ID.
     */
    function GetSteamUserData(userid) {
        var url  = apiUrl + `/api/steam/user/${userid}`;
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error fetching user data');
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
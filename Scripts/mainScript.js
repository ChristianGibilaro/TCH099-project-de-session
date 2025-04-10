class mainScript extends globalVars {
    pageName = "";
    //False = localhost , true = server api
    UseLocalAPI = true;

    constructor(pageName) {
        super();
        this.pageName = pageName;
        console.log(`api ${pageName}`);
    }

    byPage() {
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
    }

    async connecterUser(event) {
        event.preventDefault();
        const form = event.target.form;
        const formData = new FormData(form);
        var apiUrl = super.getApiUrl(this.UseLocalAPI);

        try {
            var url = `${apiUrl}/api/connexionUser`;
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
            });

            // Traitement de la reponse
            if (response.ok) {
                const jsonData = await response.json(); // Parse the JSON and store it
                alert(JSON.stringify(jsonData));
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

    async creerUser(event) {
        event.preventDefault();
        const form = event.target.form;
        const formData = new FormData(form);
        console.log(form);
        var apiUrl = super.getApiUrl(this.UseLocalAPI);

        try {
            const response = await fetch(`${apiUrl}/api/creerUser`, {
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

    async GetSteamGameData(appid) {
        var apiUrl = super.getApiUrl(this.UseLocalAPI);

        try {
            const response = await fetch(`${apiUrl}/api/steam/game/${appid}`, {
                method: 'GET',
            });

            // Traitement de la reponse
            if (response.ok) {
                const jsonData = await response.json(); // Parse the JSON and store it
                alert(appid + " = " + jsonData[appid].data["name"]);
            } else {
                console.log('FRONT-END FAILED');
            }
        } catch (error) {
            console.error('BACKEND FAIL:', error);
        }
    }

    /**
     * Fetches Steam user data for a given user ID from a local API.
     * Logs the data to the console or logs an error if the request fails.
     * @param {string} userid - The Steam user ID.
     */
    async GetSteamUserData(userId) {
        var apiUrl = super.getApiUrl(this.UseLocalAPI);

        try {
            const response = await fetch(`${apiUrl}/api/steam/user/${userId}`, {
                method: 'GET',
            });

            // Traitement de la reponse
            if (response.ok) {
                const jsonData = await response.json(); // Parse the JSON and store it
                alert(userId + " = " + jsonData.response.players[0].personaname);
            } else {
                console.log('FRONT-END FAILED');
            }
        } catch (error) {
            console.error('BACKEND FAIL:', error);
        }
    }

    async verifyWithServer(token) {
        var apiUrl = super.getApiUrl(this.UseLocalAPI);

        try {
            console.log('Sending verification request to server...');
            const response = await fetch(`${apiUrl}/api/verifyHuman`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token })
            });

            const text = await response.text();
            console.log('Response body:', text);

            const data = JSON.parse(text);

            if (!response.ok) {
                throw new Error(data.error || `Server error: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('Server verification error:', error);
            throw error;
        }
    }

    async simulateBot() {
        var apiUrl = super.getApiUrl(this.UseLocalAPI);

        try {
            const response = await fetch(`${apiUrl}/api/simulateBot`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            showError(data.message);
            captchaV2Container.style.display = 'block';
        } catch (error) {
            showError('Error simulating bot behavior');
        }
    }
}

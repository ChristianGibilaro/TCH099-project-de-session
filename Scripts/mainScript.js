class mainScript extends globalVars {
    pageName = "";
    //False = localhost , true = server api
    //"http://162.243.167.200:9999";
    // "http://localhost:9999"

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

    // Set a cookie with an expiration time


    // Get the value of a cookie by name
    static getCookie(name) {
        const decodedCookies = decodeURIComponent(document.cookie);
        const cookies = decodedCookies.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.indexOf(name + '=') === 0) {
                return cookie.substring(name.length + 1);
            }
        }
        return null;
    }

    static setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000); // Convert days to milliseconds
        document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/;Secure;`;
    }


    async connecterUser(event) {
        event.preventDefault();
        const form = event.target.form;
        const formData = new FormData(form);
        var apiUrl = "http://localhost:9999";



        try {
            var url = `${apiUrl}/api/user/connect`;
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
            });

            // Traitement de la reponse
            if (response.ok) {
                const jsonData = await response.json(); // Parse the JSON and store it
                alert(jsonData.apiKey);
                //const result = await response.json();
                //form.reset(); // Reinitialiser le formulaire si la soummission est reussie.
                // Example usage
                mainScript.setCookie('lunarCovenantApikey',jsonData.apiKey , 1); // Store for 1 day
                const authKey = mainScript.getCookie('lunarCovenantApikey');
                console.log(authKey);
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
        console.log(formData);
        var apiUrl = "http://localhost:9999";

        try {
            const response = await fetch(`${apiUrl}/api/user/create`, {
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
        var apiUrl = "http://localhost:9999";

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
        var apiUrl = "http://localhost:9999";

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
        var apiUrl = "http://localhost:9999";

        try {
            console.log('Sending verification request to server...');
            const response = await fetch(`${apiUrl}/api/recaptcha/verifyHuman`, {
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
        var apiUrl = "http://localhost:9999";

        try {
            const response = await fetch(`${apiUrl}/api/recaptcha/simulateBot`, {
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

    
    async creerActivity(event) {
        event.preventDefault();
        const form = event.target.form; // Get the form element
        const formData = new FormData(form); // Create FormData from the form
    
        // --- Helper function to get selected checkbox values from custom dropdown ---
        const getSelectedValues = (containerId) => {
            const selectedValues = [];
            // Find the container div by its label's 'for' attribute or a specific ID if available
            const container = document.querySelector(`label[for="${containerId}"]`)?.closest('.input-group');
            if (container) {
                const checkboxes = container.querySelectorAll('.choiced input[type="checkbox"]:checked');
                checkboxes.forEach(checkbox => {
                    // Get the text content of the parent li, removing leading/trailing whitespace
                    const labelText = checkbox.closest('li.choice')?.textContent.trim();
                    if (labelText) {
                        selectedValues.push(labelText);
                    }
                });
            }
            return selectedValues;
        };
    
        // --- Get selected values from each custom dropdown ---
        const selectedPositions = getSelectedValues('positionID');
        const selectedLanguages = getSelectedValues('languageID');
        const selectedTypes = getSelectedValues('TypeID');
        const selectedEnvironments = getSelectedValues('EnvironementID'); // Corrected typo 'EnvironementID'
    
        // --- Append selected values as arrays (or multiple times for FormData) ---
        // FormData handles multiple appends with the same key, which is standard for multi-select/checkboxes
        selectedPositions.forEach(value => formData.append('positionIDs[]', value)); // Use '[]' if backend expects an array
        selectedLanguages.forEach(value => formData.append('languageIDs[]', value));
        selectedTypes.forEach(value => formData.append('TypeIDs[]', value));
        selectedEnvironments.forEach(value => formData.append('EnvironmentIDs[]', value)); // Corrected typo
    
        // Append API Key
        formData.append('apiKey', mainScript.getCookie('lunarCovenantApikey'));
    
        console.log(formData);
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
    
        var apiUrl = "http://localhost:9999";
    
        try {
            const response = await fetch(`${apiUrl}/api/activity/create`, {
                method: 'POST',
                body: formData, // Send the updated FormData
            });
    
            // Traitement de la reponse
            if (response.ok) {
                const jsonData = await response.json(); // Parse the JSON and store it
                alert('Activity Created Successfully:\n' + JSON.stringify(jsonData, null, 2)); // Pretty print JSON
                // Optionally redirect or clear form
                // window.location.href = 'ActivitiesList.html';
                // form.reset(); // Reset form fields
            } else {
                const errorData = await response.json().catch(() => ({ message: 'Failed to parse error response' }));
                console.error('FRONT-END: Echec creation nouvelle activité.', response.status, errorData);
                alert(`Failed to create activity: ${errorData.message || response.statusText}`);
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('Une erreur est survenue lors de la soummission. Check console for details.');
        }
    }
}

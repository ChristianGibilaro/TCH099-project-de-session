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

        // --- Temporarily remove name from inactive switchable inputs before creating FormData ---
        const switchableGroups = form.querySelectorAll('.switchable-input-group');
        const originalData = new Map(); // Store original name and disabled state

        switchableGroups.forEach(group => {
            const inputs = group.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                const switchableContent = input.closest('.switchable-content');
                // Check if the parent switchable content container is hidden
                const isHidden = switchableContent && window.getComputedStyle(switchableContent).display === 'none';

                if (isHidden) { // If the container is hidden, this input is inactive
                    if (input.name) {
                        // Store original data and remove name
                        originalData.set(input, { name: input.name, disabled: input.disabled });
                        input.removeAttribute('name');
                        // Optionally, ensure it's disabled too, though visibility check is primary here
                        // input.disabled = true;
                        // console.log(`Temporarily removed name from hidden input: ${originalData.get(input).name}`);
                    }
                } else if (switchableContent) {
                    // Ensure the active input is not disabled (it shouldn't be, but just in case)
                    // and store its state if we need to restore it later (though unlikely needed here)
                    // originalData.set(input, { name: input.name, disabled: input.disabled });
                    // input.disabled = false;
                }
            });
        });

        // --- Create FormData AFTER potentially modifying names ---
        const formData = new FormData(form);

        // --- Restore original names and disabled states to inactive inputs ---
        originalData.forEach((data, input) => {
            if (data.name) {
                input.setAttribute('name', data.name); // Restore the original name
            } else {
                input.removeAttribute('name'); // Ensure no name if it didn't have one
            }
            input.disabled = data.disabled; // Restore original disabled state
            // console.log(`Restored name/disabled for input: ${data.name}`);
        });
        originalData.clear(); // Clear the map

        // --- Helper function to get selected checkbox values from custom dropdown ---
        const getSelectedValues = (containerId) => {
            const selectedValues = [];
            const container = document.getElementById(containerId);
            if (container && container.classList.contains('bloc-filtre')) {
                const checkboxes = container.querySelectorAll('.choiced input[type="checkbox"]:checked');
                checkboxes.forEach(checkbox => {
                    const labelText = checkbox.closest('li.choice')?.textContent.trim();
                    if (labelText && labelText !== "--Clear--") {
                        selectedValues.push(labelText);
                    }
                });
            } else {
                console.warn(`Checklist container with ID "${containerId}" not found or is not a bloc-filtre.`);
            }
            return selectedValues;
        };

        // --- Get selected values from each custom dropdown ---
        const selectedPositions = getSelectedValues('positionID');
        const selectedLanguages = getSelectedValues('languageID');
        const selectedTypes = getSelectedValues('TypeID');
        const selectedEnvironments = getSelectedValues('EnvironementID');

        // --- Remove original text input values (now replaced by checklists) ---
        formData.delete('positionID');
        formData.delete('languageID');
        formData.delete('TypeID');
        formData.delete('EnvironementID');

        // --- Append selected checklist values ---
        selectedPositions.forEach(value => formData.append('positionIDs[]', value));
        selectedLanguages.forEach(value => formData.append('languageIDs[]', value));
        selectedTypes.forEach(value => formData.append('TypeIDs[]', value));
        selectedEnvironments.forEach(value => formData.append('EnvironmentIDs[]', value));

        // Append API Key
        formData.append('apiKey', mainScript.getCookie('lunarCovenantApikey'));

        // Log the final FormData content before sending
 

        var apiUrl = "http://localhost:9999";
        console.log(formData);
        try {
            const response = await fetch(`${apiUrl}/api/activity/create`, {
                method: 'POST',
                body: formData,
            });

            // Traitement de la reponse
            if (response.ok) {
                //const jsonData = await response.json();
                const raw = await response.text();
                //alert('Activity Created Successfully:\n' + JSON.stringify(jsonData, null, 2));
                console.log('Activity Created Successfully:', raw);
                // form.reset(); // Consider resetting the form
            } else {
                let errorData;
                try {
                    errorData = await response.json();
                } catch (e) {
                    errorData = { message: response.statusText };
                }
                console.error('FRONT-END: Echec creation nouvelle activité.', response.status, errorData);
                alert(`Failed to create activity: ${errorData.message || response.statusText}`);
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            alert('Une erreur est survenue lors de la soummission. Check console for details.');
        }
    }
}

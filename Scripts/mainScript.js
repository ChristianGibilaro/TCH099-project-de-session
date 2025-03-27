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

//---------------------------Accueil--------------------------------------------//
// Cette fonction affiche les activités populaire sur la page d'accueil.
function fonctionGetAPI_Exemple(id) {
    fetch(`http://localhost:9999/api/route1/${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            fonction_out(data);
        })
        .catch(error => {
            alert("Erreur!");
            throw new Error("Erreur");
        });
}

function fonction_out(activities) {
    //Utiliser les données fournie par l'api
}

function fonctionPUTAPI_Exemple(id) {

    fetch(`http://localhost:9999/api/route1/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object_A_Envoyer)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors de l'ajout");
            }
            return response.json();
        })
        .then(data => console.log('Succès:', data))
        .catch(error => {
            alert("Erreur!");
            throw new Error("Erreur");
    });
        
    alert("Une alerte");
    window.location.href = '/page2.html';
    
}

function fonctionPOSTAPI_Exemple(){

    fetch(`http://localhost:9999/api/route1`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object_A_Creer)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors de la création");
            }
            return response.json();
        })
        .then(data => console.log('Succès:', data))
        .catch(error => {
            alert("Erreur!");
            throw new Error("Erreur");
        });
}

function fonctionAPI_Multiparametre_Exemple(filters) {
    fetch(`http://localhost:9999/api/activities/filter?param1=${valeur1}&param2=${valeur2}&param3=${valeur3}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            //Do something with data;
        })
        .catch(error => {
            alert("Erreur!");
            throw new Error("Erreur");
        });

}

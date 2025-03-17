let host = null;

document.addEventListener("DOMContentLoaded", () => {
    const nomPage = document.title;

    console.log(nomPage);
    if (nomPage == 'Tp Accueil') {
        displayPopularActivities();
    } else if (nomPage == 'Liste des activites') {
        const liste = document.getElementsByClassName('list-activite');
        host = liste[0].getElementsByTagName('tbody')[0];
        populateFilters();

        const filters =
        {
            niveau: "tous",
            lieu: "tous",
            coach: "tous",
            jour: "tous"
        };  

        displayFilteredActivities(filters)
    } else if (nomPage == "modifier l'activite") {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        const id = params.get("id");
        populateForm(id);
        const button = document.getElementById('enregistrer');
        button.addEventListener('click', 
            event=> ModifActivite(id));
        document.getElementById('back').addEventListener('click', event => { 
            event.preventDefault();
            window.location.href = 'listeActivite.html';
        });
    }else if(nomPage == "AjoutActivite"){
        const button = document.getElementById('ajout');
        button.addEventListener('click',
             event=> CreateActivite());
        document.getElementById('back').addEventListener('click', event => { 
            event.preventDefault();
            window.location.href = 'listeActivite.html';});
    } else {
        console.log('Page non-contrôlé par mainScript.');
    }
});

//---------------------------Accueil--------------------------------------------//
// Cette fonction affiche les activités populaire sur la page d'accueil.
function displayPopularActivities() {
    fetch('http://localhost:9999/api/activities/random')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            renderPopularActivities(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

function renderPopularActivities(activities) {
    const wrappers = document.getElementsByClassName('image-wrapper');
    for (let i = 0; i < wrappers.length; i++) {
        const imageSport = wrappers[i].querySelector('img');
        imageSport.src = activities[i].image;
        const titre = wrappers[i].querySelector('H2');
        titre.textContent = activities[i].name;
        const conteneurTexte = wrappers[i].querySelector('p');
        conteneurTexte.textContent = activities[i].description;
    }
}

//---------------------------Liste Activite--------------------------------------------//
//Cette fonction populate les menus de modifierActivite.
function populateForm(id) {
    let activity;
    fetch(`http://localhost:9999/api/activities/${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            activity = data
            document.getElementById("nom").value = activity.name;
            document.getElementById("description").value = activity.description;    
            document.getElementById("image").value = activity.image;
        
            const level = document.getElementById("level");
            for (let i = 0; i < level.options.length; i++)
                if (level.options[i].text == activity.levelq_id)
                    level.selectedIndex = i;
        
        
            const location = document.getElementById("location");
            for (let i = 0; i < location.options.length; i++)
                if (location.options[i].text == activity.location_id)
                    location.selectedIndex = i;
        
        
            document.getElementById("coach").value = activity.coach_id;
            const date = document.getElementById("jour");
            for (let i = 0; i < date.options.length; i++)
                if (date.options[i].text == activity.schedule_day)
                    date.selectedIndex = i;
        
            document.getElementById("time").value = activity.schedule_time;
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}
//---------------------------Modification d'une activite--------------------------------------------//
function ModifActivite(id) {

    const niv = document.getElementById("level");
    const pos = document.getElementById("location");
    const day = document.getElementById("jour");

    const iName = document.getElementById("nom").value;
    const iDesc = document.getElementById("description").value;
    const iImage = document.getElementById("image").value;
    const iLevel = niv.options[niv.selectedIndex].text;
    const iLocation = pos.options[pos.selectedIndex].text;
    const iCoach = document.getElementById("coach").value;
    const iDate = day.options[day.selectedIndex].text;
    const iSchedule_time = document.getElementById("time").value;

    const activity = {
        "name": iName,
        "description" : iDesc,
        "image": iImage,
        "level_id": iLevel,
        "coach_id": iCoach,
        "schedule_day": iDate,
        "schedule_time": iSchedule_time,
        "location_id": iLocation
    };

    let emptyOrNull = false;
    for(let i in activity)
        if(activity[i] === null || activity[i] === "") {
            emptyOrNull = true;
            break;
        }
    
    if(!emptyOrNull){

    console.log(JSON.stringify(activity));

    fetch(`http://localhost:9999/api/activities/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(activity)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors de l'ajout d'activité");
            }
            return response.json();
        })
        .then(data => console.log('Succès:', data))
        .catch(error => {
            console.error('Erreur:', error);
        });
        window.location.href = '/listeActivite.html';
        alert("Activité " + activity["name"] + " modifié!");
    } else
    {
        alert("Les champs ne sont pas tous remplis");
    }
}

//---------------------------Creation d'une activite--------------------------------------------//  
function CreateActivite(){
    //Put un nouvelle activite. 

    const niv = document.getElementById("level");
    const pos = document.getElementById("location");
    const day = document.getElementById("jour");

    const iName = document.getElementById("nom").value;
    const iDesc = document.getElementById("description").value;
    const iImage = document.getElementById("image").value;
    const iLevel = niv.options[niv.selectedIndex].text;
    const iLocation = pos.options[pos.selectedIndex].text;
    const iCoach = document.getElementById("coach").value;
    const iDate = day.options[day.selectedIndex].text;
    const iSchedule_time = document.getElementById("time").value;

    const activity = {
        "name": iName,
        "description" : iDesc,
        "image": iImage,
        "level_id": iLevel,
        "coach_id": iCoach,
        "schedule_day": iDate,
        "schedule_time": iSchedule_time,
        "location_id": iLocation
    };

    let emptyOrNull = false;
    for(let i in activity)
        if(activity[i] === null || activity[i] === "") {
            emptyOrNull = true;
            break;
        }
    
    if(!emptyOrNull){
    console.log(activity);
    fetch(`http://localhost:9999/api/activities`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(activity)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur lors de l'ajout d'activité");
            }
            window.location.href = '/listeActivite.html';
            alert("Activité " + activity["name"] + " ajouté!");
            return response.json();
        })
        .then(data => console.log('Succès:', data))
        .catch(error => {
            alert("Désolé il semblerait que nous avons rencontré une erreur.");
            throw new Error("Erreur lors de l'ajout d'activité");
        });
    }else{
        alert("Les champs ne sont pas tous remplis");
    }
}

//---------------------------Creation Filtre--------------------------------------------//

const typeDeFiltre = ["Niveau", "Lieu", "Coach", "Horaire(Jour)"];

function populateFilters() {
    
    fetch(`http://localhost:9999/api/activities`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            createFilter(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

function createFilter(activities){

    const filtre = document.getElementById('critere');
    for (let i = 0; i < typeDeFiltre.length; i++) {
        const label = document.createElement('label'); //Cree le label
        label.textContent = typeDeFiltre[i];
        label.setAttribute('for', typeDeFiltre[i]);
        filtre.appendChild(label); //On peut append le label, car on n'as plus de modification a faire dessus.
        const select = document.createElement('select'); //Cree le select
        select.id = typeDeFiltre[i];
        select.name = typeDeFiltre[i];
        select.className = "filterSelect";
        //On cree l'option tous en premier
        const optTous = document.createElement('option');
        optTous.value = "tous";
        optTous.text = "tous";
        select.appendChild(optTous);
        let dejaAjouter = [];

        activities.forEach((activity) => {
            let textTest;
            switch (i) {
                case 0:
                    textTest = activity.level_id;
                    break;
                case 1:
                    textTest = activity.location_id;
                    break;
                case 2:
                    textTest = activity.coach_id;
                    break;
                case 3:
                    textTest = activity.schedule_day;
                    break;
            }
            if (!dejaAjouter.includes(textTest)) {
                const opt = document.createElement('option');
                opt.value = textTest;
                opt.text = textTest;
                dejaAjouter.push(textTest);
                select.appendChild(opt);
            }

        });

        filtre.appendChild(select);
    }
    //Cette partie permet juste d'ajouter la fin du form.
    const labHeure = document.createElement('label');
    labHeure.textContent = "Heure";
    labHeure.setAttribute('for', "Heure");
    filtre.appendChild(labHeure);
    const inputTemps = document.createElement('input');
    inputTemps.type = 'time';
    inputTemps.name = 'time';
    inputTemps.id = 'time';
    inputTemps.value = "12:30";
    filtre.appendChild(inputTemps);
    const bouttonFiltre = document.createElement('button');
    bouttonFiltre.id = 'filtrer';
    bouttonFiltre.textContent = "Filtrer";
    bouttonFiltre.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent default behavior if inside a form
        filtrerFromOptions();
    }
    );
    filtre.appendChild(bouttonFiltre);
}

//---------------------------Partie Activite--------------------------------------------//


function addUneActivite(activity) {

    let tr = document.createElement("tr");
    let td = document.createElement("td");
    tr.append(td);

    let activite = document.createElement("div");
    activite.className = "activite";
    td.append(activite);

    let img = document.createElement("div");
    img.className = "image";
    let image = document.createElement("img");
    image.src = activity.image;
    image.alt = "image loading failed";
    img.append(image);
    activite.append(img);

    let description = document.createElement("div");
    description.className = "desc";

    let h1 = document.createElement("h1");
    h1.textContent = activity.name;
    description.append(h1);

    let ul = document.createElement("ul");

    li(activity.description, ul);
    li(activity.level_id, ul);
    li(activity.coach_id, ul);
    li(activity.schedule_day, ul);
    li(activity.schedule_time, ul);
    li(activity.location_id, ul);

    ul.append(document.createElement("br"));

    let a = document.createElement("a");
    a.href = "modifierActivite.html?id=" + (activity.id);
    let button = document.createElement("button");
    button.textContent = "modifier l'activité";
    a.append(button);
    ul.append(a);

    description.append(ul);
    activite.append(description);

    return tr;
}

function li(data, host) {
    let li = document.createElement("li");
    li.textContent = data;
    host.append(li);

}

// affiche toutes les activitÃ©s filtrÃ©es pour la page des activitÃ©s
function displayFilteredActivities(filters) {
    fetch(`http://localhost:9999/api/activities/filter?niveau=${filters.niveau}&lieu=${filters.lieu}&coach=${filters.coach}&jour=${filters.jour}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            let activities;
            activities = data;
            host.innerHTML = "";
            activities.forEach((activity) => {
            host.append(addUneActivite(activity));
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
        });

}


function filtrerFromOptions() {
    const selectList = document.getElementsByClassName("filterSelect");
    console.log("Génération des filtres!");
    const filters =
    {
        niveau: selectList[0].value,
        lieu: selectList[1].value,
        coach: selectList[2].value,
        jour: selectList[3].value
    };
    displayFilteredActivities(filters);
}
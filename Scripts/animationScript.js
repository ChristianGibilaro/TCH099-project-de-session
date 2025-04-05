let isMenuOpen = false;
var r = document.querySelector(':root');
var rs = getComputedStyle(r);
var MenuClosedSize = rs.getPropertyValue('--MenuWidthClosed');
var MenuOpenedSize = rs.getPropertyValue('--MenuWidthOpened');
var Opacity = rs.getPropertyValue('--MenuOpacityMax');


function toggleMenu() {

    if(!isMenuOpen)
    {
        isMenuOpen = true;
        r.style.setProperty( '--MenuWidth', MenuOpenedSize);
        r.style.setProperty( '--MenuOpacity', Opacity);
        r.style.setProperty( '--MenuTextOpacity', 1);
        r.style.setProperty( '--DimmerClick', `auto`);

    }else{
        isMenuOpen = false;
        menu = document.getElementById("menu");
        r.style.setProperty('--MenuWidth', MenuClosedSize);
        r.style.setProperty( '--MenuOpacity', 0);
        r.style.setProperty( '--MenuTextOpacity', 0);
        r.style.setProperty( '--DimmerClick', `none`);
    }

}

// Fonctions pour afficher la section correspondante à l'onglet cliqué
function afficherSection(id, bouton) {
    // Cacher toutes les sections
    document.querySelectorAll('.contenu-onglet').forEach(section => {
        section.style.display = 'none';
    });

    // Afficher la section sélectionnée
    document.getElementById(id).style.display = 'block';

    // Mettre à jour l'onglet actif
    document.querySelectorAll('.barre-onglets .onglet').forEach(btn => {
        btn.classList.remove('actif');
    });

    bouton.classList.add('actif');
}





const filterSearch = document.getElementById('searchBar');
const list = document.getElementsByClassName('liste-scrollable');
const choices = document.getElementsByClassName('choice');

for(var i = 0; i < choices.length; i ++){
    choices[i].addEventListener('mouseover', function (option) {
      filterSearch.value = option.target.innerHTML;
    } );
}


filterSearch.addEventListener('focus', function() {
  list[0].style.display = 'block';
});

filterSearch.addEventListener('blur', function() {
  list[0].style.display = 'none';
});


// Fonction pour filtrer la liste en fonction de ce que l'utilisateur tape
document.addEventListener("DOMContentLoaded", function () {
    const champFiltre = document.getElementById('searchBar');
    const liste = document.querySelectorAll('#liste-pays li');
  
    champFiltre.addEventListener('input', function () {
      const valeur = champFiltre.value.trim().toLowerCase();
  
      liste.forEach(item => {
        const texte = item.textContent.trim().toLowerCase();
        if (texte.startsWith(valeur) || valeur === "") {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
  

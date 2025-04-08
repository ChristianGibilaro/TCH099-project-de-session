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


  
//Fonction pour afficher l'image ou la vidéo sélectionnée en grand
function changerMedia(element) {
  const container = document.getElementById("media-display");
  container.innerHTML = "";

  // Retirer les anciens "actif"
  document.querySelectorAll('.media-thumbnails img, .media-thumbnails video')
    .forEach(el => el.classList.remove('active-media'));

  // Ajouter la classe active à l'élément sélectionné
  element.classList.add('active-media');

  // Affichage principal
  if (element.tagName === "IMG") {
    const img = document.createElement("img");
    img.src = element.src;
    img.alt = "media";
    container.appendChild(img);
  } else if (element.tagName === "VIDEO") {
    const video = document.createElement("video");
    video.src = element.src;
    video.controls = true;
    video.autoplay = true;
    container.appendChild(video);
  }
}

//Fonction pour scroller
function scrollMediaThumbnails(direction) {
  const container = document.getElementById("media-thumbnails");
  const scrollAmount = 200; // px

  container.scrollBy({
    left: direction * scrollAmount,
    behavior: 'smooth'
  });
}

document.querySelectorAll('.media-thumbnails video').forEach(video => {
  video.disablePictureInPicture = true;
});

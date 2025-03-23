var expand = document.getElementById('expand_navi_btn');
var expand_image = document.getElementById('expand_icon');
expand.addEventListener('click', function() {
    console.log("Le boutton a ete clicke");
    expand_image.classList.toggle('pivoter_img');
    var navi_bar = document.getElementsByClassName('side_bar_nav_gauche')
    navi_bar[0].classList.toggle('elargir');
    var text_navi = document.getElementsByClassName('show_texte_navi_on_extend');
    for (var i = 0; i < text_navi.length; i++) {
        text_navi[i].classList.toggle('visible');
    }
});
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
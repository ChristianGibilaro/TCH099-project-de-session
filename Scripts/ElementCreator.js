//import './steamApiKey.js'
// A fix: les docs des functions et images IDS
class ElementCreator {
    scrollableListeID = 0;
    imageID = 0;
    tabletitlesID = 0;
    tableRowID = 0;
    sideBySideID = 0;

    pageName = "";

    constructor(pageName) {
        this.pageName = pageName;
        console.log("Creator js script initialized for page " + pageName);
    }

    GetSteamGameData(appid){
        fetch(`http://localhost:9999/api/steam/game/${appid}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }

    GetSteamUserData(userid){
        fetch(`http://localhost:9999/api/steam/user/${userid}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des activités');
            }
            return response.json();
        })
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }


    /**
    * @param {String}   baseId      the base for the id ex: "scrollableTable"
    * @param {String}   id          id to apply or null for automatic Id generation
    */
    IdGenerator(baseId, id, index) {
        if (id != null) {
            return 'id = "' + id + '"';;
        } else {
            return 'id = "' + baseId + '#' + index + '"';
        }
    }

    /**
    * @param {String}   width       in px or %. ex: "90px" or null for 100%
    * @param {String}   height      in px. ex: "10px"
    * @param {Int}      colNumb     number of columns. ex: 3
    * @param {String[]} titles      like this one : [texte', "Texte", "Long texte"]
    * @param {String[][]}elements   like this one :[['texte', "Texte", "Long texte"],['texte', "Texte", "Long texte"]]
    * @param {String}   id          apply id to the main dix ex: "liste" or put nothing to generate a id.
    * @param {String}   classes     apply more classes to the main div ex: "red rotated" or null to apply the defeault class: generatedScrollableTable
    * @param {String}   extraStyle  apply more styles to the main div ex: "margin: 0 auto;"
    */
    CreateScrollableTable(width, height, titles, elements, id, classes, extraStyle) {


        var styleWidth = "";
        if (width != null) {
            styleWidth = "width: " + width + ";"
        } else {
            styleWidth = "width: 100%;"
        }

        var styleHeight = "";
        if (height != null) {
            styleHeight = "height: " + height + ";"
        } else {
            console.warn("height must be defined");
            return document.write("height must be defined");
        }

        if (classes == null) {
            classes = "generatedScrollableTable";
        }

        if (extraStyle == null) {
            extraStyle = "";
        }

        this.scrollableListeID++;
        var styleId = this.IdGenerator("ScrollableTable", id, this.scrollableListeID);

        console.log("ScrollableTable was sucessfully created with " + styleId + " !");

        return '\
        <div style="' + styleWidth + extraStyle + ' " class="listeScrollable ' + classes + '"' + styleId + '>' + '\
            <table style>\
                <thead>' +
            titles +
            '</thead>\
                <tbody  style="' + styleHeight + ';">' +
            elements +
            '</tbody>\
            </table>\
        </div>';

    }

    /**
    * @param {String}   width       in px or %. ex: "90px"  or null for 100%
    * @param {String}   height      in px or %. ex: "10px" or null for 100%
    * @param {String}   src         image emplacement. ex: "ressources/Commun/user_profile_image_example.png"
    * @param {String}   alt         alternative texte ex: "image"
    * @param {String}   id          apply id to the main dix ex: "liste" or null to generate a id.
    * @param {String}   classes     apply more classes to the main div ex: "red rotated" or null to apply the defeault class: generatedImage
    * @param {String}   extraStyle  apply more styles to the main div ex: "margin: 0 auto;"
    */
    CreateImage(width, height, src, alt, id, classes, extraStyle) {

        var styleWidth = "";
        if (width != null) {
            styleWidth = "width: " + width + ";"
        } else {
            styleWidth = "width: 100%;"
        }

        var styleHeight = "";
        if (height != null) {
            styleHeight = "height: " + height + ";"
        } else {
            styleHeight = "height: 100%;"
        }

        if (classes == null) {
            classes = "generatedImage";
        }

        if (extraStyle == null) {
            extraStyle = "";
        }

        this.imageID++;
        var styleId = this.IdGenerator("Image", id, this.imageID);

        console.log("Image (w: " + width + " h: " + height + ") was sucessfully created with " + styleId + " !");

        return '<img src = "' + src + '"' + ' style="' + styleHeight + styleWidth + extraStyle + '" class="' + classes + '"' + styleId + 'alt="' + alt + '" >'
    }

    /**
    * @param {String}   width       in px or %. ex: "90px" or null for 100%
    * @param {String}   height      in px. ex: "10px"
    * @param {Int}      colNumb     number of columns. ex: 3
    * @param {String[]} titles      like this one : [texte', "Texte", "Long texte"]
    * @param {String[][]}elements   like this one :[['texte', "Texte", "Long texte"],['texte', "Texte", "Long texte"]]
    * @param {String}   id          apply id to the main dix ex: "liste" or put nothing to generate a id.
    * @param {String}   classes     apply more classes to the main div ex: "red rotated" or null to apply the defeault class: generatedScrollableTable
    * @param {String}   extraStyle  apply more styles to the main div ex: "margin: 0 auto;"
    */
    CreateTableTitles(titles, titleSize, colNumb, id, classes, extraStyle) {

        if (titleSize != null) {
            var checkWidth = 0;
            for (var i = 0; i < titleSize.length; i++) {
                checkWidth += titleSize[i];
            }
            if (checkWidth != 100) {
                console.warn("titleSize sum of all values must be == 100");
                return document.write("titleSize sum of all values must be == 100");
            }
        }


        if (titles.length == colNumb) {

            var outTitles = "";
            for (var i = 0; i < titles.length; i++) {
                outTitles += '<th style= "width: ' + titleSize[i] + '%;">' + titles[i] + '</th>';
            }

        } else {
            console.warn("title.lenght must be == colnumb");
            return document.write("title.lenght must be == colnumb");
        }

        if (classes == null) {
            classes = "generatedTableTitles";
        }

        if (extraStyle == null) {
            extraStyle = "";
        }

        this.tableRowID++;
        var styleId = this.IdGenerator("TableTitles", id, this.tableRowID);

        console.log("TableTitles (col: " + titles.length + ") was sucessfully created with " + styleId + " !");

        return '\
        <tr style="' + extraStyle + ' " class="' + classes + '"' + styleId + '>' +
            outTitles +
            '</tr>';
    }

    /**
    * @param {String}   width       in px or %. ex: "90px" or null for 100%
    * @param {String}   height      in px. ex: "10px"
    * @param {Int}      colNumb     number of columns. ex: 3
    * @param {String[]} titles      like this one : [texte', "Texte", "Long texte"]
    * @param {String[][]}elements   like this one :[['texte', "Texte", "Long texte"],['texte', "Texte", "Long texte"]]
    * @param {String}   id          apply id to the main dix ex: "liste" or put nothing to generate a id.
    * @param {String}   classes     apply more classes to the main div ex: "red rotated" or null to apply the defeault class: generatedScrollableTable
    * @param {String}   extraStyle  apply more styles to the main div ex: "margin: 0 auto;"
    */
    CreateTableRows(elements, elementsType, elementsSize, colNumb, id, classes, extraStyle) {

        if (elementsSize != null) {
            var checkWidth = 0;
            for (var i = 0; i < elementsSize.length; i++) {
                checkWidth += elementsSize[i];
            }
            if (checkWidth != 100) {
                console.warn("elememtsSize sum of all values must be == 100");
                return document.write("elememtsSize sum of all values must be == 100");
            }
        }

        if (classes == null) {
            classes = "generatedTableRows";
        }

        if (extraStyle == null) {
            extraStyle = "";
        }

        this.tabletitlesID++;
        var styleId = this.IdGenerator("TableRow", id, this.tabletitlesID);


        var outRows = "";
        var valid = -1;

        for (var i = 0; i < elements.length; i++) {
            outRows += '<tr style="' + extraStyle + ' " class="' + classes + '"' + styleId + '>';
            if (elements[i].length != colNumb) {
                valid = i;
                break;
            }//[["img","100px","100px","image_failed","FillImage","object-position: 1% 40%;"],["txt"],["txt"]];
            for (var j = 0; j < elements[i].length; j++) {
                switch (elementsType[j][0]) {
                    case "img":
                        outRows += '<td style= "width: ' + elementsSize[j] + '%;">' +
                            this.CreateImage(elementsType[j][1], elementsType[j][2], elements[i][j], elementsType[j][3], null, elementsType[j][4], elementsType[j][5]) + '</td>';
                        break;
                    case "txt":
                        outRows += '<td style= "width: ' + elementsSize[j] + '%;">' + elements[i][j] + '</td>';
                        break;

                }
            }
            outRows += '</tr>';
        }

        if (valid != -1) {
            console.warn("the row #" + valid + " .lenght != colNumb");
            return document.write("the row #" + valid + " .lenght != colNumb");
        }

        console.log("Table Rows (row: " + elements.length + " col: " + elements[0].length + ") was sucessfully created with " + styleId + " !");
        return outRows;

    }

    SideBySide(width, height, id, classes, extraStyle, ...element) {

        if (width != null) {
            width = "width: " + width + ";"
        } else {
            width = ""
        }

        if (height != null) {
            height = "height: " + height + ";"
        } else {
            height = ""
        }

        if (classes == null) {
            classes = "generatedSideBySide";
        }

        if (extraStyle == null) {
            extraStyle = "";
        }

        this.sideBySideID++;
        var styleId = this.IdGenerator("SideBySide", id, this.sideBySideID);

        var outDiv = '<div class = "SideBySide ' + classes + '" style = "' + extraStyle + height + width + '" id="' + styleId + '">'
        for (var i = 0; i < element.length; i++) {
            outDiv += element[i];
        }
        outDiv += "</div>";

        console.log("side by Side was sucessfully created with " + styleId + " !");

        return outDiv;
    }

    CreateMenu(head, topOptions, bottomOptions) {
        var out = '\
        <table>\
            <thead id="title">\
                <th class="icon"><img src="' + head[0] + '"></th>\
                <th class="logo FillImage"><a href="Main.html"><img src="' + head[1] + '"></a></th>\
            </thead>\
            <tbody>';

        for (var i = 0; i < topOptions.length; i++) {
            out += '<tr class="menuRow">';

            out += '<td class="icon FillImage"> <div>' +
                this.CreateImage("", "", topOptions[i][0], "loading failed", null,null , null) + '</div> </td>';

            out += '<td class="menuOptions menuButton"><a style= "height: 100%; width:100%; display:table;" href = "' + topOptions[i][2] + '"> ' + topOptions[i][1] + '</a></td>';
            out += '</tr>';
        }
        out += '\
            </tbody>\
        </table>\
        \
        <div id="bottomMenu">\
            <table>\
                <tbody>';


        for (var i = 0; i < bottomOptions.length; i++) {
            out += '<tr class="menuRow">';

            out += '<td class="icon FillImage"> <div>' +
                this.CreateImage("", "", bottomOptions[i][0], "loading failed", null,null , null) + '</div> </td>';

            out += '<td class="menuOptions menuButton"><a style= "height: 100%; width:100%; display:table;" href = "' + bottomOptions[i][2] + '"> ' + bottomOptions[i][1] + '</a></td>';
            out += '</tr>';
        }

        out+='\
                </tbody>\
            </table>\
        </div>'

        console.log("side by Side was sucessfully created with " + " !");

        return out;
    }

    CreateDesc(descLeft,descRight){
        return '\
        <div id="description" class="description">\
            <div class="left-text">' +
                descLeft +
            '</div>\
            <div class="right-text">' +
                descRight +
            '</div>\
        </div>';
    }

    CreateHero(hero,logo){
        return '\
        <div id="banniere">\
            <div id="logo">\
                <img src="' + logo + '" alt="logo">\
            </div>\
        <img src="' + hero + '" alt="banniere">\
        </div>'
    }

    PutInDiv(child, id, classes, extraStyle){
        return '<div id="' + id + '" style = "' + extraStyle + '" class = "' + classes + '">' + child + '</div>'
    }
}

/*
  <div id="contenu">
    <!-- Baniere -->


    <!-- Description -->
    <div id="description" class="description">
      <div class="left-text">
        Ceci est-un texte (Gauche)
      </div>
      <div class="right-text">
        Ceci est-un texte (Droite)
      </div>
    </div>

  */
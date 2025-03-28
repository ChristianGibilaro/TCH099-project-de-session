
class ElementCreator{
    
    scrollableListeID = 0;
    imageID = 0;
    ids = [];
    pageName = ""

    constructor (pageName) {
        this.pageName = pageName;
        console.log("Creator js script initialized for page " + pageName);
    }

    /**
    * @param {String}   baseId      the base for the id ex: "scrollableTable"
    * @param {String}   id          id to apply or null for automatic Id generation
    */
    IdGenerator(baseId,id,index) {
        if (id != null) {
            return 'id = "' + id + '"'; ;
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
    CreateScrollableTable (width,height,colNumb,titles,elements,id,classes,extraStyle){
        var outTitles = "";
        var outRows = "";

        if(titles.length == colNumb){
            
            for(var i = 0; i < titles.length; i++){
                outTitles += '<th>'+titles[i]+'</th>';
            }

        }else{
            console.warn("title.lenght must be == colnumb");
            return document.write("title.lenght must be == colnumb");
        }

        var valid = -1;

        for(var i = 0; i < elements.length; i++){
            if(elements[i].length != colNumb){
                valid = i;
                break;
            }
            outRows += '<tr>';
            for(var j = 0; j < elements[i].length; j++){
                outRows += '<td>'+ elements[i][j] + '</td>';
            }
            outRows += '</tr>';
        }

        if(valid != -1){
            console.warn("the row #" + valid + " .lenght != colNumb");
            return document.write("the row #" + valid + " .lenght != colNumb");
        }
        
        var styleWidth = "";
        if(width != null){
            styleWidth = "width: " + width + ";"
        }else{
            styleWidth = "width: 100%;"
        }
    
        var styleHeight = "";
        if(height != null){
            styleHeight = "height: " + height + ";"
        }else{
            console.warn("height must be defined");
            return document.write("height must be defined");
        }

        if(classes == null){
            classes = "generatedScrollableTable";
        }

        if(extraStyle == null){
            extraStyle = "";
        }

        this.scrollableListeID++;
        var styleId = this.IdGenerator("scrollableTable",id,this.scrollableListeID);

            console.log("ScrollableTable " + elements.length + "x" + elements[0].length + " was sucessfully created with " + styleId + " !");

        return '\
        <div style="' + styleWidth + extraStyle + '" class="listeScrollable ' + classes + '"' + styleId + '>' +'\
            <table>\
                <thead>\
                <tr>'+
                outTitles +
                '</tr>\
                </thead>\
                <tbody  style="' + styleHeight +';">'+
                outRows +
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
    CreateImage (width,height,src,alt,id,classes,extraStyle){

        var styleWidth = "";
        if(width != null){
            styleWidth = "width: " + width + ";"
        }else{
            styleWidth = "width: 100%;"
        }
    
        var styleHeight = "";
        if(height != null){
            styleHeight = "height: " + height + ";"
        }else{
            styleHeight = "height: 100%;"
        }

        if(classes == null){
            classes = "generatedImage";
        }

        if(extraStyle == null){
            extraStyle = "";
        }

        this.imageID++;
        var styleId = this.IdGenerator("Image",id,this.imageID);

            console.log("Image " + width + "x" + height + " was sucessfully created with " + styleId + " !");

        return '<img src = "' + src + '"' + ' style="' + styleHeight + styleWidth + extraStyle + '" class="' + classes + '"' + styleId + 'alt="' + alt + '" >'
    }
}

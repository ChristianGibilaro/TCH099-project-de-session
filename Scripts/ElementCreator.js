
class ElementCreator{
    
    scrollableListeID = 0;
    pageName = ""

    constructor (pageName) {
        this.pageName = pageName;
        console.log("Creator js script initialized for page " + pageName);
    }

    /**
    * @param {String}   baseId      the base for the id ex: "scrollableTable"
    * @param {boolean}  id          true to require the id
    */
    IdGenerator(baseId,id) {
        if (id == false) {
            return "";
        } else {
            this.scrollableListeID++;
            return 'id = "' + baseId + '#' + this.scrollableListeID + '"';
        }
    }

    /**
    * @param {String}   width       in px or %. ex: "90px"
    * @param {String}   height      in px. ex: "10px"
    * @param {Int}      colNumb     number of columns. ex: 3
    * @param {String[]} titles      like this one : [texte', "Texte", "Long texte"]
    * @param {String[][]}elements   like this one :[['texte', "Texte", "Long texte"],['texte', "Texte", "Long texte"]]
    * @param {boolean}  id          true to generate an id
    * @param {String}   classes     apply more classes to the main div ex: "red rotated"
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
            styleHeight = "height: 100%;"
        }

        var styleId = this.IdGenerator("scrollableTable",id);

        if(id){
            console.log("ScrollableTable " + elements.length + "x" + elements[0].length + " was sucessfully created with " + styleId + " !");
        }else{
            console.log("ScrollableTable " + elements.length + "x" + elements[0].length + " was sucessfully created!");
        }

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




}

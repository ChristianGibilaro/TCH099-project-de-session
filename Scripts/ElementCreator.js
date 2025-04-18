/**
 * A class for dynamically creating HTML elements for a web page.
 */
class ElementCreator {
    /**
     * Counter for generating unique IDs for scrollable lists.
     * @private
     */
    scrollableListCounter = 0;

    /**
     * Counter for generating unique IDs for images.
     * @private
     */
    imageCounter = 0;

    /**
     * Counter for generating unique IDs for table titles.
     * @private
     */
    tableTitlesCounter = 0;

    /**
     * Counter for generating unique IDs for table rows.
     * @private
     */
    tableRowCounter = 0;

    /**
     * Counter for generating unique IDs for side-by-side divs.
     * @private
     */
    sideBySideCounter = 0;

    /**
     * The name of the current page.
     * @private
     * @type {string}
     */
    pageName = "";

    /**
     * Constructor for the ElementCreator class.
     * Initializes the page name and logs a message.
     * @param {string} pageName - The name of the page where the elements will be created.
     */
    constructor(pageName) {
        this.pageName = pageName;
        console.log(`Creator js script initialized for page ${pageName}`);
    }

    /**
     * Generates an HTML ID attribute string.
     * If an ID is provided, it uses that; otherwise, it generates a unique ID based on the base ID and a counter.
     * @private
     * @param {string} baseId - The base string for generating an ID (e.g., "scrollableTable").
     * @param {string|null} id - The specific ID to use, or null to generate one.
     * @param {number} index - The index to append to the base ID if no specific ID is provided.
     * @returns {string} The HTML ID attribute string (e.g., 'id="scrollableTable#1"').
     */
    IdGenerator(baseId, id, index) {
        if (id != null) {
            return id;
        } else {
            return `${baseId}#${index}`;
        }
    }

    /**
     * Creates an HTML img element.
     * @param {string|null} width - The width of the image in px or %, or null for 100%.
     * @param {string|null} height - The height of the image in px or %, or null for 100%.
     * @param {string} src - The source URL of the image.
     * @param {string} alt - The alternative text for the image.
     * @param {string|null} id - An optional ID to apply to the image element. If null, an ID will be generated.
     * @param {string|null} classes - Optional additional CSS classes to apply to the image element. Defaults to "generatedImage".
     * @param {string|null} extraStyle - Optional additional inline styles to apply to the image element.
     * @returns {string} The HTML string for the image element.
     */
    CreateImage(width, height, src, alt, id, classes, extraStyle) {
        let styleWidth = "";
        if (width != null) {
            styleWidth = `width: ${width};`;
        } else {
            styleWidth = "width: 100%;";
        }

        let styleHeight = "";
        if (height != null) {
            styleHeight = `height: ${height};`;
        } else {
            styleHeight = "height: 100%;";
        }

        const cssClasses = classes || "generatedImage";
        const inlineStyle = extraStyle || "";

        this.imageCounter++;
        const generatedId = this.IdGenerator("Image", id, this.imageCounter);

        return `<img src="${src}" style="${styleHeight}${styleWidth}${inlineStyle}" class="${cssClasses}" ${generatedId} alt="${alt}">`;
    }

 /**
     * Creates a scrollable list using divs instead of tables.
     * @param {string|null} width - The width of the container.
     * @param {string|null} height - The height of the scrollable area.
     * @param {string|null} titles - The HTML string for the header row (can be divs).
     * @param {string|null} elements - The HTML string for the rows (can be divs).
     * @param {string|null} id - Optional ID for the main container.
     * @param {string|null} classes - Additional CSS classes.
     * @param {string|null} extraStyle - Inline styles.
     * @returns {string} The HTML string for the scrollable list.
     */
 CreateScrollableTable(width, height, titles, elements, id, classes, extraStyle) {
    let styleWidth = width ? `width: ${width};` : "width: 100%;";
    let styleHeight = height ? `height: ${height};` : "height: 100%;";
    const cssClasses = classes || "generatedScrollableTable";
    const inlineStyle = extraStyle || "";

    this.scrollableListCounter++;
    const generatedId = this.IdGenerator("ScrollableTable", id, this.scrollableListCounter);

    return `
    <div style="${styleWidth}${inlineStyle}" class="listeScrollable ${cssClasses}" id="${generatedId}">
        ${titles ? `<div class="scrollable-header">${titles}</div>` : ""}
        <div class="scrollable-body" style="${styleHeight}">${elements || ''}</div>
    </div>`;
}

/**
 * Creates the header row using divs.
 * @param {string[]} titles - Array of header titles.
 * @param {number[]} titleSize - Array of width percentages.
 * @param {number} colNumb - Number of columns.
 * @param {string|null} id - Optional ID.
 * @param {string|null} classes - Additional CSS classes.
 * @param {string|null} extraStyle - Inline styles.
 * @returns {string} The HTML string for the header row.
 */
CreateTableTitles(titles, titleSize, colNumb, id, classes, extraStyle) {
    if (titleSize && titleSize.reduce((sum, size) => sum + size, 0) !== 100) {
        console.warn("titleSize sum of all values must be == 100");
        return "titleSize sum of all values must be == 100";
    }
    if (titles.length !== colNumb) {
        console.warn("titles.length must be equal to colNumb");
        return "titles.length must be equal to colNumb";
    }
    const cssClasses = classes || "generatedTableTitles";
    const inlineStyle = extraStyle || "";

    this.tableRowCounter++;
    const generatedId = this.IdGenerator("TableTitles", id, this.tableRowCounter);

    let outTitles = "";
    for (let i = 0; i < titles.length; i++) {
        outTitles += `<div class="scrollable-title-cell" style="width: ${titleSize[i]}%;">${titles[i]}</div>`;
    }
    return `<div class="scrollable-title-row ${cssClasses}" id="${generatedId}" style="${inlineStyle}">${outTitles}</div>`;
}

 /**
     * Generates an HTML ID attribute string.
     * If an ID is provided, it uses that; otherwise, it generates a unique ID based on the base ID and a counter.
     * @private
     * @param {string} baseId - The base string for generating an ID (e.g., "scrollableTable").
     * @param {string|null} id - The specific ID to use, or null to generate one.
     * @param {number} index - The index to append to the base ID if no specific ID is provided.
     * @returns {string} The HTML ID attribute string (e.g., 'id="scrollableTable#1"').
     */
 IdGenerator(baseId, id, index) {
    if (id != null) {
        return id;
    } else {
        return `${baseId}#${index}`;
    }
}

/**
 * Creates a scrollable list using divs instead of tables.
 * @param {string|null} width - The width of the container.
 * @param {string|null} height - The height of the scrollable area.
 * @param {string|null} titles - The HTML string for the header row (can be divs).
 * @param {string|null} elements - The HTML string for the rows (can be divs).
 * @param {string|null} id - Optional ID for the main container.
 * @param {string|null} classes - Additional CSS classes.
 * @param {string|null} extraStyle - Inline styles.
 * @returns {string} The HTML string for the scrollable list.
 */
CreateScrollableTable(width, height, titles, elements, id, classes, extraStyle) {
    let styleWidth = width ? `width: ${width};` : "width: 100%;";
    let styleHeight = height ? `height: ${height};` : "height: 100%;";
    const cssClasses = classes || "generatedScrollableTable";
    const inlineStyle = extraStyle || "";

    this.scrollableListCounter++;
    const generatedId = this.IdGenerator("ScrollableTable", id, this.scrollableListCounter);

    return `
    <div style="${styleWidth}${inlineStyle}" class="listeScrollable ${cssClasses}" id="${generatedId}">
        ${titles ? `<div class="scrollable-header">${titles}</div>` : ""}
        <div class="scrollable-body" style="${styleHeight}">${elements || ''}</div>
    </div>`;
}

/**
 * Creates the header row using divs.
 * @param {string[]} titles - Array of header titles.
 * @param {number[]} titleSize - Array of width percentages.
 * @param {number} colNumb - Number of columns.
 * @param {string|null} id - Optional ID.
 * @param {string|null} classes - Additional CSS classes.
 * @param {string|null} extraStyle - Inline styles.
 * @returns {string} The HTML string for the header row.
 */
CreateTableTitles(titles, titleSize, colNumb, id, classes, extraStyle) {

    const cssClasses = classes || "generatedTableTitles";
    const inlineStyle = extraStyle || "";

    this.tableRowCounter++;
    const generatedId = this.IdGenerator("TableTitles", id, this.tableRowCounter);

    let outTitles = "";
    for (let i = 0; i < titles.length; i++) {
        outTitles += `<div class="scrollable-title-cell" style="width: ${titleSize[i]}%;">${titles[i]}</div>`;
    }
    return `<div class="scrollable-title-row ${cssClasses}" id="${generatedId}" style="${inlineStyle}">${outTitles}</div>`;
}

/**
 * Creates the data rows using divs.
 * @param {string[][]} elements - 2D array of row data.
 * @param {Array<string[]>} elementsType - Type of each cell.
 * @param {number[]} elementsSize - Width percentages for each cell.
 * @param {number} colNumb - Number of columns.
 * @param {string|null} id - Optional ID.
 * @param {string|null} classes - Additional CSS classes.
 * @param {string|null} extraStyle - Inline styles.
 * @returns {string} The HTML string for the data rows.
 */
CreateTableRows(elements, elementsType, elementsSize, colNumb, id, classes, extraStyle) {

    const cssClasses = classes || "generatedTableRows";
    const inlineStyle = extraStyle || "";

    this.tableTitlesCounter++;
    const generatedId = this.IdGenerator("TableRow", id, this.tableTitlesCounter);

    let outRows = "";
    let invalidRowIndex = -1;

    for (let i = 0; i < elements.length; i++) {
        if (elements[i].length !== colNumb) {
            invalidRowIndex = i;
            break;
        }
        let row = "";
        for (let j = 0; j < elements[i].length; j++) {
            switch (elementsType[j][0]) {
                case "img":
                    row += `<div class="scrollable-cell" style="width: ${elementsSize[j]}%;">${this.CreateImage(elementsType[j][1], elementsType[j][2], elements[i][j], elementsType[j][3], null, elementsType[j][4], elementsType[j][5])}</div>`;
                    break;
                case "txt":
                    row += `<div class="scrollable-cell" style="width: ${elementsSize[j]}%;">${elements[i][j]}</div>`;
                    break;
            }
        }
        outRows += `<div class="scrollable-row ${cssClasses}" id="${generatedId}" style="${inlineStyle}">${row}</div>`;
    }

    if (invalidRowIndex !== -1) {
        console.warn(`The row #${invalidRowIndex} .length != colNumb`);
        return `The row #${invalidRowIndex} .length != colNumb`;
    }

    return outRows;
}

    /**
     * Creates an HTML div containing elements displayed side by side using flexbox.
     * @param {string|null} width - The width of the container div in px or %, or null for no width style.
     * @param {string|null} height - The height of the container div in px or %, or null for no height style.
     * @param {string|null} id - An optional ID to apply to the container div. If null, an ID will be generated.
     * @param {string|null} classes - Optional additional CSS classes to apply to the container div. Defaults to "generatedSideBySide".
     * @param {string|null} extraStyle - Optional additional inline styles to apply to the container div.
     * @param {...string} element - One or more HTML strings representing the elements to be placed side by side.
     * @returns {string} The HTML string for the side-by-side container.
     */
    SideBySide(width, height, id, classes, extraStyle, ...element) {
        const styleWidth = width != null ? `width: ${width};` : "";
        const styleHeight = height != null ? `height: ${height};` : "";
        const cssClasses = classes || "generatedSideBySide";
        const inlineStyle = extraStyle || "";

        this.sideBySideCounter++;
        const generatedId = this.IdGenerator("SideBySide", id, this.sideBySideCounter);

        let outDiv = `<div class="SideBySide ${cssClasses}" style="${inlineStyle}${styleHeight}${styleWidth}" id="${generatedId}">`;
        for (let i = 0; i < element.length; i++) {
            outDiv += element[i];
        }
        outDiv += "</div>";


        return outDiv;
    }

    /**
     * Creates an HTML structure for a menu, typically placed in the header.
     * @param {string[]} head - An array containing the source URLs for the header icons: [main logo, secondary logo].
     * @param {Array<string[]>} topOptions - An array of arrays, where each inner array defines a top menu item: [icon URL, text, link URL].
     * @param {Array<string[]>} bottomOptions - An array of arrays, where each inner array defines a bottom menu item: [icon URL, text, link URL].
     * @returns {string} The HTML string for the menu.
     */
    CreateMenu(head, topOptions, bottomOptions) {
        //HELLO GITHUB?!?
        let out = `
        <table>
            <thead id="title">
                <th class="icon"><img src="${head[0]}"></th>
                <th class="logo FillImage"><a href="Main.html"><img src="${head[1]}"></a></th>
            </thead>
            <tbody>`;

        for (let i = 0; i < topOptions.length; i++) {
            out += '<tr class="menuRow">';
            out += `<td class="icon FillImage"> <div>${this.CreateImage("", "", topOptions[i][0], "loading failed", null, null, null)}</div> </td>`;
            out += `<td class="menuOptions menuButton"><a style="height: 100%; width:100%; display:table;" href="${topOptions[i][2]}"> ${topOptions[i][1]}</a></td>`;
            out += '</tr>';
        }
        out += `
            </tbody>
        </table>

        <div id="bottomMenu">
            <table>
                <tbody>`;

        for (let i = 0; i < bottomOptions.length; i++) {
            out += '<tr class="menuRow">';
            out += `<td class="icon FillImage"> <div>${this.CreateImage("", "", bottomOptions[i][0], "loading failed", null, null, null)}</div> </td>`;
            out += `<td class="menuOptions menuButton"><a style="height: 100%; width:100%; display:table;" href="${bottomOptions[i][2]}"> ${bottomOptions[i][1]}</a></td>`;
            out += '</tr>';
        }

        out += `
                    </tbody>
                </table>
        </div>`;

        return out;
    }

    /**
     * Creates an HTML div for a description with left and right aligned text.
     * @param {string} descLeft - The text content for the left side of the description.
     * @param {string} descRight - The text content for the right side of the description.
     * @returns {string} The HTML string for the description div.
     */
    CreateDesc(descLeft, descRight) {
        return `
        <div id="description" class="description">
            <div class="left-text">${descLeft}</div>
            <div class="right-text">${descRight}</div>
        </div>`;
    }

    /**
     * Creates an HTML div for a hero banner with a logo overlay.
     * @param {string} hero - The source URL for the hero banner image.
     * @param {string} logo - The source URL for the logo image.
     * @returns {string} The HTML string for the hero banner.
     */
    CreateHero(hero, logo) {
        return `
        <div id="banniere">
            <div id="logo">
                <img src="${logo}" alt="logo">
            </div>
        <img src="${hero}" alt="banniere">
        </div>`;
    }

    /**
     * Creates a simple HTML div to wrap other content.
     * @param {string} child - The HTML string representing the content to be placed inside the div.
     * @param {string|null} id - An optional ID to apply to the div.
     * @param {string|null} classes - Optional CSS classes to apply to the div.
     * @param {string|null} extraStyle - Optional inline styles to apply to the div.
     * @returns {string} The HTML string for the div.
     */
    PutInDiv(child, id, classes, extraStyle) {
        return `<div id="${id || ''}" style="${extraStyle || ''}" class="${classes || ''}">${child}</div>`;
    }

    /**
     * Creates an HTML div containing a grid of elements.
     * @param {Array<string[]>} elements - An array of arrays, where each inner array defines an element in the grid.
     * Each inner array should contain: [link URL, image URL, secondary link URL, secondary image URL, title, main stats, description].
     * @param {string|null} id - An optional ID to apply to the grid container div.
     * @param {string|null} classes - Optional CSS classes to apply to the grid container div. Defaults to "grid".
     * @param {string|null} extraStyles - Optional inline styles to apply to the grid container div.
     * @returns {string} The HTML string for the grid.
     */
    CreateGrid(elements, id, classes, extraStyles) {
        let out = `<div id="${id || ''}" style="${extraStyles || ''}" class="${classes || 'grid'}">`;
        for (let i = 0; i < elements.length; i++) {
            out += `
            <div class="box">
                <a href="${elements[i][0]}"><img
                    src="${elements[i][1]}"
                    height="40px" style="border: solid var(--accent);"></a>
                <a href="${elements[i][2]}"><img src="${elements[i][3]}" alt="Image produit"
                    style="border: solid var(--accent);"></a>
                <h3>${elements[i][4]}<br><span class="mainStats"> ${elements[i][5]}</span></h3>
                <p>${elements[i][6]}</p>
            </div>`;
        }
        out += '</div>';
        return out;
    }

    /**
     * Creates an HTML structure for a filter textbox with an associated dropdown list.
     * @param {string[]} elements - An array of strings representing the options in the dropdown list.
     * @param {string} placeholder - The placeholder text for the textbox.
     * @param {string|null} id - An optional ID to apply to the main filter div.
     * @returns {HTMLElement} The HTML div element containing the filter textbox and dropdown.
     */
    CreateFilterTextbox(elements, placeholder, id) {
        const filteredDiv = document.createElement("div");
        filteredDiv.className = "bloc-filtre";
        filteredDiv.id = id || '';

        const textBox = document.createElement("input");
        textBox.type = "text";
        textBox.placeholder = placeholder;
        filteredDiv.appendChild(textBox);

        const optionsDiv = document.createElement("div");
        optionsDiv.className = "liste-choices";
        filteredDiv.appendChild(optionsDiv);

        const choicesList = document.createElement("ul");
        choicesList.className = "choiced";
        optionsDiv.appendChild(choicesList);

        this.PopulateLi(elements, choicesList, textBox);

        textBox.addEventListener('focus', () => {
            optionsDiv.style.display = 'block';
        });

        textBox.addEventListener('blur', () => {
            setTimeout(() => {
                optionsDiv.style.display = 'none';
            }, 150);
        });

        textBox.addEventListener('input', () => {
            choicesList.innerHTML = "";
            const outElements = this.FilterText(elements, textBox.value);
            this.PopulateLi(outElements, choicesList, textBox);
        });

        return filteredDiv;
    }

    /**
     * Filters an array of strings based on an input string, returning elements that start with the input.
     * @param {string[]} elements - The array of strings to filter.
     * @param {string} input - The input string to filter by.
     * @returns {string[]} An array of strings that start with the input string (case-insensitive).
     */
    FilterText(elements, input) {
        const out = [];
        const compare = (input || "").trim().toLowerCase();

        if (compare === "") {
            return elements;
        }

        for (let i = 0; i < elements.length; i++) {
            const text = elements[i].trim().toLowerCase();
            if (text.startsWith(compare)) {
                out.push(elements[i]);
            }
        }
        return out;
    }

    /**
     * Populates an HTML unordered list (ul) with list items (li) based on an array of strings.
     * Adds a mouseover event listener to each list item to update the value of a target textbox.
     * @param {string[]} elements - The array of strings to populate the list with.
     * @param {HTMLUListElement} parent - The HTML ul element to append the list items to.
     * @param {HTMLInputElement} textBox - The HTML input element to update on mouseover.
     */
    PopulateLi(elements, parent, textBox) {
        for (let i = 0; i < elements.length; i++) {
            const choice = document.createElement("li");
            choice.innerText = elements[i];
            choice.className = "choice";
            parent.appendChild(choice);
            choice.addEventListener('mouseover', (option) => {
                textBox.value = option.target.innerHTML;
            });
        }
    }

    /**
     * Creates an HTML structure for a filter textbox with an associated checklist dropdown.
     * @param {string[]} elements - An array of strings representing the labels for the checkboxes in the dropdown.
     * @param {string} placeholder - The placeholder text for the textbox.
     * @param {string|null} id - An optional ID to apply to the main filter div.
     * @returns {HTMLElement} The HTML div element containing the filter textbox and checklist dropdown.
     */
    CreateCheckList(elements, placeholder, id) {
        const filteredDiv = document.createElement("div");
        filteredDiv.className = "bloc-filtre";
        filteredDiv.id = id || '';

        const textBox = document.createElement("input");
        textBox.type = "text";
        textBox.placeholder = placeholder;
        filteredDiv.appendChild(textBox);

        const optionsDiv = document.createElement("div");
        optionsDiv.className = "liste-choices";
        filteredDiv.appendChild(optionsDiv);

        const choicesList = document.createElement("ul");
        choicesList.className = "choiced";
        optionsDiv.style.display = 'none';
        optionsDiv.appendChild(choicesList);

        this.PopulateCheck(elements, choicesList, "");

        textBox.addEventListener('click', () => {
            optionsDiv.style.display = optionsDiv.style.display === 'block' ? 'none' : 'block';
        });

        textBox.addEventListener('input', () => {
            optionsDiv.style.display = 'block';
            this.FilterCheck(choicesList.children, textBox.value);
        });

        return filteredDiv;
    }

    /**
     * Populates an HTML unordered list (ul) with list items (li) containing checkboxes.
     * @param {string[]} elements - The array of strings to use as labels for the checkboxes.
     * @param {HTMLUListElement} parent - The HTML ul element to append the list items to.
     */
    PopulateCheck(elements, parent) {
        for (let i = 0; i < elements.length; i++) {
            const choice = document.createElement("li");
            choice.innerHTML = '<input type="checkbox" style = "margin-left:5px;margin-right:5px" />' + elements[i];
            choice.className = "choice";
            parent.appendChild(choice);
        }
    }

    /**
     * Filters a collection of HTML list items (containing checkboxes) based on an input string.
     * Shows list items whose text content starts with the input string (case-insensitive) and hides others.
     * @param {HTMLCollection} elements - The HTMLCollection of li elements to filter.
     * @param {string} input - The input string to filter by.
     */
    FilterCheck(elements, input) {
        const compare = (input || "").trim().toLowerCase();

        for (let i = 0; i < elements.length; i++) {
            const text = elements[i].innerText.trim().toLowerCase();
            elements[i].hidden = compare !== "" && !text.startsWith(compare);
        }
    }

    /**
     * Creates a dynamic HTML form based on provided parameters.
     * @param {string} formTitle - The title of the form.
     * @param {string} formId - The ID of the form.
     * @param {string} formMethod - The HTTP method for the form (e.g., "POST", "GET").
     * @param {string} formEnctype - The encoding type for the form (e.g., "multipart/form-data").
     * @param {Array<object>} inputConfig - An array defining the input fields. Each object should have:
     * - `type`: The type of the input ('text', 'email', 'password', 'file', 'image', 'time', 'options', 'textarea', 'checkbox', 'color', 'date', 'number', 'url', 'switchable').
     * - `label`: The label for the input.
     * - `name`: The name attribute of the input (for 'switchable', this acts as a base name).
     * - `placeholder` (optional): The placeholder text (nullable).
     * - `defaultValue` (optional): The default value (nullable).
     * - `required` (optional): Boolean indicating if the field is required (applies based on visibility for 'switchable').
     * - For 'options' type: `options` should be an array of strings for the dropdown options.
     * - For 'checkbox' type: `value` for the checkbox, and optionally `labelLink` for a link in the label.
     * - For 'image' type: `src` for the image source.
     * - For 'switchable' type:
     * - `type1`: Object defining the first input (e.g., { type: 'url', label: 'Source URL', name: 'source_url', required: true }).
     * - `type2`: Object defining the second input (e.g., { type: 'file', label: 'Upload File', name: 'source_file', required: true }).
     * - `defaultType`: String indicating which type is visible initially ('type1' or 'type2').
     * - `switchButtonText`: (Optional) Template for the switch button text, using '%typeLabel%' as a placeholder for the *other* input's label (e.g., "Switch to %typeLabel%"). Defaults are provided if omitted.
     * @param {string} [submitButtonText="Submit"] - The text for the submit button.
     * @param {string} [loginLink] - An optional link to a login page.
     * @param {string} [loginLinkText="Already have an account? Log in."] - The text for the login link.
     * @returns {string} The HTML string for the generated form. Does NOT include the required JavaScript for switchable inputs; that must be added separately.
     */
    CreateDynamicForm(formTitle, formId, formMethod, formEnctype, inputConfig, submitButtonText = "Submit", loginLink = null,loginLinkText = "") {
        let html = `<h3 class="form-title">${formTitle}</h3>\n`;
        html += `<form id="${formId}" method="${formMethod}" enctype="${formEnctype}">\n`;

        // Helper function to generate standard input HTML (simplified version)
        const generateInputHtml = (def, idSuffix = '', extraClasses = '', extraStyles = '', isDisabled = false) => {
            const inputId = def.id || (def.name + idSuffix);
            const requiredAttr = def.required ? 'required' : '';
            const placeholderAttr = def.placeholder ? `placeholder="${def.placeholder}"` : '';
            const defaultValueAttr = def.defaultValue !== null && def.defaultValue !== undefined ? `value="${def.defaultValue}"` : '';
            const disabledAttr = isDisabled ? 'disabled' : '';
            let inputHtml = '';

            switch (def.type.toLowerCase()) {
                case 'text':
                case 'email':
                case 'password':
                case 'url':
                case 'time':
                case 'color':
                case 'date':
                case 'number':
                    inputHtml = `
                    <label for="${inputId}">${def.label} ${def.required ? '*' : ''}</label>
                    <input type="${def.type.toLowerCase()}" id="${inputId}" name="${def.name}" ${placeholderAttr} ${defaultValueAttr} ${requiredAttr} ${disabledAttr}>
                `;
                    break;
                case 'file':
                    inputHtml = `
                    <label for="${inputId}">${def.label} ${def.required ? '*' : ''}</label>
                    <input type="file" id="${inputId}" name="${def.name}" ${requiredAttr} ${disabledAttr}>
                `;
                    break;
                case 'textarea':
                    inputHtml = `
                    <label for="${inputId}">${def.label} ${def.required ? '*' : ''}</label>
                    <textarea id="${inputId}" name="${def.name}" ${placeholderAttr} ${requiredAttr} ${disabledAttr}>${def.defaultValue || ''}</textarea>
                `;
                    break;
                // Add other simple types if needed, adapting from the original function
                default:
                    console.warn(`generateInputHtml helper doesn't support type: ${def.type}`);
                    return ''; // Return empty for unsupported types in helper
            }
            return `<div class="input-wrapper ${extraClasses}" style="${extraStyles}">${inputHtml}</div>`;
        };


        inputConfig.forEach(inputDef => {
            const inputId = inputDef.id || inputDef.name;
            const requiredAttr = inputDef.required ? 'required' : '';
            const placeholderAttr = inputDef.placeholder ? `placeholder="${inputDef.placeholder}"` : '';
            const defaultValueAttr = inputDef.defaultValue !== null && inputDef.defaultValue !== undefined ? `value="${inputDef.defaultValue}"` : '';
            let inputGroupHtml = '';

            switch (inputDef.type.toLowerCase()) {
                // --- Cases for existing types (text, email, password, url, file, etc.) ---
                // (Keep your existing cases here, wrapping the output in <div class="input-group">...</div>)
                case 'text':
                case 'email':
                case 'password':
                case 'url':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="${inputDef.type.toLowerCase()}" id="${inputId}" name="${inputDef.name}" ${placeholderAttr} ${defaultValueAttr} ${requiredAttr}>
    </div>
  `;
                    break;
                case 'file':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="file" id="${inputId}" name="${inputDef.name}" ${requiredAttr}>
    </div>
  `;
                    break;
                case 'image':
                    const srcAttr = inputDef.src ? `src="${inputDef.src}"` : '';
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="image" id="${inputId}" name="${inputDef.name}" ${srcAttr} ${placeholderAttr} ${defaultValueAttr} ${requiredAttr}>
    </div>
  `;
                    break;
                case 'time':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="time" id="${inputId}" name="${inputDef.name}" ${defaultValueAttr} ${requiredAttr}>
    </div>
  `;
                    break;
                case 'options':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <select id="${inputId}" name="${inputDef.name}" ${requiredAttr}>
  `;
                    if (inputDef.options && Array.isArray(inputDef.options)) {
                        inputDef.options.forEach(option => {
                            const selectedAttr = inputDef.defaultValue === option ? 'selected' : '';
                            inputGroupHtml += `        <option value="${option}" ${selectedAttr}>${option}</option>\n`;
                        });
                    }
                    inputGroupHtml += `      </select>
    </div>
  `;
                    break;
                case 'textarea':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <textarea id="${inputId}" name="${inputDef.name}" ${placeholderAttr} ${requiredAttr}>${inputDef.defaultValue || ''}</textarea>
    </div>
  `;
                    break;
                case 'checkbox':
                    const checkboxId = inputDef.id || inputDef.name + '-' + (inputDef.value || '').replace(/\s+/g, '-').toLowerCase();
                    const checkedAttr = inputDef.defaultValue ? 'checked' : '';
                    const labelContent = inputDef.labelLink ? `${inputDef.label} <a href="${inputDef.labelLink}">${inputDef.text}</a>.` : `${inputDef.label}.`;
                    inputGroupHtml = `
    <div class="checkbox-group">
      <input type="checkbox" id="${checkboxId}" name="${inputDef.name}" value="${inputDef.value || 'on'}" ${checkedAttr} ${requiredAttr}>
      <label for="${checkboxId}">${labelContent}</label>
    </div>
  `;
                    break;
                case 'color':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="color" id="${inputId}" name="${inputDef.name}" ${defaultValueAttr} ${requiredAttr}>
    </div>
  `;
                    break;
                case 'date':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="date" id="${inputId}" name="${inputDef.name}" ${defaultValueAttr} ${requiredAttr}>
    </div>
  `;
                    break;
                case 'number':
                    inputGroupHtml = `
    <div class="input-group">
      <label for="${inputId}">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
      <input type="number" id="${inputId}" name="${inputDef.name}" ${placeholderAttr} ${defaultValueAttr} ${requiredAttr}>
    </div>
  `;
                    break;

                // --- NEW Switchable Case ---
                case 'switchable':
                    // ... (previous checks and variable assignments remain the same) ...
                    const baseName = inputDef.name;
                    const type1Def = inputDef.type1;
                    const type2Def = inputDef.type2;
                    const input1Id = type1Def.name;
                    const input2Id = type2Def.name;
                    const isType1Default = inputDef.defaultType.toLowerCase() === 'type1';
                    const type1Html = generateInputHtml(type1Def, '', `switchable-content switchable-type1`, isType1Default ? '' : 'display: none;', !isType1Default);
                    const type2Html = generateInputHtml(type2Def, '', `switchable-content switchable-type2`, !isType1Default ? '' : 'display: none;', isType1Default);
                    const defaultButtonTemplate = "Switch to %typeLabel%";
                    const buttonTemplate = inputDef.switchButtonText || defaultButtonTemplate;
                    const type1ButtonText = buttonTemplate.replace('%typeLabel%', type2Def.label || 'Type 2');
                    const type2ButtonText = buttonTemplate.replace('%typeLabel%', type1Def.label || 'Type 1');
                    const initialButtonText = isType1Default ? type1ButtonText : type2ButtonText;

                    inputGroupHtml = `
                    <div class="input-group switchable-input-group" id="group_${baseName}">
                        <label class="switchable-group-label">${inputDef.label} ${inputDef.required ? '*' : ''}</label>
                        ${type1Html}
                        ${type2Html}
                        <button type="button" class="switchable-input-button"
                                data-group-id="group_${baseName}"
                                data-type1-id="${input1Id}"
                                data-type2-id="${input2Id}"
                                data-type1-button-text="${encodeURIComponent(type1ButtonText)}"
                                data-type2-button-text="${encodeURIComponent(type2ButtonText)}">
                            ${initialButtonText}
                        </button>
                    </div>
                `;
                    break;
                default:
                    console.warn(`Unknown input type: ${inputDef.type}`);
                    break;
            }
            html += inputGroupHtml; // Append the generated group HTML
        });

        html += `    <button type="submit" id="soummission_btn" class="btn-connexion">${submitButtonText}</button>\n`;

        if (loginLink) {
            html += `    <p class="signup-link">${loginLinkText}<a href="${loginLink}">here!</a></p>`;
        }

        html += `</form>\n`;
        return html;
    }

// Add a property to track the latest search token
_searchToken = 0;

/**
 * Fetches search results and discards outdated responses if a new search is started.
 * @param {string} query - The search query.
 * @returns {object} An object containing arrays of results for Activities, Teams, and Players.
 */
async fetchSearchResults(query) {
    // Increment the search token for each new call
    const currentToken = ++this._searchToken;

    if (!query) {
        return {
            activities: [],
            teams: [],
            players: [],
        };
    }
    try {
        // Fetch players
        const playerPromise = fetch(`http://localhost:9999/api/user/search/${encodeURIComponent(query)}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                fields: ["Pseudo", "Email", "Img", "Id"],
                limit: 10
            })
        }).then(res => res.json());

        // Fetch activities
        const activityPromise = fetch(`http://localhost:9999/api/activity/search/${encodeURIComponent(query)}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                limit: 5,
                fields: ["ID", "Title"],
                levelFields: ["Name"],
                filterFields: ["TypeFilterName"]
            })
        }).then(res => res.json());

        const [playerResult, activityResult] = await Promise.all([playerPromise, activityPromise]);

        // If a new search started, discard this result
        if (currentToken !== this._searchToken) return null;

        // Players
        let players = [];
        if (playerResult.success && Array.isArray(playerResult.data)) {
            players = playerResult.data.slice(0, 3).map(
                user => `<a href="Profile.html?${user.Id}">${user.Pseudo}</a>`
            );
        }

        // Activities
        let activities = [];
        if (activityResult.success && Array.isArray(activityResult.data)) {
            activities = activityResult.data.slice(0, 3).map(
                item => `<a href="Activity.html?${item.activity.ID}">${item.activity.Title}</a>`
            );
        }

        return {
            activities,
            teams: [],
            players
        };
    } catch (e) {
        console.error("Search API error:", e);
        return {
            activities: [],
            teams: [],
            players: []
        };
    }
}

/**
 * Generates the HTML for a search page with a dropdown menu for results.
 * @param {boolean} returnElement - If true, returns a DOM element; otherwise, returns an HTML string.
 * @returns {HTMLElement|string} The HTML for the search page as either a DOM element or a string.
 */
createSearchPage(returnElement = false) {
    const searchContainer = `<div class="search-container">
        <input type="text" class="search-input" placeholder="Search...">
        <div id="search-results-dropdown" class="search-results-dropdown">
            </div>
    </div>`;

    const searchPageHTML = `<div class="search-page">
        ${searchContainer}
    </div>`;

    if (returnElement) {
        const searchPageDiv = document.createElement('div');
        searchPageDiv.innerHTML = searchPageHTML;
        const searchInputBox = searchPageDiv.querySelector('.search-input');
        const resultsDropdown = searchPageDiv.querySelector('#search-results-dropdown');

        searchInputBox.addEventListener('input', async () => {
            const query = searchInputBox.value.trim();
            resultsDropdown.innerHTML = ''; // Clear previous results

            if (query) {
                const results = await this.fetchSearchResults(query);
                // If results is null, this response is outdated
                if (!results) return;
                let hasResults = false;

                const addCategory = (categoryName, categoryResults) => {
                    if (categoryResults && categoryResults.length > 0) {
                        hasResults = true;
                        const categoryDiv = document.createElement('div');
                        categoryDiv.classList.add('dropdown-category');
                        const categoryTitle = document.createElement('h3');
                        categoryTitle.textContent = categoryName;
                        categoryDiv.appendChild(categoryTitle);
                        const ul = document.createElement('ul');
                        categoryResults.forEach(resultHTML => {
                            const li = document.createElement('li');
                            li.innerHTML = resultHTML;
                            ul.appendChild(li);
                        });
                        categoryDiv.appendChild(ul);
                        resultsDropdown.appendChild(categoryDiv);
                    }
                };

                addCategory('Activities', results.activities);
                addCategory('Teams', results.teams);
                addCategory('Players', results.players);

                if (hasResults) {
                    resultsDropdown.style.display = 'block';
                } else {
                    const noResults = document.createElement('div');
                    noResults.classList.add('dropdown-no-results');
                    noResults.textContent = `No results found for "${query}".`;
                    resultsDropdown.appendChild(noResults);
                    resultsDropdown.style.display = 'block';
                }
            } else {
                resultsDropdown.style.display = 'none';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (!searchContainer.contains(event.target)) {
                resultsDropdown.style.display = 'none';
            }
        });

        return searchPageDiv.firstChild; // Return the main div element
    } else {
        return searchPageHTML;
    }
}
    

    generateMediaViewer(mediaArray) {
        if (!Array.isArray(mediaArray) || mediaArray.length === 0) return "";
    
        // Main display uses the first media
        const [mainType, mainUrl] = mediaArray[0];
        let mainMediaHtml = "";
        if (mainType === "img") {
            mainMediaHtml = `<img src="${mainUrl}" alt="media principal" id="media-principal" />`;
        } else if (mainType === "video") {
            mainMediaHtml = `<video src="${mainUrl}" id="media-principal" controls muted></video>`;
        }
    
        // Thumbnails for all media
        const thumbnailsHtml = mediaArray.map(([type, url]) => {
            if (type === "img") {
                return `<img src="${url}" onclick="changerMedia(this)" />`;
            } else if (type === "video") {
                return `<video src="${url}" onclick="changerMedia(this)" muted></video>`;
            }
            return "";
        }).join("\n");
    
        return `
    <h2>
      <div class="media-viewer">
        <div class="media-display" id="media-display">
          ${mainMediaHtml}
        </div>
        <div class="media-thumbnails-container">
          <button class="scroll-btn left" onclick="scrollMediaThumbnails(-1)">⯇</button>
          <div class="media-thumbnails" id="media-thumbnails">
            ${thumbnailsHtml}
          </div>
          <button class="scroll-btn right" onclick="scrollMediaThumbnails(1)">⯈</button>
        </div>
      </div>
    </h2>
        `;
    }

    CreateProfile(hero, profile) {
        return `
        <div id="banniere" class="CenterChilds">
            <div id="profile">
                <img src="${profile}" alt="logo">
            </div>
        <img src="${hero}" alt="banniere">
        </div>`;
    }



    superScrollableList(options) {
        options = options || {};
        const {
          items = [],
          renderItem = item => `<div>${String(item)}</div>`,
          mode = "list", // others: "grid", "masonry", "horizontal", "carousel", "timeline", "accordion", "table", "chat", "kanban"
          width = "100%",
          height = "600px",
          containerClass = "",
          listClass = "listeScrollable generatedScrollableTable",
          bodyClass = "scrollable-body",
          gridTemplate = "repeat(auto-fill, minmax(220px, 1fr))",
          columns = [],
          kanbanColumns = [],
          virtualize = false,
          infiniteScroll = false,
          pageSize = 30,
          onLoadMore = null, // function(page) for infinite scroll
          tableHeader = [],
          timelineLineColor = "#58acec",
          carouselOptions = { show: 1, arrows: true, dots: true, auto: false, interval: 4000 }
        } = options;
      
        function escape(str) {
          return String(str).replace(/[&<>"']/g, function (m) {
            return ({
              '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[m];
          });
        }
      
        // --- Mode Implementations ---
        function makeMasonryGrid() {
          return `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};padding:6px 0;">
                <div style="column-count:3;column-gap:20px;height:${height};overflow-y:auto;">
                  ${items.map(item =>
                    `<div style="break-inside:avoid;margin-bottom:20px;">${renderItem(item)}</div>`
                  ).join("")}
                </div>
              </div>
            </div>
          `;
        }
      
        function makeHorizontal() {
          return `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};overflow-x:auto;">
                <div style="display:flex;flex-direction:row;gap:18px;height:${height};overflow-x:auto;overflow-y:hidden;">
                  ${items.map(renderItem).join("")}
                </div>
              </div>
            </div>
          `;
        }
      
        // --- Carousel: returns {html, initializer}
        function makeCarousel() {
          const carouselId = "carousel_" + Math.random().toString(36).slice(2);
          const { show = 1, arrows = true, dots = true, auto = false, interval = 4000 } = carouselOptions || {};
          const n = items.length;
          let slides = "";
          for (let i = 0; i < n; ++i) {
            slides += `<div class="carousel-slide" style="flex:0 0 ${100 / show}%;box-sizing:border-box;">
              ${renderItem(items[i], i)}
            </div>`;
          }
          const dotsHtml = dots ? `<div class="carousel-dots" style="text-align:center;margin-top:8px;"></div>` : "";
          let html = `
            <div class="${containerClass}" style="width:100%;">
              <div id="${carouselId}" class="${listClass}" style="width:${width};height:${height};overflow:hidden;position:relative;">
                <div class="carousel-track" style="display:flex;transition:transform 0.4s;width:100%;height:100%;">${slides}</div>
                ${arrows
                  ? `<button class="carousel-prev" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);z-index:2;">&#8592;</button>
                     <button class="carousel-next" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);z-index:2;">&#8594;</button>`
                  : ""}
              </div>
              ${dotsHtml}
            </div>
          `;
          // Return HTML and initializer function
          return {
            html,
            initializer: function() {
              const root = document.getElementById(carouselId);
              if (!root) return;
              const track = root.querySelector(".carousel-track");
              const prev = root.querySelector(".carousel-prev");
              const next = root.querySelector(".carousel-next");
              const dotsDiv = root.parentElement.querySelector(".carousel-dots");
              let idx = 0;
              function update() {
                track.style.transform = 'translateX(' + (-idx * 100 / show) + '%)';
                if (dotsDiv) {
                  Array.from(dotsDiv.children).forEach((d, i) => d.classList.toggle('active', i === idx));
                }
              }
              if (prev) prev.onclick = function() { idx = (idx - 1 + n) % n; update(); };
              if (next) next.onclick = function() { idx = (idx + 1) % n; update(); };
              if (dotsDiv) {
                for (let i = 0; i < n; ++i) {
                  const d = document.createElement("span");
                  d.textContent = "•";
                  d.style.cursor = "pointer";
                  d.style.fontSize = "2em";
                  d.style.margin = "0 4px";
                  d.onclick = function () { idx = i; update(); };
                  dotsDiv.appendChild(d);
                }
              }
              update();
              if (auto) setInterval(function () { idx = (idx + 1) % n; update(); }, interval);
            }
          };
        }
      
        function makeTimeline() {
          return `
            <div class="${containerClass}" style="width:100%;justify-content:flex-start;">
              <div class="${listClass}" style="width:${width};padding:24px 0;">
                <div style="position:relative;height:${height};overflow-y:auto;">
                  <div style="position:absolute;left:30px;top:0;bottom:0;width:4px;background:${timelineLineColor};border-radius:2px;"></div>
                  <div style="position:relative;">
                    ${items.map((item, idx) => `
                      <div style="position:relative;min-height:60px;margin-bottom:28px;">
                        <div style="position:absolute;left:22px;top:16px;width:20px;height:20px;background:${timelineLineColor};border-radius:50%;z-index:1;"></div>
                        <div style="margin-left:56px;">${renderItem(item, idx)}</div>
                      </div>
                    `).join("")}
                  </div>
                </div>
              </div>
            </div>
          `;
        }
      
        function makeAccordion() {
          const id = "accordion_" + Math.random().toString(36).slice(2);
          let html = `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};">
                <div class="${bodyClass}" style="height:${height};overflow-y:auto;">
                  ${items.map((item, idx) => `
                    <div class="accordion-item" style="border-bottom:1px solid var(--accent2);">
                      <div class="accordion-header" style="cursor:pointer;padding:12px 8px;font-weight:bold;background:var(--bg-darkLite);" onclick="var n=document.getElementById('${id}_body_${idx}');n.style.display=n.style.display==='block'?'none':'block';">
                        ${escape(item.title||("Item "+(idx+1)))}
                      </div>
                      <div class="accordion-body" id="${id}_body_${idx}" style="display:none;padding:12px 8px;">${renderItem(item, idx)}</div>
                    </div>
                  `).join("")}
                </div>
              </div>
            </div>
          `;
          return html;
        }
      
        function makeTable() {
          let thead = tableHeader.length
            ? `<div class="scrollable-header"><div class="scrollable-title-row">${tableHeader.map(col =>
                `<div class="scrollable-title-cell" style="width:${col.width||"auto"}">${escape(col.title||"")}</div>`
              ).join("")}</div></div>`
            : "";
          return `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};">
                ${thead}
                <div class="${bodyClass}" style="height:${height};overflow-y:auto;">
                  ${items.map((row, idx) => `
                    <div class="scrollable-row" style="display:flex;">
                      ${row.map((cell, cidx) => `<div class="scrollable-cell" style="width:${tableHeader[cidx]?.width||"auto"}">${cell}</div>`).join("")}
                    </div>
                  `).join("")}
                </div>
              </div>
            </div>
          `;
        }
      
        function makeChat() {
          return `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};background:none;">
                <div class="${bodyClass}" style="height:${height};overflow-y:auto;display:flex;flex-direction:column;">
                  ${items.map((msg, idx) => `
                    <div style="display:flex;justify-content:${idx%2===0?"flex-start":"flex-end"};">
                      <div style="max-width:60%;margin:8px;padding:12px 16px;border-radius:18px;background:${idx%2===0?"var(--accent)":"var(--bg-dark2)"};color:${idx%2===0?"var(--bg-dark)":"var(--text)"};">
                        ${renderItem(msg, idx)}
                      </div>
                    </div>
                  `).join("")}
                </div>
              </div>
            </div>
          `;
        }
      
        function makeKanban() {
          return `
            <div class="${containerClass}" style="width:100%;">
              <div class="SideBySide" style="width:${width};gap:18px;">
                ${kanbanColumns.map(col => `
                  <div class="${listClass}" style="width:260px;min-width:200px;max-width:320px;">
                    <div class="scrollable-header"><div class="scrollable-title-row"><div class="scrollable-title-cell">${escape(col.title)}</div></div></div>
                    <div class="${bodyClass}" style="height:${height};overflow-y:auto;">
                      ${items.filter(i=>i.columnId===col.id).map(renderItem).join("")}
                    </div>
                  </div>
                `).join("")}
              </div>
            </div>
          `;
        }
      
        function makeVirtualized() {
          const id = "virt_" + Math.random().toString(36).slice(2);
          let html = `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};">
                <div class="${bodyClass}" id="${id}_body" style="height:${height};overflow-y:auto;">
                  ${items.slice(0, pageSize).map(renderItem).join("")}
                </div>
                <button id="${id}_load" style="margin:12px auto;display:block;">Load More</button>
              </div>
            </div>
          `;
          setTimeout(() => {
            const body = document.getElementById(`${id}_body`);
            const btn = document.getElementById(`${id}_load`);
            if (!body || !btn) return;
            let page = 1;
            btn.onclick = function() {
              page++;
              let start = (page-1)*pageSize, end = page*pageSize;
              if (end > items.length) end = items.length;
              body.innerHTML += items.slice(start, end).map((i,idx) => renderItem(i, start+idx)).join("");
              if (end === items.length) btn.style.display = 'none';
              if (onLoadMore) onLoadMore(page);
            };
            if (items.length <= pageSize) btn.style.display = 'none';
          }, 0);
          return html;
        }
      
        function makeListOrGrid() {
          let bodyStyle = `height:${height};width:100%;`;
          let bodyExtraCls = '';
          if (mode === "grid") {
            bodyStyle += `display:grid;grid-template-columns:${gridTemplate};gap:20px;align-items:start;`;
            bodyExtraCls = ' grid';
          }
          return `
            <div class="${containerClass}" style="width:100%;">
              <div class="${listClass}" style="width:${width};">
                <div class="${bodyClass}${bodyExtraCls}" style="${bodyStyle}">
                  ${items.map(renderItem).join("")}
                </div>
              </div>
            </div>
          `;
        }
      
        // ---- Dispatch Mode ----
        let html, initializer = null;
        if (mode === "masonry") html = makeMasonryGrid();
        else if (mode === "horizontal") html = makeHorizontal();
        else if (mode === "carousel") {
          const result = makeCarousel();
          html = result.html;
          initializer = result.initializer;
        }
        else if (mode === "timeline") html = makeTimeline();
        else if (mode === "accordion") html = makeAccordion();
        else if (mode === "table") html = makeTable();
        else if (mode === "chat") html = makeChat();
        else if (mode === "kanban") html = makeKanban();
        else if (virtualize || infiniteScroll) html = makeVirtualized();
        else html = makeListOrGrid();
      
        // --- DOM insertion (not document.write!) ---
        const temp = document.createElement("div");
        temp.innerHTML = html;
        document.body.appendChild(temp);
      
        // Carousel: attach interactivity
        if (initializer) setTimeout(initializer, 0);
      
        return temp;
      }
      


    

    PrefabMenu = this.CreateMenu(["ressources/Final/Main/logo.png", "ressources/Commun/logo_example.png"],
        [["ressources/Commun/activity_button.png", "Activities", "ActivitiesList.html",], ["ressources/Commun/teams_button.png", "Teams", "Teams.html"], ["ressources/Commun/teams_button.png", "RandomActivity", "Activitymiscellaneous.html"], ["ressources/Commun/teams_button.png", "Demonstrateur", "Demonstrateur.html"],["ressources/Commun/teams_button.png", "PHP DataBase", "http://localhost:9997/"]],
        [["ressources/Commun/activity_button.png", "Sign-up", "Sign-up.html"], ["ressources/Commun/activity_button.png", "Sign-In", "Sign-in.html"], ["ressources/Commun/user_profile_image_example.png", "HeRobrain_III", "profileUtilisateur.html"]]);

}
/**
 * Controls the behavior of a menu element.
 */
let isMenuOpen = false;

/**
 * Represents the root element of the document.
 */
const rootElement = document.querySelector(':root');

/**
 * Represents the computed styles of the root element.
 */
const rootStyles = getComputedStyle(rootElement);

/**
 * The width of the menu when it is closed, retrieved from a CSS variable.
 */
const menuClosedWidth = rootStyles.getPropertyValue('--MenuWidthClosed');

/**
 * The width of the menu when it is open, retrieved from a CSS variable.
 */
const menuOpenedWidth = rootStyles.getPropertyValue('--MenuWidthOpened');

/**
 * The maximum opacity of the menu, retrieved from a CSS variable.
 */
const menuOpacityMax = rootStyles.getPropertyValue('--MenuOpacityMax');

/**
 * Toggles the visibility and state of the menu.
 * When the menu is closed, it opens and vice versa, updating the corresponding CSS variables.
 */
function toggleMenu() {
    if (!isMenuOpen) {
        // Open the menu
        isMenuOpen = true;
        rootElement.style.setProperty('--MenuWidth', menuOpenedWidth);
        rootElement.style.setProperty('--MenuOpacity', menuOpacityMax);
        rootElement.style.setProperty('--MenuTextOpacity', 1);
        rootElement.style.setProperty('--DimmerClick', 'auto');
    } else {
        // Close the menu
        isMenuOpen = false;
        rootElement.style.setProperty('--MenuWidth', menuClosedWidth);
        rootElement.style.setProperty('--MenuOpacity', 0);
        rootElement.style.setProperty('--MenuTextOpacity', 0);
        rootElement.style.setProperty('--DimmerClick', 'none');
    }
}


/**
 * Displays the content section associated with the clicked tab and updates the active tab styling.
 * @param {string} id - The ID of the content section to display.
 * @param {HTMLElement} button - The button element that was clicked.
 */
function afficherSection(id, button) {
    // Hide all content sections
    document.querySelectorAll('.contenu-onglet').forEach(section => {
        section.style.display = 'none';
    });

    // Display the selected section
    const selectedSection = document.getElementById(id);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }

    // Update the active tab
    document.querySelectorAll('.barre-onglets .onglet').forEach(btn => {
        btn.classList.remove('actif');
    });

    button.classList.add('actif');
}

/**
 * Displays the selected image or video in a larger container and updates the active thumbnail styling.
 * @param {HTMLImageElement|HTMLVideoElement} element - The image or video element that was clicked.
 */
function changerMedia(element) {
    const mediaDisplayContainer = document.getElementById("media-display");
    mediaDisplayContainer.innerHTML = "";

    // Remove the "active-media" class from previously selected thumbnails
    document.querySelectorAll('.media-thumbnails img, .media-thumbnails video')
        .forEach(el => el.classList.remove('active-media'));

    // Add the "active-media" class to the currently selected element
    element.classList.add('active-media');

    // Display the media in the main container
    if (element.tagName === "IMG") {
        const img = document.createElement("img");
        img.src = element.src;
        img.alt = "media";
        mediaDisplayContainer.appendChild(img);
    } else if (element.tagName === "VIDEO") {
        const video = document.createElement("video");
        video.src = element.src;
        video.controls = true;
        video.autoplay = true;
        mediaDisplayContainer.appendChild(video);
    }
}

/**
 * Scrolls the media thumbnails container horizontally.
 * @param {number} direction - The direction to scroll: -1 for left, 1 for right.
 */
function scrollMediaThumbnails(direction) {
    const thumbnailsContainer = document.getElementById("media-thumbnails");
    const scrollAmount = 200; // pixels

    thumbnailsContainer.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Disable the picture-in-picture functionality for all videos in the thumbnails
document.querySelectorAll('.media-thumbnails video').forEach(video => {
    video.disablePictureInPicture = true;
});

/**
 * Array of page content elements in section 7.
 */
let pageItems = [];

/**
 * The current page number for the pagination in section 7.
 */
let currentPage = 1;

/**
 * The DOM element that displays the content of the current page in section 7.
 */
const contentDiv = document.getElementById('page-content-container');

/**
 * The button element to navigate to the previous page in section 7.
 */
let prevButton;

/**
 * The button element to navigate to the next page in section 7.
 */
let nextButton;

/**
 * The container for the page number buttons.
 */
const paginationContainer = document.getElementById('pagination-container');

/**
 * The total number of pages in section 7.
 */
let totalPages = 0;

/**
 * Updates the content displayed in section 7 based on the provided page number.
 * It also updates the styling of the active page number button.
 * @param {number} pageNumber - The page number to display (1-based index).
 */
function updateContent(pageNumber) {
    if (pageNumber >= 1 && pageNumber <= totalPages) {
        // Hide all page items
        pageItems.forEach(item => item.classList.remove('active'));

        currentPage = pageNumber;
        pageItems[currentPage - 1].classList.add('active');

        // Update active button in pagination
        const pageNumberButtons = paginationContainer.querySelectorAll('.page-number');
        pageNumberButtons.forEach(btn => {
            btn.classList.remove('active');
            if (parseInt(btn.dataset.page) === currentPage) {
                btn.classList.add('active');
            }
        });
    }
}

/**
 * Initializes the pagination for section 7 based on the number of page items.
 */
function initializePagination(parentID) {
    var target = '#' + parentID + ' .page-item';
    pageItems = document.querySelectorAll(target);
    totalPages = pageItems.length;

    // Clear any existing page number buttons
    paginationContainer.innerHTML = '';

    // Create previous button
    prevButton = document.createElement('button');
    prevButton.classList.add('prev-page');
    prevButton.textContent = '< Previous';
    prevButton.addEventListener('click', () => {
        if (currentPage > 1) {
            updateContent(currentPage - 1);
        }
    });
    paginationContainer.appendChild(prevButton);

    // Create page number buttons
    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement('button');
        pageButton.classList.add('page-number');
        pageButton.textContent = i;
        pageButton.dataset.page = i;
        if (i === 1) {
            pageButton.classList.add('active');
        }
        pageButton.addEventListener('click', () => {
            const pageNumber = parseInt(pageButton.dataset.page);
            updateContent(pageNumber);
        });
        paginationContainer.appendChild(pageButton);
    }

    // Create next button
    nextButton = document.createElement('button');
    nextButton.classList.add('next-page');
    nextButton.textContent = 'Next >';
    nextButton.addEventListener('click', () => {
        if (currentPage < totalPages) {
            updateContent(currentPage + 1);
        }
    });
    paginationContainer.appendChild(nextButton);

    // Initially display the first page
    updateContent(currentPage);
}

// Call initializePagination when the section is first displayed
// This is now handled within the afficherSection function

/**
 * Initializes the switchable input functionality for forms generated by CreateDynamicForm.
 * Finds all switch buttons and adds click listeners to toggle input visibility and state.
 */
function initializeSwitchableInputs() {
    // Find all switch buttons added by the form generator
    const switchButtons = document.querySelectorAll('.switchable-input-button');

    switchButtons.forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.dataset.groupId;
            const type1InputId = this.dataset.type1Id;
            const type2InputId = this.dataset.type2Id;
            const type1ButtonText = decodeURIComponent(this.dataset.type1ButtonText);
            const type2ButtonText = decodeURIComponent(this.dataset.type2ButtonText);

            const groupElement = document.getElementById(groupId);
            if (!groupElement) {
                console.error(`Switchable group not found: ${groupId}`);
                return;
            }

            // Find the wrapper divs for the inputs within the specific group
            const input1Wrapper = groupElement.querySelector(`.switchable-type1`);
            const input2Wrapper = groupElement.querySelector(`.switchable-type2`);
            // Find the actual input elements
            const input1Element = document.getElementById(type1InputId);
            const input2Element = document.getElementById(type2InputId);


            if (!input1Wrapper || !input2Wrapper || !input1Element || !input2Element) {
                console.error(`Could not find switchable input elements for group: ${groupId}`);
                return;
            }

            // Check which input is currently visible (by checking display style)
            const isType1Visible = input1Wrapper.style.display !== 'none';

            if (isType1Visible) {
                // Switch to Type 2
                input1Wrapper.style.display = 'none';
                input1Element.disabled = true; // Disable inactive input
                if (input1Element.hasAttribute('required')) {
                   input1Element.removeAttribute('required'); // Temporarily remove required if disabled
                   input1Element.dataset.wasRequired = 'true'; // Mark it
                }


                input2Wrapper.style.display = ''; // Reset display to default (usually block or inline-block)
                input2Element.disabled = false; // Enable active input
                 if (input2Element.dataset.wasRequired === 'true') {
                   input2Element.setAttribute('required', ''); // Re-add required if it was marked
                   delete input2Element.dataset.wasRequired;
                }

                this.textContent = type2ButtonText; // Update button text
            } else {
                // Switch to Type 1
                input2Wrapper.style.display = 'none';
                input2Element.disabled = true;
                 if (input2Element.hasAttribute('required')) {
                   input2Element.removeAttribute('required');
                   input2Element.dataset.wasRequired = 'true';
                }

                input1Wrapper.style.display = '';
                input1Element.disabled = false;
                if (input1Element.dataset.wasRequired === 'true') {
                   input1Element.setAttribute('required', '');
                   delete input1Element.dataset.wasRequired;
                }

                this.textContent = type1ButtonText; // Update button text
            }
        });
    });
}

// --- HOW TO USE ---
// Make sure this script is loaded after the form HTML is on the page.
// Then call the initialization function:
//
// Example:
// document.addEventListener('DOMContentLoaded', function() {
//     // Assuming your form HTML is already rendered here or added dynamically
//     initializeSwitchableInputs();
// });
//
// Or if adding form HTML dynamically via JavaScript:
// const formContainer = document.getElementById('form-container');
// formContainer.innerHTML = CreateDynamicForm(...); // Generate the form
// initializeSwitchableInputs(); // Initialize *after* adding to DOM

/**
 * Initializes universal event handlers for dynamic form elements like lists,
 * switchable inputs, and hidden input groups using event delegation.
 *
 * @param {ElementCreator} elementCreatorInstance - An instance of the ElementCreator class,
 *        required for generating HTML when adding new list items.
 */
function initializeDynamicFormHandlers(elementCreatorInstance) {
    if (!elementCreatorInstance || typeof elementCreatorInstance.generateInputHtml !== 'function') {
        console.error("ElementCreator instance with 'generateInputHtml' method is required for initializeDynamicFormHandlers.");
        return;
    }

    console.log("Initializing dynamic form handlers...");

    document.body.addEventListener('click', function(event) {

        // --- Handle Add List Item ---
        if (event.target.matches('.add-list-item-button')) {
            console.log("Add list item button clicked");
            const addButton = event.target;
            const containerId = addButton.dataset.containerId;
            const container = document.getElementById(containerId);

            if (!container) {
                console.error(`List container with ID "${containerId}" not found.`);
                return;
            }

            try {
                const templateJson = container.dataset.inputTemplate.replace(/&apos;/g, "'");
                const inputTemplate = JSON.parse(templateJson);
                const nextIndex = parseInt(container.dataset.nextIndex, 10);
                const removeButtonText = container.dataset.removeButtonText || 'Remove';

                let newItemInputsHtml = '';
                inputTemplate.forEach(inputDef => {
                    // Use the passed ElementCreator instance
                    newItemInputsHtml += elementCreatorInstance.generateInputHtml(inputDef, nextIndex, 'list-item-input', '', false);
                });

                const newItemDiv = document.createElement('div');
                newItemDiv.classList.add('list-item');
                newItemDiv.dataset.index = nextIndex;
                newItemDiv.innerHTML = `
                    ${newItemInputsHtml}
                    <button type="button" class="remove-list-item-button" data-remove-text="${removeButtonText}">${removeButtonText}</button>
                `;

                container.appendChild(newItemDiv);
                console.log(`Added list item with index ${nextIndex} to container ${containerId}`);

                // Update the next index
                container.dataset.nextIndex = nextIndex + 1;

            } catch (e) {
                console.error("Error adding list item:", e);
                console.error("Template JSON:", container.dataset.inputTemplate);
            }
        }

        // --- Handle Remove List Item ---
        else if (event.target.matches('.remove-list-item-button')) {
            console.log("Remove list item button clicked");
            const removeButton = event.target;
            const itemToRemove = removeButton.closest('.list-item');
            if (itemToRemove) {
                const index = itemToRemove.dataset.index;
                itemToRemove.remove();
                console.log(`Removed list item with index ${index}`);
                // Optional: Re-index subsequent items if necessary, though often not required.
            }
        }

        // --- Handle Switchable Input Button ---
        else if (event.target.matches('.switchable-input-button')) {
            console.log("Switchable input button clicked");
            const button = event.target;

            const groupId = button.dataset.groupId;
            const type1InputId = button.dataset.type1Id;
            const type2InputId = button.dataset.type2Id;
            const type1ButtonText = decodeURIComponent(button.dataset.type1ButtonText);
            const type2ButtonText = decodeURIComponent(button.dataset.type2ButtonText);

            const groupElement = document.getElementById(groupId);
            if (!groupElement) {
                console.error(`Switchable group element with ID "${groupId}" not found.`);
                return;
            }

            const type1Wrapper = groupElement.querySelector(`.switchable-content.switchable-type1`);
            const type2Wrapper = groupElement.querySelector(`.switchable-content.switchable-type2`);
            const input1 = document.getElementById(type1InputId);
            const input2 = document.getElementById(type2InputId);

            if (!type1Wrapper || !type2Wrapper || !input1 || !input2) {
                console.error(`Could not find all required elements for switchable group "${groupId}". Check IDs: ${type1InputId}, ${type2InputId}`);
                return;
            }

            const isType1Active = type1Wrapper.style.display !== 'none';

            type1Wrapper.style.display = isType1Active ? 'none' : 'block';
            type2Wrapper.style.display = isType1Active ? 'block' : 'none';

            input1.disabled = isType1Active;
            input2.disabled = !isType1Active;

            const toggleRequired = (input, isEnabled) => {
                const originallyRequired = input.getAttribute('data-original-required') === 'true';
                if (originallyRequired) {
                    if (isEnabled) input.setAttribute('required', '');
                    else input.removeAttribute('required');
                }
            };

            toggleRequired(input1, !isType1Active);
            toggleRequired(input2, isType1Active);

            button.textContent = isType1Active ? type2ButtonText : type1ButtonText;
            console.log(`Switched group ${groupId} to ${isType1Active ? 'Type 2' : 'Type 1'}`);
        }

        // --- Handle Hidden Input Button ---
        else if (event.target.matches('.hidden-input-button')) {
            // Assumes inline onclick was removed from ElementCreator.js
            console.log("Hidden input button clicked");
            const button = event.target;
            // Infer content ID based on button ID convention
            const contentId = button.id.replace('hidden_button_', 'hidden_content_');
            const content = document.getElementById(contentId);

            // Get button text from data attributes (ensure these are added in ElementCreator if needed)
            const buttonTextShow = button.dataset.buttonTextShow || "Show";
            const buttonTextHide = button.dataset.buttonTextHide || "Hide";

            if (!content) {
                 console.error(`Hidden content with ID "${contentId}" not found.`);
                 return;
            }

            const isCurrentlyHidden = content.style.display === 'none';
            content.style.display = isCurrentlyHidden ? 'block' : 'none';
            button.textContent = isCurrentlyHidden ? buttonTextHide : buttonTextShow;

            // Enable/disable inputs within the content
            const inputs = content.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.disabled = !isCurrentlyHidden;
                // Toggle required attribute based on original state
                const originalRequired = input.getAttribute('data-original-required') === 'true';
                if (originalRequired) {
                    if (isCurrentlyHidden) input.setAttribute('required', '');
                    else input.removeAttribute('required');
                }
            });
            console.log(`Toggled visibility for hidden content ${contentId} to ${isCurrentlyHidden ? 'visible' : 'hidden'}`);
        }

    }); // End of main click listener

} // End of initializeDynamicFormHandlers
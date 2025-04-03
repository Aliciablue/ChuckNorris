import translations from './translations'; // Import the translations object

$(document).ready(function () {
    // DOM Element Caching
    const searchForm = $('#searchForm');
  
    const searchTypeRadios = $('input[name="type"]'); // Get all radio buttons with name 'type'
    const allowedSearchTypes = ['random', 'keyword', 'category']; // Array of allowed values
    const currentLocale = document.documentElement.lang; // Get the current locale
    const frontendValidationErrorsList = $('#frontend-validation-errors ul');
    const frontendValidationErrors = $('#frontend-validation-errors'); // Declare and initialize frontendValidationErrors

    // Function to get the translated message
    function translate(key) {
        return translations[currentLocale] && translations[currentLocale][key] ? translations[currentLocale][key] : translations['en'][key]; // Default to English if translation not found
    }

    // Function to display an error message next to the input (for keyword and email)
    function displayError(element, messageKey) {
        element.addClass('is-invalid');
        let errorContainer = element.next('.invalid-feedback');
        if (errorContainer.length === 0) {
            errorContainer = $('<div class="invalid-feedback"></div>');
            element.after(errorContainer);
        }
        errorContainer.text(translate(messageKey));
    }

    // Function to display general error messages at the top
    function displayTopErrors(errors) {
        frontendValidationErrorsList.empty();
        if (errors.length > 0) {
            $.each(errors, function (index, error) {
                frontendValidationErrorsList.append(`<li>${error}</li>`);
            });
            frontendValidationErrors.slideDown();
        } else {
            frontendValidationErrors.slideUp();
        }
    }

    // Function to clear error messages
    // Function to clear error messages
    function clearErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        frontendValidationErrors.slideUp(function () {
            frontendValidationErrorsList.empty();
        });
    }
    // Form submission event listener
    searchForm.on('submit', function (event) {
        //event.preventDefault();
        clearErrors();
        const selectedType = $('input[name="type"]:checked').val();
        const queryInput = $('#query');
        const emailInput = $('#email');
        const query = queryInput.val();
        console.log('query'+query)
        const email = emailInput.val();
        let isValid = true;
        const topErrors = [];

          // Validate if a search type is selected and is one of the allowed values
         if (!selectedType || !allowedSearchTypes.includes(selectedType)) {
             topErrors.push(translate('Please select a valid search type.'));
             isValid = false;
         }
        
         // Validate email if entered
         if (email) {
             const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
             if (!emailRegex.test(email)) {
                 displayError(emailInput, 'Please enter a valid email address.');
                 isValid = false;
             }
         }
 
         // Validate keyword if 'keyword' type is selected
         if (selectedType === 'keyword') {
             if (!query) {
                 displayError(queryInput, 'Please enter a keyword.');
                 isValid = false;
             } else if (query.length < 3) {
                 displayError(queryInput, 'Keyword must be at least 3 characters long.');
                 isValid = false;
             } else if (query.length > 255) {
                 displayError(queryInput, 'Keyword cannot be longer than 255 characters.');
                 isValid = false;
             } else {
                 const specialCharsRegex = /[^\w\s]/;
                 if (specialCharsRegex.test(query)) {
                     displayError(queryInput, 'Keyword cannot contain special characters.');
                     isValid = false;
                 }
             }
         } 
        // Validate category if 'category' type is selected
        if (selectedType === 'category') {
            if (!window.cachedCategories || window.cachedCategories.length === 0) {
                topErrors.push(translate('Categories could not be loaded.'));
                console.log('Categories could not be loaded.');
                isValid = false;
            } else if (!query || !window.cachedCategories.includes(query)) {
                console.log('1'+query);
                console.log('2'+window.cachedCategories);
                topErrors.push(translate('Please select a valid category from the list.'));
                console.log('Selected category is invalid');
                isValid = false;
            }
        }

        if (!isValid && topErrors.length > 0) {
            displayTopErrors(topErrors);
            event.preventDefault();
        } else if (!isValid) {
            event.preventDefault();
        } else {
            displayTopErrors([]);
        }
    });
});
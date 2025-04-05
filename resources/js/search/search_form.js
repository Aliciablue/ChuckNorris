$(document).ready(function () {
    // DOM Element Caching
    const queryContainer = $('#query-container');
    const queryLabel = $('#query-label');
    const searchForm = $('#searchForm');
    const oldType = searchForm.attr('data-old-type');
    const oldQuery = searchForm.attr('data-old-query'); // Get old query
    window.cachedCategories = null; // Make it a global variable


    // State Variables
    let initialLoad = true;
    let loadingCategories = false;
    //let  window.cachedCategories = null; // Variable to store categories

    /**
     * Updates the query input container based on the selected search type.
     */
    function updateQueryContainer(event) {
        const selectedType = $('input[name="type"]:checked').val();

        if (selectedType === 'random') {
            handleRandomSelection();
        } else if (selectedType === 'keyword') {
            handleKeywordSelection();
        } else if (selectedType === 'category') {
            handleCategorySelection();
        }
    }

    /**
     * Handles the UI updates when the 'random' search type is selected.
     */
    function handleRandomSelection() {
        queryContainer.hide();
    }

    /**
     * Handles the UI updates when the 'keyword' search type is selected.
     */
    function handleKeywordSelection() {
        queryContainer.show();
        const selectedLabelElement = $('input[name="type"]:checked').next('label');

        const keywordLabelText = selectedLabelElement.data('keyword-label');

        // Check if the query input is a select element
        if (queryContainer.find('select').length > 0) {
            queryContainer.html(`<label for="query" class="form-label" id="query-label">${keywordLabelText}</label><input type="text" class="form-control" id="query" name="query">`);
        } else if (queryContainer.find('input[type="text"]').length === 0) {
            queryContainer.prepend(`<label for="query" class="form-label" id="query-label">${keywordLabelText}</label><input type="text" class="form-control" id="query" name="query">`);
        } else {
            queryLabel.text(keywordLabelText);
        }
    }

    /**
     * Updates the text content of the query label and helper elements.
     * @param {string} labelText
     * @param {string} helperText
     */
    function updateQueryLabelAndHelper(labelText, helperText) {
        queryLabel.text(labelText);

    }

    /**
     * Handles the UI updates when the 'category' search type is selected.
     */
    function handleCategorySelection() {
        queryContainer.show();
        const selectedLabelElement = $('input[name="type"]:checked').next('label');
        updateQueryLabelAndHelper(
            selectedLabelElement.data('category-label'),
            selectedLabelElement.data('category-helper')
        );

        if (queryContainer.find('select').length === 0) {
            if (window.cachedCategories) {
                populateCategoryDropdown(cachedCategories);
            } else if (!loadingCategories) {
                loadCategories();
            }
        }
        $('#query').removeAttr('placeholder');
    }

    /**
     * Populates the category dropdown with the provided categories.
     * @param {Array<string>} categories
     */
    function populateCategoryDropdown(categories) {
        let options = '<select class="form-select" id="query" name="query">';
        $.each(categories, function (index, category) {
            const isSelected = oldQuery === category ? 'selected' : ''; // Compare with JavaScript variable
            options += `<option value="${category}" ${isSelected}>${category}</option>`;
        });
        options += '</select>';
        queryContainer.html('<label for="query" class="form-label" id="query-label"></label>' + options);
        $('#query-label').text($('input[name="type"]:checked').next('label').data('category-label'));
    }

    /**
     * Loads the categories from the server via AJAX.
     */
    function loadCategories() {
        loadingCategories = true;
        const currentLocale = document.documentElement.lang;
        const categoryUrl = `/${currentLocale}/categories`;
        $.ajax({
            url: categoryUrl,
            method: 'GET',
            success: function (data) {
                console.log('Categories loaded from server.');
                cachedCategories = data; // Store categories in the variable
                populateCategoryDropdown(data);
                loadingCategories = false;
            },
            error: function () {
                loadingCategories = false;
                console.error('Error loading categories.');
            }
        });
    }

    /**
     * Initializes the form based on the 'oldType' and 'oldQuery' attributes.
     */
    function initializeForm() {
        if (oldType) {
            $(`input[name="type"][value="${oldType}"]`).prop('checked', true);
            if (oldType === 'category' && window.cachedCategories) {
                populateCategoryDropdown(window.cachedCategories);
            } else if (oldType === 'keyword') {
                updateQueryContainer(); // Ensure keyword UI is set
            } else {
                updateQueryContainer(); // For random or other cases
            }
        } else {
            // Initial call if no old type is present (default to keyword)
            updateQueryContainer();
        }
        initialLoad = false;
    }

    // Event Listener
    $('input[name="type"]').on('change', updateQueryContainer);

    // Load categories on initial page load in the background
    const currentLocale = document.documentElement.lang;
    const categoryUrl = `/${currentLocale}/categories`;
    $.ajax({
        url: categoryUrl,
        method: 'GET',
        success: function (data) {
            console.log('Categories loaded on initial page load.');
            window.cachedCategories = data; // Store categories
            initializeForm(); // Initialize form after categories are loaded
        },
        error: function () {
            console.error('Error loading categories on initial page load.');
            initializeForm(); // Still initialize even if categories fail to load
        }
    });

    // Initialize form if categories were already cached
    if (window.cachedCategories) {
        initializeForm();
    } else if (initialLoad && !oldType) {
        initializeForm(); // Initialize on initial load if no oldType and categories not immediately loaded
    }
});
// Debounce function to limit how often the search is performed
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to perform the search
function performSearch() {
    const searchQuery = document.getElementById('shopSearch').value;
    const category = document.getElementById('categoryFilter').value;
    const priceRange = document.getElementById('priceFilter').value;
    
    // Show loading state
    document.getElementById('shopProductGrid').innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-primary">Searching products...</div>
        </div>
    `;
    
    // Create URL with parameters
    const url = `../controller/search_products.php?search=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(category)}&price=${encodeURIComponent(priceRange)}`;
    
    // Fetch results
    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('shopProductGrid').innerHTML = html || `
                <div class="col-12 text-center py-5">
                    <div class="text-muted">No products found matching your criteria</div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('shopProductGrid').innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="text-danger">Error loading products. Please try again.</div>
                </div>
            `;
        });
}

// Clear filters function
function clearFilters() {
    document.getElementById('shopSearch').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('priceFilter').value = '';
    performSearch();
}

// Add event listeners when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Debounced search for text input
    const debouncedSearch = debounce(() => performSearch(), 300);
    
    // Add event listeners to all filter elements
    document.getElementById('shopSearch').addEventListener('input', debouncedSearch);
    document.getElementById('categoryFilter').addEventListener('change', performSearch);
    document.getElementById('priceFilter').addEventListener('change', performSearch);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    
    // Initial search
    performSearch();
});

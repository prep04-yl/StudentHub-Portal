/**
 * Practical 6: Rendering External JSON Data using Fetch API, Search, Filter, Sort & Pagination
 */

document.addEventListener('DOMContentLoaded', () => {
    initEventsManager();
});

function initEventsManager() {
    const gridContainer = document.querySelector('.grid-container');
    const searchInput = document.getElementById('eventSearch');
    const categoryFilter = document.getElementById('eventCategory');
    const sortSelect = document.getElementById('eventSort');
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');

    if (!gridContainer) return;

    let allEvents = [];
    let filteredEvents = [];
    let currentPage = 1;
    const itemsPerPage = 4; // 4 cards per page for clean responsive grid

    // Fetch JSON data
    fetch('../../Practical 6/events.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            allEvents = data;
            applyFiltersAndRender();
        })
        .catch(err => {
            console.error('Error fetching events data:', err);
            gridContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 30px; color: #c76060;">
                    <p>Failed to load events from JSON file. Please ensure you are viewing through a local web server (e.g. Live Server).</p>
                </div>
            `;
        });

    function applyFiltersAndRender() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedCategory = categoryFilter ? categoryFilter.value : 'all';
        const sortValue = sortSelect ? sortSelect.value : 'date-asc';

        // 1. Filter by Search keyword (title, venue, description) & Category
        filteredEvents = allEvents.filter(ev => {
            const matchesCategory = (selectedCategory === 'all') || (ev.category.toLowerCase() === selectedCategory.toLowerCase());
            const matchesSearch = !searchTerm ||
                ev.title.toLowerCase().includes(searchTerm) ||
                ev.venue.toLowerCase().includes(searchTerm) ||
                ev.description.toLowerCase().includes(searchTerm);

            return matchesCategory && matchesSearch;
        });

        // 2. Sorting
        filteredEvents.sort((a, b) => {
            if (sortValue === 'date-asc') {
                return new Date(a.date) - new Date(b.date);
            } else if (sortValue === 'date-desc') {
                return new Date(b.date) - new Date(a.date);
            } else if (sortValue === 'title-asc') {
                return a.title.localeCompare(b.title);
            } else if (sortValue === 'title-desc') {
                return b.title.localeCompare(a.title);
            }
            return 0;
        });

        // Reset to page 1 on filter/search/sort change
        currentPage = 1;
        renderPagination();
    }

    function renderPagination() {
        const totalPages = Math.ceil(filteredEvents.length / itemsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const pageItems = filteredEvents.slice(startIndex, startIndex + itemsPerPage);

        // Render Cards
        if (pageItems.length === 0) {
            gridContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">
                    <p>No matching events found. Try adjusting your search or category filter.</p>
                </div>
            `;
        } else {
            gridContainer.innerHTML = pageItems.map(ev => `
                <div class="events-divs">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 6px;">
                        <h4 style="margin:0;">${escapeHTML(ev.title)}</h4>
                        <span class="event-badge">${escapeHTML(ev.category)}</span>
                    </div>
                    <p class="p-button-push">
                        <strong>Date:</strong> ${escapeHTML(ev.dateDisplay)}<br>
                        <strong>Time:</strong> ${escapeHTML(ev.time)}<br>
                        <strong>Venue:</strong> ${escapeHTML(ev.venue)}<br>
                        ${escapeHTML(ev.description)}
                    </p>
                    <button type="button" onclick="openModal('${escapeQuotes(ev.title)}', '${escapeQuotes(ev.details)}')">View Details</button>
                </div>
            `).join('');
        }

        // Update pagination controls
        if (pageInfo) {
            pageInfo.textContent = `Page ${currentPage} of ${totalPages} (${filteredEvents.length} events)`;
        }
        if (prevBtn) {
            prevBtn.disabled = (currentPage <= 1);
        }
        if (nextBtn) {
            nextBtn.disabled = (currentPage >= totalPages);
        }
    }

    // Helper functions for escaping text
    function escapeHTML(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escapeQuotes(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // Event listeners
    if (searchInput) {
        searchInput.addEventListener('input', applyFiltersAndRender);
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFiltersAndRender);
    }
    if (sortSelect) {
        sortSelect.addEventListener('change', applyFiltersAndRender);
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                gridContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredEvents.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                gridContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
}

import { Game } from './classes/Game.js';
import { Genre } from './classes/Genre.js';
import { Platform } from './classes/Platform.js';
import { GameListItem } from './components/GameListItem.js';
import { GameDetail } from './components/GameDetail.js';

/**
 * Main Application Class
 * Implements the Master-Detail UI pattern for the Game Library
 */
class App {
    constructor() {
        // DOM Elements
        this.gameListContainer = document.getElementById('game-list');
        this.gameDetailContainer = document.getElementById('game-detail');
        this.gameCountElement = document.getElementById('game-count');
        this.filtersContainer = document.getElementById('filters-container');
        this.filterToggle = document.getElementById('filter-toggle');
        this.filterHeader = document.querySelector('.filter-header');

        // Application State
        this.games = [];
        this.genres = [];
        this.platforms = [];
        this.filteredGames = [];
        this.selectedGameId = null;
        this.filtersExpanded = false; // Default to collapsed

        // Filter State
        this.activeFilters = {
            genreId: null,
            platformId: null,
            title: ''
        };

        // Initialize the application
        this.init();
    }

    /**
     * Initialize the application
     */
    async init() {
        try {
            // Load all data in parallel
            await Promise.all([
                this.loadGames(),
                this.loadGenres(),
                this.loadPlatforms()
            ]);

            // Render filters after data is loaded
            this.renderFilters();
            this.setupEventListeners();
        } catch (error) {
            this.showError('Failed to load games. Please refresh the page.');
            console.error('Initialization error:', error);
        }
    }

    /**
     * Load all games from the API
     */
    async loadGames() {
        try {
            // Show loading state
            this.gameListContainer.innerHTML = '<div class="loading">Loading games...</div>';

            // Fetch games
            this.games = await Game.findAll();
            this.filteredGames = [...this.games]; // Initially, all games are shown

            // Render the game list
            this.renderGameList();
            this.updateGameCount();

            // Auto-select first game if available
            if (this.games.length > 0) {
                this.selectGame(this.games[0].getId());
            }
        } catch (error) {
            this.gameListContainer.innerHTML = '<div class="loading">Error loading games.</div>';
            throw error;
        }
    }

    /**
     * Load all genres from the API
     */
    async loadGenres() {
        try {
            this.genres = await Genre.findAll();
        } catch (error) {
            console.error('Error loading genres:', error);
        }
    }

    /**
     * Load all platforms from the API
     */
    async loadPlatforms() {
        try {
            this.platforms = await Platform.findAll();
        } catch (error) {
            console.error('Error loading platforms:', error);
        }
    }

    /**
     * Render the game list in the master panel
     */
    renderGameList() {
        // Clear the container
        this.gameListContainer.innerHTML = '';

        // Check if there are games to display
        if (this.filteredGames.length === 0) {
            this.gameListContainer.innerHTML = '<div class="loading">No games found.</div>';
            return;
        }

        // Render each game as a list item
        this.filteredGames.forEach(game => {
            const listItem = new GameListItem(game);
            const element = listItem.render();

            // Add selected class if this is the selected game
            if (this.selectedGameId === game.getId()) {
                element.classList.add('selected');
            }

            // Add click event listener
            element.addEventListener('click', () => this.selectGame(game.getId()));

            // Append to container
            this.gameListContainer.appendChild(element);
        });
    }

    /**
     * Select a game and display its details
     * @param {number} gameId - The ID of the game to select
     */
    async selectGame(gameId) {
        try {
            // Update selected game ID
            this.selectedGameId = gameId;

            // Update selected state in list items
            this.updateSelectedState();

            // Fetch full game details
            const game = await Game.findById(gameId);

            if (game) {
                this.renderGameDetail(game);
            } else {
                this.showDetailPlaceholder('Game not found.');
            }
        } catch (error) {
            this.showDetailPlaceholder('Error loading game details.');
            console.error('Error selecting game:', error);
        }
    }

    /**
     * Update the selected state in the game list
     */
    updateSelectedState() {
        const listItems = this.gameListContainer.querySelectorAll('.game-list-item');
        listItems.forEach(item => {
            const itemGameId = parseInt(item.dataset.gameId);
            if (itemGameId === this.selectedGameId) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }

    /**
     * Render game details in the detail panel
     * @param {Game} game - The game object to display
     */
    renderGameDetail(game) {
        // Clear the container
        this.gameDetailContainer.innerHTML = '';

        // Create and render the detail component
        const detailComponent = new GameDetail(game);
        const element = detailComponent.render();

        // Append to container
        this.gameDetailContainer.appendChild(element);
    }

    /**
     * Show a placeholder message in the detail panel
     * @param {string} message - The message to display
     */
    showDetailPlaceholder(message) {
        this.gameDetailContainer.innerHTML = `
            <div class="detail-placeholder">
                <p>${message}</p>
            </div>
        `;
    }

    /**
     * Update the game count display
     */
    updateGameCount() {
        const count = this.filteredGames.length;
        this.gameCountElement.textContent = `${count} game${count !== 1 ? 's' : ''}`;
    }

    /**
     * Render the filter controls
     */
    renderFilters() {
        // Clear existing content
        this.filtersContainer.innerHTML = '';

        // Title search input
        const titleGroup = document.createElement('div');
        titleGroup.className = 'filter-group';

        const titleLabel = document.createElement('label');
        titleLabel.setAttribute('for', 'filter-title');
        titleLabel.textContent = 'Search by Title';
        titleLabel.className = 'filter-label';

        const titleInput = document.createElement('input');
        titleInput.type = 'text';
        titleInput.id = 'filter-title';
        titleInput.className = 'filter-input';
        titleInput.placeholder = 'Type to search...';
        titleInput.value = this.activeFilters.title;

        titleGroup.appendChild(titleLabel);
        titleGroup.appendChild(titleInput);
        this.filtersContainer.appendChild(titleGroup);

        // Genre filter dropdown
        const genreGroup = document.createElement('div');
        genreGroup.className = 'filter-group';

        const genreLabel = document.createElement('label');
        genreLabel.setAttribute('for', 'filter-genre');
        genreLabel.textContent = 'Filter by Genre';
        genreLabel.className = 'filter-label';

        const genreSelect = document.createElement('select');
        genreSelect.id = 'filter-genre';
        genreSelect.className = 'filter-select';

        // Add default option
        const genreDefaultOption = document.createElement('option');
        genreDefaultOption.value = '';
        genreDefaultOption.textContent = 'All Genres';
        genreSelect.appendChild(genreDefaultOption);

        // Add genre options
        this.genres.forEach(genre => {
            const option = document.createElement('option');
            option.value = genre.getId();
            option.textContent = genre.getName();
            if (this.activeFilters.genreId === genre.getId()) {
                option.selected = true;
            }
            genreSelect.appendChild(option);
        });

        genreGroup.appendChild(genreLabel);
        genreGroup.appendChild(genreSelect);
        this.filtersContainer.appendChild(genreGroup);

        // Platform filter dropdown
        const platformGroup = document.createElement('div');
        platformGroup.className = 'filter-group';

        const platformLabel = document.createElement('label');
        platformLabel.setAttribute('for', 'filter-platform');
        platformLabel.textContent = 'Filter by Platform';
        platformLabel.className = 'filter-label';

        const platformSelect = document.createElement('select');
        platformSelect.id = 'filter-platform';
        platformSelect.className = 'filter-select';

        // Add default option
        const platformDefaultOption = document.createElement('option');
        platformDefaultOption.value = '';
        platformDefaultOption.textContent = 'All Platforms';
        platformSelect.appendChild(platformDefaultOption);

        // Add platform options
        this.platforms.forEach(platform => {
            const option = document.createElement('option');
            option.value = platform.getId();
            option.textContent = platform.getName();
            if (this.activeFilters.platformId === platform.getId()) {
                option.selected = true;
            }
            platformSelect.appendChild(option);
        });

        platformGroup.appendChild(platformLabel);
        platformGroup.appendChild(platformSelect);
        this.filtersContainer.appendChild(platformGroup);

        // Clear filters button
        const clearButton = document.createElement('button');
        clearButton.id = 'clear-filters';
        clearButton.className = 'filter-clear-button';
        clearButton.textContent = 'Clear Filters';
        this.filtersContainer.appendChild(clearButton);
    }

    /**
     * Apply filters to the game list
     */
    applyFilters() {
        // Start with all games
        this.filteredGames = [...this.games];

        // Filter by genre
        if (this.activeFilters.genreId) {
            this.filteredGames = this.filteredGames.filter(
                game => game.getGenreId() === this.activeFilters.genreId
            );
        }

        // Filter by platform
        if (this.activeFilters.platformId) {
            this.filteredGames = this.filteredGames.filter(game => {
                return game.getPlatformIds().includes(this.activeFilters.platformId);
            });
        }

        // Filter by title (search)
        if (this.activeFilters.title) {
            const searchTerm = this.activeFilters.title.toLowerCase();
            this.filteredGames = this.filteredGames.filter(game =>
                game.getTitle().toLowerCase().includes(searchTerm)
            );
        }

        // Re-render the list
        this.renderGameList();
        this.updateGameCount();

        // If no games match and we had a selection, clear the detail view
        if (this.filteredGames.length === 0) {
            this.showDetailPlaceholder('No games match the current filters.');
        } else if (this.selectedGameId) {
            // Check if selected game is still in filtered list
            const selectedStillVisible = this.filteredGames.some(
                game => game.getId() === this.selectedGameId
            );
            if (!selectedStillVisible) {
                // Auto-select first game in filtered list
                this.selectGame(this.filteredGames[0].getId());
            }
        } else {
            // Auto-select first game if none selected
            this.selectGame(this.filteredGames[0].getId());
        }
    }

    /**
     * Clear all filters
     */
    clearFilters() {
        this.activeFilters = {
            genreId: null,
            platformId: null,
            title: ''
        };

        this.filteredGames = [...this.games];
        this.renderFilters();
        this.renderGameList();
        this.updateGameCount();

        // Auto-select first game
        if (this.games.length > 0) {
            this.selectGame(this.games[0].getId());
        }
    }

    /**
     * Toggle filter panel visibility
     */
    toggleFilters() {
        this.filtersExpanded = !this.filtersExpanded;

        if (this.filtersExpanded) {
            this.filtersContainer.classList.remove('filters-collapsed');
            this.filterToggle.setAttribute('aria-expanded', 'true');
        } else {
            this.filtersContainer.classList.add('filters-collapsed');
            this.filterToggle.setAttribute('aria-expanded', 'false');
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Filter toggle button
        if (this.filterToggle) {
            this.filterToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleFilters();
            });
        }

        // Also allow clicking the header to toggle
        if (this.filterHeader) {
            this.filterHeader.addEventListener('click', () => {
                this.toggleFilters();
            });
        }

        // Title search input - debounced for performance
        let titleSearchTimeout;
        const titleInput = document.getElementById('filter-title');
        if (titleInput) {
            titleInput.addEventListener('input', (e) => {
                clearTimeout(titleSearchTimeout);
                titleSearchTimeout = setTimeout(() => {
                    this.activeFilters.title = e.target.value;
                    this.applyFilters();
                }, 300); // 300ms debounce
            });
        }

        // Genre filter dropdown
        const genreSelect = document.getElementById('filter-genre');
        if (genreSelect) {
            genreSelect.addEventListener('change', (e) => {
                this.activeFilters.genreId = e.target.value ? parseInt(e.target.value) : null;
                this.applyFilters();
            });
        }

        // Platform filter dropdown
        const platformSelect = document.getElementById('filter-platform');
        if (platformSelect) {
            platformSelect.addEventListener('change', (e) => {
                this.activeFilters.platformId = e.target.value ? parseInt(e.target.value) : null;
                this.applyFilters();
            });
        }

        // Clear filters button
        const clearButton = document.getElementById('clear-filters');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                this.clearFilters();
            });
        }
    }

    /**
     * Show error message
     * @param {string} message - The error message to display
     */
    showError(message) {
        this.gameListContainer.innerHTML = `<div class="loading" style="color: #dc2626;">${message}</div>`;
    }
}

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});
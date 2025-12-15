import { ApiClient } from './api/client.js';
import { AppState } from './state/AppState.js';
import { GameList } from './components/GameList.js';
import { GameDialog } from './components/GameDialog.js';
import { DeleteDialog } from './components/DeleteDialog.js';
import { showToast } from './utils/dom.js';

class App {
    constructor() {
        this.apiClient = new ApiClient();
        this.state = new AppState();

        this.initializeComponents();
        this.setupEventListeners();
        this.initialize();
    }

    initializeComponents() {
        // Get DOM elements
        const gameListEl = document.getElementById('game-list');
        const loadingEl = document.getElementById('loading-spinner');
        const emptyStateEl = document.getElementById('empty-state');
        const gameDialogEl = document.getElementById('game-dialog');
        const deleteDialogEl = document.getElementById('delete-dialog');

        // Initialize components
        this.gameList = new GameList(
            gameListEl,
            loadingEl,
            emptyStateEl,
            (game) => this.handleEdit(game),
            (game) => this.handleDelete(game)
        );

        this.gameDialog = new GameDialog(
            gameDialogEl,
            this.apiClient,
            this.state,
            () => this.loadGames()
        );

        this.deleteDialog = new DeleteDialog(
            deleteDialogEl,
            this.apiClient,
            () => this.loadGames()
        );
    }

    setupEventListeners() {
        // Create button
        document.getElementById('create-game-btn').addEventListener('click', () => {
            this.handleCreate();
        });

        // Subscribe to state changes
        this.state.subscribe((state) => {
            this.gameList.render(state.games, state.loading);
        });
    }

    async initialize() {
        try {
            // Load reference data first
            await this.loadReferenceData();

            // Then load games
            await this.loadGames();
        } catch (error) {
            showToast('Failed to initialize application: ' + error.message, 'error');
        }
    }

    async loadReferenceData() {
        try {
            const [genresResponse, platformsResponse] = await Promise.all([
                this.apiClient.getGenres(),
                this.apiClient.getPlatforms()
            ]);

            this.state.setGenres(genresResponse.data);
            this.state.setPlatforms(platformsResponse.data);
        } catch (error) {
            throw new Error('Failed to load reference data');
        }
    }

    async loadGames() {
        this.state.setLoading(true);

        try {
            const response = await this.apiClient.getGames();
            this.state.setGames(response.data);
            this.state.setLoading(false);
        } catch (error) {
            this.state.setLoading(false);
            showToast('Failed to load games: ' + error.message, 'error');
        }
    }

    handleCreate() {
        this.gameDialog.openForCreate();
    }

    handleEdit(game) {
        this.gameDialog.openForEdit(game);
    }

    handleDelete(game) {
        this.deleteDialog.open(game);
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new App();
});

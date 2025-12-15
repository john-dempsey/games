import { clearElement, createElement, clearFormErrors, showFormErrors, showToast } from '../utils/dom.js';

export class GameDialog {
    constructor(dialogEl, apiClient, state, onSuccess) {
        this.dialog = dialogEl;
        this.form = dialogEl.querySelector('#game-form');
        this.apiClient = apiClient;
        this.state = state;
        this.onSuccess = onSuccess;
        this.editingGameId = null;

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        this.dialog.querySelector('#close-dialog-btn').addEventListener('click', () => {
            this.close();
        });

        // Cancel button
        this.dialog.querySelector('#cancel-dialog-btn').addEventListener('click', () => {
            this.close();
        });

        // Form submission
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleSubmit();
        });

        // Close on escape key or backdrop click
        this.dialog.addEventListener('cancel', (e) => {
            this.close();
        });
    }

    async openForCreate() {
        this.editingGameId = null;
        this.dialog.querySelector('#dialog-title').textContent = 'Create New Game';
        this.dialog.querySelector('#submit-game-btn').textContent = 'Create Game';

        await this.loadReferenceData();
        this.form.reset();
        clearFormErrors(this.form);

        this.dialog.showModal();
    }

    async openForEdit(game) {
        this.editingGameId = game.game_id;
        this.dialog.querySelector('#dialog-title').textContent = 'Edit Game';
        this.dialog.querySelector('#submit-game-btn').textContent = 'Update Game';

        await this.loadReferenceData();
        this.populateForm(game);
        clearFormErrors(this.form);

        this.dialog.showModal();
    }

    close() {
        this.dialog.close();
        this.form.reset();
        clearFormErrors(this.form);
        this.editingGameId = null;
    }

    async loadReferenceData() {
        // Load genres
        const genreSelect = this.form.querySelector('#game-genre');
        clearElement(genreSelect);

        const defaultOption = createElement('option', '', 'Select a genre');
        defaultOption.value = '';
        genreSelect.appendChild(defaultOption);

        this.state.genres.forEach(genre => {
            const option = createElement('option', '', genre.name);
            option.value = genre.genre_id;
            genreSelect.appendChild(option);
        });

        // Load platforms
        const platformsContainer = this.form.querySelector('#platforms-container');
        clearElement(platformsContainer);

        this.state.platforms.forEach(platform => {
            const label = createElement('label', 'checkbox-label');

            const checkbox = createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'platform_ids';
            checkbox.value = platform.platform_id;

            const text = document.createTextNode(platform.name);

            label.appendChild(checkbox);
            label.appendChild(text);
            platformsContainer.appendChild(label);
        });
    }

    populateForm(game) {
        this.form.querySelector('#game-title').value = game.title;
        this.form.querySelector('#game-release-date').value = game.release_date;
        this.form.querySelector('#game-genre').value = game.genre_id;
        this.form.querySelector('#game-description').value = game.description;

        // Check platforms
        if (game.platforms) {
            const platformIds = game.platforms.map(p => p.platform_id.toString());
            const checkboxes = this.form.querySelectorAll('input[name="platform_ids"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = platformIds.includes(checkbox.value);
            });
        }
    }

    async handleSubmit() {
        const submitBtn = this.form.querySelector('#submit-game-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        try {
            const formData = this.getFormData();

            let response;
            if (this.editingGameId) {
                response = await this.apiClient.updateGame(this.editingGameId, formData);
            } else {
                response = await this.apiClient.createGame(formData);
            }

            showToast(response.message || 'Game saved successfully', 'success');
            this.close();
            this.onSuccess();
        } catch (error) {
            if (error.isValidationError && error.isValidationError()) {
                showFormErrors(this.form, error.getValidationErrors());
            } else {
                showToast(error.message || 'An error occurred', 'error');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = this.editingGameId ? 'Update Game' : 'Create Game';
        }
    }

    getFormData() {
        const formData = new FormData(this.form);
        const data = {};

        data.title = formData.get('title');
        data.release_date = formData.get('release_date');
        data.genre_id = parseInt(formData.get('genre_id'));
        data.description = formData.get('description');

        // Get all checked platform IDs
        const platformCheckboxes = this.form.querySelectorAll('input[name="platform_ids"]:checked');
        data.platform_ids = Array.from(platformCheckboxes).map(cb => parseInt(cb.value));

        return data;
    }
}

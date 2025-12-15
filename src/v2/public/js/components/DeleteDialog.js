import { showToast } from '../utils/dom.js';

export class DeleteDialog {
    constructor(dialogEl, apiClient, onSuccess) {
        this.dialog = dialogEl;
        this.apiClient = apiClient;
        this.onSuccess = onSuccess;
        this.gameToDelete = null;

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Cancel button
        this.dialog.querySelector('#cancel-delete-btn').addEventListener('click', () => {
            this.close();
        });

        // Confirm delete button
        this.dialog.querySelector('#confirm-delete-btn').addEventListener('click', async () => {
            await this.handleDelete();
        });

        // Close on escape key
        this.dialog.addEventListener('cancel', () => {
            this.close();
        });
    }

    open(game) {
        this.gameToDelete = game;
        this.dialog.querySelector('#delete-game-title').textContent = game.title;
        this.dialog.showModal();
    }

    close() {
        this.dialog.close();
        this.gameToDelete = null;
    }

    async handleDelete() {
        if (!this.gameToDelete) return;

        const confirmBtn = this.dialog.querySelector('#confirm-delete-btn');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting...';

        try {
            await this.apiClient.deleteGame(this.gameToDelete.game_id);
            showToast('Game deleted successfully', 'success');
            this.close();
            this.onSuccess();
        } catch (error) {
            showToast(error.message || 'Failed to delete game', 'error');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete';
        }
    }
}

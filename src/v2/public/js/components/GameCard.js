import { createElement, escapeHtml } from '../utils/dom.js';

export class GameCard {
    constructor(game, onEdit, onDelete) {
        this.game = game;
        this.onEdit = onEdit;
        this.onDelete = onDelete;
    }

    render() {
        const card = createElement('div', 'game-item');

        // Title
        const title = createElement('h2', '', this.game.title);
        card.appendChild(title);

        // Release Date
        const releaseDate = createElement('p', 'release-date');
        releaseDate.innerHTML = `<strong>Released:</strong> ${this.formatDate(this.game.release_date)}`;
        card.appendChild(releaseDate);

        // Genre
        if (this.game.genre) {
            const genre = createElement('p', 'genre');
            genre.innerHTML = `<strong>Genre:</strong> ${escapeHtml(this.game.genre.name)}`;
            card.appendChild(genre);
        }

        // Description
        const description = createElement('p', 'description', this.game.description);
        card.appendChild(description);

        // Platforms
        if (this.game.platforms && this.game.platforms.length > 0) {
            const platformsDiv = createElement('div', 'platforms');

            const platformsLabel = createElement('strong', '', 'Platforms:');
            platformsDiv.appendChild(platformsLabel);

            const platformTags = createElement('div', 'platform-tags');
            this.game.platforms.forEach(platform => {
                const tag = createElement('span', 'platform-tag', platform.name);
                platformTags.appendChild(tag);
            });

            platformsDiv.appendChild(platformTags);
            card.appendChild(platformsDiv);
        }

        // Actions
        const actions = createElement('div', 'game-actions');

        const editBtn = createElement('button', 'edit-btn', 'Edit');
        editBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.onEdit(this.game);
        });

        const deleteBtn = createElement('button', 'game-delete-btn', 'Delete');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.onDelete(this.game);
        });

        actions.appendChild(editBtn);
        actions.appendChild(deleteBtn);
        card.appendChild(actions);

        return card;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
}

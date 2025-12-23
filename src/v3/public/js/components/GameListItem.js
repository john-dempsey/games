export class GameListItem {
    constructor(game) {
        this.game = game;
    }

    /**
     * Render the game list item as a DOM element
     * @returns {HTMLDivElement}
     */
    render() {
        const div = document.createElement('div');
        div.className = 'game-list-item';
        div.dataset.gameId = this.game.getId();

        // Title
        const title = document.createElement('h3');
        title.className = 'game-list-item__title';
        title.textContent = this.game.getTitle();
        div.appendChild(title);

        // Meta information container
        const meta = document.createElement('div');
        meta.className = 'game-list-item__meta';

        // Genre
        if (this.game.getGenre()) {
            const genre = document.createElement('span');
            genre.className = 'game-list-item__genre';
            genre.textContent = typeof this.game.getGenre() === 'string'
                ? this.game.getGenre()
                : this.game.getGenre().name;
            meta.appendChild(genre);
        }

        // Release date
        if (this.game.getReleaseDate()) {
            const releaseDate = document.createElement('span');
            releaseDate.className = 'game-list-item__date';
            releaseDate.textContent = this.formatDate(this.game.getReleaseDate());
            meta.appendChild(releaseDate);
        }

        div.appendChild(meta);

        // Platforms (abbreviated)
        if (this.game.getPlatforms() && this.game.getPlatforms().length > 0) {
            const platforms = document.createElement('div');
            platforms.className = 'game-list-item__platforms';

            const platformNames = this.game.getPlatforms()
                .map(p => typeof p === 'string' ? p : p.name)
                .join(', ');

            platforms.textContent = platformNames;
            div.appendChild(platforms);
        }

        return div;
    }

    /**
     * Format date string for display
     * @param {string} dateString
     * @returns {string}
     */
    formatDate(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
}

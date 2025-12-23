export class GameDetail {
    constructor(game) {
        this.game = game;
    }

    /**
     * Render the game detail view as a DOM element
     * @returns {HTMLDivElement}
     */
    render() {
        const div = document.createElement('div');
        div.className = 'game-detail';
        div.dataset.gameId = this.game.getId();

        // Header section
        const header = document.createElement('div');
        header.className = 'game-detail__header';

        // Title
        const title = document.createElement('h1');
        title.className = 'game-detail__title';
        title.textContent = this.game.getTitle();
        header.appendChild(title);

        // Release date
        if (this.game.getReleaseDate()) {
            const releaseDate = document.createElement('p');
            releaseDate.className = 'game-detail__release-date';
            releaseDate.innerHTML = `<strong>Release Date:</strong> ${this.formatDate(this.game.getReleaseDate())}`;
            header.appendChild(releaseDate);
        }

        div.appendChild(header);

        // Genre section
        if (this.game.getGenre()) {
            const genreSection = document.createElement('div');
            genreSection.className = 'game-detail__genre-section';

            const genreTitle = document.createElement('h2');
            genreTitle.className = 'game-detail__section-title';
            genreTitle.textContent = 'Genre';
            genreSection.appendChild(genreTitle);

            const genre = this.game.getGenre();

            if (typeof genre === 'object' && genre !== null) {
                // Full genre object with description
                const genreName = document.createElement('h3');
                genreName.className = 'game-detail__genre-name';
                genreName.textContent = genre.name;
                genreSection.appendChild(genreName);

                if (genre.description) {
                    const genreDescription = document.createElement('p');
                    genreDescription.className = 'game-detail__genre-description';
                    genreDescription.textContent = genre.description;
                    genreSection.appendChild(genreDescription);
                }
            } else {
                // Just genre name as string
                const genreName = document.createElement('p');
                genreName.className = 'game-detail__genre-name';
                genreName.textContent = genre;
                genreSection.appendChild(genreName);
            }

            div.appendChild(genreSection);
        }

        // Description section
        if (this.game.getDescription()) {
            const descriptionSection = document.createElement('div');
            descriptionSection.className = 'game-detail__description-section';

            const descTitle = document.createElement('h2');
            descTitle.className = 'game-detail__section-title';
            descTitle.textContent = 'Description';
            descriptionSection.appendChild(descTitle);

            const description = document.createElement('p');
            description.className = 'game-detail__description';
            description.textContent = this.game.getDescription();
            descriptionSection.appendChild(description);

            div.appendChild(descriptionSection);
        }

        // Platforms section
        if (this.game.getPlatforms() && this.game.getPlatforms().length > 0) {
            const platformsSection = document.createElement('div');
            platformsSection.className = 'game-detail__platforms-section';

            const platformsTitle = document.createElement('h2');
            platformsTitle.className = 'game-detail__section-title';
            platformsTitle.textContent = 'Available Platforms';
            platformsSection.appendChild(platformsTitle);

            const platformsList = document.createElement('ul');
            platformsList.className = 'game-detail__platforms-list';

            this.game.getPlatforms().forEach(platform => {
                const listItem = document.createElement('li');
                listItem.className = 'game-detail__platform-item';

                if (typeof platform === 'object' && platform !== null) {
                    // Full platform object with manufacturer
                    const platformName = document.createElement('span');
                    platformName.className = 'game-detail__platform-name';
                    platformName.textContent = platform.name;
                    listItem.appendChild(platformName);

                    if (platform.manufacturer) {
                        const manufacturer = document.createElement('span');
                        manufacturer.className = 'game-detail__platform-manufacturer';
                        manufacturer.textContent = ` (${platform.manufacturer})`;
                        listItem.appendChild(manufacturer);
                    }
                } else {
                    // Just platform name as string
                    listItem.textContent = platform;
                }

                platformsList.appendChild(listItem);
            });

            platformsSection.appendChild(platformsList);
            div.appendChild(platformsSection);
        }

        // Footer with metadata
        const footer = document.createElement('div');
        footer.className = 'game-detail__footer';

        const gameId = document.createElement('p');
        gameId.className = 'game-detail__id';
        gameId.innerHTML = `<strong>Game ID:</strong> ${this.game.getId()}`;
        footer.appendChild(gameId);

        if (this.game.getGenreId()) {
            const genreId = document.createElement('p');
            genreId.className = 'game-detail__genre-id';
            genreId.innerHTML = `<strong>Genre ID:</strong> ${this.game.getGenreId()}`;
            footer.appendChild(genreId);
        }

        div.appendChild(footer);

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
            month: 'long',
            day: 'numeric'
        });
    }
}

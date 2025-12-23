import { ApiClient } from './ApiClient.js';

export class Game {
    constructor(data = {}) {
        this.game_id = data.game_id || null;
        this.title = data.title || '';
        this.release_date = data.release_date || '';
        this.genre_id = data.genre_id || null;
        this.description = data.description || '';
        this.platform_ids = data.platform_ids || [];

        // Additional properties from API responses
        this.genre = data.genre || null;
        this.platforms = data.platforms || [];
    }

    /**
     * Find all games
     * @returns {Promise<Game[]>}
     */
    static async findAll() {
        const client = new ApiClient();
        const response = await client.getGames();

        if (response.success && response.data) {
            return response.data.map(gameData => new Game(gameData));
        }

        return [];
    }

    /**
     * Find a game by ID
     * @param {number} id - The game ID
     * @returns {Promise<Game|null>}
     */
    static async findById(id) {
        const client = new ApiClient();
        const response = await client.getGame(id);

        if (response.success && response.data) {
            return new Game(response.data);
        }

        return null;
    }

    /**
     * Save the game (create or update)
     * @returns {Promise<Game>}
     */
    async save() {
        const client = new ApiClient();
        const gameData = {
            title: this.title,
            release_date: this.release_date,
            genre_id: this.genre_id,
            description: this.description,
            platform_ids: this.platform_ids
        };

        let response;

        if (this.game_id) {
            // Update existing game
            response = await client.updateGame(this.game_id, gameData);
        } else {
            // Create new game
            response = await client.createGame(gameData);
        }

        if (response.success && response.data) {
            // Update this instance with the response data
            Object.assign(this, response.data);
        }

        return this;
    }

    /**
     * Delete the game
     * @returns {Promise<boolean>}
     */
    async delete() {
        if (!this.game_id) {
            throw new Error('Cannot delete a game that has not been saved');
        }

        const client = new ApiClient();
        const response = await client.deleteGame(this.game_id);

        return response.success;
    }

    /**
     * Get the game ID
     * @returns {number|null}
     */
    getId() {
        return this.game_id;
    }

    /**
     * Get the game title
     * @returns {string}
     */
    getTitle() {
        return this.title;
    }

    /**
     * Set the game title
     * @param {string} title
     */
    setTitle(title) {
        this.title = title;
    }

    /**
     * Get the release date
     * @returns {string}
     */
    getReleaseDate() {
        return this.release_date;
    }

    /**
     * Set the release date
     * @param {string} date
     */
    setReleaseDate(date) {
        this.release_date = date;
    }

    /**
     * Get the genre ID
     * @returns {number|null}
     */
    getGenreId() {
        return this.genre_id;
    }

    /**
     * Set the genre ID
     * @param {number} genreId
     */
    setGenreId(genreId) {
        this.genre_id = genreId;
    }

    /**
     * Get the description
     * @returns {string}
     */
    getDescription() {
        return this.description;
    }

    /**
     * Set the description
     * @param {string} description
     */
    setDescription(description) {
        this.description = description;
    }

    /**
     * Get the platform IDs
     * @returns {number[]}
     */
    getPlatformIds() {
        return this.platform_ids;
    }

    /**
     * Set the platform IDs
     * @param {number[]} platformIds
     */
    setPlatformIds(platformIds) {
        this.platform_ids = platformIds;
    }

    /**
     * Get the genre information (from API response)
     * @returns {Object|null}
     */
    getGenre() {
        return this.genre;
    }

    /**
     * Get the platforms information (from API response)
     * @returns {Array}
     */
    getPlatforms() {
        return this.platforms;
    }

    /**
     * Convert game to plain object
     * @returns {Object}
     */
    toObject() {
        return {
            game_id: this.game_id,
            title: this.title,
            release_date: this.release_date,
            genre_id: this.genre_id,
            description: this.description,
            platform_ids: this.platform_ids,
            genre: this.genre,
            platforms: this.platforms
        };
    }
}

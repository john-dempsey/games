import { ApiClient } from './ApiClient.js';

export class Genre {
    constructor(data = {}) {
        this.genre_id = data.genre_id || null;
        this.name = data.name || '';
        this.description = data.description || '';
    }

    /**
     * Find all genres
     * @returns {Promise<Genre[]>}
     */
    static async findAll() {
        const client = new ApiClient();
        const response = await client.getGenres();

        if (response.success && response.data) {
            return response.data.map(genreData => new Genre(genreData));
        }

        return [];
    }

    /**
     * Find a genre by ID
     * @param {number} id - The genre ID
     * @returns {Promise<Genre|null>}
     */
    static async findById(id) {
        const genres = await Genre.findAll();
        return genres.find(genre => genre.genre_id === parseInt(id)) || null;
    }

    /**
     * Get the genre ID
     * @returns {number|null}
     */
    getId() {
        return this.genre_id;
    }

    /**
     * Get the genre name
     * @returns {string}
     */
    getName() {
        return this.name;
    }

    /**
     * Get the genre description
     * @returns {string}
     */
    getDescription() {
        return this.description;
    }

    /**
     * Convert genre to plain object
     * @returns {Object}
     */
    toObject() {
        return {
            genre_id: this.genre_id,
            name: this.name,
            description: this.description
        };
    }
}

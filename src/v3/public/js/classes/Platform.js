import { ApiClient } from './ApiClient.js';

export class Platform {
    constructor(data = {}) {
        this.platform_id = data.platform_id || null;
        this.name = data.name || '';
        this.manufacturer = data.manufacturer || '';
    }

    /**
     * Find all platforms
     * @returns {Promise<Platform[]>}
     */
    static async findAll() {
        const client = new ApiClient();
        const response = await client.getPlatforms();

        if (response.success && response.data) {
            return response.data.map(platformData => new Platform(platformData));
        }

        return [];
    }

    /**
     * Find a platform by ID
     * @param {number} id - The platform ID
     * @returns {Promise<Platform|null>}
     */
    static async findById(id) {
        const platforms = await Platform.findAll();
        return platforms.find(platform => platform.platform_id === parseInt(id)) || null;
    }

    /**
     * Get the platform ID
     * @returns {number|null}
     */
    getId() {
        return this.platform_id;
    }

    /**
     * Get the platform name
     * @returns {string}
     */
    getName() {
        return this.name;
    }

    /**
     * Get the platform manufacturer
     * @returns {string}
     */
    getManufacturer() {
        return this.manufacturer;
    }

    /**
     * Convert platform to plain object
     * @returns {Object}
     */
    toObject() {
        return {
            platform_id: this.platform_id,
            name: this.name,
            manufacturer: this.manufacturer
        };
    }
}

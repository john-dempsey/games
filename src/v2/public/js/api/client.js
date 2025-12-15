export class ApiClient {
    constructor(baseUrl = '/v2/api') {
        this.baseUrl = baseUrl;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;

        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new ApiError(data.error, response.status);
            }

            return data;
        } catch (error) {
            if (error instanceof ApiError) {
                throw error;
            }
            // Network error or JSON parse error
            throw new ApiError({
                message: 'Network error. Please check your connection.',
                code: 'NETWORK_ERROR'
            }, 0);
        }
    }

    // Games endpoints
    async getGames() {
        return this.request('/games');
    }

    async getGame(id) {
        return this.request(`/games/${id}`);
    }

    async createGame(gameData) {
        return this.request('/games', {
            method: 'POST',
            body: JSON.stringify(gameData)
        });
    }

    async updateGame(id, gameData) {
        return this.request(`/games/${id}`, {
            method: 'PUT',
            body: JSON.stringify(gameData)
        });
    }

    async deleteGame(id) {
        return this.request(`/games/${id}`, {
            method: 'DELETE'
        });
    }

    // Reference data endpoints
    async getGenres() {
        return this.request('/genres');
    }

    async getPlatforms() {
        return this.request('/platforms');
    }
}

export class ApiError extends Error {
    constructor(errorData, statusCode) {
        super(errorData.message || 'An error occurred');
        this.name = 'ApiError';
        this.code = errorData.code;
        this.validationErrors = errorData.validation_errors || null;
        this.statusCode = statusCode;
    }

    isValidationError() {
        return this.code === 'VALIDATION_ERROR' && this.validationErrors !== null;
    }

    getValidationErrors() {
        return this.validationErrors || {};
    }
}

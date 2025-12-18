export class ApiClient {
    constructor(baseUrl = '/v3/api') {
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
                throw new ApiError(data, response.status);
            }

            return data;
        } catch (error) {
            if (error instanceof ApiError) {
                throw error;
            }
            // Network error or JSON parse error
            throw new ApiError('Error: ' + error.message, 0);
        }
    }

    // Games endpoints
    async getGames() {
        return this.request('/index.php');
    }

    async getGame(id) {
        return this.request(`/games_show.php?id=${id}`);
    }

    async createGame(gameData) {
        return this.request('/games_store.php', {
            method: 'POST',
            body: JSON.stringify(gameData)
        });
    }

    async updateGame(id, gameData) {
        return this.request(`/games_update.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(gameData)
        });
    }

    async deleteGame(id) {
        return this.request(`/games_delete.php?id=${id}`, {
            method: 'DELETE'
        });
    }
}

export class ApiError extends Error {
    constructor(data, statusCode) {
        super(data.message || 'An error occurred');
        this.name = 'ApiError';
        this.validationErrors = data.validation_errors || null;
        this.statusCode = statusCode;
    }

    isValidationError() {
        return this.validationErrors !== null;
    }

    getValidationErrors() {
        return this.validationErrors || {};
    }
}

export class AppState {
    constructor() {
        this.games = [];
        this.genres = [];
        this.platforms = [];
        this.loading = false;
        this.error = null;
        this.listeners = [];
    }

    setState(updates) {
        Object.assign(this, updates);
        this.notify();
    }

    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    notify() {
        this.listeners.forEach(listener => listener(this));
    }

    // Game operations
    setGames(games) {
        this.setState({ games });
    }

    addGame(game) {
        this.setState({ games: [...this.games, game] });
    }

    updateGame(updatedGame) {
        const games = this.games.map(game =>
            game.game_id === updatedGame.game_id ? updatedGame : game
        );
        this.setState({ games });
    }

    deleteGame(gameId) {
        const games = this.games.filter(game => game.game_id !== gameId);
        this.setState({ games });
    }

    // Reference data operations
    setGenres(genres) {
        this.setState({ genres });
    }

    setPlatforms(platforms) {
        this.setState({ platforms });
    }

    // UI state operations
    setLoading(loading) {
        this.setState({ loading });
    }

    setError(error) {
        this.setState({ error });
    }

    clearError() {
        this.setState({ error: null });
    }
}

import { GameCard } from './GameCard.js';
import { clearElement, showElement, hideElement } from '../utils/dom.js';

export class GameList {
    constructor(container, loadingEl, emptyStateEl, onEdit, onDelete) {
        this.container = container;
        this.loadingEl = loadingEl;
        this.emptyStateEl = emptyStateEl;
        this.onEdit = onEdit;
        this.onDelete = onDelete;
    }

    render(games, loading) {
        if (loading) {
            this.showLoading();
            return;
        }

        this.hideLoading();

        if (!games || games.length === 0) {
            this.showEmpty();
            return;
        }

        this.hideEmpty();
        this.renderGames(games);
    }

    renderGames(games) {
        clearElement(this.container);

        games.forEach(game => {
            const gameCard = new GameCard(game, this.onEdit, this.onDelete);
            this.container.appendChild(gameCard.render());
        });
    }

    showLoading() {
        hideElement(this.container);
        hideElement(this.emptyStateEl);
        showElement(this.loadingEl);
    }

    hideLoading() {
        hideElement(this.loadingEl);
    }

    showEmpty() {
        hideElement(this.container);
        showElement(this.emptyStateEl);
    }

    hideEmpty() {
        hideElement(this.emptyStateEl);
        showElement(this.container);
    }
}

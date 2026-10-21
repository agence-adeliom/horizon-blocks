const Listing: {
    selector?: string;
    getAllInstances?: () => HTMLElement[];
    initInstance?: (instance: HTMLElement) => void;
    init?: () => void;
} = {};

Listing.selector = '.listing-block';

Listing.getAllInstances = () => {
    return Array.from(document.querySelectorAll(Listing.selector));
};

Listing.getSecondaryFiltersButton = (instance: HTMLElement, panel: HTMLElement) => {
    return instance.querySelector(`[data-for="${panel.dataset.id}"]`) as HTMLElement | null;
};

Listing.closeSecondaryFilters = (instance: HTMLElement, panel: HTMLElement) => {
    const button = Listing.getSecondaryFiltersButton(instance, panel);

    panel.classList.add('hidden');
    panel.classList.remove('active');
    button?.setAttribute('aria-expanded', 'false');
    button?.focus();
};

Listing.openSecondaryFilters = (instance: HTMLElement, panel: HTMLElement) => {
    panel.classList.remove('hidden');
    panel.classList.add('active');
    Listing.getSecondaryFiltersButton(instance, panel)?.setAttribute('aria-expanded', 'true');
};

Listing.initInstanceSecondaryFilters = instance => {
    instance.querySelector('.secondary-filters-btn')?.addEventListener('click', e => {
        e.preventDefault();

        const target = instance.querySelector(`[data-id="${e.currentTarget.dataset.for}"]`);

        if (target) {
            if (target.classList.contains('hidden')) {
                Listing.openSecondaryFilters(instance, target);
            } else {
                Listing.closeSecondaryFilters(instance, target);
            }
        }
    });

    ['close', 'reset', 'apply'].forEach(action => {
        instance.querySelector(`[data-${action}]`)?.addEventListener('click', e => {
            e.preventDefault();

            const target = instance.querySelector(`[data-id="${e.currentTarget.dataset[action]}"]`);

            if (target) {
                Listing.closeSecondaryFilters(instance, target);
            }
        });
    });

    // Escape closes the open panel, from the panel or from its button.
    instance.addEventListener('keydown', e => {
        if (e.key !== 'Escape') {
            return;
        }

        const panel = instance.querySelector('.secondary-filters.active') as HTMLElement | null;

        if (panel && (panel.contains(e.target as Node) || Listing.getSecondaryFiltersButton(instance, panel) === e.target)) {
            e.stopPropagation();
            Listing.closeSecondaryFilters(instance, panel);
        }
    });
};

Listing.initInstance = instance => {
    Listing.initInstanceSecondaryFilters(instance);
};

Listing.init = () => {
    Listing.getAllInstances().forEach(instance => {
        Listing.initInstance(instance);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    Listing.init();
});

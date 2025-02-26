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

Listing.initInstanceSecondaryFilters = (instance) => {
  instance.querySelector('.secondary-filters-btn')?.addEventListener('click', (e) => {
    e.preventDefault();

    if (e.target.dataset.for) {
      const target = instance.querySelector(`[data-id="${e.target.dataset.for}"]`);

      if (target) {
        target.classList.remove('hidden');
        target.classList.add('active');
      }
    }
  });

  instance.querySelector('[data-close]')?.addEventListener('click', (e) => {
    e.preventDefault();

    if (e.target.dataset.close) {
      const target = instance.querySelector(`[data-id="${e.target.dataset.close}"]`);

      if (target) {
        target.classList.add('hidden');
        target.classList.remove('active');
      }
    }
  })
}

Listing.initInstance = (instance) => {
  const form = instance.querySelector('form');

  if (form) {
    form.addEventListener('change', () => {
      const loading = instance.querySelector('.loading');
      const results = instance.querySelector('.results');

      if (loading && results) {
        loading.classList.remove('hidden');
        results.classList.add('hidden');
      }
    });
  }

  Listing.initInstanceSecondaryFilters(instance);
};

Listing.init = () => {
  console.log(Listing.getAllInstances());
  Listing.getAllInstances().forEach((instance) => {
    Listing.initInstance(instance);
  });
};

document.addEventListener('DOMContentLoaded', () => {
  Listing.init();
});

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
};

Listing.init = () => {
  document.addEventListener('DOMContentLoaded', () => {
    Listing.getAllInstances().forEach((instance) => {
      Listing.initInstance(instance);
    });
  });
};

Listing.init();

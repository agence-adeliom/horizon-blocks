const PostSummary = {
    init: undefined,
    getPostSummary: undefined,
    selector: '.summary-list',
    initElt: undefined,
    eltSelector: '.summary-elt',
    associations: [],
    scrollOffset: 100,
    eltActiveClass: 'summary-active',
    eltBeforeActiveClass: 'summary-before-active',
    handleScroll: undefined,
};

let instance: HTMLElement = null;

// WordPress remplace les apostrophes et guillemets droits du titre affiché (wptexturize) : on compare sans en tenir compte
const normalizeTitle = (text: string): string =>
    text
        .replace(/[\u2018\u2019]/g, "'")
        .replace(/[\u201C\u201D\u00AB\u00BB]/g, '"')
        .replace(/\s+/g, ' ')
        .trim();

PostSummary.getPostSummary = () => {
    if (null === instance) {
        instance = document.querySelector(PostSummary.selector);

        if (instance && instance.hasAttribute('scroll-offset')) {
            const scrollOffset = instance.getAttribute('scroll-offset');

            // Check if is number
            if (!isNaN(Number(scrollOffset))) {
                PostSummary.scrollOffset = Number(scrollOffset);
            } else {
                console.warn(`PostSummary: scroll-offset attribute is not a number`);
            }
        }

        if (instance && instance.hasAttribute('active-class')) {
            const activeClass = instance.getAttribute('active-class');

            if (activeClass) {
                PostSummary.eltActiveClass = activeClass;
            } else {
                console.warn(`PostSummary: active-class attribute is empty`);
            }
        }

        if (instance && instance.hasAttribute('before-active-class')) {
            const beforeActiveClass = instance.getAttribute('before-active-class');

            if (beforeActiveClass) {
                PostSummary.eltBeforeActiveClass = beforeActiveClass;
            } else {
                console.warn(`PostSummary: before-active-class attribute is empty`);
            }
        }
    }

    return instance;
};

PostSummary.initElt = (elt: HTMLElement) => {
    const listElts: Element[] = Array.from(elt.querySelectorAll(PostSummary.eltSelector));
    const htmlBased = elt.hasAttribute('html-based');

    listElts.forEach(listElt => {
        let title: string = null;
        let level: string = 'h2';
        let contentTitle = null;

        if (listElt.hasAttribute('data-title')) {
            title = listElt.getAttribute('data-title').trim();
        } else {
            title = listElt.textContent.trim();
        }

        if (listElt.hasAttribute('data-level')) {
            level = listElt.getAttribute('data-level').trim();
        }

        if (!htmlBased) {
            const xpath = `//*[normalize-space(text())="${title}"]`;
            const nodeSnapshot = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

            for (let i = 0; i < nodeSnapshot.snapshotLength; i++) {
                const item = nodeSnapshot.snapshotItem(i);

                if (item.classList.contains('heading')) {
                    contentTitle = item;
                    break;
                }
            }
        } else if (level && title) {
            const elements = document.querySelectorAll(level);

            // Parcourt les éléments pour trouver celui qui a le bon textContent
            for (const element of elements) {
                // textContent récupère le texte brut sans les balises HTML
                if (normalizeTitle(element.textContent) === normalizeTitle(title)) {
                    contentTitle = element;
                }
            }
        }

        if (contentTitle) {
            linkToTitle(listElt as HTMLAnchorElement, contentTitle);

            PostSummary.associations.push({
                content: contentTitle,
                summary: listElt,
            });
        }
    });
};

// Le lien cible l'id du titre (posé s'il manque) : le titre reçoit le focus, la lecture et la tabulation reprennent à la section
const linkToTitle = (link: HTMLAnchorElement, title: HTMLElement) => {
    const wantedId = link.hash.slice(1);

    if (!title.id) {
        title.id = wantedId && !document.getElementById(wantedId) ? wantedId : `${wantedId || 'section'}-${PostSummary.associations.length + 1}`;
    }

    link.href = `#${title.id}`;
    title.style.scrollMarginTop = `${PostSummary.scrollOffset}px`;

    link.addEventListener('click', (event) => {
        event.preventDefault();

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        title.setAttribute('tabindex', '-1');
        title.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth' });
        title.focus({ preventScroll: true });
        history.replaceState(null, '', `#${title.id}`);
    });
};

const setSummaryEltAsBeforeActive = (elt: HTMLElement) => {
    if (elt.classList.contains(PostSummary.eltActiveClass)) {
        elt.classList.remove(PostSummary.eltActiveClass);
    }

    if (!elt.classList.contains(PostSummary.eltBeforeActiveClass)) {
        elt.classList.add(PostSummary.eltBeforeActiveClass);
    }

    elt.removeAttribute('aria-current');
};

const setSummaryEltAsActive = (elt: HTMLElement) => {
    if (elt.classList.contains(PostSummary.eltBeforeActiveClass)) {
        elt.classList.remove(PostSummary.eltBeforeActiveClass);
    }

    if (!elt.classList.contains(PostSummary.eltActiveClass)) {
        elt.classList.add(PostSummary.eltActiveClass);
    }

    elt.setAttribute('aria-current', 'location');
};

const setSummaryEltAsInactive = (elt: HTMLElement) => {
    if (elt.classList.contains(PostSummary.eltActiveClass)) {
        elt.classList.remove(PostSummary.eltActiveClass);
    }

    if (elt.classList.contains(PostSummary.eltBeforeActiveClass)) {
        elt.classList.remove(PostSummary.eltBeforeActiveClass);
    }

    elt.removeAttribute('aria-current');
};

PostSummary.handleScroll = () => {
    const threshold = window.innerHeight / 2;
    let activeIndex = -1;

    PostSummary.associations.forEach((association, idx) => {
        const rectTop = association.content.getBoundingClientRect().top;

        if (rectTop <= threshold) {
            activeIndex = idx; // Le dernier à avoir passé le seuil
        }
    });

    PostSummary.associations.forEach((association, idx) => {
        const summary = association.summary;

        if (idx < activeIndex) {
            setSummaryEltAsBeforeActive(summary);
        } else if (idx === activeIndex) {
            setSummaryEltAsActive(summary);
        } else {
            setSummaryEltAsInactive(summary);
        }
    });

    // Fallback : activer le premier si aucun trouvé
    if (activeIndex === -1 && PostSummary.associations.length > 0) {
        setSummaryEltAsActive(PostSummary.associations[0].summary);
    }
};

PostSummary.init = () => {
    window.addEventListener('load', () => {
        if (PostSummary.getPostSummary()) {
            PostSummary.initElt(PostSummary.getPostSummary());

            const toggleSummary: HTMLButtonElement = document.querySelector('[js-toggle-summary]');
            const summaryContent: HTMLElement = document.querySelector('[js-summary-content]');

            // Replié : liens retirés du parcours et de la restitution
            toggleSummary?.addEventListener('click', () => {
                const isOpen = toggleSummary.getAttribute('aria-expanded') === 'true';

                toggleSummary.setAttribute('aria-expanded', String(!isOpen));
                summaryContent.classList.toggle('grid-rows-[1fr]', !isOpen);
                summaryContent.classList.toggle('grid-rows-[0fr]', isOpen);
                summaryContent.inert = isOpen;
            });

            if (PostSummary.associations) {
                window.addEventListener('scroll', () => {
                    PostSummary.handleScroll();
                });

                PostSummary.handleScroll();
            }
        }
    });
};

PostSummary.init();

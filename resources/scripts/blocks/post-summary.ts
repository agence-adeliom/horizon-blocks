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

    listElts.forEach(listElt => {
        let title: string = null;

        if (listElt.hasAttribute('data-title')) {
            title = listElt.getAttribute('data-title').trim();
        } else {
            title = listElt.textContent.trim();
        }

        const xpath = `//*[normalize-space(text())="${title}"]`;
        let contentTitle = null;
        const nodeSnapshot = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

        for (let i = 0; i < nodeSnapshot.snapshotLength; i++) {
            const item = nodeSnapshot.snapshotItem(i);

            if (item.classList.contains('heading')) {
                contentTitle = item;
                break;
            }
        }

        if (contentTitle) {
            PostSummary.associations.push({
                content: contentTitle,
                summary: listElt,
            });
            listElt.addEventListener('click', () => {
                // Scroll to contentTitle
                window.scrollTo({
                    top: contentTitle.getBoundingClientRect().top + window.scrollY - PostSummary.scrollOffset,
                    behavior: 'smooth',
                });
            });
        }
    });
};

const setSummaryEltAsBeforeActive = (elt: HTMLElement) => {
    if (elt.classList.contains(PostSummary.eltActiveClass)) {
        elt.classList.remove(PostSummary.eltActiveClass);
    }

    if (!elt.classList.contains(PostSummary.eltBeforeActiveClass)) {
        elt.classList.add(PostSummary.eltBeforeActiveClass);
    }
};

const setSummaryEltAsActive = (elt: HTMLElement) => {
    if (elt.classList.contains(PostSummary.eltBeforeActiveClass)) {
        elt.classList.remove(PostSummary.eltBeforeActiveClass);
    }

    if (!elt.classList.contains(PostSummary.eltActiveClass)) {
        elt.classList.add(PostSummary.eltActiveClass);
    }
};

const setSummaryEltAsInactive = (elt: HTMLElement) => {
    if (elt.classList.contains(PostSummary.eltActiveClass)) {
        elt.classList.remove(PostSummary.eltActiveClass);
    }

    if (elt.classList.contains(PostSummary.eltBeforeActiveClass)) {
        elt.classList.remove(PostSummary.eltBeforeActiveClass);
    }
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
